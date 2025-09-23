<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    public function dashboard()
    {
        $user = Auth::user();

        // Recent shipments (latest 10)
        $shipments = Shipment::where('user_id', $user->id)->latest()->take(10)->get();

        // Summary counts
        $summary = [
            'pending' => Shipment::where('user_id', $user->id)->where('status', 'pending')->count(),
            'in_transit' => Shipment::where('user_id', $user->id)->whereIn('status', ['assigned','picked','in_transit'])->count(),
            'delivered' => Shipment::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'cancelled' => Shipment::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        return view('shipments.dashboard', compact('shipments', 'summary'));
    }


    public function index()
    {
        $shipments = Shipment::where('user_id', Auth::id())->latest()->get();
        return view('shipments.index', compact('shipments'));
    }

    public function create()
    {
        return view('shipments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pickup_name' => 'required|string|max:255',
            'pickup_phone' => 'required|string|max:20',
            'pickup_address' => 'required|string',
            'drop_name' => 'required|string|max:255',
            'drop_phone' => 'required|string|max:20',
            'drop_address' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500',
        ]);

        Shipment::create([
            'tracking_number' => 'TRK' . strtoupper(uniqid()),
            'user_id' => Auth::id(),
            'pickup_name' => $request->pickup_name,
            'pickup_phone' => $request->pickup_phone,
            'pickup_address' => $request->pickup_address,
            'drop_name' => $request->drop_name,
            'drop_phone' => $request->drop_phone,
            'drop_address' => $request->drop_address,
            'weight_kg' => $request->weight_kg,
            'price' => $request->weight_kg * 100, // simple calculation
            'notes' => $request->notes,
        ]);

        return redirect()->route('shipments.dashboard')->with('success', 'Shipment created successfully!');
    }

    public function show(Shipment $shipment)
    {
        $user = Auth::user();

        // Only allow the logged-in customer to view their own shipment
        if ($user->role !== 'customer' || $shipment->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        // Optionally, calculate estimated delivery in human-readable format
        $estimated = $shipment->estimated_delivery_at
            ? $shipment->estimated_delivery_at->format('d M Y, H:i')
            : 'Not Estimated';

        return view('shipments.show', compact('shipment', 'estimated'));
    }


    public function cancel(Shipment $shipment)
    {
        if ($shipment->status === 'pending') {
            $shipment->update(['status' => 'cancelled']);
        }
        return redirect()->route('shipments.dashboard')->with('success', 'Shipment cancelled successfully.');
    }
}
