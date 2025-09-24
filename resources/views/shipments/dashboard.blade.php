@extends('layouts.app')

@section('content')
<div class="container py-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Shipment Dashboard</h2>
        <a href="{{ route('shipments.create') }}" class="btn btn-success shadow-sm"><i class="fas fa-plus me-1"></i>New Shipment</a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        @php
            $cards = [
                'pending' => ['color' => 'warning', 'icon' => 'hourglass-start', 'label' => 'Pending'],
                'in_transit' => ['color' => 'primary', 'icon' => 'truck', 'label' => 'In Transit'],
                'delivered' => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Delivered'],
                'cancelled' => ['color' => 'danger', 'icon' => 'times-circle', 'label' => 'Cancelled'],
            ];
        @endphp

        @foreach($cards as $key => $card)
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center py-4">
                <div class="card-body">
                    <i class="fas fa-{{ $card['icon'] }} fa-2x text-{{ $card['color'] }} mb-2"></i>
                    <h5 class="fw-bold">{{ $card['label'] }}</h5>
                    <h3 class="text-{{ $card['color'] }}">{{ $summary[$key] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Total Cost Card -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center py-4">
            <div class="card-body">
                <i class="fas fa-coins fa-2x text-warning mb-2"></i>
                <h5 class="fw-bold">Total Cost</h5>
                <h3 class="text-success">৳ {{ number_format($totalCost, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Monthly Wise Cost -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-calendar-alt me-2"></i>Monthly Cost Breakdown
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyCosts as $month)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($month->month.'-01')->format('F Y') }}</td>
                                <td>৳ {{ number_format($month->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div><br>

    <!-- Recent Shipments Table -->
    <div class="card shadow-sm border-0">
        {{-- <div class="card-header bg-danger text-white fw-bold">
            <i class="fas fa-boxes me-2"></i>Recent Shipments
        </div> --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tracking #</th>
                            <th>Pickup</th>
                            <th>Drop</th>
                            <th>Weight</th>
                            <th>Status</th>
                            <th>Delivery Fee</th>
                            <th>Booked At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($shipments as $shipment)
                        <tr>
                            <td>{{ $shipment->tracking_number }}</td>
                            <td>{{ $shipment->pickup_name }}<br><small class="text-muted">{{ Str::limit($shipment->pickup_address,30) }}</small></td>
                            <td>{{ $shipment->drop_name }}<br><small class="text-muted">{{ Str::limit($shipment->drop_address,30) }}</small></td>
                            <td>{{ $shipment->weight_kg }} kg</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'assigned' => 'info',
                                        'picked' => 'primary',
                                        'in_transit' => 'secondary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$shipment->status] }}">
                                    {{ ucfirst(str_replace('_',' ', $shipment->status)) }}
                                </span>
                            </td>
                            <td>৳ {{ number_format($shipment->price, 2) }}</td>
                            <td>{{ $shipment->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-sm btn-outline-info">View</a>
                                @if($shipment->status === 'pending')
                                    <form action="{{ route('shipments.cancel', $shipment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-truck-loading fa-2x mb-2"></i><br>No shipments yet.</td>
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
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endpush
