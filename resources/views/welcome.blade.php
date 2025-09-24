@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="text-white d-flex align-items-center justify-content-center text-center"
    style="background: linear-gradient(157deg, #ff416c, #ff4b2b, #34bf36);
           min-height: 100vh; position: relative; overflow: hidden;">
    <div class="container position-relative z-2">
        <!-- Branding -->
        <h1 class="fw-bold display-2 animate__animated animate__fadeInDown">
            StepUp Courier
        </h1>
        <h2 class="fw-bold display-2 mb-3 animate__animated animate__fadeInDown">
            Fast • Reliable • Secure 🚚
        </h2>
        <p class="lead mb-4 animate__animated animate__fadeInUp">
            Your trusted courier partner across Bangladesh
        </p>
        @guest
            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5 rounded-pill shadow-lg me-3">Get Started</a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-5 rounded-pill shadow-lg">Login</a>
        @else
            <a href="{{ route('shipments.create') }}" class="btn btn-success btn-lg px-5 rounded-pill shadow-lg">Create Shipment</a>
        @endguest
    </div>
</section>

<!-- Tracking Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-lg border-0 p-4 glass-box text-center mx-auto" style="max-width: 700px;">
            <h3 class="fw-bold mb-3">📦 Track Your Parcel</h3>
            <form action="{{ route('tracking.search') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-9">
                    <input type="text" name="tracking_number" class="form-control form-control-lg rounded-pill shadow-sm"
                           placeholder="Enter Tracking Number (e.g. TRK12345678)" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill">Track</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-5">🚀 Our Services</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-box shadow-lg p-4">
                    <div class="icon-circle bg-danger"><i class="fas fa-store fa-2x text-white"></i></div>
                    <h5 class="fw-bold mt-3">E-commerce Delivery</h5>
                    <p>Superfast & secure delivery for online businesses.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-box shadow-lg p-4">
                    <div class="icon-circle bg-success"><i class="fas fa-shipping-fast fa-2x text-white"></i></div>
                    <h5 class="fw-bold mt-3">Pick & Drop</h5>
                    <p>Simple pickup & drop-off designed for daily parcels.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-box shadow-lg p-4">
                    <div class="icon-circle bg-warning"><i class="fas fa-warehouse fa-2x text-white"></i></div>
                    <h5 class="fw-bold mt-3">Warehousing</h5>
                    <p>Safe & organized storage solutions for your goods.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg,#0f2027,#203a43,#2c5364);">
    <div class="container text-center">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="fw-bold counter" data-target="5000">0</h2>
                <p>Happy Clients</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold counter" data-target="100000">0</h2>
                <p>Parcels Delivered</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold counter" data-target="50">0</h2>
                <p>Districts Covered</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-5">❤️ What Customers Say</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-box shadow-lg p-4 text-center">
                    <img src="https://i.pravatar.cc/80?img=1" class="rounded-circle mb-3">
                    <p>"Super fast and reliable service. Tracking is awesome!"</p>
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <h6 class="mt-2">Ayesha - Dhaka</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-box shadow-lg p-4 text-center">
                    <img src="https://i.pravatar.cc/80?img=2" class="rounded-circle mb-3">
                    <p>"My #1 courier choice for my online store."</p>
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <h6 class="mt-2">Rahim - Chittagong</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-box shadow-lg p-4 text-center">
                    <img src="https://i.pravatar.cc/80?img=3" class="rounded-circle mb-3">
                    <p>"Great support team. Always helpful!"</p>
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <h6 class="mt-2">Karim - Sylhet</h6>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-white text-center"
    style="background: linear-gradient(90deg,#11998e,#38ef7d);">
    <div class="container">
        <h2 class="fw-bold mb-4">Ready to Ship Smarter? 🚀</h2>
        @guest
            <a href="{{ route('register') }}" class="btn btn-light btn-lg me-2 shadow-lg">Join Now</a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg shadow-lg">Login</a>
        @else
            <a href="{{ route('shipments.create') }}" class="btn btn-dark btn-lg shadow-lg">Create Shipment</a>
        @endguest
    </div>
</section>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script>
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
/* Glass box */
.glass-box {
    backdrop-filter: blur(10px);
    border-radius: 20px;
}

/* Service cards */
.service-box {
    border-radius: 20px;
    transition: transform .3s ease;
}
.service-box:hover { transform: translateY(-8px); }
.icon-circle {
    width: 70px; height: 70px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto;
}

/* Testimonials */
.testimonial-box {
    border-radius: 20px;
    background: #fff;
    transition: transform .3s ease;
}
.testimonial-box:hover { transform: translateY(-6px); }
.stars { color: gold; }
</style>
@endpush
