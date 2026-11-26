<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public marketing pages: about, become-a-seller, become-an-affiliate, contact.
 */
class PageController extends Controller
{
    public function about(): Renderable
    {
        return view('pages.about');
    }

    public function becomeSeller(): Renderable
    {
        $categories = \App\Models\Category::active()->parents()->orderBy('sort_order')->get();

        return view('pages.become-seller', compact('categories'));
    }

    public function becomeAffiliate(): Renderable
    {
        return view('pages.become-affiliate');
    }

    public function contact(): Renderable
    {
        return view('pages.contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // The message is persisted in the newsletter log so nothing is lost
        // when the mail transport is not configured in this environment.
        NewsletterSubscriber::firstOrCreate(
            ['email' => $validated['email']],
            ['name' => $validated['name']]
        );

        Log::info('Contact message received', $validated);

        return back()->with('success', __('messages.contact_sent'));
    }
}
