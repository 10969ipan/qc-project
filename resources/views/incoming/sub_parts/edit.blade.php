@extends('layouts.admin')

@section('title', 'Edit Incoming Sub-Part')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Data Incoming Sub-Part</h6>
                <a href="{{ route('incoming.sub_parts.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @include('incoming.sub_parts.partials.edit_form')
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/checksheet/incoming-edit.js') }}"></script>
@endpush