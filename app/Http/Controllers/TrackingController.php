<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function search(Request $request)
    {
        // validate the same input name as the form (tracking_number)
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $tracking = trim($request->input('tracking_number'));

        // redirect to the show route with the tracking number
        return redirect()->route('tracking.show', ['tracking' => $tracking]);
    }

    // Show the shipment and logs
    public function show($tracking)
    {
        // Find by tracking number
        $shipment = Shipment::where('tracking_number', $tracking)->firstOrFail();

        // Get status logs if the relation exists on the model
        if (method_exists($shipment, 'statusLogs')) {
            $logs = $shipment->statusLogs()->latest('created_at')->get();
        } else {
            // fallback to empty collection so view won't break
            $logs = collect();
        }

        return view('tracking.show', compact('shipment', 'logs'));
    }
}
