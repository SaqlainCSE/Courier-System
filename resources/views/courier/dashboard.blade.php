@extends('layouts.app')

@section('content')
@php use Illuminate\Support\Str; @endphp

<div class="container py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold">
            <i class="fas fa-shipping-fast text-danger me-2"></i> StepUp<span class="text-danger">Courier</span>
        </h1>
        <p class="text-muted">Fast • Reliable • Secure Deliveries</p>
    </div>
    <h3 class="fw-bold"><i class="fas fa-tachometer-alt me-2"></i> Delivery Dashboard</h3>
    <div class="row mb-4 mt-4">
    <!-- Today's Earnings -->
    <div class="col-6 col-md-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack fs-3 text-success mb-1"></i>
                <h6 class="text-muted text-uppercase small mb-2">Today’s Earnings</h6>
                <h4 class="fw-bold text-success">৳ {{ number_format($todayEarnings, 2) }}</h4>
            </div>
        </div>
    </div>

    <!-- Last 30 Days Earnings -->
    <div class="col-6 col-md-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-calendar3 fs-3 text-primary mb-1"></i>
                <h6 class="text-muted text-uppercase small mb-2">Last 30 Days</h6>
                <h4 class="fw-bold text-primary">৳ {{ number_format($lastMonthEarnings, 2) }}</h4>
            </div>
        </div>
    </div>

    <!-- New Assignments (Clickable) -->
    <div class="col-6 col-md-3 mb-3">
        <a href="{{ url('courier/dashboard?status=assigned') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-bag-check fs-3 text-primary mb-1"></i>
                    <h6 class="text-muted text-uppercase small mb-2">New Assignments</h6>
                    <h4 class="fw-bold text-primary">{{ $newAssignments }}</h4>
                </div>
            </div>
        </a>
    </div>

    <!-- Delivered Assignments -->
    <div class="col-6 col-md-3 mb-3">
        <a href="{{ url('courier/dashboard?status=delivered') }}" class="text-decoration-none">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-check2-circle fs-3 text-success mb-1"></i>
                <h6 class="text-muted text-uppercase small mb-2">Delivered</h6>
                <h4 class="fw-bold text-success">{{ $deliveredAssignments }}</h4>
            </div>
        </div>
        </a>
    </div>

    <!-- Partially Delivered -->
    <div class="col-6 col-md-3 mb-3">
        <a href="{{ url('courier/dashboard?status=partially_delivered') }}" class="text-decoration-none">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-hourglass-split fs-3 text-info mb-1"></i>
                <h6 class="text-muted text-uppercase small mb-2">Partially Delivered</h6>
                <h4 class="fw-bold text-info">{{ $partiallyDeliveredAssignments }}</h4>
            </div>
        </div>
        </a>
    </div>

    <!-- In Transit -->
    <div class="col-6 col-md-3 mb-3">
        <a href="{{ url('courier/dashboard?status=in_transit') }}" class="text-decoration-none">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-truck fs-3 text-warning mb-1"></i>
                <h6 class="text-muted text-uppercase small mb-2">In Transit</h6>
                <h4 class="fw-bold text-warning">{{ $inTransitAssignments }}</h4>
            </div>
        </div>
        </a>
    </div>

    <!-- Cancelled -->
    <div class="col-6 col-md-3 mb-3">
        <a href="{{ url('courier/dashboard?status=cancelled') }}" class="text-decoration-none">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-x-circle fs-3 text-danger mb-1"></i>
                <h6 class="text-muted text-uppercase small mb-2">Cancelled</h6>
                <h4 class="fw-bold text-danger">{{ $cancelledAssignments }}</h4>
            </div>
        </div>
        </a>
    </div>

    <!-- Hold -->
    <div class="col-6 col-md-3 mb-3">
        <a href="{{ url('courier/dashboard?status=hold') }}" class="text-decoration-none">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-pause-circle fs-3 text-dark mb-1"></i>
                <h6 class="text-muted text-uppercase small mb-2">Hold</h6>
                <h4 class="fw-bold text-dark">{{ $holdAssignments }}</h4>
            </div>
        </div>
        </a>
    </div>
