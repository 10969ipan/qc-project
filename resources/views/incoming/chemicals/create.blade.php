@extends('layouts.admin')

@section('title', 'Input Data Incoming Chemical')

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
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_chemicals', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0214',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp

    <div class="row">
        <!-- Kolom Kiri: Input Data -->
        <div class="col-xl-6 col-lg-6 col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <table style="width:100%; border-collapse:collapse; border:1px solid #dee2e6;">
                            <tr>
                                {{-- ===== [1] KOLOM LOGO ===== --}}
                                <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                                    <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                                </td>

                                {{-- ===== [2] KOLOM JUDUL ===== --}}
                                <td style="border:1px solid #dee2e6; padding:5px 8px; text-align:center; vertical-align:middle;">
                                    <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800"
                                        style="font-size:0.85rem; letter-spacing:0.3px;">
                                        CHECK SHEET INCOMING CHEMICAL
                                    </h1>
                                </td>

                                {{-- ===== [3] KOLOM KANAN: No. Dokumen + Signatures ===== --}}
                                <td style="width:1px; border:1px solid #dee2e6; padding:0 !important; vertical-align:top; white-space:nowrap;">

                                    {{-- Wrapper baris — menyamakan tinggi kedua tabel anak --}}
                                    <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                        <tr style="height:100%;">

                                            {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                                            <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                                                <table style="border-collapse:collapse; border:1px solid #dee2e6; font-size:0.65rem; background:#fff; height:100%; width:100%;">
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:700; color:#212529; white-space:nowrap;">
                                                            {{ $docHeader['no_dokumen'] }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                                                    </tr>
                                                </table>
                                            </td>

                                            {{-- ===== [3B] Tabel Signatures ===== --}}
                                            <td style="border:none; padding:4px 4px 4px 0; vertical-align:top; height:100%;">
                                                <table style="border-collapse:collapse; border:1px solid #dee2e6; text-align:center; font-size:0.65rem; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:388px;">
                                                    <thead>
                                                        <tr>
                                                            <th style="border:1px solid #dee2e6; padding:3px 2px; font-weight:600; color:#495057; background:#fff; width:28px;">Tgl.</th>
                                                            <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Dibuat</th>
                                                            <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diperiksa</th>
                                                            <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diketahui</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- BARIS 1: Gambar tanda tangan --}}
                                                        <tr>
                                                            <td rowspan="3" style="border:1px solid #dee2e6; padding:2px; vertical-align:middle; text-align:center; width:28px;">
                                                                <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:0.58rem; font-weight:400; margin:0 auto; color:#6c757d;">
                                                                    06-Jan-26
                                                                </div>
                                                            </td>

                                                            {{-- Tanda tangan Dibuat --}}
                                                            <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                                @if(in_array(strtolower($plantCode ?? 'karawang'), ['jakarta', 'jkt']))
                                                                    <img src="{{ asset('signatures/suli.png') }}" alt="Masuli"
                                                                         style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                                @else
                                                                    <img src="{{ asset('signatures/arif.png') }}" alt="Arief H"
                                                                         style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                                @endif
                                                            </td>

                                                            {{-- Tanda tangan Diperiksa --}}
                                                            <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                                <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S"
                                                                     style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            </td>

                                                            {{-- Tanda tangan Diketahui --}}
                                                            <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                                <img src="{{ asset('signatures/desti.png') }}" alt="Desti K"
                                                                     style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            </td>
                                                        </tr>

                                                        {{-- BARIS 2: Nama --}}
                                                        <tr>
                                                            <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">{{ in_array(strtolower($plantCode ?? 'karawang'), ['jakarta', 'jkt']) ? 'Masuli' : 'Arief H' }}</td>
                                                            <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Iwan S</td>
                                                            <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Desti K</td>
                                                        </tr>

                                                        {{-- BARIS 3: Jabatan --}}
                                                        <tr>
                                                            <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Spv. QC</td>
                                                            <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                                            <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>

                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <form action="{{ route('incoming.chemicals.store') }}" method="POST" id="checksheetForm" novalidate>
                        @csrf
                        <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                        <!-- SECTION 1: INFORMASI CHEMICAL & TANGGAL -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            INFORMASI CHEMICAL &amp; TANGGAL
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Chemical Name <span class="text-danger">*</span></label>
                            <select class="form-control" name="item_id" id="itemSelect" required style="width: 100%;">
                                <option value="">-- Pilih Chemical --</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" 
                                        data-name="{{ $item->name }}"
                                        data-part-number="{{ $item->part_number ?? '' }}"
                                        data-sap-code="{{ $item->sap_code ?? '' }}"
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
                            DETAIL KUANTITAS
                        </div>

                        <div class="bg-light p-3 rounded border mb-4 shadow-sm">
                            <div class="form-row">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Qty (Kg/L/Botol)</label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="quantity_kg" id="lotQtyInput" placeholder="0" required>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Komp/Jirigen</label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="komper_jirigen_kg" id="komperJirigenInput" placeholder="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Sampling Size</label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="sampling_size_jirigen_kg" id="totalCheckInput" placeholder="0" required>
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
                <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
                    <div>
                        <a id="downloadStandardBtn" href="#" target="_blank" class="btn btn-xs btn-primary mr-1" style="display:none;" title="Download PDF">
                            <i class="fas fa-download mr-1"></i> Download PDF
                        </a>
                        <button type="button" class="btn btn-xs btn-secondary" id="fullStandardBtn" style="display:none;" title="Full Screen Preview">
                            <i class="fas fa-expand mr-1"></i> Fullscreen
                        </button>
                    </div>
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

    <!-- Modal Full Screen PDF Preview -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95vw; margin: 10px auto;">
            <div class="modal-content" style="height: 92vh; display: flex; flex-direction: column;">
                <div class="modal-header py-2 bg-dark text-white align-items-center">
                    <h6 class="modal-title font-weight-bold" id="pdfModalLabel">
                        <i class="fas fa-file-pdf text-danger mr-2"></i> Preview Standard PDF - <span id="pdfInfo">File 1</span>
                    </h6>
                    <div class="d-flex align-items-center">
                        <div class="btn-group mr-3">
                            <button type="button" class="btn btn-sm btn-outline-light" id="pdfZoomOut" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-light" id="pdfZoomReset" title="Reset Zoom"><i class="fas fa-sync-alt"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-light" id="pdfZoomIn" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                        </div>
                        <div class="btn-group mr-3">
                            <button type="button" class="btn btn-sm btn-outline-light" id="prevPdf" title="File PDF Sebelumnya" style="display:none;"><i class="fas fa-step-backward"></i> Prev File</button>
                            <button type="button" class="btn btn-sm btn-outline-light" id="prevPage" title="Halaman Sebelumnya"><i class="fas fa-chevron-left"></i> Prev</button>
                            <span class="btn btn-sm btn-dark disabled text-white" id="pageInfo" style="min-width: 100px;">Page 1</span>
                            <button type="button" class="btn btn-sm btn-outline-light" id="nextPage" title="Halaman Selanjutnya">Next <i class="fas fa-chevron-right"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-light" id="nextPdf" title="File PDF Selanjutnya" style="display:none;">Next File <i class="fas fa-step-forward"></i></button>
                        </div>
                        <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body p-0 bg-secondary flex-grow-1" style="overflow: auto; display: flex; justify-content: center; align-items: flex-start;">
                    <canvas id="the-canvas" class="shadow-lg my-2" style="background-color: white;"></canvas>
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
    <script src="{{ asset('js/vendor/item-search.js') }}?v={{ time() }}"></script>
    <script>
        $(document).ready(function () {
            if (typeof window.initItemSearch === 'function') {
                window.initItemSearch('itemSelect');
            }
        });
    </script>
    <script src="{{ asset('js/checksheet/incoming-chemical-create.js') }}?v={{ time() }}"></script>
@endpush
