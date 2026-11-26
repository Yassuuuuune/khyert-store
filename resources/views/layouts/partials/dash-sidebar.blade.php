@php
    $user = auth()->user();
    $role = $user->role;

    if ($role === 'admin') {
        $pendingSellers    = \App\Models\SellerProfile::where('status', 'pending')->count();
        $pendingAffiliates = \App\Models\AffiliateProfile::where('status', 'pending')->count();
        $pendingProducts   = \App\Models\Product::where('status', 'pending')->count();
        $pendingPayouts    = \App\Models\PayoutRequest::where('status', 'pending')->count();
        $pendingReviews    = \App\Models\Review::where('is_approved', false)->count();
    }

    if ($role === 'seller') {
        $sellerPendingOrders = \App\Models\SellerOrder::where('seller_id', $user->id)->where('status', 'pending')->count();
    }

    if ($role === 'affiliate') {
        $affiliatePendingCommissions = \App\Models\Commission::where('affiliate_id', $user->id)->where('status', 'pending')->count();
    }
@endphp

<aside class="dash-sidebar">
    <a href="{{ route('home') }}" class="dash-brand">
        <i class="bi bi-brightness-high-fill text-warning"></i> SAHARA <span class="text-warning">CONNECT</span>
    </a>

    <div class="px-3 py-3 border-bottom" style="border-color:rgba(255,255,255,.08)!important;">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar-circle">{{ $user->initials }}</span>
            <div class="small">
                <div class="text-white fw-semibold">{{ Str::limit($user->name, 18) }}</div>
                <div class="dash-role">{{ __('roles.' . $role) }}</div>
            </div>
        </div>
    </div>

    <nav class="nav flex-column pb-4">
        @if ($role === 'admin')
            <div class="nav-section">{{ __('admin.nav.overview') }}</div>
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i>{{ __('admin.dashboard') }}</a>

            <div class="nav-section">{{ __('admin.nav.marketplace') }}</div>
            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="bi bi-box-seam"></i>{{ __('admin.products') }}@if($pendingProducts)<span class="badge bg-warning text-dark">{{ $pendingProducts }}</span>@endif</a>
            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i>{{ __('admin.categories') }}</a>
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><i class="bi bi-receipt"></i>{{ __('admin.orders') }}</a>
            <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}"><i class="bi bi-star"></i>{{ __('admin.reviews') }}@if($pendingReviews)<span class="badge bg-warning text-dark">{{ $pendingReviews }}</span>@endif</a>

            <div class="nav-section">{{ __('admin.nav.people') }}</div>
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i>{{ __('admin.users') }}</a>
            <a class="nav-link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}" href="{{ route('admin.sellers.index') }}"><i class="bi bi-shop"></i>{{ __('admin.sellers') }}@if($pendingSellers)<span class="badge bg-warning text-dark">{{ $pendingSellers }}</span>@endif</a>
            <a class="nav-link {{ request()->routeIs('admin.affiliates.*') ? 'active' : '' }}" href="{{ route('admin.affiliates.index') }}"><i class="bi bi-share"></i>{{ __('admin.affiliates') }}@if($pendingAffiliates)<span class="badge bg-warning text-dark">{{ $pendingAffiliates }}</span>@endif</a>

            <div class="nav-section">{{ __('admin.nav.affiliate') }}</div>
            <a class="nav-link {{ request()->routeIs('admin.commissions.*') ? 'active' : '' }}" href="{{ route('admin.commissions.index') }}"><i class="bi bi-cash-coin"></i>{{ __('admin.commissions') }}</a>
            <a class="nav-link {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}" href="{{ route('admin.payouts.index') }}"><i class="bi bi-wallet2"></i>{{ __('admin.payouts') }}@if($pendingPayouts)<span class="badge bg-warning text-dark">{{ $pendingPayouts }}</span>@endif</a>

            <div class="nav-section">{{ __('admin.nav.tourism') }}</div>
            <a class="nav-link {{ request()->routeIs('admin.destinations.*') ? 'active' : '' }}" href="{{ route('admin.destinations.index') }}"><i class="bi bi-geo-alt"></i>{{ __('admin.destinations') }}</a>
            <a class="nav-link {{ request()->routeIs('admin.tourism-categories.*') ? 'active' : '' }}" href="{{ route('admin.tourism-categories.index') }}"><i class="bi bi-signpost-split"></i>{{ __('admin.tourism_categories') }}</a>

            <div class="nav-section">{{ __('admin.nav.system') }}</div>
            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear"></i>{{ __('admin.settings') }}</a>

        @elseif ($role === 'seller')
            <div class="nav-section">{{ __('seller.nav.overview') }}</div>
            <a class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}" href="{{ route('seller.dashboard') }}"><i class="bi bi-speedometer2"></i>{{ __('seller.dashboard') }}</a>

            <div class="nav-section">{{ __('seller.nav.catalog') }}</div>
            <a class="nav-link {{ request()->routeIs('seller.products.*') ? 'active' : '' }}" href="{{ route('seller.products.index') }}"><i class="bi bi-box-seam"></i>{{ __('seller.products') }}</a>
            <a class="nav-link {{ request()->routeIs('seller.products.create') ? 'active' : '' }}" href="{{ route('seller.products.create') }}"><i class="bi bi-plus-circle"></i>{{ __('seller.add_product') }}</a>

            <div class="nav-section">{{ __('seller.nav.sales') }}</div>
            <a class="nav-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}" href="{{ route('seller.orders.index') }}"><i class="bi bi-receipt"></i>{{ __('seller.orders') }}@if($sellerPendingOrders)<span class="badge bg-warning text-dark">{{ $sellerPendingOrders }}</span>@endif</a>

            <div class="nav-section">{{ __('seller.nav.account') }}</div>
            <a class="nav-link {{ request()->routeIs('seller.profile.*') ? 'active' : '' }}" href="{{ route('seller.profile.edit') }}"><i class="bi bi-shop-window"></i>{{ __('seller.shop_profile') }}</a>

        @elseif ($role === 'affiliate')
            <div class="nav-section">{{ __('affiliate.nav.overview') }}</div>
            <a class="nav-link {{ request()->routeIs('affiliate.dashboard') ? 'active' : '' }}" href="{{ route('affiliate.dashboard') }}"><i class="bi bi-speedometer2"></i>{{ __('affiliate.dashboard') }}</a>
            <a class="nav-link {{ request()->routeIs('affiliate.statistics') ? 'active' : '' }}" href="{{ route('affiliate.statistics') }}"><i class="bi bi-graph-up"></i>{{ __('affiliate.statistics') }}</a>

            <div class="nav-section">{{ __('affiliate.nav.promotion') }}</div>
            <a class="nav-link {{ request()->routeIs('affiliate.links') ? 'active' : '' }}" href="{{ route('affiliate.links') }}"><i class="bi bi-link-45deg"></i>{{ __('affiliate.links') }}</a>

            <div class="nav-section">{{ __('affiliate.nav.earnings') }}</div>
            <a class="nav-link {{ request()->routeIs('affiliate.commissions.*') ? 'active' : '' }}" href="{{ route('affiliate.commissions.index') }}"><i class="bi bi-cash-coin"></i>{{ __('affiliate.commissions') }}@if($affiliatePendingCommissions)<span class="badge bg-warning text-dark">{{ $affiliatePendingCommissions }}</span>@endif</a>
            <a class="nav-link {{ request()->routeIs('affiliate.payouts.*') ? 'active' : '' }}" href="{{ route('affiliate.payouts.index') }}"><i class="bi bi-wallet2"></i>{{ __('affiliate.payouts') }}</a>

            <div class="nav-section">{{ __('affiliate.nav.account') }}</div>
            <a class="nav-link {{ request()->routeIs('affiliate.profile.*') ? 'active' : '' }}" href="{{ route('affiliate.profile.edit') }}"><i class="bi bi-person-gear"></i>{{ __('affiliate.profile') }}</a>
        @endif
    </nav>
</aside>
