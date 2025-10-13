<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Courier;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;

class CourierAdminController extends Controller
{
    public function index()
    {
        $couriers = Courier::with('user')->paginate(20);
        return view('admin.couriers.index', compact('couriers'));
    }

    public function view(Request $request, $id)
    {
        // Fetch courier with user info
        $courier = Courier::with('user')->findOrFail($id);

        // Get filter inputs
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        // Shipments query for this courier
        $query = $courier->shipments()->with('customer');

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $shipments = $query->latest()->get();

        // Status summary for all shipments of this courier
        $allStatuses = ['pending','assigned','picked','in_transit','delivered','partially_delivered','hold','cancelled'];
        $statusSummary = [];
        foreach ($allStatuses as $st) {
            $statusSummary[$st] = $courier->shipments()->where('status', $st)->count();
        }

        // Total delivered shipments
        $totalDeliveredShipments = $courier->shipments()
            ->whereIn('status', ['delivered','partially_delivered'])
            ->count();

        // Total earnings based on fixed commission amount
        $commission = $totalDeliveredShipments * $courier->commission_rate;

        // Total delivered amount (optional, if you want total price delivered)
        $totalDeliveredAmount = $courier->shipments()
            ->whereIn('status', ['delivered','partially_delivered'])
            ->sum('price');

        return view('admin.couriers.view', compact(
            'courier',
            'shipments',
            'statusSummary',
            'commission',
            'totalDeliveredAmount',
            'totalDeliveredShipments',
            'status',
            'from',
            'to'
        ));
    }

    public function create()
    {
        $users = User::where('role', 'courier')->get();
        return view('admin.couriers.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'status' => 'required|in:available,busy,off',
        ]);

        Courier::create($validated);

        return redirect()->route('admin.couriers.index')->with('success', 'Courier added successfully.');
    }

    public function edit(Courier $courier)
    {
        $users = User::where('role', 'courier')->get();
        return view('admin.couriers.edit', compact('courier', 'users'));
    }

    public function update(Request $request, Courier $courier)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'status' => 'required|in:available,busy,off',
        ]);

        $courier->update($validated);

        return redirect()->route('admin.couriers.index')->with('success', 'Courier updated successfully.');
    }

    public function destroy(Courier $courier)
    {
        $courier->delete();
        return redirect()->route('admin.couriers.index')->with('success', 'Courier deleted successfully.');
    }

    public function print(Request $request, $id)
    {
        $courier = Courier::with('user')->findOrFail($id);

        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        // Build query with filters
        $query = $courier->shipments();

        if ($status) {
            $query->where('status', $status);
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Get shipments after filters
        $shipments = $query->latest()->get();

        // Total parcels based on filters
        $totalParcel = $shipments->count();

        // Total delivered/partially delivered amount based on filters
        $totalDeliveredAmount = $query->whereIn('status', ['delivered', 'partially_delivered'])->sum('price');

        // Status summary for cards in PDF
        $statusSummary = $courier->shipments()
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Generate PDF
        $pdf = Pdf::loadView('admin.couriers.print', compact(
            'courier',
            'shipments',
            'statusSummary',
            'totalParcel',
            'totalDeliveredAmount',
            'status',
            'from',
            'to'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('delivery-man-'.$courier->user->name.'-report.pdf');
    }

}
