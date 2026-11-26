@extends('layouts.app')

@php
    $gallery = collect([$product->main_image_url]);
    foreach ($product->images as $image) {
        $gallery->push($image->url);
    }
    $gallery = $gallery->unique()->values();
    $seller  = $product->sellerProfile;
    $reviews = $product->reviews;
@endphp

@section('title', $product->localized_name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->localized_description), 155))
@section('og_title', $product->localized_name)
@section('og_image', $product->main_image_url)

@section('content')

<header class="page-header" style="background-image:url('{{ $product->main_image_url }}')">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('products.breadcrumb_home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">{{ __('products.breadcrumb_shop') }}</a></li>
                @if ($product->category)
                    <li class="breadcrumb-item">
                        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->localized_name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($product->localized_name, 40) }}</li>
            </ol>
        </nav>
        <h1 class="mt-2 h2">{{ $product->localized_name }}</h1>
    </div>
</header>

<section class="section">
    <div class="container">
        <div class="row g-5">
            {{-- ── Gallery ─────────────────────────────────────────────── --}}
            <div class="col-lg-6">
                <div class="position-relative">
                    <img data-gallery-main src="{{ $gallery->first() }}" alt="{{ $product->localized_name }}"
                         class="img-fluid rounded-xl w-100 object-cover ratio-4x3" style="aspect-ratio:4/3;">
                    @if ($product->isOnSale() && $product->discount_percent > 0)
                        <span class="badge badge-float badge-sale position-absolute" style="top:.9rem;left:.9rem;">-{{ $product->discount_percent }}%</span>
                    @endif
                </div>

                @if ($gallery->count() > 1)
                    <div class="row g-2 mt-3">
                        @foreach ($gallery as $i => $src)
                            <div class="col-3">
                                <img data-gallery-thumb="{{ $src }}" src="{{ $src }}" alt="{{ $product->localized_name }}"
                                     class="gallery-thumb w-100 {{ $i === 0 ? 'active' : '' }}" loading="lazy">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ── Buy box ─────────────────────────────────────────────── --}}
            <div class="col-lg-6">
                @if ($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="eyebrow">{{ $product->category->localized_name }}</a>
                @endif
                <h2 class="section-title mt-2 mb-2">{{ $product->localized_name }}</h2>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="text-warning">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= round($product->rating_avg) ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </span>
                    <span class="text-muted small">{{ __('products.reviews_count', ['count' => $product->rating_count]) }}</span>
                    @if ($product->isInStock())
                        <span class="badge bg-success-subtle text-success-emphasis">{{ __('products.in_stock') }}</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger-emphasis">{{ __('products.out_of_stock') }}</span>
                    @endif
                </div>

                <div class="d-flex align-items-baseline gap-3 mb-3">
                    <span class="price fs-3">{{ number_format($product->current_price, 2, ',', ' ') }} MAD</span>
                    @if ($product->isOnSale())
                        <span class="price-old fs-6">{{ number_format((float) $product->price, 2, ',', ' ') }} MAD</span>
                        <span class="badge badge-sale">{{ __('products.sale') }} -{{ $product->discount_percent }}%</span>
                    @endif
                </div>

                <p class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($product->localized_description), 220) }}</p>

                @if ($product->isInStock() && $product->stock <= 10)
                    <p class="text-terracotta small"><i class="bi bi-exclamation-circle me-1"></i>{{ __('products.only_left', ['count' => $product->stock]) }}</p>
                @endif

                <form method="POST" action="{{ route('cart.add', $product) }}" data-cart-add class="mt-4">
                    @csrf
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <div class="qty-stepper" data-qty-stepper>
                            <button type="button" data-qty="down" aria-label="-"><i class="bi bi-dash"></i></button>
                            <input type="number" name="quantity" value="1" min="1" max="{{ max(1, $product->stock) }}" inputmode="numeric">
                            <button type="button" data-qty="up" aria-label="+"><i class="bi bi-plus"></i></button>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg" @disabled(! $product->isInStock())>
                            <i class="bi bi-bag-plus me-1"></i>{{ __('products.add_to_cart') }}
                        </button>
                        <button type="button" class="wish-btn position-static" aria-label="{{ __('products.favorite') }}" style="width:48px;height:48px;">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </form>

                {{-- Seller card --}}
                @if ($seller)
                    <div class="card card-sc p-3 mt-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="avatar-circle avatar-lg">{{ $seller->initials }}</span>
                            <div class="flex-fill">
                                <div class="eyebrow">{{ __('products.seller') }}</div>
                                <div class="fw-600 text-brown">{{ $seller->shop_name }}</div>
                                <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $seller->city ?? '—' }}@if ($seller->region), {{ $seller->region }}@endif</div>
                            </div>
                            <a href="{{ route('products.index', ['q' => $seller->shop_name]) }}" class="btn btn-outline-dark btn-sm">
                                {{ __('products.visit_shop') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Affiliate / share box --}}
                <div class="card card-sc p-3 mt-3 bg-sand border-0">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-share-fill text-terracotta fs-5"></i>
                        <div class="flex-fill">
                            <div class="fw-600 text-brown">{{ __('products.affiliate_box_title') }}</div>
                            <p class="small text-muted mb-2">{{ __('products.affiliate_box_text') }}
                                <span class="badge bg-gold text-brown">{{ $product->effective_commission_rate }} %</span>
                            </p>

                            @auth
                                @if (auth()->user()->referral_code)
                                    @php $shareLink = url()->current() . '?ref=' . auth()->user()->referral_code; @endphp
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" value="{{ $shareLink }}" readonly>
                                        <button type="button" class="btn btn-primary" data-copy="{{ $shareLink }}">
                                            <i class="bi bi-clipboard me-1"></i>{{ __('messages.copy') }}
                                        </button>
                                    </div>
                                @endif
                            @endauth

                            <div class="d-flex gap-2 mt-2">
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                                   href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                                   href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($product->localized_name) }}">
                                    <i class="bi bi-twitter-x"></i>
                                </a>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                                   href="https://wa.me/?text={{ urlencode($product->localized_name . ' — ' . url()->current()) }}">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Description & details ─────────────────────────────────── --}}
        <div class="row g-4 mt-5">
            <div class="col-lg-8">
                <h2 class="h4 mb-3">{{ __('products.description') }}</h2>
                <div class="text-brown" style="white-space:pre-line;">{{ $product->localized_description }}</div>
            </div>
            <div class="col-lg-4">
                <div class="card p-3">
                    <h3 class="h6 text-uppercase text-muted mb-3">{{ __('products.details') }}</h3>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">{{ __('products.sku') }}</dt>
                        <dd class="col-7">{{ $product->sku ?: '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('products.category') }}</dt>
                        <dd class="col-7">{{ $product->category?->localized_name ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('products.seller') }}</dt>
                        <dd class="col-7">{{ $seller?->shop_name ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('products.availability') }}</dt>
                        <dd class="col-7">
                            @if ($product->isInStock())
                                <span class="text-success">{{ __('products.in_stock') }}</span>
                            @else
                                <span class="text-danger">{{ __('products.out_of_stock') }}</span>
                            @endif
                        </dd>

                        @if ($product->weight)
                            <dt class="col-5 text-muted fw-normal">{{ __('products.details') }}</dt>
                            <dd class="col-7">{{ $product->weight }} kg</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- ── Reviews ───────────────────────────────────────────────── --}}
        <div class="row g-4 mt-5">
            <div class="col-lg-7">
                <h2 class="h4 mb-3">{{ __('products.reviews_title') }}</h2>

                @forelse ($reviews as $review)
                    <div class="card card-sc p-3 mb-3">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="avatar-circle">{{ $review->user?->initials ?? 'SC' }}</span>
                            <div class="flex-fill">
                                <div class="fw-600 text-brown">{{ $review->user?->name ?? __('site.brand') }}</div>
                                <div class="small text-muted">{{ $review->created_at?->translatedFormat('j F Y') }}</div>
                            </div>
                            @if ($review->is_verified_purchase)
                                <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-patch-check me-1"></i>{{ __('reviews.verified') }}</span>
                            @endif
                        </div>
                        <div class="text-warning small mb-2">{!! $review->stars_html !!}</div>
                        @if ($review->title)
                            <div class="fw-600 text-brown">{{ $review->title }}</div>
                        @endif
                        @if ($review->body)
                            <p class="mb-0 text-muted">{{ $review->body }}</p>
                        @endif
                    </div>
                @empty
                    <div class="empty-state py-4">
                        <i class="bi bi-chat-square-text"></i>
                        <p class="mt-3 mb-0">{{ __('products.no_reviews') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="col-lg-5">
                <div class="card p-4">
                    <h3 class="h5 mb-3">{{ __('reviews.review_form_title') }}</h3>

                    @auth
                        <form method="POST" action="{{ route('reviews.store', $product) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="review-rating" class="form-label">{{ __('reviews.your_rating') }}</label>
                                <select id="review-rating" name="rating" class="form-select @error('rating') is-invalid @enderror" required>
                                    <option value="">{{ __('reviews.select_rating') }}</option>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }} — {{ __('reviews.star_'.$i) }}</option>
                                    @endfor
                                </select>
                                @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label for="review-title" class="form-label">{{ __('reviews.title_label') }}</label>
                                <input id="review-title" type="text" name="title" class="form-control" value="{{ old('title') }}" maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label for="review-body" class="form-label">{{ __('reviews.body_label') }}</label>
                                <textarea id="review-body" name="body" rows="4" class="form-control" maxlength="1000">{{ old('body') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-send me-1"></i>{{ __('reviews.submit') }}
                            </button>
                        </form>
                    @else
                        <p class="text-muted">{{ __('products.login_to_review') }}</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-dark w-100">{{ __('nav.login') }}</a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- ── Related products ──────────────────────────────────────── --}}
        @if ($relatedProducts->isNotEmpty())
            <div class="mt-5">
                <h2 class="h4 mb-4">{{ __('products.related_products') }}</h2>
                <div class="row g-3 g-md-4">
                    @foreach ($relatedProducts as $related)
                        <div class="col-6 col-md-4 col-lg-3">
                            <article class="card product-card card-sc">
                                <div class="product-media">
                                    <a href="{{ route('products.show', $related) }}">
                                        <img src="{{ $related->main_image_url }}" alt="{{ $related->localized_name }}" loading="lazy">
                                    </a>
                                </div>
                                <div class="product-body d-flex flex-column">
                                    <a href="{{ route('products.show', $related) }}" class="product-title mb-2">{{ $related->localized_name }}</a>
                                    <span class="price mt-auto">{{ number_format($related->current_price, 2, ',', ' ') }} MAD</span>
                                    <form method="POST" action="{{ route('cart.add', $related) }}" data-cart-add class="mt-2">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-outline-dark btn-sm w-100">
                                            <i class="bi bi-bag-plus me-1"></i>{{ __('products.add_to_cart') }}
                                        </button>
                                    </form>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
