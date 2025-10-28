@extends('layouts.admin')

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <h3 class="fw-bold text-dark mb-3 mb-md-0">
            <i class="fas fa-motorcycle me-2"></i> Delivery Man Management
        </h3>
        <a href="{{ route('admin.couriers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add New
        </a>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="fas fa-list me-2 text-dark"></i> All Delivery Men
            </h5>
            <span class="badge bg-secondary mt-2 mt-md-0">Total: {{ $couriers->count() }}</span>
        </div>

        <div class="card-body p-0">
            @if($couriers->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-info-circle me-1"></i> No delivery men found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-striped mb-0 table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th class="text-center d-none d-sm-table-cell">Phone</th>
                                <th class="d-none d-md-table-cell">Commission (BDT)</th>
                                <th class="d-none d-lg-table-cell">Vehicle</th>
                                <th>Status</th>
                                <th class="text-center d-none d-sm-table-cell">Joined</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($couriers as $index => $courier)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $courier->user->name ?? '—' }}</span>
                                            <small class="text-muted d-sm-none">{{ $courier->user->phone ?? '—' }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center d-none d-sm-table-cell">{{ $courier->user->phone ?? '—' }}</td>
                                    <td class="d-none d-md-table-cell text-center">{{ $courier->commission_rate }}</td>
                                    <td class="d-none d-lg-table-cell">
                                        {{ $courier->vehicle_type ?? '—' }}<br>
                                        <small class="text-muted">{{ $courier->vehicle_number ?? '' }}</small>
                                    </td>

                                    @php
                                        $statusColors = [
                                            'available' => 'bg-success',
                                            'busy' => 'bg-warning text-dark',
                                            'off' => 'bg-secondary'
                                        ];
                                    @endphp
                                    <td>
                                        <span class="badge {{ $statusColors[$courier->status] ?? 'bg-light text-dark' }}">
                                            {{ ucfirst($courier->status) }}
                                        </span>
                                    </td>

                                    <td class="text-center d-none d-sm-table-cell">{{ $courier->created_at->format('d M Y') }}</td>

                                    <td class="text-center">
                                        <div class="d-flex flex-wrap justify-content-center gap-1">
                                            <a href="{{ route('admin.couriers.view', $courier->id) }}"
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="{{ route('admin.couriers.edit', $courier->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('admin.couriers.destroy', $courier->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this delivery man?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
