<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Courier;
use App\Models\ShipmentStatusLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ShipmentAdminController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Shipment::query();

        // ================= FILTER APPLY =================
        $query = (clone $baseQuery)->with(['courier.user', 'customer']);

        // Period filter
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('tracking_number', 'like', "%{$q}%")
                    ->orWhere('pickup_name', 'like', "%{$q}%")
                    ->orWhere('drop_name', 'like', "%{$q}%")
                    ->orWhere('pickup_address', 'like', "%{$q}%")
                    ->orWhere('drop_address', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // ================= DATA =================
        $shipments = $query->latest()->paginate(20)->withQueryString();
        $couriers = Courier::with('user')->get();

        // ================= SUMMARY (OPTIMIZED) =================
        $summaryQuery = (clone $baseQuery);

        $summary = [
            'pending' => (clone $summaryQuery)->where('status', 'pending')->count(),
            'assigned' => (clone $summaryQuery)->where('status', 'assigned')->count(),
            'picked' => (clone $summaryQuery)->where('status', 'picked')->count(),
            'in_transit' => (clone $summaryQuery)->where('status', 'in_transit')->count(),
            'delivered' => (clone $summaryQuery)->where('status', 'delivered')->count(),
            'hold' => (clone $summaryQuery)->where('status', 'hold')->count(),
            'partially_delivered' => (clone $summaryQuery)->where('status', 'partially_delivered')->count(),
            'cancelled' => (clone $summaryQuery)->where('status', 'cancelled')->count(),

            'total' => (clone $summaryQuery)->count(),
            'today' => (clone $summaryQuery)->whereDate('created_at', today())->count(),
            'this_week' => (clone $summaryQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => (clone $summaryQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'this_year' => (clone $summaryQuery)->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.shipments.index', compact('shipments', 'couriers', 'summary'));
    }

    public function show(Shipment $shipment)
    {
        $logs = $shipment->statusLogs()->latest()->get();
        $couriers = Courier::with('user')->where('status', 'available')->get();
        $assignedCourier = $shipment->courier?->user;

        return view('admin.shipments.show', compact('shipment', 'logs', 'couriers', 'assignedCourier'));
    }

    public function assignCourier(Request $request, Shipment $shipment)
    {
        $data = $request->validate(['courier_id'=>'required|exists:couriers,id']);
        $shipment->update([
            'courier_id'=>$data['courier_id'],
            'status'=>'assigned'
        ]);

        ShipmentStatusLog::create([
            'shipment_id'=>$shipment->id,
            'user_id'=>Auth::id(),
            'status'=>'assigned',
            'changed_by'=>Auth::id(),
            'note'=>'Assigned to delivery man (' . Courier::find($data['courier_id'])->user->name . ' - ' . Courier::find($data['courier_id'])->user->phone . ').'
        ]);

        $courier = Courier::find($data['courier_id']);
        $courier->update(['status'=>'available']);

        return back()->with('success','Courier assigned.');
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        // ================= VALIDATION =================
        $data = $request->validate([
            'status' => 'required|in:pending,assigned,picked,in_transit,hold,delivered,partially_delivered,cancelled',
            'note' => 'nullable|string',
            'partial_price' => 'nullable|numeric|min:0|max:' . $shipment->price
        ]);

        if ($data['status'] === 'partially_delivered' && empty($data['partial_price'])) {
            return back()->withErrors([
                'partial_price' => 'Received amount is required for partially delivered shipments.'
            ]);
        }

        // ================= STATUS UPDATE =================
        $shipment->status = $data['status'];

        $deliveryCharge = $shipment->cost_of_delivery_amount ?? 0;

        // ================= CALCULATION =================
        if ($data['status'] === 'delivered') {

            $collectedAmount = $shipment->price;

            // Merchant balance (never negative)
            $shipment->balance_cost = max($collectedAmount - $deliveryCharge, 0);

        } elseif ($data['status'] === 'partially_delivered') {

            $shipment->partial_price = $data['partial_price'];

            $collectedAmount = $shipment->partial_price;

            $shipment->balance_cost = max($collectedAmount - $deliveryCharge, 0);

        } else {
            $shipment->balance_cost = 0;
        }

        // ================= SAVE =================
        $shipment->save();

        // ================= LOG =================
        ShipmentStatusLog::create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => $data['status'],
            'changed_by' => Auth::id(),
            'note' => $data['note'] ?? 'Updated by system admin'
        ]);

        return back()->with('success', 'Shipment status updated successfully.');
    }

    // Bulk Assign Page - Show today's pending shipments
    public function bulkAssignPage()
    {
        $shipments = Shipment::where('status', 'pending')
            // ->whereDate('created_at', now()->toDateString())
            ->with(['customer', 'courier.user'])
            ->latest()
            ->get();

        $couriers = Courier::with('user')->where('status', 'available')->get();

        return view('admin.shipments.bulk-assign', compact('shipments', 'couriers'));
    }

    // Handle Bulk Assignment
    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'exists:shipments,id'
        ]);

        $courierId = $data['courier_id'];
        $shipmentIds = $data['shipment_ids'];
        $courier = Courier::find($courierId);

        // Assign all selected shipments to the courier
        foreach ($shipmentIds as $shipmentId) {
            $shipment = Shipment::find($shipmentId);

            $shipment->update([
                'courier_id' => $courierId,
                'status' => 'assigned'
            ]);

            // Log the assignment
            ShipmentStatusLog::create([
                'shipment_id' => $shipment->id,
                'user_id' => Auth::id(),
                'status' => 'assigned',
                'changed_by' => Auth::id(),
                'note' => 'Assigned to delivery man (' . $courier->user->name . ' - ' . $courier->user->phone . ') via bulk assign.'
            ]);
        }

        // Update courier status
        $courier->update(['status' => 'available']);

        return back()->with('success', count($shipmentIds) . ' shipment(s) assigned successfully to ' . $courier->user->name);
    }

}
