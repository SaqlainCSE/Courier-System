@extends('layouts.admin')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="text-center mb-5">
        <h1 class="fw-semibold fs-3">
            <i class="fas fa-truck-moving text-danger me-2"></i> Bulk assign shipments
        </h1>
        <p class="text-muted small mb-0">Search by tracking number or phone number, select shipments, and assign to a delivery man</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-dark text-white rounded-top-3 fw-semibold">
            <i class="fas fa-search me-2"></i> Search shipments
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="shipmentSearch" class="form-label fw-semibold small mb-1">
                        Tracking number or phone number
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text"
                               id="shipmentSearch"
                               class="form-control"
                               placeholder="e.g. TRK-12345 or 01712345678"
                               value="{{ request('q') }}"
                               autocomplete="off">
                        <button type="button" class="btn btn-primary" id="searchBtn">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn" style="display: none;">
                            <i class="fas fa-times me-1"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div id="searchStatus" class="small text-muted py-2">
                        <i class="fas fa-info-circle me-1"></i> Type tracking # or phone number to search
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.shipments.bulk.store') }}" method="POST" id="bulkAssignForm">
        @csrf

        {{-- Hidden inputs for selections made via search (synced by JS) --}}
        <div id="hiddenShipmentInputs"></div>

        {{-- Shipments Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-dark text-white rounded-top-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="fw-semibold">
                    <i class="fas fa-list me-2"></i>
                    <span id="tableTitle">Pending shipments</span>
                    (<span id="visibleCount">{{ count($shipments) }}</span>)
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="selectAll">
                        <i class="fas fa-check-square me-1"></i> Select all visible
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="deselectAll">
                        <i class="fas fa-square me-1"></i> Clear selection
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" class="form-check-input" id="masterCheckbox">
                                </th>
                                <th>Tracking #</th>
                                <th>Drop phone</th>
                                <th>Pickup</th>
                                <th>Dropoff</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody id="shipmentsTableBody">
                            @forelse($shipments as $shipment)
                                <tr class="shipment-row"
                                    data-id="{{ $shipment->id }}"
                                    data-tracking="{{ strtolower($shipment->tracking_number) }}"
                                    data-phone="{{ strtolower($shipment->drop_phone) }}">
                                    <td>
                                        <input type="checkbox" name="shipment_ids[]"
                                               value="{{ $shipment->id }}"
                                               class="form-check-input shipment-checkbox">
                                    </td>
                                    <td><strong>{{ $shipment->tracking_number }}</strong></td>
                                    <td class="text-nowrap">{{ $shipment->drop_phone }}</td>
                                    <td style="max-width: 180px;">
                                        <div class="small text-muted text-truncate">
                                            {{ $shipment->customer->business_name ?? 'N/A' }}<br>
                                            {{ Str::limit($shipment->pickup_address, 35) }}
                                        </div>
                                    </td>
                                    <td style="max-width: 180px;">
                                        <div class="small text-muted text-truncate">
                                            {{ $shipment->drop_name }}<br>
                                            {{ Str::limit($shipment->drop_address, 35) }}
                                        </div>
                                    </td>
                                    <td><strong>৳ {{ number_format($shipment->price, 2) }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $shipment->status === 'hold' ? 'warning text-dark' : 'secondary' }}">
                                            {{ ucfirst($shipment->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap small text-muted">
                                        {{ $shipment->created_at->format('d M Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="8" class="p-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                        <p class="mb-0">No pending shipments found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Assignment Section --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white rounded-top-3 fw-semibold">
                <i class="fas fa-user-tie me-2"></i> Assign to delivery man
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">
                            Select delivery man <span class="text-danger">*</span>
                        </label>
                        <select name="courier_id"
                                class="form-select form-select-lg @error('courier_id') is-invalid @enderror"
                                required>
                            <option value="">-- Choose a delivery man --</option>
                            @foreach($couriers as $courier)
                                <option value="{{ $courier->id }}"
                                        @selected(old('courier_id') == $courier->id)>
                                    {{ $courier->user->name }} ({{ $courier->user->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('courier_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">
                            Selected shipments:
                            <span id="selectedCount" class="badge bg-primary ms-1">0</span>
                        </label>
                        <div class="alert alert-info py-2 mt-2 mb-0 small"
                             id="selectedAlert" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="selectedMessage"></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success btn-sm" id="assignBtn" disabled>
                        <i class="fas fa-check-circle me-2"></i> Assign selected shipments
                    </button>
                    <a href="{{ route('admin.shipments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
    const searchUrl          = @json(route('admin.shipments.bulk.search'));
    const searchInput        = document.getElementById('shipmentSearch');
    const searchBtn          = document.getElementById('searchBtn');
    const clearSearchBtn     = document.getElementById('clearSearchBtn');
    const searchStatus       = document.getElementById('searchStatus');
    const tableBody          = document.getElementById('shipmentsTableBody');
    const tableTitle         = document.getElementById('tableTitle');
    const visibleCount       = document.getElementById('visibleCount');
    const masterCheckbox     = document.getElementById('masterCheckbox');
    const selectAllBtn       = document.getElementById('selectAll');
    const deselectAllBtn     = document.getElementById('deselectAll');
    const selectedCount      = document.getElementById('selectedCount');
    const assignBtn          = document.getElementById('assignBtn');
    const selectedAlert      = document.getElementById('selectedAlert');
    const selectedMessage    = document.getElementById('selectedMessage');
    const hiddenInputs       = document.getElementById('hiddenShipmentInputs');

    const originalTableHtml  = tableBody.innerHTML;
    let searchTimeout        = null;
    let isSearchMode         = false;
    const selectedIds        = new Set();

    function getVisibleCheckboxes() {
        return [...document.querySelectorAll('.shipment-checkbox')].filter(cb => {
            const row = cb.closest('tr');
            return row && row.style.display !== 'none';
        });
    }

    function getAllCheckboxes() {
        return [...document.querySelectorAll('.shipment-checkbox')];
    }

    function syncHiddenInputs() {
        hiddenInputs.innerHTML = '';
        selectedIds.forEach(id => {
            const inTable = document.querySelector(`.shipment-checkbox[value="${id}"]`);
            if (!inTable || !inTable.checked) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'shipment_ids[]';
                input.value = id;
                hiddenInputs.appendChild(input);
            }
        });
    }

    function updateCount() {
        getAllCheckboxes().forEach(cb => {
            if (cb.checked) {
                selectedIds.add(cb.value);
            } else {
                selectedIds.delete(cb.value);
            }
        });

        syncHiddenInputs();

        const total = selectedIds.size;
        selectedCount.textContent = total;

        if (total > 0) {
            selectedAlert.style.display = 'block';
            selectedMessage.textContent = total + ' shipment(s) selected for assignment';
            assignBtn.disabled = false;
        } else {
            selectedAlert.style.display = 'none';
            assignBtn.disabled = true;
        }
    }

    function updateMasterCheckbox() {
        const visible = getVisibleCheckboxes();
        if (visible.length === 0) {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = false;
            return;
        }
        const all  = visible.every(c => c.checked);
        const some = visible.some(c => c.checked);
        masterCheckbox.checked = all;
        masterCheckbox.indeterminate = some && !all;
    }

    function bindCheckboxEvents() {
        getAllCheckboxes().forEach(cb => {
            cb.removeEventListener('change', onCheckboxChange);
            cb.addEventListener('change', onCheckboxChange);

            if (selectedIds.has(cb.value)) {
                cb.checked = true;
            }
        });
        updateMasterCheckbox();
        updateCount();
    }

    function onCheckboxChange() {
        if (this.checked) {
            selectedIds.add(this.value);
        } else {
            selectedIds.delete(this.value);
        }
        updateMasterCheckbox();
        updateCount();
    }

    function renderShipmentRows(shipments) {
        if (shipments.length === 0) {
            tableBody.innerHTML = `
                <tr id="noResultsRow">
                    <td colspan="8" class="p-5 text-center text-muted">
                        <i class="fas fa-search fa-2x mb-3 d-block"></i>
                        <p class="mb-0">No shipments found for this search.</p>
                    </td>
                </tr>`;
            visibleCount.textContent = '0';
            masterCheckbox.disabled = true;
            return;
        }

        masterCheckbox.disabled = false;
        visibleCount.textContent = shipments.length;

        tableBody.innerHTML = shipments.map(s => {
            const checked = selectedIds.has(String(s.id)) ? 'checked' : '';
            const statusBadge = s.status === 'hold'
                ? 'bg-warning text-dark'
                : 'bg-secondary';

            return `
                <tr class="shipment-row" data-id="${s.id}">
                    <td>
                        <input type="checkbox" name="shipment_ids[]"
                               value="${s.id}"
                               class="form-check-input shipment-checkbox" ${checked}>
                    </td>
                    <td><strong>${escapeHtml(s.tracking_number)}</strong></td>
                    <td class="text-nowrap">${escapeHtml(s.drop_phone)}</td>
                    <td style="max-width: 180px;">
                        <div class="small text-muted text-truncate">
                            ${escapeHtml(s.pickup_name)}<br>
                            ${escapeHtml(truncate(s.pickup_address, 35))}
                        </div>
                    </td>
                    <td style="max-width: 180px;">
                        <div class="small text-muted text-truncate">
                            ${escapeHtml(s.drop_name)}<br>
                            ${escapeHtml(truncate(s.drop_address, 35))}
                        </div>
                    </td>
                    <td><strong>৳ ${s.price}</strong></td>
                    <td>
                        <span class="badge ${statusBadge}">${escapeHtml(s.status)}</span>
                    </td>
                    <td class="text-nowrap small text-muted">${escapeHtml(s.created_at)}</td>
                </tr>`;
        }).join('');

        bindCheckboxEvents();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function truncate(text, len) {
        if (!text) return '';
        return text.length > len ? text.substring(0, len) + '…' : text;
    }

    function performSearch() {
        const query = searchInput.value.trim();

        if (!query) {
            resetSearch();
            return;
        }

        searchStatus.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Searching...';
        isSearchMode = true;
        tableTitle.textContent = 'Search results';
        clearSearchBtn.style.display = 'inline-block';

        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                renderShipmentRows(data);
                searchStatus.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> Found <strong>${data.length}</strong> shipment(s) for "<strong>${escapeHtml(query)}</strong>"`;
            })
            .catch(() => {
                searchStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-1"></i> Search failed. Please try again.';
            });
    }

    function resetSearch() {
        searchInput.value = '';
        isSearchMode = false;
        tableTitle.textContent = 'Pending shipments';
        clearSearchBtn.style.display = 'none';
        searchStatus.innerHTML = '<i class="fas fa-info-circle me-1"></i> Type tracking # or phone numberto search';
        tableBody.innerHTML = originalTableHtml;
        masterCheckbox.disabled = false;

        const rows = document.querySelectorAll('.shipment-row');
        visibleCount.textContent = rows.length;

        bindCheckboxEvents();
    }

    function filterLocalRows() {
        const query = searchInput.value.trim().toLowerCase();

        if (!query) {
            if (isSearchMode) {
                resetSearch();
            }
            return;
        }

        if (isSearchMode) return;

        const rows = document.querySelectorAll('.shipment-row');
        let visible = 0;

        rows.forEach(row => {
            const tracking = row.dataset.tracking || '';
            const phone    = row.dataset.phone || '';
            const match    = tracking.includes(query) || phone.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        visibleCount.textContent = visible;
        tableTitle.textContent = visible < rows.length ? 'Filtered shipments' : 'Pending shipments';
        clearSearchBtn.style.display = 'inline-block';
        searchStatus.innerHTML = `<i class="fas fa-filter me-1"></i> Showing <strong>${visible}</strong> of <strong>${rows.length}</strong> shipment(s)`;

        updateMasterCheckbox();
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (!query) {
            resetSearch();
            return;
        }

        clearSearchBtn.style.display = 'inline-block';

        searchTimeout = setTimeout(() => {
            if (query.length >= 2) {
                performSearch();
            } else {
                filterLocalRows();
            }
        }, 350);
    });

    searchBtn.addEventListener('click', performSearch);

    clearSearchBtn.addEventListener('click', resetSearch);

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    masterCheckbox.addEventListener('change', function () {
        getVisibleCheckboxes().forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) {
                selectedIds.add(cb.value);
            } else {
                selectedIds.delete(cb.value);
            }
        });
        updateCount();
    });

    selectAllBtn.addEventListener('click', () => {
        getVisibleCheckboxes().forEach(cb => {
            cb.checked = true;
            selectedIds.add(cb.value);
        });
        masterCheckbox.checked = true;
        masterCheckbox.indeterminate = false;
        updateCount();
    });

    deselectAllBtn.addEventListener('click', () => {
        getAllCheckboxes().forEach(cb => {
            cb.checked = false;
        });
        selectedIds.clear();
        masterCheckbox.checked = false;
        masterCheckbox.indeterminate = false;
        updateCount();
    });

    @if(request('q'))
        performSearch();
    @else
        bindCheckboxEvents();
    @endif
</script>
@endpush
@endsection
