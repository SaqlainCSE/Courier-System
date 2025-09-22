<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Controller as BaseController;

class ShipmentController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:customer')->only(['create','store','index','show']);
    }

    public function index()
    {
        $shipments = Auth::user()->shipments()->latest()->paginate(10);
        return view('shipments.index', compact('shipments'));
    }

    public function create()
    {
        return view('shipments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pickup_name'=>'required',
            'pickup_phone'=>'required',
            'pickup_address'=>'required',
            'drop_name'=>'required',
            'drop_phone'=>'required',
            'drop_address'=>'required',
            'weight_kg'=>'required|numeric|min:0',
            'price'=>'required|numeric|min:0'
        ]);

        $data['user_id'] = Auth::id();
        $shipment = Shipment::create($data);

        ShipmentStatusLog::create([
            'shipment_id' => $shipment->id,
            'status' => $shipment->status,
            'changed_by' => Auth::id(),
            'note' => 'Shipment created by customer'
        ]);

        return redirect()->route('shipments.show', $shipment)->with('success','Shipment created. Tracking: '.$shipment->tracking_number);
    }

    public function show(Shipment $shipment)
    {
        $this->authorizeView($shipment);
        $logs = $shipment->statusLogs()->latest()->get();
        return view('shipments.show', compact('shipment','logs'));
    }

    protected function authorizeView(Shipment $shipment)
    {
        if (Auth::user()->isAdmin()) return true;
        if (Auth::id() !== $shipment->user_id) abort(403);
        return true;
    }

    public function cancel(Shipment $shipment)
    {
        $this->authorizeView($shipment);
        if (!in_array($shipment->status, ['pending','assigned'])) {
            return back()->with('error','Cannot cancel at this stage');
        }
        $shipment->update(['status'=>'cancelled']);
        ShipmentStatusLog::create(['shipment_id'=>$shipment->id,'status'=>'cancelled','changed_by'=>Auth::id(),'note'=>'Cancelled by customer']);
        return back()->with('success','Shipment cancelled.');
    }
}
