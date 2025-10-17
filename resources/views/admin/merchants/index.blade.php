@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-store me-2 text-dark"></i>Merchants Management
        </h2>
        <div>
            <a href="{{ route('admin.merchants.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-2"></i>New Merchant
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Merchants</div>
                    <h4 class="fw-bold mb-0">{{ $summary['total'] ?? 0 }}</h4>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">New Today</div>
                    <h4 class="fw-bold mb-0">{{ $summary['new_today'] ?? 0 }}</h4>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">New This Week</div>
                    <h4 class="fw-bold mb-0">{{ $summary['new_this_week'] ?? 0 }}</h4>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">New This Month</div>
                    <h4 class="fw-bold mb-0">{{ $summary['new_this_month'] ?? 0 }}</h4>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}"
                        class="form-control form-control-sm" placeholder="Search name, business, email, phone">
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-sm btn-dark"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="{{ route('admin.merchants.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Merchants Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light fw-semibold">
            <i class="fas fa-list me-2"></i>Merchant List
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-dark">
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
                        <tr>
                            <td class="fw-semibold">{{ $m->name }}</td>
                            <td>{{ $m->business_name ?? '—' }}</td>
                            <td>{{ $m->email }}</td>
                            <td>{{ $m->phone ?? '—' }}</td>
                            <td class="text-center">{{ $m->shipments()->count() }}</td>
                            <td class="text-center">{{ $m->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
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
                                <i class="fas fa-info-circle me-1"></i>No merchants found
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
@endsection
