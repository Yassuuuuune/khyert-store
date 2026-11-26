<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateClick;
use App\Models\Commission;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Affiliate-facing overview, promotion links and statistics.
 *
 * This controller is strictly READ-ONLY over the commission ledger: every
 * query is scoped to the authenticated affiliate's own user id so an affiliate
 * can only ever see their own data.
 */
class DashboardController extends Controller
{
    /**
     * Affiliate overview: KPIs, two charts, referral kit and recent commissions.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $userId = $user->id;
        $profile = $user->affiliateProfile;

        // ── Ledger totals (never trust cached or browser-sent amounts) ──────
        $totals = Commission::where('affiliate_id', $userId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status IN ('pending','approved','paid') THEN commission_amount ELSE 0 END), 0) AS earned,
                COALESCE(SUM(CASE WHEN status = 'pending'  THEN commission_amount ELSE 0 END), 0) AS pending,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN commission_amount ELSE 0 END), 0) AS available,
                COALESCE(SUM(CASE WHEN status = 'paid'     THEN commission_amount ELSE 0 END), 0) AS paid
            ")
            ->first();

        $totalEarned        = (float) ($totals->earned ?? 0);
        $pendingCommissions = (float) ($totals->pending ?? 0);
        $availableBalance   = (float) ($totals->available ?? 0);
        $paidTotal          = (float) ($totals->paid ?? 0);

        $totalClicks      = (int) ($profile->total_clicks ?? 0);
        $totalConversions = (int) ($profile->total_conversions ?? 0);
        $conversionRate   = (float) ($profile->conversion_rate ?? 0);

        // Number of products the affiliate can currently promote.
        $activeLinks = Product::where('affiliate_enabled', true)
            ->where('status', 'active')
            ->count();

        // ── Clicks per day over the last 30 days ────────────────────────────
        $clicksRaw = AffiliateClick::where('affiliate_id', $userId)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('date(created_at) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $clickLabels = [];
        $clickData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $day           = now()->subDays($i);
            $clickLabels[] = $day->format('d/m');
            $clickData[]   = (int) ($clicksRaw[$day->format('Y-m-d')] ?? 0);
        }

        // ── Commissions earned per month over the last 12 months ────────────
        $commissionRaw = Commission::where('affiliate_id', $userId)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->selectRaw("strftime('%Y-%m', created_at) as m, SUM(commission_amount) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $commissionLabels = [];
        $commissionData   = [];
        for ($i = 11; $i >= 0; $i--) {
            $month              = now()->subMonths($i);
            $commissionLabels[] = $month->format('m/Y');
            $commissionData[]   = (float) ($commissionRaw[$month->format('Y-m')] ?? 0);
        }

        $recentCommissions = Commission::where('affiliate_id', $userId)
            ->with(['product', 'order'])
            ->latest()
            ->limit(10)
            ->get();

        $referralCode = $user->referral_code;
        $referralLink = route('home') . '?ref=' . $referralCode;

        return view('affiliate.dashboard', compact(
            'profile',
            'totalEarned',
            'pendingCommissions',
            'availableBalance',
            'paidTotal',
            'totalClicks',
            'totalConversions',
            'conversionRate',
            'activeLinks',
            'clickLabels',
            'clickData',
            'commissionLabels',
            'commissionData',
            'recentCommissions',
            'referralCode',
            'referralLink'
        ));
    }

    /**
     * Promotion kit: referral code/link plus a searchable grid of eligible
     * products, each with its own referral link and commission preview.
     */
    public function links(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user         = auth()->user();
        $profile      = $user->affiliateProfile;
        $referralCode = $user->referral_code;
        $referralLink = route('home') . '?ref=' . $referralCode;

        $search = trim((string) $request->input('q'));

        $products = Product::where('affiliate_enabled', true)
            ->where('status', 'active')
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('name_ar', 'like', $term)
                        ->orWhere('name_en', 'like', $term);
                });
            })
            ->orderByDesc('sales_count')
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(function (Product $product) use ($referralCode) {
            $rate   = (float) $product->effective_commission_rate;
            $price  = (float) $product->current_price;

            $product->setAttribute('referral_link', route('products.show', $product) . '?ref=' . $referralCode);
            $product->setAttribute('referral_rate', $rate);
            $product->setAttribute('referral_amount', round($price * $rate / 100, 2));

            return $product;
        });

        return view('affiliate.links', compact(
            'profile',
            'referralCode',
            'referralLink',
            'products',
            'search'
        ));
    }

    /**
     * Detailed performance: engagement over time, top products and a
     * per-product breakdown table.
     */
    public function statistics(): View
    {
        $userId = auth()->id();
        $start  = now()->subMonths(11)->startOfMonth();

        // ── Engagement over the last 12 months ──────────────────────────────
        $clicksByMonth = AffiliateClick::where('affiliate_id', $userId)
            ->where('created_at', '>=', $start)
            ->selectRaw("strftime('%Y-%m', created_at) as m, COUNT(*) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $conversionsByMonth = Commission::where('affiliate_id', $userId)
            ->where('created_at', '>=', $start)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->selectRaw("strftime('%Y-%m', created_at) as m, COUNT(DISTINCT order_id) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $commissionByMonth = Commission::where('affiliate_id', $userId)
            ->where('created_at', '>=', $start)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->selectRaw("strftime('%Y-%m', created_at) as m, SUM(commission_amount) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $chartLabels       = [];
        $clicksSeries      = [];
        $conversionsSeries = [];
        $bestKey           = null;
        $bestValue         = -1.0;

        for ($i = 11; $i >= 0; $i--) {
            $month  = now()->subMonths($i);
            $key    = $month->format('Y-m');

            $chartLabels[]       = $month->format('m/Y');
            $clicksSeries[]      = (int) ($clicksByMonth[$key] ?? 0);
            $conversionsSeries[] = (int) ($conversionsByMonth[$key] ?? 0);

            $monthCommission = (float) ($commissionByMonth[$key] ?? 0);
            if ($monthCommission > $bestValue) {
                $bestValue = $monthCommission;
                $bestKey   = $key;
            }
        }

        $bestPeriod       = null;
        $bestPeriodAmount = 0.0;
        if ($bestKey !== null && $bestValue > 0) {
            $bestPeriod       = \Illuminate\Support\Carbon::createFromFormat('Y-m', $bestKey)->format('m/Y');
            $bestPeriodAmount = $bestValue;
        }

        // ── Top converting products ─────────────────────────────────────────
        $topProducts = Commission::where('affiliate_id', $userId)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->select(
                'product_id',
                DB::raw('COUNT(DISTINCT order_id) as orders_count'),
                DB::raw('COALESCE(SUM(commission_amount), 0) as commission_total')
            )
            ->groupBy('product_id')
            ->orderByDesc('orders_count')
            ->limit(5)
            ->with('product')
            ->get();

        // ── Per-product breakdown (paginated) ───────────────────────────────
        $productIds = AffiliateClick::where('affiliate_id', $userId)
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->merge(
                Commission::where('affiliate_id', $userId)->distinct()->pluck('product_id')
            )
            ->unique()
            ->values();

        $clicksMap = AffiliateClick::where('affiliate_id', $userId)
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('COUNT(*) as total'))
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $commissionMap = Commission::where('affiliate_id', $userId)
            ->whereIn('product_id', $productIds)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->select(
                'product_id',
                DB::raw('COUNT(DISTINCT order_id) as orders'),
                DB::raw('COALESCE(SUM(sale_amount), 0) as revenue'),
                DB::raw('COALESCE(SUM(commission_amount), 0) as commission')
            )
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $productStats = Product::whereIn('id', $productIds)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $productStats->getCollection()->transform(function (Product $product) use ($clicksMap, $commissionMap) {
            $clicks = (int) ($clicksMap[$product->id] ?? 0);
            $row    = $commissionMap[$product->id] ?? null;
            $orders = (int) ($row->orders ?? 0);

            $product->setAttribute('stat_clicks', $clicks);
            $product->setAttribute('stat_orders', $orders);
            $product->setAttribute('stat_revenue', (float) ($row->revenue ?? 0));
            $product->setAttribute('stat_commission', (float) ($row->commission ?? 0));
            $product->setAttribute('stat_conversion_rate', $clicks > 0 ? round($orders / $clicks * 100, 2) : 0);

            return $product;
        });

        return view('affiliate.statistics', compact(
            'chartLabels',
            'clicksSeries',
            'conversionsSeries',
            'bestPeriod',
            'bestPeriodAmount',
            'topProducts',
            'productStats'
        ));
    }
}
