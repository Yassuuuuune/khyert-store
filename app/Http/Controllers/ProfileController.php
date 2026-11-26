<?php

namespace App\Http\Controllers;

use App\Models\AffiliateProfile;
use App\Models\SellerProfile;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

/**
 * Manages the authenticated user's profile on SAHARA CONNECT.
 *
 * Handles personal info updates, avatar upload, and application
 * forms for becoming a seller or affiliate partner.
 */
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the profile edit form.
     */
    public function edit(): Renderable
    {
        /** @var \App\Models\User $user */
        $user = auth()->user()->load(['sellerProfile', 'affiliateProfile']);

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the authenticated user's personal information and optionally password / avatar.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'    => ['nullable', 'string', 'max:20'],
            'locale'   => ['nullable', 'string', 'in:fr,ar,en'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', Password::defaults()],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $data = [
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'phone'  => $validated['phone'] ?? null,
            'locale' => $validated['locale'] ?? $user->locale,
        ];

        // Update password only when explicitly provided
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Remove the old avatar file if it exists in storage
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store the new avatar under storage/app/public/avatars/
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $user->update($data);

        // Update locale in the session so the page reflects the change immediately
        session(['locale' => $user->locale]);

        return back()->with('success', __('profile.updated_successfully'));
    }

    /**
     * Submit a seller application for the authenticated user.
     *
     * Creates a pending SellerProfile record. The user keeps
     * their current role until an admin approves the application.
     */
    public function applyAsSeller(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Guard: prevent duplicate applications
        if ($user->sellerProfile) {
            return back()->with('error', __('profile.seller_already_applied'));
        }

        $validated = $request->validate([
            'shop_name'   => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'city'        => ['required', 'string', 'max:100'],
            'region'      => ['nullable', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'whatsapp'    => ['nullable', 'string', 'max:20'],
        ]);

        SellerProfile::create([
            'user_id'     => $user->id,
            'shop_name'   => $validated['shop_name'],
            'description' => $validated['description'],
            'city'        => $validated['city'],
            'region'      => $validated['region'] ?? null,
            'phone'       => $validated['phone'] ?? null,
            'whatsapp'    => $validated['whatsapp'] ?? null,
            'status'      => 'pending',
        ]);

        return back()->with('success', __('profile.seller_application_sent'));
    }

    /**
     * Submit an affiliate partner application for the authenticated user.
     *
     * Creates a pending AffiliateProfile record. Approval is handled
     * by an admin from the back-office panel.
     */
    public function applyAsAffiliate(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Guard: prevent duplicate applications
        if ($user->affiliateProfile) {
            return back()->with('error', __('profile.affiliate_already_applied'));
        }

        $validated = $request->validate([
            'website'           => ['nullable', 'url', 'max:255'],
            'promotion_methods' => ['required', 'string', 'max:1000'],
        ]);

        AffiliateProfile::create([
            'user_id'           => $user->id,
            'website'           => $validated['website'] ?? null,
            'promotion_methods' => $validated['promotion_methods'],
            'status'            => 'pending',
        ]);

        return back()->with('success', __('profile.affiliate_application_sent'));
    }
}
