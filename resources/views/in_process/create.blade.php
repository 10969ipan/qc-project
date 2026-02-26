@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET IN PROCESS INJECTION
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');
                        @endphp
                        <span
                            class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                            style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                {{ $plantCode === 'jakarta' ? 'QC - JKT - F - 032/0' : 'QC-KRW-F-0212' }}
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                {{ $plantCode === 'jakarta' ? '21.02.2023' : '25/03/2015' }}
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi / Tgl</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                {{ $plantCode === 'jakarta' ? '1 / 14.06.2023' : '3 / 22/12/2025' }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Halaman</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 1 / 1</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $plantCode = strtolower(request('plant') ?? (auth()->user()->plant ? auth()->user()->plant->code : ''));
        $jakartaMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
        $karawangMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
        $machineNumbers = ($plantCode === 'jakarta') ? $jakartaMachineNumbers : $karawangMachineNumbers;
    @endphp



    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($errors) && (is_array($errors) ? count($errors) > 0 : $errors->any()))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="font-weight-bold">Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach(is_array($errors) ? $errors : $errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4 border-left-info">
        <a href="#collapseMachineStatus" class="d-block card-header py-3" data-toggle="collapse" role="button"
            aria-expanded="true" aria-controls="collapseMachineStatus">
            <h6 class="m-0 font-weight-bold text-info">Control Status Mesin (Manual)</h6>
        </a>
        <div class="collapse" id="collapseMachineStatus">
            <div class="card-body">
                <form action="{{ route('machine-status.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="machine">
                    <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Pilih Mesin</label>
                            <select name="number" class="form-control form-control-sm" required>
                                <option value="">- Pilih Mesin -</option>
                                @foreach($machineNumbers as $num)
                                    <option value="{{ $num }}">MESIN-{{ $num }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Status</label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="normal">NORMAL (Auto)</option>
                                <option value="maintenance">GANTI MOLD/SETTING (Kuning)</option>
                                <option value="stopped">STAND BY (Hitam)</option>
                                <option value="trouble">TROUBLE (Merah)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Keterangan (Optional)</label>
                            <input type="text" name="description" class="form-control form-control-sm"
                                placeholder="Keterangan...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-info btn-sm btn-block">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Inprocess</h6>
        </div>
        <div class="card-body">
            <!-- Plant Selector for Admin -->
            @if(auth()->user()->role === 'admin')
                <form method="GET" action="{{ route('in_process.create') }}" class="mb-3">
                    <div class="form-group row">
                        <label for="plant" class="col-sm-2 col-form-label font-weight-bold">Pilih Plant:</label>
                        <div class="col-sm-4">
                            <select name="plant" id="plant" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Semua Plant --</option>
                                <option value="karawang" {{ request('plant') == 'karawang' ? 'selected' : '' }}>Karawang</option>
                                <option value="jakarta" {{ request('plant') == 'jakarta' ? 'selected' : '' }}>Jakarta</option>
                            </select>
                            <small class="text-muted">Pilih plant untuk memfilter daftar item.</small>
                        </div>
                    </div>
                </form>
            @endif

            <form action="{{ route('in_process.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <tr class="text-center">
                            <th rowspan="2" style="align-middle">Item Part</th>
                            <th rowspan="2" style="align-middle">Tanggal / Shift</th>
                            <th rowspan="2" style="align-middle">Total Qty</th>
                            <th rowspan="2" style="align-middle">Sampling Qty</th>
                            <th rowspan="2" style="align-middle">Check Dimensi</th>
                            <th rowspan="2" style="align-middle; min-width: 280px;">Jenis (OK/NG) & Detail NG</th>
                            <th rowspan="2" style="align-middle">Total (OK/NG)</th>
                            <th rowspan="2" style="align-middle">Judgment</th>
                            <th rowspan="2" style="align-middle">Inisial QC</th>
                            <th rowspan="2" style="align-middle">Keterangan</th>
                        </tr>
                        <tbody>
                            <tr>

                                <!-- Pilihan Barang -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold">Kode SAP</label>
                                        <input type="text" class="form-control" id="sapCodeInput"
                                            placeholder="Ketik Kode SAP..." style="min-width: 200px;">
                                        <small class="text-muted">Auto-select item berdasarkan SAP code</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part</label>
                                        <select class="form-control" name="item_id" id="itemSelect" required
                                            style="min-width: 300px;">
                                            <option value="" disabled selected style="font-weight: bold; color: #6c757d;">
                                                Pilih
                                                Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-similar="{{ $item->similar_part_file_path ? route('items.pdf', ['id' => $item->id, 'index' => 'similar']) : '' }}"
                                                    data-name="{{ $item->name }}" data-part-number="{{ $item->part_number }}"
                                                    data-description="{{ $item->description }}"
                                                    data-defects="{{ json_encode($item->defects) }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}"
                                                    data-cavity="{{ $item->cavity }}"
                                                    data-dimension-standards="{{ json_encode($item->dimension_standards) }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                    {{ $item->sap_code ? '- SAP: ' . $item->sap_code : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Tanggal / Shift -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="sr-only">Tanggal</label>
                                        <input type="date" class="form-control" style="min-width: 110px;" name="date"
                                            value="{{ $defaultDate }}" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="sr-only">Shift</label>
                                        <select class="form-control" style="min-width: 80px;" name="shift" required>
                                            <option value="1" {{ ($defaultShift ?? 1) == 1 ? 'selected' : '' }}>Shift 1
                                            </option>
                                            <option value="2" {{ ($defaultShift ?? 1) == 2 ? 'selected' : '' }}>Shift 2
                                            </option>
                                            <option value="3" {{ ($defaultShift ?? 1) == 3 ? 'selected' : '' }}>Shift 3
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label class="sr-only">No Mesin</label>
                                        <select name="code_machine" id="code_machine" class="form-control"
                                            style="min-width: 80px;" required>
                                            <option value="">Pilih Mesin</option>
                                            @foreach($machineNumbers as $num)
                                                <option value="{{ $num }}">Mesin {{ $num }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Quality (Total Quantity produced) -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="total_qty" placeholder="0" min="0" required>
                                </td>

                                <!-- Sampling Check Quantity -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="sampling_qty" placeholder="0" min="0" required>
                                </td>

                                <!-- Check Dimensi (Cavity & Points) -->
                                <td class="align-middle">
                                    <div class="d-flex justify-content-center mb-2">
                                        <div class="btn-toolbar bg-white border rounded shadow-sm p-1" role="toolbar">
                                            <div class="btn-group mr-2" role="group">
                                                <button type="button" class="btn btn-primary btn-xs" id="addCavityBtn"
                                                    title="Tambah Cavity">
                                                    <i class="fas fa-plus"></i> Cavity
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-xs"
                                                    id="deleteCavityBtn" title="Hapus Cavity Terakhir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-info btn-xs" id="addPointBtn"
                                                    title="Tambah Point">
                                                    <i class="fas fa-plus"></i> Point
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-xs"
                                                    id="deletePointBtn" title="Hapus Point Terakhir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive" style="max-height: 400px; overflow: auto;">
                                        <table class="table table-sm table-bordered mb-0" id="dimensionTable">
                                            <thead class="text-center bg-light">
                                                <tr id="dimensionHeadRow">
                                                    <th
                                                        style="min-width: 100px; position: sticky; left: 0; z-index: 2; background: #f8f9fa;">
                                                        Cavity</th>
                                                    @for ($j = 1; $j <= 5; $j++)
                                                        <th class="point-header">Point {{ $j }}</th>
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody id="dimensionBody">
                                                @for ($i = 1; $i <= 2; $i++)
                                                    <tr class="cavity-row" data-cavity="{{ $i }}">
                                                        <td class="text-center font-weight-bold bg-light"
                                                            style="position: sticky; left: 0; z-index: 1;">Cav {{ $i }}</td>
                                                        @for ($j = 1; $j <= 5; $j++)
                                                            <td class="point-cell">
                                                                <input type="text"
                                                                    class="form-control form-control-sm dimension-input"
                                                                    style="min-width: 60px;" name="dimensions[{{ $i }}][{{ $j }}]"
                                                                    placeholder="P{{ $j }}">
                                                            </td>
                                                        @endfor
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </td>

                                <td class="align-middle" style="min-width: 280px;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="check_ok" value="1"
                                            id="checkOK">
                                        <label class="form-check-label text-success font-weight-bold" for="checkOK">OK
                                            (Pass)</label>
                                    </div>
                                    <hr class="my-2">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="input-group mb-2 defect-row">
                                            <select class="form-control defect-select" style="min-width: 180px;"
                                                name="defect_types[]" id="defectSelect">
                                                <option value="">-- Pilih Defect --</option>
                                            </select>
                                            <input type="number" class="form-control defect-qty" style="min-width: 100px;"
                                                name="defect_quantities[]" placeholder="Qty" min="1">
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-info mt-1"
                                        style="display: none;">
                                        <i class="fas fa-plus"></i> Tambah Jenis
                                    </button>
                                </td>

                                <!-- Total OK / NG -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <div class="row no-gutters mb-1">
                                        <div
                                            class="col-4 text-center bg-success text-white py-1 rounded-left small font-weight-bold">
                                            OK</div>
                                        <div class="col-8">
                                            <input type="number"
                                                class="form-control form-control-sm rounded-0 rounded-right text-center"
                                                style="font-size: 14px;" name="total_ok" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                    <div class="row no-gutters">
                                        <div
                                            class="col-4 text-center bg-danger text-white py-1 rounded-left small font-weight-bold">
                                            NG</div>
                                        <div class="col-8">
                                            <input type="number"
                                                class="form-control form-control-sm rounded-0 rounded-right text-center"
                                                style="font-size: 14px;" name="total_ng" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle">
                                    <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect"
                                        required>
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                    <div id="aql_info" class="small mt-1 font-weight-bold text-center"
                                        style="display:none;">
                                        <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                    </div>
                                </td>

                                <!-- Inisial Operator -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" style="min-width: 60px;"
                                        name="operator_initials" placeholder="Inisial"
                                        value="{{ auth()->user()->initials ?? '' }}" required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next
                                            Proses: <span class="text-danger">*</span></label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            <option value="CRUSHING">CRUSHING</option>
                                            <option value="SORTIR">SORTIR</option>
                                            <option value="FINISHING">FINISHING</option>
                                            <option value="REPAIR">REPAIR</option>
                                            <option value="SORTIR + FINISHING">SORTIR + FINISHING</option>
                                            <option value="FINISHING + PASANG SUB PART">FINISHING + PASANG SUB PART</option>
                                            <option value="FINISHING + PACKING">FINISHING + PACKING</option>
                                            <option value="REBUS + FINISHING + PACKING">REBUS + FINISHING + PACKING</option>
                                            <option value="SORTIR + CRUSHING">SORTIR + CRUSHING</option>
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="4"
                                        placeholder="Catatan tambahan..."></textarea>
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
                        <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                            <i class="fas fa-save fa-sm"></i> Simpan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PDF Side-by-Side Display Section -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-eye mr-2"></i>STANDARD</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">PCCP</h6>
                        <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                            <div class="mr-2 border-right pr-2 d-flex align-items-center file-nav" style="display:none;">
                                <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardFile"
                                    title="Previous File">
                                    <i class="fas fa-file-pdf"></i> <i class="fas fa-arrow-left fa-xs"></i>
                                </button>
                                <span id="standardFileInfo" class="small font-weight-bold mx-1">1/1</span>
                                <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardFile"
                                    title="Next File">
                                    <i class="fas fa-arrow-right fa-xs"></i> <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center page-nav">
                                <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevStandardPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextStandardPage"
                                    title="Next Page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn" id="fullStandardBtn"
                            style="display:none;">
                            <i class="fas fa-expand"></i> Full
                        </button>
                    </div>
                    <div id="standardPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <!-- Updated default state: message shown when no item selected -->
                        <div id="standardPdfPlaceholder"
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-pdf fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan Standard PDF</p>
                        </div>
                        <canvas id="standardPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                        <div id="standardPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">DIMENSI</h6>
                        <div class="d-flex align-items-center similar-nav-controls" style="display:none;">
                            <div class="d-flex align-items-center page-nav">
                                <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevSimilarPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="similarPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextSimilarPage"
                                    title="Next Page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn" id="fullSimilarBtn"
                            style="display:none;">
                            <i class="fas fa-expand"></i> Full
                        </button>
                    </div>
                    <div id="similarPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="similarPdfPlaceholder"
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan Similar Part</p>
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

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">STANDARD (Image)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2">
                        <button type="button" class="btn btn-primary btn-sm mr-2" id="zoomIn">
                            <i class="fas fa-search-plus"></i> Zoom In
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="zoomReset">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="zoomOut">
                            <i class="fas fa-search-minus"></i> Zoom Out
                        </button>
                    </div>
                    <div class="text-center" style="overflow: auto; max-height: 70vh;">
                        <img id="modalImage" src="" class="img-fluid mb-3" alt="Detail Gambar"
                            style="transition: transform 0.2s ease;">
                    </div>
                    <div class="text-center">
                        <h5 id="modalTitle" class="font-weight-bold"></h5>
                        <p id="modalDescription"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Modal (Added) -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2 align-items-center flex-wrap">
                        <div class="mr-3 mb-2">
                            <button type="button" class="btn btn-dark btn-sm" id="prevPdf">
                                <i class="fas fa-file-pdf"></i> <i class="fas fa-arrow-left"></i>
                            </button>
                            <span id="pdfInfo" class="mx-2 font-weight-bold">File 1 of ?</span>
                            <button type="button" class="btn btn-dark btn-sm" id="nextPdf">
                                <i class="fas fa-arrow-right"></i> <i class="fas fa-file-pdf"></i>
                            </button>
                        </div>
                        <div class="mr-3 mb-2 border-left pl-3">
                            <button type="button" class="btn btn-secondary btn-sm" id="prevPage">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="pageInfo" class="mx-2">Page 1 of ?</span>
                            <button type="button" class="btn btn-secondary btn-sm" id="nextPage">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="border-left pl-3 mb-2">
                            <button type="button" class="btn btn-primary btn-sm mr-1" id="pdfZoomIn">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-1" id="pdfZoomReset">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="pdfZoomOut">
                                <i class="fas fa-search-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-center bg-dark" style="overflow: auto; max-height: 80vh;">
                        <canvas id="the-canvas" style="border: 1px solid black; direction: ltr;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script>
         document.addEventListener('DOMContentLoaded', function () {
                // === INPUT LOCK UNTIL START ===
                // Disable all form inputs in checksheetForm until Start button is clicked
                var formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
                formInputs.prop('disabled', true);
                $('#checksheetForm').addClass('inputs-locked');
                $('<style>#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');

                // --- PDF.js Logic ---
                pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";

                let pdfDoc = null;
                let pageNum = 1;
                let pageRendering = false;
                let pageNumPending = null;
                let scale = 1.0;
                const canvas = document.getElementById('the-canvas');
                const ctx = canvas.getContext('2d');

                function renderPage(num) {
                    pageRendering = true;
                    pdfDoc.getPage(num).then(function (page) {
                        const viewport = page.getViewport({ scale: scale });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };
                        const renderTask = page.render(renderContext);

                        renderTask.promise.then(function () {
                            pageRendering = false;
                            if (pageNumPending !== null) {
                                renderPage(pageNumPending);
                                pageNumPending = null;
                            }
                        });
                    });
                    document.getElementById('pageInfo').textContent = 'Page ' + num + ' of ' + pdfDoc.numPages;
                }

                function queueRenderPage(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                    } else {
                        renderPage(num);
                    }
                }

                document.getElementById('prevPage').addEventListener('click', function () {
                    if (pageNum <= 1) return;
                    pageNum--;
                    queueRenderPage(pageNum);
                });

                document.getElementById('nextPage').addEventListener('click', function () {
                    if (pageNum >= pdfDoc.numPages) return;
                    pageNum++;
                    queueRenderPage(pageNum);
                });

                document.getElementById('pdfZoomIn').addEventListener('click', function () {
                    scale += 0.25;
                    queueRenderPage(pageNum);
                });

                document.getElementById('pdfZoomOut').addEventListener('click', function () {
                    if (scale > 0.25) {
                        scale -= 0.25;
                        queueRenderPage(pageNum);
                    }
                });

                document.getElementById('pdfZoomReset').addEventListener('click', function () {
                    scale = 1.0;
                    queueRenderPage(pageNum);
                });

                let currentPdfIndex = 0;
                let totalPdfFiles = 0;
                let currentItemId = null;

                function loadPdf(itemId, index) {
                    // Use Laravel route helper to generate robust URL, replacing placeholders with actual values
                    const routePattern = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}";
                    const url = routePattern.replace('ID_PLACEHOLDER', itemId).replace('INDEX_PLACEHOLDER', index);

                    pdfDoc = null;
                    pageNum = 1;
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    document.getElementById('pageInfo').textContent = 'Loading...';

                    if (index === 'similar') {
                        document.getElementById('pdfInfo').textContent = 'Similar Part PDF';
                        $('#prevPdf, #nextPdf').hide();
                    } else {
                        document.getElementById('pdfInfo').textContent = `File ${index + 1} of ${totalPdfFiles}`;
                        $('#prevPdf, #nextPdf').show();
                    }

                    pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
                        pdfDoc = pdfDoc_;
                        document.getElementById('pageInfo').textContent = 'Page 1 of ' + pdfDoc.numPages;
                        renderPage(pageNum);
                    }, function (reason) {
                        console.error(reason);
                        let errorMsg = 'Error loading PDF. ';
                        if (reason.name === 'MissingPDFException') {
                            errorMsg += 'The PDF file could not be found on the server.';
                        } else {
                            errorMsg += reason.message || reason;
                        }
                        document.getElementById('pageInfo').textContent = 'Error: ' + reason.name;
                        alert(errorMsg);
                    });
                }

                document.getElementById('prevPdf').addEventListener('click', function () {
                    if (currentPdfIndex <= 0) return;
                    currentPdfIndex--;
                    loadPdf(currentItemId, currentPdfIndex);
                });

                document.getElementById('nextPdf').addEventListener('click', function () {
                    if (currentPdfIndex >= totalPdfFiles - 1) return;
                    currentPdfIndex++;
                    loadPdf(currentItemId, currentPdfIndex);
                });

                // Trigger PDF Modal from dynamic button (delegated event)
                $(document).on('click', '.view-pdf-btn', function () {
                    currentItemId = $(this).data('id');
                    var isSimilar = $(this).data('similar');

                    if (isSimilar) {
                        totalPdfFiles = 1;
                        currentPdfIndex = 'similar';
                    } else {
                        totalPdfFiles = $(this).data('count');
                        currentPdfIndex = 0;
                    }

                    // Show modal
                    $('#pdfModal').modal('show');

                    // Load PDF
                    loadPdf(currentItemId, currentPdfIndex);
                });

                // Trigger PDF Modal from dynamic button (delegated event) - Legacy support removed in replace

                // --- Existing Logic ---
                function getSampleSize(lotSize) {
                    if (lotSize >= 500001) return 1250;
                    if (lotSize >= 150001) return 800;
                    if (lotSize >= 35001) return 500;
                    if (lotSize >= 10001) return 315;
                    if (lotSize >= 3201) return 200;
                    if (lotSize >= 1201) return 125;
                    if (lotSize >= 501) return 80;
                    if (lotSize >= 281) return 50;
                    if (lotSize >= 151) return 32;
                    if (lotSize >= 20) return 20;
                    return lotSize;
                }

                // AQL 0.65 Standard Table (Acc/Rej Limits)
                function getAqlLimits(sampleSize) {
                    // Mapping based on standard AQL 0.65 (General Inspection Level II)
                    // The user requested details if LOT SIZE check exceeds standard.
                    // We interpret this as ensuring the logic handles any sample size correctly.
                    if (sampleSize >= 1250) return { acc: 14, rej: 15 };
                    if (sampleSize >= 800) return { acc: 10, rej: 11 };
                    if (sampleSize >= 500) return { acc: 7, rej: 8 };
                    if (sampleSize >= 315) return { acc: 5, rej: 6 };
                    if (sampleSize >= 200) return { acc: 3, rej: 4 };
                    if (sampleSize >= 125) return { acc: 2, rej: 3 };
                    if (sampleSize >= 80) return { acc: 1, rej: 2 };
                    // For smaller samples (20, 32, 50), AQL 0.65 is very strict (Acc 0, Rej 1)
                    if (sampleSize >= 20) return { acc: 0, rej: 1 };

                    // Fallback for very small custom samples
                    return { acc: 0, rej: 1 };
                }

                $('input[name="total_qty"]').on('input', function () {
                    var lotSize = parseInt($(this).val()) || 0;
                    var sampleSize = getSampleSize(lotSize);
                    $('input[name="sampling_qty"]').val(sampleSize).trigger('input');
                });

                function updateJudgment() {
                    var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
                    var ng = parseInt($('input[name="total_ng"]').val()) || 0;
                    var isDimensiInvalid = $('.is-invalid').length > 0;

                    // 1. Calculate Total OK
                    if (sampling >= ng) {
                        $('input[name="total_ok"]').val(sampling - ng);
                    } else {
                        $('input[name="total_ok"]').val(Math.max(0, sampling - ng));
                    }

                    // 2. Determine Acc/Rej Limits
                    var limits = getAqlLimits(sampling);
                    $('#acc_val').text(limits.acc);
                    $('#rej_val').text(limits.rej);
                    $('#aql_info').show();

                    // 3. Auto-Judgment Logic
                    var judgmentSelect = $('#judgmentSelect');
                    if (ng > 0 || sampling > 0 || isDimensiInvalid) { // Only judge if there is data
                        if (isDimensiInvalid || ng >= limits.rej) {
                            judgmentSelect.val('NG');
                            judgmentSelect.removeClass('text-success').addClass('text-danger');
                            // Lock OK (Pass) checkbox if Dimension is NG
                            $('#checkOK').prop('checked', false).prop('disabled', true);

                            //                    Auto-select Defect 'Dimensi'
                            autoAddDimensionDefect();
                        } else if (ng <= limits.acc) {
                            judgmentSelect.val('OK');
                            judgmentSelect.removeClass('text-danger').addClass('text-success');
                            // Unlock OK (Pass) checkbox if valid
                            $('#checkOK').prop('disabled', false);

                            // Auto-remove Defect 'Dimensi' if exists
                            autoRemoveDimensionDefect();
                        } else {
                            judgmentSelect.val('NG'); // Fail safe
                            judgmentSelect.removeClass('text-success').addClass('text-danger');
                            $('#checkOK').prop('checked', false).prop('disabled', true);
                        }
                    } else {
                        judgmentSelect.val('');
                        judgmentSelect.removeClass('text-success text-danger');
                        $('#checkOK').prop('disabled', false);
                    }

                    // Show/Hide Next Proses dropdown based on judgment
                    toggleNextProsesDropdown();
                }

                function toggleNextProsesDropdown() {
                    var judgment = $('#judgmentSelect').val();
                    if (judgment === 'NG') {
                        $('#nextProsesContainer').slideDown();
                    } else {
                        $('#nextProsesContainer').slideUp();
                        // Removed reset to prevent accidental clearing during auto-calculations
                    }
                }

                $('input[name="total_ng"], input[name="sampling_qty"]').on('input', function () {
                    updateJudgment();
                });

                // Also trigger on manual judgment change
                $('#judgmentSelect').on('change', function () {
                    toggleNextProsesDropdown();
                });

                function autoAddDimensionDefect() {
                    // Check if 'dimension' or 'Dimensi' is already selected
                    var alreadySelected = false;
                    $('.defect-select').each(function () {
                        var val = $(this).val();
                        var text = $(this).find('option:selected').text();
                        if (val === 'dimension' || text.toLowerCase() === 'dimensi') {
                            alreadySelected = true;
                            return false; // break
                        }
                    });

                    if (alreadySelected) return;

                    // Try to find an empty slot first
                    var targetSelect = null;
                    $('.defect-select').each(function () {
                        if ($(this).val() === '') {
                            targetSelect = $(this);
                            return false; // break
                        }
                    });

                    // If no empty slot, add a new row if possible
                    if (!targetSelect) {
                        if ($('.defect-row').length < 4) {
                            $('#addDefectBtn').trigger('click');
                            targetSelect = $('.defect-select').last();
                        } else {
                            // Full, maybe alert or just use the last one (overwrite)? No, safer to just notify or do nothing.
                            // If full and no empty, we can't add.
                            return;
                        }
                    }

                    if (targetSelect) {
                        // Try setting value 'dimension'
                        var options = targetSelect.find('option');
                        var foundVal = '';
                        options.each(function () {
                            if ($(this).val() === 'dimension' || $(this).text().toLowerCase() === 'dimensi') {
                                foundVal = $(this).val();
                                return false;
                            }
                        });

                        if (foundVal) {
                            targetSelect.val(foundVal).trigger('change');
                            // Removed focus to prevent interruption while typing dimension
                            // targetSelect.closest('.defect-row').find('.defect-qty').focus();
                        } else {
                            console.warn("Defect 'Dimensi' not found in options");
                        }
                    }
                }

                function autoRemoveDimensionDefect() {
                    $('.defect-select').each(function () {
                        var val = $(this).val();
                        var text = $(this).find('option:selected').text();

                        if (val === 'dimension' || text.toLowerCase() === 'dimensi') {
                            var row = $(this).closest('.defect-row');

                            // If it's the only row, reset it
                            if ($('.defect-row').length === 1) {
                                $(this).val('').trigger('change');
                                row.find('.defect-qty').val('');
                            } else {
                                // If multiple rows, remove this row
                                row.remove();
                                // Show add button if we dropped below limit
                                if ($('.defect-row').length < 4) {
                                    $('#addDefectBtn').show();
                                }
                            }
                        }
                    });
                    // Recalculate Total NG after removal/reset
                    calculateTotalNG();
                }

                // Store default defects for fallback
                var defaultDefects = [
                    { value: 'scratch', text: 'BARET' },
                    { value: 'silver', text: 'SILVER' },
                    { value: 'flow', text: 'FLOW' },
                    { value: 'flash', text: 'FLASH' },
                    { value: 'shoot_mold', text: 'SHOOT MOLD' },
                    { value: 'bending', text: 'BENDING' },
                    { value: 'sinkmark', text: 'SINKMARK' },
                    { value: 'dimension', text: 'Dimensi' }
                ];

                $('#itemSelect').change(function () {
                    var selectedOption = $(this).find('option:selected');
                    var imageUrl = selectedOption.data('image');
                    var files = selectedOption.data('files');
                    var itemId = selectedOption.val();
                    var name = selectedOption.data('name');
                    var description = selectedOption.data('description');
                    var defectsData = selectedOption.data('defects');

                    // PDFs for Side-by-Side
                    var standardPdf = selectedOption.data('standard');
                    var similarPdf = selectedOption.data('similar');

                    console.log("Selected Item:", itemId, "Standard:", standardPdf, "Similar:", similarPdf);

                    // Reset Reference View State
                    refStandardPdfDoc = null;
                    refStandardPageNum = 1;
                    refStandardFileIndex = 0;
                    refStandardFiles = selectedOption.data('files') || [];

                    refSimilarPdfDoc = null;
                    refSimilarPageNum = 1;

                    // Update Side-by-Side PDF Previews (Canvas based for Mobile)
                    if (standardPdf) {
                        if (window.renderPdfToCanvas) {
                            window.renderPdfToCanvas(standardPdf, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', 1);
                        } else {
                            console.error("renderPdfToCanvas not defined yet");
                        }
                    } else {
                        $('#standardPdfCanvas').addClass('d-none').hide();
                        $('#standardPdfPlaceholder').removeClass('d-none').addClass('d-flex').find('p').text('Standard PDF tidak tersedia');
                        $('.standard-nav-controls').hide();
                    }

                    if (similarPdf) {
                        if (window.renderPdfToCanvas) {
                            window.renderPdfToCanvas(similarPdf, 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', 1);
                        }
                        $('#similarStatusText').text('');
                    } else {
                        $('#similarPdfCanvas').addClass('d-none').hide();
                        $('#similarPdfPlaceholder').removeClass('d-none').addClass('d-flex');
                        $('#similarStatusText').text('Referral Similar Part tidak tersedia untuk item ini');
                        $('.similar-nav-controls').hide();
                    }

                    updateRefNavControls();

                    var container = $('#imageContainer');
                    var htmlContent = '';

                    if (files && files.length > 0) {
                        htmlContent += '<button type="button" class="btn btn-danger btn-sm view-pdf-btn mb-1" data-id="' + itemId + '" data-count="' + files.length + '"><i class="fas fa-file-pdf"></i> PDF (' + files.length + ')</button>';
                    }

                    if (imageUrl) {
                        htmlContent += '<img src="' + imageUrl + '" ' +
                            'style="max-width: 100px; max-height: 80px; border: 1px solid #dee2e6; cursor: pointer; display:block; margin: 0 auto;" ' +
                            'class="img-thumbnail" ' +
                            'data-toggle="modal" ' +
                            'data-target="#imageModal" ' +
                            'data-image="' + imageUrl + '" ' +
                            'data-title="' + name + '" ' +
                            'data-description="' + description + '">';
                    }

                    if ((!files || files.length === 0) && !imageUrl) {
                        htmlContent = '<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>';
                    }

                    // Use d-flex column if both exist
                    if (files && files.length > 0 && imageUrl) {
                        container.html('<div class="d-flex flex-column align-items-center">' + htmlContent + '</div>');
                    } else {
                        container.html(htmlContent);
                    }

                    // Update Defect Dropdown
                    // Reset to single row first
                    $('#defectContainer').html('<div class="input-group mb-2 defect-row">' +
                        '<select class="form-control defect-select" name="defect_types[]" id="defectSelect">' +
                        '<option value="">-- Pilih Defect --</option>' +
                        '</select>' +
                        '<input type="number" class="form-control defect-qty" name="defect_quantities[]" placeholder="Qty" min="1" style="max-width: 80px;">' +
                        '</div>');

                    var defectSelect = $('#defectSelect');
                    // defectSelect.empty(); // No need
                    // defectSelect.append('<option value="">-- Pilih Defect --</option>');

                    // Defensive parse: handle if defectsData is a string
                    if (typeof defectsData === 'string') {
                        try {
                            defectsData = JSON.parse(defectsData);
                        } catch (e) {
                            console.error('Error parsing defects data', e);
                            defectsData = [];
                        }
                    }

                    if (Array.isArray(defectsData) && defectsData.length > 0) {
                        // Use specific defects from item
                        $.each(defectsData, function (index, value) {
                            defectSelect.append('<option value="' + value + '">' + value + '</option>');
                        });
                    } else {
                        // Use default defects
                        $.each(defaultDefects, function (index, defect) {
                            defectSelect.append('<option value="' + defect.value + '">' + defect.text + '</option>');
                        });
                    }

                    // --- Dynamic Cavity & Point Logic (Karawang Only) ---
                    const cavityCount = selectedOption.data('cavity');

                    // Calculate Point Count from Dimension Standards
                    let pointCount = 5; // Default
                    const dimStandards = selectedOption.data('dimension-standards');
                    if (dimStandards) {
                        if (Array.isArray(dimStandards)) {
                            pointCount = dimStandards.length;
                        } else if (typeof dimStandards === 'object') {
                            const keys = Object.keys(dimStandards).map(k => parseInt(k));
                            if (keys.length > 0) {
                                pointCount = Math.max(...keys);
                            }
                        }
                    }

                    if (currentPlant === 'karawang') {
                        // Update rows AND columns
                        updateCavityRows(cavityCount || 1, pointCount);
                        toggleManualCavityButtons(true);
                    } else {
                        // Start with default 2 if not Karawang or no cavity data,
                        // BUT only if we want to reset.
                        // For now, if not Karawang, we leave it as is or reset to default?
                        // Let's stick to: if not Karawang, do nothing (retain existing behavior/rows)
                        if (currentPlant === 'karawang') {
                            // usage case: item has no cavity set? Default to 1 or 2?
                            // If item has no cavity data, maybe fall back to manual?
                            updateCavityRows(1); // Default to 1 if undefined for Karawang?
                            toggleManualCavityButtons(true);
                        }
                    }
                    // ---------------------------------------------

                    calculateTotalNG();
                });

                // Initial Cavity Generation Logic (Karawang Only)
                // Pass PHP plant variable to JS
                const currentPlant = '{{ request('plant') ?? auth()->user()->plant_id }}'; // Or use a cleaner way if available

                function updateCavityRows(cavityCount, pointCount = 5) {
                    // Only run this logic if plant is Karawang
                    if (currentPlant !== 'karawang') {
                        return;
                    }

                    const tbody = $('#dimensionBody');
                    const theadRow = $('#dimensionHeadRow');
                    tbody.empty(); // Clear existing rows

                    // --- Update Header ---
                    let headerHtml = '<th style="min-width: 100px; position: sticky; left: 0; z-index: 2; background: #f8f9fa;">Cavity</th>';

                    for (let j = 1; j <= pointCount; j++) {
                        headerHtml += `<th class="point-header">Point ${j}</th>`;
                    }
                    theadRow.html(headerHtml);

                    // --- Generate Rows ---
                    for (let i = 1; i <= cavityCount; i++) {
                        let rowHtml = `<tr class="cavity-row" data-cavity="${i}">`;
                        rowHtml += `<td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${i}</td>`;
                        for (let j = 1; j <= pointCount; j++) {
                            rowHtml += `<td class="point-cell">
                                                                                    <input type="text"
                                                                                        class="form-control form-control-sm dimension-input"
                                                                                        style="min-width: 60px;" name="dimensions[${i}][${j}]"
                                                                                        placeholder="P${j}">
                                                                                </td>`;
                        }
                        rowHtml += `</tr>`;
                        tbody.append(rowHtml);
                    }

                    // Update global counter
                    currentCavities = cavityCount;

                    // Re-bind events if necessary (e.g. input validation)
                    // Since we use delegated events (on document or table), specific re-binding might not be needed
                    // IF the validation uses $(document).on('change', '.dimension-input', ...)
                }

                // Function to toggle Manual Add/Delete buttons
                function toggleManualCavityButtons(isDynamic) {
                    if (currentPlant !== 'karawang') {
                        // For non-Karawang, always show buttons (or leave as is)
                        $('#addCavityBtn').show();
                        $('#deleteCavityBtn').show();
                        $('#addPointBtn').show();
                        $('#deletePointBtn').show();
                        return;
                    }

                    if (isDynamic) {
                        $('#addCavityBtn').hide();
                        $('#deleteCavityBtn').hide();
                        $('#addPointBtn').hide();
                        $('#deletePointBtn').hide();
                    } else {
                        $('#addCavityBtn').show();
                        $('#deleteCavityBtn').show();
                        $('#addPointBtn').show();
                        $('#deletePointBtn').show();
                    }
                }

                $('#sapCodeInput').on('input', function () {
                    var sapCode = $(this).val().trim();

                    if (sapCode.length >= 1) {
                        // Find matching item by SAP code
                        var matchedOption = $('#itemSelect option').filter(function () {
                            var itemSapCode = $(this).data('sap-code');
                            return itemSapCode && itemSapCode.toString().toLowerCase() === sapCode.toLowerCase();
                        });

                        if (matchedOption.length > 0) {
                            // Auto-select the matched item
                            $('#itemSelect').val(matchedOption.val()).trigger('change');
                            // Visual feedback
                            $('#sapCodeInput').removeClass('is-invalid').addClass('is-valid');
                        } else {
                            // No match found
                            $('#sapCodeInput').removeClass('is-valid').addClass('is-invalid');
                        }
                    } else {
                        // Clear validation classes when input is empty
                        $('#sapCodeInput').removeClass('is-valid is-invalid');
                    }
                });

                // Zoom Logic (Image)
                var currentZoom = 1;
                var zoomStep = 0.25;

                function updateZoom() {
                    $('#modalImage').css('transform', 'scale(' + currentZoom + ')');
                    if (currentZoom > 1) {
                        $('#modalImage').css('transform-origin', 'top center');
                    } else {
                        $('#modalImage').css('transform-origin', 'center center');
                    }
                }

                $('#zoomIn').click(function () {
                    currentZoom += zoomStep;
                    updateZoom();
                });

                $('#zoomOut').click(function () {
                    if (currentZoom > zoomStep) {
                        currentZoom -= zoomStep;
                        updateZoom();
                    }
                });

                $('#zoomReset').click(function () {
                    currentZoom = 1;
                    updateZoom();
                });

                $('#imageModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget);
                    var image = button.data('image');
                    var title = button.data('title');
                    var description = button.data('description');

                    var modal = $(this);
                    modal.find('#modalImage').attr('src', image);
                    modal.find('#modalTitle').text(title);
                    modal.find('#modalDescription').text(description);
                    currentZoom = 1;
                    updateZoom();
                });

                $('#checkOK').change(function () {
                    if ($(this).is(':checked')) {
                        $('select[name="judgment"]').val('OK').trigger('change');
                        // We don't clear defect selects here anymore because user might want to log 'minor' defects even if overall OK
                        // OR more likely, if OK, there are no defects. But let's leave that to user discretion or specific requirement.
                        // The previous code cleared #defectSelect. With multiple rows, clearing all might be annoying if accidental click.
                        // For now, removing the clearing logic or just clearing the first one?
                        // Let's stick to minimal interference: just set Judgment OK.
                    }
                });

                // Add Defect Button Logic
                $('#addDefectBtn').click(function () {
                    var rowCount = $('.defect-row').length;
                    if (rowCount < 4) {
                        var firstSelect = $('#defectSelect'); // The original one
                        var newRow = $('<div class="input-group mb-2 defect-row">' +
                            '<select class="form-control defect-select" style="min-width: 180px;" name="defect_types[]">' +
                            firstSelect.html() +
                            '</select>' +
                            '<input type="number" class="form-control defect-qty" style="min-width: 100px;" name="defect_quantities[]" placeholder="Qty" min="1">' +
                            '<div class="input-group-append">' +
                            '<button class="btn btn-danger btn-sm remove-defect-btn" type="button"><i class="fas fa-minus"></i></button>' +
                            '</div>' +
                            '</div>');
                        $('#defectContainer').append(newRow);
                    }
                    if ($('.defect-row').length >= 4) {
                        $(this).hide();
                    }
                });

                // Calculate Total NG from Defect Quantities
                function calculateTotalNG() {
                    var total = 0;
                    $('.defect-qty').each(function () {
                        var qty = parseInt($(this).val()) || 0;
                        total += qty;
                    });
                    $('input[name="total_ng"]').val(total).trigger('input');
                }

                // Listener for defect qty changes
                $(document).on('input', '.defect-qty', function () {
                    calculateTotalNG();
                });

                // Remove Defect Button Logic
                $(document).on('click', '.remove-defect-btn', function () {
                    $(this).closest('.defect-row').remove();
                    calculateTotalNG();
                    if ($('.defect-row').length < 4) {
                        $('#addDefectBtn').show();
                    }
                });

                // Toggle "Add Defect" button based on NG count >= 1
                $('input[name="total_ng"]').on('input', function () {
                    var ng = parseInt($(this).val()) || 0;
                    if (ng >= 1) {
                        if ($('.defect-row').length < 4) {
                            $('#addDefectBtn').show();
                        }
                    } else {
                        $('#addDefectBtn').hide();
                    }
                });

                // --- Timer Logic (Cycle Time) ---
                var timerInterval = null;
                var totalSeconds = 0;
                var timerRunning = false;

                function updateTimerDisplay() {
                    var hours = Math.floor(totalSeconds / 3600);
                    var minutes = Math.floor((totalSeconds % 3600) / 60);
                    var seconds = totalSeconds % 60;

                    var text =
                        (hours < 10 ? "0" + hours : hours) + ":" +
                        (minutes < 10 ? "0" + minutes : minutes) + ":" +
                        (seconds < 10 ? "0" + seconds : seconds);

                    $('#timerDisplay').text(text);
                    $('#cycleTimeInput').val(totalSeconds);
                }

                $('#startTimerBtn').click(function () {
                    if (!timerRunning) {
                        timerRunning = true;
                        $(this).removeClass('btn-success').addClass('btn-secondary').attr('disabled', true).html('<i class="fas fa-clock"></i> Running...');

                        // === UNLOCK ALL INPUTS ===
                        formInputs.prop('disabled', false);
                        $('#checksheetForm').removeClass('inputs-locked');
                        $('#saveBtn').prop('disabled', false);

                        timerInterval = setInterval(function () {
                            totalSeconds++;
                            updateTimerDisplay();
                        }, 1000);
                    }
                });

                // Handle form submission via AJAX
                $('#checksheetForm').on('submit', function (e) {
                    e.preventDefault();

                    // Validate: If NG, next_proses must be selected
                    var judgment = $('#judgmentSelect').val();
                    var nextProses = $('#nextProses').val();

                    if (judgment === 'NG' && !nextProses) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Next Proses Wajib Dipilih',
                            text: 'Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!',
                            confirmButtonColor: '#3085d6'
                        });

                        // Specific highlight
                        var $nextProses = $('#nextProses');
                        $nextProses.addClass('is-invalid').focus();
                        setTimeout(function () {
                            $nextProses.removeClass('is-invalid');
                        }, 3000);

                        return false;
                    }

                    // Validate Mandatory Dimensions
                    if (!checkMandatoryDimensions()) {
                        return false;
                    }

                    // Validate Defect Qty if Dimension defect is selected
                    var dimensionDefectSelected = false;
                    var dimensionQtyEmpty = false;
                    $('.defect-select').each(function () {
                        var val = $(this).val();
                        var text = $(this).find('option:selected').text();
                        if (val === 'dimension' || text.toLowerCase() === 'dimensi') {
                            dimensionDefectSelected = true;
                            var qtyInput = $(this).closest('.defect-row').find('.defect-qty');
                            if (!qtyInput.val() || parseInt(qtyInput.val()) <= 0) {
                                dimensionQtyEmpty = true;
                                qtyInput.addClass('is-invalid');
                            } else {
                                qtyInput.removeClass('is-invalid');
                            }
                        }
                    });

                    if (dimensionDefectSelected && dimensionQtyEmpty) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Qty Defect Dimensi Wajib Diisi',
                            text: 'Karena ditemukan NG Dimensi, anda wajib mengisi Qty pada Defect List!',
                            confirmButtonColor: '#3085d6'
                        });
                        return false;
                    }

                    if (timerRunning) {
                        clearInterval(timerInterval);
                        timerRunning = false;
                        // Update final value
                        $('#cycleTimeInput').val(totalSeconds);
                    }

                    // Show loading state
                    var saveBtn = $('#saveBtn');
                    var originalHtml = saveBtn.html();
                    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                    var formData = new FormData(this);

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            $('#global-loader').hide();
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Data Berhasil Disimpan',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Lihat Data',
                                    cancelButtonText: 'Tutup',
                                    reverseButtons: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = response.index_url;
                                    } else {
                                        // Reset Form & Re-lock
                                        $('#checksheetForm')[0].reset();
                                        resetState();
                                    }
                                });
                            }
                        },
                        error: function (xhr) {
                            $('#global-loader').hide();
                            var errorMsg = 'Gagal menyimpan data.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                            saveBtn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });



                function resetState() {
                    clearInterval(timerInterval);
                    timerRunning = false;
                    totalSeconds = 0;
                    updateTimerDisplay();
                    $('#startTimerBtn').removeClass('btn-secondary').addClass('btn-success').removeAttr('disabled').html('<i class="fas fa-play"></i> Start');

                    // RE-LOCK INPUTS
                    formInputs.prop('disabled', true);
                    $('#checksheetForm').addClass('inputs-locked');
                    $('#saveBtn').prop('disabled', true);
                    $('#addDefectBtn').hide();
                    $('.defect-row').not(':first').remove();

                    // Clear images/standard info
                    $('#imageContainer').html('<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');

                    // Reset PDF views
                    $('#standardPdfCanvas, #similarPdfCanvas').hide();
                    $('#standardPdfPlaceholder').show().find('p').text('Pilih Item untuk menampilkan Standard PDF');
                    $('#similarPdfPlaceholder').show().find('p').text('Pilih Item untuk menampilkan Similar Part');
                    $('#similarStatusText').text('');
                    $('#fullStandardBtn, #fullSimilarBtn').hide();
                    $('.standard-nav-controls, .similar-nav-controls').hide();

                    // Reset PDF Reference State
                    refStandardPdfDoc = null;
                    refStandardPageNum = 1;
                    refStandardFileIndex = 0;
                    refStandardFiles = [];
                    refSimilarPdfDoc = null;
                    refSimilarPageNum = 1;

                    // Reset select2 if used (standard select used here)
                    $('#itemSelect').val('').trigger('change');
                }

                // Optional: Reset timer on form reset
                $('button[type="reset"]').click(function () {
                    resetState();
                });

                // --- Centralized Dimension Validation Logic ---
                // The dimension standards are now passed from the controller.
                const partDimensionStandards = JSON.parse('{!! $partDimensionStandards !!}');


                function normalizePartNumber(pn) {
                    if (!pn) return '';
                    return pn.toString()
                        .replace(/[\u2012\u2013\u2014\u2212]/g, '-') // EN, EM, FIGURE DASH, MINUS
                        .replace(/\s+/g, '') // Remove all whitespace
                        .toUpperCase();
                }

                function validateDimensions() {
                    const selectedOption = $('#itemSelect').find('option:selected');
                    const rawPartNumber = selectedOption.data('part-number');
                    const itemPartNumber = normalizePartNumber(rawPartNumber);

                    // Get the dimension standards for the currently selected item.
                    const dimensionStandards = partDimensionStandards[itemPartNumber];

                    $('input[name^="dimensions"]').each(function () {
                        const name = $(this).attr('name');
                        // Extracts the point number from the input name (e.g., dimensions[1][2] -> '2').
                        const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                        if (!match) return;

                        const point = match[2]; // The point number (e.g., '1', '2', '3').
                        // Look up the standard for the current point for the selected part.
                        const standard = dimensionStandards ? dimensionStandards[point] : null;
                        const valStr = $(this).val().trim();
                        const value = parseFloat(valStr.replace(',', '.')); // Handle comma decimals

                        // Check if a standard exists for this point and the input is a valid number.
                        if (standard && valStr !== '' && !isNaN(value)) {
                            let isInvalid = false;

                            if (standard.min !== null && value < standard.min) {
                                isInvalid = true;
                            }
                            if (standard.max !== null && value > standard.max) {
                                isInvalid = true;
                            }

                            // Fallback to Size +/- Tolerance if no Min/Max is set at all
                            if (standard.min === null && standard.max === null) {
                                if (standard.size !== null && standard.tolerance !== null) {
                                    const lowerBound = standard.size - standard.tolerance;
                                    const upperBound = standard.size + standard.tolerance;
                                    if (value < lowerBound || value > upperBound) {
                                        isInvalid = true;
                                    }
                                }
                            }

                            if (isInvalid) {
                                $(this).addClass('is-invalid');
                            } else {
                                $(this).removeClass('is-invalid');
                            }
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    // Trigger judgment update to reflect dimension status
                    updateJudgment();
                }

                function checkMandatoryDimensions() {
                    const selectedOption = $('#itemSelect').find('option:selected');
                    const rawPartNumber = selectedOption.data('part-number');
                    const itemPartNumber = normalizePartNumber(rawPartNumber);
                    const dimensionStandards = partDimensionStandards[itemPartNumber];

                    if (!dimensionStandards) return true; // No standards, no mandatory checks
                    if (currentPlant === 'jakarta') return true; // Jakarta allows manual/partial input

                    let allFilled = true;
                    let firstEmptyInput = null;

                    // Loop through all visible dimension inputs
                    $('.dimension-input').each(function () {
                        const name = $(this).attr('name');
                        const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                        if (!match) return;

                        const point = match[2];
                        // Only check if a standard exists for this point
                        if (dimensionStandards[point]) {
                            const val = $(this).val().trim();
                            if (val === '') {
                                allFilled = false;
                                $(this).addClass('is-invalid');
                                if (!firstEmptyInput) firstEmptyInput = $(this);
                            }
                        }
                    });

                    if (!allFilled) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Dimensi Belum Lengkap',
                            text: 'Mohon isi semua kolom dimensi yang memiliki standar!',
                            confirmButtonColor: '#3085d6'
                        });
                        if (firstEmptyInput) {
                            $('html, body').animate({
                                scrollTop: firstEmptyInput.offset().top - 200
                            }, 500);
                            firstEmptyInput.focus();
                        }
                        return false;
                    }
                    return true;
                }

                // --- Dynamic Dimension Expansion Logistic ---
                let currentCavities = 2;
                let currentPoints = 5;
                const maxCavities = 30;
                const maxPoints = 30;

                $('#addCavityBtn').click(function () {
                    if (currentCavities < maxCavities) {
                        currentCavities++;
                        let newRow = `<tr class="cavity-row" data-cavity="${currentCavities}">
                                                                                                                                                                            <td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${currentCavities}</td>`;

                        for (let j = 1; j <= currentPoints; j++) {
                            newRow += `<td class="point-cell">
                                                                                                                                                                                <input type="text" class="form-control form-control-sm dimension-input" 
                                                                                                                                                                                    style="min-width: 60px;"
                                                                                                                                                                                    name="dimensions[${currentCavities}][${j}]" 
                                                                                                                                                                                    placeholder="P${j}">
                                                                                                                                                                            </td>`;
                        }
                        newRow += `</tr>`;
                        $('#dimensionBody').append(newRow);
                    } else {
                        alert('Maximum 30 cavities reached');
                    }
                });

                $('#deleteCavityBtn').click(function () {
                    if (currentCavities > 1) {
                        $('#dimensionBody tr:last-child').remove();
                        currentCavities--;
                        updateJudgment();
                    }
                });

                $('#addPointBtn').click(function () {
                    if (currentPoints < maxPoints) {
                        currentPoints++;
                        // Add header
                        $('#dimensionHeadRow').append(`<th class="point-header">Point ${currentPoints}</th>`);

                        // Add cells to each row
                        $('.cavity-row').each(function () {
                            let cavityNum = $(this).data('cavity');
                            $(this).append(`<td class="point-cell">
                                                                                                                                                                                <input type="text" class="form-control font-control-sm dimension-input" 
                                                                                                                                                                                    style="min-width: 60px;"
                                                                                                                                                                                    name="dimensions[${cavityNum}][${currentPoints}]" 
                                                                                                                                                                                    placeholder="P${currentPoints}">
                                                                                                                                                                            </td>`);
                        });
                    } else {
                        alert('Maximum 30 points reached');
                    }
                });

                $('#deletePointBtn').click(function () {
                    if (currentPoints > 1) {
                        // Remove last header
                        $('#dimensionHeadRow th.point-header:last-child').remove();
                        // Remove last cell from each row
                        $('.cavity-row').each(function () {
                            $(this).find('td.point-cell:last-child').remove();
                        });
                        currentPoints--;
                        updateJudgment();
                    }
                });

                $(document).on('input', '.dimension-input', validateDimensions);

                // --- PDF Cache & Render Logic (Global/Robust) ---
                const pdfCache = {};

                // State for Reference Views
                let refStandardPdfDoc = null;
                let refStandardPageNum = 1;
                let refStandardFileIndex = 0;
                let refStandardFiles = [];

                let refSimilarPdfDoc = null;
                let refSimilarPageNum = 1;

                // Ensure function is available globally or within this closure safely
                window.renderPdfToCanvas = function (url, canvasId, placeholderId, loadingId, pageNum = 1) {
                    const canvas = document.getElementById(canvasId);
                    const ctx = canvas.getContext('2d');
                    const $placeholder = $('#' + placeholderId);
                    const $loading = $('#' + loadingId);
                    const $canvas = $(canvas);

                    // Reset UI: Show Loading, Hide others
                    $placeholder.removeClass('d-flex').addClass('d-none');
                    $canvas.addClass('d-none').hide();
                    $loading.removeClass('d-none').addClass('d-flex');

                    console.log("Starting render for:", url, "Page:", pageNum);

                    // Check Cache first
                    if (pdfCache[url]) {
                        renderPageOnCanvas(pdfCache[url], canvas, ctx, $loading, $canvas, pageNum, canvasId);
                        return;
                    }

                    pdfjsLib.getDocument(url).promise.then(function (pdf) {
                        pdfCache[url] = pdf; // Store in cache
                        renderPageOnCanvas(pdf, canvas, ctx, $loading, $canvas, pageNum, canvasId);
                    }).catch(function (error) {
                        console.error('Error rendering preview PDF:', error);
                        $loading.removeClass('d-flex').addClass('d-none');
                        $placeholder.removeClass('d-none').addClass('d-flex').find('p').text('Gagal memuat PDF: ' + (error.message || 'Unknown error'));
                    });
                };

                function renderPageOnCanvas(pdf, canvas, ctx, $loading, $canvas, pageNum, canvasId) {
                    pdf.getPage(pageNum).then(function (page) {
                        const containerWidth = $(canvas).parent().width() || 500;
                        // Subtract more padding (40px) to ensure no scrollbar and fit comfortably
                        const availableWidth = containerWidth - 40;
                        const viewport = page.getViewport({ scale: 1.0 });
                        const scale = availableWidth / viewport.width;
                        const scaledViewport = page.getViewport({ scale: scale });

                        canvas.height = scaledViewport.height;
                        canvas.width = scaledViewport.width;

                        // Force CSS to fit container
                        $canvas.css('width', '100%');
                        $canvas.css('height', 'auto');

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: scaledViewport
                        };

                        page.render(renderContext).promise.then(function () {
                            $loading.removeClass('d-flex').addClass('d-none');
                            $canvas.removeClass('d-none').show();

                            // Update info labels if this is a reference canvas
                            if (canvasId === 'standardPdfCanvas') {
                                refStandardPdfDoc = pdf;
                                $('#standardPageInfo').text('P ' + pageNum + '/' + pdf.numPages);
                            } else if (canvasId === 'similarPdfCanvas') {
                                refSimilarPdfDoc = pdf;
                                $('#similarPageInfo').text('P ' + pageNum + '/' + pdf.numPages);
                            }
                        });
                    }).catch(function (err) {
                        console.error("Error rendering page:", pageNum, err);
                        $loading.removeClass('d-flex').addClass('d-none');
                    });
                }

                function updateRefNavControls() {
                    // Standard
                    if (refStandardFiles && refStandardFiles.length > 0) {
                        $('.standard-nav-controls').attr('style', 'display: flex !important;');
                        if (refStandardFiles.length > 1) {
                            $('.standard-nav-controls .file-nav').attr('style', 'display: flex !important;');
                            $('#standardFileInfo').text((refStandardFileIndex + 1) + '/' + refStandardFiles.length);
                        } else {
                            $('.standard-nav-controls .file-nav').hide();
                        }
                    } else {
                        $('.standard-nav-controls').hide();
                    }

                    // Similar
                    if (refSimilarPdfDoc) {
                        $('.similar-nav-controls').attr('style', 'display: flex !important;');
                    } else {
                        $('.similar-nav-controls').hide();
                    }
                }

                // Reference View Navigation Events
                $('#prevStandardPage').click(function () {
                    if (refStandardPageNum > 1) {
                        refStandardPageNum--;
                        renderPageOnCanvas(refStandardPdfDoc, document.getElementById('standardPdfCanvas'), document.getElementById('standardPdfCanvas').getContext('2d'), $('#standardPdfLoading'), $('#standardPdfCanvas'), refStandardPageNum, 'standardPdfCanvas');
                    }
                });

                $('#nextStandardPage').click(function () {
                    if (refStandardPdfDoc && refStandardPageNum < refStandardPdfDoc.numPages) {
                        refStandardPageNum++;
                        renderPageOnCanvas(refStandardPdfDoc, document.getElementById('standardPdfCanvas'), document.getElementById('standardPdfCanvas').getContext('2d'), $('#standardPdfLoading'), $('#standardPdfCanvas'), refStandardPageNum, 'standardPdfCanvas');
                    }
                });

                $('#prevStandardFile').click(function () {
                    if (refStandardFileIndex > 0) {
                        refStandardFileIndex--;
                        refStandardPageNum = 1;
                        const itemId = $('#itemSelect').val();
                        const url = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
                            .replace('ID_PLACEHOLDER', itemId)
                            .replace('INDEX_PLACEHOLDER', refStandardFileIndex);
                        window.renderPdfToCanvas(url, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', 1);
                        updateRefNavControls();
                    }
                });

                $('#nextStandardFile').click(function () {
                    if (refStandardFileIndex < refStandardFiles.length - 1) {
                        refStandardFileIndex++;
                        refStandardPageNum = 1;
                        const itemId = $('#itemSelect').val();
                        const url = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
                            .replace('ID_PLACEHOLDER', itemId)
                            .replace('INDEX_PLACEHOLDER', refStandardFileIndex);
                        window.renderPdfToCanvas(url, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', 1);
                        updateRefNavControls();
                    }
                });

                $('#prevSimilarPage').click(function () {
                    if (refSimilarPageNum > 1) {
                        refSimilarPageNum--;
                        renderPageOnCanvas(refSimilarPdfDoc, document.getElementById('similarPdfCanvas'), document.getElementById('similarPdfCanvas').getContext('2d'), $('#similarPdfLoading'), $('#similarPdfCanvas'), refSimilarPageNum, 'similarPdfCanvas');
                    }
                });

                $('#nextSimilarPage').click(function () {
                    if (refSimilarPdfDoc && refSimilarPageNum < refSimilarPdfDoc.numPages) {
                        refSimilarPageNum++;
                        renderPageOnCanvas(refSimilarPdfDoc, document.getElementById('similarPdfCanvas'), document.getElementById('similarPdfCanvas').getContext('2d'), $('#similarPdfLoading'), $('#similarPdfCanvas'), refSimilarPageNum, 'similarPdfCanvas');
                    }
                });

                // Force trigger change if item is already selected (e.g. browser cache or default)
                setTimeout(function () {
                    if ($('#itemSelect').val()) {
                        $('#itemSelect').trigger('change');
                    }
                }, 500);

                // Add CSS for invalid inputs
                $('<style>' +
                    '.is-invalid { border-color: #dc3545 !important; background-color: #f8d7da !important; }' +
                    '.btn-xs { padding: 1px 5px; font-size: 12px; line-height: 1.5; border-radius: 3px; }' +
                    '</style>').appendTo('head');
            });
        </script>
@endpush