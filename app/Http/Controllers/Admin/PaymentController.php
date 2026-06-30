<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\User;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private array $deliveredStatuses = ['delivered', 'partially_delivered', 'cancelled', 'merchant_pay'];

    public function index(Request $request)
    {
        $statuses = $this->deliveredStatuses;

        $query = User::whereHas('shipments', function ($q) use ($statuses) {
            $q->whereIn('status', $statuses);
        });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search, $statuses) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('shipments', function ($q2) use ($search, $statuses) {
                        $q2->where('tracking_number', 'like', "%{$search}%")
                           ->whereIn('status', $statuses);
                    });
            });
        }

        $merchants = $query->with(['shipments' => function ($q) use ($statuses) {
            $q->whereIn('status', $statuses)->with('payments');
        }])->paginate(20);

        return view('admin.payments.index', compact('merchants'));
    }

    public function adjustPayment(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'amount'      => 'required|numeric|min:0',
        ]);

        $shipment = Shipment::findOrFail($request->shipment_id);
        $this->applyPaymentToShipment($shipment, $request->amount);

        $payment = Payment::create([
            'shipment_id' => $shipment->id,
            'amount'      => $request->amount,
            'method'      => 'cash',
            'status'      => 'paid',
            'meta'        => json_encode([
                'adjusted_at' => now(),
                'adjusted_by' => 'admin',
            ]),
        ]);

        $remainingBalance = $this->getRemainingBalance($shipment);

        return response()->json([
            'success'        => true,
            'message'        => 'Payment adjusted successfully.',
            'balance_cost'   => $remainingBalance,
            'invoice_number' => $payment->invoice_number,
            'invoice_url'    => route('admin.payments.invoice', $payment->id),
        ]);
    }

    public function bulkAdjustPayment(Request $request)
    {
        $request->validate([
            'shipment_ids'   => 'required|array|min:1',
            'shipment_ids.*' => 'exists:shipments,id',
        ]);

        $shipments = Shipment::whereIn('id', $request->shipment_ids)->get();

        if ($shipments->isEmpty()) {
            return response()->json(['message' => 'No shipments found.'], 422);
        }

        $merchantIds = $shipments->pluck('user_id')->unique();
        if ($merchantIds->count() > 1) {
            return response()->json([
                'message' => 'All selected shipments must belong to the same merchant.',
            ], 422);
        }

        $payableItems = [];
        foreach ($shipments as $shipment) {
            $balance = $this->getPayableBalance($shipment);
            $payableItems[] = ['shipment' => $shipment, 'amount' => $balance];
        }

        $totalAmount = collect($payableItems)->sum('amount');
        $merchantId  = $merchantIds->first();

        $paymentInvoice = DB::transaction(function () use ($payableItems, $totalAmount, $merchantId) {
            $invoice = PaymentInvoice::create([
                'user_id'      => $merchantId,
                'total_amount' => $totalAmount,
                'method'       => 'cash',
                'status'       => 'paid',
                'meta'         => json_encode([
                    'adjusted_at' => now(),
                    'adjusted_by' => 'admin',
                    'shipment_count' => count($payableItems),
                ]),
            ]);

            foreach ($payableItems as $item) {
                $shipment = $item['shipment'];
                $amount   = $item['amount'];
                $lineMeta = $this->buildPaymentLineMeta($shipment, $amount);

                $this->applyPaymentToShipment($shipment, $amount);

                Payment::create([
                    'shipment_id'        => $shipment->id,
                    'payment_invoice_id' => $invoice->id,
                    'amount'             => $amount,
                    'method'             => 'cash',
                    'status'             => 'paid',
                    'meta'               => json_encode($lineMeta),
                ]);
            }

            return $invoice;
        });

        return response()->json([
            'success'        => true,
            'message'        => count($payableItems) . ' shipment(s) paid successfully.',
            'invoice_number' => $paymentInvoice->invoice_number,
            'invoice_url'    => route('admin.payments.bulk-invoice', $paymentInvoice->id),
            'total_amount'   => $totalAmount,
            'shipment_ids'   => collect($payableItems)->pluck('shipment.id'),
            'balances'       => collect($payableItems)->mapWithKeys(function ($item) {
                return [$item['shipment']->id => $this->getPayableBalance($item['shipment'])];
            }),
        ]);
    }

    public function invoice(Payment $payment)
    {
        $payment->load('shipment.user');
        return view('admin.payments.invoice', compact('payment'));
    }

    public function bulkInvoice(PaymentInvoice $paymentInvoice)
    {
        $paymentInvoice->load(['user', 'payments.shipment']);
        return view('admin.payments.bulk-invoice', compact('paymentInvoice'));
    }

    public function invoices(Request $request)
    {
        $paymentQuery = Payment::with(['shipment.user'])
            ->whereNull('payment_invoice_id')
            ->orderByDesc('created_at');

        $invoiceQuery = PaymentInvoice::with(['user', 'payments.shipment'])
            ->orderByDesc('created_at');

        if ($merchantId = $request->input('merchant_id')) {
            $paymentQuery->whereHas('shipment', fn ($q) => $q->where('user_id', $merchantId));
            $invoiceQuery->where('user_id', $merchantId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $paymentQuery->whereDate('created_at', '>=', $dateFrom);
            $invoiceQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $paymentQuery->whereDate('created_at', '<=', $dateTo);
            $invoiceQuery->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = $request->input('search')) {
            $paymentQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('shipment', fn ($q2) => $q2->where('tracking_number', 'like', "%{$search}%"));
            });
            $invoiceQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('payments.shipment', fn ($q2) => $q2->where('tracking_number', 'like', "%{$search}%"));
            });
        }

        $singlePayments = $paymentQuery->get();
        $bulkInvoices   = $invoiceQuery->get();

        $allInvoices = collect()
            ->merge($singlePayments->map(fn ($p) => [
                'type'           => 'single',
                'invoice_number' => $p->invoice_number,
                'merchant'       => $p->shipment->user,
                'tracking'       => $p->shipment->tracking_number ?? '—',
                'amount'         => $p->amount,
                'created_at'     => $p->created_at,
                'url'            => route('admin.payments.invoice', $p->id),
            ]))
            ->merge($bulkInvoices->map(fn ($inv) => [
                'type'           => 'bulk',
                'invoice_number' => $inv->invoice_number,
                'merchant'       => $inv->user,
                'tracking'       => $inv->payments->count() . ' shipments',
                'amount'         => $inv->total_amount,
                'created_at'     => $inv->created_at,
                'url'            => route('admin.payments.bulk-invoice', $inv->id),
            ]))
            ->sortByDesc('created_at')
            ->values();

        $totalAmount   = $allInvoices->sum('amount');
        $totalInvoices = $allInvoices->count();

        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $items   = $allInvoices->slice(($page - 1) * $perPage, $perPage)->values();

        $payments = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allInvoices->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $merchants = User::where('role', 'customer')
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'phone']);

        return view('admin.payments.invoices', compact(
            'payments', 'totalAmount', 'totalInvoices', 'merchants'
        ));
    }

    private function getPayableBalance(Shipment $shipment): float
    {
        if ($shipment->status === 'partially_delivered') {
            return (float) ($shipment->partial_price - $shipment->cost_of_delivery_amount);
        }
        if (in_array($shipment->status, ['merchant_pay', 'cancelled'], true)) {
            return (float) (-$shipment->cost_of_delivery_amount);
        }

        return (float) $shipment->balance_cost;
    }

    private function getRemainingBalance(Shipment $shipment): float
    {
        return $this->getPayableBalance($shipment);
    }

    private function buildPaymentLineMeta(Shipment $shipment, float $amount): array
    {
        $deliveryCharge = (float) ($shipment->cost_of_delivery_amount ?? 0);

        if ($shipment->status === 'partially_delivered') {
            $codAmount = (float) ($shipment->partial_price ?? 0);
        } elseif (in_array($shipment->status, ['merchant_pay', 'cancelled'], true)) {
            $codAmount = 0;
        } else {
            $codAmount = (float) ($shipment->price ?? 0);
        }

        return [
            'adjusted_at'     => now()->toIso8601String(),
            'adjusted_by'     => 'admin',
            'bulk'            => true,
            'status'          => $shipment->status,
            'tracking_number' => $shipment->tracking_number,
            'cod_amount'      => $codAmount,
            'delivery_charge' => $deliveryCharge,
            'net_amount'      => $amount,
        ];
    }

    private function applyPaymentToShipment(Shipment $shipment, float $amount): void
    {
        if ($shipment->status === 'partially_delivered') {
            $shipment->partial_price = max(0, $shipment->partial_price - $amount);
        } elseif (in_array($shipment->status, ['merchant_pay', 'cancelled'], true)) {
            // Negative amount (e.g. -50) settles delivery charge
            $shipment->cost_of_delivery_amount = max(0, (float) $shipment->cost_of_delivery_amount + $amount);
        } else {
            $shipment->balance_cost = max(0, $shipment->balance_cost - $amount);
        }

        $shipment->save();
    }
}
