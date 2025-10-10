@extends('layouts.admin')

@section('content')
<div class="container-fluid">

        {{-- Dashboard Main Content --}}
        <div class="col-lg-12">
            <h3 class="mb-4"><i class="fas fa-chart-line me-2"></i> Admin Dashboard</h3>

            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
            {{-- Total Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Total Shipments</div>
                    <div class="h4 fw-bold text-primary">{{ $totalShipments }}</div>
                </div>
            </div> --}}

            {{-- Pending Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Pending Shipments</div>
                    <div class="h4 fw-bold text-warning">{{ $pendingShipments }}</div>
                </div>
            </div> --}}

            {{-- Delivered Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Delivered Shipments</div>
                    <div class="h4 fw-bold text-success">{{ $deliveredShipments }}</div>
                </div>
            </div> --}}

            {{-- Picked Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Picked Shipments</div>
                    <div class="h4 fw-bold text-success">{{ $pickedShipments }}</div>
                </div>
            </div> --}}

            {{-- In Transit Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">In Transit Shipments</div>
                    <div class="h4 fw-bold text-primary">{{ $inTransitShipments }}</div>
                </div>
            </div> --}}

            {{-- Partially Delivered Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Partially Delivered Shipments</div>
                    <div class="h4 fw-bold text-success">{{ $partiallyDeliveredShipments }}</div>
                </div>
            </div> --}}

            {{-- Hold Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Hold Shipments</div>
                    <div class="h4 fw-bold text-secondary">{{ $holdShipments }}</div>
                </div>
            </div> --}}

            {{-- Cancelled Shipments --}}
            {{-- <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Cancelled Shipments</div>
                    <div class="h4 fw-bold text-danger">{{ $cancelledShipments }}</div>
                </div>
            </div> --}}

            {{-- Total Earnings --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Total Earnings</div>
                    <div class="h4 fw-bold text-success">৳ {{ number_format($totalEarnings,2) }}</div>
                </div>
            </div>

            {{-- Today Earnings --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Today Earnings</div>
                    <div class="h4 fw-bold text-success">৳ {{ number_format($todayEarnings,2) }}</div>
                </div>
            </div>

            {{-- Last 7 Days Earnings --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Last 7 Days Earnings</div>
                    <div class="h4 fw-bold text-success">৳ {{ number_format($last7Earnings,2) }}</div>
                </div>
            </div>

            {{-- Last 30 Days Earnings --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Last 30 Days Earnings</div>
                    <div class="h4 fw-bold text-success">৳ {{ number_format($last30Earnings,2) }}</div>
                </div>
            </div>

            {{-- Last 365 Days Earnings --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Last 365 Days Earnings</div>
                    <div class="h4 fw-bold text-success">৳ {{ number_format($last365Earnings,2) }}</div>
                </div>
            </div>

            {{-- Average Shipment Price --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Average Shipment Amount</div>
                    <div class="h4 fw-bold text-info">৳ {{ number_format($averageShipmentPrice,2) }}</div>
                </div>
            </div>

            {{-- Cancelled Amount --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Cancelled Amount</div>
                    <div class="h4 fw-bold text-danger">৳ {{ number_format($cancelledAmount,2) }}</div>
                </div>
            </div>

            {{-- Pending Value --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Pending Amount</div>
                    <div class="h4 fw-bold text-warning">৳ {{ number_format($pendingValue,2) }}</div>
                </div>
            </div>

            {{-- Active Couriers --}}
            <div class="col-6 col-md-3">
                <div class="card text-center p-3 shadow-sm border-0">
                    <div class="small text-muted">Active Delivery Man</div>
                    <div class="h4 fw-bold text-secondary">{{ $activeCouriers }}</div>
                </div>
            </div>
        </div>

            {{-- Charts Section --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 p-3">
                        <h6 class="fw-semibold mb-3"><i class="fas fa-chart-bar me-2"></i> Earnings Overview</h6>
                        <canvas id="earningsChart" height="150"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 p-3">
                        <h6 class="fw-semibold mb-3"><i class="fas fa-truck-moving me-2"></i> Shipments Overview</h6>
                        <canvas id="shipmentsChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            {{-- Top Couriers --}}
            <h5 class="mt-4 mb-3"><i class="fas fa-trophy me-2 text-warning"></i>Top Delivery Man (by Deliveries)</h5>
            <div class="row g-3 mb-4">
                @foreach($topCouriers as $c)
                    <div class="col-sm-6 col-md-3">
                        <div class="card p-3 shadow-sm border-0 text-center">
                            <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
                            <div class="fw-semibold">{{ $c->user->name ?? '—' }}</div>
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
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Tracking</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Delivery Man</th>
                                <th class="text-center">Amount</th>
                                <th class="text-center">Created at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentShipments as $ship)
                                <tr>
                                    <td class="text-center">{{ $ship->tracking_number }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $ship->status == 'delivered' ? 'success' : ($ship->status == 'cancelled' ? 'danger' : 'secondary') }}">
                                            {{ ucwords(str_replace('_',' ', $ship->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $ship->courier?->user?->name ?? 'N/A' }}</td>
                                    <td class="text-center">৳ {{ number_format($ship->price,2) }}</td>
                                    <td class="text-center">{{ $ship->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-3 text-muted">No recent shipments found</td></tr>
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
document.addEventListener('DOMContentLoaded', function(){

    // Earnings Line Chart
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
            plugins: {
                legend: { display: true }
            }
        }
    });

    // Shipments Bar Chart
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
            plugins: {
                legend: { display: true }
            }
        }
    });

});
</script>
@endpush

