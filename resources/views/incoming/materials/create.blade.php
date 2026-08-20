@extends('layouts.admin')

@section('title', 'Input Data Incoming Material')

@push('styles')
<style>
    #checksheetTable th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f8f9fc; }
    #checksheetTable td { font-size: 0.85rem; }
    .ok-label { background-color: #28a745; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
    .ng-label { background-color: #dc3545; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
    #judgmentBadge { min-width: 80px; min-height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }

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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Kolom Kiri: Input Data -->
        <div class="col-xl-6 col-lg-6 col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Material</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('incoming.materials.store') }}" method="POST" id="checksheetForm" novalidate>
                        @csrf
                        <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                        <!-- SECTION 1: INFORMASI MATERIAL & TANGGAL -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            INFORMASI MATERIAL &amp; TANGGAL
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Material Name <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="item_id" id="itemSelect" required style="width: 100%;">
                                <option value="">-- Pilih Material --</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" 
                                        data-defects="{{ json_encode($item->defects) }}"
                                        data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row mb-3">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-gray-700">Tgl Datang <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="tanggal_datang" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small font-weight-bold text-gray-700">Expired Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="expired_date" required>
                            </div>
                        </div>

                        <div class="form-row mb-4">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-gray-700">Tanggal Check <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="date" value="{{ $defaultDate }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small font-weight-bold text-gray-700">Lot/Batch Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm border-0 shadow-sm" name="lot_batch_number" placeholder="Lot #" required>
                            </div>
                        </div>

                        <!-- SECTION 2: DETAIL KUANTITAS -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            DETAIL KUANTITAS (KG)
                        </div>

                        <div class="bg-light p-3 rounded border mb-4 shadow-sm">
                            <div class="form-row">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Qty (Kg)</label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="quantity_kg" id="lotQtyInput" placeholder="0" required>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Komper/Karung</label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="komper_karung_kg" id="komperKarungInput" placeholder="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Sampling Size</label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="sampling_size_karung_kg" id="totalCheckInput" placeholder="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: HASIL INSPEKSI & JUDGMENT -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            HASIL INSPEKSI &amp; JUDGMENT
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 d-block mb-1">Defect List (NG):</label>
                            <div id="defectContainer">
                                <div class="row no-gutters mb-2 defect-row align-items-center">
                                    <div class="col-7 pr-1">
                                        <select class="form-control form-control-sm defect-select font-weight-bold border-0 shadow-sm" name="defect_types[]" id="defectSelect">
                                            <option value="">-- Pilih Defect --</option>
                                        </select>
                                    </div>
                                    <div class="col-3 pr-1">
                                        <input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold border-0 shadow-sm" name="defect_quantities[]" placeholder="Qty" min="1">
                                    </div>
                                    <div class="col-2 text-center action-col">
                                        <button type="button" id="addDefectBtn" class="btn btn-primary btn-sm shadow-sm" style="display: none;" title="Tambah Jenis">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded border mb-3 shadow-sm">
                            <div class="form-row align-items-center">
                                <div class="col-md-6 mb-2 mb-md-0 text-center border-right">
                                    <label class="small font-weight-bold text-gray-700 d-block mb-1">Judgment Result</label>
                                    <div id="judgmentBadge" class="mb-1 p-2 font-weight-bold h5 rounded d-none shadow-sm" style="border: 2px solid transparent;">-</div>
                                    <select class="form-control form-control-sm font-weight-bold d-none" name="judgment" id="judgmentSelect" required>
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                    <input type="hidden" name="total_ng" id="totalNgInput" value="0">
                                    <div id="aql_info" class="small font-weight-bold text-center" style="display:none;">
                                        <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                    </div>
                                </div>
                                <div class="col-md-6 pl-md-3">
                                    <label class="small font-weight-bold text-gray-700">QC Initials <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm text-center font-weight-bold border-0 shadow-sm" name="operator_initials" value="{{ auth()->user()->initials ?? '' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-gray-700">Remarks / Catatan</label>
                            <textarea class="form-control form-control-sm border-0 shadow-sm" name="remarks" rows="2" placeholder="Tuliskan catatan opsional di sini..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                                <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">
                            </div>
                            <div>
                                <button type="button" class="btn btn-success btn-sm mr-2 shadow-sm font-weight-bold px-3" id="startTimerBtn">
                                    <i class="fas fa-play mr-1"></i> Start
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold" id="saveBtn" disabled>
                                    <i class="fas fa-save mr-1"></i> SIMPAN DATA
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: STANDARD -->
        <div class="col-xl-6 col-lg-6 col-md-12">
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
                                style="height: 950px; min-height: 850px; overflow: auto; position: relative;">
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
