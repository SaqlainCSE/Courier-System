@extends('layouts.admin')

@section('content')
<div class="container">
    <h3><i class="fas fa-motorcycle me-2"></i> Edit Delivery Man</h3>
    <form action="{{ route('admin.couriers.update', $courier->id) }}" method="POST">
        @method('PUT')
        @include('admin.couriers._form', ['courier' => $courier])
    </form>
</div>
@endsection
