<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shipment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('shipments');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhereHas('shipments', function($q2) use ($search) {
                    $q2->where('tracking_number', 'like', "%{$search}%");
                });
            });
        }

        $merchants = $query->with(['shipments'])->paginate(20);

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

        return response()->json([
            'success' => true,
            'message' => 'Payment adjusted successfully.',
            'balance_cost' => $shipment->balance_cost,
        ]);
    }
}
