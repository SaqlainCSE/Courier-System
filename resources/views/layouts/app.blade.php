<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courier Service</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
      body { background: #f8f9fa; }
      .hero { background: url('https://source.unsplash.com/1600x600/?delivery,logistics') center/cover no-repeat; height: 70vh; color: #fff; display: flex; align-items: center; }
      .hero h1 { font-size: 3rem; font-weight: 700; }
      .hero p { font-size: 1.2rem; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="{{ url('/') }}">Courier Service</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>

        @guest
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
        @else
            @php
                $role = auth()->user()->role;
            @endphp

            @if($role === 'admin')
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
            @elseif($role === 'courier')
                <li class="nav-item"><a class="nav-link" href="{{ route('courier.dashboard') }}">Courier Dashboard</a></li>
            @else
                <li class="nav-item"><a class="nav-link" href="{{ route('shipments.dashboard') }}">Dashboard</a></li>
            @endif

            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light ms-2" type="submit">Logout</button>
                </form>
            </li>
        @endguest
      </ul>
    </div>
  </div>
</nav>


@yield('content')

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-3 mt-5" style="width: 100%;">
    <div class="container">
        <div class="row g-4">
            <!-- About -->
            <div class="col-md-4">
                <h5 class="fw-bold text-danger">Courier Service</h5>
                <p>Fast, reliable, and secure delivery service across Bangladesh. Track your shipment anytime and get real-time updates.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4">
                <h5 class="fw-bold text-success">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ url('/') }}" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="{{ route('shipments.dashboard') }}" class="text-white text-decoration-none">Dashboard</a></li>
                    <li><a href="{{ route('tracking.form') }}" class="text-white text-decoration-none">Track Shipment</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-md-4">
                <h5 class="fw-bold text-danger">Contact</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i>123 Main Street, Dhaka, Bangladesh</p>
                <p><i class="fas fa-phone-alt me-2"></i>+880 1234 567 890</p>
                <p><i class="fas fa-envelope me-2"></i>support@courierservice.com</p>
                <div class="mt-2">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">

        <div class="text-center small">
            &copy; {{ date('Y') }} Courier Service. All Rights Reserved.
        </div>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>
