<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Editable affiliate profile.
 *
 * Only descriptive/payment fields may be changed here. The money and counter
 * columns (total_earned, total_pending, total_paid, balance, total_clicks,
 * total_conversions) are owned exclusively by CommissionService and are never
 * accepted from this form.
 */
class ProfileController extends Controller
{
    /**
     * Show the affiliate's profile form.
     */
    public function edit(): View
    {
        /** @var \App\Models\User $user */
        $user    = auth()->user();
        $profile = $user->affiliateProfile;

        abort_if($profile === null, 404);

        return view('affiliate.profile.edit', compact('user', 'profile'));
    }

    /**
     * Persist the affiliate's editable profile fields.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user    = auth()->user();
        $profile = $user->affiliateProfile;

        abort_if($profile === null, 404);

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'website'           => ['nullable', 'string', 'max:255'],
            'promotion_methods' => ['nullable', 'string', 'max:2000'],
            'payment_method'    => ['nullable', 'in:bank,paypal,cash'],
            'payment_details'   => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $profile, $validated) {
            // User-level identity fields.
            $user->update([
                'name'  => $validated['name'],
                'phone' => $validated['phone'] ?? null,
            ]);

            // Affiliate-profile fields — strictly excluding every money column.
            $profile->update([
                'website'           => $validated['website'] ?? null,
                'promotion_methods' => $validated['promotion_methods'] ?? null,
                'payment_method'    => $validated['payment_method'] ?? null,
                'payment_details'   => $validated['payment_details'] ?? null,
            ]);
        });

        return redirect()
            ->route('affiliate.profile.edit')
            ->with('success', __('affiliate.profile_updated'));
    }
}
