@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📦 My Shipments</h2>
        <a href="{{ route('shipments.create') }}" class="btn btn-primary shadow">+ New Shipment</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Tracking #</th>
                        <th>Pickup</th>
                        <th>Drop</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Booked At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($shipments as $shipment)
                    <tr>
                        <td><span class="fw-semibold">{{ $shipment->tracking_number }}</span></td>
                        <td>{{ $shipment->pickup_name }} <br><small class="text-muted">{{ $shipment->pickup_address }}</small></td>
                        <td>{{ $shipment->drop_name }} <br><small class="text-muted">{{ $shipment->drop_address }}</small></td>
                        <td>{{ $shipment->weight_kg }} kg</td>
                        <td>
                            <span class="badge bg-{{ $shipment->status === 'delivered' ? 'success' : ($shipment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst(str_replace('_',' ', $shipment->status)) }}
                            </span>
                        </td>
                        <td>৳ {{ number_format($shipment->price, 2) }}</td>
                        <td>{{ $shipment->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-sm btn-outline-info">View</a>
                            @if($shipment->status === 'pending')
                                <form action="{{ route('shipments.cancel', $shipment) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">🚚 No shipments yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
