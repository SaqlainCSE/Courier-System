@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0 text-dark">
            <i class="fas fa-money-bill-wave me-2"></i> Payment Management
        </h2>
    </div>

    <div class="mb-4">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="d-flex" style="max-width: 500px">
            <input type="text" name="search" class="form-control me-2"
                placeholder="Search by merchant, tracking number,  phone, or email"
                value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-dark">
                Search
            </button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary ms-2">
                    Clear
                </a>
        </form>
    </div>

    <div class="accordion" id="merchantAccordion">
        @foreach($merchants as $merchant)
            <div class="accordion-item border-0 shadow mb-4 rounded-4 overflow-hidden">
                <h2 class="accordion-header" id="heading-{{ $merchant->id }}">
                    <button class="accordion-button collapsed fw-semibold text-dark bg-light" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $merchant->id }}"
                            aria-expanded="false"
                            aria-controls="collapse-{{ $merchant->id }}">
                        <i class="fas fa-user-tie me-2 text-primary"></i>
                        {{ $merchant->business_name }} —
                        <small class="text-muted ms-2">{{ $merchant->email }} | {{ $merchant->phone }}</small>
                    </button>
                </h2>

                <div id="collapse-{{ $merchant->id }}" class="accordion-collapse collapse"
                     aria-labelledby="heading-{{ $merchant->id }}" data-bs-parent="#merchantAccordion">
                    <div class="accordion-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr class="text-uppercase small">
                                    <th class="ps-4">Tracking #</th>
                                    <th>Balance (৳)</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Sort shipments: unpaid first, then paid — both by latest first
                                    $shipments = $merchant->shipments
                                        ->sortByDesc('created_at')
                                        ->sortBy(fn($s) => $s->balance_cost <= 0);
                                @endphp

                                @foreach($shipments as $shipment)
                                    <tr id="shipment-{{ $shipment->id }}" class="{{ $shipment->balance_cost > 0 ? 'table-warning' : '' }}">
                                        <td class="ps-4">{{ $shipment->tracking_number }}</td>
                                        <td class="balance">
                                            {{ number_format($shipment->balance_cost, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if($shipment->balance_cost > 0)
                                                <button
                                                    class="btn btn-sm px-3 py-1 pay-btn btn-outline-success"
                                                    data-id="{{ $shipment->id }}"
                                                    data-balance="{{ $shipment->balance_cost }}"
                                                    data-tracking="{{ $shipment->tracking_number }}">
                                                    <i class="far fa-money-bill-alt me-1"></i> Payment
                                                </button>
                                            @else
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    <i class="fas fa-check-circle me-1"></i> Paid
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form id="paymentForm">
        @csrf
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">
            <i class="fas fa-credit-card me-2"></i> Adjust Payment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="shipment_id" id="shipment_id">

          <div class="mb-3">
            <label class="form-label fw-semibold">Tracking Number</label>
            <input type="text" class="form-control border-0 bg-light" id="tracking_number" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Current Balance (৳)</label>
            <input type="text" class="form-control border-0 bg-light" id="current_balance" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Enter Payment Amount (৳)</label>
            <input type="number" name="amount" class="form-control border border-success"
                   placeholder="Enter payment amount" step="0.01" required>
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
@endsection

@push('scripts')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$(document).ready(function() {
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));

    // open modal
    $(document).on('click', '.pay-btn', function() {
        $('#shipment_id').val($(this).data('id'));
        $('#tracking_number').val($(this).data('tracking'));
        $('#current_balance').val($(this).data('balance'));
        modal.show();
    });

    // submit payment
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('admin.payments.adjust') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    const id = $('#shipment_id').val();
                    const row = $('#shipment-' + id);
                    row.find('.balance').text(res.balance_cost.toFixed(2));

                    if (res.balance_cost <= 0) {
                        row.find('.pay-btn').remove();
                        row.find('td:last').html(`
                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i> Paid
                            </span>
                        `);
                        row.removeClass('table-warning');
                    }
                    modal.hide();
                }
            },
            error: function() {
                alert('Something went wrong. Please try again.');
            }
        });
    });
});
</script>
@endpush
