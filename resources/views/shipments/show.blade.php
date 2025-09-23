@extends('layouts.app')

@section('content')

<style>
.progress-tracker {
    position: relative;
    margin: 40px 20px;
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
    background: #46c43a;
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
}

.step .circle {
    width: 50px;
    height: 50px;
    margin: 0 auto;
    border-radius: 50%;
    border: 3px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.4s ease;
}

.step.active .circle {
    transform: scale(1.1);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-box-open me-2"></i>Shipment Details</h2>
        <a href="{{ route('shipments.dashboard') }}" class="btn btn-secondary shadow-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Tracking & Status -->
            <div class="row g-4">
                <div class="col-md-6">
                    <p><strong>Tracking #:</strong> <span class="text-danger">{{ $shipment->tracking_number }}</span></p>
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
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $statusColors[$shipment->status] }} py-2 px-3">
                            {{ ucfirst(str_replace('_',' ', $shipment->status)) }}
                        </span>
                    </p>
                </div>
            </div>
            <hr>

            <!-- Shipment Progress Tracker -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-4"><i class="fas fa-route me-2"></i>Delivery Progress</h5>

                    @php
                        $statuses = ['cancelled','pending','assigned','picked','in_transit','delivered'];

                        $statusLabels = [
                            'cancelled' => 'Cancelled',
                            'pending' => 'Pending',
                            'assigned' => 'Assigned',
                            'picked' => 'Picked Up',
                            'in_transit' => 'On The Way',
                            'delivered' => 'Delivered'
                        ];

                        $icons = [
                            'cancelled' => 'fa-times-circle',
                            'pending' => 'fa-hourglass-start',
                            'assigned' => 'fa-user-check',
                            'picked' => 'fa-box',
                            'in_transit' => 'fa-truck-moving',
                            'delivered' => 'fa-flag-checkered'
                        ];

                        $colors = [
                            'cancelled' => '#dc3545',   // red
                            'pending'   => '#ffc107',   // yellow
                            'assigned'  => '#0dcaf0',   // cyan
                            'picked'    => '#0d6efd',   // blue
                            'in_transit'=> '#6f42c1',   // purple
                            'delivered' => '#198754'    // green
                        ];

                        $currentIndex = array_search($shipment->status, $statuses);
                    @endphp

                    <div class="progress-tracker">
                        <div class="progress-bar"></div>
                        <div class="steps d-flex justify-content-between">
                            @foreach($statuses as $index => $status)
                                <div class="step text-center {{ $index <= $currentIndex ? 'active' : '' }}">
                                    <div class="circle" style="border-color: {{ $colors[$status] }};
                                                            background: {{ $index <= $currentIndex ? $colors[$status] : '#f8f9fa' }};
                                                            color: {{ $index <= $currentIndex ? '#fff' : $colors[$status] }}">
                                        <i class="fas {{ $icons[$status] }}"></i>
                                    </div>
                                    <p class="mt-2 small fw-semibold" style="color: {{ $index <= $currentIndex ? $colors[$status] : '#6c757d' }}">
                                        {{ $statusLabels[$status] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <br>

            <!-- Pickup & Dropoff -->
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="fw-bold text-primary"><i class="fas fa-location-arrow me-1"></i>Pickup</h5>
                    <p class="mb-1">{{ $shipment->pickup_name }} - {{ $shipment->pickup_phone }}</p>
                    <p class="mb-0">{{ $shipment->pickup_address }}</p>
                </div>
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
                    <p><strong>Price:</strong> ৳ {{ number_format($shipment->price, 2) }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Booked At:</strong> {{ $shipment->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            @if($shipment->notes)
            <div class="mt-3">
                <h6 class="fw-bold text-secondary"><i class="fas fa-sticky-note me-1"></i>Notes</h6>
                <p>{{ $shipment->notes }}</p>
            </div>
            @endif

            <!-- Delivery Cost Details -->
            <div class="mt-4 p-3 border rounded bg-light">
                <h5 class="fw-bold text-danger mb-3"><i class="fas fa-money-bill-wave me-1"></i>Delivery Cost Details</h5>
                @php
                    $basePrice = 60;
                    $additional = max(0, ceil($shipment->weight_kg - 1) * 10);
                    $totalPrice = $basePrice + $additional;
                @endphp
                <p><strong>Base Price (up to 1 kg):</strong> ৳ {{ $basePrice }}</p>
                <p><strong>Additional Weight Charges:</strong> ৳ {{ $additional }} ({{ max(0, $shipment->weight_kg - 1) }} kg × 10)</p>
                <hr>
                <p class="fw-bold"><strong>Total Delivery Price:</strong> ৳ {{ $totalPrice }}</p>
            </div>

            @if($shipment->status === 'pending')
            <form action="{{ route('shipments.cancel', $shipment) }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-times me-1"></i>Cancel Shipment</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let steps = @json($statuses);
    let currentIndex = {{ $currentIndex }};
    let totalSteps = steps.length - 1;
    let progress = (currentIndex / totalSteps) * 100;
    document.querySelector(".progress-bar").style.width = progress + "%";
});
</script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endpush
