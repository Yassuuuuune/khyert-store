@extends('layouts.dashboard')

@section('title', __('seller.dashboard'))
@section('page_title', __('seller.dashboard'))

@php
    $statusColors = [
        'pending'    => 'warning',
        'confirmed'  => 'info',
        'processing' => 'primary',
        'shipped'    => 'secondary',
        'delivered'  => 'success',
        'cancelled'  => 'danger',
    ];
@endphp

@section('content')
    {{-- ── Stat cards ─────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-gold"><i class="bi bi-cash-coin"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ number_format($totalRevenue, 2, ',', ' ') }} <span class="fs-6">MAD</span></div>
                        <div class="stat-label">{{ __('seller.total_revenue') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-primary"><i class="bi bi-receipt"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ $totalOrders }}</div>
                        <div class="stat-label">{{ __('seller.total_orders') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-red"><i class="bi bi-hourglass-split"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ $pendingOrders }}</div>
                        <div class="stat-label">{{ __('seller.pending_orders') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-green"><i class="bi bi-graph-up-arrow"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ $totalSales }}</div>
                        <div class="stat-label">{{ __('seller.total_sales') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-brown"><i class="bi bi-box-seam"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ $totalProducts }}</div>
                        <div class="stat-label">{{ __('seller.total_products') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-gold"><i class="bi bi-star-half"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ number_format($averageRating, 1, ',', ' ') }} <span class="fs-6">/ 5</span></div>
                        <div class="stat-label">{{ __('seller.average_rating') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-blue"><i class="bi bi-wallet2"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ number_format($averageOrder, 2, ',', ' ') }} <span class="fs-6">MAD</span></div>
                        <div class="stat-label">{{ __('seller.avg_order') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card {{ $lowStockProducts > 0 ? 'border-danger' : '' }}">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-red"><i class="bi bi-exclamation-triangle"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value">{{ $lowStockProducts }}</div>
                        <div class="stat-label">{{ __('seller.low_stock') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($lowStockProducts > 0)
        <div class="alert alert-warning d-flex align-items-center mb-3">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <div>{{ __('seller.stock_alert', ['count' => $lowStockProducts]) }}</div>
        </div>
    @endif

    {{-- ── Charts ─────────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card p-3 h-100">
                <h2 class="h6 text-brown mb-3">{{ __('seller.revenue_chart_title') }}</h2>
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-3 h-100">
                <h2 class="h6 text-brown mb-3">{{ __('seller.orders_by_status') }}</h2>
                <canvas id="statusChart" height="220"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Recent orders & best sellers ───────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h2 class="h6 mb-0 text-brown">{{ __('seller.recent_orders') }}</h2>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.view_all') }}</a>
                </div>

                @if ($recentOrders->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-receipt"></i>
                        <p class="mt-2 mb-0">{{ __('seller.no_orders') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sc align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('seller.order_number') }}</th>
                                    <th>{{ __('seller.customer') }}</th>
                                    <th>{{ __('seller.date') }}</th>
                                    <th class="text-end">{{ __('seller.subtotal') }}</th>
                                    <th>{{ __('seller.status_label') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('seller.orders.show', $order) }}" class="fw-600 text-terracotta">
                                                {{ $order->order?->order_number ?? ('#' . $order->id) }}
                                            </a>
                                        </td>
                                        <td>{{ $order->order?->shipping_name ?? $order->order?->customer?->name ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $order->created_at?->format('d/m/Y') }}</td>
                                        <td class="text-end">{{ number_format((float) $order->subtotal, 2, ',', ' ') }} MAD</td>
                                        <td>
                                            <span class="badge text-bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ __('status.' . $order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 text-brown">{{ __('seller.top_products') }}</h2>
                </div>

                @if ($bestSellingProducts->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-box-seam"></i>
                        <p class="mt-2 mb-0">{{ __('seller.no_products') }}</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($bestSellingProducts as $product)
                            <li class="list-group-item d-flex align-items-center gap-3">
                                <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}"
                                     class="rounded" style="width:48px;height:48px;object-fit:cover;">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-600 text-brown text-truncate">{{ $product->name }}</div>
                                    <div class="small text-muted">{{ $product->category?->name ?? '—' }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-600 text-terracotta">{{ $product->sales_count }}</div>
                                    <div class="small text-muted">{{ __('seller.sales') }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Affiliate activity ─────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="h6 mb-0 text-brown">{{ __('seller.affiliate_sales') }}</h2>
                <div class="small text-muted">{{ __('seller.affiliate_sales_sub') }}</div>
            </div>
            @if ($commissionRate !== null)
                <span class="chip">
                    <i class="bi bi-percent"></i>
                    {{ __('seller.affiliate_commission_rate') }} : {{ rtrim(rtrim(number_format((float) $commissionRate, 2, ',', ' '), '0'), ',') }}%
                </span>
            @endif
        </div>

        <div class="row g-3 p-3">
            <div class="col-md-6">
                <div class="bg-sand rounded-xl p-3 h-100 d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-primary"><i class="bi bi-cash-stack"></i></span>
                    <div>
                        <div class="stat-value">{{ number_format($affiliateCommissionsTotal, 2, ',', ' ') }} <span class="fs-6">MAD</span></div>
                        <div class="stat-label">{{ __('seller.affiliate_commissions_total') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-sand rounded-xl p-3 h-100 d-flex align-items-center gap-3">
                    <span class="stat-icon stat-icon-blue"><i class="bi bi-share"></i></span>
                    <div>
                        <div class="stat-value">{{ $affiliateConversionsCount }}</div>
                        <div class="stat-label">{{ __('seller.affiliate_conversions') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <h3 class="h6 text-brown mb-2">{{ __('seller.recent_affiliate_conversions') }}</h3>

            @if ($recentCommissions->isEmpty())
                <div class="empty-state py-4">
                    <i class="bi bi-share"></i>
                    <p class="mt-2 mb-0">{{ __('seller.no_affiliate_conversions') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sc align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('seller.date') }}</th>
                                <th>{{ __('seller.product_name') }}</th>
                                <th>{{ __('seller.affiliate') }}</th>
                                <th class="text-end">{{ __('seller.sale_amount') }}</th>
                                <th class="text-end">{{ __('seller.commission_amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentCommissions as $commission)
                                <tr>
                                    <td class="text-nowrap">{{ $commission->created_at?->format('d/m/Y') }}</td>
                                    <td class="text-truncate" style="max-width:220px;">{{ $commission->product?->name ?? '—' }}</td>
                                    <td>{{ $commission->affiliate?->name ?? '—' }}</td>
                                    <td class="text-end">{{ number_format((float) $commission->sale_amount, 2, ',', ' ') }} MAD</td>
                                    <td class="text-end fw-600 text-terracotta">{{ number_format((float) $commission->commission_amount, 2, ',', ' ') }} MAD</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const revenueEl = document.getElementById('revenueChart');
            if (revenueEl) {
                new Chart(revenueEl, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: @json(__('seller.total_revenue')),
                            data: @json($chartData),
                            borderColor: '#C0693F',
                            backgroundColor: 'rgba(192, 105, 63, .12)',
                            fill: true,
                            tension: .35,
                            pointBackgroundColor: '#C0693F',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: (v) => v + ' MAD' } }
                        }
                    }
                });
            }

            const statusEl = document.getElementById('statusChart');
            if (statusEl) {
                new Chart(statusEl, {
                    type: 'doughnut',
                    data: {
                        labels: @json($statusLabels),
                        datasets: [{
                            data: @json($statusData),
                            backgroundColor: ['#D9A441', '#2E7D74', '#C0693F', '#74573F', '#6E7F58', '#A83A34'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        });
    </script>
@endpush
