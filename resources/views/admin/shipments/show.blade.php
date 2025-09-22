@extends('layouts.app')
@section('content')
<h3>Shipment {{ $shipment->tracking_number }}</h3>
<p><strong>Status:</strong> {{ $shipment->status }}</p>
<form action="{{ route('admin.shipments.assign', $shipment) }}" method="POST">
  @csrf
  <div class="mb-3">
    <label>Select Courier</label>
    <select name="courier_id" class="form-select">
      <option value="">-- choose --</option>
      @foreach($couriers as $c)
        <option value="{{ $c->id }}">{{ $c->user->name }} — {{ $c->vehicle_type }}</option>
      @endforeach
    </select>
    <button class="btn btn-primary mt-2">Assign</button>
  </div>
</form>

<h5>Logs</h5>
<ul>
  @foreach($logs as $log)
    <li>{{ $log->created_at }} — <strong>{{ $log->status }}</strong> — {{ $log->note }}</li>
  @endforeach
</ul>

<form action="{{ route('admin.shipments.updateStatus', $shipment) }}" method="POST" class="mt-3">
  @csrf
  <div class="mb-3">
    <label>Change status</label>
    <select name="status" class="form-select">
      <option value="pending">pending</option>
      <option value="assigned">assigned</option>
      <option value="picked">picked</option>
      <option value="in_transit">in_transit</option>
      <option value="delivered">delivered</option>
      <option value="cancelled">cancelled</option>
    </select>
  </div>
  <div class="mb-3"><textarea name="note" class="form-control" placeholder="note"></textarea></div>
  <button class="btn btn-success">Update Status</button>
</form>
@endsection
