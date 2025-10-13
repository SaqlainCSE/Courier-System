<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Default filters
        $filter = $request->get('filter', 'total');
        $status = $request->get('status', 'all');
        $dateRange = $request->only(['start_date', 'end_date']);

        // 🔹 Build query
        $query = Shipment::query();

        // Time filters
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
            case 'total':
            default:
                // no filter
                break;
        }

        // 🔹 Status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Fetch results
        $shipments = $query->latest()->get();

        // 🔹 Summary data
        $summary = [
            'total'      => Shipment::count(),
            'today'      => Shipment::whereDate('created_at', now()->toDateString())->count(),
            'this_week'  => Shipment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Shipment::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'this_year'  => Shipment::whereYear('created_at', now()->year)->count(),
            'pending'    => Shipment::where('status', 'pending')->count(),
            'assigned'   => Shipment::where('status', 'assigned')->count(),
            'picked'     => Shipment::where('status', 'picked')->count(),
            'in_transit' => Shipment::where('status', 'in_transit')->count(),
            'delivered'  => Shipment::where('status', 'delivered')->count(),
            'partially_delivered' => Shipment::where('status', 'partially_delivered')->count(),
            'hold'       => Shipment::where('status', 'hold')->count(),
            'cancelled'  => Shipment::where('status', 'cancelled')->count(),
        ];

        return view('admin.reports.index', compact('summary', 'shipments', 'filter', 'status', 'dateRange'));
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
        $shipments = Shipment::with(['courier', 'customer', 'statusLogs'])
                                    ->whereIn('status', ['assigned', 'pending','hold'])
                                    ->latest()
                                    ->get();

        return view('admin.reports.print-multi', compact('shipments'));
    }

    public function exportPdf(Request $request)
    {
        // same logic as index to get shipments
        $filter = $request->input('filter', 'total');
        $status = $request->input('status', 'pending');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = \App\Models\Shipment::query();

        if ($status != 'all') {
            $query->where('status', $status);
        }

        if ($filter === 'custom' && $start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $shipments = $query->latest()->get();

        // Generate PDF
        $pdf = Pdf::loadView('admin.reports.pdf', compact('shipments', 'filter', 'status'))
            ->setPaper('a4', 'potrait');

        return $pdf->stream('shipment-report-' . now()->format('Ymd-His') . '.pdf');
    }
}
