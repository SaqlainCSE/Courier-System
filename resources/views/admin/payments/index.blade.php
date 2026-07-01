@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 text-center text-md-start">
        <h2 class="fw-bold mb-3 mb-md-0 text-dark">
            <i class="fas fa-money-bill-wave me-2 text-success"></i> Payment Management
        </h2>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.payments.index') }}"
              class="d-flex flex-wrap gap-2 align-items-center justify-content-center justify-content-md-start"
              style="max-width: 100%">
            <input type="text" name="search" class="form-control w-100 w-md-auto flex-grow-1"
                placeholder="Search by merchant, tracking number, phone, or email"
                value="{{ request('search') }}">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark">
                    <i class="fas fa-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <a href="{{ route('admin.payments.invoices') }}" class="btn btn-sm btn-danger">
        <i class="fas fa-file-invoice-dollar me-1"></i> Payment Invoices
    </a><br><br>

    <!-- Merchant Accordion -->
    <div class="accordion" id="merchantAccordion">
        @foreach($merchants as $merchant)
            <div class="accordion-item border-0 shadow-sm mb-3 rounded-4 overflow-hidden">
                <h2 class="accordion-header" id="heading-{{ $merchant->id }}">
                    <button class="accordion-button collapsed fw-semibold text-dark bg-light" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $merchant->id }}"
                            aria-expanded="false"
                            aria-controls="collapse-{{ $merchant->id }}">
                        <i class="fas fa-user-tie me-2 text-primary"></i>
                        <span>{{ $merchant->business_name }}</span>
                        <small class="text-muted ms-2 d-block d-md-inline">
                            {{ $merchant->email }} | {{ $merchant->phone }}
                        </small>
                    </button>
                </h2>

                <div id="collapse-{{ $merchant->id }}" class="accordion-collapse collapse"
                     aria-labelledby="heading-{{ $merchant->id }}" data-bs-parent="#merchantAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 merchant-table"
                                   data-merchant-id="{{ $merchant->id }}"
                                   data-merchant-name="{{ $merchant->business_name }}">
                                <thead class="table-dark">
                                    <tr class="text-uppercase small text-center">
                                        <th class="text-center" style="width: 45px;">
                                            <input type="checkbox" class="form-check-input select-all-checkbox"
                                                   title="Select all">
                                        </th>
                                        <th class="text-start ps-3">Tracking</th>
                                        <th class="text-center">Status</th>
                                        <th>Balance (৳)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $shipments = $merchant->shipments
                                            ->sortByDesc('created_at')
                                            ->sortBy(function ($s) {
                                                if ($s->status === 'partially_delivered') {
                                                    $bal = $s->partial_price - $s->cost_of_delivery_amount;
                                                } elseif ($s->status === 'merchant_pay') {
                                                    $bal = $s->balance_cost - $s->cost_of_delivery_amount;
                                                } else {
                                                    $bal = $s->balance_cost;
                                                }
                                                return $bal <= 0;
                                            });
                                    @endphp

                                    @foreach($shipments as $shipment)
                                        @php
                                            if ($shipment->status === 'partially_delivered') {
                                                $displayBalance = $shipment->partial_price - $shipment->cost_of_delivery_amount;
                                            } elseif ($shipment->status === 'merchant_pay') {
                                                $displayBalance = (- $shipment->cost_of_delivery_amount);
                                            } elseif ($shipment->status === 'cancelled') {
                                                $displayBalance = (- $shipment->cost_of_delivery_amount);
                                            } else {
                                                $displayBalance = $shipment->balance_cost;
                                            }
                                        @endphp

                                        <tr id="shipment-{{ $shipment->id }}"
                                            class="{{ $displayBalance > 0 ? 'table-warning' : ($displayBalance < 0 ? 'table-danger-subtle' : '') }}">
                                            <td class="text-center">
                                                <input type="checkbox"
                                                       class="form-check-input shipment-checkbox"
                                                       data-id="{{ $shipment->id }}"
                                                       data-balance="{{ $displayBalance }}"
                                                       data-tracking="{{ $shipment->tracking_number }}"
                                                       data-merchant-id="{{ $merchant->id }}"
                                                       data-merchant-name="{{ $merchant->business_name }}">
                                            </td>
                                            <td class="ps-3">{{ $shipment->tracking_number }}</td>
                                            <td class="text-center">
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'secondary',
                                                        'assigned' => 'info',
                                                        'picked' => 'primary',
                                                        'in_transit' => 'warning',
                                                        'delivered' => 'success',
                                                        'partially_delivered' => 'dark',
                                                        'cancelled' => 'danger',
                                                        'hold' => 'secondary',
                                                        'merchant_pay' => 'success',
                                                    ];
                                                        $paidEligibleStatuses = ['delivered', 'partially_delivered', 'merchant_pay'];
                                                        $isPaid = in_array($shipment->status, $paidEligibleStatuses) && $displayBalance <= 0;
                                                @endphp

                                                    @if($isPaid)
                                                        <span class="badge bg-success px-3 py-2 rounded-pill">
                                                            Paid
                                                        </span>
                                                    @else
                                                        <span class="badge bg-{{ $statusColors[$shipment->status] ?? 'secondary' }} px-3 py-2 rounded-pill">
                                                            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                                        </span>
                                                    @endif
                                            </td>
                                            <td class="balance text-center {{ $displayBalance < 0 ? 'text-danger fw-semibold' : '' }}">
                                                {{ number_format($displayBalance, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Floating Bulk Pay Bar -->
<div id="bulkPayBar" class="bulk-pay-bar d-none">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <span class="fw-bold text-dark" id="selectedCount">0</span> selected
            <span class="text-muted mx-2">|</span>
            <span class="text-muted">Merchant:</span>
            <span class="fw-semibold" id="selectedMerchant">—</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <small class="text-muted d-block">Total Amount</small>
                <span class="fs-5 fw-bold text-success">৳ <span id="selectedTotal">0.00</span></span>
            </div>
            <button type="button" class="btn btn-success" id="openBulkPayBtn">
                <i class="far fa-money-bill-alt me-1"></i> Pay Selected
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSelectionBtn">
                Clear
            </button>
        </div>
    </div>
</div>

<!-- Bulk Payment Modal -->
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form id="bulkPaymentForm">
        @csrf
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i> Bulk Payment</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Merchant</label>
            <input type="text" class="form-control border-0 bg-light" id="bulk_merchant_name" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Selected Shipments</label>
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
              <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Tracking</th>
                    <th class="text-end">Amount (৳)</th>
                  </tr>
                </thead>
                <tbody id="bulkShipmentList"></tbody>
                <tfoot class="table-light">
                  <tr>
                    <th class="text-end">Total</th>
                    <th class="text-end text-success" id="bulkModalTotal">0.00</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label fw-semibold">Total Payment Amount (৳)</label>
            <input type="text" class="form-control border border-success bg-light fw-bold text-success"
                id="bulk_payment_amount" readonly>
          </div>
        </div>

        <div class="modal-footer bg-light rounded-bottom-4">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-success">
            <i class="far fa-money-bill-alt me-1"></i> Submit Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
.bulk-pay-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    border-top: 2px solid #198754;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    padding: 1rem 0;
    z-index: 1050;
}

@media (max-width: 767.98px) {
    h2 { font-size: 1.3rem; }
    .accordion-button { font-size: 0.9rem; flex-wrap: wrap; }
    .accordion-button small { font-size: 0.8rem; }
    .bulk-pay-bar .fs-5 { font-size: 1.1rem !important; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const bulkModal = new bootstrap.Modal(document.getElementById('bulkPaymentModal'));
    let selectedShipments = [];

    function getSelectedCheckboxes() {
        return $('.shipment-checkbox:checked');
    }

    function updateBulkBar() {
        const checked = getSelectedCheckboxes();
        selectedShipments = checked.map(function() {
            return {
                id: $(this).data('id'),
                balance: parseFloat($(this).data('balance')),
                tracking: $(this).data('tracking'),
                merchantId: $(this).data('merchant-id'),
                merchantName: $(this).data('merchant-name'),
            };
        }).get();

        const count = selectedShipments.length;
        const total = selectedShipments.reduce((sum, s) => sum + s.balance, 0);
        const totalEl = $('#selectedTotal');
        const totalClass = total >= 0 ? 'text-success' : 'text-danger';

        if (count > 0) {
            $('#bulkPayBar').removeClass('d-none');
            $('#selectedCount').text(count);
            $('#selectedMerchant').text(selectedShipments[0].merchantName);
            totalEl.text(total.toFixed(2));
            totalEl.closest('.fs-5').removeClass('text-success text-danger').addClass(totalClass);
        } else {
            $('#bulkPayBar').addClass('d-none');
        }
    }

    // Select all within a merchant table
    $(document).on('change', '.select-all-checkbox', function() {
        const table = $(this).closest('table');
        const isChecked = $(this).is(':checked');
        const checkboxes = table.find('.shipment-checkbox');

        if (isChecked) {
            const currentMerchant = checkboxes.first().data('merchant-id');
            const otherSelected = $('.shipment-checkbox:checked').not(checkboxes);
            if (otherSelected.length > 0) {
                $(this).prop('checked', false);
                Swal.fire({
                    icon: 'warning',
                    title: 'Same Merchant Only',
                    text: 'Please select shipments from one merchant at a time.',
                });
                return;
            }
        }

        checkboxes.prop('checked', isChecked);
        updateBulkBar();
    });

    // Individual checkbox — enforce same merchant
    $(document).on('change', '.shipment-checkbox', function() {
        const merchantId = $(this).data('merchant-id');
        const otherMerchants = $('.shipment-checkbox:checked').filter(function() {
            return $(this).data('merchant-id') !== merchantId;
        });

        if (otherMerchants.length > 0) {
            $(this).prop('checked', false);
            Swal.fire({
                icon: 'warning',
                title: 'Same Merchant Only',
                text: 'Please select shipments from one merchant at a time.',
            });
            return;
        }

        const table = $(this).closest('table');
        const all = table.find('.shipment-checkbox');
        const checked = table.find('.shipment-checkbox:checked');
        table.find('.select-all-checkbox').prop('checked', all.length === checked.length);

        updateBulkBar();
    });

    $('#clearSelectionBtn').on('click', function() {
        $('.shipment-checkbox, .select-all-checkbox').prop('checked', false);
        updateBulkBar();
    });

    // Open bulk payment modal
    $('#openBulkPayBtn').on('click', function() {
        if (selectedShipments.length === 0) return;

        const total = selectedShipments.reduce((sum, s) => sum + s.balance, 0);

        $('#bulk_merchant_name').val(selectedShipments[0].merchantName);
        $('#bulk_payment_amount').val(total.toFixed(2));
        $('#bulkModalTotal').text(total.toFixed(2));

        let rows = '';
        selectedShipments.forEach(s => {
            const amtClass = s.balance < 0 ? 'text-danger' : '';
            rows += `<tr>
                <td>${s.tracking}</td>
                <td class="text-end ${amtClass}">${s.balance.toFixed(2)}</td>
            </tr>`;
        });
        $('#bulkShipmentList').html(rows);
        $('#bulkModalTotal').removeClass('text-success text-danger').addClass(total >= 0 ? 'text-success' : 'text-danger');

        bulkModal.show();
    });

    // Submit bulk payment
    $('#bulkPaymentForm').on('submit', function(e) {
        e.preventDefault();

        const shipmentIds = selectedShipments.map(s => s.id);

        $.ajax({
            url: "{{ route('admin.payments.bulk-adjust') }}",
            method: "POST",
            data: {
                _token: $('input[name="_token"]').val(),
                shipment_ids: shipmentIds,
            },
            success: function(res) {
                if (res.success) {
                    res.shipment_ids.forEach(function(id) {
                        const row = $('#shipment-' + id);
                        const balance = parseFloat(res.balances[id] ?? 0);
                        const balanceCell = row.find('.balance');
                        balanceCell.text(balance.toFixed(2));
                        balanceCell.removeClass('text-danger fw-semibold');
                        if (balance < 0) balanceCell.addClass('text-danger fw-semibold');
                        row.removeClass('table-warning table-danger-subtle');
                        if (balance > 0) row.addClass('table-warning');
                        else if (balance < 0) row.addClass('table-danger-subtle');
                    });

                    $('.shipment-checkbox, .select-all-checkbox').prop('checked', false);
                    updateBulkBar();
                    bulkModal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful!',
                        html: `<strong>${res.message}</strong><br>
                               Invoice No: <strong>${res.invoice_number}</strong><br>
                               Total: <strong>৳${parseFloat(res.total_amount).toFixed(2)}</strong><br><br>
                               <a href="${res.invoice_url}" target="_blank"
                                  class="btn btn-danger btn-sm">
                                  <i class="fas fa-file-invoice me-1"></i> View & Print Invoice
                               </a>`,
                        showConfirmButton: true,
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#6c757d'
                    });
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                Swal.fire({ icon: 'error', title: 'Error!', text: msg });
            }
        });
    });
});
</script>
@endpush
@endsection
