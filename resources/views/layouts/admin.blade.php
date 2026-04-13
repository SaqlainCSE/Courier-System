<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel — StepUp Courier</title>

  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      height: 100vh;
      background: #111;
      color: white;
      position: fixed;
      left: 0;
      top: 0;
      width: 260px;
      padding-top: 1rem;
      overflow-y: auto;
      transition: all 0.3s ease;
    }

    .sidebar a {
      display: block;
      color: #cbd5e1;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 6px;
      margin: 2px 10px;
      transition: all 0.2s ease;
    }

    .sidebar a.active,
    .sidebar a:hover {
      background: #0d6efd;
      color: #fff;
    }

    /* Navbar */
    .navbar {
      margin-left: 260px;
      background-color: #fff;
      border-bottom: 1px solid #dee2e6;
      transition: margin-left 0.3s ease;
    }

    /* Main Content */
    .main {
      margin-left: 260px;
      padding: 20px;
      flex: 1;
      transition: margin-left 0.3s ease;
    }

    /* Footer FIX */
    footer {
      margin-left: 260px;
      transition: margin-left 0.3s ease;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
      .sidebar {
        left: -260px;
        z-index: 1050;
      }

      .sidebar.active {
        left: 0;
      }

      .navbar,
      .main,
      footer {
        margin-left: 0;
      }

      .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1040;
        display: none;
      }

      .overlay.show {
        display: block;
      }
    }

    /* Footer Links */
    .footer-link {
      color: #ccc;
      text-decoration: none;
      display: block;
      margin-bottom: 5px;
    }

    .footer-link:hover {
      color: #0dcaf0;
    }

    .social-icon {
      color: #ccc;
      margin-right: 10px;
      font-size: 18px;
      transition: 0.2s;
    }

    .social-icon:hover {
      color: #0dcaf0;
    }
  </style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h5>
    <a class="navbar-brand fw-bold d-flex align-items-center px-3" href="/">
      <i class="fas fa-shipping-fast text-danger me-2"></i>
      <span class="text-light">StepUp<span class="text-danger">Courier</span></span>
    </a>
  </h5>

  <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-chart-line me-2"></i> Dashboard
  </a>

  <a href="{{ route('admin.shipments.index') }}">
    <i class="fas fa-boxes me-2"></i> Shipments
  </a>

  <a href="{{ route('admin.couriers.index') }}">
    <i class="fas fa-motorcycle me-2"></i> Delivery Man
  </a>

  <a href="{{ route('admin.merchants.index') }}">
    <i class="fas fa-users me-2"></i> Merchants
  </a>

  <a href="{{ route('admin.payments.index') }}">
    <i class="fas fa-money-bill me-2"></i> Payment Management
  </a>

  <a href="{{ route('admin.software-management.index') }}">
    <i class="fas fa-calculator me-2"></i> Software Management
  </a>

  <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    <i class="fas fa-sign-out-alt me-2"></i> Logout
  </a>

  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm px-3">
  <div class="container-fluid">
    <button class="btn btn-outline-dark d-lg-none me-2" id="sidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
    <span class="fw-bold">Welcome, {{ auth()->user()->name }}</span>
  </div>
</nav>

<!-- Main -->
<main class="main">
  @yield('content')
</main>

<!-- Footer -->
<footer class="text-white pt-5 pb-3 mt-5"
        style="background: linear-gradient(135deg, #111 0%, #222 100%); width:100%;">
  <div class="container-fluid">
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
          <li><a href="{{ url('/') }}" class="footer-link">📦 Dashboard</a></li>
          {{-- <li><a href="{{ route('tracking.form') }}" class="footer-link">🔍 Track Shipment</a></li> --}}
          {{-- <li><a href="#" class="footer-link">📞 Contact Us</a></li> --}}
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  const toggleBtn = document.getElementById('sidebarToggle');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('show');
  });

  overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('show');
  });
</script>

</body>
</html>
