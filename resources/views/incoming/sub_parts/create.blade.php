@extends('layouts.admin')

@section('title', 'Input Data Incoming Sub-Part')

@push('styles')
<style>
    #checksheetTable th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f8f9fc; }
    #checksheetTable td { font-size: 0.85rem; }
    .ok-label { background-color: #28a745; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
    .ng-label { background-color: #dc3545; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
    #judgmentBadge { min-width: 80px; min-height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    
    /* Dimension styling */
    #dimensionTable th { background-color: #f1f5f9; color: #475569; font-size: 0.65rem; padding: 6px; }
    #dimensionTable td { padding: 4px; }
    .dimension-input { font-size: 0.85rem; border: 1.5px solid #cbd5e1; background: #fff; text-align: center; border-radius: 4px; width: 100%; min-width: 50px; padding: 6px 8px; height: 38px; }
    .dimension-input:focus { border-color: #4e73df; outline: none; box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.25); }

    /* Form Inputs Overrides - "Besar & Pas" */
    #checksheetForm .form-control,
    #checksheetForm input[type="text"],
    #checksheetForm input[type="number"],
    #checksheetForm input[type="date"],
    #checksheetForm select.form-control {
        height: 42px !important;
        font-size: 0.925rem !important;
        font-weight: 500 !important;
        padding: 0.45rem 0.85rem !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 0.4rem !important;
        background-color: #ffffff !important;
        color: #1e293b !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }

    #checksheetForm textarea.form-control {
        height: auto !important;
        min-height: 80px !important;
        font-size: 0.9rem !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 0.4rem !important;
        background-color: #ffffff !important;
        padding: 0.5rem 0.85rem !important;
    }

    #checksheetForm .form-control:focus,
    #checksheetForm input:focus,
    #checksheetForm select:focus,
    #checksheetForm textarea:focus {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25) !important;
        background-color: #ffffff !important;
        outline: none !important;
    }

    /* Select2 Container Overrides */
    #checksheetForm .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 0.4rem !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    }

    #checksheetForm .select2-container--default .select2-selection--single .select2-selection__rendered {
        font-size: 0.925rem !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        line-height: 40px !important;
        padding-left: 0.85rem !important;
        padding-right: 1.5rem !important;
    }

    #checksheetForm .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }

    #checksheetForm label {
        font-size: 0.825rem !important;
        font-weight: 700 !important;
        color: #334155 !important;
        margin-bottom: 0.35rem !important;
    }

    #checksheetForm .defect-select,
    #checksheetForm .defect-qty {
        height: 42px !important;
        font-size: 0.9rem !important;
    }
</style>
@endpush

