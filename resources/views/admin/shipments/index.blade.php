@extends('layouts.admin')

@section('content')
<div class="container py-4">

    <!-- Branding / Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold">
            <i class="fas fa-shipping-fast text-danger me-2"></i> StepUp<span class="text-danger">Courier</span> Admin
        </h1>
        <p class="text-muted small">Manage, Track, and Assign Shipments with Ease</p>
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
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 hover-card h-100 shipment-card"
                    data-type="status"
                    data-value="{{ $key }}"
                    style="cursor:pointer;">

                    <i class="fas fa-{{ $card['icon'] }} fa-2x text-{{ $card['color'] }} mb-2"></i>
                    <h6 class="fw-bold small text-muted mb-1">{{ $card['label'] }}</h6>
                    <h5 class="fw-bold text-{{ $card['color'] }}">{{ $summary[$key] ?? 0 }}</h5>
                </div>
            </div>
        @endforeach

        {{-- Period Cards --}}
        @foreach($periods as $key => $card)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 hover-card h-100 shipment-card"
                    data-type="period"
                    data-value="{{ $key }}"
                    style="cursor:pointer;">

                    <i class="fas fa-{{ $card['icon'] }} fa-2x text-{{ $card['color'] }} mb-2"></i>
                    <h6 class="fw-bold small text-muted mb-1">{{ $card['label'] }}</h6>
                    <h5 class="fw-bold text-{{ $card['color'] }}">{{ $summary[$key] ?? 0 }}</h5>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-gradient text-dark fw-bold rounded-top-4 d-flex align-items-center justify-content-between">
            <span><i class="fas fa-filter me-2"></i> Filter Shipments</span>
            <button class="btn btn-sm btn-outline-dark d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <div class="card-body collapse show" id="filterCollapse">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-sm-6 col-md-3">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search tracking, address, name..." value="{{ request('q') }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>

                        @foreach([
                            'pending',
                            'assigned',
                            'picked',
                            'in_transit',
                            'hold',
                            'delivered',
                            'partially_delivered',
                            'cancelled'
                        ] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>
                                {{ ucwords(str_replace('_',' ', $s)) }}
                            </option>
                        @endforeach

                        <option value="paid" @selected(request('status') == 'paid')>
                            Paid
                        </option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="courier_id" class="form-select form-select-sm">
                        <option value="">All Couriers</option>
                        @foreach($couriers as $c)
                            <option value="{{ $c->id }}" @selected(request('courier_id')==$c->id)>
                                {{ $c->user->name ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-12 mt-2 d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-dark"><i class="fas fa-search me-1"></i> Filter</button>
                    <a href="{{ route('admin.shipments.index') }}" class="btn btn-sm btn-outline-dark">Clear</a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">Reports</a>
                    <a href="{{ route('admin.reports.export', request()->all()) }}" class="btn btn-sm btn-primary">Export CSV</a>
                    <a href="{{ route('admin.shipments.print.all') }}" target="_blank" class="btn btn-sm btn-success">Print</a>
                    <a href="{{ route('admin.shipments.bulk.assign') }}" class="btn btn-sm btn-warning"><i class="fas fa-user-tie me-1"></i> Today Assign</a>
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
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th>Tracking</th>
                            <th>Pickup</th>
                            <th>Dropoff</th>
                            <th>Delivery Man</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Partial Amount</th>
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
                                <td class="text-truncate" style="max-width: 200px;">
                                    <div class="small text-muted">
                                        {{ $s->customer->business_name }} - {{ $s->customer->phone }} <br>
                                        {{ Str::limit($s->pickup_address, 40) }}
                                    </div>
                                </td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    <div class="small text-muted">
                                        {{ $s->drop_name }} - {{ $s->drop_phone }} <br>
                                        {{ Str::limit($s->drop_address, 40) }}
                                    </div>
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

                                    @if(in_array($s->status, ['delivered','partially_delivered']) && $s->balance_cost <= 0)
                                        <br>
                                        <span class="badge bg-success mt-1">
                                            <i class="fas fa-check-circle me-1"></i> Paid
                                        </span>
                                    @endif

                                </td>
                                <td>৳ {{ number_format($s->price,2) }}</td>
                                <td>৳ {{ number_format($s->partial_price,2) }}</td>
                                <td>
                                    <a href="{{ route('admin.shipments.show', $s) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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

    <!-- Shipment Modal -->
    <div class="modal fade" id="shipmentModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Filtered Shipments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="modalContent">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
    document.querySelectorAll('.shipment-card').forEach(card => {
        card.addEventListener('click', function () {
            let type = this.dataset.type;
            let value = this.dataset.value;

            let url = "{{ route('admin.shipments.index') }}?" + type + "=" + value;

            // show modal
            let modal = new bootstrap.Modal(document.getElementById('shipmentModal'));
            modal.show();

            // loading state
            document.getElementById('modalContent').innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            `;

            // fetch data
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    // শুধু table অংশ extract করতে পারো (optional)
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let table = doc.querySelector('.table-responsive');

                    document.getElementById('modalContent').innerHTML = table
                        ? table.outerHTML
                        : html;
                });
        });
    });
    </script>
@endpush

@push('styles')
    <style>
    .bg-gradient {
        background: linear-gradient(45deg, #4facfe, #00f2fe);
    }
    .hover-card {
        transition: transform 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
    }
    .table-responsive {
        overflow-x: auto;
    }
    @media (max-width: 576px) {
        h1 {
            font-size: 1.5rem;
        }
        .hover-card h5 {
            font-size: 1rem;
        }
        .card-header .btn {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        .table {
            font-size: 0.85rem;
        }
    }
    </style>
@endpush

@push('scripts')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endpush
@endsection
