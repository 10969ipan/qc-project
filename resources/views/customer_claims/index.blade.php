@extends('layouts.admin')

@section('title', 'Data Claim Customer')

@section('content')
    <x-plant-header title="Data Claim Customer Quality" :plant="request()->get('plant')" />

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Claim Customer</h6>
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                <div class="d-flex align-items-center">

                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                        data-target="#modalTambahData">
                        <i class="fas fa-plus"></i> Tambah Data
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('admin.customer-claims.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    {{-- Preserve plant parameter --}}
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="plant_filter" class="small font-weight-bold">Plant</label>
                            <select name="plant" id="plant_filter" class="form-control form-control-sm">
                                <option value="">Semua Plant</option>
                                @foreach($plants as $p)
                                    <option value="{{ $p->code }}" {{ request('plant') == $p->code ? 'selected' : '' }}>
                                        {{ strtoupper($p->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

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
            @php
                $groupedClaims = $claims->groupBy(function ($item) {
                    return $item->plant->code;
                });
                
                $plantSections = [
                    'karawang' => ['title' => 'Karawang', 'color' => 'success'],
                    'jakarta' => ['title' => 'Jakarta', 'color' => 'info'],
                    'total' => ['title' => 'TOTAL', 'color' => 'dark'],
                ];
            @endphp

            <div class="row">
                @foreach($plantSections as $code => $section)
                    <div class="col-lg-4 col-md-12 mb-4">
                        <div class="card border-left-{{ $section['color'] }} shadow h-100">
                            <div class="card-header py-3 bg-{{ $section['color'] }} text-white">
                                <h6 class="m-0 font-weight-bold text-uppercase">{{ $section['title'] }}</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0" style="font-size: 0.85rem;">
                                        <thead class="bg-light text-dark">
                                            <tr class="text-center">
                                                <th class="py-2">Bulan</th>
                                                <th class="py-2">PPM</th>
                                                <th class="py-2">Target</th>
                                                <th class="py-2">Total</th>
                                                <th class="py-2" width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($groupedClaims[$code] ?? [] as $claim)
                                                <tr class="text-center">
                                                    <td class="align-middle py-2">{{ $claim->month_name }}</td>
                                                    <td class="align-middle py-2 font-weight-bold {{ $code == 'total' ? 'text-dark' : 'text-primary' }}">
                                                        {{ $claim->ppm_value !== null ? number_format($claim->ppm_value, 2) : '-' }}
                                                    </td>
                                                    <td class="align-middle py-2 font-weight-bold text-danger">
                                                        {{ $claim->target_value !== null ? number_format($claim->target_value, 2) : '-' }}
                                                    </td>
                                                    <td class="align-middle py-2 font-weight-bold text-dark">
                                                        {{ $claim->total_claims !== null ? number_format($claim->total_claims, 0) : '-' }}
                                                    </td>
                                                    <td class="align-middle py-2">
                                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-transparent text-primary btn-edit-claim p-0 mr-2" 
                                                                    data-toggle="modal"
                                                                    data-target="#modalEditData" data-id="{{ $claim->id }}"
                                                                    data-plant="{{ $claim->plant_id }}" data-plant-code="{{ $claim->plant->code }}" 
                                                                    data-year="{{ $claim->year }}"
                                                                    data-month="{{ $claim->month }}" data-ppm="{{ $claim->ppm_value }}"
                                                                    data-target-val="{{ $claim->target_value }}" data-total="{{ $claim->total_claims }}" 
                                                                    data-total-claim-pcs="{{ $claim->total_claim_pcs }}"
                                                                    data-total-delivery="{{ $claim->total_delivery }}"
                                                                    title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <form action="{{ route('admin.customer-claims.destroy', $claim->id) }}" method="POST"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                                    <input type="hidden" name="year" value="{{ request('year') }}">
                                                                    <input type="hidden" name="month" value="{{ request('month') }}">
                                                                    <button type="submit" class="btn btn-transparent text-danger p-0"
                                                                        onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
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


    {{-- Modal Tambah Data (Bulk Input) --}}
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
                                    {{-- Row for Month 0 (Summary) --}}
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
                                                class="form-control form-control-sm bg-light" placeholder="0.00" readonly>
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
                                                    class="form-control form-control-sm bg-light" placeholder="0.00" readonly>
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

    <!-- Modal Edit Data -->
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
                    {{-- Preserve filters --}}
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
                                    <input type="number" step="0.01" name="ppm_value" id="edit_ppm_value" class="form-control bg-light" readonly>
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
        $(document).ready(function() {
            function toggleFields(plantId, modalPrefix) {
                // We need to fetch the plant code to decide which fields to show
                // For simplicity, we can embed the plant mapping in a JS object
                const plantMapping = {
                    @foreach($plants as $p)
                        '{{ $p->id }}': '{{ $p->code }}',
                    @endforeach
                };

                const plantCode = plantMapping[plantId];
                if (plantCode === 'total') {
                    $(`.${modalPrefix}-ppm-fields`).hide();
                    $(`.${modalPrefix}-total-fields`).show();
                } else {
                    $(`.${modalPrefix}-ppm-fields`).show();
                    $(`.${modalPrefix}-total-fields`).hide();
                }
            }

            // Calculation Logic
            function calculatePPM(pcs, delivery) {
                if (pcs && delivery && delivery > 0) {
                    return ((pcs / delivery) * 1000000).toFixed(2);
                }
                return '';
            }

            // Calculation for Tambah Data Modal
            $(document).on('input', '.calc-input-summary, [class*="calc-input-"]', function() {
                let month = $(this).data('month');
                let pcsInput, deliveryInput, ppmInput;

                if (month === 'summary') {
                    pcsInput = $('input[name="total_claim_pcs"]');
                    deliveryInput = $('input[name="total_delivery"]');
                    ppmInput = $('#ppm_value_summary');
                } else {
                    pcsInput = $(`input[name="data[${month}][total_claim_pcs]"]`);
                    deliveryInput = $(`input[name="data[${month}][total_delivery]"]`);
                    ppmInput = $(`#ppm_value_${month}`);
                }

                let pcs = parseFloat(pcsInput.val());
                let delivery = parseFloat(deliveryInput.val());

                ppmInput.val(calculatePPM(pcs, delivery));
            });

             // Calculation for Edit Modal
            $(document).on('input', '.calc-input-edit', function() {
                let pcs = parseFloat($('#edit_total_claim_pcs').val());
                let delivery = parseFloat($('#edit_total_delivery').val());
                $('#edit_ppm_value').val(calculatePPM(pcs, delivery));
            });


            // For Tambah Data Modal
            $('#modal_plant_id').on('change', function() {
                toggleFields($(this).val(), 'modal');
            });
            // Initial toggle for Tambah Data
            if ($('#modal_plant_id').val()) {
                toggleFields($('#modal_plant_id').val(), 'modal');
            }

            // Edit Claim Logic
            $('.btn-edit-claim').on('click', function() {
                var id = $(this).data('id');
                var plantId = $(this).data('plant');
                var plantCode = $(this).data('plant-code');
                var year = $(this).data('year');
                var month = $(this).data('month');
                var ppm = $(this).data('ppm');
                var target = $(this).data('target-val');
                var total = $(this).data('total');
                // We need to fetch the new fields if they exist in the row data, 
                // but since they might not be in the table view yet, we might need to rely on what's available or fetch via ajax. 
                // For now, assuming we might need to add data attributes to the button.
                // Assuming the button attributes for new fields are added in the blade loop.
                var pcs = $(this).data('total-claim-pcs');
                var delivery = $(this).data('total-delivery');

                $('#edit_plant_id').val(plantId);
                $('#edit_year').val(year);
                $('#edit_month').val(month);
                $('#edit_ppm_value').val(ppm);
                $('#edit_target_value').val(target);
                $('#edit_total_claims').val(total);
                $('#edit_total_claim_pcs').val(pcs);
                $('#edit_total_delivery').val(delivery);

                toggleFields(plantId, 'edit');

                // Update form action
                var url = "{{ route('admin.customer-claims.update', ':id') }}";
                url = url.replace(':id', id);
                $('#formEditClaim').attr('action', url);
            });

            // Handle plant change in Edit modal
            $('#edit_plant_id').on('change', function() {
                toggleFields($(this).val(), 'edit');
            });
        });
    </script>
@endpush