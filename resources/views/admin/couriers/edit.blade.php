@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Edit Delivery Man: {{ $courier->user->name }}</h2>
    <form action="{{ route('admin.couriers.update', $courier) }}" method="POST">
        @csrf
        @method('PATCH')
        @include('admin.couriers._form', ['courier' => $courier])

    </form>
</div>
@endsection
