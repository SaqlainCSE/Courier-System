@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-3">
                <div class="card-body p-5">
                    <h3 class="mb-4 text-center fw-bold text-danger">
                        <i class="fas fa-shield-alt me-2"></i> Confirm Password
                    </h3>

                    <p class="text-muted text-center mb-4">
                        This is a secure area of the application. Please confirm your password before continuing.
                    </p>

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-muted"></i>Password
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control form-control-lg rounded-pill @error('password') is-invalid @enderror"
                                   placeholder="Enter your password"
                                   required autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-danger btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-check-circle me-1"></i> Confirm
                            </button>
                        </div>
                    </form>

                    <!-- Back to Login -->
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
