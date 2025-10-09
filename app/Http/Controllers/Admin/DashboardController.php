<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Courier;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalShipments = Shipment::count();
        $todayEarnings = Shipment::whereDate('created_at', today())->sum('price');
        $last30Earnings = Shipment::where('created_at', '>=', now()->subDays(30))->sum('price');
        $averageShipmentPrice = Shipment::avg('price');
        $pendingShipments = Shipment::where('status', 'pending')->count();
        $deliveredShipments = Shipment::where('status', 'delivered')->count();
        $holdShipments = Shipment::where('status', 'hold')->count();
        $pickedShipments = Shipment::where('status', 'picked')->count();
        $inTransitShipments = Shipment::where('status', 'in_transit')->count();
        $partiallyDeliveredShipments = Shipment::where('status', 'partially_delivered')->count();
        $cancelledShipments = Shipment::where('status', 'cancelled')->count();
        $cancelledAmount = Shipment::where('status', 'cancelled')->sum('price');
        $pendingValue = Shipment::where('status', 'pending')->sum('price');
        $activeCouriers = Courier::count();

        $topCouriers = Courier::with('user')
            ->withCount(['shipments as delivered_count' => fn($q) => $q->where('status', 'delivered')])
            ->orderByDesc('delivered_count')
            ->take(4)
            ->get();

        $recentShipments = Shipment::latest()->take(10)->get();

        // Chart Data
        $chartData = [
            'dates' => collect(range(6,0,-1))->map(fn($d) => now()->subDays($d)->format('d M'))->toArray(),
            'earnings' => collect(range(6,0,-1))->map(fn($d) => Shipment::whereDate('created_at', now()->subDays($d))->sum('price'))->toArray(),
            'shipments' => collect(range(6,0,-1))->map(fn($d) => Shipment::whereDate('created_at', now()->subDays($d))->count())->toArray(),
        ];

        return view('admin.dashboard', compact(
            'totalShipments',
            'todayEarnings',
            'last30Earnings',
            'averageShipmentPrice',
            'pendingShipments',
            'deliveredShipments',
            'holdShipments',
            'pickedShipments',
            'inTransitShipments',
            'partiallyDeliveredShipments',
            'cancelledShipments',
            'cancelledAmount',
            'pendingValue',
            'activeCouriers',
            'topCouriers',
            'recentShipments',
            'chartData'
        ));
    }

}
