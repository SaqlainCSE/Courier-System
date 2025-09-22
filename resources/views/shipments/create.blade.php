@extends('layouts.app')
@section('content')
<h3>Book a Shipment</h3>
<form action="{{ route('shipments.store') }}" method="POST">
  @csrf
  <div class="mb-3">
    <label>Pickup name</label>
    <input name="pickup_name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Pickup phone</label>
    <input name="pickup_phone" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Pickup address</label>
    <textarea name="pickup_address" class="form-control" required></textarea>
  </div>

  <div class="mb-3">
    <label>Drop name</label>
    <input name="drop_name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Drop phone</label>
    <input name="drop_phone" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Drop address</label>
    <textarea name="drop_address" class="form-control" required></textarea>
  </div>

  <div class="row">
    <div class="col">
      <label>Weight (kg)</label>
      <input name="weight_kg" class="form-control" type="number" step="0.01" value="0.5" required>
    </div>
    <div class="col">
      <label>Price</label>
      <input name="price" class="form-control" type="number" step="0.01" value="0.00" required>
    </div>
  </div>

  <div class="mt-3">
    <button class="btn btn-primary">Create Shipment</button>
  </div>
</form>
@endsection
