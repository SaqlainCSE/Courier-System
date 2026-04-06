<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentStatusLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ShipmentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    public function dashboard(Request $request)
    {
        $user = Auth::user();

        $query = Shipment::with('payments')->where('user_id', $user->id);

        // 🔍 Search filter
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                ->orWhere('pickup_address', 'like', "%{$search}%")
                ->orWhere('drop_address', 'like', "%{$search}%")
                ->orWhere('pickup_name', 'like', "%{$search}%")
                ->orWhere('drop_name', 'like', "%{$search}%")
                ->orWhere('pickup_phone', 'like', "%{$search}%")
                ->orWhere('drop_phone', 'like', "%{$search}%");
            });
        }

        // 📅 Date filter (created date for listing)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 📌 Status filter
        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->whereIn('status', ['delivered', 'partially_delivered'])
                    ->where('balance_cost', '<=', 0);
            } else {
                $query->where('status', $request->status);
            }
        }

        // 📦 Shipments list
        $shipments = $query->latest()->paginate(20);

        // ================= SUMMARY =================
        $summary = [
            'pending' => Shipment::where('user_id', $user->id)->where('status', 'pending')->count(),
            'assigned' => Shipment::where('user_id', $user->id)->where('status', 'assigned')->count(),
            'picked' => Shipment::where('user_id', $user->id)->where('status', 'picked')->count(),
            'in_transit' => Shipment::where('user_id', $user->id)->where('status', 'in_transit')->count(),
            'delivered' => Shipment::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'hold' => Shipment::where('user_id', $user->id)->where('status', 'hold')->count(),
            'cancelled' => Shipment::where('user_id', $user->id)->where('status', 'cancelled')->count(),
            'partially_delivered' => Shipment::where('user_id', $user->id)->where('status', 'partially_delivered')->count(),
        ];

        // ================= ENTRY BALANCE =================
        $entryBalance = Shipment::where('user_id', $user->id)->sum('price');

        // ================= COD BALANCE =================
        $codBalance = Shipment::where('user_id', $user->id)
            ->whereIn('status', ['delivered', 'partially_delivered'])
            ->sum('balance_cost');

        // ================= PAID AMOUNT =================
        $paidAmount = Payment::whereHas('shipment', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'paid')
        ->sum('amount');

        // ================= FINAL DUE =================
        $newCOD = $codBalance - $paidAmount;

        // ================= MONTHLY REPORT =================
        $monthlyCosts = Shipment::where('user_id', $user->id)
            ->whereIn('status', ['delivered','partially_delivered'])
            ->whereNotNull('delivered_at')
            ->selectRaw("DATE_FORMAT(delivered_at, '%Y-%m') as month, SUM(balance_cost) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return view('shipments.dashboard', compact(
            'shipments',
            'summary',
            'entryBalance',
            'codBalance',
            'paidAmount',
            'newCOD',
            'monthlyCosts'
        ))->with('filters', $request->only(['q','status','start_date','end_date']));
    }

    public function create()
    {
        return view('shipments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'drop_name' => 'required|string|max:255',
            'drop_phone' => 'required|regex:/^01[3-9][0-9]{8}$/',
            'drop_address' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
        ]);

        // Inputs
        $weight = $request->weight_kg;
        $total_price_of_product = $request->price;

        // ---- Cost Breakdown ----
        $deliveryFee = Auth::user()->delivery_fee ?? 60;
        $codFee           = 0;   // future feature
        $discount         = 0;   // future feature
        $promoDiscount    = 0;   // future feature
        $compensationCost = 0;   // future feature
        $additionalCharge = max(0, ceil($weight - 1) * 10);

        $costOfDeliveryAmount = $deliveryFee + $codFee + $additionalCharge - $discount - $promoDiscount + $compensationCost;

        // Balance cost calculation
        // if ($total_price_of_product == 0) {
        //     $balanceCost = $costOfDeliveryAmount;
        // } else {
        //     $balanceCost = $total_price_of_product - $costOfDeliveryAmount;
        // }

        // ---- Tracking Number ----
        $buss_name = trim(Auth::user()->business_name ?? '');
        $namePrefix = 'STUP';

        if (!empty($buss_name)) {
            $parts = preg_split('/\s+/', $buss_name);
            if (count($parts) === 1) {
                $namePrefix = strtoupper(substr($parts[0], 0, 3));
            } else {
                $namePrefix = strtoupper(implode('', array_map(fn($p) => substr($p, 0, 1), array_slice($parts, 0, 3))));
            }
        }

        $tracking = $namePrefix . strtoupper(uniqid());

        // ---- Save Shipment ----
        Shipment::create([
            'tracking_number' => $tracking,
            'user_id' => Auth::id(),
            'drop_name' => $request->drop_name,
            'drop_phone' => $request->drop_phone,
            'drop_address' => $request->drop_address,
            'weight_kg' => $weight,
            'price' => $total_price_of_product,
            'cost_of_delivery_amount' => $costOfDeliveryAmount,
            'additional_charge' => $additionalCharge,
            // 'balance_cost' => $balanceCost,
            'notes' => $request->notes,
        ]);

        return redirect()->route('shipments.dashboard')
                        ->with('success', 'Shipment created successfully!');
    }

    public function show(Shipment $shipment)
    {
        $user = Auth::user();

        // Authorization: only allow the owner (customer) to view
        // if ($user->role !== 'customer' || $shipment->user_id !== $user->id) {
        //     abort(403, 'Unauthorized access.');
        // }

        // Estimated delivery formatting
        $estimated = $shipment->estimated_delivery_at
            ? $shipment->estimated_delivery_at->format('d M Y, H:i')
            : 'Not Estimated';

        // Get assigned courier
        $courier = $shipment->courier; // Shipment -> Courier model
        $courierUser = $courier?->user; // Courier -> User model

        $courierName = $courierUser?->name ?? 'Not Assigned';
        $courierPhone = $courierUser?->phone ?? '';

        $costDetails = [
            'pickupName'        => $user->name,
            'pickupPhone'       => $user->phone,
            'pickupAddress'     => $user->business_address,
            'deliveryManName'    => $courierName,
            'deliveryManPhone'   => $courierPhone,
            'price'              => $shipment->price,
            'costOfDelivery'     => 60,
            'additionalCharge'   => $shipment->additional_charge,
            'balanceCost'        => $shipment->balance_cost,
        ];

        $logs = ShipmentStatusLog::where('shipment_id', $shipment->id)
                        ->with('deliveryMan')
                        ->latest()
                        ->get();

        return view('shipments.show', compact('shipment', 'estimated', 'costDetails', 'logs'));
    }


    public function edit(Shipment $shipment)
    {
        $user = Auth::user();

        // Authorization: only allow the owner (customer) to edit
        if ($user->role !== 'customer' || $shipment->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('shipments.edit', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $user = Auth::user();

        // Authorization again
        if ($user->role !== 'customer' || $shipment->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        // Validation rules (similar to store)
        $request->validate([
            'drop_name'              => 'required|string|max:255',
            'drop_phone'             => 'required|regex:/^01[3-9][0-9]{8}$/',
            'drop_address'           => 'required|string',
            'weight_kg'              => 'required|numeric|min:0.1',
            'notes'                  => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
        ]);

        // Recalculate costs
        $weight = $request->weight_kg;
        $total_price_of_product = $request->price;

        $deliveryFee = Auth::user()->delivery_fee ?? 60;
        $codFee           = 0;
        $discount         = 0;
        $promoDiscount    = 0;
        $additionalCharge = max(0, ceil($weight - 1) * 10);
        $compensationCost = 0;

        $costOfDeliveryAmount = $deliveryFee + $codFee + $additionalCharge - $discount - $promoDiscount + $compensationCost;

        if ($total_price_of_product == 0) {
            $balanceCost = $costOfDeliveryAmount;
        } else {
            $balanceCost = $total_price_of_product - $costOfDeliveryAmount;
        }

        // Update shipment
        $shipment->update([
            'drop_name'              => $request->drop_name,
            'drop_phone'             => $request->drop_phone,
            'drop_address'           => $request->drop_address,
            'weight_kg'              => $weight,
            'price'                  => $total_price_of_product,
            'cost_of_delivery_amount'=> $costOfDeliveryAmount,
            'additional_charge' => $additionalCharge,
            'balance_cost'           => $balanceCost,
            'notes'                  => $request->notes,
        ]);

        return redirect()->route('shipments.show', $shipment)->with('success', 'Shipment updated successfully!');
    }

    // public function cancel(Shipment $shipment)
    // {
    //     if ($shipment->status === 'pending') {
    //         $shipment->update(['status' => 'cancelled']);
    //     }
    //     return redirect()->route('shipments.dashboard')->with('success', 'Shipment cancelled successfully.');
    // }

    public function getDropoffDetails(Request $request)
    {
        $phone = $request->get('drop_phone');
        $shipment = Shipment::where('drop_phone', $phone)->latest()->first();

        if ($shipment) {
            return response()->json([
                'success' => true,
                'data' => [
                    'drop_name' => $shipment->drop_name,
                    'drop_address' => $shipment->drop_address,
                    'drop_district' => $shipment->drop_district,
                    'drop_area' => $shipment->drop_area,
                    'drop_street' => $shipment->drop_street,
                ]
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function print(Shipment $shipment)
    {
        $user = Auth::user();

        if (
            $user->role === 'customer' && $shipment->user_id !== $user->id
            || $user->isCourier() && $shipment->courier_id !== $user->courierProfile?->id
        ) {
            abort(403, 'Unauthorized access.');
        }

        $shipment->load(['courier', 'customer']);

        return view('shipments.print', compact('shipment'));
    }

    public function printAll(Request $request)
    {
        $shipments = Shipment::with(['courier', 'customer', 'statusLogs'])
                                    ->whereIn('status', ['assigned', 'in_transit'])
                                    ->latest()
                                    ->get();

        return view('shipments.print-multi', compact('shipments'));
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['q','status','start_date','end_date']);
        $query = Shipment::where('user_id', Auth::id());

        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                ->orWhere('pickup_address', 'like', "%{$search}%")
                ->orWhere('drop_address', 'like', "%{$search}%")
                ->orWhere('pickup_name', 'like', "%{$search}%")
                ->orWhere('drop_name', 'like', "%{$search}%")
                ->orWhere('pickup_phone', 'like', "%{$search}%")
                ->orWhere('drop_phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['start_date'])) $query->whereDate('created_at', '>=', $filters['start_date']);
        if (!empty($filters['end_date'])) $query->whereDate('created_at', '<=', $filters['end_date']);

        $shipments = $query->latest()->get();

        $pdf = Pdf::loadView('shipments.exports.pdf', compact('shipments'));
        return $pdf->download('shipments_' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['q','status','start_date','end_date']);
        return Excel::download(new ShipmentsExport($filters), 'shipments_' . date('Y-m-d') . '.xlsx');
    }

}
