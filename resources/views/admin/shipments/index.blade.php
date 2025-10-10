@extends('layouts.admin')

@section('content')
<div class="container py-5">

    <!-- Branding / Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold">
            <i class="fas fa-shipping-fast text-danger me-2"></i> StepUp<span class="text-danger">Courier</span> Admin
        </h1>
        <p class="text-muted">Manage, Track, and Assign Shipments with Ease</p>
    </div>

    <!-- Dashboard Summary Cards -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                'pending' => ['color' => 'warning', 'icon' => 'hourglass-half', 'label' => 'Pending'],
                'assigned' => ['color' => 'info', 'icon' => 'user-check', 'label' => 'Assigned'],
                'picked' => ['color' => 'primary', 'icon' => 'box-open', 'label' => 'Picked'],
                'in_transit' => ['color' => 'primary', 'icon' => 'truck-moving', 'label' => 'In Transit'],
                'delivered' => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Delivered'],
                'hold' => ['color' => 'secondary', 'icon' => 'pause-circle', 'label' => 'On Hold'],
                'partially_delivered' => ['color' => 'dark', 'icon' => 'clipboard-check', 'label' => 'Partially Delivered'],
                'cancelled' => ['color' => 'danger', 'icon' => 'times-circle', 'label' => 'Cancelled'],
            ];

            $periods = [
                'today' => ['color' => 'dark', 'icon' => 'calendar-day', 'label' => 'Today'],
                'this_week' => ['color' => 'dark', 'icon' => 'calendar-week', 'label' => 'This Week'],
                'this_month' => ['color' => 'dark', 'icon' => 'calendar-alt', 'label' => 'This Month'],
                'this_year' => ['color' => 'dark', 'icon' => 'calendar', 'label' => 'This Year'],
                'total' => ['color' => 'dark', 'icon' => 'boxes', 'label' => 'Total Shipments'],
            ];
        @endphp

        {{-- Status Cards --}}
        @foreach($cards as $key => $card)
            <div class="col-6 col-md-3">
                <a href="{{ route('admin.shipments.index', array_merge(request()->all(), ['status' => $key])) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3 hover-card">
                        <i class="fas fa-{{ $card['icon'] }} fa-2x text-{{ $card['color'] }} mb-2"></i>
                        <h6 class="fw-bold text-muted">{{ $card['label'] }}</h6>
                        <h4 class="fw-bold text-{{ $card['color'] }}">{{ $summary[$key] ?? 0 }}</h4>
                    </div>
                </a>
            </div>
        @endforeach

        {{-- Period Cards --}}
        @foreach($periods as $key => $card)
            <div class="col-6 col-md-3">
                <a href="{{ route('admin.shipments.index', array_merge(request()->all(), ['period' => $key])) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3 hover-card">
                        <i class="fas fa-{{ $card['icon'] }} fa-2x text-{{ $card['color'] }} mb-2"></i>
                        <h6 class="fw-bold text-muted">{{ $card['label'] }}</h6>
                        <h4 class="fw-bold text-{{ $card['color'] }}">{{ $summary[$key] ?? 0 }}</h4>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-gradient text-dark fw-bold rounded-top-4">
            <i class="fas fa-filter me-2"></i> Filter Shipments
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search tracking, address, name..." value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach(['pending','assigned','picked','in_transit','hold','delivered','partially_delivered','cancelled'] as $s)
                            <option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="courier_id" class="form-select form-select-sm">
                        <option value="">All Couriers</option>
                        @foreach($couriers as $c)
                            <option value="{{ $c->id }}" @selected(request('courier_id')==$c->id)>
                                {{ $c->user->name ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-12 mt-2 d-flex gap-2">
                    <button class="btn btn-sm btn-dark"><i class="fas fa-search me-1"></i> Filter</button>
                    <a href="{{ route('admin.shipments.index') }}" class="btn btn-sm btn-outline-dark">Clear</a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">Reports</a>
                    <a href="{{ route('admin.reports.export', request()->all()) }}" class="btn btn-sm btn-primary">Export CSV</a>
                    <a href="{{ route('admin.shipments.print.all') }}" target="_blank" class="btn btn-sm btn-success">Print</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-dark text-white fw-bold rounded-top-4">
            <i class="fas fa-boxes me-2"></i> All Shipments
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tracking</th>
                            <th>Pickup</th>
                            <th>Dropoff</th>
                            <th>Delivery Man</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $s)
                            <tr>
                                <td>
                                    <strong>{{ $s->tracking_number }}</strong>
                                    <div class="small text-muted">{{ $s->created_at->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div>{{ Str::limit($s->pickup_address,40) }}</div>
                                    <div class="small text-muted">{{ Str::limit($s->drop_address,40) }}</div>
                                </td>
                                <td>
                                    <div class="small text-muted">{{ Str::limit($s->drop_address,40) }}</div>
                                </td>
                                <td>{{ $s->courier?->user?->name ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'assigned' => 'info',
                                            'picked' => 'primary',
                                            'in_transit' => 'primary',
                                            'delivered' => 'success',
                                            'hold' => 'secondary',
                                            'partially_delivered' => 'dark',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$s->status] ?? 'light' }}">
                                        {{ ucfirst(str_replace('_',' ',$s->status)) }}
                                    </span>
                                </td>
                                <td>৳ {{ number_format($s->price,2) }}</td>
                                <td>
                                    <a href="{{ route('admin.shipments.show', $s) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-truck-loading fa-2x mb-2"></i><br>No shipments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center flex-wrap">
                {{ $shipments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient {
    background: linear-gradient(45deg, #4facfe, #00f2fe);
}
.hover-card:hover {
    transform: translateY(-5px);
    transition: 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

@endpush
@endsection
