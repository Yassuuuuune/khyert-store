@extends('layouts.app')

@section('title', __('products.title'))
@section('meta_description', __('products.subtitle'))

@section('content')
@php
    $headerImage = \App\Support\Image::generate(
        'Artisan market stall in southern Morocco filled with handmade pottery, textiles and spices, warm golden light, editorial photography',
        'landscape_16_9'
    );
@endphp

<header class="page-header" style="background-image:url('{{ $headerImage }}')">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('products.breadcrumb_home') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('products.breadcrumb_shop') }}</li>
            </ol>
        </nav>
        <h1 class="mt-2">{{ __('products.title') }}</h1>
        <p class="lead mb-0">{{ __('products.subtitle') }}</p>
    </div>
</header>

<section class="section">
    <div class="container">
        <div class="row g-4">
            {{-- ── Sidebar filters ──────────────────────────────────────── --}}
            <aside class="col-lg-3">
                <form method="GET" action="{{ route('products.index') }}" class="card p-3 p-lg-4 sticky-lg-top" style="top:96px;">
                    <h2 class="h6 text-uppercase text-muted mb-3"><i class="bi bi-funnel me-1"></i>{{ __('products.filters') }}</h2>

                    <div class="mb-3">
                        <label for="filter-q" class="form-label">{{ __('products.search_label') }}</label>
                        <input id="filter-q" type="search" name="q" class="form-control"
                               placeholder="{{ __('products.search_placeholder') }}" value="{{ request('q') }}">
                    </div>

                    <div class="mb-3">
                        <label for="filter-category" class="form-label">{{ __('products.categories') }}</label>
                        <select id="filter-category" name="category" class="form-select">
                            <option value="">{{ __('products.all_categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                    {{ $category->localized_name }} ({{ $category->products_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <span class="form-label d-block">{{ __('products.price_range') }}</span>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="filter-min" class="visually-hidden">{{ __('products.min_price') }}</label>
                                <input id="filter-min" type="number" min="0" step="1" name="min_price" class="form-control"
                                       placeholder="{{ __('products.min_price') }}" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-6">
                                <label for="filter-max" class="visually-hidden">{{ __('products.max_price') }}</label>
                                <input id="filter-max" type="number" min="0" step="1" name="max_price" class="form-control"
                                       placeholder="{{ __('products.max_price') }}" value="{{ request('max_price') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="filter-sort" class="form-label">{{ __('products.sort_by') }}</label>
                        <select id="filter-sort" name="sort" class="form-select">
                            <option value="newest" @selected($sort === 'newest')>{{ __('products.sort_newest') }}</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>{{ __('products.sort_price_asc') }}</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>{{ __('products.sort_price_desc') }}</option>
                            <option value="popular" @selected($sort === 'popular')>{{ __('products.sort_popular') }}</option>
                            <option value="rating" @selected($sort === 'rating')>{{ __('products.sort_rating') }}</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('products.apply_filters') }}</button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('products.reset_filters') }}</a>
                    </div>
                </form>
            </aside>

            {{-- ── Results ──────────────────────────────────────────────── --}}
            <div class="col-lg-9">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <p class="text-muted mb-0">{{ __('products.results', ['count' => $products->total()]) }}</p>
                    @if ($activeCategory)
                        <span class="badge bg-sand text-brown">
                            <i class="bi bi-tag me-1"></i>{{ $activeCategory->localized_name }}
                        </span>
                    @endif
                </div>

                @if ($products->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-search"></i>
                        <h2 class="h5 mt-3">{{ __('products.no_products') }}</h2>
                        <p class="mb-3">{{ __('products.no_products_hint') }}</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">{{ __('products.reset_filters') }}</a>
                    </div>
                @else
                    <div class="row g-3 g-md-4">
                        @foreach ($products as $product)
                            <div class="col-6 col-md-4">
                                <article class="card product-card card-sc">
                                    <div class="product-media">
                                        @if ($product->isOnSale() && $product->discount_percent > 0)
                                            <span class="badge badge-float badge-sale">-{{ $product->discount_percent }}%</span>
                                        @elseif ($product->created_at && $product->created_at->gt(now()->subDays(14)))
                                            <span class="badge badge-float badge-new">{{ __('products.new') }}</span>
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

                                        @if ($product->isInStock())
                                            <span class="small text-success mt-1"><i class="bi bi-check-circle me-1"></i>{{ __('products.in_stock') }}</span>
                                        @else
                                            <span class="small text-danger mt-1"><i class="bi bi-x-circle me-1"></i>{{ __('products.out_of_stock') }}</span>
                                        @endif

                                        <div class="d-flex gap-2 mt-3">
                                            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-dark btn-sm flex-fill">{{ __('products.view_product') }}</a>
                                            <form method="POST" action="{{ route('cart.add', $product) }}" data-cart-add class="flex-fill">
                                                @csrf
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-primary btn-sm w-100" @disabled(! $product->isInStock())>
                                                    <i class="bi bi-bag-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($products->hasPages())
                        <nav class="mt-5" aria-label="{{ __('products.title') }}">
                            <ul class="pagination justify-content-center flex-wrap">
                                <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $products->previousPageUrl() ?? '#' }}" rel="prev">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                                    <li class="page-item {{ $page === $products->currentPage() ? 'active' : '' }}">
                                        @if ($page === $products->currentPage())
                                            <span class="page-link">{{ $page }}</span>
                                        @else
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        @endif
                                    </li>
                                @endforeach
                                <li class="page-item {{ $products->hasMorePages() ? '' : 'disabled' }}">
                                    <a class="page-link" href="{{ $products->nextPageUrl() ?? '#' }}" rel="next">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
