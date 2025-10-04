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
    public function index()
    {
        $shipments = Shipment::latest()->paginate(15);
        return view('admin.shipments.index', compact('shipments'));
    }

    public function show(Shipment $shipment)
    {
        $logs = $shipment->statusLogs()->latest()->get();
        $couriers = Courier::with('user')->where('status','available')->get();
        return view('admin.shipments.show', compact('shipment','logs','couriers'));
    }

    public function assignCourier(Request $request, Shipment $shipment)
    {
        $data = $request->validate(['courier_id'=>'required|exists:couriers,id']);
        $shipment->update([
            'courier_id' => $data['courier_id'],
            'status' => 'assigned'
        ]);

        ShipmentStatusLog::create([
            'shipment_id'=>$shipment->id,
            'user_id'=>Auth::id(),
            'status'=>'assigned',
            'changed_by'=>Auth::id(),
            'note'=>'Assigned to courier id '.$data['courier_id']]);

        // mark courier busy
        $courier = Courier::find($data['courier_id']);
        $courier->update(['status'=>'busy']);

        return back()->with('success','Courier assigned.');
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $data = $request->validate(['status'=>'required|in:pending,assigned,picked,in_transit,delivered,cancelled','note'=>'nullable|string']);
        $shipment->update(['status'=>$data['status']]);
        ShipmentStatusLog::create([
            'shipment_id'=>$shipment->id,
            'user_id'=>Auth::id(),
            'status'=>$data['status'],
            'changed_by'=>Auth::id(),
            'note'=>$data['note'] ?? null]);

        return back()->with('success','Status updated.');
    }
}
