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
        $query = Shipment::query()->with(['courier.user', 'customer']);

        // Period filter from summary cards
        if ($request->filled('period')) {
            $period = $request->period;
            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', now()->toDateString());
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
                case 'total':
                default:
                    // no filter, show all
                    break;
            }
        }

        // Other filters (search, status, courier, date range)
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

        // Fetch data
        $shipments = $query->latest()->paginate(20)->withQueryString();
        $couriers = Courier::with('user')->get();

        // Dashboard summary
        $summary = [
            'pending' => Shipment::where('status', 'pending')->count(),
            'assigned' => Shipment::where('status', 'assigned')->count(),
            'picked' => Shipment::where('status', 'picked')->count(),
            'in_transit' => Shipment::where('status', 'in_transit')->count(),
            'delivered' => Shipment::where('status', 'delivered')->count(),
            'hold' => Shipment::where('status', 'hold')->count(),
            'partially_delivered' => Shipment::where('status', 'partially_delivered')->count(),
            'cancelled' => Shipment::where('status', 'cancelled')->count(),
            'total' => Shipment::count(),
            'today' => Shipment::whereDate('created_at', now()->toDateString())->count(),
            'this_week' => Shipment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Shipment::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'this_year' => Shipment::whereYear('created_at', now()->year)->count(),
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

    // public function updateStatus(Request $request, Shipment $shipment)
    // {
    //     $data = $request->validate(['status'=>'required|in:pending,assigned,picked,in_transit,hold,delivered,partially_delivered,cancelled','note'=>'nullable|string']);
    //     $shipment->update(['status'=>$data['status']]);
    //     ShipmentStatusLog::create([
    //         'shipment_id'=>$shipment->id,
    //         'user_id'=>Auth::id(),
    //         'status'=>$data['status'],
    //         'changed_by'=>Auth::id(),
    //         'note'=>$data['note'] ?? null]);

    //     return back()->with('success','Status updated.');
    // }


    public function updateStatus(Request $request, Shipment $shipment)
    {
        // Validate input (admin can update all statuses)
        $data = $request->validate([
            'status' => 'required|in:pending,assigned,picked,in_transit,hold,delivered,partially_delivered,cancelled',
            'note' => 'nullable|string',
            'partial_price' => 'nullable|numeric|min:0'
        ]);

        // If partially delivered, require received amount
        if ($data['status'] === 'partially_delivered' && empty($data['partial_price'])) {
            return back()->withErrors(['partial_price' => 'Received amount is required for partially delivered shipments.']);
        }

        // Update shipment status
        $shipment->status = $data['status'];

        // --- Handle Partial Delivery ---
        if ($data['status'] === 'partially_delivered') {
            $shipment->price = $data['partial_price']; // Update shipment price with received amount
        }

        // --- Handle Delivered or Partially Delivered: calculate balance cost ---
        if (in_array($data['status'], ['delivered', 'partially_delivered'])) {
            $costOfDeliveryAmount = $shipment->cost_of_delivery_amount ?? 0;
            $totalPriceOfProduct = $shipment->price; // may be full or partial

            // Balance cost = product price - delivery charge
            if ($totalPriceOfProduct == 0) {
                $shipment->balance_cost = $costOfDeliveryAmount;
            } else {
                $shipment->balance_cost = $totalPriceOfProduct - $costOfDeliveryAmount;
            }
        }

        // Save shipment
        $shipment->save();

        // --- Log the status change ---
        ShipmentStatusLog::create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => $data['status'],
            'changed_by' => Auth::id(),
            'note' => $data['note'] ?? 'Updated by system admin'
        ]);

        return back()->with('success', 'Shipment status updated successfully.');
    }

    public function printAll(Request $request)
    {
        $shipments = Shipment::with(['courier', 'customer', 'statusLogs'])
                                    ->whereIn('status', ['assigned', 'pending'])
                                    ->latest()
                                    ->get();

        return view('admin.reports.print-multi', compact('shipments'));
    }

}
