@extends('layouts.app')
@section('content')
<h3>Track your shipment</h3>
<form method="POST" action="{{ route('tracking.search') }}">
  @csrf
  <div class="input-group">
    <input name="tracking" class="form-control" placeholder="Enter tracking number">
    <button class="btn btn-primary">Track</button>
  </div>
</form>
@endsection
