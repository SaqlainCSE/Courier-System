@extends('layouts.app')

@section('content')
<div class="container py-5">

    <!-- Branding / Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold">
            <i class="fas fa-shipping-fast text-danger me-2"></i> StepUp<span class="text-danger">Courier</span>
        </h1>
        <p class="text-muted">Fast • Reliable • Secure Deliveries</p>
    </div>

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-tachometer-alt me-2"></i> Shipment Dashboard</h3>
        <a href="{{ route('shipments.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus me-1"></i> New Shipment
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                'pending' => ['color' => 'warning', 'icon' => 'hourglass-half', 'label' => 'Pending'],
                'assigned' => ['color' => 'info', 'icon' => 'user-check', 'label' => 'Assigned'],
                'picked' => ['color' => 'primary', 'icon' => 'box-open', 'label' => 'Picked'],
                'in_transit' => ['color' => 'primary', 'icon' => 'truck-moving', 'label' => 'In Transit'],
                'delivered' => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Delivered'],
                'hold' => ['color' => 'secondary', 'icon' => 'pause-circle', 'label' => 'On Hold'],
                'cancelled' => ['color' => 'danger', 'icon' => 'times-circle', 'label' => 'Cancelled'],
                'partially_delivered' => ['color' => 'dark', 'icon' => 'clipboard-check', 'label' => 'Partially Delivered'],
            ];
        @endphp

        @foreach($cards as $key => $card)
            <div class="col-6 col-md-3">
                <div class="text-decoration-none shipment-card"
                    data-status="{{ $key }}"
                    data-label="{{ $card['label'] }}"
                    style="cursor:pointer;">
                    <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3 hover-card">
                        <i class="fas fa-{{ $card['icon'] }} fa-2x text-{{ $card['color'] }} mb-2"></i>
                        <h6 class="fw-bold text-muted">{{ $card['label'] }}</h6>
                        <h4 class="fw-bold text-{{ $card['color'] }}">{{ $summary[$key] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Entry Balance -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3 hover-card">
                <i class="fas fa-inbox fa-2x text-info mb-2"></i>
                <h6 class="fw-bold text-muted">Entry Balance</h6>
                <h4 class="fw-bold text-info">৳ {{ number_format($entryBalance, 2) }}</h4>
            </div>
        </div>

        <!-- COD (Cash On Delivery) Balance -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3 hover-card">
                <i class="fas fa-coins fa-2x text-warning mb-2"></i>
                <h6 class="fw-bold text-muted">COD Balance</h6>
                <h4 class="fw-bold text-warning">৳ {{ number_format($codBalance, 2) }}</h4>
            </div>
        </div>

        <!-- Paid Amount -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3 hover-card">
                <i class="fas fa-handshake fa-2x text-success mb-2"></i>
                <h6 class="fw-bold text-muted">Paid Amount</h6>
                <h4 class="fw-bold text-success">৳ {{ number_format($paidAmount, 2) }}</h4>
            </div>
        </div>

        <!-- Remaining Balance -->
        {{--  <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3 hover-card">
                <i class="fas fa-balance-scale fa-2x text-danger mb-2"></i>
                <h6 class="fw-bold text-muted">New COD</h6>
                <h4 class="fw-bold text-danger">৳ {{ number_format($newCOD, 2) }}</h4>
            </div>
        </div>  --}}
    </div>

    <!-- Monthly Wise Cost -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-gradient text-dark fw-bold rounded-top-4">
            <i class="fas fa-calendar-alt me-2"></i> Monthly Balance Breakdown
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyCosts as $month)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($month->month.'-01')->format('F Y') }}</td>
                                <td><span class="fw-bold text-success">৳ {{ number_format($month->total, 2) }}</span></td>
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
    </div>

    <!-- All Shipments -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="mb-3 d-flex gap-2 mt-4">
            <a href="{{ route('shipments.export.excel', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('shipments.export.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('shipments.invoices') }}" class="btn btn-warning">
                <i class="fas fa-file-invoice"></i> Payment Invoices
            </a>
        </div>

        <div class="col-md-12 mt-4">
            <form method="GET" action="{{ route('shipments.dashboard') }}" class="row g-2 mb-3 align-items-center">

                <!-- Search -->
                <div class="col-md-3">
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                        class="form-control form-control-sm"
                        placeholder="🔍 Search tracking, address, name or phone">
                </div>

                <!-- Status Dropdown -->
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" @selected(($filters['status'] ?? '')=='pending')>Pending</option>
                        <option value="assigned" @selected(($filters['status'] ?? '')=='assigned')>Assigned</option>
                        <option value="picked" @selected(($filters['status'] ?? '')=='picked')>Picked</option>
                        <option value="in_transit" @selected(($filters['status'] ?? '')=='in_transit')>In Transit</option>
                        <option value="delivered" @selected(($filters['status'] ?? '')=='delivered')>Delivered</option>
                        <option value="partially_delivered" @selected(($filters['status'] ?? '')=='partially_delivered')>Partially Delivered</option>
                        <option value="hold" @selected(($filters['status'] ?? '')=='hold')>Hold</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '')=='cancelled')>Cancelled</option>
                        <option value="paid" @selected(($filters['status'] ?? '')=='paid')>Paid</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm"
                        value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm"
                        value="{{ $filters['end_date'] ?? '' }}">
                </div>

                <!-- Actions -->
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                    <a href="{{ route('shipments.dashboard') }}" class="btn btn-sm btn-outline-dark w-100">Clear</a>
                </div>
            </form>
        </div>

        <div class="card-header bg-dark text-white fw-bold rounded-top-4">
            <i class="fas fa-boxes me-2"></i> All Shipments
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" id="shipmentTable">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tracking #</th>
                            {{-- <th>Pickup</th> --}}
                            <th>Drop Address</th>
                            <th>Weight</th>
                            <th>Status</th>
                            <th>Delivery Cost</th>
                            <th>Amount</th>
                            <th>Partial Amount</th>
                            <th>Booked At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($shipments as $shipment)
                        <tr>
                            <td>{{ $shipment->tracking_number }}</td>
                            {{-- <td>
                                {{ $shipment->pickup_name }}<br>
                                <small class="text-muted">{{ Str::limit($shipment->pickup_address,30) }}</small>
                            </td> --}}
                            <td>
                                {{ $shipment->drop_name }}<br>
                                <small class="text-muted">{{ Str::limit($shipment->drop_address,30) }}</small>
                            </td>
                            <td>{{ $shipment->weight_kg }} kg</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'assigned' => 'info',
                                        'picked' => 'primary',
                                        'in_transit' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        'hold' => 'secondary',
                                        'partially_delivered' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$shipment->status] }}">
                                    {{ ucfirst(str_replace('_',' ', $shipment->status)) }}
                                </span>

                                @if($shipment->is_paid)
                                    <br>
                                    <span class="badge bg-success mt-1">
                                        <i class="fas fa-check-circle me-1"></i> Paid
                                    </span>
                                @endif
                            </td>
                            <td>৳ {{ number_format($shipment->additional_charge + $shipment->user->delivery_fee ?? 60, 2) }}</td>
                            <td class="fw-bold text-success">৳ {{ number_format($shipment->price ) }}</td>

                            @if($shipment->partial_price > 0)
                                <td>৳ {{ number_format($shipment->partial_price, 2) }}</td>
                            @else
                                <td class="text-muted">৳ 0.00</td>
                            @endif

                            <td>{{ $shipment->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-sm btn-outline-info">View</a>
                                @if($shipment->status === 'pending')
                                        <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-sm btn-outline-warning">Edit
                                        </a>
                                @endif
                                {{-- @if($shipment->status === 'pending')
                                    <form action="{{ route('shipments.cancel', $shipment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
                                @endif --}}

                                {{--  @if($shipment->payments && $shipment->payments->isNotEmpty() && $shipment->balance_cost <= 0)
                                    <a href="{{ route('admin.payments.invoice', $shipment->payments->last()->id) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-danger mt-1">
                                        <i class="fas fa-file-invoice"></i> Invoice
                                    </a>
                                @endif  --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-truck-loading fa-2x mb-2"></i><br>No shipments yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

                    <!-- Pagination -->
                    <div class="mt-3 d-flex justify-content-center flex-wrap">
                        {{ $shipments->links('pagination::bootstrap-5') }}
                    </div>
        </div>
    </div>
</div>

<!-- Shipment Modal -->
<div class="modal fade" id="shipmentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Shipments</h5>
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

@endsection

@push('styles')
<style>
    .btn-gradient {
        background: linear-gradient(45deg, #4facfe, #00f2fe);
        color: #fff;
        font-weight: bold;
        border: none;
    }
    .btn-gradient:hover {
        opacity: 0.9;
        color: #fff;
    }
    .bg-gradient {
        background: linear-gradient(45deg, #4facfe, #00f2fe);
    }
    .hover-card:hover {
        transform: translateY(-5px);
        transition: 0.3s;
    }
</style>
@endpush

@push('scripts')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<script>
    document.querySelectorAll('.shipment-card').forEach(card => {
        card.addEventListener('click', function () {

            let status = this.dataset.status;
            let label = this.dataset.label;

            let url = "{{ route('shipments.dashboard') }}?status=" + status;

            let modal = new bootstrap.Modal(document.getElementById('shipmentModal'));
            modal.show();

            document.getElementById('modalTitle').innerText = label + " Shipments";

            document.getElementById('modalContent').innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            `;

            fetch(url)
                .then(res => res.text())
                .then(html => {

                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');

                    // ✅ ONLY shipment table
                    let table = doc.querySelector('#shipmentTable');

                    document.getElementById('modalContent').innerHTML =
                        table ? table.outerHTML : '<p>No data found</p>';
                });
        });
    });
</script>

@endpush
