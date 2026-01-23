@extends('layouts.admin')

@section('title', 'Edit Checksheet In Process')

@section('content')
    <x-plant-header title="Edit Data Checksheet Inprocess" :plant="request('plant')" />
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Data Checksheet Inprocess</h1>
        <a href="{{ route('in_process.index', ['plant' => request('plant')]) }}"
            class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Checksheet Inprocess</h6>
        </div>
        <div class="card-body">
            @include('in_process.partials.edit_form')
        </div>
    </div>
@endsection