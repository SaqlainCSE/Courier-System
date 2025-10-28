@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h2 class="fw-bold mb-0">
            <i class="fas fa-user-plus me-2 text-dark"></i>Create Merchant
        </h2>
        <a href="{{ route('admin.merchants.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Form Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.merchants.store') }}" autocomplete="off">
                @csrf
                @include('admin.merchants._form')
            </form>
        </div>
    </div>

</div>
@endsection
