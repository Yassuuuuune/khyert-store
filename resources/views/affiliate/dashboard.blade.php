@extends('layouts.dashboard')

@section('title', __('affiliate.dashboard'))
@section('page_title', __('affiliate.dashboard'))

@section('content')
    {{-- ── KPI cards ────────────────────────────────────────────────────── --}}
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($totalEarned, 2, ',', ' ') }} <span class="fs-6 text-brown">MAD</span></div>
                        <div class="stat-label mt-1">{{ __('affiliate.total_earned') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-gold"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($pendingCommissions, 2, ',', ' ') }} <span class="fs-6 text-brown">MAD</span></div>
                        <div class="stat-label mt-1">{{ __('affiliate.pending_commissions') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-brown"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($availableBalance, 2, ',', ' ') }} <span class="fs-6 text-brown">MAD</span></div>
                        <div class="stat-label mt-1">{{ __('affiliate.available_balance') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-green"><i class="bi bi-wallet2"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($paidTotal, 2, ',', ' ') }} <span class="fs-6 text-brown">MAD</span></div>
                        <div class="stat-label mt-1">{{ __('affiliate.paid_total') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-primary"><i class="bi bi-check2-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($totalClicks, 0, ',', ' ') }}</div>
                        <div class="stat-label mt-1">{{ __('affiliate.total_clicks') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-blue"><i class="bi bi-cursor"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($totalConversions, 0, ',', ' ') }}</div>
                        <div class="stat-label mt-1">{{ __('affiliate.conversions') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-green"><i class="bi bi-bag-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($conversionRate, 2, ',', ' ') }} %</div>
                        <div class="stat-label mt-1">{{ __('affiliate.conversion_rate') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-gold"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card hover-lift">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-value">{{ number_format($activeLinks, 0, ',', ' ') }}</div>
                        <div class="stat-label mt-1">{{ __('affiliate.active_links') }}</div>
                    </div>
                    <div class="stat-icon stat-icon-blue"><i class="bi bi-link-45deg"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Referral kit ─────────────────────────────────────────────────── --}}
    <div class="cta-panel cta-affiliate mt-4">
        <div class="position-relative">
            <h2 class="h4 mb-1 font-display">{{ __('affiliate.share_title') }}</h2>
            <p class="mb-4 opacity-75">{{ __('affiliate.share_hint') }}</p>

            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label text-white-50 small text-uppercase">{{ __('affiliate.referral_code') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace fw-bold" value="{{ $referralCode }}" readonly>
                        <button type="button" class="btn btn-light" data-copy="{{ $referralCode }}">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div class="col-lg-8">
                    <label class="form-label text-white-50 small text-uppercase">{{ __('affiliate.referral_link') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $referralLink }}" readonly>
                        <button type="button" class="btn btn-light" data-copy="{{ $referralLink }}">
                            <i class="bi bi-clipboard me-1"></i>{{ __('affiliate.copy_link') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Charts ───────────────────────────────────────────────────────── --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card card-sc border-0 h-100">
                <div class="card-body">
                    <h3 class="h6 text-brown mb-3">{{ __('affiliate.clicks_last_30') }}</h3>
                    <canvas id="clicksChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-sc border-0 h-100">
                <div class="card-body">
                    <h3 class="h6 text-brown mb-3">{{ __('affiliate.commissions_last_12') }}</h3>
                    <canvas id="commissionsChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent commissions ───────────────────────────────────────────── --}}
    <div class="card card-sc border-0 mt-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="h6 text-brown mb-0">{{ __('affiliate.recent_commissions') }}</h3>
                <a href="{{ route('affiliate.commissions.index') }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('affiliate.commissions') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            @if ($recentCommissions->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-cash-coin d-block mb-2"></i>
                    <p class="mb-1 fw-600">{{ __('affiliate.no_commissions') }}</p>
                    <p class="mb-0 small">{{ __('affiliate.no_commissions_hint') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sc align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('affiliate.date') }}</th>
                                <th>{{ __('affiliate.order_number') }}</th>
                                <th>{{ __('affiliate.product') }}</th>
                                <th class="text-end">{{ __('affiliate.sale_amount') }}</th>
                                <th class="text-end">{{ __('affiliate.commission') }}</th>
                                <th class="text-center">{{ __('affiliate.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentCommissions as $commission)
                                <tr>
                                    <td class="text-nowrap">{{ $commission->created_at?->format('d/m/Y') }}</td>
                                    <td class="font-monospace small">{{ $commission->order?->order_number ?? '—' }}</td>
                                    <td>{{ Str::limit($commission->product?->localized_name ?? '—', 40) }}</td>
                                    <td class="text-end">{{ number_format((float) $commission->sale_amount, 2, ',', ' ') }} MAD</td>
                                    <td class="text-end fw-semibold text-terracotta">{{ number_format((float) $commission->commission_amount, 2, ',', ' ') }} MAD</td>
                                    <td class="text-center">
                                        <span class="badge {{ $commission->status === 'paid' ? 'bg-success' : ($commission->status === 'cancelled' ? 'bg-danger' : ($commission->status === 'approved' ? 'bg-info text-dark' : 'bg-warning text-dark')) }}">
                                            {{ __('affiliate.status_' . $commission->status) }}
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart) return;

            var clicksCanvas = document.getElementById('clicksChart');
            if (clicksCanvas) {
                new Chart(clicksCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($clickLabels),
                        datasets: [{
                            label: @json(__('affiliate.clicks')),
                            data: @json($clickData),
                            borderColor: '#2E7D74',
                            backgroundColor: 'rgba(46,125,116,.12)',
                            fill: true,
                            tension: .35,
                            borderWidth: 2,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });
            }

            var commissionsCanvas = document.getElementById('commissionsChart');
            if (commissionsCanvas) {
                new Chart(commissionsCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($commissionLabels),
                        datasets: [{
                            label: @json(__('affiliate.commissions')),
                            data: @json($commissionData),
                            borderColor: '#C0693F',
                            backgroundColor: 'rgba(192,105,63,.12)',
                            fill: true,
                            tension: .35,
                            borderWidth: 2,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        });
    </script>
@endpush
