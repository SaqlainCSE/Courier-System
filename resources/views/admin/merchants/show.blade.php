@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom pb-2 text-center text-md-start">
        <h2 class="fw-bold mb-3 mb-md-0">
            <i class="fas fa-user-tie me-2 text-dark"></i>
            {{ $merchant->business_name ?: $merchant->name }}
        </h2>
        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
            <a href="{{ route('admin.merchants.edit', $merchant) }}" class="btn btn-warning btn-sm shadow-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('admin.merchants.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Merchant Details --}}
    <div class="row g-4 mb-4">

        {{-- Personal Info --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">
                    <i class="fas fa-id-card me-2"></i>Merchant Info
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted">Name</div>
                        <div class="fw-semibold">{{ $merchant->name }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">Email</div>
                        <div>{{ $merchant->email }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">Phone</div>
                        <div>{{ $merchant->phone ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Business Info --}}
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">
                    <i class="fas fa-briefcase me-2"></i>Business Overview
                </div>
                <div class="card-body">

                    {{-- Address --}}
                    <div class="mb-3">
                        <div class="small text-muted">Business Address</div>
                        <div class="fw-semibold text-muted">{{ $merchant->business_address ?: '—' }}</div>
                    </div>

                    {{-- Stats --}}
                    <div class="row text-center mb-3">
                        <div class="col-6 col-md-3 border-end mb-2 mb-md-0">
                            <div class="small text-muted">Total Shipments</div>
                            <h5 class="fw-bold">{{ $summary['total_shipments'] ?? 0 }}</h5>
                        </div>
                        <div class="col-6 col-md-3 border-end mb-2 mb-md-0">
                            <div class="small text-muted">Delivered</div>
                            <h5 class="fw-bold text-success">{{ $summary['delivered'] ?? 0 }}</h5>
                        </div>
                        <div class="col-6 col-md-3 border-end mb-2 mb-md-0">
                            <div class="small text-muted">Partially Delivered</div>
                            <h5 class="fw-bold text-warning">{{ $summary['partially_delivered'] ?? 0 }}</h5>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Balance</div>
                            <h5 class="fw-bold text-primary">৳{{ number_format($summary['balance'] ?? 0, 2) }}</h5>
                        </div>
                    </div>

                    {{-- Recent Shipments --}}
                    <h6 class="fw-semibold mb-3"><i class="fas fa-truck me-2"></i>Recent Shipments</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-dark d-none d-md-table-header-group">
                                <tr>
                                    <th>Tracking</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipments as $s)
                                <tr class="clickable-row" onclick="window.location='{{ route('admin.shipments.show', $s) }}'" role="button">
                                    <td data-label="Tracking">{{ $s->tracking_number }}</td>
                                    <td data-label="Status">
                                        @php
                                            $statusColors = [
                                                'pending' => '#ffc107',
                                                'assigned' => '#0dcaf0',
                                                'picked' => '#0d6efd',
                                                'in_transit' => '#0d6efd',
                                                'delivered' => '#198754',
                                                'cancelled' => '#dc3545',
                                                'hold' => '#6c757d',
                                                'partially_delivered' => '#343a40',
                                            ];
                                            $status = strtolower($s->status);
                                            $badgeColor = $statusColors[$status] ?? '#adb5bd';
                                        @endphp
                                        <span class="badge text-light" style="background-color: {{ $badgeColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $s->status)) }}
                                        </span>
                                    </td>
                                    <td data-label="Price">৳{{ number_format($s->price, 2) }}</td>
                                    <td data-label="Date">{{ $s->created_at->format('d M Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle me-1"></i>No shipments
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $shipments->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- Responsive Styles --}}
@push('styles')
<style>
/* Mobile table adjustments */
@media (max-width: 767.98px) {
    .table thead {
        display: none;
    }
    .table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .table tbody td {
        display: flex;
        justify-content: space-between;
        padding: 0.4rem 0;
        font-size: 0.9rem;
    }
    .table tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6c757d;
    }
}
.clickable-row:hover {
    cursor: pointer;
    background-color: #f8f9fa;
}
</style>
@endpush
@endsection
