<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manages the shopping cart for SAHARA CONNECT.
 *
 * Supports both authenticated users (cart tied to user_id) and
 * anonymous visitors (cart tied to session_id). Cart items are
 * always priced from the server-side product price to prevent
 * client-side price manipulation.
 */
class CartController extends Controller
{
    // ─── Helper ────────────────────────────────────────────────────────────────

    /**
     * Find or create the current cart for the request.
     *
     * - Logged-in user  → find/create by user_id (merge session cart if present)
     * - Guest           → find/create by session_id
     */
    protected function getOrCreateCart(Request $request): Cart
    {
        if (auth()->check()) {
            $userId = auth()->id();

            // Check if there is an orphan guest cart to merge
            $sessionId   = $request->session()->getId();
            $guestCart   = Cart::where('session_id', $sessionId)
                               ->whereNull('user_id')
                               ->first();

            $userCart = Cart::firstOrCreate(
                ['user_id' => $userId],
                ['session_id' => $sessionId]
            );

            // Merge guest cart items into user cart
            if ($guestCart && $guestCart->id !== $userCart->id) {
                foreach ($guestCart->items as $guestItem) {
                    $existing = $userCart->items()
                        ->where('product_id', $guestItem->product_id)
                        ->first();

                    if ($existing) {
                        $existing->increment('quantity', $guestItem->quantity);
                    } else {
                        $userCart->items()->create([
                            'product_id' => $guestItem->product_id,
                            'quantity'   => $guestItem->quantity,
                            'price'      => $guestItem->price,
                        ]);
                    }
                }
                $guestCart->delete();
            }

            return $userCart;
        }

        // Guest cart keyed on Laravel session ID
        $sessionId = $request->session()->getId();

        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null]
        );
    }

    // ─── Actions ───────────────────────────────────────────────────────────────

    /**
     * Display the cart page.
     */
    public function index(Request $request): Renderable
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load(['items.product.images', 'items.product.category']);

        return view('cart.index', compact('cart'));
    }

    /**
     * Add a product to the cart (supports AJAX).
     *
     * @return JsonResponse|RedirectResponse
     */
    public function add(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        // Guard: product must be active and in stock
        if ($product->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart.product_unavailable'),
                ], 422);
            }
            return back()->withErrors(['product' => __('cart.product_unavailable')]);
        }

        $quantity = (int) $request->input('quantity');

        if ($product->stock < $quantity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart.insufficient_stock', ['available' => $product->stock]),
                ], 422);
            }
            return back()->withErrors(['quantity' => __('cart.insufficient_stock', ['available' => $product->stock])]);
        }

        $cart = $this->getOrCreateCart($request);

        // Find or create a CartItem for this product
        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $quantity;

            if ($product->stock < $newQty) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('cart.insufficient_stock', ['available' => $product->stock]),
                    ], 422);
                }
                return back()->withErrors(['quantity' => __('cart.insufficient_stock', ['available' => $product->stock])]);
            }

            $cartItem->update(['quantity' => $newQty]);
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'price'      => $product->current_price, // server-side price snapshot
            ]);
        }

        $cartCount = $cart->items()->sum('quantity');

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => __('cart.added_successfully'),
                'cart_count' => $cartCount,
                'item'       => [
                    'id'       => $cartItem->id,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => number_format($cartItem->subtotal, 2),
                ],
            ]);
        }

        return back()->with('success', __('cart.added_successfully'));
    }

    /**
     * Update the quantity of a cart item (supports AJAX).
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $quantity = (int) $request->input('quantity');
        $product  = $cartItem->product;

        if ($product->stock < $quantity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart.insufficient_stock', ['available' => $product->stock]),
                ], 422);
            }
            return back()->withErrors(['quantity' => __('cart.insufficient_stock', ['available' => $product->stock])]);
        }

        // Always refresh the price from DB in case of price change
        $cartItem->update([
            'quantity' => $quantity,
            'price'    => $product->current_price,
        ]);

        $cart      = $cartItem->cart;
        $cartCount = $cart->items()->sum('quantity');
        $cartTotal = $cart->items()->get()->sum(fn ($i) => $i->price * $i->quantity);

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => true,
                'cart_count'   => $cartCount,
                'cart_total'   => number_format($cartTotal, 2),
                'item_subtotal'=> number_format($cartItem->subtotal, 2),
            ]);
        }

        return back()->with('success', __('cart.updated_successfully'));
    }

    /**
     * Remove a single item from the cart (supports AJAX).
     *
     * @return JsonResponse|RedirectResponse
     */
    public function remove(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $cart = $cartItem->cart;
        $cartItem->delete();

        $cartCount = $cart->items()->sum('quantity');
        $cartTotal = $cart->items()->get()->sum(fn ($i) => $i->price * $i->quantity);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => __('cart.removed_successfully'),
                'cart_count' => $cartCount,
                'cart_total' => number_format($cartTotal, 2),
            ]);
        }

        return back()->with('success', __('cart.removed_successfully'));
    }

    /**
     * Clear all items from the current cart.
     */
    public function clear(Request $request): RedirectResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();

        return back()->with('success', __('cart.cleared_successfully'));
    }

    /**
     * Return the current cart item count as JSON (for the navbar badge).
     */
    public function getCartCount(Request $request): JsonResponse
    {
        $cart  = $this->getOrCreateCart($request);
        $count = $cart->items()->sum('quantity');

        return response()->json(['count' => $count]);
    }
}
