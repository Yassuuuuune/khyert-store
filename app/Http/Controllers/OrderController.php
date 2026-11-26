<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Displays and manages a customer's own orders on SAHARA CONNECT.
 *
 * All routes in this controller require authentication. Ownership
 * is verified before any order data is exposed or mutated.
 */
class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
        $this->middleware('auth');
    }

    /**
     * Display a paginated list of the authenticated customer's orders.
     */
    public function index(): Renderable
    {
        $orders = Order::where('customer_id', auth()->id())
            ->with(['items.product', 'sellerOrders'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display a single order detail page.
     *
     * Aborts with 403 if the order does not belong to the authenticated user.
     */
    public function show(Order $order): Renderable
    {
        abort_unless(
            $order->customer_id === auth()->id(),
            403,
            __('orders.access_denied')
        );

        $order->load([
            'items.product.images',
            'sellerOrders.seller',
            'customer',
        ]);

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel a cancellable order owned by the authenticated user.
     */
    public function cancel(Order $order): RedirectResponse
    {
        abort_unless(
            $order->customer_id === auth()->id(),
            403,
            __('orders.access_denied')
        );

        if (!$order->isCancellable()) {
            return back()->with('error', __('orders.cannot_cancel', [
                'status' => $order->status,
            ]));
        }

        $this->orderService->customerCancel($order);

        return back()->with('success', __('orders.cancelled_successfully', [
            'number' => $order->order_number,
        ]));
    }
}
