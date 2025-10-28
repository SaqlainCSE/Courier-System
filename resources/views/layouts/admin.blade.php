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
    }

    /* Sidebar (Desktop View) */
    .sidebar {
      height: 100vh;
      background: #111;
      color: white;
      position: fixed;
      left: 0;
      top: 0;
      width: 240px;
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

    .main {
      margin-left: 240px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }

    .navbar {
      margin-left: 240px;
      background-color: #fff;
      border-bottom: 1px solid #dee2e6;
      transition: margin-left 0.3s ease;
    }

    /* 🔹 Responsive Sidebar */
    @media (max-width: 991.98px) {
      .sidebar {
        position: fixed;
        left: -240px;
        z-index: 1050;
      }

      .sidebar.active {
        left: 0;
      }

      .navbar {
        margin-left: 0;
      }

      .main {
        margin-left: 0;
      }

      /* Dark overlay when sidebar opens */
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
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <h5>
      <a class="navbar-brand fw-bold d-flex align-items-center px-3" href="http://127.0.0.1:8000">
        <i class="fas fa-shipping-fast text-danger me-2"></i>
        <span class="text-light">StepUp<span class="text-danger">Courier</span></span>
      </a>
    </h5>

    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <i class="fas fa-chart-line me-2"></i> Dashboard
    </a>

    <a href="{{ route('admin.shipments.index') }}" class="{{ request()->routeIs('admin.shipments.index') ? 'active' : '' }}">
      <i class="fas fa-boxes me-2"></i> Shipments
    </a>

    <a href="{{ route('admin.couriers.index') }}" class="{{ request()->routeIs('admin.couriers.index') ? 'active' : '' }}">
      <i class="fas fa-motorcycle me-2"></i> Delivery Man
    </a>

    <a href="{{ route('admin.merchants.index') }}" class="{{ request()->routeIs('admin.merchants.index') ? 'active' : '' }}">
      <i class="fas fa-users me-2"></i> Merchants
    </a>

    <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.index') ? 'active' : '' }}">
      <i class="fas fa-money-bill me-2"></i> Payments
    </a>

    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="fas fa-sign-out-alt me-2"></i> Logout
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
  </div>

  <!-- Overlay for mobile -->
  <div class="overlay" id="overlay"></div>

  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg shadow-sm px-3">
    <div class="container-fluid">
      <button class="btn btn-outline-dark d-lg-none me-2" id="sidebarToggle">
        <i class="fas fa-bars"></i>
      </button>
      <span class="fw-bold">Welcome, {{ auth()->user()->name }}</span>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="main">
    @yield('content')
  </main>

  @stack('scripts')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // 🔹 Sidebar Toggle for Mobile
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
