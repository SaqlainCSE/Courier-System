@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero-section text-white d-flex align-items-center"
    style="background: url('/slider.jpg') center/cover no-repeat;
           min-height: 100vh; position: relative;">

    <!-- Optional animated shapes / overlays -->
    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.25); mix-blend-mode: multiply;"></div>

    <div class="container text-center position-relative" style="z-index: 2;">
        <h1 class="fw-bold display-3 animate__animated animate__fadeInDown mb-3">
            Your Trusted Courier Partner
        </h1>
        <p class="lead mb-5 animate__animated animate__fadeInUp">
            Delivering speed, safety, and satisfaction across Bangladesh
        </p>

        @guest
            <a href="{{ route('register') }}"
               class="btn btn-danger btn-lg px-5 me-3 shadow-lg animate__animated animate__fadeInLeft"
               style="border-radius: 50px;">Sign Up</a>
            <a href="{{ route('login') }}"
               class="btn btn-outline-light btn-lg px-5 shadow-lg animate__animated animate__fadeInRight"
               style="border-radius: 50px;">Login</a>
        @else
            <a href="{{ route('shipments.create') }}"
               class="btn btn-success btn-lg px-5 shadow-lg animate__animated animate__fadeInUp"
               style="border-radius: 50px;">Create Shipment</a>
        @endguest
    </div>

    <!-- Optional floating courier truck icon -->
    <div style="position: absolute; bottom: 20px; right: 20px; font-size: 3rem; color: rgba(255,255,255,0.3);">
        <i class="fas fa-truck-moving animate__animated animate__bounce animate__infinite"></i>
    </div>
</section>


<!-- Curved Divider -->
<div class="custom-shape-divider-bottom-hero">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M321.39,56.44C160.2,82.31,0,0,0,0V120H1200V0S482.59,29.77,321.39,56.44Z" opacity=".25" class="shape-fill"></path>
    </svg>
</div>

<!-- Tracking Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">📦 Track Your Parcel</h2>
        <form action="{{ route('tracking.search') }}" method="POST" class="row g-3 justify-content-center">
            @csrf
            <div class="col-md-6">
                <input type="text" name="tracking_number" class="form-control form-control-lg shadow-sm rounded-pill" placeholder="Enter Tracking Number" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill shadow-sm">Track</button>
            </div>
        </form>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-5">🚀 What We Offer</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card service-card shadow-lg border-0 h-100 p-4">
                    <i class="fas fa-store fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">E-commerce Delivery</h5>
                    <p>Powering your online business with fast, secure, and reliable delivery.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card shadow-lg border-0 h-100 p-4">
                    <i class="fas fa-shipping-fast fa-3x text-success mb-3"></i>
                    <h5 class="fw-bold">Pick & Drop</h5>
                    <p>Simple pickup & drop-off services designed for everyday parcels.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card shadow-lg border-0 h-100 p-4">
                    <i class="fas fa-warehouse fa-3x text-warning mb-3"></i>
                    <h5 class="fw-bold">Warehousing</h5>
                    <p>Organized storage solutions with maximum safety for your goods.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-dark text-white">
    <div class="container text-center">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="fw-bold counter text-danger" data-target="3000">0</h2>
                <p>Registered Merchants</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold counter text-success" data-target="10000">0</h2>
                <p>Delivery Personnel</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold counter text-warning" data-target="500">0</h2>
                <p>Delivery Points</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-5">❤️ What Our Clients Say</h2>
        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner text-center">
                <div class="carousel-item active">
                    <blockquote class="blockquote">
                        <p class="mb-4">“Amazing service! My parcels always arrive on time with live tracking.”</p>
                        <footer class="blockquote-footer">Ayesha, Dhaka</footer>
                    </blockquote>
                </div>
                <div class="carousel-item">
                    <blockquote class="blockquote">
                        <p class="mb-4">“Best courier company for e-commerce deliveries. Super reliable.”</p>
                        <footer class="blockquote-footer">Rahim, Chittagong</footer>
                    </blockquote>
                </div>
                <div class="carousel-item">
                    <blockquote class="blockquote">
                        <p class="mb-4">“I trust them with all my logistics needs. Great support team.”</p>
                        <footer class="blockquote-footer">Karim, Sylhet</footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-white" style="background: linear-gradient(45deg,#d32f2f,#8e0000);">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Start Shipping Smarter Today 🚚</h2>
        @guest
            <a href="{{ route('register') }}" class="btn btn-light btn-lg me-2 shadow-lg">Get Started</a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg shadow-lg">Login</a>
        @else
            <a href="{{ route('shipments.create') }}" class="btn btn-success btn-lg shadow-lg">Create Shipment</a>
        @endguest
    </div>
</section>
@endsection

@push('scripts')
<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script>
// Counter Animation
document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const updateCounter = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / 200;
            if(count < target){
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCounter, 10);
            } else {
                counter.innerText = target.toLocaleString();
            }
        }
        updateCounter();
    });
});
</script>
<style>
.hero-section {
    position: relative;
    text-shadow: 0 3px 10px rgba(0,0,0,0.6);
}
.service-card {
    transition: transform .3s ease, box-shadow .3s ease;
    border-radius: 20px;
    background: #fff;
}
.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}
.custom-shape-divider-bottom-hero {
    position: relative;
    bottom: -1px;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}
.custom-shape-divider-bottom-hero svg {
    position: relative;
    display: block;
    width: calc(132% + 1.3px);
    height: 80px;
}
</style>
@endpush
