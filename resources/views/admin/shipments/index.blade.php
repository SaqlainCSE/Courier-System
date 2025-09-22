@extends('layouts.app')
@section('content')
<h3>All Shipments</h3>
<table class="table">
<thead><tr><th>ID</th><th>Tracking</th><th>Customer</th><th>Courier</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
@foreach($shipments as $s)
<tr>
  <td>{{ $s->id }}</td>
  <td>{{ $s->tracking_number }}</td>
  <td>{{ $s->customer->name }}</td>
  <td>{{ $courier->user->name ?? 'N/A' }}</td>
  <td>{{ $s->status }}</td>
  <td><a class="btn btn-sm btn-primary" href="{{ route('admin.shipments.show', $s) }}">View</a></td>
</tr>
@endforeach
</tbody>
</table>
{{ $shipments->links() }}
@endsection
