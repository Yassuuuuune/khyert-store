<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles product reviews for SAHARA CONNECT.
 *
 * Authentication is required. The controller checks whether the
 * user has already reviewed the product and whether they qualify
 * as a verified purchaser (i.e. they have a delivered order
 * containing the product).
 */
class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new product review.
     *
     * Business rules enforced:
     *  - Each user may only submit one review per product.
     *  - `is_verified_purchase` is set to true when the user has a
     *    delivered order that contains the product being reviewed.
     *  - After saving, the product's cached `rating_avg` and `rating_count`
     *    are recalculated from approved reviews.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Validate input
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title'  => ['nullable', 'string', 'max:100'],
            'body'   => ['nullable', 'string', 'max:1000'],
        ]);

        // Guard: prevent duplicate reviews
        $alreadyReviewed = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', __('reviews.already_reviewed'));
        }

        // Determine verified purchase status:
        // The user must have an order with status 'delivered' that contains this product.
        $verifiedOrderItem = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('customer_id', $user->id)
                  ->where('status', 'delivered');
            })
            ->first();

        $isVerifiedPurchase = $verifiedOrderItem !== null;

        // Find the qualifying order ID for the review (nullable if not verified)
        $orderId = $verifiedOrderItem?->order_id;

        // Create the review (pending approval by default)
        Review::create([
            'product_id'          => $product->id,
            'user_id'             => $user->id,
            'order_id'            => $orderId,
            'rating'              => $validated['rating'],
            'title'               => $validated['title'] ?? null,
            'body'                => $validated['body'] ?? null,
            'is_approved'         => false,       // admin moderation required
            'is_verified_purchase'=> $isVerifiedPurchase,
        ]);

        // Recalculate the product's aggregate rating from ALL approved reviews
        $this->updateProductRating($product);

        return back()->with('success', __('reviews.submitted_pending_approval'));
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    /**
     * Recalculate and persist `rating_avg` and `rating_count` on the product
     * based on all approved reviews.
     */
    private function updateProductRating(Product $product): void
    {
        $stats = Review::where('product_id', $product->id)
            ->where('is_approved', true)
            ->selectRaw('COUNT(*) as count, AVG(rating) as avg')
            ->first();

        $product->update([
            'rating_count' => (int) ($stats->count ?? 0),
            'rating_avg'   => round((float) ($stats->avg ?? 0), 2),
        ]);
    }
}