</div>



    <!-- Assignments Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="mb-0 fw-bold text-dark">📦 Your Assignments</h3>
            <small class="text-muted">Latest updates appear first • Showing {{ $assignments->count() }} of {{ $assignments->total() }} assignments</small>
        </div>

        <div class="d-flex gap-2">
            <form class="d-flex" method="GET" action="{{ route('courier.dashboard') }}">
                <input name="q" value="{{ request('q') }}" class="form-control form-control-sm me-2 shadow-sm" placeholder="🔍 Search tracking, address or name" />
                <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
            </form>

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterStatus" data-bs-toggle="dropdown" aria-expanded="false">
                    Filter
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="filterStatus">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => '']) }}">All</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'assigned']) }}">Assigned</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'picked']) }}">Picked</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'hold']) }}">Hold</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'delivered']) }}">Delivered</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'partially_delivered']) }}">Partially Delivered</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'cancelled']) }}">Cancelled</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'in_transit']) }}">In Transit</a></li>
                </ul>
            </div>

            <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-dark" title="Refresh">Clear</a>
            <a href="{{ route('shipments.print.all') }}" target="_blank" class="btn btn-sm btn-outline-success">Print</a>
        </div>
    </div>

    <!-- Empty State -->
    @if($assignments->total() === 0)
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-box-seam display-4 text-muted"></i>
            </div>
            <h5 class="mb-2 fw-bold">No assignments yet</h5>
            <p class="text-muted mb-0">You're all caught up. New assignments will appear here when available.</p>
        </div>
    @else
        <div class="row gy-4">
            @foreach($assignments as $assignment)
                @php
                    $status = $assignment->status;
                    $badgeClass = match($status) {
                        'pending'    => 'bg-secondary',
                        'assigned'   => 'bg-info text-dark',
                        'picked'     => 'bg-primary',
                        'in_transit' => 'bg-warning text-dark',
                        'delivered'  => 'bg-success',
                        'hold'       => 'bg-dark',
                        'partially_delivered' => 'bg-secondary',
                        'cancelled'  => 'bg-danger',
                        default      => 'bg-secondary'
                    };
                    $progress = match($status) {
                        'pending' => 10,
                        'assigned' => 35,
                        'picked' => 55,
                        'in_transit' => 75,
                        'delivered' => 100,
                        'hold' => 80,
                        'partially_delivered' => 90,
                        'cancelled' => 0,
                        default => 0
                    };
                @endphp

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 hover-shadow">
                        <!-- Header -->
                        <div class="card-header p-3 d-flex justify-content-between align-items-start text-white" style="background: linear-gradient(135deg, #1a1e24, #486e64);">
                            <div>
                                <div class="small">Tracking#: {{ $assignment->tracking_number }}</div>
                                <div class="fw-semibold">{{ $assignment->customer->name ?? '-' }} → {{ $assignment->drop_name }}</div>
                                <div class="small opacity-75">{{ $assignment->created_at?->format('d M Y • H:i') }}</div>
                            </div>
                        </div>
                        <div class="small mt-2 text-end me-3">
                                <span class="badge {{ $badgeClass }} fs-6 rounded-pill">{{ Str::of($status)->replace('_', ' ')->title() }}</span>
                        </div>

                        <!-- Body -->
                        <div class="card-body d-flex flex-column">
                            <!-- Pickup (Customer Info) -->
                            <div class="mb-2">
                                <div class="small text-uppercase text-muted fw-bold mb-1">
                                    <i class="bi bi-building-up"></i> Pickup
                                </div>
                                <div class="fw-medium">{{ $assignment->customer->business_name ?? '-' }}</div>
                                <div class="text-muted small">{{ $assignment->customer->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $assignment->customer->phone ?? '-' }}</div>
                                <div class="text-muted small">{{ $assignment->customer->business_address ?? '-' }}</div>
                            </div>

                            <!-- Dropoff -->
                            <div class="mb-3">
                                <div class="small text-uppercase text-muted fw-bold mb-1"><i class="bi bi-geo-alt-fill"></i> Dropoff</div>
                                <div class="fw-medium">{{ $assignment->drop_name }}</div>
                                <div class="text-muted small">{{ $assignment->drop_phone }}</div>
                                <div class="text-muted small">{{ $assignment->drop_address }}</div>
                            </div>

                            <!-- Weight & Price -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <div>Weight</div><div>Price</div>
                                </div>
                                <div class="d-flex justify-content-between fw-semibold">
                                    <div>{{ $assignment->weight_kg }} kg</div>
                                    <div>৳ {{ number_format($assignment->price, 2) }}</div>
                                </div>
                            </div>

                            <!-- Progress -->
                            <div class="mb-3">
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar {{ $badgeClass }}" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <div>Status: {{ ucwords(str_replace('_',' ', $status)) }}</div>
                                    <div>{{ $assignment->estimated_delivery_at?->diffForHumans() ?? '-' }}</div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-auto">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('courier.shipments.show', $assignment) }}" class="btn btn-sm btn-outline-secondary flex-grow-1">Details</a>
                                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#quickUpdate-{{ $assignment->id }}">
                                        Quick Update
                                    </button>
                                </div>

                                <!-- Quick Update Form -->
                                <div class="collapse mt-3" id="quickUpdate-{{ $assignment->id }}">
                                    <form action="{{ route('courier.shipments.updateStatus', $assignment) }}" method="POST" class="row g-2">
                                        @csrf
                                        <div class="col-7">
                                            <select name="status" class="form-select form-select-sm status-select" data-id="{{ $assignment->id }}" required>
                                                <option value="picked" @selected($assignment->status=='picked')>Picked</option>
                                                <option value="delivered" @selected($assignment->status=='delivered')>Delivered</option>
                                                <option value="hold" @selected($assignment->status=='hold')>Hold</option>
                                                <option value="partially_delivered" @selected($assignment->status=='partially_delivered')>Partially Delivered</option>
                                                <option value="cancelled" @selected($assignment->status=='cancelled')>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-5">
                                            <button class="btn btn-sm btn-success w-100" type="submit">Apply</button>
                                        </div>

                                        {{-- Note field --}}
                                        <div class="col-12">
                                            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Optional note"></textarea>
                                        </div>

                                        {{-- Partial Price Field (hidden by default) --}}
                                        <div class="col-12 partial-price d-none" id="partial-price-{{ $assignment->id }}">
                                            <input type="number" name="partial_price" class="form-control form-control-sm" placeholder="Enter received amount (e.g. 1000)" min="0" step="0.01">
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div><!-- card-body -->
                    </div><!-- card -->
                </div><!-- col -->
            @endforeach
        </div><!-- row -->

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $assignments->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.status-select').forEach(function(select){
    select.addEventListener('change', function(){
        let id = this.getAttribute('data-id');
        let partialBox = document.getElementById('partial-price-' + id);

        if(this.value === 'partially_delivered'){
            partialBox.classList.remove('d-none');
        } else {
            partialBox.classList.add('d-none');
            partialBox.querySelector('input').value = ''; // clear input if hidden
        }
    });
});
</script>
@endpush

