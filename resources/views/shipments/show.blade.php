@extends('layouts.app')

@section('content')
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
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endpush
