<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Product;
use App\Models\SellerOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Products at or below this stock level are flagged as "low stock". */
    private const LOW_STOCK_THRESHOLD = 5;

    /** Statuses shown in the orders doughnut chart, in display order. */
    private const ORDER_STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

    /**
     * Display the seller dashboard with statistics and Chart.js datasets.
     * Every metric is scoped to the authenticated seller only.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user          = auth()->user();
        $sellerId      = $user->id;
        $sellerProfile = $user->sellerProfile;

        // ── Revenue base: the seller's delivered / already-paid sub-orders ────────
        $revenueBase = SellerOrder::query()
            ->where('seller_orders.seller_id', $sellerId)
            ->join('orders', 'seller_orders.order_id', '=', 'orders.id')
            ->whereNull('orders.deleted_at')
            ->where('seller_orders.status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->where('seller_orders.status', 'delivered')
                    ->orWhere('orders.payment_status', 'paid');
            });

        $totalRevenue = (float) (clone $revenueBase)->sum('seller_orders.subtotal');

        // ── Orders ────────────────────────────────────────────────────────────────
        $totalOrders     = SellerOrder::where('seller_id', $sellerId)->count();
        $pendingOrders   = SellerOrder::where('seller_id', $sellerId)->where('status', 'pending')->count();
        $deliveredOrders = SellerOrder::where('seller_id', $sellerId)->where('status', 'delivered')->count();

        $averageOrder = $deliveredOrders > 0
            ? $totalRevenue / $deliveredOrders
            : 0.0;

        // ── Products ──────────────────────────────────────────────────────────────
        $totalProducts   = Product::where('seller_id', $sellerId)->count();
        $activeProducts  = Product::where('seller_id', $sellerId)->where('status', 'active')->count();
        $pendingProducts = Product::where('seller_id', $sellerId)->where('status', 'pending')->count();
        $lowStockProducts = Product::where('seller_id', $sellerId)
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->count();

        // ── Rating & sales ────────────────────────────────────────────────────────
        $averageRating = (float) Product::where('seller_id', $sellerId)
            ->where('rating_count', '>', 0)
            ->avg('rating_avg');

        $totalSales = (int) Product::where('seller_id', $sellerId)->sum('sales_count');

        // ── Revenue chart: last 12 months (grouped in PHP for DB portability) ─────
        $monthlyRevenue = (clone $revenueBase)
            ->where('seller_orders.created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select('seller_orders.subtotal as subtotal', 'seller_orders.created_at as created_at')
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->created_at)->format('Y-m'));

        $chartLabels = [];
        $chartData   = [];
        for ($i = 11; $i >= 0; $i--) {
            $date          = now()->subMonths($i);
            $key           = $date->format('Y-m');
            $chartLabels[] = $date->translatedFormat('M Y');
            $chartData[]   = round((float) $monthlyRevenue->get($key, collect())->sum('subtotal'), 2);
        }

        // ── Orders per status (doughnut chart) ────────────────────────────────────
        $statusCounts = SellerOrder::where('seller_id', $sellerId)
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusLabels = array_map(fn ($status) => __('status.' . $status), self::ORDER_STATUSES);
        $statusData   = array_map(fn ($status) => (int) ($statusCounts[$status] ?? 0), self::ORDER_STATUSES);

        // ── Recent orders & best sellers ──────────────────────────────────────────
        $recentOrders = SellerOrder::where('seller_id', $sellerId)
            ->with(['order.customer'])
            ->latest()
            ->limit(5)
            ->get();

        $bestSellingProducts = Product::where('seller_id', $sellerId)
            ->with('category')
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get();

        // ── Affiliate activity generated on this seller's catalogue ───────────────
        $affiliateCommissionsTotal = (float) Commission::where('seller_id', $sellerId)->sum('commission_amount');
        $affiliateConversionsCount = Commission::where('seller_id', $sellerId)->count();

        $recentCommissions = Commission::where('seller_id', $sellerId)
            ->with(['product', 'affiliate', 'order'])
            ->latest()
            ->limit(5)
            ->get();

        $commissionRate = $sellerProfile?->commission_rate;

        return view('seller.dashboard', compact(
            'sellerProfile',
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'deliveredOrders',
            'averageOrder',
            'totalProducts',
            'activeProducts',
            'pendingProducts',
            'lowStockProducts',
            'averageRating',
            'totalSales',
            'chartLabels',
            'chartData',
            'statusLabels',
            'statusData',
            'recentOrders',
            'bestSellingProducts',
            'affiliateCommissionsTotal',
            'affiliateConversionsCount',
            'recentCommissions',
            'commissionRate'
        ));
    }
}
