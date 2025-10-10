<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Courier;

class DashboardController extends Controller
{
    public function index()
    {
        // $totalShipments = Shipment::count();
        $totalEarnings = Shipment::whereIn('status',['delivered', 'partially_delivered'])->sum('cost_of_delivery_amount');
        $todayEarnings = Shipment::whereDate('created_at', today())->whereIn('status',['delivered', 'partially_delivered'])->sum('cost_of_delivery_amount');
        $last7Earnings = Shipment::where('created_at', '>=', now()->subDays(7))->whereIn('status',['delivered', 'partially_delivered'])->sum('cost_of_delivery_amount');
        $last30Earnings = Shipment::where('created_at', '>=', now()->subDays(30))->whereIn('status',['delivered', 'partially_delivered'])->sum('cost_of_delivery_amount');
        $last365Earnings = Shipment::where('created_at', '>=', now()->subDays(365))->whereIn('status',['delivered', 'partially_delivered'])->sum('cost_of_delivery_amount');
        $averageShipmentPrice = Shipment::whereIn('status',['delivered', 'partially_delivered'])->avg('price');
        // $pendingShipments = Shipment::where('status', 'pending')->count();
        // $deliveredShipments = Shipment::where('status', 'delivered')->count();
        // $holdShipments = Shipment::where('status', 'hold')->count();
        // $pickedShipments = Shipment::where('status', 'picked')->count();
        // $inTransitShipments = Shipment::where('status', 'in_transit')->count();
        // $partiallyDeliveredShipments = Shipment::where('status', 'partially_delivered')->count();
        // $cancelledShipments = Shipment::where('status', 'cancelled')->count();
        $cancelledAmount = Shipment::where('status', 'cancelled')->sum('price');
        $pendingValue = Shipment::where('status', 'pending')->sum('price');
        $activeCouriers = Courier::count();

        $topCouriers = Courier::with('user')
            ->withCount(['shipments as delivered_count' => fn($q) => $q->whereIn('status', ['delivered','partially_delivered'])])
            ->orderByDesc('delivered_count')
            ->take(4)
            ->get();

        $recentShipments = Shipment::latest()->take(10)->get();

        // Chart Data
        $chartData = [
            'dates' => collect(range(6,0,-1))->map(fn($d) => now()->subDays($d)->format('d M'))->toArray(),
            'earnings' => collect(range(6,0,-1))->map(fn($d) => Shipment::whereDate('created_at', now()->subDays($d))->sum('cost_of_delivery_amount'))->toArray(),
            'shipments' => collect(range(6,0,-1))->map(fn($d) => Shipment::whereDate('created_at', now()->subDays($d))->count())->toArray(),
        ];

        return view('admin.dashboard', compact(
            // 'totalShipments',
            'totalEarnings',
            'todayEarnings',
            'last7Earnings',
            'last30Earnings',
            'last365Earnings',
            'averageShipmentPrice',
            // 'pendingShipments',
            // 'deliveredShipments',
            // 'holdShipments',
            // 'pickedShipments',
            // 'inTransitShipments',
            // 'partiallyDeliveredShipments',
            // 'cancelledShipments',
            'cancelledAmount',
            'pendingValue',
            'activeCouriers',
            'topCouriers',
            'recentShipments',
            'chartData'
        ));
    }

}
