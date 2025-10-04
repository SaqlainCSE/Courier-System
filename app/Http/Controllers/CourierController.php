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

    public function dashboard()
    {
        $courier = Auth::user()->courierProfile;
        $assignments = $courier->assignedShipments()->whereIn('status',['assigned','picked','in_transit'])->orderBy('created_at','desc')->get();
        return view('courier.dashboard', compact('assignments'));
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $userCourier = Auth::user()->courierProfile;
        if ($shipment->courier_id !== $userCourier->id) abort(403, 'Not your assignment');

        $data = $request->validate(['status'=>'required|in:picked,in_transit,delivered','note'=>'nullable|string']);
        $shipment->update(['status'=>$data['status']]);

        ShipmentStatusLog::create([
            'shipment_id'=>$shipment->id,
            'user_id'=>Auth::id(),
            'status'=>$data['status'],
            'changed_by'=>Auth::id(),
            'note'=>$data['note'] ?? 'Updated by courier'
        ]);

        if ($data['status'] === 'delivered') {
            // release courier
            $userCourier->update(['status'=>'available']);
        }

        return back()->with('success','Status updated');
    }

    // optional: update courier location via AJAX
    public function updateLocation(Request $request)
    {
        $request->validate(['lat'=>'required','lng'=>'required']);
        $courier = Auth::user()->courierProfile;
        $courier->update(['current_lat'=>$request->lat,'current_lng'=>$request->lng]);
        return response()->json(['ok' => true]);
    }
}
