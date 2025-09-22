@extends('layouts.app')
@section('content')
<h3>Tracking: {{ $shipment->tracking_number }}</h3>
<p>Status: {{ $shipment->status }}</p>
<h5>Logs</h5>
<ul>
  @foreach($logs as $log)
    <li>{{ $log->created_at }} — {{ $log->status }} — {{ $log->note }}</li>
  @endforeach
</ul>
@endsection
