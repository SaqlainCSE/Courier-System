<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    public function dashboard()
    {
        $user = Auth::user();

        // Recent shipments (latest 10)
        $shipments = Shipment::where('user_id', $user->id)->where('created_at', '>=', now()->subMonths(3))->latest()->paginate(20);

        // Summary counts
        $summary = [
            'pending' => Shipment::where('user_id', $user->id)->where('status', 'pending')->count(),
            'in_transit' => Shipment::where('user_id', $user->id)->whereIn('status', ['assigned','picked','in_transit'])->count(),
            'delivered' => Shipment::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'cancelled' => Shipment::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        // ✅ Total cost for delivered shipments only
        $totalCost = Shipment::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->sum('price');

        // ✅ Monthly cost (delivered only)
        $monthlyCosts = Shipment::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(price) as total")
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return view('shipments.dashboard', compact('shipments', 'summary', 'totalCost', 'monthlyCosts'));
    }

    public function create()
    {
        return view('shipments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pickup_name' => 'required|string|max:255',
            'pickup_phone' => 'required|string|max:20',
            'pickup_address' => 'required|string',
            'drop_name' => 'required|string|max:255',
            'drop_phone' => 'required|string|max:20',
            'drop_address' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500',
            'total_price_of_product' => 'required|numeric|min:1',
        ]);

        // Weight
        $weight = $request->weight_kg;

        // ---- Cost Breakdown ----
        $total_price_of_product = $request->total_price_of_product;

        $deliveryFee      = 60;
        $codFee           = 0;
        $discount         = 0;
        $promoDiscount    = 0;
        $additionalCharge = max(0, ceil($weight - 1) * 10);
        $compensationCost = 0;

        $costOfDeliveryAmount = $deliveryFee + $codFee + $additionalCharge - $discount - $promoDiscount + $compensationCost;
        // $totalCost = $total_price_of_product +  $costOfDeliveryAmount;

        $buss_name = trim(Auth::user()->business_name ?? '');
        $nameParts = explode(' ', $buss_name);
        $namePrefix = 'STUP';

        if (!empty($buss_name)) {

            if (count($nameParts) === 1) {

                $namePrefix = strtoupper(substr($buss_name, 0, 3));

            } else {

                $initialsArray = array_map(
                    fn($part) => strtoupper(substr($part, 0, 1)),
                    array_slice($nameParts, 0, 3)
                );
                $namePrefix = implode('', $initialsArray);

                if (empty($namePrefix)) {
                    $namePrefix = 'STUP';
                }
            }
        }

        $tracking = $namePrefix . strtoupper(uniqid());

        Shipment::create([
            'tracking_number' => $tracking,
            'user_id' => Auth::id(),
            'pickup_name' => $request->pickup_name,
            'pickup_phone' => $request->pickup_phone,
            'pickup_address' => $request->pickup_address,
            'drop_name' => $request->drop_name,
            'drop_phone' => $request->drop_phone,
            'drop_address' => $request->drop_address,
            'weight_kg' => $weight,
            'price' => $total_price_of_product,
            'cost_of_delivery_amount' => $costOfDeliveryAmount,
            'notes' => $request->notes,
        ]);

        return redirect()->route('shipments.dashboard')->with('success', 'Shipment created successfully!');
    }

    public function show(Shipment $shipment)
    {
        $user = Auth::user();

        if ($user->role !== 'customer' || $shipment->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $estimated = $shipment->estimated_delivery_at
            ? $shipment->estimated_delivery_at->format('d M Y, H:i')
            : 'Not Estimated';

        //Cost calculations moved here
        $deliveryFee = 60;
        $codFee = 0;
        $discount = 0;
        $promoDiscount = 0;
        $additionalCharge = max(0, ceil($shipment->weight_kg - 1) * 10);
        $compensationCost = 0;

        $totalCost = $shipment->price  +  $shipment->cost_of_delivery_amount;

        // Pass all cost details as array
        $costDetails = [
            'deliveryFee' => $deliveryFee,
            'codFee' => $codFee,
            'discount' => $discount,
            'promoDiscount' => $promoDiscount,
            'additionalCharge' => $additionalCharge,
            'compensationCost' => $compensationCost,
            'totalCost' => $totalCost,
        ];

        return view('shipments.show', compact('shipment', 'estimated', 'costDetails'));
    }



    public function cancel(Shipment $shipment)
    {
        if ($shipment->status === 'pending') {
            $shipment->update(['status' => 'cancelled']);
        }
        return redirect()->route('shipments.dashboard')->with('success', 'Shipment cancelled successfully.');
    }
}
