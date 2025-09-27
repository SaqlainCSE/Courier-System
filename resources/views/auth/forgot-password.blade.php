@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-3">
                <div class="card-body p-5">
                    <h3 class="mb-4 text-center fw-bold text-primary">
                        <i class="fas fa-unlock-alt me-2"></i> Forgot Password
                    </h3>

                    <p class="text-muted text-center mb-4">
                        Enter your email address and we’ll send you a link to reset your password.
                    </p>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Reset Form -->
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-muted"></i>Email Address
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control form-control-lg rounded-pill @error('email') is-invalid @enderror"
                                   placeholder="Enter your email"
                                   required autofocus>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-paper-plane me-1"></i> Send Reset Link
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
