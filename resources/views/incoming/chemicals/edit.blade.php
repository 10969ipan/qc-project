@extends('layouts.admin')

@section('title', 'Edit Incoming Chemical')

@section('content')
    <div class="container-fluid">
            @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_chemicals', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
    <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Data Incoming Chemical</h6>
                <a href="{{ route('incoming.chemicals.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @include('incoming.chemicals.partials.edit_form')
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/checksheet/incoming-edit.js') }}"></script>
@endpush
