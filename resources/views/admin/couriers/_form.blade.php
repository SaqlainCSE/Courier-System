@csrf

<div class="card shadow-sm border-0 rounded-4 p-4">

    <div class="row g-3">

        {{-- Delivery Man User --}}
        <div class="col-md-6">
            <label for="user_id" class="form-label fw-semibold">Delivery Man (User)</label>
            <select name="user_id" id="user_id" class="form-select" required>
                <option value="">-- Select User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ old('user_id', $courier->user_id ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Vehicle Type --}}
        <div class="col-md-6">
            <label for="vehicle_type" class="form-label fw-semibold">Vehicle Type</label>
            <input type="text" name="vehicle_type" id="vehicle_type" class="form-control"
                   value="{{ old('vehicle_type', $courier->vehicle_type ?? '') }}" placeholder="e.g., Bike, Car, Van">
        </div>

        {{-- Vehicle Number --}}
        <div class="col-md-6">
            <label for="vehicle_number" class="form-label fw-semibold">Vehicle Number</label>
            <input type="text" name="vehicle_number" id="vehicle_number" class="form-control"
                   value="{{ old('vehicle_number', $courier->vehicle_number ?? '') }}" placeholder="e.g., DHA-1234">
        </div>

        {{-- Commission Rate --}}
        <div class="col-md-6">
            <label for="commission_rate" class="form-label fw-semibold">Commission Rate (%)</label>
            <input type="number" step="0.01" min="0" max="100"
                   name="commission_rate" id="commission_rate" class="form-control"
                   value="{{ old('commission_rate', $courier->commission_rate ?? 30) }}">
        </div>

        {{-- Status --}}
        <div class="col-md-6">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select name="status" id="status" class="form-select">
                @foreach(['available', 'busy', 'off'] as $status)
                    <option value="{{ $status }}"
                        {{ old('status', $courier->status ?? 'available') == $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Buttons --}}
        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
                <i class="fas fa-save me-1"></i> {{ isset($courier) ? 'Update' : 'Create' }}
            </button>
            <a href="{{ route('admin.couriers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </div>

    </div>
</div>
