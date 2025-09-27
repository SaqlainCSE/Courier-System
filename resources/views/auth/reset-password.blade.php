@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-3">
                <div class="card-body p-5">
                    <h3 class="mb-4 text-center fw-bold text-warning">
                        <i class="fas fa-key me-2"></i> Reset Password
                    </h3>

                    <p class="text-muted text-center mb-4">
                        Enter your email address and set a new password to access your account.
                    </p>

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <!-- Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-muted"></i>Email Address
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $request->email) }}"
                                   class="form-control form-control-lg rounded-pill @error('email') is-invalid @enderror"
                                   placeholder="Enter your email"
                                   required autofocus autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-muted"></i>New Password
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control form-control-lg rounded-pill @error('password') is-invalid @enderror"
                                   placeholder="Enter new password"
                                   required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-muted"></i>Confirm Password
                            </label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="form-control form-control-lg rounded-pill @error('password_confirmation') is-invalid @enderror"
                                   placeholder="Re-enter new password"
                                   required autocomplete="new-password">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-sync-alt me-1"></i> Reset Password
                            </button>
                        </div>
                    </form>

                    <!-- Back to login -->
                    <p class="text-center mt-4 mb-0">
                        <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Back to Login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
