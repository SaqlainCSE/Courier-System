<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Courier;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $earningStatuses = ['delivered', 'partially_delivered', 'merchant_pay', 'cancelled'];

        $earningExpr = '(COALESCE(shipments.cost_of_delivery_amount, 0) - COALESCE(couriers.commission_rate, 0))';

        $baseQuery = function () use ($earningStatuses) {
            return Shipment::query()
                ->leftJoin('couriers', 'couriers.id', '=', 'shipments.courier_id')
                ->whereIn('shipments.status', $earningStatuses);
        };

        // ---------------- Earnings ----------------
        $totalEarnings = $baseQuery()
            ->sum(DB::raw($earningExpr));

        $todayEarnings = $baseQuery()
            ->whereDate('shipments.delivered_at', $today)
            ->sum(DB::raw($earningExpr));

        $last7Earnings = $baseQuery()
            ->whereBetween('shipments.delivered_at', [Carbon::now()->subDays(7), Carbon::now()])
            ->sum(DB::raw($earningExpr));

        $last30Earnings = $baseQuery()
            ->whereBetween('shipments.delivered_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->sum(DB::raw($earningExpr));

        $last365Earnings = $baseQuery()
            ->whereBetween('shipments.delivered_at', [Carbon::now()->subDays(365), Carbon::now()])
            ->sum(DB::raw($earningExpr));


        $TodayAllMarchantCODCollected = Shipment::leftJoin('couriers', 'couriers.id', '=', 'shipments.courier_id')
            ->whereIn('shipments.status', ['delivered', 'partially_delivered', 'merchant_pay', 'cancelled'])
            ->whereDate('shipments.delivered_at', today())
            ->select(DB::raw("
                SUM(
                    CASE
                        WHEN shipments.status = 'delivered' THEN shipments.price - COALESCE(couriers.commission_rate, 0)
                        WHEN shipments.status = 'partially_delivered' THEN shipments.partial_price - COALESCE(couriers.commission_rate, 0)
                        WHEN shipments.status = 'merchant_pay' THEN shipments.price - COALESCE(couriers.commission_rate, 0)
                        WHEN shipments.status = 'cancelled' THEN 0
                        ELSE 0
                    END
                ) as total
            "))
            ->value('total');

        // ---------------- Merchant Paid / Unpaid / Partial (Today) ----------------
        $todayMarchantPaidAmount = Payment::join('shipments', 'shipments.id', '=', 'payments.shipment_id')
            ->leftJoin('couriers', 'couriers.id', '=', 'shipments.courier_id')
            ->whereDate('payments.created_at', today())
            ->where('payments.status', 'paid')
            ->select(DB::raw('SUM(payments.amount - COALESCE(couriers.commission_rate, 0)) as total'))
            ->value('total');

        $todayMarchantUnpaidAmount = Payment::whereDate('created_at', today())
                                            ->whereNotIn('status', ['paid'])
                                            ->sum('amount');

        $todayPartialAmount = Shipment::whereIn('status',['partially_delivered'])
                                    ->whereDate('delivered_at', today())
                                    ->sum('partial_price');

        // ---------------- Today's Status-wise Shipment Counts ----------------
        $pendingShipments = Shipment::whereDate('created_at', $today)
            ->where('status', 'pending')->count();

        $inTransitShipments = Shipment::whereDate('created_at', $today)
            ->where('status', 'in_transit')->count();

        $deliveredShipments = Shipment::whereDate('delivered_at', $today)
            ->where('status', 'delivered')->count();

        $partiallyDeliveredShipments = Shipment::whereDate('delivered_at', $today)
            ->where('status', 'partially_delivered')->count();

        $holdShipments = Shipment::whereDate('created_at', $today)
            ->where('status', 'hold')->count();

        $cancelledShipments = Shipment::whereDate('delivered_at', $today)
            ->where('status', 'cancelled')->count();

        // ---------------- Active Couriers ----------------
        $activeCouriers = Courier::where('status', 'active')->count();

        // ---------------- Top Couriers (by Deliveries) ----------------
        $topCouriers = Courier::with('user')
            ->withCount(['assignedShipments as delivered_count' => function ($q) {
                $q->whereIn('status', ['delivered', 'partially_delivered', 'merchant_pay']);
            }])
            ->orderByDesc('delivered_count')
            ->take(4)
            ->get();

        // ---------------- Recent Shipments ----------------
        $recentShipments = Shipment::with('courier.user')
            ->latest()
            ->take(10)
            ->get();

        // ---------------- Chart Data (Last 7 Days) ----------------
        $dates = [];
        $earningsChart = [];
        $shipmentsChart = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('d M');

            $earningsChart[] = (float) $baseQuery()
                ->whereDate('shipments.delivered_at', $date)
                ->sum(DB::raw($earningExpr));

            $shipmentsChart[] = Shipment::whereDate('created_at', $date)->count();
        }

        $chartData = [
            'dates' => $dates,
            'earnings' => $earningsChart,
            'shipments' => $shipmentsChart,
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
            'inTransitShipments',
            'deliveredShipments',
            'partiallyDeliveredShipments',
            'holdShipments',
            'cancelledShipments',
            'activeCouriers',
            'topCouriers',
            'recentShipments',
            'chartData'
        ));
    }
}