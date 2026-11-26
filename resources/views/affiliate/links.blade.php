@extends('layouts.dashboard')

@section('title', __('affiliate.links'))
@section('page_title', __('affiliate.links'))

@section('content')
    {{-- ── Referral kit ─────────────────────────────────────────────────── --}}
    <div class="cta-panel cta-affiliate">
        <div class="position-relative">
            <h2 class="h4 mb-3 font-display">{{ __('affiliate.share_title') }}</h2>
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label text-white-50 small text-uppercase">{{ __('affiliate.your_referral_code') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace fw-bold" value="{{ $referralCode }}" readonly>
                        <button type="button" class="btn btn-light" data-copy="{{ $referralCode }}">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div class="col-lg-8">
                    <label class="form-label text-white-50 small text-uppercase">{{ __('affiliate.your_referral_link') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $referralLink }}" readonly>
                        <button type="button" class="btn btn-light" data-copy="{{ $referralLink }}">
                            <i class="bi bi-clipboard me-1"></i>{{ __('affiliate.copy_link') }}
                        </button>
                    </div>
                </div>
            </div>

            @if ($profile?->commission_rate)
                <p class="mb-0 mt-3 small opacity-75">
                    <i class="bi bi-percent me-1"></i>{{ __('affiliate.your_commission_rate') }} : {{ number_format((float) $profile->commission_rate, 2, ',', ' ') }} %
                </p>
            @endif
        </div>
    </div>

    {{-- ── Search + eligible products ───────────────────────────────────── --}}
    <div class="card card-sc border-0 mt-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h3 class="h6 text-brown mb-0">{{ __('affiliate.eligible_products') }}</h3>
                    <p class="small text-muted mb-0">{{ __('affiliate.eligible_products_sub') }}</p>
                </div>
                <form method="GET" action="{{ route('affiliate.links') }}" class="d-flex gap-2">
                    <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm"
                           placeholder="{{ __('affiliate.search_placeholder') }}" style="min-width:220px;">
                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                        <i class="bi bi-search me-1"></i>{{ __('affiliate.search') }}
                    </button>
                    @if ($search !== '')
                        <a href="{{ route('affiliate.links') }}" class="btn btn-sm btn-outline-secondary text-nowrap">{{ __('affiliate.reset') }}</a>
                    @endif
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-box-seam d-block mb-2"></i>
                    <p class="mb-1 fw-600">{{ __('affiliate.no_products') }}</p>
                    <p class="mb-0 small">{{ __('affiliate.no_products_hint') }}</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach ($products as $product)
                        <div class="col-md-6 col-xl-4">
                            <div class="card card-sc border-0 h-100">
                                <img src="{{ $product->main_image_url }}" alt="{{ $product->localized_name }}"
                                     class="card-img-top object-cover rounded-top" style="height:160px;">
                                <div class="card-body d-flex flex-column">
                                    <h4 class="h6 text-brown mb-1">{{ Str::limit($product->localized_name, 50) }}</h4>

                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="chip bg-sand text-brown">
                                            {{ number_format((float) $product->current_price, 2, ',', ' ') }} MAD
                                        </span>
                                        <span class="chip bg-sand text-terracotta">
                                            <i class="bi bi-percent"></i> {{ number_format((float) $product->referral_rate, 2, ',', ' ') }} %
                                        </span>
                                        <span class="chip bg-sand text-brown">
                                            {{ number_format((float) $product->referral_amount, 2, ',', ' ') }} MAD
                                        </span>
                                    </div>

                                    <div class="input-group input-group-sm mt-auto">
                                        <input type="text" class="form-control" value="{{ $product->referral_link }}" readonly>
                                        <button type="button" class="btn btn-outline-secondary" data-copy="{{ $product->referral_link }}">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
