@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Shipment Details</h4>
            <p class="mb-1"><strong>Tracking#:</strong>
                        <span class="text-danger fs-6">{{ $shipment->tracking_number }}</span>
                    </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">Back</a>
            <a href="{{ route('shipments.print', $shipment) }}" target="_blank" class="btn btn-sm btn-outline-dark">Print</a>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">Quick Print</button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="small text-muted">Status</div>
                            @php
                                $status = $shipment->status;
                                $badge = match($status) {
                                    'pending' => 'bg-secondary',
                                    'assigned' => 'bg-info text-dark',
                                    'picked' => 'bg-primary',
                                    'hold' => 'bg-secondary',
                                    'delivered' => 'bg-success',
                                    'partially_delivered' => 'bg-secondary',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badge }} rounded-pill px-3 py-2">{{ ucwords(str_replace('_',' ', $status)) }}</span>
                        </div>

                        <div class="text-end small text-muted">
                            Created: {{ $shipment->created_at?->format('d M Y • H:i') }}<br />
                            Updated: {{ $shipment->updated_at?->diffForHumans() }}
                        </div>
                    </div>

                    <hr />

                    <div class="row g-3">
                        <!-- Pickup (Customer Info from User model) -->
                        <div class="col-md-6">
                            <h6 class="mb-2 small text-uppercase text-muted">Pickup</h6>
                            <div class="fw-semibold">
                                {{ $shipment->customer?->business_name ?? '-' }}
                                <span class="text-muted small">• {{ $shipment->customer?->phone ?? '-' }}</span>
                            </div>
                            <div class="text-muted small">{{ $shipment->customer?->business_address ?? '-' }}</div>
                            @if($shipment->pickup_lat && $shipment->pickup_lng)
                                <div class="mt-2">
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $shipment->pickup_lat }},{{ $shipment->pickup_lng }}" target="_blank" class="link-secondary small">Open pickup on map</a>
                                </div>
                            @endif
                        </div>

                        <!-- Dropoff (still shipment fields) -->
                        <div class="col-md-6">
                            <h6 class="mb-2 small text-uppercase text-muted">Dropoff</h6>
                            <div class="fw-semibold">{{ $shipment->drop_name }} <span class="text-muted small">• {{ $shipment->drop_phone }}</span></div>
                            <div class="text-muted small">{{ $shipment->drop_address }}</div>
                            @if($shipment->drop_lat && $shipment->drop_lng)
                                <div class="mt-2">
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $shipment->drop_lat }},{{ $shipment->drop_lng }}" target="_blank" class="link-secondary small">Open dropoff on map</a>
                                </div>
                            @endif
                        </div>

                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="small text-muted">Weight</div>
                                    <div class="fw-semibold">{{ $shipment->weight_kg }} kg</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Price</div>
                                    <div class="fw-semibold">৳ {{ number_format($shipment->price, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        @if($shipment->notes)
                            <div class="col-12">
                                <div class="small text-muted">Notes</div>
                                <div class="border rounded p-2 bg-light">{{ $shipment->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="mb-3">Activity & Status Timeline</h6>

                    @if($shipment->statusLogs && $shipment->statusLogs->isNotEmpty())
                        <ul class="timeline list-unstyled mb-0">
                            @foreach($shipment->statusLogs->sortByDesc('created_at') as $log)
                                <li class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="fw-semibold small">{{ ucwords(str_replace('_',' ', $log->status)) }}</div>
                                            <div class="text-muted small">{{ $log->note }}</div>
                                            <div class="text-muted very-small">{{ $log->created_at?->format('d M Y • H:i') }} • by {{ $log->user?->name ?? 'System' }}</div>
                                        </div>
                                        <div class="text-muted small">{{ $log->created_at?->diffForHumans() }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4 text-muted">No activity yet.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="small text-uppercase text-muted mb-3">Assignment</h6>

                    <div class="mb-2">
                        <div class="small text-muted">Courier</div>
                        <div class="fw-semibold">{{ $shipment->courier?->user?->name ?? 'Unassigned' }}</div>
                        @if($shipment->courier?->user?->phone)
                            <div class="text-muted small">{{ $shipment->courier?->user?->phone }}</div>
                        @endif
                    </div>

                    <div class="mb-2">
                        <div class="small text-muted">From Branch</div>
                        <div class="fw-semibold">{{ $shipment->fromBranch?->name ?? 'Goran' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Estimated delivery</div>
                        <div class="fw-semibold">{{ $shipment->estimated_delivery_at?->format('d M Y • H:i') ?? '-' }}</div>
                    </div>

                    @can('update', $shipment)
                        <div class="d-grid gap-2">
                            <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-sm btn-outline-primary">Edit Shipment</a>
                        </div>
                    @endcan
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="small text-uppercase text-muted mb-3">Quick Actions</h6>

                    @if(Auth::user()?->isCourier() && Auth::user()?->courierProfile?->id === $shipment->courier_id)
                        <button class="btn btn-sm btn-primary w-100 mb-2" data-bs-toggle="collapse" data-bs-target="#quickUpdatePanel" aria-expanded="false">
                            Update Status
                        </button>

                        <div class="collapse" id="quickUpdatePanel">
                            <form action="{{ route('courier.shipments.updateStatus', $shipment) }}" method="POST" class="mt-3">
                                @csrf
                                <div class="mb-2">
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="picked" @selected($shipment->status=='picked')>Picked</option>
                                        <option value="hold" @selected($shipment->status=='hold')>Hold</option>
                                        <option value="delivered" @selected($shipment->status=='delivered')>Delivered</option>
                                        <option value="partially_delivered" @selected($shipment->status=='partially_delivered')>Partially Delivered</option>
                                        <option value="cancelled" @selected($shipment->status=='cancelled')>Cancelled</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Optional note"></textarea>
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-sm btn-success">Apply</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="text-muted small">Only assigned courier can update status from this panel.</div>
                    @endif

                    @if($shipment->customer?->phone || $shipment->drop_phone)
                        <hr class="my-3" />
                        <div class="small text-muted mb-2">Contact</div>
                        @if($shipment->customer?->phone)
                            <p class="d-block mb-1"><i class="bi bi-telephone me-1"></i> Pickup: {{ $shipment->customer->phone }}</p>
                        @endif
                        @if($shipment->drop_phone)
                            <p class="d-block"><i class="bi bi-telephone me-1"></i> Dropoff: {{ $shipment->drop_phone }}</p>
                        @endif

                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
/* small timeline helper */
.timeline li { border-left: 2px solid rgba(0,0,0,.05); padding-left: 12px; margin-left: 6px; }
.very-small { font-size: .72rem; color: #6c757d; }
</style>
@endsection
