@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-3">
                <div class="card-body p-5">
                    <h3 class="mb-4 text-center fw-bold text-success">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </h3>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-muted"></i>Email
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control form-control-lg rounded-pill"
                                   placeholder="Enter your email"
                                   required autofocus>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-muted"></i>Password
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
                                        onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember + Forgot -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label small" for="remember">Remember Me</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot Password?</a>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </button>
                        </div>
                    </form>

                    <!-- Register -->
                    <p class="text-center mt-4 mb-0">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const password = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");

        if (password.type === "password") {
            password.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            password.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>
@endpush
