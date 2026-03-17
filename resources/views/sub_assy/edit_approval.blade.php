@extends('layouts.admin')

@section('title', 'Edit Status Approval')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Status Approval</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Checksheet ID: {{ $checksheet->id }}</h6>
        </div>
        <div class="card-body">
            @include('sub_assy.partials.edit_approval_form')
        </div>
    </div>
@endsection
