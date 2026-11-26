<?php

namespace App\Http\Middleware;

use App\Models\AffiliateClick;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captures affiliate referrals coming from unique referral links (?ref=CODE).
 *
 * The code is stored in both the session and a long-lived cookie so the
 * attribution survives the whole configurable attribution window
 * (config('affiliate.cookie_days')). The key used here — `affiliate_code` —
 * is the single source of truth consumed by the checkout flow.
 */
class TrackAffiliate
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->query('ref');

        if ($code && is_string($code)) {
            $affiliate = User::where('referral_code', $code)->first();

            if ($affiliate
                && $affiliate->isAffiliate()
                && $affiliate->affiliateProfile?->isApproved()) {

                // Never attribute a referral to the affiliate's own browser session.
                if (! auth()->check() || auth()->id() !== $affiliate->id) {

                    $minutes = (int) config('affiliate.cookie_days', 30) * 1440;

                    cookie()->queue(cookie('affiliate_code', $affiliate->referral_code, $minutes));
                    session(['affiliate_code' => $affiliate->referral_code]);

                    $productId = null;
                    $product   = $request->route('product');

                    if ($product instanceof \App\Models\Product) {
                        $productId = $product->id;
                    } elseif (is_string($product)) {
                        $productId = \App\Models\Product::where('slug', $product)->value('id');
                    }

                    AffiliateClick::create([
                        'affiliate_id' => $affiliate->id,
                        'product_id'   => $productId,
                        'ip_address'   => $request->ip(),
                        'user_agent'   => mb_substr((string) $request->userAgent(), 0, 255),
                        'referer'      => mb_substr((string) $request->headers->get('referer'), 0, 255),
                        'landing_page' => mb_substr($request->fullUrl(), 0, 255),
                    ]);

                    $affiliate->affiliateProfile->increment('total_clicks');
                }
            }
        }

        return $next($request);
    }
}
