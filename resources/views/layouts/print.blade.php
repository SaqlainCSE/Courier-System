<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Print')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 (CDN) - adjust to your project's local assets if needed -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Page layout */
        body { background: #fff; color: #222; -webkit-print-color-adjust: exact; }
        .print-container { max-width: 900px; margin: 0 auto; padding: 18px; }

        /* Hide UI not needed on print */
        .no-print { display: none; }

        /* Improve table and card printing */
        .card { border: 1px solid #e9ecef; }
        .card-header, .card-body { padding: .75rem 1rem; }

        /* Remove link urls in print */
        a[href]:after { content: ""; }

        /* Print-specific tweaks */
        @media print {
            .print-container { padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <div>
                <h5 class="mb-0">@yield('title', 'Document')</h5>
                <small class="text-muted">@yield('subtitle')</small>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        @yield('content')
    </div>

    <!-- Optional JS (kept minimal) -->
    <script>
        // Optionally auto-print when opened in a new tab
        // if (new URLSearchParams(window.location.search).has('autoprint')) {
        //     window.print();
        // }
    </script>
</body>
</html>
