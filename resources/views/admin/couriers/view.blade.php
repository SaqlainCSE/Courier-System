@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-motorcycle me-2 text-primary"></i>{{ $courier->user->name }} — Delivery Man</h2>
        <a href="{{ route('admin.couriers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Courier Info -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center">
                    <i class="fas fa-user-circle text-primary" style="font-size: 5rem;"></i>
                    <h5 class="mt-3 mb-0">{{ $courier->user->name }}</h5>
                    <small class="text-muted">{{ $courier->user->email }}</small>
                </div>
                <div class="col-md-8">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <strong>Vehicle Type:</strong> {{ $courier->vehicle_type ?? 'N/A' }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Vehicle Number:</strong> {{ $courier->vehicle_number ?? 'N/A' }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $courier->status == 'available' ? 'success' : ($courier->status == 'busy' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($courier->status) }}
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Commission:</strong> {{ $courier->commission_rate }} BDT / shipment
                        </div>
                        <div class="col-12 mt-2">
                            <div class="alert alert-info mb-0">
                                <strong>Total Earnings:</strong> ৳{{ number_format($commission, 2) }} <br>
                                <strong>Today's Earning:</strong> ৳{{ number_format($todayEarnings, 2) }} <br>
                                <strong>Total Delivered:</strong> {{ $totalDeliveredShipments }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Summary Cards -->
    <div class="row g-3 mb-4">
        @php
            $allStatuses = ['pending','assigned','picked','in_transit','delivered','partially_delivered','hold','cancelled'];
            $statusColors = [
                'pending' => 'warning',
                'assigned' => 'info',
                'picked' => 'primary',
                'in_transit' => 'primary',
                'delivered' => 'success',
                'partially_delivered' => 'dark',
                'hold' => 'secondary',
                'cancelled' => 'danger',
            ];
        @endphp

        @foreach($allStatuses as $st)
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm text-center border-0">
                    <div class="card-body">
                        <h6 class="text-capitalize text-muted">{{ str_replace('_',' ',$st) }}</h6>
                        <h4 class="text-{{ $statusColors[$st] ?? 'secondary' }}">
                            {{ $statusSummary[$st] ?? 0 }}
                        </h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter Form -->
    <form method="GET" class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach($allStatuses as $s)
                            <option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-md-2 d-grid gap-2">
                    <button type="submit" class="btn btn-dark btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.couriers.view', $courier->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo me-1"></i> Clear</a>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.couriers.print', ['id'=>$courier->id, 'status'=>$status, 'from'=>$from, 'to'=>$to]) }}" target="_blank" class="btn btn-success btn-sm">
                        <i class="fas fa-print me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Shipments Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i> Shipments Assigned</h5>
            <span class="badge bg-light text-dark">Total: {{ $shipments->count() }}</span>
        </div>
        <div class="card-body p-0">
            @if($shipments->isEmpty())
                <p class="text-center py-4 text-muted">No shipments assigned yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking</th>
                                <th>Status</th>
                                <th>Amount (৳)</th>
                                <th>Customer</th>
                                <th>Pickup</th>
                                <th>Dropoff</th>
                                <th>Notes</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipments as $shipment)
                            <tr>
                                <td>{{ $shipment->tracking_number }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$shipment->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_',' ',$shipment->status)) }}
                                    </span>
                                </td>
                                <td>{{ number_format($shipment->price, 2) }}</td>
                                <td>
                                    {{ $shipment->customer->business_name ?? '—' }} <br>
                                    {{ $shipment->customer->phone ?? '' }}
                                </td>
                                <td>{{ $shipment->pickup_address }}</td>
                                <td>{{ $shipment->drop_address }}</td>
                                <td>{{ $shipment->notes ?? '-' }}</td>
                                <td>{{ $shipment->created_at->format('d M, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
