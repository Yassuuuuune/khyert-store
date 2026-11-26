<?php

namespace App\Http\Controllers;

use App\Models\AffiliateProfile;
use App\Models\Category;
use App\Models\Destination;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\TourismCategory;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles the public-facing homepage of SAHARA CONNECT.
 *
 * No authentication is required for the home view.
 */
class HomeController extends Controller
{
    /**
     * Display the homepage: hero, featured products, popular categories,
     * the "Discover Southern Morocco" band, testimonials and platform stats.
     */
    public function index(): Renderable
    {
        $featuredProducts = Product::active()
            ->featured()
            ->inStock()
            ->with(['category', 'sellerProfile', 'images'])
            ->take(8)
            ->get();

        // Fall back to the newest active products so the section is never empty.
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::active()
                ->inStock()
                ->with(['category', 'sellerProfile', 'images'])
                ->latest()
                ->take(8)
                ->get();
        }

        $featuredDestinations = Destination::active()
            ->featured()
            ->with('tourismCategories')
            ->take(6)
            ->get();

        if ($featuredDestinations->isEmpty()) {
            $featuredDestinations = Destination::active()
                ->with('tourismCategories')
                ->orderByDesc('views')
                ->take(6)
                ->get();
        }

        $popularCategories = Category::active()
            ->parents()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(8)
            ->get();

        $tourismCategories = TourismCategory::active()
            ->withCount(['destinations' => fn ($q) => $q->active()])
            ->orderBy('sort_order')
            ->take(9)
            ->get();

        $testimonials = Review::with(['user', 'product'])
            ->where('is_approved', true)
            ->whereNotNull('body')
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'products'     => Product::active()->count(),
            'sellers'      => SellerProfile::where('status', 'approved')->count(),
            'affiliates'   => AffiliateProfile::where('status', 'approved')->count(),
            'destinations' => Destination::active()->count(),
        ];

        return view('home', compact(
            'featuredProducts',
            'featuredDestinations',
            'popularCategories',
            'tourismCategories',
            'testimonials',
            'stats'
        ));
    }

    /**
     * Store a newsletter subscription coming from the homepage or footer form.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name'  => ['nullable', 'string', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $validated['email']],
            ['name' => $validated['name'] ?? null]
        );

        if (! $subscriber->wasRecentlyCreated) {
            $subscriber->update(['is_active' => true]);
        }

        return back()->with('success', __('home.newsletter_success'));
    }
}
