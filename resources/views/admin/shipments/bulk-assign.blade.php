@extends('layouts.admin')

@section('content')
<div class="container py-4">

    <!-- Branding / Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold">
            <i class="fas fa-truck-moving text-danger me-2"></i> bulk Assign Shipments
        </h1>
        <p class="text-muted small">Select multiple pending shipments and assign to a delivery man</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Bulk Assign Form -->
    <form action="{{ route('admin.shipments.bulk.store') }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-dark text-white fw-bold rounded-top-4 d-flex align-items-center justify-content-between">
                <span><i class="fas fa-list me-2"></i> Today's Pending Shipments ({{ count($shipments) }})</span>
                <div>
                    <button type="button" class="btn btn-sm btn-light" id="selectAll">
                        <i class="fas fa-check-square me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="deselectAll">
                        <i class="fas fa-square me-1"></i> Clear All
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                @if(count($shipments) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" class="form-check-input" id="masterCheckbox">
                                    </th>
                                    <th>Tracking #</th>
                                    <th>Pickup</th>
                                    <th>Dropoff</th>
                                    <th>Amount</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shipments as $shipment)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="shipment_ids[]" value="{{ $shipment->id }}"
                                                   class="form-check-input shipment-checkbox">
                                        </td>
                                        <td>
                                            <strong>{{ $shipment->tracking_number }}</strong>
                                        </td>
                                        <td class="text-truncate" style="max-width: 200px;">
                                            <div class="small text-muted">
                                                {{ $shipment->customer->business_name ?? 'N/A' }}<br>
                                                {{ Str::limit($shipment->pickup_address, 40) }}
                                            </div>
                                        </td>
                                        <td class="text-truncate" style="max-width: 200px;">
                                            <div class="small text-muted">
                                                {{ $shipment->drop_name }}<br>
                                                {{ Str::limit($shipment->drop_address, 40) }}
                                            </div>
                                        </td>
                                        <td>
                                            <strong>৳ {{ number_format($shipment->price, 2) }}</strong>
                                        </td>
                                        <td>{{ $shipment->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-5 text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                        <p>No pending shipments for today.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Assignment Section -->
        @if(count($shipments) > 0)
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-dark text-white fw-bold rounded-top-4">
                    <i class="fas fa-user-tie me-2"></i> Assign to Delivery Man
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Delivery Man <span class="text-danger">*</span></label>
                            <select name="courier_id" class="form-select form-select-lg @error('courier_id') is-invalid @enderror" required>
                                <option value="">-- Choose a Delivery Man --</option>
                                @foreach($couriers as $courier)
                                    <option value="{{ $courier->id }}" @selected(old('courier_id') == $courier->id)>
                                        {{ $courier->user->name }} ({{ $courier->user->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('courier_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Selected Shipments: <span id="selectedCount" class="badge bg-primary">0</span></label>
                            <div class="alert alert-info mt-2" id="selectedAlert" style="display:none;">
                                <i class="fas fa-info-circle me-2"></i>
                                <span id="selectedMessage">No shipments selected</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-lg btn-success" id="assignBtn" disabled>
                            <i class="fas fa-check-circle me-2"></i> Assign Selected Shipments
                        </button>
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-lg btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <a href="{{ route('admin.shipments.index') }}" class="btn btn-lg btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Shipments
                </a>
            </div>
        @endif
    </form>

</div>

@push('scripts')
<script>
    // Select/Deselect functionality
    const masterCheckbox = document.getElementById('masterCheckbox');
    const shipmentCheckboxes = document.querySelectorAll('.shipment-checkbox');
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const selectedCount = document.getElementById('selectedCount');
    const assignBtn = document.getElementById('assignBtn');
    const selectedAlert = document.getElementById('selectedAlert');
    const selectedMessage = document.getElementById('selectedMessage');

    function updateCount() {
        const checked = document.querySelectorAll('.shipment-checkbox:checked').length;
        selectedCount.textContent = checked;

        if (checked > 0) {
            selectedAlert.style.display = 'block';
            selectedMessage.textContent = checked + ' shipment(s) selected for assignment';
            assignBtn.disabled = false;
        } else {
            selectedAlert.style.display = 'none';
            selectedMessage.textContent = 'No shipments selected';
            assignBtn.disabled = true;
        }
    }

    // Master checkbox
    masterCheckbox.addEventListener('change', function() {
        shipmentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateCount();
    });

    // Individual checkboxes
    shipmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(shipmentCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(shipmentCheckboxes).some(cb => cb.checked);

            masterCheckbox.checked = allChecked;
            masterCheckbox.indeterminate = someChecked && !allChecked;
            updateCount();
        });
    });

    // Select All button
    selectAllBtn.addEventListener('click', function() {
        shipmentCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        masterCheckbox.checked = true;
        updateCount();
    });

    // Deselect All button
    deselectAllBtn.addEventListener('click', function() {
        shipmentCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        masterCheckbox.checked = false;
        updateCount();
    });

    // Initial count
    updateCount();
</script>
@endpush

@push('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.3rem;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    .btn-success {
        background-color: #198754;
        border-color: #198754;
    }

    .btn-success:hover {
        background-color: #147d3b;
        border-color: #147d3b;
    }

    .card {
        border: 1px solid #e9ecef !important;
    }

    @media (max-width: 768px) {
        .table {
            font-size: 0.85rem;
        }

        .btn-lg {
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
        }
    }
</style>
@endpush

@endsection
