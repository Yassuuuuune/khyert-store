@extends('layouts.app')

@section('title', __('site.tagline'))
@section('meta_description', __('site.meta_default'))
@section('body_class', 'page-home')

@section('content')
@php
    $heroImage = \App\Support\Image::generate(
        'Panoramic golden-hour photograph of the southern Moroccan Sahara: rolling dunes, palm oasis and a traditional mud-brick kasbah, warm cinematic light',
        'landscape_16_9'
    );
    $bandImage = \App\Support\Image::generate(
        'Wide editorial travel photograph of southern Morocco: Atlantic coast near Mirleft, dramatic desert cliffs and turquoise ocean at sunset',
        'landscape_16_9'
    );
@endphp

{{-- ── 1. HERO ──────────────────────────────────────────────────────────── --}}
<section class="hero" style="background-image:url('{{ $heroImage }}')">
    <div class="container hero-inner py-5">
        <div class="row">
            <div class="col-lg-9">
                <span class="hero-badge reveal"><i class="bi bi-brightness-high-fill"></i> {{ __('home.hero_badge') }}</span>
                <h1 class="mt-3 reveal">{{ __('home.hero_title') }}</h1>
                <p class="lead mt-3 reveal">{{ __('home.hero_subtitle') }}</p>

                <form method="GET" action="{{ route('products.search') }}" class="hero-search mt-4 reveal" role="search">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md">
                            <label for="hero-q" class="visually-hidden">{{ __('home.search_label') }}</label>
                            <input id="hero-q" type="search" name="q" class="form-control form-control-lg border-0"
                                   placeholder="{{ __('home.hero_search_placeholder') }}" value="{{ request('q') }}">
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="hero-category" class="visually-hidden">{{ __('home.all_categories') }}</label>
                            <select id="hero-category" name="category" class="form-select form-select-lg border-0">
                                <option value="">{{ __('home.all_categories') }}</option>
                                @foreach ($popularCategories as $category)
                                    <option value="{{ $category->slug }}">{{ $category->localized_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-search me-1"></i>{{ __('home.hero_search_btn') }}
                            </button>
                        </div>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-3 mt-4 reveal">
                    <a href="{{ route('products.index') }}" class="btn btn-gold btn-lg">
                        <i class="bi bi-bag-heart me-1"></i>{{ __('home.hero_cta_shop') }}
                    </a>
                    <a href="{{ route('tourism.index') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-compass me-1"></i>{{ __('home.hero_cta_tourism') }}
                    </a>
                </div>

                <div class="row g-4 mt-5 reveal">
                    <div class="col-6 col-lg-3">
                        <div class="hero-stat">
                            <div class="num">{{ number_format($stats['products'], 0, ',', ' ') }}</div>
                            <div class="lbl">{{ __('home.stat_products') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="hero-stat">
                            <div class="num">{{ number_format($stats['sellers'], 0, ',', ' ') }}</div>
                            <div class="lbl">{{ __('home.stat_sellers') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="hero-stat">
                            <div class="num">{{ number_format($stats['affiliates'], 0, ',', ' ') }}</div>
                            <div class="lbl">{{ __('home.stat_affiliates') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="hero-stat">
                            <div class="num">{{ number_format($stats['destinations'], 0, ',', ' ') }}</div>
                            <div class="lbl">{{ __('home.stat_destinations') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── 2. FEATURED PRODUCTS ─────────────────────────────────────────────── --}}
{{-- Reference inspired service shortcuts and destination ribbon --}}
<section class="home-shortcuts" aria-label="{{ __('home.discover_title') }}">
    <div class="container">
        <div class="shortcut-grid">
            <a class="shortcut-card shortcut-market" href="{{ route('products.index') }}">
                <span class="shortcut-icon"><i class="bi bi-cart3"></i></span>
                <span class="shortcut-copy"><strong>{{ __('nav.shop') }}</strong><small>{{ __('home.featured_products_sub') }}</small><b>{{ __('home.hero_cta_shop') }} <i class="bi bi-arrow-right"></i></b></span>
            </a>
            <a class="shortcut-card shortcut-tourism" href="{{ route('tourism.index') }}">
                <span class="shortcut-icon"><i class="bi bi-airplane"></i></span>
                <span class="shortcut-copy"><strong>{{ __('nav.tourism') }}</strong><small>{{ __('home.featured_destinations_sub') }}</small><b>{{ __('home.hero_cta_tourism') }} <i class="bi bi-arrow-right"></i></b></span>
            </a>
            <a class="shortcut-card shortcut-affiliate" href="{{ route('pages.become-affiliate') }}">
                <span class="shortcut-icon"><i class="bi bi-people"></i></span>
                <span class="shortcut-copy"><strong>{{ __('nav.become_affiliate') }}</strong><small>{{ __('home.become_affiliate_text') }}</small><b>{{ __('home.become_affiliate_cta') }} <i class="bi bi-arrow-right"></i></b></span>
            </a>
            <a class="shortcut-card shortcut-experience" href="{{ route('pages.become-seller') }}">
                <span class="shortcut-icon"><i class="bi bi-star"></i></span>
                <span class="shortcut-copy"><strong>{{ __('nav.become_seller') }}</strong><small>{{ __('home.become_seller_text') }}</small><b>{{ __('home.become_seller_cta') }} <i class="bi bi-arrow-right"></i></b></span>
            </a>
        </div>
        @if ($featuredDestinations->isNotEmpty())
            <nav class="region-ribbon" aria-label="{{ __('home.featured_destinations') }}">
                <span class="region-label"><i class="bi bi-geo-alt-fill"></i> {{ __('home.featured_destinations') }}</span>
                @foreach ($featuredDestinations->take(5) as $destination)
                    <a class="region-pill" href="{{ route('tourism.show', $destination) }}"><span class="region-dot"><i class="bi bi-sun"></i></span>{{ $destination->localized_name }}</a>
                @endforeach
                <a class="region-more" href="{{ route('tourism.index') }}">{{ __('home.see_all_destinations') }} <i class="bi bi-arrow-right"></i></a>
            </nav>
        @endif
    </div>
</section>

<section class="section reveal">
    <div class="container">
        <div class="row align-items-end mb-4">
            <div class="col-lg-8">
                <span class="eyebrow">{{ __('nav.shop') }}</span>
                <h2 class="section-title mb-2">{{ __('home.featured_products') }}</h2>
                <div class="divider-gold mb-3"></div>
                <p class="section-subtitle mb-0">{{ __('home.featured_products_sub') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('products.index') }}" class="btn btn-outline-dark">
                    {{ __('home.see_all_products') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        @if ($featuredProducts->isEmpty())
            <div class="empty-state">
                <i class="bi bi-box-seam"></i>
                <p class="mt-3 mb-3">{{ __('home.no_featured_products') }}</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">{{ __('home.hero_cta_shop') }}</a>
            </div>
        @else
            <div class="row g-3 g-md-4">
                @foreach ($featuredProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <article class="card product-card card-sc">
                            <div class="product-media">
                                @if ($product->isOnSale() && $product->discount_percent > 0)
                                    <span class="badge badge-float badge-sale">-{{ $product->discount_percent }}%</span>
                                @elseif ($product->is_featured)
                                    <span class="badge badge-float badge-featured">{{ __('products.featured') }}</span>
                                @endif
                                <a href="{{ route('products.show', $product) }}">
                                    <img src="{{ $product->main_image_url }}" alt="{{ $product->localized_name }}" loading="lazy">
                                </a>
                                <button type="button" class="wish-btn" aria-label="{{ __('products.favorite') }}">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                            <div class="product-body d-flex flex-column">
                                @if ($product->category)
                                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="eyebrow mb-1">{{ $product->category->localized_name }}</a>
                                @endif
                                <a href="{{ route('products.show', $product) }}" class="product-title mb-1">{{ $product->localized_name }}</a>

                                @if ($product->rating_count > 0)
                                    <div class="small text-warning mb-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= round($product->rating_avg) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                        <span class="text-muted">({{ $product->rating_count }})</span>
                                    </div>
                                @endif

                                <div class="d-flex align-items-baseline gap-2 mt-auto">
                                    <span class="price">{{ number_format($product->current_price, 2, ',', ' ') }} MAD</span>
                                    @if ($product->isOnSale())
                                        <span class="price-old">{{ number_format((float) $product->price, 2, ',', ' ') }} MAD</span>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('cart.add', $product) }}" data-cart-add class="mt-3">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-bag-plus me-1"></i>{{ __('products.add_to_cart') }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ── 3. POPULAR CATEGORIES ────────────────────────────────────────────── --}}
@if ($popularCategories->isNotEmpty())
<section class="section-sm bg-sand reveal">
    <div class="container">
        <div class="text-center mb-4">
            <span class="eyebrow">{{ __('nav.shop') }}</span>
            <h2 class="section-title mb-2">{{ __('home.popular_categories') }}</h2>
            <div class="divider-gold mx-auto mb-3"></div>
            <p class="section-subtitle mx-auto">{{ __('home.popular_categories_sub') }}</p>
        </div>

        <div class="row g-3">
            @foreach ($popularCategories as $category)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="category-tile">
                        <span class="cat-icon">
                            <i class="bi bi-{{ $category->icon ?: 'basket' }}"></i>
                        </span>
                        <span class="cat-name d-block">{{ $category->localized_name }}</span>
                        <span class="cat-count">{{ __('home.products_count', ['count' => $category->products_count]) }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── 4. DISCOVER BAND ─────────────────────────────────────────────────── --}}
<section class="band-dark reveal">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow text-gold">{{ __('nav.tourism') }}</span>
                <h2 class="section-title mt-2 mb-3 text-white">{{ __('home.discover_title') }}</h2>
                <p class="mb-4">{{ __('home.discover_sub') }}</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('tourism.index') }}" class="btn btn-gold btn-lg">
                        <i class="bi bi-compass me-1"></i>{{ __('home.hero_cta_tourism') }}
                    </a>
                    <a href="{{ route('tourism.map') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-map me-1"></i>{{ __('nav.map') }}
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="{{ $bandImage }}" alt="{{ __('home.discover_title') }}" class="img-fluid rounded-xl object-cover w-100 hover-lift" loading="lazy">
            </div>
        </div>
    </div>
</section>

{{-- ── 5. FEATURED DESTINATIONS ─────────────────────────────────────────── --}}
<section class="section reveal">
    <div class="container">
        <div class="row align-items-end mb-4">
            <div class="col-lg-8">
                <span class="eyebrow">{{ __('nav.tourism') }}</span>
                <h2 class="section-title mb-2">{{ __('home.featured_destinations') }}</h2>
                <div class="divider-gold mb-3"></div>
                <p class="section-subtitle mb-0">{{ __('home.featured_destinations_sub') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('tourism.index') }}" class="btn btn-outline-dark">
                    {{ __('home.see_all_destinations') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        @if ($featuredDestinations->isEmpty())
            <div class="empty-state">
                <i class="bi bi-geo-alt"></i>
                <p class="mt-3 mb-3">{{ __('home.no_featured_destinations') }}</p>
                <a href="{{ route('tourism.index') }}" class="btn btn-primary">{{ __('home.hero_cta_tourism') }}</a>
            </div>
        @else
            <div class="row g-3 g-md-4">
                @foreach ($featuredDestinations as $destination)
                    <div class="col-6 col-md-4 col-lg-4">
                        <a href="{{ route('tourism.show', $destination) }}" class="destination-card d-block">
                            <div class="dest-media">
                                <img src="{{ $destination->main_image_url }}" alt="{{ $destination->localized_name }}" loading="lazy">
                                <div class="dest-chips">
                                    @foreach ($destination->tourismCategories->take(2) as $tcat)
                                        <span class="chip"><i class="bi bi-tag"></i>{{ $tcat->name }}</span>
                                    @endforeach
                                </div>
                                <div class="dest-overlay">
                                    <h3 class="dest-title">{{ $destination->localized_name }}</h3>
                                    <div class="dest-meta">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $destination->city }}@if ($destination->region), {{ $destination->region }}@endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ── 6. BECOME A SELLER ───────────────────────────────────────────────── --}}
<section class="section-sm reveal">
    <div class="container">
        <div class="cta-panel">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="eyebrow text-gold">{{ __('nav.become_seller') }}</span>
                    <h2 class="mt-2 mb-3">{{ __('home.become_seller_title') }}</h2>
                    <p class="mb-4">{{ __('home.become_seller_text') }}</p>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i>{{ __('home.become_seller_b1') }}</li>
                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i>{{ __('home.become_seller_b2') }}</li>
                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i>{{ __('home.become_seller_b3') }}</li>
                    </ul>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('pages.become-seller') }}" class="btn btn-gold btn-lg">
                        <i class="bi bi-shop me-1"></i>{{ __('home.become_seller_cta') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── 7. BECOME AN AFFILIATE ───────────────────────────────────────────── --}}
<section class="section-sm reveal">
    <div class="container">
        <div class="cta-panel cta-affiliate">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="eyebrow text-gold">{{ __('nav.become_affiliate') }}</span>
                    <h2 class="mt-2 mb-3">{{ __('home.become_affiliate_title') }}</h2>
                    <p class="mb-4">{{ __('home.become_affiliate_text') }}</p>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i>{{ __('home.become_affiliate_b1') }}</li>
                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i>{{ __('home.become_affiliate_b2') }}</li>
                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i>{{ __('home.become_affiliate_b3') }}</li>
                    </ul>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('pages.become-affiliate') }}" class="btn btn-gold btn-lg">
                        <i class="bi bi-share me-1"></i>{{ __('home.become_affiliate_cta') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── 8. WHY CHOOSE ────────────────────────────────────────────────────── --}}
