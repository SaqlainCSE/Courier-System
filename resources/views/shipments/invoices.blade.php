@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-2 mb-md-0">
            <i class="fas fa-file-invoice-dollar me-2 text-danger"></i> Payment Invoices
        </h2>
        <a href="{{ route('shipments.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Search / Filter --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('shipments.invoices') }}">
                <div class="row g-2 align-items-end">

                    {{-- Date From --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="fas fa-calendar-alt me-1"></i> From
                        </label>
                        <input type="date" name="date_from" class="form-control border-0 bg-light"
                               value="{{ request('date_from') }}">
                    </div>

                    {{-- Date To --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="fas fa-calendar-check me-1"></i> To
                        </label>
                        <input type="date" name="date_to" class="form-control border-0 bg-light"
                               value="{{ request('date_to') }}">
                    </div>

                    {{-- Text Search --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <input type="text" name="search" class="form-control border-0 bg-light"
                               placeholder="Invoice no or tracking..."
                               value="{{ request('search') }}">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark btn-sm w-100">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('shipments.invoices') }}" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <div class="text-muted small mb-1">
                    <i class="fas fa-file-invoice me-1"></i> Total Invoices
                </div>
                <div class="fs-4 fw-bold text-dark">{{ $totalInvoices }}</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <div class="text-muted small mb-1">
                    <i class="fas fa-money-bill-wave me-1"></i> Total Paid (৳)
                </div>
                <div class="fs-4 fw-bold text-success">
                    {{ number_format($totalAmount, 2) }}
                </div>
            </div>
        </div>

        {{-- Filter Info --}}
        @if(request('date_from') || request('date_to') || request('search'))
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 d-flex flex-row align-items-center gap-3">
                <i class="fas fa-filter fs-4 text-primary"></i>
                <div>
                    <div class="text-muted small">Filtered Results</div>
                    <div class="fw-semibold text-dark">
                        {{ $totalInvoices }} invoice(s) —
                        <span class="text-success">৳{{ number_format($totalAmount, 2) }}</span>

                        @if(request('date_from') || request('date_to'))
                            &bull; {{ request('date_from') ?? '...' }} → {{ request('date_to') ?? '...' }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr class="text-uppercase small text-center">
                        <th class="text-start ps-3">#</th>
                        <th class="text-start">Invoice No</th>
                        <th>Tracking</th>
                        <th>Amount (৳)</th>
                        <th>Payment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payments as $i => $payment)
                        <tr>
                            <td class="ps-3 text-muted small">
                                {{ $payments->firstItem() + $i }}
                            </td>

                            <td class="fw-semibold text-dark">
                                {{ $payment->invoice_number }}
                            </td>

                            <td class="text-center fw-semibold">
                                {{ $payment->shipment->tracking_number ?? '—' }}
                            </td>

                            <td class="text-center fw-bold text-success">
                                ৳{{ number_format($payment->amount, 2) }}
                            </td>

                            <td class="text-center text-muted small">
                                {{ $payment->created_at->format('d M Y') }}<br>
                                <span>{{ $payment->created_at->format('h:i A') }}</span>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('shipments.invoice', $payment->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-file-invoice me-1"></i> Invoice
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- ✅ FIXED TOTAL --}}
                @if($payments->isNotEmpty())
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold ps-3 text-dark">Total:</td>
                        <td class="text-center fw-bold text-success">
                            ৳{{ number_format($totalAmount, 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif

            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $payments->withQueryString()->links() }}
    </div>

</div>
@endsection
