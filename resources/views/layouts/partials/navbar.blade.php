@php
    $locale = app()->getLocale();
    $locales = ['fr' => 'Français', 'ar' => 'العربية', 'en' => 'English'];
@endphp

<nav class="navbar navbar-expand-lg navbar-sc sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <span class="kh-logo" aria-hidden="true"><i class="bi bi-bag"></i><span class="kh-dune"></span></span>
            <span class="kh-brand-copy">
                <span class="kh-brand-name">KHYERT STORE</span>
                <small>DISCOVER / SHOP / EXPLORE</small>
            </span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#scNavbar" aria-controls="scNavbar" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="scNavbar">
            <form method="GET" action="{{ route('products.search') }}" class="kh-nav-search" role="search">
                <label for="kh-nav-q" class="visually-hidden">{{ __('home.search_label') }}</label>
                <input id="kh-nav-q" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('home.hero_search_placeholder') }}">
                <label for="kh-nav-category" class="visually-hidden">{{ __('home.all_categories') }}</label>
                <select id="kh-nav-category" name="category" aria-label="{{ __('home.all_categories') }}">
                    <option value="">{{ __('home.all_categories') }}</option>
                    @foreach (($popularCategories ?? collect()) as $category)
                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->localized_name }}</option>
                    @endforeach
                </select>
                <button type="submit" aria-label="{{ __('home.search_label') }}"><i class="bi bi-search"></i></button>
            </form>
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('nav.home') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">{{ __('nav.shop') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tourism.*') ? 'active' : '' }}" href="{{ route('tourism.index') }}">{{ __('nav.tourism') }}</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">{{ __('nav.discover') }}</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('tourism.map') }}"><i class="bi bi-map me-2"></i>{{ __('nav.map') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('pages.about') }}"><i class="bi bi-info-circle me-2"></i>{{ __('nav.about') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('pages.become-seller') }}"><i class="bi bi-shop me-2"></i>{{ __('nav.become_seller') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('pages.become-affiliate') }}"><i class="bi bi-share me-2"></i>{{ __('nav.become_affiliate') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('pages.contact') }}"><i class="bi bi-envelope me-2"></i>{{ __('nav.contact') }}</a></li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Language switcher --}}
                <div class="dropdown lang-switch">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-translate me-1"></i>{{ strtoupper($locale) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach ($locales as $code => $label)
                            <li>
                                <form method="POST" action="{{ route('language.switch') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $code }}">
                                    <button type="submit" class="dropdown-item {{ $locale === $code ? 'active' : '' }}">{{ $label }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Cart --}}
                <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary position-relative" aria-label="{{ __('nav.cart') }}">
                    <i class="bi bi-bag"></i>
                    <span class="badge bg-primary cart-badge {{ ($cartCount ?? 0) > 0 ? '' : 'd-none' }}" data-cart-count>{{ $cartCount ?? 0 }}</span>
                </a>

                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle object-cover" width="26" height="26" alt="{{ auth()->user()->name }}">
                            @else
                                <span class="avatar-circle" style="width:26px;height:26px;font-size:.7rem;">{{ auth()->user()->initials }}</span>
                            @endif
                            <span class="d-none d-lg-inline">{{ Str::limit(auth()->user()->name, 14) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2">
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ auth()->user()->email }}</div>
                                <span class="badge bg-secondary mt-1">{{ __('roles.' . auth()->user()->role) }}</span>
                            </li>
                            <li><hr class="dropdown-divider"></li>

                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>{{ __('nav.admin_dashboard') }}</a></li>
                            @elseif (auth()->user()->isSeller())
                                <li><a class="dropdown-item" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop-window me-2"></i>{{ __('nav.seller_dashboard') }}</a></li>
                            @elseif (auth()->user()->isAffiliate())
                                <li><a class="dropdown-item" href="{{ route('affiliate.dashboard') }}"><i class="bi bi-share me-2"></i>{{ __('nav.affiliate_dashboard') }}</a></li>
                            @endif

                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-box-seam me-2"></i>{{ __('nav.my_orders') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>{{ __('nav.profile') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('nav-logout').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('nav.logout') }}
                                </a>
                                <form id="nav-logout" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-dark">{{ __('nav.login') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">{{ __('nav.register') }}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
