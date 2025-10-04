<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Invoice')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome 6 CDN (for icons) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        :root{
            --brand: #0d6efd;
            --muted:#6c757d;
            --paper:#ffffff;
        }
        body{ background:#f4f6f9; font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; color:#222; -webkit-print-color-adjust:exact; }
        .print-wrap{ max-width:900px; margin:18px auto; padding:28px; background:var(--paper); border-radius:10px; box-shadow:0 6px 20px rgba(25,39,58,.06); }
        .brand { color:var(--brand); font-weight:700; letter-spacing:.2px; }
        .small-muted{ color:var(--muted); font-size:.85rem; }

        .invoice-head { border-bottom:1px solid #e9edf2; padding-bottom:18px; margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .logo { display:flex; align-items:center; gap:10px; }
        .logo img{ height:48px; width:auto; }

        .meta { text-align:right; }
        .meta .h5{ margin:0; }

        .table thead th{ border-bottom:2px solid #eef1f6; background:#fbfcfe; }
        .table td, .table th{ vertical-align:middle; border-top:1px solid #f1f4f8; }

        .totals-row td{ border-top:2px dashed #eef1f6; font-weight:700; }

        .notes{ background:#f8f9fb; border:1px solid #eef1f6; padding:12px; border-radius:6px; color:#495057; }
        .signature{ margin-top:28px; display:flex; justify-content:space-between; gap:24px; align-items:center; }
        .sig-box{ width:45%; text-align:center; color:var(--muted); font-size:.9rem; }
        .barcode { font-family: monospace; font-size:.9rem; letter-spacing:2px; padding:8px 12px; border-radius:6px; background:#f4f6fa; display:inline-block; }

        @media print{
            body{ background:#fff; }
            .print-wrap{ box-shadow:none; margin:0; border-radius:0; }
            .no-print{ display:none !important; }
        }
    </style>
</head>
<body>
    <div class="print-wrap">
        <div class="no-print mb-3 d-flex justify-content-end gap-2">
            <button class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
            <a class="btn btn-sm btn-outline-secondary" href="{{ url()->previous() }}">Back</a>
        </div>

        @yield('content')

        <div class="pt-3 mt-4 small-muted text-center">
            &copy; {{ date('Y') }} {{ config('app.name', 'StepUp Courier') }} — Generated {{ now()->format('d M Y • H:i') }}
        </div>
    </div>
</body>
</html>
