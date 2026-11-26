<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles the public product catalogue for SAHARA CONNECT.
 *
 * Supports filtering by category slug, price range, full-text search,
 * and multiple sort options. Products are paginated in pages of 16.
 */
class ProductController extends Controller
{
    /**
     * Display a filtered, paginated list of active, in-stock products.
     */
    public function index(Request $request): Renderable
    {
        $query = Product::active()
            ->inStock()
            ->with(['category', 'sellerProfile', 'images']);

        // ── Filter: category slug ────────────────────────────────────────────────
        if ($request->filled('category')) {
            $query->whereHas('category', function (Builder $q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // ── Filter: price range ──────────────────────────────────────────────────
        if ($request->filled('min_price')) {
            $query->where(function (Builder $q) use ($request) {
                $minPrice = (float) $request->input('min_price');
                // Compare against sale_price when present, otherwise price
                $q->where(function (Builder $inner) use ($minPrice) {
                    $inner->whereNotNull('sale_price')
                          ->where('sale_price', '>=', $minPrice);
                })->orWhere(function (Builder $inner) use ($minPrice) {
                    $inner->whereNull('sale_price')
                          ->where('price', '>=', $minPrice);
                });
            });
        }

        if ($request->filled('max_price')) {
            $query->where(function (Builder $q) use ($request) {
                $maxPrice = (float) $request->input('max_price');
                $q->where(function (Builder $inner) use ($maxPrice) {
                    $inner->whereNotNull('sale_price')
                          ->where('sale_price', '<=', $maxPrice);
                })->orWhere(function (Builder $inner) use ($maxPrice) {
                    $inner->whereNull('sale_price')
                          ->where('price', '<=', $maxPrice);
                });
            });
        }

        // ── Filter: full-text search ─────────────────────────────────────────────
        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('name_ar', 'like', $term)
                  ->orWhere('name_en', 'like', $term)
                  ->orWhere('description', 'like', $term);
            });
        }

        // ── Sorting ──────────────────────────────────────────────────────────────
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            'popular'    => $query->orderByDesc('sales_count'),
            'rating'     => $query->orderByDesc('rating_avg'),
            default      => $query->orderByDesc('created_at'),   // 'newest'
        };

        $products = $query->paginate(16)->withQueryString();

        // Sidebar: active parent categories with product counts
        $categories = Category::active()
            ->parents()
            ->withCount(['products' => fn (Builder $q) => $q->active()->inStock()])
            ->orderByDesc('products_count')
            ->get();

        // Current active category for sidebar highlighting
        $activeCategory = null;
        if ($request->filled('category')) {
            $activeCategory = Category::where('slug', $request->input('category'))->first();
        }

        return view('products.index', compact('products', 'categories', 'activeCategory', 'sort'));
    }

    /**
     * Display a single product's detail page.
     *
     * Increments the product's view counter, loads related data,
     * and fetches up to 4 related products from the same category.
     */
    public function show(Product $product): Renderable
    {
        // Abort if product is not active
        abort_unless($product->status === 'active', 404);

        // Increment view counter without touching the timestamps
        $product->increment('views');

        // Eager-load relationships needed on the detail page
        $product->load([
            'category',
            'sellerProfile.user',
            'images',
            'reviews.user',
        ]);

        // Related products from the same category
        $relatedProducts = Product::active()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'images'])
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Redirect a search query to the product listing with the 'q' parameter.
     */
    public function search(Request $request): RedirectResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        return redirect()->route('products.index', ['q' => $request->input('q')]);
    }
}
