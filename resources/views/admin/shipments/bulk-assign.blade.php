@extends('layouts.admin')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="text-center mb-5">
        <h1 class="fw-semibold fs-3">
            <i class="fas fa-truck-moving text-danger me-2"></i> Bulk assign shipments
        </h1>
        <p class="text-muted small mb-0">Select multiple pending shipments and assign to a delivery man</p>
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

    <form action="{{ route('admin.shipments.bulk.store') }}" method="POST">
        @csrf

        {{-- Shipments Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-dark text-white rounded-top-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="fw-semibold">
                    <i class="fas fa-list me-2"></i> Today's pending shipments ({{ count($shipments) }})
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="selectAll">
                        <i class="fas fa-check-square me-1"></i> Select all
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="deselectAll">
                        <i class="fas fa-square me-1"></i> Clear all
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                @if(count($shipments) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
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
                                            <input type="checkbox" name="shipment_ids[]"
                                                   value="{{ $shipment->id }}"
                                                   class="form-check-input shipment-checkbox">
                                        </td>
                                        <td><strong>{{ $shipment->tracking_number }}</strong></td>
                                        <td style="max-width: 200px;">
                                            <div class="small text-muted text-truncate">
                                                {{ $shipment->customer->business_name ?? 'N/A' }}<br>
                                                {{ Str::limit($shipment->pickup_address, 40) }}
                                            </div>
                                        </td>
                                        <td style="max-width: 200px;">
                                            <div class="small text-muted text-truncate">
                                                {{ $shipment->drop_name }}<br>
                                                {{ Str::limit($shipment->drop_address, 40) }}
                                            </div>
                                        </td>
                                        <td><strong>৳ {{ number_format($shipment->price, 2) }}</strong></td>
                                        <td class="text-nowrap small text-muted">
                                            {{ $shipment->created_at->format('d M Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-5 text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                        <p class="mb-0">No pending shipments for today.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Assignment Section --}}
        @if(count($shipments) > 0)
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-dark text-white rounded-top-3 fw-semibold">
                    <i class="fas fa-user-tie me-2"></i> Assign to delivery man
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                Select delivery man <span class="text-danger">*</span>
                            </label>
                            <select name="courier_id"
                                    class="form-select form-select-lg @error('courier_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Choose a delivery man --</option>
                                @foreach($couriers as $courier)
                                    <option value="{{ $courier->id }}"
                                            @selected(old('courier_id') == $courier->id)>
                                        {{ $courier->user->name }} ({{ $courier->user->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('courier_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                Selected shipments:
                                <span id="selectedCount" class="badge bg-primary ms-1">0</span>
                            </label>
                            <div class="alert alert-info py-2 mt-2 mb-0 small"
                                 id="selectedAlert" style="display: none;">
                                <i class="fas fa-info-circle me-2"></i>
                                <span id="selectedMessage"></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-success btn-sm" id="assignBtn" disabled>
                            <i class="fas fa-check-circle me-2"></i> Assign selected shipments
                        </button>
                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <a href="{{ route('admin.shipments.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i> Back to shipments
                </a>
            </div>
        @endif
    </form>

</div>

@push('scripts')
<script>
    const masterCheckbox   = document.getElementById('masterCheckbox');
    const shipmentCheckboxes = document.querySelectorAll('.shipment-checkbox');
    const selectAllBtn     = document.getElementById('selectAll');
    const deselectAllBtn   = document.getElementById('deselectAll');
    const selectedCount    = document.getElementById('selectedCount');
    const assignBtn        = document.getElementById('assignBtn');
    const selectedAlert    = document.getElementById('selectedAlert');
    const selectedMessage  = document.getElementById('selectedMessage');

    function updateCount() {
        const checked = document.querySelectorAll('.shipment-checkbox:checked').length;
        selectedCount.textContent = checked;

        if (checked > 0) {
            selectedAlert.style.display = 'block';
            selectedMessage.textContent = checked + ' shipment(s) selected for assignment';
            assignBtn.disabled = false;
        } else {
            selectedAlert.style.display = 'none';
            assignBtn.disabled = true;
        }
    }

    masterCheckbox.addEventListener('change', function () {
        shipmentCheckboxes.forEach(cb => cb.checked = this.checked);
        updateCount();
    });

    shipmentCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const all  = [...shipmentCheckboxes].every(c => c.checked);
            const some = [...shipmentCheckboxes].some(c => c.checked);
            masterCheckbox.checked       = all;
            masterCheckbox.indeterminate = some && !all;
            updateCount();
        });
    });

    selectAllBtn.addEventListener('click', () => {
        shipmentCheckboxes.forEach(cb => cb.checked = true);
        masterCheckbox.checked = true;
        updateCount();
    });

    deselectAllBtn.addEventListener('click', () => {
        shipmentCheckboxes.forEach(cb => cb.checked = false);
        masterCheckbox.checked = false;
        updateCount();
    });

    updateCount();
</script>
@endpush
@endsection
