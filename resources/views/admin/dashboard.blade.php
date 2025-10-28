@extends('layouts.admin')

@section('content')
    <div class="container-fluid">

        {{-- Dashboard Header --}}
        <div class="col-12 mb-3">
            <h4 class="fw-semibold">
                <i class="fas fa-chart-line me-2"></i> Admin Dashboard
            </h4>
        </div>

        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            @php
                $stats = [
                    ['label' => 'Total Earnings', 'value' => $totalEarnings, 'color' => 'success'],
                    ['label' => 'Today Earnings', 'value' => $todayEarnings, 'color' => 'success'],
                    ['label' => 'Last 7 Days Earnings', 'value' => $last7Earnings, 'color' => 'success'],
                    ['label' => 'Last 30 Days Earnings', 'value' => $last30Earnings, 'color' => 'success'],
                    ['label' => 'Last 365 Days Earnings', 'value' => $last365Earnings, 'color' => 'success'],
                    ['label' => 'Avg Shipment Amount', 'value' => $averageShipmentPrice, 'color' => 'info'],
                    ['label' => 'Cancelled Amount', 'value' => $cancelledAmount, 'color' => 'danger'],
                    ['label' => 'Pending Amount', 'value' => $pendingValue, 'color' => 'warning'],
                    [
                        'label' => 'Active Delivery Man',
                        'value' => $activeCouriers,
                        'color' => 'secondary',
                        'isCurrency' => false,
                    ],
                ];
            @endphp

            @foreach ($stats as $stat)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card text-center p-3 shadow-sm border-0 h-100">
                        <div class="small text-muted">{{ $stat['label'] }}</div>
                        <div class="h5 fw-bold text-{{ $stat['color'] }}">
                            @if (isset($stat['isCurrency']) && !$stat['isCurrency'])
                                {{ $stat['value'] }}
                            @else
                                ৳ {{ number_format($stat['value'], 2) }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts Section --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 p-3 h-100">
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-chart-bar me-2"></i> Earnings Overview
                    </h6>
                    <canvas id="earningsChart" height="150"></canvas>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 p-3 h-100">
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-truck-moving me-2"></i> Shipments Overview
                    </h6>
                    <canvas id="shipmentsChart" height="150"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Couriers --}}
        <h5 class="mt-4 mb-3">
            <i class="fas fa-trophy me-2 text-warning"></i>Top Delivery Man (by Deliveries)
        </h5>
        <div class="row g-3 mb-4">
            @foreach ($topCouriers as $c)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card p-3 shadow-sm border-0 text-center h-100">
                        <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
                        <div class="fw-semibold small">{{ $c->user->name ?? '—' }}</div>
                        <div class="small text-muted">Delivered: {{ $c->delivered_count }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Recent Shipments Table --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light fw-semibold">
                <i class="fas fa-clock me-2"></i> Recent Shipments
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center small">Tracking</th>
                                <th class="text-center small">Status</th>
                                <th class="text-center small">Delivery Man</th>
                                <th class="text-center small">Amount</th>
                                <th class="text-center small">Created at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentShipments as $ship)
                                <tr>
                                    <td class="text-center small">{{ $ship->tracking_number }}</td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'assigned' => 'info',
                                                'picked' => 'primary',
                                                'in_transit' => 'primary',
                                                'hold' => 'secondary',
                                                'delivered' => 'success',
                                                'partially_delivered' => 'dark',
                                                'cancelled' => 'danger',
                                            ];
                                            $badgeColor = $statusColors[$ship->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            {{ ucwords(str_replace('_', ' ', $ship->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-center small">{{ $ship->courier?->user?->name ?? 'N/A' }}</td>
                                    <td class="text-center small">৳ {{ number_format($ship->price, 2) }}</td>
                                    <td class="text-center small">{{ $ship->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No recent shipments found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Earnings Chart
            const earningsCtx = document.getElementById('earningsChart').getContext('2d');
            new Chart(earningsCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['dates']),
                    datasets: [{
                        label: 'Earnings (৳)',
                        data: @json($chartData['earnings']),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Shipments Chart
            const shipmentsCtx = document.getElementById('shipmentsChart').getContext('2d');
            new Chart(shipmentsCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['dates']),
                    datasets: [{
                        label: 'Shipments',
                        data: @json($chartData['shipments']),
                        backgroundColor: 'rgba(25,135,84,0.7)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>

    <style>
        /* 🔹 Mobile Responsive Adjustments */
        @media (max-width: 767.98px) {

            h4,
            h5 {
                font-size: 1.1rem;
            }

            .card {
                padding: 1rem !important;
            }

            .card .h5 {
                font-size: 1rem;
            }

            .small {
                font-size: 0.8rem;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .table th,
            .table td {
                white-space: nowrap;
            }
        }
    </style>
@endpush
