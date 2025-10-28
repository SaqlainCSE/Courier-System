@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom pb-2 text-center text-md-start">
        <h2 class="fw-bold mb-3 mb-md-0">
            <i class="fas fa-store me-2 text-dark"></i> Merchants Management
        </h2>
        <div>
            <a href="{{ route('admin.merchants.create') }}" class="btn btn-primary shadow-sm w-100 w-md-auto">
                <i class="fas fa-plus me-2"></i> New Merchant
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['title'=>'Total Merchants','value'=>$summary['total'] ?? 0,'color'=>'primary'],
            ['title'=>'New Today','value'=>$summary['new_today'] ?? 0,'color'=>'success'],
            ['title'=>'New This Week','value'=>$summary['new_this_week'] ?? 0,'color'=>'success'],
            ['title'=>'New This Month','value'=>$summary['new_this_month'] ?? 0,'color'=>'success'],
        ] as $card)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center text-md-start">
                    <div class="text-muted small mb-1">{{ $card['title'] }}</div>
                    <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-{{ $card['color'] }}" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center justify-content-center justify-content-md-start">
                <div class="col-12 col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}"
                        class="form-control form-control-sm" placeholder="Search name, business, email, phone">
                </div>
                <div class="col-auto mt-2 mt-md-0">
                    <button class="btn btn-sm btn-dark w-100 w-md-auto">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-auto mt-2 mt-md-0">
                    <a href="{{ route('admin.merchants.index') }}" class="btn btn-sm btn-outline-secondary w-100 w-md-auto">
                        <i class="fas fa-undo me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Merchants Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light fw-semibold">
            <i class="fas fa-list me-2"></i> Merchant List
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-dark d-none d-md-table-header-group">
                    <tr>
                        <th>Name</th>
                        <th>Business</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-center">Shipments</th>
                        <th class="text-center">Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($merchants as $m)
                        <tr class="merchant-row">
                            <td data-label="Name" class="fw-semibold">{{ $m->name }}</td>
                            <td data-label="Business">{{ $m->business_name ?? '—' }}</td>
                            <td data-label="Email">{{ $m->email }}</td>
                            <td data-label="Phone">{{ $m->phone ?? '—' }}</td>
                            <td data-label="Shipments" class="text-center">{{ $m->shipments()->count() }}</td>
                            <td data-label="Joined" class="text-center">{{ $m->created_at->format('d M Y') }}</td>
                            <td data-label="Actions" class="text-center">
                                <div class="btn-group d-flex d-md-inline-flex flex-wrap gap-1 justify-content-center">
                                    <a href="{{ route('admin.merchants.show', $m) }}"
                                       class="btn btn-sm btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.merchants.edit', $m) }}"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.merchants.destroy', $m) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete merchant?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle me-1"></i> No merchants found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light">
            {{ $merchants->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Responsive Styles --}}
@push('styles')
<style>
/* Mobile adjustments */
@media (max-width: 767.98px) {
    h2 {
        font-size: 1.3rem;
    }

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
        align-items: center;
        padding: 0.4rem 0;
        font-size: 0.9rem;
    }

    .table tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6c757d;
    }

    .table tbody td:last-child {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        justify-content: center;
    }

    .btn-group .btn {
        flex: 1 1 auto;
        font-size: 0.8rem;
        padding: 0.4rem 0.5rem;
    }
}
</style>
@endpush
@endsection
