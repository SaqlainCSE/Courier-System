<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Courier App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-light bg-light mb-4">
  <div class="container">
    <a class="navbar-brand" href="{{ url('/') }}">CourierApp</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        @auth
          @if(auth()->user()->isCustomer())
            <li class="nav-item"><a class="nav-link" href="{{ route('shipments.index') }}">My Shipments</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('shipments.create') }}">Book</a></li>
          @endif
          @if(auth()->user()->isCourier())
            <li class="nav-item"><a class="nav-link" href="{{ route('courier.dashboard') }}">Courier Dashboard</a></li>
          @endif
          @if(auth()->user()->isAdmin())
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.shipments.index') }}">Admin Shipments</a></li>
          @endif
          <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST">@csrf <button class="btn btn-link nav-link">Logout</button></form>
          </li>
        @else
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
        @endauth
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
  @yield('content')
</div>
</body>
</html>
