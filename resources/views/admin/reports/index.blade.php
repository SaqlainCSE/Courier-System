@extends('layouts.admin')

@section('content')

<div class="container py-4">
    <h1 class="mb-4">📦 Shipment Reports</h1>

    <!-- 🔹 Summary Cards -->
    <div class="row g-3 mb-4">
        @foreach(['total'=>'Total','today'=>'Today','this_week'=>'This Week','this_month'=>'This Month','this_year'=>'This Year'] as $key => $label)
        <div class="col-md-3">
            <a href="{{ route('admin.reports.index', ['filter'=>$key, 'status'=>$status]) }}" class="text-decoration-none">
                <div class="card text-center {{ $filter === $key ? 'border-primary shadow-sm' : '' }}">
                    <div class="card-body">
                        <h6 class="text-muted">{{ $label }}</h6>
                        <h3>{{ $summary[$key] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- 🔹 Status Cards -->
    <h5 class="mt-4 mb-3">By Status</h5>
    <div class="row g-3 mb-4">
        @foreach(['pending','assigned','picked','in_transit','delivered','partially_delivered','hold','cancelled'] as $st)
        <div class="col-md-3">
            <a href="{{ route('admin.reports.index', ['filter'=>$filter, 'status'=>$st]) }}" class="text-decoration-none">
                <div class="card text-center {{ $status === $st ? 'border-success shadow-sm' : '' }}">
                    <div class="card-body">
                        <h6 class="text-capitalize">{{ str_replace('_',' ', $st) }}</h6>
                        <h3>{{ $summary[$st] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- 🔹 Custom Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('admin.reports.index') }}">
                <input type="hidden" name="filter" value="custom">
                <div class="col-md-2">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="{{ $dateRange['start_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="{{ $dateRange['end_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ $status=='all'?'selected':'' }}>All</option>
                        @foreach(['pending','assigned','picked','in_transit','delivered','partially_delivered','hold','cancelled'] as $st)
                        <option value="{{ $st }}" {{ $status==$st?'selected':'' }}>
                            {{ ucfirst(str_replace('_',' ',$st)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 align-self-end">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn  btn-outline-dark">Clear</a>

                    <a href="{{ route('admin.reports.exportPdf', request()->query()) }}"
                    class="btn btn-outline-danger">
                    Download PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>


    <!-- 🔹 Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Results:
                <strong>{{ ucfirst(str_replace('_',' ',$status)) }}</strong>
                — {{ ucfirst(str_replace('_',' ',$filter)) }}
                ({{ $shipments->count() }})
            </h5>
        </div>
        <div class="card-body">
            @if($shipments->isEmpty())
                <p class="text-muted">No shipments found for selected filters.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking</th>
                                <th>Delivery Man</th>
                                <th>Status</th>
                                <th>Amount (৳)</th>
                                <th>Merchent</th>
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
                                        'assigned' => 'bg-primary',
                                        'picked' => 'bg-info text-dark',
                                        'in_transit' => 'bg-primary',
                                        'delivered' => 'bg-success',
                                        'partially_delivered' => 'bg-secondary',
                                        'hold' => 'bg-dark',
                                        'cancelled' => 'bg-danger',
                                    ];
                                    $badgeClass = $statusColors[$shipment->status] ?? 'bg-light text-dark';
                                @endphp
                                <td>
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_',' ',$shipment->status)) }}
                                    </span>
                                </td>

                                <td>{{ number_format($shipment->price, 2) }}</td>
                                <td>{{ $shipment->customer->business_name }} - {{ $shipment->customer->phone}} <br> {{ $shipment->customer->business_address }}</td>
                                <td>{{ $shipment->drop_name }} - {{ $shipment->drop_phone }} <br> {{ $shipment->drop_address }}</td>
                                <td>{{ $shipment->notes }}</td>
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
