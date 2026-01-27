@extends('layouts.admin')

@section('title', 'Input Claim Customer Per Tahun')

@section('content')
    <x-plant-header title="Input Data Claim Customer" :plant="request()->get('plant')" />

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Form Input - Tahun {{ $year }}</h6>
            <div class="d-flex align-items-center">
                <form action="{{ route('admin.customer-claims.yearly') }}" method="GET" class="form-inline mr-3">
                    <label class="mr-2 small font-weight-bold">Plant:</label>
                    <select name="plant" class="form-control form-control-sm" onchange="this.form.submit()">
                        @foreach($plants as $p)
                            <option value="{{ $p->code }}" {{ request('plant') == $p->code ? 'selected' : '' }}>
                                {{ strtoupper($p->name) }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="year" value="{{ $year }}">
                </form>
                <form action="{{ route('admin.customer-claims.yearly') }}" method="GET" class="form-inline">
                    @if(request('plant'))
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif
                    <label class="mr-2 small font-weight-bold">Tahun:</label>
                    <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                        @php $currentY = (int) date('Y'); @endphp
                        @for($y = $currentY + 1; $y >= 2022; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
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
                            @if(request('plant') === 'total')
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold">Total Claim (Tahunan)</label>
                                        @php
                                            $summaryTotal = '';
                                            if ($existingData instanceof \Illuminate\Database\Eloquent\Model) {
                                                $summaryTotal = $existingData->total_claims;
                                            } elseif ($existingData instanceof \Illuminate\Support\Collection && $existingData->has(0)) {
                                                $summaryTotal = $existingData->get(0)->total_claims;
                                            }
                                        @endphp
                                        <input type="number" step="0.01" name="total_claims" class="form-control"
                                            placeholder="0" value="{{ old('total_claims', $summaryTotal) }}">
                                    </div>
                                </div>
                                <input type="hidden" name="ppm_value" value="0">
                                <input type="hidden" name="target_value" value="0">
                            @else
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
                                            placeholder="Contoh: 15.50" value="{{ old('ppm_value', $summaryPpm) }}">
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
                                            placeholder="Contoh: 5.00" value="{{ old('target_value', $summaryTarget) }}">
                                    </div>
                                </div>
                                <input type="hidden" name="total_claims" value="0">
                            @endif
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
                                            @if(request('plant') === 'total')
                                                <th>Total Claim</th>
                                            @else
                                                <th>PPM Value</th>
                                                <th>Target PPM</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($months as $num => $name)
                                            @php
                                                $claim = ($existingData instanceof \Illuminate\Support\Collection) ? $existingData->get($num) : null;
                                            @endphp
                                            <tr>
                                                <td class="align-middle font-weight-bold text-gray-800">{{ $name }}</td>
                                                @if(request('plant') === 'total')
                                                    <td>
                                                        <input type="number" step="0.01" name="data[{{ $num }}][total_claims]"
                                                            class="form-control" placeholder="0"
                                                            value="{{ old("data.$num.total_claims", $claim ? $claim->total_claims : '') }}">
                                                        <input type="hidden" name="data[{{ $num }}][ppm_value]" value="0">
                                                        <input type="hidden" name="data[{{ $num }}][target_value]" value="0">
                                                    </td>
                                                @else
                                                    <td>
                                                        <input type="number" step="0.01" name="data[{{ $num }}][ppm_value]"
                                                            class="form-control" placeholder="0.00"
                                                            value="{{ old("data.$num.ppm_value", $claim ? $claim->ppm_value : '') }}">
                                                        <input type="hidden" name="data[{{ $num }}][total_claims]" value="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="data[{{ $num }}][target_value]"
                                                            class="form-control" placeholder="0.00"
                                                            value="{{ old("data.$num.target_value", $claim ? $claim->target_value : '') }}">
                                                    </td>
                                                @endif
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