<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the seller's shop profile form.
     */
    public function edit(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return view('seller.profile.edit', [
            'profile' => $user->sellerProfile,
        ]);
    }

    /**
     * Persist the shop profile, re-uploading the logo / banner when provided.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'shop_name'   => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'city'        => ['nullable', 'string', 'max:100'],
            'region'      => ['nullable', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'whatsapp'    => ['nullable', 'string', 'max:20'],
            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Uploaded files are handled explicitly below so an empty file input
        // can never wipe an existing logo / banner.
        unset($validated['logo'], $validated['banner']);

        $profile = $user->sellerProfile ?? new SellerProfile(['user_id' => $user->id]);

        if ($request->hasFile('logo')) {
            $this->deleteStoredFile($profile->logo);
            $validated['logo'] = $request->file('logo')->store('sellers/' . $user->id, 'public');
        }

        if ($request->hasFile('banner')) {
            $this->deleteStoredFile($profile->banner);
            $validated['banner'] = $request->file('banner')->store('sellers/' . $user->id, 'public');
        }

        $profile->fill($validated);
        $profile->user_id = $user->id;
        $profile->save();

        return redirect()
            ->route('seller.profile.edit')
            ->with('success', __('seller.profile_updated'));
    }

    /**
     * Remove a previously stored file from the public disk (ignores external URLs).
     */
    private function deleteStoredFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
