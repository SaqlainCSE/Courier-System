{{-- resources/views/admin/merchants/_form.blade.php --}}
@csrf

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">

            {{-- Name --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-user me-1 text-dark"></i>Name
                </label>
                <input name="name" type="text" class="form-control form-control-lg"
                       value="{{ old('name', $merchant->name ?? '') }}" required>
            </div>

            {{-- Phone --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-phone me-1 text-dark"></i>Phone
                </label>
                <input name="phone" type="text" class="form-control form-control-lg"
                       value="{{ old('phone', $merchant->phone ?? '') }}">
            </div>

            {{-- Business Name --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-briefcase me-1 text-dark"></i>Business Name
                </label>
                <input name="business_name" type="text" class="form-control form-control-lg"
                       value="{{ old('business_name', $merchant->business_name ?? '') }}">
            </div>

            {{-- Business Address --}}
            <div class="col-12">
                <label class="form-label fw-semibold">
                    <i class="fas fa-map-marker-alt me-1 text-dark"></i>Business Address
                </label>
                <textarea name="business_address" class="form-control form-control-lg" rows="2"
                          placeholder="Enter full business address...">{{ old('business_address', $merchant->business_address ?? '') }}</textarea>
            </div>

            {{-- Email --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-envelope me-1 text-dark"></i>Email
                </label>
                <input name="email" type="email" class="form-control form-control-lg"
                       value="{{ old('email', $merchant->email ?? '') }}" required>
            </div>

            {{-- Password --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-lock me-1 text-dark"></i>Password
                    <small class="text-muted">(leave empty to keep unchanged)</small>
                </label>
                <input name="password" type="password" class="form-control form-control-lg">
            </div>

            {{-- Confirm Password --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-key me-1 text-dark"></i>Confirm Password
                </label>
                <input name="password_confirmation" type="password" class="form-control form-control-lg">
            </div>

        </div>
    </div>

    {{-- Footer Buttons --}}
    <div class="card-footer bg-light text-end">
        <a href="{{ route('admin.merchants.index') }}" class="btn btn-outline-secondary px-4">
            <i class="fas fa-arrow-left me-1"></i>Cancel
        </a>
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-1"></i>{{ isset($merchant) ? 'Update' : 'Create' }}
        </button>
    </div>
</div>
