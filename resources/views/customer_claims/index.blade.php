@extends('layouts.admin')

@section('title', 'Data Claim Customer')

@section('content')
    <x-plant-header title="Data Claim Customer Quality Department" :plant="request()->get('plant')" />

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Claim Customer</h6>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.customer-claims.yearly', ['plant' => request('plant')]) }}"
                    class="btn btn-info btn-sm mr-2">
                    <i class="fas fa-calendar-alt"></i> Input Per Tahun
                </a>
                <a href="{{ route('admin.customer-claims.create', ['plant' => request('plant')]) }}"
                    class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Data
                </a>
            </div>
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('admin.customer-claims.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    {{-- Preserve plant parameter --}}
                    @if(request('plant'))
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif

                    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="year" class="small font-weight-bold">Tahun</label>
                            <select name="year" id="year" class="form-control form-control-sm">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="month" class="small font-weight-bold">Bulan</label>
                            <select name="month" id="month" class="form-control form-control-sm">
                                <option value="">Semua Bulan</option>
                                <option value="0" {{ request('month') === '0' ? 'selected' : '' }}>Tahunan (Summary)</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            <a href="{{ route('admin.customer-claims.index', ['plant' => request('plant')]) }}"
                                class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Plant</th>
                            <th>Tahun</th>
                            <th>Bulan</th>
                            <th>PPM Value</th>
                            <th>Target</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                            <tr class="text-center">
                                <td class="align-middle">{{ $claims->firstItem() + $loop->index }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $claim->plant->code === 'jakarta' ? 'info' : 'success' }}">
                                        {{ $claim->plant->name }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $claim->year }}</td>
                                <td class="align-middle">{{ $claim->month_name }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-primary">{{ number_format($claim->ppm_value, 2) }}</span>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-danger">{{ number_format($claim->target_value, 2) }}</span>
                                </td>
                                <td class="align-middle">{{ $claim->creator->name ?? '-' }}</td>
                                <td class="align-middle text-nowrap">
                                    <a href="{{ route('admin.customer-claims.edit', ['customer_claim' => $claim->id, 'plant' => request('plant'), 'year' => request('year'), 'month' => request('month')]) }}"
                                        class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.customer-claims.destroy', $claim->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                                        <input type="hidden" name="year" value="{{ request('year') }}">
                                        <input type="hidden" name="month" value="{{ request('month') }}">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $claims->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection