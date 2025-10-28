<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Courier;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

        $todayEarnings = $courier->shipments()
                                            ->whereIn('status', ['delivered', 'partially_delivered'])
                                            ->whereDate('updated_at', today())
                                            ->count() * $courier->commission_rate;

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
            'todayEarnings',
            'status',
            'from',
            'to'
        ));
    }

    public function create()
    {
        return view('admin.couriers.create'); // form to register delivery man
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // User fields
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',

            // Courier fields
            'commission_rate' => 'required|numeric|min:0|max:100',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'status' => 'required|in:available,busy,off',
        ]);

        DB::transaction(function () use ($validated) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'business_name' => $validated['business_name'] ?? null,
                'business_address' => $validated['business_address'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => 'courier',
            ]);

            Courier::create([
                'user_id' => $user->id,
                'commission_rate' => $validated['commission_rate'],
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('admin.couriers.index')
                        ->with('success', 'Delivery man registered successfully!');
    }

    public function edit(Courier $courier)
    {
        $courier->load('user');
        return view('admin.couriers.edit', compact('courier'));
    }

    public function update(Request $request, Courier $courier)
    {
        $user = $courier->user;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',

            'commission_rate' => 'required|numeric|min:0|max:100',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'status' => 'required|in:available,busy,off',
        ]);

        DB::transaction(function () use ($validated, $user, $courier) {

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'business_name' => $validated['business_name'] ?? null,
                'business_address' => $validated['business_address'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            $courier->update([
                'commission_rate' => $validated['commission_rate'],
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('admin.couriers.index')
                        ->with('success', 'Delivery man details updated successfully!');
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
