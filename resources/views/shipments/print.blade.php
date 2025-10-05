@extends('layouts.print')

@section('title', 'Shipment Invoice')
@section('content')

<div class="invoice-head">
    <div class="logo">
        {{-- Replace src with your logo asset if available --}}
        <img src="{{ asset('images/logo.png') }}" alt="logo" onerror="this.style.display='none'">
        <div>
            <h2 class="fw-bold"><i class="fas fa-shipping-fast text-danger me-2"></i>
            StepUp<span class="text-danger">Courier</span></h2>
            <div class="small-muted">Fast • Reliable • Secure Deliveries</div>
        </div>
    </div>

    <div class="meta">
        <div class="h5">INVOICE</div>
        <div class="small-muted">Tracking #: <strong class="text-dark">{{ $shipment->tracking_number }}</strong></div>
        <div class="small-muted">Status: <strong>{{ ucwords(str_replace('_',' ',$shipment->status)) }}</strong></div>
        <div class="small-muted">Date: <strong>{{ $shipment->created_at?->format('d M Y') }}</strong></div>
    </div>
</div>

{{-- Addresses --}}
<div class="row mb-3">
    <div class="col-md-6">
        <div class="section-title">Pickup / Sender</div>
        <div class="section-content">{{ $shipment->customer?->business_name ?? ($shipment->pickup_name ?? '-') }}</div>
        <div class="small-muted">{{ $shipment->customer?->name ?? $shipment->pickup_name ?? '-' }} • {{ $shipment->customer?->phone ?? $shipment->pickup_phone ?? '-' }}</div>
        <div class="small-muted mt-2">{{ $shipment->customer?->business_address ?? $shipment->pickup_address ?? '-' }}</div>
    </div>

    <div class="col-md-6 text-end">
        <div class="section-title">Dropoff / Recipient</div>
        <div class="section-content">{{ $shipment->drop_name ?? '-' }}</div>
        <div class="small-muted">{{ $shipment->drop_phone ?? '-' }}</div>
        <div class="small-muted mt-2">{{ $shipment->drop_address ?? '-' }}</div>
    </div>
</div>

{{-- Items / Details table --}}
<table class="table mb-0">
    <thead>
        <tr>
            <th style="width:60%;">Description</th>
            <th class="text-center" style="width:12%;">Weight</th>
            <th class="text-end" style="width:14%;">Unit Price</th>
            <th class="text-end" style="width:14%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div class="fw-semibold">Shipment — {{ $shipment->tracking_number }}</div>
                <div class="small-muted">{{ Str::limit($shipment->pickup_address.' → '.$shipment->drop_address, 140) }}</div>
            </td>
            <td class="text-center">{{ $shipment->weight_kg ?? '0' }} kg</td>
            <td class="text-end">৳ {{ number_format($shipment->price ?? 0, 2) }}</td>
            <td class="text-end">৳ {{ number_format($shipment->price ?? 0, 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td class="text-end small-muted">Subtotal</td>
            <td class="text-end">৳ {{ number_format($shipment->price ?? 0, 2) }}</td>
        </tr>
        {{-- Example tax/discount rows if needed --}}
        {{-- <tr>
            <td></td>
            <td></td>
            <td class="text-end small-muted">Tax (0%)</td>
            <td class="text-end">৳ 0.00</td>
        </tr> --}}
        <tr class="totals-row">
            <td></td>
            <td></td>
            <td class="text-end">Total</td>
            <td class="text-end">৳ {{ number_format($shipment->price ?? 0, 2) }}</td>
        </tr>
    </tfoot>
</table>

{{-- Notes and Barcode --}}
<div class="row mt-4 g-3">
    <div class="col-md-6">
        @if($shipment->notes)
            <div class="notes">
                <div class="section-title">Notes</div>
                <div class="mt-1">{{ $shipment->notes }}</div>
            </div>
        @else
            <div class="small-muted">No additional notes.</div>
        @endif
    </div>

    <div class="col-md-6 text-end">
        <div class="mb-2 small-muted">Assigned Courier</div>
        <div class="fw-semibold">{{ $shipment->courier?->user?->business_name ?? ($shipment->courier?->user?->name ?? 'Unassigned') }}</div>
        <div class="small-muted">{{ $shipment->courier?->user?->phone ?? '-' }}</div>

        <div class="mt-3">
            {{-- Simple barcode / tracking block (replace with real barcode image if available) --}}
            <div class="barcode mt-2">{{ $shipment->tracking_number }}</div>
            <div class="small-muted mt-1">Scan / Ref</div>
        </div>
    </div>
</div>

{{-- Signature --}}
<div class="signature">
    <div class="sig-box text-start">
        <div class="small-muted">Delivered By</div>
        <div style="height:10px;"></div>
        <div>______________________</div>
    </div>

    <div class="sig-box text-end">
        <div class="small-muted">Received By</div>
        <div style="height:10px;"></div>
        <div>______________________</div>
    </div>
</div>
@endsection

<style>
    :root{
        --brand: #0d6efd;
        --muted:#6c757d;
        --paper:#ffffff;
    }

    /* Page setup for printing */
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    html, body {
        height: 100%;
        background: #f4f6f9;
        -webkit-print-color-adjust: exact;
        font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        color: #222;
    }

    .print-wrap{
        max-width: 186mm; /* A4 width (210mm) - 2*12mm page margin */
        width: 100%;
        margin: 0 auto;
        padding: 14mm;
        background: var(--paper);
        border-radius: 8px;
        box-shadow: 0 6px 20px rgba(25,39,58,.06);
        box-sizing: border-box;
    }

    .no-print { display: block; }

    /* ...other existing styles... */

    /* Ensure columns remain side-by-side in print */
    @media print {
        html, body {
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .print-wrap{
            margin: 0 auto;
            box-shadow: none;
            border-radius: 0;
            padding: 12mm;
            -webkit-print-color-adjust: exact;
        }

        /* Force row to behave as flex so col-md-* widths apply */
        .row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-right: 0;
            margin-left: 0;
        }

        /* Ensure Bootstrap column classes keep their widths in print */
        .row > [class*="col-"] {
            box-sizing: border-box;
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }

        .col-md-6 { flex: 0 0 50% !important; max-width: 50% !important; }
        .col-md-4 { flex: 0 0 33.333333% !important; max-width: 33.333333% !important; }
        .col-md-3 { flex: 0 0 25% !important; max-width: 25% !important; }
        .col-md-12 { flex: 0 0 100% !important; max-width: 100% !important; }

        /* Hide interactive UI */
        .no-print{ display: none !important; }

        /* Prevent table rows splitting badly */
        table { page-break-inside: auto; }
        tr    { page-break-inside: avoid; page-break-after: auto; }
    }
</style>
