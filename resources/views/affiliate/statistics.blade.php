@extends('layouts.dashboard')

@section('title', __('affiliate.statistics'))
@section('page_title', __('affiliate.statistics'))

@section('content')
    <p class="text-muted">{{ __('affiliate.statistics_sub') }}</p>

    {{-- ── Engagement over time ─────────────────────────────────────────── --}}
    <div class="card card-sc border-0">
        <div class="card-body">
            <h3 class="h6 text-brown mb-1">{{ __('affiliate.engagement_title') }}</h3>
            <p class="small text-muted">{{ __('affiliate.engagement_sub') }}</p>
            <canvas id="engagementChart" height="110"></canvas>
        </div>
    </div>

    {{-- ── Best period ──────────────────────────────────────────────────── --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-4">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ $bestPeriod ?? '—' }}</div>
                        <div class="stat-label mt-1">{{ __('affiliate.best_period') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-gold"><i class="bi bi-trophy"></i></div>
                </div>
                @if ($bestPeriod)
                    <p class="mb-0 mt-2 small text-muted">
                        {{ number_format($bestPeriodAmount, 2, ',', ' ') }} MAD
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Top converting products ──────────────────────────────────────── --}}
    <div class="card card-sc border-0 mt-3">
        <div class="card-body">
            <h3 class="h6 text-brown mb-1">{{ __('affiliate.top_products') }}</h3>
            <p class="small text-muted">{{ __('affiliate.top_products_sub') }}</p>

            @if ($topProducts->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-bar-chart-line d-block mb-2"></i>
                    <p class="mb-0 fw-600">{{ __('affiliate.no_top_products') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sc align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('affiliate.product') }}</th>
                                <th class="text-end">{{ __('affiliate.orders') }}</th>
                                <th class="text-end">{{ __('affiliate.commission_earned') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topProducts as $row)
                                <tr>
                                    <td>{{ Str::limit($row->product?->localized_name ?? '—', 50) }}</td>
                                    <td class="text-end">{{ number_format((int) $row->orders_count, 0, ',', ' ') }}</td>
                                    <td class="text-end fw-semibold text-terracotta">{{ number_format((float) $row->commission_total, 2, ',', ' ') }} MAD</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Per-product breakdown ────────────────────────────────────────── --}}
    <div class="card card-sc border-0 mt-3">
        <div class="card-body">
            <h3 class="h6 text-brown mb-1">{{ __('affiliate.product_breakdown') }}</h3>
            <p class="small text-muted">{{ __('affiliate.product_breakdown_sub') }}</p>

            @if ($productStats->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-clipboard-data d-block mb-2"></i>
                    <p class="mb-0 fw-600">{{ __('affiliate.no_activity') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sc align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('affiliate.product') }}</th>
                                <th class="text-end">{{ __('affiliate.clicks') }}</th>
                                <th class="text-end">{{ __('affiliate.orders') }}</th>
                                <th class="text-end">{{ __('affiliate.revenue_generated') }}</th>
                                <th class="text-end">{{ __('affiliate.commission_earned') }}</th>
                                <th class="text-end">{{ __('affiliate.conversion_rate') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productStats as $product)
                                <tr>
                                    <td>{{ Str::limit($product->localized_name, 50) }}</td>
                                    <td class="text-end">{{ number_format((int) $product->stat_clicks, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format((int) $product->stat_orders, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format((float) $product->stat_revenue, 2, ',', ' ') }} MAD</td>
                                    <td class="text-end fw-semibold text-terracotta">{{ number_format((float) $product->stat_commission, 2, ',', ' ') }} MAD</td>
                                    <td class="text-end">{{ number_format((float) $product->stat_conversion_rate, 2, ',', ' ') }} %</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $productStats->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var canvas = document.getElementById('engagementChart');
            if (!canvas || !window.Chart) return;

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: @json(__('affiliate.clicks')),
                            data: @json($clicksSeries),
                            borderColor: '#2E7D74',
                            backgroundColor: 'rgba(46,125,116,.12)',
                            fill: true,
                            tension: .35,
                            borderWidth: 2,
                            pointRadius: 0
                        },
                        {
                            label: @json(__('affiliate.conversions')),
                            data: @json($conversionsSeries),
                            borderColor: '#C0693F',
                            backgroundColor: 'rgba(192,105,63,.12)',
                            fill: true,
                            tension: .35,
                            borderWidth: 2,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        });
    </script>
@endpush
