@extends('layouts.admin')

@section('content')
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="col-12 mb-3">
            <h4 class="fw-semibold">
                <i class="fas fa-coins me-2"></i> Earnings Overview
            </h4>
        </div>

        {{-- Lifetime Earnings Banner --}}
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card p-3 shadow-sm border-0 bg-success text-white">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-infinity fa-2x opacity-75"></i>
                        </div>
                        <div class="col">
                            <div class="small opacity-75">Lifetime Total Earnings</div>
                            <div class="h4 fw-bold mb-0">৳ {{ number_format($earnings['lifetime']['total_earning'], 2) }}</div>
                            <div class="small opacity-75 mt-1">
                                {{ number_format($earnings['lifetime']['total_count']) }} total deliveries &nbsp;·&nbsp;
                                Delivered: {{ number_format($earnings['lifetime']['delivered']) }} &nbsp;·&nbsp;
                                Cancelled: {{ number_format($earnings['lifetime']['cancelled']) }} &nbsp;·&nbsp;
                                Partial: {{ number_format($earnings['lifetime']['partially_delivered']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Earnings Summary Cards --}}
        <div class="row g-3 mb-4">
            @php
                $earningCards = [
                    ['label' => 'Today Earnings',        'value' => $earnings['today']['total_earning']],
                    ['label' => 'Last 7 Days Earnings',  'value' => $earnings['7days']['total_earning']],
                    ['label' => 'Last 30 Days Earnings', 'value' => $earnings['30days']['total_earning']],
                ];
            @endphp

            @foreach ($earningCards as $card)
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card text-center p-3 shadow-sm border-0 h-100">
                        <div class="small text-muted">{{ $card['label'] }}</div>
                        <div class="h5 fw-bold text-success">
                            ৳ {{ number_format($card['value'], 2) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Period Breakdown Tabs --}}
        <h5 class="mt-2 mb-3">
            <i class="fas fa-chart-bar me-2 text-primary"></i> Earning Breakdown by Period
        </h5>

        <ul class="nav nav-tabs mb-3" id="earningTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button">
                    <i class="fas fa-calendar-day me-1"></i> Today
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="week-tab" data-bs-toggle="tab" data-bs-target="#week" type="button">
                    <i class="fas fa-calendar-week me-1"></i> Last 7 Days
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="month-tab" data-bs-toggle="tab" data-bs-target="#month" type="button">
                    <i class="fas fa-calendar-alt me-1"></i> Last 30 Days
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="lifetime-tab" data-bs-toggle="tab" data-bs-target="#lifetime" type="button">
                    <i class="fas fa-infinity me-1"></i> Lifetime
                </button>
            </li>
        </ul>

        <div class="tab-content" id="earningTabsContent">

            @foreach ([
                'today'    => ['id' => 'today',    'label' => "Today's"],
                '7days'    => ['id' => 'week',     'label' => 'Last 7 Days'],
                '30days'   => ['id' => 'month',    'label' => 'Last 30 Days'],
                'lifetime' => ['id' => 'lifetime', 'label' => 'Lifetime'],
            ] as $key => $meta)

                <div class="tab-pane fade {{ $key === 'today' ? 'show active' : '' }}"
                     id="{{ $meta['id'] }}" role="tabpanel">

                    {{-- Period Summary Cards --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="card text-center p-3 shadow-sm border-0 h-100">
                                <div class="small text-muted">{{ $meta['label'] }} Total Earnings</div>
                                <div class="h5 fw-bold text-success">
                                    ৳ {{ number_format($earnings[$key]['total_earning'], 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="card text-center p-3 shadow-sm border-0 h-100">
                                <div class="small text-muted">Total Deliveries</div>
                                <div class="h5 fw-bold text-primary">
                                    {{ number_format($earnings[$key]['total_count']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="card text-center p-3 shadow-sm border-0 h-100">
                                <div class="small text-muted">Rate per Delivery</div>
                                <div class="h5 fw-bold text-secondary">
                                    ৳ {{ $earnings[$key]['total_count'] > 0 ? number_format($earnings[$key]['total_earning'] / $earnings[$key]['total_count'], 2) : '10.00' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Status Breakdown Cards --}}
                    <div class="row g-3 mb-4">
                        @php
                            $total = $earnings[$key]['total_count'] ?: 1;
                            $dPct  = round($earnings[$key]['delivered']           / $total * 100);
                            $cPct  = round($earnings[$key]['cancelled']           / $total * 100);
                            $pPct  = round($earnings[$key]['partially_delivered'] / $total * 100);
                        @endphp

                        {{-- Delivered --}}
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="card p-3 shadow-sm border-0 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success me-2">Delivered</span>
                                    <span class="small text-muted">Successful deliveries</span>
                                </div>
                                <div class="h5 fw-bold text-success mb-0">
                                    {{ number_format($earnings[$key]['delivered']) }}
                                </div>
                                <div class="small text-muted mt-1">
                                    Earnings: <strong class="text-success">৳ {{ number_format($earnings[$key]['delivered_earning'], 2) }}</strong>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-success" style="width: {{ $dPct }}%"></div>
                                </div>
                                <div class="text-end" style="font-size: 11px; color: #6c757d;">{{ $dPct }}%</div>
                            </div>
                        </div>

                        {{-- Cancelled --}}
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="card p-3 shadow-sm border-0 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-danger me-2">Cancelled</span>
                                    <span class="small text-muted">Cancelled deliveries</span>
                                </div>
                                <div class="h5 fw-bold text-danger mb-0">
                                    {{ number_format($earnings[$key]['cancelled']) }}
                                </div>
                                <div class="small text-muted mt-1">
                                    Earnings: <strong class="text-danger">৳ {{ number_format($earnings[$key]['cancelled_earning'], 2) }}</strong>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $cPct }}%"></div>
                                </div>
                                <div class="text-end" style="font-size: 11px; color: #6c757d;">{{ $cPct }}%</div>
                            </div>
                        </div>

                        {{-- Partially Delivered --}}
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="card p-3 shadow-sm border-0 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-dark me-2">Partial</span>
                                    <span class="small text-muted">Partially delivered</span>
                                </div>
                                <div class="h5 fw-bold text-dark mb-0">
                                    {{ number_format($earnings[$key]['partially_delivered']) }}
                                </div>
                                <div class="small text-muted mt-1">
                                    Earnings: <strong class="text-dark">৳ {{ number_format($earnings[$key]['partial_earning'], 2) }}</strong>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-dark" style="width: {{ $pPct }}%"></div>
                                </div>
                                <div class="text-end" style="font-size: 11px; color: #6c757d;">{{ $pPct }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Table --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light fw-semibold small">
                            <i class="fas fa-table me-2"></i> {{ $meta['label'] }} Earnings Breakdown
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="small">Status</th>
                                            <th class="text-center small">Count</th>
                                            <th class="text-center small">Rate</th>
                                            <th class="text-center small">Total Earnings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-success">Delivered</span></td>
                                            <td class="text-center small">{{ number_format($earnings[$key]['delivered']) }}</td>
                                            <td class="text-center small">৳ {{ $earnings[$key]['delivered'] > 0 ? number_format($earnings[$key]['delivered_earning'] / $earnings[$key]['delivered'], 2) : '10.00' }}</td>
                                            <td class="text-center small fw-bold text-success">৳ {{ number_format($earnings[$key]['delivered_earning'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-danger">Cancelled</span></td>
                                            <td class="text-center small">{{ number_format($earnings[$key]['cancelled']) }}</td>
                                            <td class="text-center small">৳ {{ $earnings[$key]['cancelled'] > 0 ? number_format($earnings[$key]['cancelled_earning'] / $earnings[$key]['cancelled'], 2) : '10.00' }}</td>
                                            <td class="text-center small fw-bold text-danger">৳ {{ number_format($earnings[$key]['cancelled_earning'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-dark">Partially Delivered</span></td>
                                            <td class="text-center small">{{ number_format($earnings[$key]['partially_delivered']) }}</td>
                                            <td class="text-center small">৳ {{ $earnings[$key]['partially_delivered'] > 0 ? number_format($earnings[$key]['partial_earning'] / $earnings[$key]['partially_delivered'], 2) : '10.00' }}</td>
                                            <td class="text-center small fw-bold">৳ {{ number_format($earnings[$key]['partial_earning'], 2) }}</td>
                                        </tr>
                                        <tr class="table-success">
                                            <td class="fw-bold small">Total</td>
                                            <td class="text-center small fw-bold">{{ number_format($earnings[$key]['total_count']) }}</td>
                                            <td class="text-center small">—</td>
                                            <td class="text-center small fw-bold text-success">৳ {{ number_format($earnings[$key]['total_earning'], 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach

        </div>{{-- end tab-content --}}

    </div>
@endsection

@push('scripts')
    <style>
        @media (max-width: 767.98px) {
            h4, h5 { font-size: 1.1rem; }
            .card { padding: 1rem !important; }
            .card .h5 { font-size: 1rem; }
            .small { font-size: 0.8rem; }
            .nav-link { font-size: 0.85rem; padding: 6px 10px; }
            .table-responsive { overflow-x: auto; }
            .table th, .table td { white-space: nowrap; }
        }
    </style>
@endpush
