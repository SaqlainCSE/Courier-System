@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h1 class="mb-4 text-center text-md-start">📦 Shipment Reports</h1>

    <!-- 🔹 Summary Cards -->
    <div class="row g-2 g-md-3 mb-4">
        @foreach(['total'=>'Total','today'=>'Today','this_week'=>'This Week','this_month'=>'This Month','this_year'=>'This Year'] as $key => $label)
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.reports.index', ['filter'=>$key, 'status'=>$status]) }}" class="text-decoration-none">
                <div class="card text-center {{ $filter === $key ? 'border-primary shadow-sm' : '' }}">
                    <div class="card-body py-3">
                        <h6 class="text-muted small">{{ $label }}</h6>
                        <h3 class="fw-bold fs-5">{{ $summary[$key] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- 🔹 Status Cards -->
    <h5 class="mt-4 mb-3">By Status</h5>
    <div class="row g-2 g-md-3 mb-4">
        @foreach(['pending','assigned','picked','in_transit','delivered','partially_delivered','hold','cancelled'] as $st)
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.reports.index', ['filter'=>$filter, 'status'=>$st]) }}" class="text-decoration-none">
                <div class="card text-center {{ $status === $st ? 'border-success shadow-sm' : '' }}">
                    <div class="card-body py-3">
                        <h6 class="text-capitalize small">{{ str_replace('_',' ', $st) }}</h6>
                        <h3 class="fw-bold fs-5">{{ $summary[$st] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- 🔹 Custom Filter -->
    <div class="card mb-4 border-0 shadow-sm rounded-3">
        <div class="card-body">
            <form class="row g-2 g-md-3" method="GET" action="{{ route('admin.reports.index') }}">
                <input type="hidden" name="filter" value="custom">

                <div class="col-6 col-md-2">
                    <label class="small fw-bold">Start</label>
                    <input type="date" name="start_date" value="{{ $dateRange['start_date'] ?? '' }}" class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-2">
                    <label class="small fw-bold">End</label>
                    <input type="date" name="end_date" value="{{ $dateRange['end_date'] ?? '' }}" class="form-control form-control-sm">
                </div>

                <div class="col-12 col-md-2">
                    <label class="small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" {{ $status=='all'?'selected':'' }}>All</option>
                        @foreach(['pending','assigned','picked','in_transit','delivered','partially_delivered','hold','cancelled'] as $st)
                        <option value="{{ $st }}" {{ $status==$st?'selected':'' }}>
                            {{ ucfirst(str_replace('_',' ',$st)) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 d-flex flex-wrap gap-2 align-items-end justify-content-center justify-content-md-end">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-dark">Clear</a>
                    <a href="{{ route('admin.reports.exportPdf', request()->query()) }}" class="btn btn-sm btn-outline-danger">Download PDF</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 🔹 Data Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0 text-center text-md-start">
                Results:
                <strong>{{ ucfirst(str_replace('_',' ',$status)) }}</strong> —
                {{ ucfirst(str_replace('_',' ',$filter)) }}
                ({{ $shipments->count() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($shipments->isEmpty())
                <p class="text-muted text-center py-4">No shipments found for selected filters.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking</th>
                                <th>Delivery Man</th>
                                <th>Status</th>
                                <th>Amount (৳)</th>
                                <th>Merchant</th>
                                <th>Customer</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipments as $shipment)
                            <tr>
                                <td>{{ $shipment->tracking_number }}</td>
                                <td class="text-center">{{ $shipment->courier->user->name ?? '—' }}</td>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning text-dark',
                                        'assigned' => 'bg-info text-dark',
                                        'picked' => 'bg-primary',
                                        'in_transit' => 'bg-primary',
                                        'delivered' => 'bg-success',
                                        'partially_delivered' => 'bg-dark',
                                        'hold' => 'bg-secondary',
                                        'cancelled' => 'bg-danger',
                                    ];
                                    $badgeClass = $statusColors[$shipment->status] ?? 'bg-light text-dark';
                                @endphp
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_',' ',$shipment->status)) }}</span></td>
                                <td>{{ number_format($shipment->price, 2) }}</td>
                                <td>
                                    <div class="small">
                                        <strong>{{ $shipment->customer->business_name }}</strong><br>
                                        {{ $shipment->customer->phone }}<br>
                                        <span class="text-muted">{{ Str::limit($shipment->customer->business_address, 40) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <strong>{{ $shipment->drop_name }}</strong><br>
                                        {{ $shipment->drop_phone }}<br>
                                        <span class="text-muted">{{ Str::limit($shipment->drop_address, 40) }}</span>
                                    </div>
                                </td>
                                <td class="small">{{ $shipment->notes ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ✅ Responsive Styles -->
@push('styles')
<style>
@media (max-width: 767.98px) {
    h1 {
        font-size: 1.5rem;
    }

    .card-body h3 {
        font-size: 1.2rem;
    }

    .table {
        font-size: 0.85rem;
    }

    .table thead {
        display: none;
    }

    .table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.75rem;
    }

    .table tbody td {
        display: flex;
        justify-content: space-between;
        padding: 0.4rem 0;
        font-size: 0.9rem;
    }

    .table tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6c757d;
    }
}
</style>
@endpush
@endsection
