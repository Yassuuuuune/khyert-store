<?php

namespace App\Http\Controllers;

use App\Models\AffiliateProfile;
use App\Models\AffiliateReferral;
use App\Models\Cart;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerOrder;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Handles the checkout flow for SAHARA CONNECT.
 *
 * Authentication is required for both steps. All prices, shipping fees and
 * commission amounts are recalculated server-side from the database — values
 * submitted by the browser are never trusted.
 */
class CheckoutController extends Controller
{
    public function __construct(private CommissionService $commissions)
    {
        $this->middleware('auth');
    }

    /**
     * Display the checkout form.
     */
    public function index(Request $request): Renderable|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product.images', 'items.product.sellerProfile'])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('checkout.cart_empty'));
        }

        $affiliateCode = $this->resolveAffiliateCode($request);
        $affiliate     = $affiliateCode ? User::where('referral_code', $affiliateCode)->first() : null;

        if ($affiliate && $affiliate->id === $user->id) {
            $affiliate = null;
        }

        $shipping = $this->calculateShipping((float) $cart->total);

        return view('checkout.index', compact('cart', 'affiliate', 'affiliateCode', 'shipping'));
    }

    /**
     * Process the checkout inside a database transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = auth()->user();

        $validated = $request->validate([
            'shipping_name'    => ['required', 'string', 'max:255'],
            'shipping_phone'   => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city'    => ['required', 'string', 'max:100'],
            'shipping_region'  => ['nullable', 'string', 'max:100'],
            'payment_method'   => ['nullable', 'in:cod'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = Cart::where('user_id', $customer->id)->with(['items.product'])->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('checkout.cart_empty'));
        }

        // ── Server-side stock verification ────────────────────────────────────
        foreach ($cart->items as $item) {
            $product = $item->product;

            if (! $product || $product->status !== 'active') {
                return back()->with('error', __('checkout.product_unavailable', [
                    'name' => $product?->name ?? '#' . $item->product_id,
                ]));
            }

            if ($product->stock < $item->quantity) {
                return back()->with('error', __('checkout.insufficient_stock', [
                    'name'      => $product->name,
                    'available' => $product->stock,
                ]));
            }
        }

        // ── Affiliate attribution (never self-referral) ───────────────────────
        $affiliateCode = $this->resolveAffiliateCode($request);
        $affiliateUser = null;

        if ($affiliateCode) {
            $candidate = User::where('referral_code', $affiliateCode)->first();

            if ($candidate
                && $candidate->id !== $customer->id
                && $candidate->isAffiliate()
                && $candidate->affiliateProfile?->isApproved()) {
                $affiliateUser = $candidate;
            }
        }

        // ── Totals recalculated from the database ─────────────────────────────
        $subtotal = (float) $cart->items->sum(fn ($item) => $item->product->current_price * $item->quantity);
        $shipping = $this->calculateShipping($subtotal);
        $discount = 0.00;
        $total    = $subtotal + $shipping - $discount;

        try {
            $order = DB::transaction(function () use (
                $customer, $cart, $validated, $subtotal, $shipping, $discount, $total,
                $affiliateUser, $affiliateCode
            ) {
                $order = Order::create([
                    'order_number'     => Order::generateOrderNumber(),
                    'customer_id'      => $customer->id,
                    'affiliate_id'     => $affiliateUser?->id,
                    'affiliate_code'   => $affiliateUser?->referral_code,
                    'subtotal'         => $subtotal,
                    'discount'         => $discount,
                    'shipping'         => $shipping,
                    'total'            => $total,
                    'status'           => 'pending',
                    'payment_method'   => 'cod',
                    'payment_status'   => 'pending',
                    'shipping_name'    => $validated['shipping_name'],
                    'shipping_phone'   => $validated['shipping_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_city'    => $validated['shipping_city'],
                    'shipping_region'  => $validated['shipping_region'] ?? null,
                    'shipping_country' => 'MA',
                    'notes'            => $validated['notes'] ?? null,
                ]);

                $sellerSubtotals = [];

                foreach ($cart->items as $item) {
                    $product      = $item->product;
                    $unitPrice    = $product->current_price;
                    $itemSubtotal = $unitPrice * $item->quantity;

                    $commissionRate = ($affiliateUser && $product->affiliate_enabled)
                        ? $product->effective_commission_rate
                        : 0;

                    $commissionAmount = round($itemSubtotal * ($commissionRate / 100), 2);

                    $orderItem = OrderItem::create([
                        'order_id'                    => $order->id,
                        'product_id'                  => $product->id,
                        'seller_id'                   => $product->seller_id,
                        'product_name'                => $product->name,
                        'product_image'               => $product->main_image,
                        'unit_price'                  => $unitPrice,
                        'quantity'                    => $item->quantity,
                        'subtotal'                    => $itemSubtotal,
                        'affiliate_commission_rate'   => $commissionRate,
                        'affiliate_commission_amount' => $commissionAmount,
                        'commission_generated'        => false,
                    ]);

                    $sellerSubtotals[$product->seller_id] =
                        ($sellerSubtotals[$product->seller_id] ?? 0) + $itemSubtotal;

                    // Ledger entry — created as `pending`, approved once the
                    // configured order status is reached.
                    if ($affiliateUser && $commissionAmount > 0) {
                        Commission::create([
                            'affiliate_id'      => $affiliateUser->id,
                            'order_id'          => $order->id,
                            'order_item_id'     => $orderItem->id,
                            'product_id'        => $product->id,
                            'seller_id'         => $product->seller_id,
                            'sale_amount'       => $itemSubtotal,
                            'commission_rate'   => $commissionRate,
                            'commission_amount' => $commissionAmount,
                            'status'            => 'pending',
                        ]);

                        $orderItem->update(['commission_generated' => true]);
                    }

                    $product->decrement('stock', $item->quantity);
                    $product->increment('sales_count', $item->quantity);
                }

                // Multi-vendor: one sub-order per seller
                foreach ($sellerSubtotals as $sellerId => $sellerSubtotal) {
                    SellerOrder::create([
                        'order_id'  => $order->id,
                        'seller_id' => $sellerId,
                        'subtotal'  => $sellerSubtotal,
                        'status'    => 'pending',
                    ]);
                }

                // Referral attribution record (unique per order + affiliate)
                if ($affiliateUser) {
                    AffiliateReferral::create([
                        'affiliate_id'  => $affiliateUser->id,
                        'order_id'      => $order->id,
                        'customer_id'   => $customer->id,
                        'referral_code' => $affiliateUser->referral_code,
                    ]);

                    if ($affiliateUser->affiliateProfile) {
                        $affiliateUser->affiliateProfile->increment('total_conversions');
                        $this->commissions->refreshProfile($affiliateUser);
                    }
                }

                $cart->items()->delete();

                return $order;
            });

            session()->forget('affiliate_code');
            cookie()->queue(cookie()->forget('affiliate_code'));

            return redirect()->route('checkout.confirmation', $order)
                ->with('success', __('checkout.order_placed', ['number' => $order->order_number]));

        } catch (Throwable $e) {
            Log::error('Checkout failed', [
                'user_id' => $customer->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', __('checkout.error_processing'));
        }
    }

    /**
     * Order confirmation page (owner only).
     */
    public function confirmation(Order $order): Renderable
    {
        abort_unless($order->customer_id === auth()->id(), 403);

        $order->load(['items.product', 'sellerOrders.seller']);

        return view('checkout.confirmation', compact('order'));
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function resolveAffiliateCode(Request $request): ?string
    {
        $code = $request->cookie('affiliate_code') ?? $request->session()->get('affiliate_code');

        return is_string($code) && $code !== '' ? $code : null;
    }

    /**
     * Flat 40 MAD delivery fee, free above 500 MAD.
     */
    private function calculateShipping(float $subtotal): float
    {
        return $subtotal >= 500 ? 0.00 : 40.00;
    }
}
