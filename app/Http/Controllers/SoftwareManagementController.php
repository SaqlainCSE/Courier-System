<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SoftwareManagementController extends Controller
{
    const RATE_PER_DELIVERY = 10;
    const EARNING_STATUSES = ['delivered', 'cancelled', 'partially_delivered'];

    public function index()
    {
        $earnings = [
            'today' => $this->getEarnings(Carbon::today(), Carbon::now()),
            '7days' => $this->getEarnings(Carbon::now()->subDays(7), Carbon::now()),
            '30days' => $this->getEarnings(Carbon::now()->subDays(30), Carbon::now()),
        ];

        return view('admin.software-management.index', compact('earnings'));
    }

    private function getEarnings(Carbon $from, Carbon $to): array
    {
        $results = Shipment::select('status', DB::raw('COUNT(*) as count'))
            ->whereIn('status', self::EARNING_STATUSES)
            ->whereBetween('updated_at', [$from, $to])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $delivered         = $results['delivered'] ?? 0;
        $cancelled         = $results['cancelled'] ?? 0;
        $partially_delivered = $results['partially_delivered'] ?? 0;

        $total = $delivered + $cancelled + $partially_delivered;

        return [
            'delivered'           => $delivered,
            'cancelled'           => $cancelled,
            'partially_delivered' => $partially_delivered,
            'total_count'         => $total,
            'total_earning'       => $total * self::RATE_PER_DELIVERY,
            'delivered_earning'   => $delivered * self::RATE_PER_DELIVERY,
            'cancelled_earning'   => $cancelled * self::RATE_PER_DELIVERY,
            'partial_earning'     => $partially_delivered * self::RATE_PER_DELIVERY,
        ];
    }
}
