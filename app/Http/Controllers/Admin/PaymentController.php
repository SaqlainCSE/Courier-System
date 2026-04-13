<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Models\Shipment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('shipments', function($q) {
            $q->where('status', '=', 'delivered')
              ->orWhere('status', '=', 'partially_delivered');
        });

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhereHas('shipments', function($q2) use ($search) {
                    $q2->where('tracking_number', 'like', "%{$search}%")
                    ->where('status', '=', 'delivered')
                    ->orWhere('status', '=', 'partially_delivered');
                });
            });
        }

        $merchants = $query->with(['shipments' => function($q) {
            $q->where('status', '=', 'delivered')
              ->orWhere('status', '=', 'partially_delivered')
              ->with('payments');
        }])->paginate(20);

        return view('admin.payments.index', compact('merchants'));
    }

    public function adjustPayment(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $shipment = Shipment::findOrFail($request->shipment_id);

        $shipment->balance_cost -= $request->amount;

        if ($shipment->balance_cost < 0) {
            $shipment->balance_cost = 0;
        }

        $shipment->save();

        $payment = Payment::create([
            'shipment_id' => $shipment->id,
            'amount' => $request->amount,
            'method' => 'cash',
            'status' => 'paid',
            'meta' => json_encode(['adjusted_at' => now(), 'adjusted_by' => 'admin']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment adjusted successfully.',
            'balance_cost' => $shipment->balance_cost,
            'invoice_number' => $payment->invoice_number,
            'invoice_url' => route('admin.payments.invoice', $payment->id),
        ]);
    }

    public function invoice(Payment $payment)
    {
        $payment->load('shipment.customer');
        return view('admin.payments.invoice', compact('payment'));
    }

        public function invoices(Request $request)
    {
        $query = Payment::with(['shipment.user'])
            ->orderByDesc('created_at');

        // Merchant filter
        if ($merchantId = $request->input('merchant_id')) {
            $query->whereHas('shipment', function ($q) use ($merchantId) {
                $q->where('user_id', $merchantId);
            });
        }

        // Date range filter
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Search: invoice number, tracking
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('shipment', function ($q2) use ($search) {
                      $q2->where('tracking_number', 'like', "%{$search}%");
                  });
            });
        }

        // Summary totals (before pagination)
        $totalAmount   = (clone $query)->sum('amount');
        $totalInvoices = (clone $query)->count();

        $payments = $query->paginate(20);

        // All merchants for dropdown
        $merchants = \App\Models\User::where('role', 'customer')
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'phone']);

        return view('admin.payments.invoices', compact(
            'payments', 'totalAmount', 'totalInvoices', 'merchants'
        ));
    }
}
