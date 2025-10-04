@extends('layouts.print') {{-- create a minimal print layout or use layouts.app with print styles --}}

@section('content')
<div class="p-4">
    <h4>Shipment #{{ $shipment->tracking_number }}</h4>
    <p class="text-muted mb-2">Created: {{ $shipment->created_at?->format('d M Y • H:i') }}</p>

    <div class="row">
        <div class="col-6">
            <h6 class="small text-uppercase text-muted">Pickup</h6>
            <div>{{ $shipment->pickup_name }} • {{ $shipment->pickup_phone }}</div>
            <div class="text-muted small">{{ $shipment->pickup_address }}</div>
        </div>
        <div class="col-6">
            <h6 class="small text-uppercase text-muted">Dropoff</h6>
            <div>{{ $shipment->drop_name }} • {{ $shipment->drop_phone }}</div>
            <div class="text-muted small">{{ $shipment->drop_address }}</div>
        </div>
    </div>

    <hr />

    <div class="d-flex justify-content-between">
        <div>Weight: <strong>{{ $shipment->weight_kg }} kg</strong></div>
        <div>Price: <strong>৳ {{ number_format($shipment->price, 2) }}</strong></div>
    </div>

    <hr />

    <div>
        <strong>Status:</strong> {{ ucwords(str_replace('_',' ', $shipment->status)) }}
    </div>

    @if($shipment->notes)
        <hr />
        <div><strong>Notes</strong><div class="mt-1">{{ $shipment->notes }}</div></div>
    @endif
</div>
@endsection
