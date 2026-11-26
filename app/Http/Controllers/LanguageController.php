<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles runtime locale switching for SAHARA CONNECT.
 *
 * Supported locales: fr (French), ar (Arabic), en (English).
 * The chosen locale is stored in the session and consumed by
 * the SetLocale middleware on every subsequent request.
 */
class LanguageController extends Controller
{
    /**
     * Switch the application locale for the current user session.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function switchLanguage(Request $request): RedirectResponse
    {
        $request->validate([
            'locale' => ['required', 'string', 'in:fr,ar,en'],
        ]);

        $locale = $request->input('locale');

        // Persist the chosen locale in the session
        session(['locale' => $locale]);

        // If the user is authenticated, also update their profile preference
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return redirect()->back()->with('success', __('messages.language_changed'));
    }
}
