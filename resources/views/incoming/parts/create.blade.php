@extends('layouts.admin')

@section('title', 'Input Data Incoming Part')

@push('styles')
<style>
    #checksheetTable th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f8f9fc; vertical-align: middle; }
    #checksheetTable td { font-size: 0.85rem; vertical-align: middle; }
    .form-control-sm.text-center { font-weight: bold; border-color: #d1d3e2; }
    .form-control-sm.text-center:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
    #judgmentBadge { min-width: 80px; min-height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    
    /* Style Daftar Antrian Scan (Queue Table) presisi 100% selaras dengan #checksheetTable */
    #tempQueueCard {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }
    #tempQueueCard .card-header {
        background-color: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    #tempQueueTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: 1px solid #e2e8f0 !important;
        width: 100% !important;
    }
    #tempQueueTable > thead > tr > th,
    #tempQueueTable th {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 105 !important;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px !important;
        padding: 8px 12px !important;
        border: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        vertical-align: middle !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        text-align: center !important;
    }
    #tempQueueTable > tbody > tr > td,
    #tempQueueTable td {
        border: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.8rem !important;
        padding: 8px 10px !important;
    }
    #tempQueueTable tfoot td {
        background-color: #f8fafc !important;
        border-top: 2px solid #cbd5e1 !important;
        font-size: 0.8rem !important;
    }
</style>
@endpush

