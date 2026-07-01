<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $paymentInvoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; padding: 30px; color: #333; }
        .invoice-box { max-width: 850px; margin: auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .brand h1 { font-size: 28px; font-weight: 800; }
        .brand h1 span { color: #dc3545; }
        .brand p { color: #888; font-size: 13px; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { font-size: 22px; color: #dc3545; font-weight: 700; }
        .invoice-meta p { font-size: 13px; color: #555; }
        .divider { border: none; border-top: 2px solid #f0f0f0; margin: 20px 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .info-box h6 { font-size: 12px; text-transform: uppercase; color: #999; margin-bottom: 6px; letter-spacing: 0.5px; }
        .info-box p { font-size: 14px; font-weight: 600; }
        .info-box p.sub { font-weight: 400; color: #666; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table thead tr { background: #1a1a2e; color: #fff; }
        table th, table td { padding: 12px 15px; text-align: left; font-size: 14px; }
        table tbody tr { border-bottom: 1px solid #f0f0f0; }
        table tbody tr:last-child { border: none; }
        .status-badge { display: inline-block; font-size: 11px; padding: 2px 8px; border-radius: 10px; background: #f8d7da; color: #842029; margin-left: 6px; }
        .total-section { text-align: right; margin-bottom: 20px; }
        .total-section .total-row { display: flex; justify-content: flex-end; gap: 40px; margin-bottom: 6px; font-size: 14px; color: #555; }
        .total-section .total-row.grand { font-size: 18px; font-weight: 800; color: #198754; border-top: 2px solid #f0f0f0; padding-top: 10px; margin-top: 10px; }
        .badge-paid { display: inline-block; background: #d4edda; color: #155724; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .text-danger { color: #dc3545; }
        .footer { text-align: center; font-size: 12px; color: #aaa; margin-top: 30px; }
        .print-btn { display: block; text-align: center; margin: 20px auto 0; }
        @media print {
            body { background: #fff; padding: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <div class="brand">
            <h1>StepUp<span>Courier</span></h1>
            <p>Fast • Reliable • Secure Deliveries</p>
        </div>
        <div class="invoice-meta">
            <h2>INVOICE</h2>
            <p><strong>{{ $paymentInvoice->invoice_number }}</strong></p>
            <span class="badge-paid">✓ PAID</span>
            <p class="sub" style="margin-top:8px;">{{ $paymentInvoice->payments->count() }} Shipment(s)</p>
        </div>
    </div>

    <hr class="divider">

    <div class="info-grid">
        <div class="info-box">
            <h6>Merchant Info</h6>
            <p>{{ $paymentInvoice->user->business_name ?? $paymentInvoice->user->name }}</p>
            <p class="sub">{{ $paymentInvoice->user->phone }}</p>
            <p class="sub">{{ $paymentInvoice->user->email }}</p>
        </div>
        <div class="info-box">
            <h6>Payment Info</h6>
            <p>Status: Paid</p>
            <p class="sub">Paid At: {{ $paymentInvoice->created_at->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    @php
        $totalCod = 0;
        $totalDeliveryDeduction = 0;
        $totalNet = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tracking</th>
                <th>COD Amount</th>
                <th>Delivery Charge</th>
                <th>Net Payable</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentInvoice->payments as $index => $payment)
                @php
                    $shipment = $payment->shipment;
                    $meta = json_decode($payment->meta, true) ?? [];
                    $status = $meta['status'] ?? $shipment->status;
                    $isChargeDeduction = in_array($status, ['merchant_pay', 'cancelled'], true);

                    if (isset($meta['cod_amount'], $meta['delivery_charge'])) {
                        $codAmount = (float) $meta['cod_amount'];
                        $deliveryCharge = (float) $meta['delivery_charge'];
                        $netAmount = (float) ($meta['net_amount'] ?? $payment->amount);
                    } elseif ($isChargeDeduction) {
                        $codAmount = 0;
                        $deliveryCharge = abs((float) $payment->amount);
                        $netAmount = (float) $payment->amount;
                    } elseif ($shipment->status === 'partially_delivered') {
                        $codAmount = (float) ($shipment->partial_price ?? 0);
                        $deliveryCharge = (float) ($shipment->cost_of_delivery_amount ?? 0);
                        $netAmount = (float) $payment->amount;
                    } else {
                        $codAmount = (float) ($shipment->price ?? 0);
                        $deliveryCharge = (float) ($shipment->cost_of_delivery_amount ?? 0);
                        $netAmount = (float) $payment->amount;
                    }

                    $totalCod += $codAmount;
                    if ($isChargeDeduction) {
                        $totalDeliveryDeduction += $deliveryCharge;
                    }
                    $totalNet += $netAmount;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $shipment->tracking_number }}
                        @if($isChargeDeduction)
                            <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                        @endif
                    </td>
                    <td>৳ {{ number_format($codAmount, 2) }}</td>
                    <td class="{{ $isChargeDeduction ? 'text-danger' : '' }}">
                        @if($isChargeDeduction)
                            -৳ {{ number_format($deliveryCharge, 2) }}
                        @else
                            ৳ {{ number_format($deliveryCharge, 2) }}
                        @endif
                    </td>
                    <td class="{{ $netAmount < 0 ? 'text-danger' : '' }}">
                        ৳ {{ number_format($netAmount, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <!-- <div class="total-row">
            <span>Total COD Amount</span>
            <span>৳ {{ number_format($totalCod, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Total Delivery Charge (Cancelled / Merchant Pay)</span>
            <span class="text-danger">-৳ {{ number_format($totalDeliveryDeduction, 2) }}</span>
        </div> -->
        <div class="total-row">
            <span>Net Payable ({{ $paymentInvoice->payments->count() }} shipments)</span>
            <span>৳ {{ number_format($totalNet, 2) }}</span>
        </div>
        <div class="total-row grand">
            <span>Grand Total Paid to Merchant</span>
            <span>৳ {{ number_format($paymentInvoice->total_amount, 2) }}</span>
        </div>
    </div>

    <hr class="divider">

    <div class="footer">
        <p>Thank you for using StepUpCourier!</p>
        <p><em>Cancelled / Merchant Pay shipments: delivery charge is deducted from merchant payout.</em></p>
        <p>This is a system-generated invoice. No signature required.</p>
    </div>
</div>

<div class="print-btn">
    <button onclick="window.print()"
        style="padding: 10px 30px; background: #dc3545; color: #fff; border: none; border-radius: 8px; font-size: 15px; cursor: pointer;">
        🖨️ Print Invoice
    </button>
</div>

</body>
</html>