<section class="section bg-sand reveal">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow">{{ __('nav.about') }}</span>
            <h2 class="section-title mb-2">{{ __('home.why_title') }}</h2>
            <div class="divider-gold mx-auto mb-3"></div>
            <p class="section-subtitle mx-auto">{{ __('home.why_sub') }}</p>
        </div>

        <div class="row g-4">
            @foreach ([1 => 'geo-alt', 2 => 'cash-coin', 3 => 'patch-check', 4 => 'compass'] as $i => $icon)
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-sc h-100 p-4 border-0">
                        <span class="feature-icon mb-3"><i class="bi bi-{{ $icon }}"></i></span>
                        <h3 class="h5 mb-2">{{ __('home.why_'.$i.'_title') }}</h3>
                        <p class="text-muted mb-0">{{ __('home.why_'.$i.'_text') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── 9. TESTIMONIALS ──────────────────────────────────────────────────── --}}
@if ($testimonials->isNotEmpty())
<section class="section reveal">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow">{{ __('nav.about') }}</span>
            <h2 class="section-title mb-2">{{ __('home.testimonials_title') }}</h2>
            <div class="divider-gold mx-auto mb-3"></div>
            <p class="section-subtitle mx-auto">{{ __('home.testimonials_sub') }}</p>
        </div>

        <div class="row g-4">
            @foreach ($testimonials as $testimonial)
                <div class="col-12 col-md-6 col-lg-4">
                    <figure class="testimonial-card mb-0">
                        <div class="quote-mark">&ldquo;</div>
                        <div class="text-warning small mb-2">{!! $testimonial->stars_html !!}</div>
                        <blockquote class="mb-3">
                            <p class="mb-0">{{ \Illuminate\Support\Str::limit($testimonial->body, 180) }}</p>
                        </blockquote>
                        <figcaption class="d-flex align-items-center gap-3">
                            <span class="avatar-circle">{{ $testimonial->user?->initials ?? 'SC' }}</span>
                            <div>
                                <div class="fw-semibold text-brown">{{ $testimonial->user?->name ?? __('site.brand') }}</div>
                                @if ($testimonial->product)
                                    <div class="small text-muted">{{ __('home.testimonial_product', ['product' => $testimonial->product->localized_name]) }}</div>
                                @endif
                            </div>
                        </figcaption>
                    </figure>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── 10. NEWSLETTER ───────────────────────────────────────────────────── --}}
<section class="section-sm reveal">
    <div class="container">
        <div class="newsletter-band">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="eyebrow">{{ __('footer.newsletter') }}</span>
                    <h2 class="section-title mb-2">{{ __('home.newsletter_title') }}</h2>
                    <p class="mb-0">{{ __('home.newsletter_text') }}</p>
                </div>
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('newsletter.subscribe') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label for="newsletter-name" class="visually-hidden">{{ __('home.newsletter_name') }}</label>
                                <input id="newsletter-name" type="text" name="name" class="form-control"
                                       placeholder="{{ __('home.newsletter_name') }}" value="{{ old('name') }}">
                            </div>
                            <div class="col-sm-6">
                                <label for="newsletter-email" class="visually-hidden">{{ __('home.newsletter_email') }}</label>
                                <input id="newsletter-email" type="email" name="email" class="form-control"
                                       placeholder="{{ __('home.newsletter_email') }}" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-envelope-paper me-1"></i>{{ __('home.newsletter_cta') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