@section('content')
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_parts', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0210',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '0',
            'halaman' => '- / -'
        ]);
    @endphp
    
    <!-- Header Document -->
    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                            CHECK SHEET INCOMING PART
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

    <div class="row">
        <!-- Kolom Kiri: Input Data -->
        <div class="col-xl-6 col-lg-6 col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Part</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('incoming.parts.store') }}" method="POST" id="checksheetForm" novalidate>
                        @csrf
                        <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                        <input type="hidden" name="arrival_id" id="arrivalIdInput" value="">
                        <input type="hidden" id="initialBalanceInput" value="0">
                        <input type="hidden" name="qrcode" id="qrcodeInput">
                        <input type="hidden" name="part_code" id="partCodeInput">
                        <input type="hidden" name="supplier_id" id="supplierIdInput">
                        <input type="hidden" name="quantity" id="quantityInput">
                        <input type="hidden" name="unique_code_id" id="uniqueCodeInput">
                        <input type="hidden" name="sap_code" id="sapCodeInputHidden">
                        <input type="hidden" name="scan_method" id="scanMethodInput" value="manual">

                        <!-- SECTION 1: INFORMASI ITEM PART & KEDATANGAN SUPPLIER -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            INFORMASI ITEM PART &amp; KEDATANGAN SUPPLIER
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Item Part <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <select class="form-control select2 flex-grow-1" name="item_id" id="itemSelect" required style="width: 100%;">
                                    <option value="">-- Pilih Item --</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}"
                                            data-part-number="{{ $item->part_number ?? '' }}"
                                            data-defects="{{ json_encode($item->defects) }}"
                                            data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                            data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                            data-similar-files="{{ json_encode($item->similar_file_paths ?? ($item->similar_file_path ? [$item->similar_file_path] : [])) }}"
                                            data-weight-standard="{{ $item->weight_standard ?? '' }}"
                                            data-dimension-standards="{{ json_encode($item->dimension_standards) }}">
                                            {{ $item->name }} {{ $item->part_number ? "({$item->part_number})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-primary ml-2 flex-shrink-0" id="btnScanQrModal" title="Scan QR Barcode">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-row mb-4">
                            <div class="col-md-8 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-gray-700">Tgl &amp; Shift Kedatangan Supplier</label>
                                <select class="form-control form-control-sm select2 mb-1" id="arrivalSelect" style="width: 100%;">
                                    <option value="">-- Pilih Datang Supplier --</option>
                                    @foreach($recentArrivals as $arr)
                                        <option value="{{ $arr['id'] }}"
                                                data-arrival-date="{{ $arr['date'] }}"
                                                data-arrival-shift="{{ $arr['shift'] }}"
                                                data-supplier-name="{{ $arr['supplier_name'] }}"
                                                data-po-number="{{ $arr['po_number'] }}"
                                                data-surat-jalan="{{ $arr['surat_jalan'] }}"
                                                data-items="{{ json_encode($arr['items']) }}">
                                            [{{ $arr['supplier_name'] }}] {{ \Carbon\Carbon::parse($arr['date'])->format('d/m/Y') }} Shift {{ $arr['shift'] }} (PO: {{ $arr['po_number'] }})
                                        </option>
                                    @endforeach
                                </select>

                                <!-- Display Detail Supplier -->
                                <div id="arrivalDetailBox" class="p-1 rounded bg-light border" style="display:none; font-size: 0.7rem;">
                                    <div><strong>Supp:</strong> <span id="arrSupplierName">-</span></div>
                                    <div><strong>PO:</strong> <span id="arrPoNumber">-</span></div>
                                    <div><strong>SJ:</strong> <span id="arrSuratJalan">-</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="small font-weight-bold text-gray-700 d-block">Qty Balance</label>
                                <span id="qtyBalanceBadge" class="badge badge-info px-3 py-2 font-weight-bold" style="font-size: 0.9rem;">0</span>
                            </div>
                        </div>

                        <!-- SECTION 2: TANGGAL CHECK & KUANTITAS SAMPLING -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            TANGGAL CHECK &amp; KUANTITAS SAMPLING
                        </div>

                        <div class="bg-light p-3 rounded border mb-4 shadow-sm">
                            <div class="form-row mb-3">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Tanggal Check <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="date" value="{{ $defaultDate }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Shift Check <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm font-weight-bold border-0 shadow-sm" name="shift" required>
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Total Check <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="total_check" id="totalCheckInput" placeholder="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Qty Sampling <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="qty_sampling" id="qtySamplingInput" placeholder="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: SAMPLING BERAT & DIMENSI -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            SAMPLING BERAT &amp; DIMENSI
                        </div>

                        <div class="row align-items-stretch mb-4">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="card bg-light border-0 shadow-sm h-100">
                                    <div class="card-body p-2">
                                        <h6 class="font-weight-bold text-dark mb-2 border-bottom pb-1" style="font-size: 0.8rem;">
                                            <i class="fas fa-balance-scale text-primary mr-1"></i> SAMPLING BERAT (GRAM)
                                        </h6>
                                        <div class="row no-gutters text-center small font-weight-bold mb-1">
                                            <div class="col-4">STD</div>
                                            <div class="col-4">MIN</div>
                                            <div class="col-4">MAX</div>
                                        </div>
                                        <div class="row no-gutters text-center mb-2">
                                            <div class="col-4 px-1"><input type="text" class="form-control form-control-sm text-center bg-white border-0 shadow-sm" id="stdBeratDisplay" value="-" readonly></div>
                                            <div class="col-4 px-1"><input type="text" class="form-control form-control-sm text-center bg-white border-0 shadow-sm" id="minBeratDisplay" value="-" readonly></div>
                                            <div class="col-4 px-1"><input type="text" class="form-control form-control-sm text-center bg-white border-0 shadow-sm" id="maxBeratDisplay" value="-" readonly></div>
                                        </div>
                                        <div class="row no-gutters text-center">
                                            <div class="col-4 px-1"><input type="number" step="any" class="form-control form-control-sm text-center border-0 shadow-sm" name="weight_pcs_1" id="berat1" placeholder="Pcs 1"></div>
                                            <div class="col-4 px-1"><input type="number" step="any" class="form-control form-control-sm text-center border-0 shadow-sm" name="weight_pcs_2" id="berat2" placeholder="Pcs 2"></div>
                                            <div class="col-4 px-1"><input type="number" step="any" class="form-control form-control-sm text-center border-0 shadow-sm" name="weight_pcs_3" id="berat3" placeholder="Pcs 3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-sm h-100">
                                    <div class="card-body p-2">
                                        <h6 class="font-weight-bold text-dark mb-2 border-bottom pb-1" style="font-size: 0.8rem;">
                                            <i class="fas fa-ruler-combined text-info mr-1"></i> SAMPLING DIMENSI (POINT / UKURAN)
                                        </h6>
                                        <div class="table-responsive" style="max-height: 120px; overflow-y: auto;">
                                            <table class="table table-bordered table-sm mb-0 bg-white" id="dimensionTable">
                                                <thead class="bg-light text-dark small text-center sticky-top">
                                                    <tr>
                                                        <th style="width: 40%;">Point Standard</th>
                                                        <th style="width: 50%;">Hasil Ukur (Pcs 1 / Sampling)</th>
                                                        <th style="width: 10%;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dimensionBody">
                                                    <tr class="point-row">
                                                        <td class="p-1">
                                                            <input type="text" class="dimension-input form-control form-control-sm border-0 bg-transparent text-center font-weight-bold" name="dimension_standards_list[]" placeholder="Point A..." readonly>
                                                        </td>
                                                        <td class="p-1">
                                                            <input type="text" class="dimension-input form-control form-control-sm border-0 text-center font-weight-bold" name="dimensions[]" placeholder="Hasil...">
                                                        </td>
                                                        <td class="text-center align-middle p-1">
                                                            <button type="button" class="btn btn-xs btn-danger delete-point-row" title="Hapus Point"><i class="fas fa-trash-alt"></i></button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: HASIL INSPEKSI & JUDGMENT -->
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

                        <!-- Progress Bar Loading Simpan -->
                        <div id="saveProgressWrapper" class="mb-3" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold text-primary" id="saveProgressStatus">Menyimpan data...</span>
                                <span class="small font-weight-bold text-primary" id="saveProgressPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 18px; border-radius: 9px;">
                                <div id="saveProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>

                        <!-- Footer Control Bar -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-stopwatch text-gray-500 mr-2 fa-lg"></i>
                                <h5 class="mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                                <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">
                                <button type="button" class="btn btn-success btn-sm ml-3 shadow-sm font-weight-bold px-3" id="startTimerBtn">
                                    <i class="fas fa-play mr-1"></i> Start
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" id="btnAddToQueue" disabled>
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah ke List Antrean
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Antrean Data (Queue List) -->
            <div class="card shadow mb-4" id="queueCard">
                <div class="card-header py-2 bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-list-ol mr-1"></i> Antrean List Data Check Sheet (<span id="queueCountBadge">0</span>)
                    </h6>
                    <span class="badge badge-light text-primary font-weight-bold" style="font-size: 0.75rem;">Siap Disimpan Ke Database</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered mb-0 text-center" id="queueTable">
                            <thead class="bg-light small font-weight-bold text-uppercase">
                                <tr>
                                    <th>No</th>
                                    <th>Item Part</th>
                                    <th>Tgl &amp; Shift Kedatangan</th>
                                    <th>Supplier / PO</th>
                                    <th>Tgl &amp; Shift Check</th>
                                    <th>Total Check</th>
                                    <th>Qty Sampling</th>
                                    <th>Detail NG</th>
                                    <th>Judgment</th>
                                    <th>Berat / Dimensi</th>
                                    <th>QC</th>
                                    <th>Remarks</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="queueTableBody">
                                <tr id="emptyQueueRow">
                                    <td colspan="13" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                        Belum ada data di list antrean. Isikan form di atas lalu klik tombol <strong>"Tambah ke List Antrean"</strong>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <small class="text-muted font-italic">* Periksa kembali seluruh baris antrean sebelum menyimpan ke database.</small>
                    <div class="text-right ml-auto">
                        <button type="button" class="btn btn-danger btn-sm mr-2 shadow-sm" id="btnClearQueue">
                            <i class="fas fa-trash-alt mr-1"></i> Kosongkan List
                        </button>
                        <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnSaveQueue">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Simpan Semua Data List (<span id="queueCountDisplay">0</span> Data)
                        </button>
                    </div>
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
                        <!-- PCCP / Standard -->
                        <div class="col-md-12 border-bottom mb-4 pb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-dark mb-0">PCCP DAN SIMILAR PART</h6>
                                <div class="d-flex align-items-center">
                                    <div class="btn-group mr-2">
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutStandard" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetStandard" title="Reset Zoom"><i class="fas fa-sync-alt"></i></button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInStandard" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                                    </div>
                                    <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                                        <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardPage" title="Previous Page"><i class="fas fa-chevron-left"></i></button>
                                        <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                        <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardPage" title="Next Page"><i class="fas fa-chevron-right"></i></button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn mr-1" id="fullStandardBtn" style="display:none;"><i class="fas fa-expand"></i> Full</button>
                                    <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download Standard PDF" style="display:none;"><i class="fas fa-download"></i></a>
                                </div>
                            </div>
                            <div id="standardPdfContainer" class="rounded border" style="height: 650px; min-height: 550px; position: relative; background-color: #eee; overflow: auto;">
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
                        <!-- Similar Part / Dimensi -->
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-dark mb-0">DIMENSI</h6>
                                <div class="d-flex align-items-center">
                                    <div class="btn-group mr-2">
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutSimilar" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetSimilar" title="Reset Zoom"><i class="fas fa-sync-alt"></i></button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInSimilar" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                                    </div>
                                    <div class="d-flex align-items-center similar-nav-controls" style="display:none;">
                                        <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevSimilarPage" title="Previous Page"><i class="fas fa-chevron-left"></i></button>
                                        <span id="similarPageInfo" class="small mx-1">P 1/1</span>
                                        <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextSimilarPage" title="Next Page"><i class="fas fa-chevron-right"></i></button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn mr-1" id="fullSimilarBtn" style="display:none;"><i class="fas fa-expand"></i> Full</button>
                                    <a id="downloadSimilarBtn" class="btn btn-sm btn-info" href="#" download title="Download Dimensi Part PDF" style="display:none;"><i class="fas fa-download"></i></a>
                                </div>
                            </div>
                            <div id="similarPdfContainer" class="rounded border" style="height: 650px; min-height: 550px; position: relative; background-color: #eee; overflow: auto;">
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
        </div>
    </div>

    <!-- Modal Pemindai QR -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrScannerModalLabel"><i class="fas fa-qrcode mr-2"></i>QR Code Scanner</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="position-relative">
                        <video id="qr-video" class="w-100" autoplay muted playsinline style="border-radius: 8px;"></video>
                        <button type="button" id="toggleFlashBtn" class="btn btn-sm btn-dark position-absolute d-none" style="top: 10px; left: 10px; opacity: 0.7; z-index: 10;">
                            <i class="fas fa-bolt text-white"></i> Flash
                        </button>
                        <button type="button" id="toggleMirrorBtn" class="btn btn-sm btn-dark position-absolute" style="top: 10px; right: 10px; opacity: 0.7; z-index: 10;">
                            <i class="fas fa-arrows-alt-h text-white"></i> Flip
                        </button>
                    </div>
                    <style>
                        #qr-video.mirrored { transform: scaleX(-1) !important; }
                        #zoomContainer { background: rgba(0,0,0,0.5); border-radius: 0 0 8px 8px; }
                        #zoomSlider { height: 6px; cursor: pointer; }
                    </style>
                    <div id="zoomContainer" class="p-2 d-none">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-search-minus text-white mr-2"></i>
                            <input type="range" id="zoomSlider" class="custom-range flex-grow-1" min="1" max="1" step="0.1" value="1">
                            <i class="fas fa-search-plus text-white ml-2"></i>
                        </div>
                    </div>
                    <div id="qr-reader-results" class="p-3 text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Memuat...</span>
                        </div>
                        <p class="mt-2 text-muted">Memproses data QR...</p>
                    </div>
                    <div class="p-3 border-top bg-light">
                        <label class="font-weight-bold">Atau Unggah Gambar QR:</label>
                        <input type="file" id="qr-input-file" accept="image/*" class="form-control-file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.INCOMING_PART_CONFIG = {
            arrivalsUrl: "{{ route('incoming.parts.arrivals') }}",
            checkFirstTimeUrl: "{{ route('incoming.parts.check_first_time') }}",
            qrUniqueUrl: "{{ route('items.check-qr-unique') }}",
            itemSearchUrl: "{{ route('items.search-by-part') }}",
            useQueue: true
        };
    </script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script>
        window.pdfWorkerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";
        window.pdfUrlPattern = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}";
    </script>
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/incoming-create.js') }}"></script>
    <script>
        $(document).ready(function () {
            if(typeof window.initItemSearch === 'function') {
                window.initItemSearch('itemSelect');
            }
        });
    </script>
@endpush
