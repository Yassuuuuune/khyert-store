<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerOrder;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Allowed seller-driven status transitions. The terminal states cannot be
     * changed by the seller; every other step moves strictly forward, and a
     * seller may cancel any order that is not yet shipped.
     */
    private const TRANSITIONS = [
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered'],
        'delivered'  => [],
        'cancelled'  => [],
    ];

    public function __construct(private OrderService $orderService)
    {
    }

    /**
     * Paginated list of the sub-orders addressed to the authenticated seller.
     */
    public function index(Request $request): View
    {
        $sellerId = $request->user()->id;

        $orders = SellerOrder::where('seller_id', $sellerId)
            ->with(['order.customer'])
            ->withCount('items')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.orders.index', [
            'orders'  => $orders,
            'filters' => ['status' => $request->input('status')],
        ]);
    }

    /**
     * Show one seller sub-order with its own items and shipping details.
     */
    public function show(SellerOrder $sellerOrder): View
    {
        $this->authorize('view', $sellerOrder);

        $sellerOrder->load(['order.customer', 'items.product']);

        return view('seller.orders.show', [
            'sellerOrder'     => $sellerOrder,
            'nextStatuses'    => self::TRANSITIONS[$sellerOrder->status] ?? [],
            'itemsSubtotal'   => (float) $sellerOrder->items->sum('subtotal'),
        ]);
    }

    /**
     * Transition the seller's sub-order through an allowed status and let the
     * OrderService keep the parent order, stock and commissions consistent.
     */
    public function updateStatus(Request $request, SellerOrder $sellerOrder): RedirectResponse
    {
        $this->authorize('update', $sellerOrder);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,processing,shipped,delivered,cancelled'],
        ]);

        $allowed = self::TRANSITIONS[$sellerOrder->status] ?? [];

        if (! in_array($validated['status'], $allowed, true)) {
            return back()->with('error', __('seller.invalid_status_transition'));
        }

        $this->orderService->sellerSetStatus($sellerOrder, $validated['status']);

        return back()->with('success', __('seller.status_updated'));
    }
}
