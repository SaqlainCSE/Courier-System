@extends('layouts.print')

@section('content')
<div class="container-fluid py-3">
    <div class="row g-3">
        @foreach($shipments as $shipment)
            <div class="col-6 print-invoice">
                <div class="invoice-card shadow-sm border rounded-3 h-100 d-flex flex-column justify-content-between p-3">
                    <!-- Header with QR -->
                    <div class="justify-content-between align-items-start">
                        <h6 class="fw-bold">
                            <i class="fas fa-shipping-fast text-danger me-2"></i> StepUp<span class="text-danger">Courier</span>
                        </h6>
                        <!-- Tracking -->
                        <p class="small mb-3"><strong>Tracking#:</strong> {{ $shipment->tracking_number }}</p>
                        <div class="text-end" style="margin-top: -50px;">
                            @php
                                $qrData = [
                                    'Tracking' => $shipment->tracking_number,
                                    'Pickup Name' => $shipment->customer?->business_name ?? '-',
                                    'Pickup Phone' => $shipment->customer?->phone ?? '-',
                                    'Drop Name' => $shipment->drop_name,
                                    'Drop Phone' => $shipment->drop_phone,
                                    'Drop Address' => $shipment->drop_address,
                                    'Weight (kg)' => $shipment->weight_kg,
                                    'COD Price' => $shipment->price,
                                    'Notes' => $shipment->notes ?? '-',
                                ];

                                $qrContent = json_encode($qrData, JSON_UNESCAPED_UNICODE);
                            @endphp
                                {!! QrCode::size(50)->generate($qrContent) !!}
                        </div>
                    </div>

                    <!-- Pickup -->
                    <div class="small mb-1">
                        <strong>Merchant:</strong><br>
                        Name: {{ $shipment->customer?->business_name ?? '-' }}<br>Phone: {{ $shipment->customer?->phone ?? '-' }}<br>
                        <span class="text-muted">Address: {{ $shipment->customer?->business_address ?? '-' }}</span>
                    </div>

                    <!-- Dropoff -->
                    <div class="small mb-1">
                        <strong>Customer:</strong><br>
                        Name: {{ $shipment->drop_name }} <br>Phone: {{ $shipment->drop_phone }}<br>
                        <span class="text-muted">Address: {{ $shipment->drop_address }}</span>
                    </div>

                    <!-- Price & Weight -->
                    <div class="d-flex justify-content-between small mb-2">
                        <span><strong>Weight:</strong> {{ $shipment->weight_kg }} kg</span>
                        <span><strong>COD:</strong> ৳ {{ number_format($shipment->price, 2) }}</span>
                    </div>

                    <!-- Notes -->
                    @if($shipment->notes)
                        <div class="small text-muted mb-2">
                            <strong>Notes:</strong> {{ Str::limit($shipment->notes, 60) }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    @media print {
        @page {
            size: A4 portrait; /* Portrait mode */
            margin: 8mm;       /* Small margins */
        }

        body {
            -webkit-print-color-adjust: exact;
            margin: 0;
            font-size: 11px;
        }

        .row.g-3 {
            display: flex;
            flex-wrap: wrap;
        }

        .print-invoice {
            page-break-inside: avoid;
            width: 50%;     /* 2 columns */
            padding: 6px;
            box-sizing: border-box;
        }

        .invoice-card {
            height: 90mm;   /* ✅ 3 rows = 3 × 90mm ≈ 270mm inside A4 */
            overflow: hidden;
            border: 1px solid #ccc;
            padding: 6px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
    }

    /* Responsive for screen (not print) */
    @media (max-width: 768px) {
        .print-invoice {
            width: 100% !important; /* full width on mobile */
        }
    }
</style>


@endsection
