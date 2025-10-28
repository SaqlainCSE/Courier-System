@php
    $isEdit = isset($courier);
    $user = $isEdit ? $courier->user : new App\Models\User();
@endphp

<div class="row g-3">

    <h4 class="mt-4">User Details</h4>

    <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $user->phone ?? '') }}" required>
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Business Name</label>
        <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror"
               value="{{ old('business_name', $user->business_name ?? '') }}">
        @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Business Address</label>
        <input type="text" name="business_address" class="form-control @error('business_address') is-invalid @enderror"
               value="{{ old('business_address', $user->business_address ?? '') }}">
        @error('business_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Password fields are conditionally displayed/required --}}
    <h4 class="mt-4">{{ $isEdit ? 'Update Password (Optional)' : 'Set Password' }}</h4>

    <div class="col-md-6">
        <label class="form-label">Password</label>
        {{-- 'required' is only added if NOT in edit mode --}}
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ !$isEdit ? 'required' : '' }}>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" {{ !$isEdit ? 'required' : '' }}>
    </div>

    {{-- --- Courier Info --- --}}
    <h4 class="mt-4">Courier Details</h4>

    <div class="col-md-6">
        <label class="form-label">Vehicle Type</label>
        <input type="text" name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror"
               value="{{ old('vehicle_type', $courier->vehicle_type ?? '') }}">
        @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Vehicle Number</label>
        <input type="text" name="vehicle_number" class="form-control @error('vehicle_number') is-invalid @enderror"
               value="{{ old('vehicle_number', $courier->vehicle_number ?? '') }}">
        @error('vehicle_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Commission Rate (%)</label>
        <input type="number" name="commission_rate" step="0.01" min="0" max="100"
               class="form-control @error('commission_rate') is-invalid @enderror"
               value="{{ old('commission_rate', $courier->commission_rate ?? '30') }}" required>
        @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @php $currentStatus = old('status', $courier->status ?? 'available'); @endphp
            <option value="available" {{ $currentStatus == 'available' ? 'selected' : '' }}>Available</option>
            <option value="busy" {{ $currentStatus == 'busy' ? 'selected' : '' }}>Busy</option>
            <option value="off" {{ $currentStatus == 'off' ? 'selected' : '' }}>Off</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 text-end mt-3">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Delivery Man' : 'Register Delivery Man' }}</button>
    </div>

</div>
