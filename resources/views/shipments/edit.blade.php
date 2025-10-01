@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-edit me-2"></i>Edit Shipment</h2>
        <a href="{{ route('shipments.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body">
            <form action="{{ route('shipments.update', $shipment->id) }}" method="POST">
                @method('PUT')
                @include('shipments._form', ['buttonText' => '💾 Update Shipment'])
            </form>
        </div>
    </div>
</div>
@endsection
