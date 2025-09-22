<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show($tracking)
    {
        $shipment = Shipment::where('tracking_number', $tracking)->firstOrFail();
        $logs = $shipment->statusLogs()->latest()->get();
        return view('tracking.show', compact('shipment','logs'));
    }

    public function search(Request $request)
    {
        $request->validate(['tracking'=>'required|string']);
        return redirect()->route('tracking.show', $request->tracking);
    }
}
