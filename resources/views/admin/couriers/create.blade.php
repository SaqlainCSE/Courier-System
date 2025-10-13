@extends('layouts.admin')

@section('content')
<div class="container">
    <h3><i class="fas fa-motorcycle me-2"></i>Create New Delivery Man</h3>
    <form action="{{ route('admin.couriers.store') }}" method="POST">
        @include('admin.couriers._form', ['courier' => null])
    </form>
</div>
@endsection
