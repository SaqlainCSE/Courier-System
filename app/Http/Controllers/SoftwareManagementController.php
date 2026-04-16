<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SoftwareManagementController extends Controller
{
    const EARNING_STATUSES = ['delivered', 'cancelled', 'partially_delivered'];

    public function index()
    {
        $earnings = [
            'lifetime' => $this->getLifetimeEarnings(),
            'today'    => $this->getEarnings(Carbon::today(), Carbon::now()),
            '7days'    => $this->getEarnings(Carbon::now()->subDays(7), Carbon::now()),
            '30days'   => $this->getEarnings(Carbon::now()->subDays(30), Carbon::now()),
        ];

        return view('admin.software-management.index', compact('earnings'));
    }

    private function getEarnings(Carbon $from, Carbon $to): array
    {
        $results = Shipment::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(earning) as total_earning')
            )
            ->whereIn('status', self::EARNING_STATUSES)
            ->whereBetween('delivered_at', [$from, $to])
            ->groupBy('status')
            ->get();

        return $this->formatEarningsData($results);
    }

    private function getLifetimeEarnings(): array
    {
        $results = Shipment::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(earning) as total_earning')
            )
            ->whereIn('status', self::EARNING_STATUSES)
            ->groupBy('status')
            ->get();

        return $this->formatEarningsData($results);
    }

    private function formatEarningsData($results): array
    {
        $data = [];
        foreach ($results as $row) {
            $data[$row->status] = [
                'count' => $row->count,
                'earning' => $row->total_earning,
            ];
        }

        $delivered = $data['delivered'] ?? ['count' => 0, 'earning' => 0];
        $cancelled = $data['cancelled'] ?? ['count' => 0, 'earning' => 0];
        $partial   = $data['partially_delivered'] ?? ['count' => 0, 'earning' => 0];

        return [
            'delivered'           => $delivered['count'],
            'cancelled'           => $cancelled['count'],
            'partially_delivered' => $partial['count'],
            'delivered_earning'   => $delivered['earning'],
            'cancelled_earning'   => $cancelled['earning'],
            'partial_earning'     => $partial['earning'],
            'total_count'         => $delivered['count'] + $cancelled['count'] + $partial['count'],
            'total_earning'       => $delivered['earning'] + $cancelled['earning'] + $partial['earning'],
        ];
    }
}
