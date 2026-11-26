<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Affiliate payout requests.
 *
 * The only write an affiliate is allowed to perform is inserting a
 * `payout_requests` row — never a commission and never a ledger money column.
 * Every amount is validated server-side against the live ledger balance.
 */
class PayoutController extends Controller
{
    /**
     * Payout history, balance summary and the request form.
     */
    public function index(): View
    {
        $this->authorize('viewAny', PayoutRequest::class);

        /** @var \App\Models\User $user */
        $user   = auth()->user();
        $userId = $user->id;

        $payouts = PayoutRequest::where('affiliate_id', $userId)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Live balance summary straight from the ledger.
        $totals = Commission::where('affiliate_id', $userId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status IN ('pending','approved','paid') THEN commission_amount ELSE 0 END), 0) AS earned,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN commission_amount ELSE 0 END), 0) AS available,
                COALESCE(SUM(CASE WHEN status = 'paid'     THEN commission_amount ELSE 0 END), 0) AS paid
            ")
            ->first();

        $hasPendingRequest = PayoutRequest::where('affiliate_id', $userId)
            ->where('status', 'pending')
            ->exists();

        $minPayout = (float) config('affiliate.min_payout');
        $profile   = $user->affiliateProfile;

        return view('affiliate.payouts.index', compact(
            'payouts',
            'totals',
            'hasPendingRequest',
            'minPayout',
            'profile'
        ));
    }

    /**
     * Store a new pending payout request.
     */
    public function request(Request $request): RedirectResponse
    {
        $this->authorize('create', PayoutRequest::class);

        /** @var \App\Models\User $user */
        $user      = auth()->user();
        $minPayout = (float) config('affiliate.min_payout');

        $validated = $request->validate([
            'amount'          => ['required', 'numeric', 'min:' . $minPayout],
            'payment_method'  => ['required', 'string', 'max:255'],
            'payment_details' => ['required', 'string', 'max:255'],
        ]);

        // Available balance = approved-but-unpaid commissions (never trusted from the browser).
        $available = (float) Commission::where('affiliate_id', $user->id)
            ->where('status', 'approved')
            ->sum('commission_amount');

        $amount = (float) $validated['amount'];

        if ($amount > $available) {
            return back()
                ->withInput()
                ->with('error', __('affiliate.payout_amount_exceeds'));
        }

        // Refuse a second request while one is already pending.
        $hasPending = PayoutRequest::where('affiliate_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()
                ->withInput()
                ->with('error', __('affiliate.payout_pending_exists'));
        }

        DB::transaction(function () use ($user, $amount, $validated) {
            PayoutRequest::create([
                'affiliate_id'    => $user->id,
                'amount'          => $amount,
                'payment_method'  => $validated['payment_method'],
                'payment_details' => $validated['payment_details'],
                'status'          => 'pending',
            ]);
        });

        return redirect()
            ->route('affiliate.payouts.index')
            ->with('success', __('affiliate.payout_requested'));
    }
}
