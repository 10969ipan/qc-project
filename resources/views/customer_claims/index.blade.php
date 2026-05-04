@extends('layouts.admin')

@section('title', 'Customer Claim')

@section('content')
    @php
        $currentPlant = request('plant') ? collect($plants)->firstWhere('code', request('plant')) : null;
    @endphp

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:1.15rem; letter-spacing:0.3px;">
                            DATA CLAIM CUSTOMER QUALITY 
                        </h1>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-2 d-flex justify-content-between align-items-center bg-white border-0">
            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem; letter-spacing: 0.3px;">
                DAFTAR DATA CLAIM CUSTOMER
            </h6>
        </div>
        <div class="card-body py-3">
            <form action="{{ route('admin.customer-claims.index') }}" method="GET"
                class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 10px;">
                
                <!-- Field: Plant -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700 text-nowrap">Plant:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <select name="plant" id="plant_filter" class="form-control form-control-sm border-0" style="font-size: 0.75rem; min-width: 120px;">
                            <option value="">Semua Plant</option>
                            @foreach($plants as $p)
                                <option value="{{ $p->code }}" {{ request('plant') == $p->code ? 'selected' : '' }}>
                                    {{ strtoupper($p->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Field: Tahun -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700 text-nowrap">Tahun:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <select name="year" id="year" class="form-control form-control-sm border-0" style="font-size: 0.75rem; min-width: 100px;">
                            <option value="">Semua Tahun</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Field: Bulan -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700 text-nowrap">Bulan:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <select name="month" id="month" class="form-control form-control-sm border-0" style="font-size: 0.75rem; min-width: 120px;">
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

                <!-- Tombol Aksi -->
                <div class="ml-auto d-flex" style="gap: 5px;">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data">
                        <i class="fas fa-search fa-sm mr-1"></i>
                    </button>
                    <a href="{{ route('admin.customer-claims.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                        <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal"
                            data-target="#modalTambahData" title="Tambah Data">
                            <i class="fas fa-plus fa-sm mr-1"></i> Tambah Data
                        </button>
                    @endif
                </div>
            </form>

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

            @php
                $groupedClaims = $claims->groupBy(function ($item) {
                    return $item->plant->code;
                });
                
                $plantSections = [
                    'karawang' => ['title' => 'Karawang'],
                    'jakarta' => ['title' => 'Jakarta'],
                    'total' => ['title' => 'TOTAL'],
                ];
            @endphp
            
            <style>
                .custom-table-wrapper {
                    max-height: 75vh !important;
                    overflow: auto !important;
                    border: none !important;
                    box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
                }
                .clean-table {
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                    border: 1px solid #e2e8f0 !important;
                    width: 100% !important;
                }
                .clean-table td, .clean-table th {
                    border: 1px solid #e2e8f0 !important;
                }
                .clean-table tbody td {
                    vertical-align: middle !important;
                    color: #334155 !important;
                    padding: 6px 8px !important;
                }
                .custom-table-wrapper .clean-table thead th,
                .custom-table-wrapper table.clean-table thead th {
                    position: -webkit-sticky !important;
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 100 !important;
                    background-color: #f8fafc !important; 
                    color: #475569 !important;
                    font-weight: 700 !important;
                    text-transform: uppercase;
                    font-size: 0.65rem !important;
                    letter-spacing: 0.5px;
                    padding: 10px 8px !important;
                    border: 1px solid #e2e8f0 !important;
                    vertical-align: middle !important;
                    white-space: nowrap !important;
                }
                .btn-xs { 
                    padding: 0.2rem 0.4rem !important; 
                    font-size: 0.65rem !important;
                }
                .plant-header {
                    background-color: #f8fafc !important;
                    border-bottom: 1px solid #e2e8f0;
                }
            </style>

            <div class="row">
                @foreach($plantSections as $code => $section)
                    <div class="col-lg-4 col-md-12 mb-4">
                        <div class="card shadow-sm h-100 border text-center" style="border-radius: 8px; overflow: hidden;">
                            <div class="card-header py-2 border-0 plant-header">
                                <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem; letter-spacing: 0.3px;">
                                    {{ $section['title'] }}
                                </h6>
                            </div>
                            <div class="card-body p-0 bg-white">
                                <div class="custom-table-wrapper">
                                    <table class="table clean-table mb-0">
                                        <thead>
                                            <tr class="text-center">
                                                <th>Bulan</th>
                                                <th>PPM</th>
                                                <th>Target</th>
                                                <th>Total</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($groupedClaims[$code] ?? [] as $claim)
                                                <tr class="text-center">
                                                    <td class="align-middle" style="font-size: 0.8rem;">{{ $claim->month_name }}</td>
                                                    <td class="align-middle font-weight-bold {{ $code == 'total' ? 'text-dark' : 'text-primary' }}" style="font-size: 0.8rem;">
                                                        {{ number_format($claim->ppm_value ?? 0, 2) }}
                                                    </td>
                                                    <td class="align-middle font-weight-bold text-danger" style="font-size: 0.8rem;">
                                                        {{ number_format($claim->target_value ?? 0, 2) }}
                                                    </td>
                                                    <td class="align-middle font-weight-bold text-dark" style="font-size: 0.8rem;">
                                                        {{ number_format($claim->total_claims ?? 0, 0) }}
                                                    </td>
                                                    <td class="align-middle">
                                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                                            <div class="d-flex justify-content-center" style="gap: 5px;">
                                                                <button type="button" class="btn btn-warning btn-xs py-0 btn-edit-claim" 
                                                                    data-toggle="modal"
                                                                    data-target="#modalEditData" data-id="{{ $claim->id }}"
                                                                    data-plant="{{ $claim->plant_id }}" data-plant-code="{{ $claim->plant->code }}" 
                                                                    data-year="{{ $claim->year }}"
                                                                    data-month="{{ $claim->month }}" data-ppm="{{ $claim->ppm_value }}"
                                                                    data-target-val="{{ $claim->target_value }}" data-total="{{ $claim->total_claims }}" 
                                                                    data-total-claim-pcs="{{ $claim->total_claim_pcs }}"
                                                                    data-total-delivery="{{ $claim->total_delivery }}"
                                                                    title="Edit">
                                                                    <i class="fas fa-edit fa-xs"></i>
                                                                </button>
                                                                <form action="{{ route('admin.customer-claims.destroy', $claim->id) }}" method="POST"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                                    <input type="hidden" name="year" value="{{ request('year') }}">
                                                                    <input type="hidden" name="month" value="{{ request('month') }}">
                                                                    <button type="submit" class="btn btn-danger btn-xs py-0"
                                                                        onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                                                                        <i class="fas fa-trash fa-xs"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3 small">
                                                        Tidak ada data
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahData" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahDataLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Data Claim Customer
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.customer-claims.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle mr-1"></i> Form ini akan menyimpan data untuk plant yang dipilih.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-0">
                                    <label for="modal_plant_id" class="font-weight-bold small">Plant <span
                                            class="text-danger">*</span></label>
                                    <select name="plant_id" id="modal_plant_id" class="form-control form-control-sm"
                                        required>
                                        <option value="">Pilih Plant</option>
                                        @foreach($plants as $plant)
                                            <option value="{{ $plant->id }}" {{ $plantId == $plant->id ? 'selected' : '' }}>
                                                {{ $plant->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-0">
                                    <label for="modal_year" class="font-weight-bold small">Tahun <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="year" id="modal_year" class="form-control form-control-sm"
                                        value="{{ $currentYear }}" min="2020" max="2100" required>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="bg-light">
                                    <tr class="text-center small">
                                        <th width="150">Bulan</th>
                                        <th class="modal-ppm-fields">Total Claim (pcs)</th>
                                        <th class="modal-ppm-fields">Total Delivery</th>
                                        <th class="modal-ppm-fields">PPM Value</th>
                                        <th class="modal-ppm-fields">Target PPM</th>
                                        <th class="modal-total-fields" style="display: none;">Total Claim</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-light font-weight-bold">
                                        <td class="align-middle text-info pl-3">TAHUNAN (0)</td>
                                        <td class="modal-ppm-fields">
                                             <input type="number" step="0.01" name="total_claim_pcs"
                                                class="form-control form-control-sm calc-input-summary" data-month="summary" placeholder="0">
                                        </td>
                                        <td class="modal-ppm-fields">
                                             <input type="number" step="0.01" name="total_delivery"
                                                class="form-control form-control-sm calc-input-summary" data-month="summary" placeholder="0">
                                        </td>
                                        <td class="modal-ppm-fields">
                                             <input type="number" step="0.01" name="ppm_value" id="ppm_value_summary"
                                                 class="form-control form-control-sm bg-light" value="0.00" placeholder="0.00" readonly>
                                         </td>
                                        <td class="modal-ppm-fields">
                                            <input type="number" step="0.01" name="target_value"
                                                class="form-control form-control-sm" placeholder="0.00">
                                        </td>
                                        <td class="modal-total-fields" style="display: none;">
                                            <input type="number" step="0.01" name="total_claims"
                                                class="form-control form-control-sm" placeholder="0">
                                        </td>
                                    </tr>
                                    @foreach($months as $num => $name)
                                        <tr class="small">
                                            <td class="align-middle pl-3">{{ $name }}</td>
                                            <td class="modal-ppm-fields">
                                                <input type="number" step="0.01" name="data[{{ $num }}][total_claim_pcs]"
                                                    class="form-control form-control-sm calc-input-{{ $num }}" data-month="{{ $num }}" placeholder="0">
                                            </td>
                                            <td class="modal-ppm-fields">
                                                <input type="number" step="0.01" name="data[{{ $num }}][total_delivery]"
                                                    class="form-control form-control-sm calc-input-{{ $num }}" data-month="{{ $num }}" placeholder="0">
                                            </td>
                                            <td class="modal-ppm-fields">
                                                 <input type="number" step="0.01" name="data[{{ $num }}][ppm_value]" id="ppm_value_{{ $num }}"
                                                     class="form-control form-control-sm bg-light" value="0.00" placeholder="0.00" readonly>
                                             </td>
                                            <td class="modal-ppm-fields">
                                                <input type="number" step="0.01" name="data[{{ $num }}][target_value]"
                                                    class="form-control form-control-sm" placeholder="0.00">
                                            </td>
                                            <td class="modal-total-fields" style="display: none;">
                                                <input type="number" step="0.01" name="data[{{ $num }}][total_claims]"
                                                    class="form-control form-control-sm" placeholder="0">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save mr-1"></i> Simpan Semua
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditData" tabindex="-1" role="dialog" aria-labelledby="modalEditDataLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditDataLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Data Customer Claim
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditClaim" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="filter_plant" value="{{ request('plant') }}">
                    <input type="hidden" name="filter_year" value="{{ request('year') }}">
                    <input type="hidden" name="filter_month" value="{{ request('month') }}">

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Plant <span class="text-danger">*</span></label>
                            <select name="plant_id" id="edit_plant_id" class="form-control" required>
                                @foreach($plants as $p)
                                    <option value="{{ $p->id }}">{{ strtoupper($p->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Tahun <span class="text-danger">*</span></label>
                                    <input type="number" name="year" id="edit_year" class="form-control" min="2000"
                                        max="2100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Bulan <span class="text-danger">*</span></label>
                                    <select name="month" id="edit_month" class="form-control" required>
                                        @foreach($months as $num => $name)
                                            <option value="{{ $num }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row edit-ppm-fields">
                             <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Total Claim (pcs)</label>
                                    <input type="number" step="0.01" name="total_claim_pcs" id="edit_total_claim_pcs" class="form-control calc-input-edit">
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Total Delivery</label>
                                    <input type="number" step="0.01" name="total_delivery" id="edit_total_delivery" class="form-control calc-input-edit">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">PPM Value <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="ppm_value" id="edit_ppm_value" class="form-control bg-light" value="0.00" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Target PPM <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="target_value" id="edit_target_value" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0 edit-total-fields" style="display: none;">
                            <label class="font-weight-bold">Total Claim <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="total_claims" id="edit_total_claims" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm px-4">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.__CUSTOMER_CLAIMS__ = {
            routes: {
                update: "{{ route('admin.customer-claims.update', ':id') }}"
            }
        };
    </script>
    <script src="{{ asset('js/customer-claims/customer-claims.js') }}"></script>
@endpush
