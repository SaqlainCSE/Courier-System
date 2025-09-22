@extends('layouts.app')
@section('content')
<h3>Shipment {{ $shipment->tracking_number }}</h3>
<p><strong>Status:</strong> {{ $shipment->status }}</p>
<p><strong>Pickup:</strong> {{ $shipment->pickup_name }}, {{ $shipment->pickup_address }}</p>
<p><strong>Drop:</strong> {{ $shipment->drop_name }}, {{ $shipment->drop_address }}</p>

@if($shipment->courier)
  <p><strong>Courier:</strong> {{ $shipment->courier->user->name }} ({{ $shipment->courier->vehicle_type }})</p>
@endif

<h5>Logs</h5>
<ul>
  @foreach($logs as $log)
    <li>{{ $log->created_at }} — <strong>{{ $log->status }}</strong> — {{ $log->note }} @if($log->changer) by {{ $log->changer->name ?? '' }} @endif</li>
  @endforeach
</ul>

@endsection
