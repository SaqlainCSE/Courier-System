@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4">📦 Create Shipment</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('shipments.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <!-- Pickup -->
                    <div class="col-md-6">
                        <h5 class="fw-semibold mb-3">Pickup Details</h5>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="pickup_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="pickup_phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="pickup_address" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>

                    <!-- Dropoff -->
                    <div class="col-md-6">
                        <h5 class="fw-semibold mb-3">Dropoff Details</h5>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="drop_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="drop_phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="drop_address" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" name="weight_kg" class="form-control" step="0.1" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success shadow">🚀 Book Shipment</button>
                    <a href="{{ route('shipments.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
