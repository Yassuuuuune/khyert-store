@extends('layouts.dashboard')

@section('title', __('seller.products'))
@section('page_title', __('seller.products'))

@php
    $productStatusColors = [
        'draft'    => 'secondary',
        'pending'  => 'warning',
        'active'   => 'success',
        'rejected' => 'danger',
        'archived' => 'dark',
    ];
    $statuses = ['draft', 'pending', 'active', 'rejected', 'archived'];
@endphp

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="h5 mb-0 text-brown">{{ __('seller.products') }}</h2>
        <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>{{ __('seller.add_product') }}
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('seller.products.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label small mb-1">{{ __('messages.search') }}</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           class="form-control" placeholder="{{ __('seller.search_product') }}">
                </div>
                <div class="col-8 col-md-3">
                    <label class="form-label small mb-1">{{ __('seller.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('seller.all_statuses') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>
                                {{ __('status.' . $status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>{{ __('messages.filter') }}
                    </button>
                    <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary" title="{{ __('messages.reset') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if ($products->isEmpty())
        <div class="card">
            <div class="empty-state">
                <i class="bi bi-box-seam"></i>
                <p class="mt-2 mb-1 fw-600 text-brown">{{ __('seller.no_products') }}</p>
                <p class="mb-3">{{ __('seller.no_products_hint') }}</p>
                <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('seller.create_first_product') }}
                </a>
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sc align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('seller.product_thumbnail') }}</th>
                            <th>{{ __('seller.product_name') }}</th>
                            <th>{{ __('seller.category') }}</th>
                            <th class="text-end">{{ __('seller.price') }}</th>
                            <th class="text-center">{{ __('seller.stock') }}</th>
                            <th>{{ __('seller.status') }}</th>
                            <th class="text-end">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}"
                                         class="rounded" style="width:52px;height:52px;object-fit:cover;">
                                </td>
                                <td>
                                    <div class="fw-600 text-brown">{{ $product->name }}</div>
                                    @if ($product->sku)
                                        <div class="small text-muted">{{ $product->sku }}</div>
                                    @endif
                                </td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <span class="fw-600 text-terracotta">{{ number_format((float) $product->current_price, 2, ',', ' ') }} MAD</span>
                                    @if ($product->isOnSale())
                                        <div class="small text-muted text-decoration-line-through">
                                            {{ number_format((float) $product->price, 2, ',', ' ') }} MAD
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($product->stock <= 0)
                                        <span class="badge text-bg-danger">{{ __('seller.out_of_stock') }}</span>
                                    @elseif ($product->stock <= 5)
                                        <span class="badge text-bg-warning text-dark">{{ $product->stock }}</span>
                                    @else
                                        <span>{{ $product->stock }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $productStatusColors[$product->status] ?? 'secondary' }}">
                                        {{ __('status.' . $product->status) }}
                                    </span>
                                    @if ($product->status === 'rejected' && $product->rejection_reason)
                                        <div class="small text-danger mt-1" title="{{ $product->rejection_reason }}">
                                            {{ Str::limit($product->rejection_reason, 40) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ __('seller.delete_confirm') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
