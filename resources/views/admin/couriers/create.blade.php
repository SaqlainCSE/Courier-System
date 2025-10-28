@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Register New Delivery Man</h2>
    <form action="{{ route('admin.couriers.store') }}" method="POST">
        @csrf
        @include('admin.couriers._form')

    </form>
</div>
@endsection
