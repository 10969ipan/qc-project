@extends('layouts.admin')

@section('title', 'Input Data Incoming Material')

@push('styles')
<style>
    #checksheetTable th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f8f9fc; }
    #checksheetTable td { font-size: 0.85rem; }
    .ok-label { background-color: #28a745; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
    .ng-label { background-color: #dc3545; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
    .form-control-sm.text-center { font-weight: bold; border-color: #d1d3e2; }
    .form-control-sm.text-center:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
    #judgmentBadge { min-width: 80px; min-height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_materials', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0211',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                            CHECK SHEET INCOMING MATERIAL 
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Material</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.materials.store') }}" method="POST" id="checksheetForm" novalidate>
                @csrf
                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <thead class="bg-light text-center small font-weight-bold">
                            <tr>
                                <th rowspan="2">Material Name</th>
                                <th rowspan="2">Tgl Datang</th>
                                <th rowspan="2">Expired Date</th>
                                <th rowspan="2">Tanggal Check</th>
                                <th rowspan="2">Lot/Batch Number</th>
                                <th colspan="3">Quantity Details (Kg)</th>
                                <th rowspan="2" style="min-width: 200px;">Detail NG</th>
                                <th rowspan="2">Judgment</th>
                                <th rowspan="2">QC</th>
                                <th rowspan="2">Remarks</th>
                            </tr>
                            <tr>
                                <th>Qty (Kg)</th>
                                <th>Komper/Karung</th>
                                <th>Sampling Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Material Name -->
                                <td>
                                    <select class="form-control select2" name="item_id" id="itemSelect" required
                                        style="min-width: 200px;">
                                        <option value="">-- Pilih Material --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" 
                                                data-defects="{{ json_encode($item->defects) }}"
                                                data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Tanggal Datang -->
                                <td>
                                    <input type="date" class="form-control" name="tanggal_datang" required>
                                </td>

                                <!-- Expired Date -->
                                <td>
                                    <input type="date" class="form-control" name="expired_date" required>
                                </td>

                                <!-- Tanggal Check -->
                                <td>
                                    <input type="date" class="form-control" name="date" value="{{ $defaultDate }}" required>
                                </td>

                                <!-- Lot Number -->
                                <td>
                                    <input type="text" class="form-control" name="lot_batch_number" placeholder="Lot #"
                                        required>
                                </td>

                                <!-- Quantity Details -->
                                <td>
                                    <input type="number" step="any" class="form-control text-center" name="quantity_kg"
                                        id="lotQtyInput" placeholder="0" required style="min-width: 120px;">
                                </td>
                                <td>
                                    <input type="number" step="any" class="form-control text-center"
                                        name="komper_karung_kg" id="komperKarungInput" placeholder="0" required style="min-width: 120px;">
                                </td>
                                <td>
                                    <input type="number" step="any" class="form-control text-center"
                                        name="sampling_size_karung_kg" id="totalCheckInput" placeholder="0" required style="min-width: 120px;">
                                </td>

                                <!-- Defect Details -->
                                <td class="align-middle" style="min-width: 280px;">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="row no-gutters mb-2 defect-row align-items-center">
                                            <div class="col-7 pr-1">
                                                <select class="form-control defect-select font-weight-bold"
                                                    name="defect_types[]" id="defectSelect">
                                                    <option value="">-- Pilih Defect --</option>
                                                </select>
                                            </div>
                                            <div class="col-3 pr-1">
                                                <input type="number" class="form-control defect-qty text-center font-weight-bold"
                                                    name="defect_quantities[]" placeholder="Qty" min="1">
                                            </div>
                                            <div class="col-2 text-center action-col">
                                                <button type="button" id="addDefectBtn" class="btn btn-primary btn-sm shadow-sm" style="display: none;" title="Tambah Jenis">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Result -->
                                <td class="align-middle text-center" style="min-width: 150px;">
                                    <div id="judgmentBadge" class="mb-2 p-3 font-weight-bold h4 rounded d-none shadow-sm"
                                        style="border: 2px solid transparent;">
                                        -
                                    </div>
                                    <select class="form-control font-weight-bold d-none" name="judgment" id="judgmentSelect"
                                        required style="min-width: 100px;">
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                    <input type="hidden" name="total_ng" id="totalNgInput" value="0">
                                    <div id="aql_info" class="small mt-1 font-weight-bold text-center"
                                        style="display:none;">
                                        <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                    </div>
                                </td>

                                <!-- QC -->
                                <td>
                                    <input type="text" class="form-control text-center" name="operator_initials"
                                        value="{{ auth()->user()->initials ?? '' }}" required style="min-width: 60px;">
                                </td>

                                <!-- Remarks -->
                                <td>
                                    <textarea class="form-control" name="remarks" rows="2" placeholder="..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-right d-flex justify-content-end align-items-center">
                        <h5 class="mr-3 mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                        <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">

                        <button type="button" class="btn btn-success mr-3" id="startTimerBtn">
                            <i class="fas fa-play"></i> Start
                        </button>
                        <button type="submit" class="btn btn-primary px-5" id="saveBtn" disabled>
                            <i class="fas fa-save mr-1"></i> SIMPAN DATA
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bagian Tampilan PDF -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">STANDARD PDF</h6>
                        <div class="d-flex align-items-center">
                            <!-- Kontrol Zoom -->
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutStandard"
                                    title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetStandard"
                                    title="Reset Zoom">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInStandard"
                                    title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardPage"
                                    title="Next Page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Area Tampilan Canvas PDF -->
                    <div class="border rounded bg-dark d-flex justify-content-center align-items-center"
                        style="height: 500px; overflow: auto; position: relative;">
                        <!-- Loading Indicator -->
                        <div id="standardPdfLoading" class="position-absolute w-100 h-100 d-none justify-content-center align-items-center bg-dark" style="z-index: 10; opacity: 0.8;">
                            <div class="text-center text-white">
                                <div class="spinner-border mb-2" role="status"></div>
                                <p class="mb-0 small">Memuat PDF...</p>
                            </div>
                        </div>
                        <!-- Canvas -->
                        <canvas id="standardPdfCanvas" class="shadow-sm d-none" style="direction: ltr;"></canvas>
                        <!-- Placeholder (Kosong) -->
                        <div id="standardPdfPlaceholder" class="text-center text-secondary d-flex flex-column justify-content-center align-items-center w-100 h-100">
                            <i class="fas fa-file-pdf fa-3x mb-2 opacity-50"></i>
                            <p class="mb-0 small">Pilih Item untuk menampilkan Standard PDF</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script>
        window.pdfWorkerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";
        window.pdfUrlPattern = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}";
    </script>
    <script src="{{ asset('js/checksheet/incoming-create.js') }}"></script>
@endpush
