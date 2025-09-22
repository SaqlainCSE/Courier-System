@extends('layouts.app')
@section('content')
<h3>Your Assignments</h3>
@foreach($assignments as $a)
  <div class="card mb-2">
    <div class="card-body">
      <h5>{{ $a->tracking_number }} — {{ $a->status }}</h5>
      <p>Pickup: {{ $a->pickup_address }}</p>
      <p>Drop: {{ $a->drop_address }}</p>
      <form action="{{ route('courier.shipments.updateStatus', $a) }}" method="POST">
        @csrf
        <select name="status" class="form-select mb-2">
          <option value="picked">picked</option>
          <option value="in_transit">in_transit</option>
          <option value="delivered">delivered</option>
        </select>
        <textarea name="note" class="form-control mb-2" placeholder="note"></textarea>
        <button class="btn btn-primary">Update</button>
      </form>
    </div>
  </div>
@endforeach
@endsection
