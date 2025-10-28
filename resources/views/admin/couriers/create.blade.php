@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Register New Delivery Man</h2>

    <form action="{{ route('admin.couriers.store') }}" method="POST">
    @csrf
    <div class="row g-3">

        {{-- User Info --}}
        <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Business Name</label>
            <input type="text" name="business_name" class="form-control">
        </div>

        <div class="col-md-12">
            <label class="form-label">Business Address</label>
            <input type="text" name="business_address" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        {{-- Courier Info --}}
        <div class="col-md-6">
            <label class="form-label">Vehicle Type</label>
            <input type="text" name="vehicle_type" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Vehicle Number</label>
            <input type="text" name="vehicle_number" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Commission Rate (%)</label>
            <input type="number" name="commission_rate" step="0.01" min="0" max="100" class="form-control" value="30">
        </div>

        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="available">Available</option>
                <option value="busy">Busy</option>
                <option value="off">Off</option>
            </select>
        </div>

        <div class="col-12 text-end mt-3">
            <button type="submit" class="btn btn-primary">Register Delivery Man</button>
        </div>

    </div>
</form>


</div>
@endsection
