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

    // ✅ Reusable status list
    private array $deliveredStatuses = ['delivered', 'partially_delivered'];

    public function dashboard(Request $request)
    {
        $user = Auth::user();

        $query = Shipment::with('payments')->where('user_id', $user->id);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('pickup_address', 'like', "%{$search}%")
                    ->orWhere('drop_address', 'like', "%{$search}%")
                    ->orWhere('pickup_name', 'like', "%{$search}%")
                    ->orWhere('drop_name', 'like', "%{$search}%")
                    ->orWhere('pickup_phone', 'like', "%{$search}%")
                    ->orWhere('drop_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->whereIn('status', $this->deliveredStatuses)
                    ->where('balance_cost', '<=', 0);
            } else {
                $query->where('status', $request->status);
            }
        }

        $shipments = $query->latest()->paginate(20);

        // ✅ Bug 1 Fix: একটাই query দিয়ে সব status count
        $summaryCounts = Shipment::where('user_id', $user->id)
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'pending'            => $summaryCounts['pending'] ?? 0,
            'assigned'           => $summaryCounts['assigned'] ?? 0,
            'picked'             => $summaryCounts['picked'] ?? 0,
            'in_transit'         => $summaryCounts['in_transit'] ?? 0,
            'delivered'          => $summaryCounts['delivered'] ?? 0,
            'hold'               => $summaryCounts['hold'] ?? 0,
            'cancelled'          => $summaryCounts['cancelled'] ?? 0,
            'partially_delivered'=> $summaryCounts['partially_delivered'] ?? 0,
        ];

        $entryBalance = Shipment::where('user_id', $user->id)
                        ->whereDate('created_at', today())
                        ->sum('balance_cost');

        // $codBalance = Shipment::where('user_id', $user->id)
        //     ->whereIn('status', $this->deliveredStatuses)
        //     ->sum('balance_cost');

         // ✅ Bug Fix: delivered shipment gular balance_cost
        $deliveredBalance = Shipment::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->sum('balance_cost');

        // ✅ Bug Fix: partially_delivered shipment gular partial_price
        $partiallyDeliveredBalance = Shipment::where('user_id', $user->id)
            ->where('status', 'partially_delivered')
            ->sum('partial_price');

        // ✅ duitar sum mile codBalance hobe (delivered + partially_delivered)
        $codBalance = $deliveredBalance + $partiallyDeliveredBalance;

        $paidAmount = Payment::whereHas('shipment', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'paid')
        ->sum('amount');

        // ✅ Bug 7 Fix: negative হলে 0 দেখাবে
        $newCOD = max(0, $codBalance - $paidAmount);

        $monthlyCosts = Shipment::where('user_id', $user->id)
            ->whereIn('status', $this->deliveredStatuses)
            ->whereNotNull('delivered_at')
            ->selectRaw("DATE_FORMAT(delivered_at, '%Y-%m') as month, SUM(balance_cost) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return view('shipments.dashboard', compact(
            'shipments', 'summary', 'entryBalance',
            'codBalance', 'paidAmount', 'newCOD', 'monthlyCosts'
        ))->with('filters', $request->only(['q', 'status', 'start_date', 'end_date']));
    }

    public function create()
    {
        return view('shipments.create');
    }

    private function calculateCosts(float $weight, float $price): array
    {
        $deliveryFee      = Auth::user()->delivery_fee ?? 60;
        $additionalCharge = max(0, ceil($weight - 1) * 10);
        $costOfDelivery   = $deliveryFee + $additionalCharge;

        // ✅ Bug 2 Fix: store/update consistent balance_cost logic
        $balanceCost = ($price == 0)
            ? $costOfDelivery
            : $price - $costOfDelivery;

        return compact('deliveryFee', 'additionalCharge', 'costOfDelivery', 'balanceCost');
    }

    public function store(Request $request)
    {
        $request->validate([
            'drop_name'    => 'required|string|max:255',
            'drop_phone'   => 'required|regex:/^01[3-9][0-9]{8}$/',
            'drop_address' => 'required|string',
            'weight_kg'    => 'required|numeric|min:0.1',
            'notes'        => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
        ]);

        $costs = $this->calculateCosts($request->weight_kg, $request->price);

        $buss_name  = trim(Auth::user()->business_name ?? '');
        $namePrefix = 'STUP';

        if (!empty($buss_name)) {
            $parts = preg_split('/\s+/', $buss_name);
            $namePrefix = count($parts) === 1
                ? strtoupper(substr($parts[0], 0, 3))
                : strtoupper(implode('', array_map(fn($p) => substr($p, 0, 1), array_slice($parts, 0, 3))));
        }

        $tracking = $namePrefix . strtoupper(uniqid());

        Shipment::create([
            'tracking_number'         => $tracking,
            'user_id'                 => Auth::id(),
            'drop_name'               => $request->drop_name,
            'drop_phone'              => $request->drop_phone,
            'drop_address'            => $request->drop_address,
            'weight_kg'               => $request->weight_kg,
            'price'                   => $request->price,
            'cost_of_delivery_amount' => $costs['costOfDelivery'],
            'additional_charge'       => $costs['additionalCharge'],
            'balance_cost'            => $costs['balanceCost'],
            'notes'                   => $request->notes,
        ]);

        return redirect()->route('shipments.dashboard')
            ->with('success', 'Shipment created successfully!');
    }

    public function show(Shipment $shipment)
    {
        $user = Auth::user();

        $estimated = $shipment->estimated_delivery_at
            ? $shipment->estimated_delivery_at->format('d M Y, H:i')
            : 'Not Estimated';

        $courier     = $shipment->courier;
        $courierUser = $courier?->user;

        $costDetails = [
            'pickupName'       => $user->name,
            'pickupPhone'      => $user->phone,
            'pickupAddress'    => $user->business_address,
            'deliveryManName'  => $courierUser?->name ?? 'Not Assigned',
            'deliveryManPhone' => $courierUser?->phone ?? '',
            'price'            => $shipment->price,
            'costOfDelivery'   => $shipment->cost_of_delivery_amount, // ✅ Bug 3 Fix
            'additionalCharge' => $shipment->additional_charge,
            'balanceCost'      => $shipment->balance_cost,
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

        if ($user->role !== 'customer' || $shipment->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('shipments.edit', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $user = Auth::user();

        if ($user->role !== 'customer' || $shipment->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'drop_name'    => 'required|string|max:255',
            'drop_phone'   => 'required|regex:/^01[3-9][0-9]{8}$/',
            'drop_address' => 'required|string',
            'weight_kg'    => 'required|numeric|min:0.1',
            'notes'        => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
        ]);

        $costs = $this->calculateCosts($request->weight_kg, $request->price);

        $shipment->update([
            'drop_name'               => $request->drop_name,
            'drop_phone'              => $request->drop_phone,
            'drop_address'            => $request->drop_address,
            'weight_kg'               => $request->weight_kg,
            'price'                   => $request->price,
            'cost_of_delivery_amount' => $costs['costOfDelivery'],
            'additional_charge'       => $costs['additionalCharge'],
            'balance_cost'            => $costs['balanceCost'],
            'notes'                   => $request->notes,
        ]);

        return redirect()->route('shipments.show', $shipment)
            ->with('success', 'Shipment updated successfully!');
    }

    public function getDropoffDetails(Request $request)
    {
        $phone    = $request->get('drop_phone');
        $shipment = Shipment::where('drop_phone', $phone)->latest()->first();

        if ($shipment) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'drop_name'     => $shipment->drop_name,
                    'drop_address'  => $shipment->drop_address,
                    'drop_district' => $shipment->drop_district,
                    'drop_area'     => $shipment->drop_area,
                    'drop_street'   => $shipment->drop_street,
                ],
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function print(Shipment $shipment)
    {
        $user = Auth::user();

        // ✅ Bug 5 Fix: proper parentheses for operator precedence
        if (
            ($user->role === 'customer' && $shipment->user_id !== $user->id) ||
            ($user->isCourier() && $shipment->courier_id !== $user->courierProfile?->id)
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

    private function applyShipmentFilters($query, array $filters)
    {
        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('pickup_address', 'like', "%{$search}%")
                    ->orWhere('drop_address', 'like', "%{$search}%")
                    ->orWhere('pickup_name', 'like', "%{$search}%")
                    ->orWhere('drop_name', 'like', "%{$search}%")
                    ->orWhere('pickup_phone', 'like', "%{$search}%")
                    ->orWhere('drop_phone', 'like', "%{$search}%");
            });
        }

        // ✅ Bug 6 Fix: exportPdf-এও 'paid' special case handle
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'paid') {
                $query->whereIn('status', $this->deliveredStatuses)
                    ->where('balance_cost', '<=', 0);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query;
    }

    public function exportPdf(Request $request)
    {
        $filters  = $request->only(['q', 'status', 'start_date', 'end_date']);
        $query    = Shipment::where('user_id', Auth::id());
        $shipments = $this->applyShipmentFilters($query, $filters)->latest()->get();

        $pdf = Pdf::loadView('shipments.exports.pdf', compact('shipments'));
        return $pdf->download('shipments_' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['q', 'status', 'start_date', 'end_date']);
        return Excel::download(new ShipmentsExport($filters), 'shipments_' . date('Y-m-d') . '.xlsx');
    }

    public function invoice(Payment $payment)
    {
        // ✅ Bug 4 Fix: customer → user
        $payment->load('shipment.user');
        return view('shipments.invoices', compact('payment'));
    }

    public function invoices(Request $request)
    {
        $user = Auth::user();

        $query = Payment::with(['shipment'])
            ->whereHas('shipment', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('created_at');

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('shipment', function ($q2) use ($search) {
                        $q2->where('tracking_number', 'like', "%{$search}%");
                    });
            });
        }

        $totalAmount   = (clone $query)->sum('amount');
        $totalInvoices = (clone $query)->count();
        $payments      = $query->paginate(20);

        return view('shipments.invoices', compact(
            'payments', 'totalAmount', 'totalInvoices'
        ));
    }
}