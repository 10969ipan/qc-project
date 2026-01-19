@extends('layouts.admin')

@section('title', 'Input Claim Customer Per Tahun')

@section('content')
    <x-plant-header title="Input Data Claim Customer" :plant="request()->get('plant')" />

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Form Input - Tahun {{ $year }}</h6>
            <form action="{{ route('admin.customer-claims.yearly') }}" method="GET" class="form-inline">
                @if(request('plant'))
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                @endif
                <label class="mr-2 small font-weight-bold">Ganti Tahun:</label>
                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                    @php $currentY = (int) date('Y'); @endphp
                    @for($y = $currentY + 1; $y >= 2022; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('admin.customer-claims.store-yearly') }}" method="POST">
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                <input type="hidden" name="plant_id" value="{{ $plantId }}">
                <input type="hidden" name="year" value="{{ $year }}">

                {{-- Annual Summary Section (Always Visible) --}}
                <div class="card bg-light border-left-info mb-4">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-info mb-3">
                            <i class="fas fa-chart-line mr-1"></i> Ringkasan / Target Tahunan (Month = 0)
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">PPM Value (Tahunan)</label>
                                    @php
                                        $summaryPpm = '';
                                        if ($existingData instanceof \Illuminate\Database\Eloquent\Model) {
                                            $summaryPpm = $existingData->ppm_value;
                                        } elseif ($existingData instanceof \Illuminate\Support\Collection && $existingData->has(0)) {
                                            $summaryPpm = $existingData->get(0)->ppm_value;
                                        }
                                    @endphp
                                    <input type="number" step="0.01" name="ppm_value" class="form-control"
                                        placeholder="Contoh: 15.50"
                                        value="{{ old('ppm_value', $summaryPpm) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">Target PPM (Tahunan)</label>
                                    @php
                                        $summaryTarget = '';
                                        if ($existingData instanceof \Illuminate\Database\Eloquent\Model) {
                                            $summaryTarget = $existingData->target_value;
                                        } elseif ($existingData instanceof \Illuminate\Support\Collection && $existingData->has(0)) {
                                            $summaryTarget = $existingData->get(0)->target_value;
                                        }
                                    @endphp
                                    <input type="number" step="0.01" name="target_value" class="form-control"
                                        placeholder="Contoh: 5.00"
                                        value="{{ old('target_value', $summaryTarget) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($year >= $currentYear)
                    {{-- Monthly Detail Section (Only for Current/Future Years) --}}
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list mr-1"></i> Detail Bulanan (Januari - Desember)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-center">
                                            <th width="200">Bulan</th>
                                            <th>PPM Value</th>
                                            <th>Target PPM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($months as $num => $name)
                                            @php
                                                $claim = ($existingData instanceof \Illuminate\Support\Collection) ? $existingData->get($num) : null;
                                            @endphp
                                            <tr>
                                                <td class="align-middle font-weight-bold text-gray-800">{{ $name }}</td>
                                                <td>
                                                    <input type="number" step="0.01" name="data[{{ $num }}][ppm_value]"
                                                        class="form-control" placeholder="0.00"
                                                        value="{{ old("data.$num.ppm_value", $claim ? $claim->ppm_value : '') }}">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="data[{{ $num }}][target_value]"
                                                        class="form-control" placeholder="0.00"
                                                        value="{{ old("data.$num.target_value", $claim ? $claim->target_value : '') }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-group mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                        <i class="fas fa-save mr-2"></i> Simpan Data Tahun {{ $year }}
                    </button>
                    <a href="{{ route('admin.customer-claims.index', ['plant' => request('plant'), 'year' => $year]) }}"
                        class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection