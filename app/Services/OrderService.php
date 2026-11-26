<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerOrder;
use Illuminate\Support\Facades\DB;

/**
 * Centralises every order status transition so that the multi-vendor
 * structure stays consistent:
 *
 *   Order (customer facing)  ──1..n──▶  SellerOrder (per seller)
 *                                     └─▶ OrderItems (snapshot of each line)
 *
 * Side effects handled here:
 *   • seller sub-orders are kept in sync with the parent order
 *   • payment_status follows the delivery / refund state
 *   • commissions are approved when the configured trigger status is reached
 *   • commissions are cancelled (and stock restored) on cancellation / refund
 */
class OrderService
{
    /** Progression used to derive a parent status from its seller sub-orders. */
    private const PROGRESSION = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    public function __construct(private CommissionService $commissions)
    {
    }

    /**
     * Admin-side transition: applies the status to the order and to every
     * seller sub-order.
     */
    public function adminSetStatus(Order $order, string $status, ?string $note = null): void
    {
        DB::transaction(function () use ($order, $status, $note) {
            $sellerStatus = $this->sellerStatusFor($status);

            foreach ($order->sellerOrders as $sellerOrder) {
                $this->applySellerStatus($sellerOrder, $sellerStatus, $note);
            }

            $this->applyOrderStatus($order, $status, $note);

            if ($status === 'cancelled' || $status === 'refunded') {
                $this->restoreStock($order);
                $this->commissions->cancelForOrder($order, __('admin.commission_cancelled_reason'));
            }

            if ($status === config('affiliate.commission_trigger_status', 'delivered')) {
                $this->commissions->approveForOrder($order);
            }
        });
    }

    /**
     * Seller-side transition: only touches the seller's own sub-order and then
     * recomputes the parent order status.
     */
    public function sellerSetStatus(SellerOrder $sellerOrder, string $status, ?string $note = null): void
    {
        DB::transaction(function () use ($sellerOrder, $status, $note) {
            $this->applySellerStatus($sellerOrder, $status, $note);

            $order = $sellerOrder->order->fresh(['sellerOrders']);
            $parentStatus = $this->deriveParentStatus($order);

            $this->applyOrderStatus($order, $parentStatus, $note);

            if ($parentStatus === 'cancelled') {
                $this->restoreStock($order);
                $this->commissions->cancelForOrder($order, __('admin.commission_cancelled_reason'));
            }

            if ($parentStatus === config('affiliate.commission_trigger_status', 'delivered')) {
                $this->commissions->approveForOrder($order);
            }
        });
    }

    /**
     * Cancel an order placed by a customer (only pending/confirmed orders).
     */
    public function customerCancel(Order $order): void
    {
        $this->adminSetStatus($order, 'cancelled', __('orders.cancelled_by_customer'));
    }

    // ─── Internals ─────────────────────────────────────────────────────────────

    private function applyOrderStatus(Order $order, string $status, ?string $note = null): void
    {
        $attributes = ['status' => $status];

        if ($status === 'confirmed' && ! $order->confirmed_at) {
            $attributes['confirmed_at'] = now();
        }

        if ($status === 'delivered') {
            $attributes['delivered_at'] = now();
            $attributes['payment_status'] = 'paid';
        }

        if ($status === 'refunded') {
            $attributes['payment_status'] = 'refunded';
        }

        if ($note) {
            $attributes['notes'] = trim(($order->notes ? $order->notes . "\n" : '') . $note);
        }

        $order->update($attributes);
    }

    private function applySellerStatus(SellerOrder $sellerOrder, string $status, ?string $note = null): void
    {
        $attributes = ['status' => $status];

        if ($status === 'shipped' && ! $sellerOrder->shipped_at) {
            $attributes['shipped_at'] = now();
        }

        if ($status === 'delivered') {
            $attributes['delivered_at'] = now();
        }

        if ($note) {
            $attributes['seller_notes'] = $note;
        }

        $sellerOrder->update($attributes);
    }

    private function sellerStatusFor(string $orderStatus): string
    {
        return match ($orderStatus) {
            'refunded' => 'cancelled',
            default    => in_array($orderStatus, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'], true)
                ? $orderStatus
                : 'pending',
        };
    }

    /**
     * The parent order is "as advanced as its least advanced active seller".
     * If every seller sub-order is cancelled, the whole order is cancelled.
     */
    private function deriveParentStatus(Order $order): string
    {
        $active = $order->sellerOrders->whereNotIn('status', ['cancelled']);

        if ($active->isEmpty()) {
            return 'cancelled';
        }

        $indexes = $active->map(fn (SellerOrder $s) => array_search($s->status, self::PROGRESSION, true))
            ->filter(fn ($i) => $i !== false);

        if ($indexes->isEmpty()) {
            return 'pending';
        }

        return self::PROGRESSION[$indexes->min()];
    }

    /**
     * Put the ordered quantities back in stock when an order is cancelled.
     */
    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            $product->increment('stock', $item->quantity);

            if ($product->sales_count >= $item->quantity) {
                $product->decrement('sales_count', $item->quantity);
            }
        }
    }
}
