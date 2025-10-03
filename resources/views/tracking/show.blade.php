@extends('layouts.app')

@section('content')
<div class="container py-5">
    <a href="/" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>

    <div class="card shadow border-0 rounded-3">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-shipping-fast text-success me-2"></i>
                        Tracking: <span class="text-danger">{{ $shipment->tracking_number }}</span>
                    </h4>
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Booked at: {{ $shipment->created_at->format('d M Y, H:i') }}
                    </small>
                </div>

                <!-- Status Badge -->
                <div class="text-end">
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'assigned' => 'info',
                            'picked' => 'primary',
                            'in_transit' => 'secondary',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $statusIcons = [
                            'pending' => 'fas fa-hourglass-start',
                            'assigned' => 'fas fa-user-check',
                            'picked' => 'fas fa-box',
                            'in_transit' => 'fas fa-truck-moving',
                            'delivered' => 'fas fa-check-circle',
                            'cancelled' => 'fas fa-times-circle'
                        ];
                        $badge = $statusColors[$shipment->status] ?? 'secondary';
                        $icon = $statusIcons[$shipment->status] ?? 'fas fa-info-circle';
                    @endphp
                    <span class="badge bg-{{ $badge }} py-2 px-3 fs-6">
                        <i class="{{ $icon }} me-1"></i>
                        {{ ucfirst(str_replace('_',' ', $shipment->status)) }}
                    </span>
                </div>
            </div>

            <!-- Progress Bar -->
            @php
                $statuses = ['pending','assigned','picked','in_transit','delivered'];
                $currentIndex = array_search($shipment->status, $statuses);
                $progressPercent = $currentIndex === false ? 0 : (($currentIndex + 1) / count($statuses)) * 100;
            @endphp

            <div class="mb-5">
                <div class="progress" style="height: 14px; border-radius: 10px;">
                    <div class="progress-bar progress-bar-striped bg-success"
                         role="progressbar"
                         style="width: {{ $progressPercent }}%;"
                         aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2 small fw-semibold">
                    @foreach($statuses as $st)
                        <div class="{{ (array_search($st, $statuses) <= $currentIndex) ? 'text-success' : 'text-muted' }}">
                            {{ ucfirst(str_replace('_',' ', $st)) }}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipment Details -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="p-3 rounded bg-light shadow-sm h-100">
                        @if(isset($deliveryManName))
                            <h6 class="fw-bold"><i class="fas fa-truck text-primary me-2"></i> Delivery Man</h6>
                            <p class="mb-1">
                                <i class="fas fa-user me-1"></i>{{ $deliveryManName }} — {{ $deliveryManPhone }}
                            </p>
                        @else
                            <p class="text-muted mb-0"><i class="fas fa-truck me-1"></i>No delivery man assigned yet.</p>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded bg-light shadow-sm h-100">
                        <h6 class="fw-bold"><i class="fas fa-location-arrow text-danger me-2"></i> Pickup</h6>
                        <p class="mb-1"><i class="fas fa-user me-1"></i>{{ $customerName }} - {{ $customerPhone }}</p>
                        <p class="text-muted small mb-0"><i class="fas fa-location-dot me-1"></i>{{ $customerAddress }}</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded bg-light shadow-sm h-100">
                        <h6 class="fw-bold"><i class="fas fa-map-marker-alt text-success me-2"></i> Dropoff</h6>
                        <p class="mb-1"><i class="fas fa-user me-1"></i>{{ $shipment->drop_name }} — {{ $shipment->drop_phone }}</p>
                        <p class="text-muted small mb-0"><i class="fas fa-location-dot me-1"></i>{{ $shipment->drop_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Activity Logs -->
            <h5 class="fw-bold mb-3"><i class="fas fa-history me-2 text-primary"></i> Activity Log</h5>

            @if($logs->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i> No status updates yet.
                </div>
            @else
                <div class="timeline">
                    @foreach($logs as $log)
                        <div class="card mb-3 shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><i class="fas fa-circle-check text-success me-2"></i>{{ ucfirst(str_replace('_',' ', $log->status)) }}</strong>
                                    @if(!empty($log->note))
                                        <div class="text-muted small mt-1">{{ $log->note }}</div>
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Footer Note -->
            <div class="mt-4 text-muted small">
                <i class="fas fa-life-ring me-1"></i>
                If the tracking number is not found, please check again or contact StepUp Courier support.
            </div>
        </div>
    </div>
</div>
@endsection
