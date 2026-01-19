@extends('layouts.admin')

@section('title', 'Edit Data Claim Customer')

@section('content')
    <x-plant-header title="Edit Data Claim Customer" :plant="$customerClaim->plant" />

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Data Claim Customer</h6>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('admin.customer-claims.update', $customerClaim->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                <input type="hidden" name="filter_year" value="{{ request('year') }}">
                <input type="hidden" name="filter_month" value="{{ request('month') }}">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="plant_id" class="font-weight-bold">Plant <span class="text-danger">*</span></label>
                            <select name="plant_id" id="plant_id"
                                class="form-control @error('plant_id') is-invalid @enderror" required>
                                <option value="">Pilih Plant</option>
                                @foreach($plants as $plant)
                                    <option value="{{ $plant->id }}" {{ old('plant_id', $customerClaim->plant_id) == $plant->id ? 'selected' : '' }}>
                                        {{ $plant->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="year" class="font-weight-bold">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="year" id="year"
                                class="form-control @error('year') is-invalid @enderror"
                                value="{{ old('year', $customerClaim->year) }}" min="2020" max="2100" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="month" class="font-weight-bold">Bulan <span class="text-danger">*</span></label>
                            @if($customerClaim->month == 0)
                                <input type="text" class="form-control" value="Tahunan" readonly>
                                <input type="hidden" name="month" value="0">
                            @else
                                <select name="month" id="month" class="form-control @error('month') is-invalid @enderror"
                                    required>
                                    <option value="">Pilih Bulan</option>
                                    @php
                                        $months = [
                                            1 => 'Januari',
                                            2 => 'Februari',
                                            3 => 'Maret',
                                            4 => 'April',
                                            5 => 'Mei',
                                            6 => 'Juni',
                                            7 => 'Juli',
                                            8 => 'Agustus',
                                            9 => 'September',
                                            10 => 'Oktober',
                                            11 => 'November',
                                            12 => 'Desember'
                                        ];
                                    @endphp
                                    @foreach($months as $num => $name)
                                        <option value="{{ $num }}" {{ old('month', $customerClaim->month) == $num ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ppm_value" class="font-weight-bold">PPM Value <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="ppm_value" id="ppm_value"
                                class="form-control @error('ppm_value') is-invalid @enderror"
                                value="{{ old('ppm_value', $customerClaim->ppm_value) }}" min="0" required>
                            @error('ppm_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Nilai PPM claim customer</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="target_value" class="font-weight-bold">Target <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="target_value" id="target_value"
                                class="form-control @error('target_value') is-invalid @enderror"
                                value="{{ old('target_value', $customerClaim->target_value) }}" min="0" required>
                            @error('target_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Target PPM yang ingin dicapai</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('admin.customer-claims.index', ['plant' => request('plant'), 'year' => request('year'), 'month' => request('month')]) }}"
                        class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection