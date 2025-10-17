@extends('layouts.admin')

@section('content')
<style>
/* --- Progress Tracker Styles --- */
.progress-tracker {
    position: relative;
    margin: 30px 0 50px 0;
}

.progress-tracker::before {
    content: "";
    position: absolute;
    top: 25px;
    left: 0;
    width: 100%;
    height: 6px;
    border-radius: 10px;
    background: #e9ecef;
    z-index: 1;
}

.progress-bar {
    position: absolute;
    top: 25px;
    left: 0;
    height: 6px;
    border-radius: 10px;
    background: linear-gradient(90deg, #0d6efd, #20c997);
    width: 0;
    z-index: 2;
    transition: width 0.6s ease;
}

.steps {
    position: relative;
    z-index: 3;
}

.step {
    flex: 1;
    text-align: center;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.step:hover {
    transform: scale(1.05);
}

.step .circle {
    width: 55px;
    height: 55px;
    margin: 0 auto;
    border-radius: 50%;
    border: 3px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    transition: all 0.4s ease;
    background: #fff;
}

.step.active .circle {
    transform: scale(1.1);
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    color: #fff;
}

.step p {
    margin-top: 8px;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-shipping-fast text-danger me-2"></i>
            Tracking #<span class="text-danger">{{ $shipment->tracking_number }}
        </h4>
        <a href="{{ url()->previous() }}" class="btn btn-outline-dark">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <!-- Shipment Status & Tracker -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div><strong>Status:</strong>
                    @php
                        $statusColors = [
                            'pending'=>'warning','assigned'=>'info','picked'=>'primary',
                            'in_transit'=>'primary','delivered'=>'success','cancelled'=>'danger',
                            'hold'=>'secondary','partially_delivered'=>'dark'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$shipment->status] }}">
                        {{ ucfirst(str_replace('_',' ', $shipment->status)) }}
                    </span>
                </div>
                <div><strong>Weight:</strong> {{ $shipment->weight_kg }} kg</div>
                <div><strong>Amount:</strong> ৳ {{ $shipment->price }}</div>

            </div>

            @php
                $allStatuses = ['pending','assigned','picked','in_transit','hold','delivered','partially_delivered'];
                if ($shipment->status==='cancelled') $statuses=['cancelled'];
                elseif ($shipment->status==='hold') $statuses=['pending','assigned','picked','in_transit','hold'];
                elseif ($shipment->status==='delivered') $statuses=['pending','assigned','picked','in_transit','delivered'];
                elseif ($shipment->status==='partially_delivered') $statuses=['pending','assigned','picked','in_transit','partially_delivered'];
                else $statuses=array_filter($allStatuses, fn($s)=>$s!=='cancelled');

                $statusLabels=['pending'=>'Pending','assigned'=>'Assigned','picked'=>'Picked Up','in_transit'=>'On The Way',
                               'delivered'=>'Delivered','cancelled'=>'Cancelled','hold'=>'On Hold','partially_delivered'=>'Partially Delivered'];
                $icons=['pending'=>'fa-hourglass-start','assigned'=>'fa-user-check','picked'=>'fa-box',
                        'in_transit'=>'fa-truck-moving','delivered'=>'fa-flag-checkered','cancelled'=>'fa-times-circle',
                        'hold'=>'fa-pause-circle','partially_delivered'=>'fa-clipboard-check'];
                $colors=['pending'=>'#ffc107','assigned'=>'#0dcaf0','picked'=>'#0d6efd','in_transit'=>'#6f42c1',
                        'delivered'=>'#198754','cancelled'=>'#dc3545','hold'=>'#6c757d','partially_delivered'=>'#343a40'];
                $currentIndex=array_search($shipment->status,$statuses);
                if($currentIndex===false) $currentIndex=0;
            @endphp

            <div class="progress-tracker">
                <div class="progress-bar"></div>
                <div class="steps d-flex justify-content-between">
                    @foreach($statuses as $i=>$s)
                        <div class="step {{ $i<=$currentIndex?'active':'' }}">
                            <div class="circle"
                                 style="border-color:{{ $colors[$s] }};
                                        background:{{ $i<=$currentIndex?$colors[$s]:'#fff' }};
                                        color:{{ $i<=$currentIndex?'#fff':$colors[$s] }};">
                                <i class="fas {{ $icons[$s] }}"></i>
                            </div>
                            <p style="color:{{ $i<=$currentIndex?$colors[$s]:'#6c757d' }}">{{ $statusLabels[$s] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Pickup & Dropoff Details -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3 p-3">
                <h5 class="fw-bold text-danger mb-2"><i class="fas fa-location-arrow me-1"></i>Pickup</h5>
                <p class="mb-1"><strong>Name:</strong> {{ $shipment->customer?->business_name ?? '-' }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $shipment->customer?->phone ?? '-' }}</p>
                <p class="text-muted small mb-0"><strong>Address:</strong> {{ $shipment->customer?->business_address ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3 p-3">
                <h5 class="fw-bold text-success mb-2"><i class="fas fa-map-marker-alt me-1"></i>Dropoff</h5>
                <p class="mb-1"><strong>Name:</strong> {{ $shipment->drop_name }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $shipment->drop_phone }}</p>
                <p class="text-muted small mb-0"><strong>Address:</strong> {{ $shipment->drop_address }}</p>
            </div>
        </div>
    </div>

    <!-- Assigned Delivery Man -->
    @if($assignedCourier)
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body d-flex align-items-center">
            <i class="fas fa-user-tie fa-2x text-primary me-3"></i>
            <div>
                <h6 class="mb-1 fw-bold">Assigned Delivery Man</h6>
                <p class="mb-0">{{ $assignedCourier->name }} {{ $assignedCourier->phone ? '('.$assignedCourier->phone.')' : '' }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Assign Courier -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-secondary text-white">Assign Delivery Man</div>
        <div class="card-body">
            <form action="{{ route('admin.shipments.assign',$shipment) }}" method="POST" class="row g-2">
                @csrf
                <div class="col-md-8">
                    <select name="courier_id" class="form-select" required>
                        <option value="">-- Select Delivery Man --</option>
                        @foreach($couriers as $c)
                            <option value="{{ $c->id }}">{{ $c->user->name }} — {{ $c->user->phone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Status -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-success text-white">Update Status</div>
        <div class="card-body">
            <form action="{{ route('admin.shipments.updateStatus', $shipment) }}" method="POST" class="row g-3">
                @csrf

                <!-- Status dropdown -->
                <div class="col-md-4">
                    <select name="status" id="statusSelect" class="form-select">
                        <option value="">-- Change Status --</option>
                        @foreach(['pending','assigned','picked','in_transit','hold','delivered','partially_delivered','cancelled'] as $status)
                            <option value="{{ $status }}" {{ $shipment->status === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_',' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Note textarea -->
                <div class="col-md-8">
                    <textarea name="note" class="form-control" placeholder="Add note (optional)" rows="2"></textarea>
                </div>

                <!-- Partial price input (hidden by default) -->
                <div class="col-md-4" id="partialPriceField" style="display: none;">
                    <input
                        type="number"
                        name="partial_price"
                        class="form-control"
                        placeholder="Enter received amount"
                        min="0"
                        step="0.01"
                    >
                </div>

                <div class="col-12 text-end">
                    <button class="btn btn-success">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs -->
    <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-history me-2 text-primary"></i> Activity Log</h5>
    @if($logs->isEmpty())
        <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i> No status updates yet.</div>
    @else
        <div class="timeline">
            @foreach($logs as $log)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="fas fa-circle-check text-success me-2"></i>{{ ucfirst(str_replace('_',' ', $log->status)) }}</strong>
                                @if(!empty($log->note))<div class="text-muted small mt-1">{{ $log->note }}</div>@endif
                                @if($log->user)
                                    <div class="mt-2 small"><i class="fas fa-user-tie text-info me-1"></i>{{ $log->user->name }} <span class="text-muted">({{ $log->user->phone ?? 'N/A' }})</span></div>
                                @endif
                            </div>
                            <div class="text-muted small"><i class="fas fa-clock me-1"></i>{{ $log->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){
    const progressBar = document.querySelector(".progress-bar");
    let currentIndex = parseInt(@json($currentIndex),10);
    let totalSteps = @json(count($statuses)-1);
    if(!progressBar || totalSteps<=0) return;
    let progress = (currentIndex/totalSteps)*100;
    if(!isFinite(progress)||progress<0) progress=0;
    if(progress>100) progress=100;
    progressBar.style.width = progress+"%";
});
</script>

<!-- Script to toggle partial price field -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect = document.getElementById('statusSelect');
        const partialPriceField = document.getElementById('partialPriceField');

        function togglePartialPriceField() {
            if (statusSelect.value === 'partially_delivered') {
                partialPriceField.style.display = 'block';
            } else {
                partialPriceField.style.display = 'none';
            }
        }

        // Run on page load (in case status is preselected)
        togglePartialPriceField();

        // Run on change
        statusSelect.addEventListener('change', togglePartialPriceField);
    });
</script>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endpush
