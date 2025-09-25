<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StepUp Courier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Add inside <head> of layouts/app.blade.php -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <style>
      body { background: #f8f9fa; }
      .hero { background: url('https://source.unsplash.com/1600x600/?delivery,logistics') center/cover no-repeat; height: 70vh; color: #fff; display: flex; align-items: center; }
      .hero h1 { font-size: 3rem; font-weight: 700; }
      .hero p { font-size: 1.2rem; }
  </style>
</head>
<body>

    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top"
     style="background: rgba(0,0,0,0.85); backdrop-filter: blur(6px);">
  <div class="container">

    <!-- Branding with Icon -->
    <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ url('/') }}">
      <i class="fas fa-shipping-fast text-danger me-2"></i>
      StepUp <span class="text-danger">Courier</span>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Items -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">

        <li class="nav-item">
          <a class="nav-link fw-semibold" href="{{ url('/') }}">🏠 Home</a>
        </li>

        @guest
            <li class="nav-item">
              <a style="background-color: #34bf36;" class="btn text-light fw-bold ms-lg-3 px-4 rounded-pill shadow-sm" href="{{ route('login') }}">🔑 Login</a>
            </li>
            <li class="nav-item">
              <a style="background-color: red;" class="btn text-light fw-bold ms-lg-3 px-4 rounded-pill shadow-sm"
                 href="{{ route('register') }}">
                 🚀 Register
              </a>
            </li>
        @else
            @php
                $role = auth()->user()->role;
            @endphp

            @if($role === 'admin')
                <li class="nav-item"><a class="nav-link fw-semibold" href="{{ route('admin.dashboard') }}">📊 Admin Dashboard</a></li>
            @elseif($role === 'courier')
                <li class="nav-item"><a class="nav-link fw-semibold" href="{{ route('courier.dashboard') }}">🚴 Courier Dashboard</a></li>
            @else
                <li class="nav-item"><a class="nav-link fw-semibold" href="{{ route('shipments.dashboard') }}">📦 Dashboard</a></li>
            @endif

            <li class="nav-item ms-lg-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light rounded-pill px-3" type="submit">
                        🔓 Logout
                    </button>
                </form>
            </li>
        @endguest
      </ul>
    </div>
  </div>
</nav>


                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


@yield('content')

<!-- Footer -->
<footer class="text-white pt-5 pb-3 mt-5"
        style="background: linear-gradient(135deg, #111 0%, #222 100%); width:100%;">
  <div class="container">
    <div class="row g-4">

      <!-- About -->
      <div class="col-md-4">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/"><h5 class="fw-bold d-flex align-items-center">
          <i class="fas fa-shipping-fast text-danger me-2"></i> StepUp <span class="text-danger">Courier</span>
        </h5></a>
        <p class="small text-light">
          🚚 Fast, reliable, and secure delivery service across Bangladesh.
          Track your shipment anytime with real-time updates.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="col-md-4">
        <h5 class="fw-bold text-info">Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="{{ url('/') }}" class="footer-link">🏠 Home</a></li>
          <li><a href="{{ route('shipments.dashboard') }}" class="footer-link">📦 Dashboard</a></li>
          <li><a href="{{ route('tracking.form') }}" class="footer-link">🔍 Track Shipment</a></li>
          <li><a href="#" class="footer-link">📞 Contact Us</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-md-4">
        <h5 class="fw-bold text-danger">Contact</h5>
        <p class="small"><i class="fas fa-map-marker-alt me-2 text-warning"></i>Goran Khilgaon, Dhaka-1219</p>
        <p class="small"><i class="fas fa-phone-alt me-2 text-info"></i>+880 1643-348342</p>
        <p class="small"><i class="fas fa-envelope me-2 text-success"></i>stepupcourier.com@gmail.com</p>

        <!-- Social Icons -->
        <div class="mt-3">
          <a href="https://www.facebook.com/people/StepUp-courier/100087178264688/" target="__blank" class="social-icon"><i class="fab fa-facebook-f"></i></a>
          <a href="https://wa.me/8801643348342" target="__blank" class="social-icon"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.facebook.com/people/StepUp-courier/100087178264688/" target="__blank" class="social-icon"><i class="fab fa-instagram"></i></a>
          <a href="https://www.facebook.com/people/StepUp-courier/100087178264688/" target="__blank" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>

    </div>

    <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">

    <div class="text-center small"> &copy; {{ date('Y') }} StepUp Courier. All Rights Reserved. </div>
  </div>
</footer>

<!-- Extra Footer Styling -->
<style>
  .footer-link {
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    display: block;
    padding: 3px 0;
    transition: color 0.3s;
    font-size: 0.9rem;
  }
  .footer-link:hover {
    color: #07ff45;
    padding-left: 4px;
  }
  .social-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    color: #fff;
    margin-right: 8px;
    transition: all 0.3s;
  }
  .social-icon:hover {
    background: #07ff45;
    color: #000;
    transform: translateY(-3px);
  }
</style>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>
