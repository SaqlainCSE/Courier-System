@extends('layouts.app')

@section('content')

<style>
/* --- Progress Tracker Styles --- */
.progress-tracker {
    position: relative;
    margin: 50px 20px;
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

<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-shipping-fast text-danger me-2"></i>
            StepUp <span class="text-danger">Courier</span> - Shipment Details</h2>
        <a href="{{ route('shipments.dashboard') }}" class="btn btn-outline-dark shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body">
            <!-- Tracking & Status -->
            <div class="row g-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Tracking #:</strong>
                        <span class="text-danger fs-6">{{ $shipment->tracking_number }}</span>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
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
                    <p class="mb-0"><strong>Status:</strong>
                        <span class="badge bg-{{ $statusColors[$shipment->status] }} fs-6 px-3 py-2">
                            {{ ucfirst(str_replace('_',' ', $shipment->status)) }}
                        </span>
                    </p>
                </div>
            </div>
            <hr>

            <!-- Shipment Progress Tracker -->
            @php
                if ($shipment->status === 'cancelled') {
                    $statuses = ['cancelled'];
                } else {
                    $statuses = ['pending','assigned','picked','in_transit','delivered'];
                }

                $statusLabels = [
                    'pending'     => 'Pending',
                    'assigned'    => 'Assigned',
                    'picked'      => 'Picked Up',
                    'in_transit'  => 'On The Way',
                    'delivered'   => 'Delivered',
                    'cancelled'   => 'Cancelled',
                ];

                $icons = [
                    'pending'     => 'fa-hourglass-start',
                    'assigned'    => 'fa-user-check',
                    'picked'      => 'fa-box',
                    'in_transit'  => 'fa-truck-moving',
                    'delivered'   => 'fa-flag-checkered',
                    'cancelled'   => 'fa-times-circle'
                ];

                $colors = [
                    'pending'     => '#ffc107',
                    'assigned'    => '#0dcaf0',
                    'picked'      => '#0d6efd',
                    'in_transit'  => '#6f42c1',
                    'delivered'   => '#198754',
                    'cancelled'   => '#dc3545'
                ];

                $currentIndex = array_search($shipment->status, $statuses);
                if ($currentIndex === false) $currentIndex = 0;
            @endphp

            <div class="progress-tracker">
                <div class="progress-bar"></div>

                <div class="steps d-flex justify-content-between">
                    @foreach($statuses as $index => $status)
                        <div class="step {{ $index <= $currentIndex ? 'active' : '' }}">
                            <div class="circle"
                                style="border-color: {{ $colors[$status] }};
                                       background: {{ $index <= $currentIndex ? $colors[$status] : '#fff' }};
                                       color: {{ $index <= $currentIndex ? '#fff' : $colors[$status] }};">
                                <i class="fas {{ $icons[$status] }}"></i>
                            </div>
                            <p style="color: {{ $index <= $currentIndex ? $colors[$status] : '#6c757d' }}">
                                {{ $statusLabels[$status] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
            <br>

            <div class="row g-4">
                <!--Delivery Man-->
                <div class="col-md-6">
                    <h5 class="fw-bold text-primary"><i class="fas fa-location-arrow me-1"></i>Delivery Man</h5>
                    <p class="mb-1">{{ $costDetails['deliveryManName'] }} - {{ $costDetails['deliveryManPhone'] }}</p>
                </div>
                <!--Dropoff-->
                <div class="col-md-6">
                    <h5 class="fw-bold text-success"><i class="fas fa-map-marker-alt me-1"></i>Dropoff</h5>
                    <p class="mb-1">{{ $shipment->drop_name }} - {{ $shipment->drop_phone }}</p>
                    <p class="mb-0">{{ $shipment->drop_address }}</p>
                </div>
            </div>
            <hr>

            <!-- Details -->
            <div class="row g-4">
                <div class="col-md-4">
                    <p><strong>Weight:</strong> {{ $shipment->weight_kg }} kg</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Amount:</strong> ৳ {{ number_format($shipment->price, 2) }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Booked At:</strong> {{ $shipment->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            @if($shipment->notes)
                <div class="mt-3">
                    <h6 class="fw-bold text-secondary"><i class="fas fa-sticky-note me-1"></i>Notes</h6>
                    <p class="mb-0">{{ $shipment->notes }}</p>
                </div>
            @endif

            <!-- Delivery Cost Details -->
            <div class="mt-4 p-4 border rounded bg-light shadow-sm">
                <h5 class="fw-bold mb-3 text-danger"><i class="fas fa-money-bill-wave me-1"></i>Cost Breakdown</h5>

                <div class="d-flex justify-content-between mb-2">
                    <span>Product Price</span>
                    <span>৳ {{ number_format($shipment->price, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Delivery Charge</span>
                    <span class="text-danger">-৳ {{ number_format(60, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Additional Charge</span>
                    <span class="text-danger">-৳ {{ number_format($shipment->additional_charge, 2) }}</span> </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Payable Amount</span>
                    <span class="text-success">৳ {{ number_format($shipment->balance_cost, 2) }}</span>
                </div>
            </div><br>
            @if($shipment->status === 'pending')
                   <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-sm btn-outline-warning">Edit Shipment
                    </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let currentIndex = parseInt(@json($currentIndex), 10);
    let totalSteps = @json(count($statuses) - 1);
    const progressBar = document.querySelector(".progress-bar");

    if (!progressBar) return;

    if (totalSteps <= 0) {
        progressBar.style.display = 'none';
        return;
    }

    let progress = (currentIndex / totalSteps) * 100;
    if (!isFinite(progress) || progress < 0) progress = 0;
    if (progress > 100) progress = 100;

    progressBar.style.width = progress + "%";
});
</script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endpush
