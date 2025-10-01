@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-6">
            <div class="card shadow border-0 rounded-3">
                <div class="card-body p-5">
                    <h3 class="mb-4 text-center fw-bold text-danger">
                        <i class="fas fa-user-plus me-2"></i> Register
                    </h3>

                    <!-- Register Form -->
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-muted"></i>Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control form-control-lg rounded-pill"
                                   placeholder="Enter your full name"
                                   value="{{ old('name') }}" required>
                        </div>

                        <!-- Business Name -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-building me-2 text-muted"></i>Business Name
                            </label>
                            <input type="text" name="business_name" class="form-control form-control-lg rounded-pill"
                                   placeholder="Enter your business name"
                                   value="{{ old('business_name') }}">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-muted"></i>Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control form-control-lg rounded-pill"
                                   placeholder="Enter your email"
                                   value="{{ old('email') }}" required>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-phone me-2 text-muted"></i>Phone <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="phone" class="form-control form-control-lg rounded-pill"
                                   placeholder="Enter your phone number"
                                   value="{{ old('phone') }}" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-muted"></i>Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                    id="password"
                                    name="password"
                                    class="form-control form-control-lg rounded-start-pill"
                                    placeholder="Enter your password"
                                    required>
                                <button type="button"
                                        class="btn btn-outline-secondary rounded-end-pill"
                                        onclick="togglePassword('password', 'toggleIcon1')">
                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-muted"></i>Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control form-control-lg rounded-start-pill"
                                    placeholder="Confirm your password"
                                    required>
                                <button type="button"
                                        class="btn btn-outline-secondary rounded-end-pill"
                                        onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </button>
                        </div>
                    </form>

                    <!-- Login -->
                    <p class="text-center mt-4 mb-0">
                        Already have an account?
                        <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);

    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        field.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>
