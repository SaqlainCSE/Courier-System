<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // show simple filter UI
        return view('admin.reports.index');
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
}