@section('content')
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_sub_parts', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0212',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp

    <!-- Form Input Data (Full Width di Atas) -->
    <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                            <tr>
                                <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                                    <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                                </td>
                                <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                                    <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                        CHECK SHEET INCOMING SUB-PART
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
                    <form action="{{ route('incoming.sub_parts.store') }}" method="POST" id="checksheetForm" novalidate>
                        @csrf
                        <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                        <div class="table-responsive" style="overflow-x: auto; border: none; box-shadow: inset 0 0 5px rgba(0,0,0,0.02);">
                            <table class="table" id="checksheetTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr class="text-center">
                                        <th rowspan="2" class="align-middle" style="min-width: 280px;">Sub-Part Name</th>
                                        <th rowspan="2" class="align-middle" style="min-width: 150px;">Tanggal Datang &amp; Check</th>
                                        <th rowspan="2" class="align-middle" style="min-width: 130px;">Lot/Batch Number</th>
                                        <th rowspan="2" class="align-middle" style="min-width: 140px;">Kuantitas (Pcs)</th>
                                        <th rowspan="2" class="align-middle" style="min-width: 220px;">Check Dimensi</th>
                                        <th rowspan="2" class="align-middle" style="min-width: 280px;">Jenis (OK/NG) &amp; Detail NG</th>
                                        <th rowspan="2" class="align-middle">Judgment</th>
                                        <th rowspan="2" class="align-middle">Inisial QC</th>
                                        <th rowspan="2" class="align-middle" style="min-width: 180px;">Remarks</th>
                                    </tr>
                                    <tr></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <!-- 1. Sub-Part Name -->
                                        <td class="align-middle">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold mb-1">Sub-Part Name</label>
                                                <select class="form-control select2" name="item_id" id="itemSelect" required style="width: 100%; min-width: 260px;">
                                                    <option value="" disabled selected style="font-weight: bold; color: #6c757d;">-- Pilih Sub-Part --</option>
                                                    @foreach($items as $item)
                                                        <option value="{{ $item->id }}" 
                                                            data-part-number="{{ $item->part_number ?? '' }}"
                                                            data-defects="{{ json_encode($item->defects) }}"
                                                            data-dimension-standards="{{ json_encode($item->dimension_standards ?? []) }}"
                                                            data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                            data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                            data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                            data-similar="{{ $item->similar_part_file_path ? route('items.pdf', ['id' => $item->id, 'index' => 'similar']) : '' }}">
                                                            {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>

                                        <!-- 2. Tanggal Datang & Check -->
                                        <td class="align-middle">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold mb-1">Tgl Datang</label>
                                                <input type="date" class="form-control" style="min-width: 120px;" name="tanggal_datang" required>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold mb-1">Tgl Check</label>
                                                <input type="date" class="form-control" style="min-width: 120px;" name="date" value="{{ $defaultDate }}" required>
                                            </div>
                                        </td>

                                        <!-- 3. Lot/Batch Number -->
                                        <td class="align-middle">
                                            <label class="font-weight-bold mb-1">Lot / Batch #</label>
                                            <input type="text" class="form-control" name="lot_batch_number" placeholder="Lot #" required>
                                        </td>

                                        <!-- 4. Kuantitas -->
                                        <td class="align-middle">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold mb-1">Qty/Lot</label>
                                                <input type="number" step="any" class="form-control text-center font-weight-bold" name="quantity" id="lotQtyInput" placeholder="0" required>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold mb-1">Sampling</label>
                                                <input type="number" step="any" class="form-control text-center font-weight-bold" name="sampling_size_pcs" id="totalCheckInput" placeholder="0" required>
                                            </div>
                                        </td>

                                        <!-- 5. Check Dimensi -->
                                        <td class="align-middle">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="font-weight-bold mb-0">Dimensi:</label>
                                                <button type="button" class="btn btn-xs btn-success shadow-sm" id="addPointRowBtn" title="Tambah Point">
                                                    <i class="fas fa-plus"></i> Point
                                                </button>
                                            </div>
                                            <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                                                <table class="table table-bordered table-sm mb-0 bg-white" id="dimensionTable">
                                                    <thead class="bg-light text-dark small text-center sticky-top">
                                                        <tr>
                                                            <th style="background-color: #f1f5f9 !important;">Point</th>
                                                            <th style="background-color: #f1f5f9 !important;">Hasil Ukur</th>
                                                            <th style="width: 25px; background-color: #f1f5f9 !important;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="dimensionBody">
                                                        @for ($j = 1; $j <= 1; $j++)
                                                            <tr class="point-row">
                                                                <td class="text-center font-weight-bold bg-light align-middle point-label" style="font-size:0.7rem;">P{{ $j }}</td>
                                                                <td class="point-cell p-1">
                                                                    <input type="text" class="dimension-input form-control border-0 shadow-sm w-100 text-center" name="dimensions[]" placeholder="...">
                                                                </td>
                                                                <td class="text-center align-middle p-1">
                                                                    <button type="button" class="btn btn-xs btn-danger shadow-sm delete-point-row" title="Hapus Point">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endfor
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>

                                        <!-- 6. Detail NG -->
                                        <td class="align-middle">
                                            <label class="font-weight-bold d-block mb-1">Jenis (OK/NG) &amp; Detail NG:</label>
                                            <div id="defectContainer">
                                                <div class="row no-gutters mb-2 defect-row align-items-center">
                                                    <div class="col-7 pr-1">
                                                        <select class="form-control defect-select font-weight-bold" name="defect_types[]" id="defectSelect">
                                                            <option value="">-- Pilih Defect --</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-3 pr-1">
                                                        <input type="number" class="form-control defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">
                                                    </div>
                                                    <div class="col-2 text-center action-col">
                                                        <button type="button" id="addDefectBtn" class="btn btn-primary btn-sm shadow-sm" style="display: none;" title="Tambah Jenis">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- 7. Judgment -->
                                        <td class="align-middle text-center">
                                            <div id="judgmentBadge" class="mb-2 p-2 font-weight-bold h5 rounded d-none shadow-sm text-center" style="border: 2px solid transparent;">-</div>
                                            <select class="form-control font-weight-bold d-none" name="judgment" id="judgmentSelect" required>
                                                <option value="" disabled selected>-- Result --</option>
                                                <option value="OK" class="text-success">OK</option>
                                                <option value="NG" class="text-danger">NG</option>
                                            </select>
                                            <input type="hidden" name="total_ng" id="totalNgInput" value="0">
                                            <div id="aql_info" class="small mt-1 font-weight-bold text-center" style="display:none;">
                                                <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                                <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                            </div>
                                        </td>

                                        <!-- 8. QC Initials -->
                                        <td class="align-middle">
                                            <input type="text" class="form-control text-center font-weight-bold" name="operator_initials" value="{{ auth()->user()->initials ?? '' }}" required style="min-width: 70px;">
                                        </td>

                                        <!-- 9. Remarks -->
                                        <td class="align-middle">
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="..."></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-md-12 text-right d-flex justify-content-end align-items-center">
                                <h5 class="mr-3 mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                                <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">

                                <button type="button" class="btn btn-success btn-sm mr-2 shadow-sm" id="startTimerBtn">
                                    <i class="fas fa-play mr-1"></i> Start
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-sm px-4" id="saveBtn" disabled>
                                    <i class="fas fa-save mr-1"></i> SIMPAN DATA
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

    <!-- DOKUMEN STANDARD & DIMENSI (Di Bawah Form Input) -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
                <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-pdf mr-1"></i> DOKUMEN STANDARD & DIMENSI</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Panel Kiri: Standard PDF -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-dark mb-0">STANDARD PDF</h6>
                                <div class="d-flex align-items-center">
                                    <div class="btn-group mr-2">
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutStandard" title="Zoom Out">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetStandard" title="Reset Zoom">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInStandard" title="Zoom In">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                                        <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardPage" title="Previous Page">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                        <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardPage" title="Next Page">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn mr-1" id="fullStandardBtn" style="display:none;">
                                        <i class="fas fa-expand"></i> Full
                                    </button>
                                    <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download Standard PDF" style="display:none;">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            <div id="standardPdfContainer" class="rounded border" style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                                <div id="standardPdfPlaceholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                                    <i class="fas fa-file-pdf fa-3x mb-3"></i>
                                    <p class="mb-0">Pilih Item untuk menampilkan Standard PDF</p>
                                </div>
                                <canvas id="standardPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                                <div id="standardPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Kanan: Dimensi PDF -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-dark mb-0">DIMENSI</h6>
                                <div class="d-flex align-items-center">
                                    <div class="btn-group mr-2">
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutSimilar" title="Zoom Out">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetSimilar" title="Reset Zoom">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInSimilar" title="Zoom In">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center similar-nav-controls" style="display:none;">
                                        <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevSimilarPage" title="Previous Page">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <span id="similarPageInfo" class="small mx-1">P 1/1</span>
                                        <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextSimilarPage" title="Next Page">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn mr-1" id="fullSimilarBtn" style="display:none;">
                                        <i class="fas fa-expand"></i> Full
                                    </button>
                                    <a id="downloadSimilarBtn" class="btn btn-sm btn-info" href="#" download title="Download Dimensi Part PDF" style="display:none;">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            <div id="similarPdfContainer" class="rounded border" style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                                <div id="similarPdfPlaceholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                                    <i class="fas fa-file-alt fa-3x mb-3"></i>
                                    <p class="mb-0">Pilih Item untuk menampilkan Dimensi Part</p>
                                    <p class="small mt-2" id="similarStatusText"></p>
                                </div>
                                <canvas id="similarPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                                <div id="similarPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
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
