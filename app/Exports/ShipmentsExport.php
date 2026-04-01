<?php

namespace App\Exports;

use App\Models\Shipment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;

class ShipmentsExport implements FromView
{
    public $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Shipment::where('user_id', Auth::id());

        if (!empty($this->filters['q'])) {
            $search = $this->filters['q'];
            $query->where(function($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('pickup_address', 'like', "%{$search}%")
                  ->orWhere('drop_address', 'like', "%{$search}%")
                  ->orWhere('pickup_name', 'like', "%{$search}%")
                  ->orWhere('drop_name', 'like', "%{$search}%")
                  ->orWhere('pickup_phone', 'like', "%{$search}%")
                  ->orWhere('drop_phone', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        $shipments = $query->latest()->get();

        return view('shipments.exports.excel', compact('shipments'));
    }
}
