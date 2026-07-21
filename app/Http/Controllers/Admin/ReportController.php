<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filter     = $request->get('filter', 'total');
        $status     = $request->get('status', 'all');
        $merchantId = $request->get('merchant_id', 'all');
        $dateRange  = $request->only(['start_date', 'end_date']);

        $query = Shipment::query();

        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', now()->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'this_year':
                $query->whereYear('created_at', now()->year);
                break;
            case 'custom':
                if (!empty($dateRange['start_date']) && !empty($dateRange['end_date'])) {
                    $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
                }
                break;
            default:
                break;
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($merchantId !== 'all') {
            $query->where('user_id', $merchantId);
        }

        $shipments = $query->latest()->get();

        $summary = [
            'total'               => Shipment::count(),
            'today'               => Shipment::whereDate('created_at', now()->toDateString())->count(),
            'this_week'           => Shipment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month'          => Shipment::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'this_year'           => Shipment::whereYear('created_at', now()->year)->count(),
            'pending'             => Shipment::where('status', 'pending')->count(),
            'assigned'            => Shipment::where('status', 'assigned')->count(),
            'picked'              => Shipment::where('status', 'picked')->count(),
            'in_transit'          => Shipment::where('status', 'in_transit')->count(),
            'delivered'           => Shipment::where('status', 'delivered')->count(),
            'partially_delivered' => Shipment::where('status', 'partially_delivered')->count(),
            'hold'                => Shipment::where('status', 'hold')->count(),
            'cancelled'           => Shipment::where('status', 'cancelled')->count(),
        ];

        $merchants = User::where('role', 'customer')->get();

        return view('admin.reports.index', compact(
            'summary', 'shipments', 'filter', 'status', 'dateRange', 'merchants', 'merchantId'
        ));
    }

    public function export(Request $request)
    {
        $fileName = 'shipments_' . now()->format('Ymd_His') . '.csv';
        $response = new StreamedResponse(function() use($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tracking','Status','Pickup','Drop','Price','Balance','Created At']);

            $query = Shipment::query()->with('customer');

            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('start_date')) $query->whereDate('created_at','>=',$request->start_date);
            if ($request->filled('end_date')) $query->whereDate('created_at','<=',$request->end_date);

            $query->chunk(200, function($rows) use($handle){
                foreach ($rows as $s) {
                    fputcsv($handle, [
                        $s->tracking_number,
                        $s->status,
                        optional($s->customer)->business_name,
                        $s->drop_name,
                        $s->price,
                        $s->balance_cost,
                        $s->created_at,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type','text/csv');
        $response->headers->set('Content-Disposition','attachment; filename="'.$fileName.'"');
        return $response;
    }

    public function printAll(Request $request)
    {
        $query = Shipment::query()->with(['courier.user', 'customer', 'statusLogs']);

        // Period filter
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('tracking_number', 'like', "%{$q}%")
                    ->orWhere('pickup_name', 'like', "%{$q}%")
                    ->orWhere('drop_name', 'like', "%{$q}%")
                    ->orWhere('pickup_address', 'like', "%{$q}%")
                    ->orWhere('drop_address', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->whereIn('status', ['delivered', 'partially_delivered'])
                    ->where('balance_cost', '<=', 0);
            } else {
                $query->where('status', $request->status);
            }
        } else {
            $query->whereIn('status', ['assigned', 'pending', 'hold']);
        }

        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $shipments = $query->latest()->get();

        return view('admin.reports.print-multi', compact('shipments'));
    }

    public function exportPdf(Request $request)
{
    $filter = $request->input('filter', 'total');
    $status = $request->input('status', 'all');
    $merchantId = $request->input('merchant_id', 'all');
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');

    $query = \App\Models\Shipment::query()->with(['courier.user', 'customer']);

    // Status filter
    if ($status !== 'all') {
        $query->where('status', $status);
    }

    // Period filter
    switch ($filter) {
        case 'today':
            $query->whereDate('created_at', today());
            break;
        case 'this_week':
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'this_month':
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            break;
        case 'this_year':
            $query->whereYear('created_at', now()->year);
            break;
        case 'custom':
            if ($start_date && $end_date) {
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::parse($start_date)->startOfDay(),
                    \Carbon\Carbon::parse($end_date)->endOfDay(),
                ]);
            }
            break;
        // 'total' হলে কোনো period restriction লাগবে না
    }

    // Merchant filter
    if ($merchantId !== 'all') {
        $query->where('user_id', $merchantId);
    }

    $shipments = $query->latest()->get();

    $html = view('admin.reports.pdf', compact('shipments', 'filter', 'status'))->render();

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'fontDir' => array_merge(
            (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'],
            [public_path('fonts')]
        ),
        'fontdata' => array_merge(
            (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'],
            [
                'notosansbengali' => [
                    'R' => 'NotoSansBengali-Regular.ttf',
                ]
            ]
        ),
        'default_font' => 'notosansbengali',
        'useOTL' => 0xFF,
        'useKashida' => 75,
    ]);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output('shipment-report-' . now()->format('Ymd-His') . '.pdf', 'I'), 200)
        ->header('Content-Type', 'application/pdf');
}
}
