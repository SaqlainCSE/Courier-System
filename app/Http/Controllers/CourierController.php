<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class CourierController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:courier']);
    }

    public function dashboard(Request $request)
    {
        $courier = Auth::user()->courierProfile;

        // Assignments list with filter
        $assignments = $courier->assignedShipments()
                                        ->with(['customer']) // eager load user (customer)
                                        ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                                        ->when($request->filled('q'), function($q) use ($request) {
                                            $q->where(function($sub) use ($request) {
                                                $sub->where('tracking_number', 'like', '%'.$request->q.'%')
                                                    ->orWhere('pickup_address', 'like', '%'.$request->q.'%')
                                                    ->orWhere('drop_address', 'like', '%'.$request->q.'%')
                                                    ->orWhere('pickup_name', 'like', '%'.$request->q.'%')
                                                    ->orWhere('drop_name', 'like', '%'.$request->q.'%');
                                            });
                                        })
                                        ->orderByRaw("CASE
                                            WHEN status = 'assigned' THEN 1
                                            WHEN status IN ('picked','in_transit','partially_delivered') THEN 2
                                            WHEN status = 'delivered' THEN 3
                                            ELSE 4
                                        END")
                                        ->orderBy('created_at', 'desc')
                                        ->paginate(20)
                                        ->withQueryString();

        // === Dashboard Stats ===
        $todayEarnings = $courier->assignedShipments()
                                            ->whereIn('status', ['delivered', 'partially_delivered'])
                                            ->whereDate('updated_at', today())
                                            ->count() * $courier->commission_rate;

        $lastMonthEarnings = $courier->assignedShipments()
                                                    ->whereIn('status', ['delivered', 'partially_delivered'])
                                                    ->whereBetween('updated_at', [now()->subMonth()->startOfDay(), now()])
                                                    ->count() * $courier->commission_rate;


        $newAssignments = $courier->assignedShipments()
                                                    ->where('status','assigned')
                                                    ->count();

        $deliveredAssignments = $courier->assignedShipments()
                                                            ->whereIn('status',['delivered','partially_delivered'])
                                                            ->count();

        $partiallyDeliveredAssignments = $courier->assignedShipments()
                                                                                ->where('status','partially_delivered')
                                                                                ->count();

        return view('courier.dashboard', compact(
            'assignments',
            'todayEarnings',
            'lastMonthEarnings',
            'newAssignments',
            'deliveredAssignments',
            'partiallyDeliveredAssignments'
        ));
    }


    public function updateStatus(Request $request, Shipment $shipment)
    {
        $courier = Auth::user()->courierProfile;
        if ($shipment->courier_id !== $courier->id) abort(403);

        $data = $request->validate([
            'status'=>'required|in:picked,hold,delivered,partially_delivered,cancelled',
            'note'=>'nullable|string'
        ]);

        $shipment->update(['status'=>$data['status']]);

        ShipmentStatusLog::create([
            'shipment_id'=>$shipment->id,
            'user_id'=>Auth::id(),
            'status'=>$data['status'],
            'changed_by'=>Auth::id(),
            'note'=>$data['note'] ?? 'Updated by courier'
        ]);

        // Release courier if delivered
        if ($data['status'] === 'delivered') {
            $courier->update(['status'=>'available']);
        } else {
            $courier->update(['status'=>'busy']);
        }

        return back()->with('success','Status updated');
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat'=>'required|numeric',
            'lng'=>'required|numeric'
        ]);

        $courier = Auth::user()->courierProfile;
        $courier->update([
            'current_lat'=>$request->lat,
            'current_lng'=>$request->lng
        ]);

        return response()->json(['ok'=>true]);
    }

    // Optional: Courier shipment history
    public function history()
    {
        $courier = Auth::user()->courierProfile;
        $history = $courier->assignedShipments()
            ->where('status','delivered')
            ->orderBy('updated_at','desc')
            ->get();

        return view('courier.history', compact('history'));
    }

    // Optional: Show shipment details to courier
    public function show(Shipment $shipment)
    {
        $courier = Auth::user()->courierProfile;
        if ($shipment->courier_id !== $courier->id) abort(403);

        return view('courier.show', compact('shipment'));
    }
}

