@extends('layouts.app')
@section('content')
<h3>My Shipments</h3>
<table class="table">
  <thead><tr><th>Tracking</th><th>Pickup</th><th>Drop</th><th>Status</th><th>Price</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($shipments as $s)
      <tr>
        <td>{{ $s->tracking_number }}</td>
        <td>{{ Str::limit($s->pickup_address,40) }}</td>
        <td>{{ Str::limit($s->drop_address,40) }}</td>
        <td>{{ $s->status }}</td>
        <td>{{ $s->price }}</td>
        <td>
          <a class="btn btn-sm btn-primary" href="{{ route('shipments.show', $s) }}">View</a>
          @if(in_array($s->status, ['pending','assigned']))
            <form action="{{ route('shipments.cancel', $s) }}" method="POST" style="display:inline">@csrf
              <button class="btn btn-sm btn-danger">Cancel</button>
            </form>
          @endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $shipments->links() }}
@endsection
