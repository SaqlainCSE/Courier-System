<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Courier;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total earnings
        $totalEarnings = Shipment::whereIn('status',['delivered', 'partially_delivered'])
                            ->sum('cost_of_delivery_amount');

        // Earnings based on delivered_at
        $todayEarnings = Shipment::whereIn('status',['delivered', 'partially_delivered'])
                            ->whereDate('delivered_at', today())
                            ->sum('cost_of_delivery_amount');

        $last7Earnings = Shipment::whereIn('status',['delivered', 'partially_delivered'])
                            ->where('delivered_at', '>=', now()->subDays(7))
                            ->sum('cost_of_delivery_amount');

        $last30Earnings = Shipment::whereIn('status',['delivered', 'partially_delivered'])
                            ->where('delivered_at', '>=', now()->subDays(30))
                            ->sum('cost_of_delivery_amount');

        $last365Earnings = Shipment::whereIn('status',['delivered', 'partially_delivered'])
                            ->where('delivered_at', '>=', now()->subDays(365))
                            ->sum('cost_of_delivery_amount');

        // Total COD collected today
        $TodayAllMarchantCODCollected = Shipment::whereIn('status', ['delivered', 'partially_delivered'])
                                                ->whereDate('delivered_at', today())
                                                ->select(DB::raw("
                                                    SUM(
                                                        CASE
                                                            WHEN status = 'delivered' THEN price
                                                            WHEN status = 'partially_delivered' THEN partial_price
                                                            ELSE 0
                                                        END
                                                    ) as total
                                                "))
                                                ->value('total');

        // Today merchant payments
        $todayMarchantPaidAmount = Payment::whereDate('created_at', today())
                                        ->where('status','paid')
                                        ->sum('amount');

        $todayMarchantUnpaidAmount = Payment::whereDate('created_at', today())
                                            ->whereNotIn('status', ['paid'])
                                            ->sum('amount');

        $todayPartialAmount = Shipment::whereIn('status',['partially_delivered'])
                                    ->whereDate('delivered_at', today())
                                    ->sum('partial_price');

        // Shipment counts (use delivered_at for delivered types)
        $pendingShipments = Shipment::whereDate('created_at', today())->where('status', 'pending')->count();
        $deliveredShipments = Shipment::whereIn('status', ['delivered'])->whereDate('delivered_at', today())->count();
        $partiallyDeliveredShipments = Shipment::whereIn('status', ['partially_delivered'])->whereDate('delivered_at', today())->count();
        $holdShipments = Shipment::whereDate('created_at', today())->where('status', 'hold')->count();
        $inTransitShipments = Shipment::whereDate('created_at', today())->where('status', 'in_transit')->count();
        $cancelledShipments = Shipment::whereDate('delivered_at', today())->where('status', 'cancelled')->count();

        // Active couriers and top performers
        $activeCouriers = Courier::count();
        $topCouriers = Courier::with('user')
            ->withCount(['shipments as delivered_count' => fn($q) => $q->whereIn('status', ['delivered','partially_delivered'])])
            ->orderByDesc('delivered_count')
            ->take(4)
            ->get();

        // Recent shipments
        $recentShipments = Shipment::latest()->take(10)->get();

        // Chart Data (last 7 days)
        $chartData = [
            'dates' => collect(range(6,0,-1))->map(fn($d) => now()->subDays($d)->format('d M'))->toArray(),
            'earnings' => collect(range(6,0,-1))->map(fn($d) => Shipment::whereIn('status', ['delivered','partially_delivered'])
                                                                        ->whereDate('delivered_at', now()->subDays($d))
                                                                        ->sum('cost_of_delivery_amount'))->toArray(),
            'shipments' => collect(range(6,0,-1))->map(fn($d) => Shipment::whereDate('created_at', now()->subDays($d))->count())->toArray(),
        ];

        return view('admin.dashboard', compact(
            'totalEarnings',
            'todayEarnings',
            'last7Earnings',
            'last30Earnings',
            'last365Earnings',
            'TodayAllMarchantCODCollected',
            'todayMarchantPaidAmount',
            'todayMarchantUnpaidAmount',
            'todayPartialAmount',
            'pendingShipments',
            'deliveredShipments',
            'holdShipments',
            'inTransitShipments',
            'partiallyDeliveredShipments',
            'cancelledShipments',
            'activeCouriers',
            'topCouriers',
            'recentShipments',
            'chartData'
        ));
    }

}
