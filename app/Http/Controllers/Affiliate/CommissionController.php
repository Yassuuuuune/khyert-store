<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only view of the affiliate's own commission ledger.
 *
 * Affiliates can never create, edit or delete a commission row — every query
 * is scoped to their own user id and no write method exists here.
 */
class CommissionController extends Controller
{
    /** Allowed status filters. */
    protected const STATUSES = ['pending', 'approved', 'paid', 'cancelled'];

    /** Period filters mapped to the number of days back (null = all time / named period). */
    protected const PERIODS = ['30', '90', 'month', 'year'];

    /**
     * Paginated, filterable list of the affiliate's commissions.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Commission::class);

        $userId = auth()->id();

        $status = $request->input('status');
        $period = $request->input('period');

        $query = Commission::where('affiliate_id', $userId)
            ->with(['product', 'order']);

        if (in_array($status, self::STATUSES, true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        if (in_array($period, self::PERIODS, true)) {
            match ($period) {
                '30'    => $query->where('created_at', '>=', now()->subDays(30)),
                '90'    => $query->where('created_at', '>=', now()->subDays(90)),
                'month' => $query->where('created_at', '>=', now()->startOfMonth()),
                'year'  => $query->where('created_at', '>=', now()->startOfYear()),
            };
        } else {
            $period = null;
        }

        $commissions = $query->latest()->paginate(15)->withQueryString();

        // Totals per status across the affiliate's whole ledger (not just the page).
        $totalsByStatus = Commission::where('affiliate_id', $userId)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('affiliate.commissions.index', compact(
            'commissions',
            'status',
            'period',
            'totalsByStatus'
        ));
    }
}
