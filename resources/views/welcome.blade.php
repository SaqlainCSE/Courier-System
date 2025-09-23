@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://source.unsplash.com/1600x600/?delivery,logistics') center/cover no-repeat;">
    <div class="container text-center">
        <h1 class="fw-bold mb-3">Fast & Reliable Courier Service</h1>
        <p class="lead mb-4">Send your packages safely across Bangladesh with real-time tracking</p>
        @guest
            <a href="{{ route('register') }}" class="btn btn-danger btn-lg me-2">Sign Up</a>
            <a href="{{ route('login') }}" class="btn btn-success btn-lg">Login</a>
        @else
            <a href="{{ route('shipments.create') }}" class="btn btn-success btn-lg">Create Shipment</a>
        @endguest
    </div>
</section>

<!-- Tracking Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">Track Your Parcel</h2>
        <form action="{{ route('tracking.search') }}" method="POST" class="row g-3 justify-content-center">
            @csrf
            <div class="col-md-6">
                <input type="text" name="tracking_number" class="form-control form-control-lg" placeholder="Enter Tracking Number" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-lg w-100">Track</button>
            </div>
        </form>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-5">Our Services</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger fw-bold">E-commerce Delivery</h5>
                        <p class="card-text">Fast and secure delivery for your online business orders.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-success fw-bold">Pick & Drop</h5>
                        <p class="card-text">Convenient pickup and drop-off services for all parcels.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger fw-bold">Warehousing</h5>
                        <p class="card-text">Safe and organized storage solutions for businesses and individuals.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="text-danger fw-bold">300k+</h2>
                <p>Registered Merchants</p>
            </div>
            <div class="col-md-4">
                <h2 class="text-success fw-bold">10k+</h2>
                <p>Delivery Personnel</p>
            </div>
            <div class="col-md-4">
                <h2 class="text-danger fw-bold">500+</h2>
                <p>Delivery Points</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-white" style="background-color: #d32f2f;">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Ready to Send Your Parcel?</h2>
        @guest
            <a href="{{ route('register') }}" class="btn btn-success btn-lg me-2">Become a Marchant</a>
            {{-- <a href="{{ route('login') }}" class="btn btn-light btn-lg">Login</a> --}}
        @else
            <a href="{{ route('shipments.create') }}" class="btn btn-success btn-lg">Create Shipment</a>
        @endguest
    </div>
</section>
@endsection
