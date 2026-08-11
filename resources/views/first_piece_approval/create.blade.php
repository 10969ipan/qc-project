@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

@section('content')
<style>
    /* ─── Create Form Table: Minimalist Industrial (selaras dengan index.blade.php) ─── */
    #checksheetTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: 1px solid #e2e8f0 !important;
        width: 100% !important;
        table-layout: auto !important;
    }

    #checksheetTable td, #checksheetTable th {
        border: 1px solid #e2e8f0 !important;
    }

    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 105 !important;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 8px 12px !important;
        border: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
    }

    #checksheetTable > tbody > tr > td {
        border: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.8rem !important;
        padding: 8px 10px !important;
    }

    #checksheetTable .form-control {
        font-size: 0.78rem !important;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background-color: #f8fafc;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #checksheetTable .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        background-color: #fff;
    }

    #checksheetTable .btn {
        font-size: 0.7rem !important;
        padding: 0.25rem 0.5rem !important;
    }

    /* ─── Inner Dimension Table ─── */
    #dimensionTable,
    #checksheetTable .table-sm {
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 0 !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
    }
    #dimensionTable td,
    #dimensionTable th {
        background-color: transparent !important;
        border: 1px solid #e2e8f0 !important;
        padding: 4px 6px !important;
        text-align: center !important;
        font-size: 0.68rem !important;
    }
    #dimensionTable thead th {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.58rem !important;
        border-bottom: 2px solid #cbd5e1 !important;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    #dimensionTable tbody td {
        color: #1e293b !important;
        font-size: 0.65rem !important;
    }
    #dimensionTable .dimension-input {
        font-size: 0.68rem !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 4px !important;
        background: #f8fafc !important;
        text-align: center;
        padding: 2px 4px !important;
        min-width: 55px;
    }
    #dimensionTable .dimension-input:focus {
        border-color: #6366f1 !important;
        background: #fff !important;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.1) !important;
    }

    /* ─── OK/NG Labels ─── */
    .ok-label  { background-color: #16a34a; color: #fff; padding: 0.25rem 0.5rem; border-radius: 4px 0 0 4px; font-size: 0.7rem; font-weight: bold; }
    .ng-label  { background-color: #dc2626; color: #fff; padding: 0.25rem 0.5rem; border-radius: 4px 0 0 4px; font-size: 0.7rem; font-weight: bold; }

    /* ─── Judgment Badge ─── */
    #judgmentBadge {
        font-size: 1rem !important;
        letter-spacing: 0.5px;
    }

    /* ─── Card header style ─── */
    .card-header h6.text-primary {
        font-size: 0.78rem;
        letter-spacing: 0.3px;
        font-weight: 700;
        text-transform: uppercase;
        color: #3b5bdb !important;
    }

</style>
    @php
        $plant = $plant ?? request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
    @endphp
        @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('first_piece_approval', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
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
                            CHECK SHEET FIRST PIECE APPROVAL
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
    @php
        $plantCode = strtolower(request('plant') ?? (auth()->user()->plant ? auth()->user()->plant->code : ''));
        $jakartaMachineNumbers = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
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

    <div class="card shadow mb-4">
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet First Piece Approval</h6>
        </div>
        <div class="card-body">


            <form action="{{ route('first_piece_approval.store') }}" method="POST" id="checksheetForm" novalidate>
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <input type="hidden" name="qrcode" id="qrcodeInput">
                <input type="hidden" name="part_code" id="partCodeInput">
                <input type="hidden" name="supplier_id" id="supplierIdInput">
                <input type="hidden" name="quantity" id="quantityInput">
                <input type="hidden" name="unique_code_id" id="uniqueCodeInput">
                <input type="hidden" name="sap_code" id="sapCodeInputHidden">
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <div class="table-responsive" style="overflow-x: auto; border: none; box-shadow: inset 0 0 5px rgba(0,0,0,0.02);">
                    <table class="table" id="checksheetTable" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2" class="align-middle">Item Part</th>
                                <th rowspan="2" class="align-middle">Tanggal / Shift</th>
                                <th rowspan="2" class="align-middle">Total Qty</th>
                                <th rowspan="2" class="align-middle">Sampling Qty</th>
                                <th rowspan="2" class="align-middle">Check Dimensi</th>
                                <th rowspan="2" class="align-middle col-berat-part" style="display: none; min-width: 280px;">
                                    Berat Part</th>
                                <th rowspan="2" class="align-middle" style="min-width: 280px;">Jenis (OK/NG) &amp; Detail NG</th>
                                <th rowspan="2" class="align-middle">Total (OK/NG)</th>
                                <th rowspan="2" class="align-middle">Judgment</th>
                                <th rowspan="2" class="align-middle">Inisial QC</th>
                                <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            </tr>
                            <tr></tr>
                        </thead>
                        <tbody>
                            <tr>

                                <!-- Pilihan Barang -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="font-weight-bold mb-0">Kode SAP</label>
                                        </div>
                                        <input type="text" class="form-control" id="sapCodeInput"
                                            placeholder="Ketik Kode SAP..." style="min-width: 200px;">
                                        <small class="text-muted">Auto-select item berdasarkan SAP code</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part</label>
                                        <select class="form-control" name="item_id" id="itemSelect"
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
                                                    data-sap_code="{{ $item->sap_code ?? '' }}"
                                                    data-cavity="{{ $item->cavity }}" data-customer="{{ $item->customer }}"
                                                    data-weight-standard="{{ $item->weight_standard }}"
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
                                            value="{{ $defaultDate }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="sr-only">Shift</label>
                                        <select class="form-control" style="min-width: 80px;" name="shift">
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
                                            style="min-width: 80px;">
                                            <option value="">Pilih Mesin</option>
                                            @foreach($machineNumbers as $num)
                                                <option value="{{ $num }}">Mesin {{ $num }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mt-2 mb-0">
                                        <label class="sr-only">Kategori</label>
                                        <select name="category" id="categoryInput" class="form-control"
                                            style="min-width: 100px;" required>
                                            <option value="">Pilih Kategori</option>
                                            @foreach(($fpaCategories ?? []) as $cat)
                                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>
                                                    {{ ucfirst($cat) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Kualitas (Total Kuantitas diproduksi) -->
                                <td class="align-middle">
                                    <input type="number" id="total_qty" class="form-control text-center" style="min-width: 60px;"
                                        name="total_qty" placeholder="0" min="0">
                                </td>

                                <!-- Sampling Qty -->
                                <td class="align-middle">
                                    <input type="number" id="sampling_qty" class="form-control text-center" style="min-width: 60px;"
                                        name="sampling_qty" placeholder="0" min="0">
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

                                {{-- Berat Part (Kondisional) --}}
                                <td class="align-middle col-berat-part" style="display: none; min-width: 280px;">
                                    <div class="px-2">
                                        {{-- Baris kontrol --}}
                                        <div class="d-flex align-items-center mb-2" style="gap:4px;">
                                            <button type="button" id="addWeightCavBtn" title="Tambah Cavity"
                                                style="width:22px;height:22px;border-radius:50%;border:none;background:#4e73df;color:#fff;font-size:15px;line-height:22px;padding:0;cursor:pointer;text-align:center;">+</button>
                                            <button type="button" id="removeWeightCavBtn" title="Hapus Cavity"
                                                style="width:22px;height:22px;border-radius:50%;border:1px solid #ccc;background:#fff;color:#aaa;font-size:15px;line-height:20px;padding:0;cursor:pointer;text-align:center;">−</button>
                                        </div>
                                        {{-- Baris per-cavity (disuntikkan oleh JS) --}}
                                        <div id="weightCavContainer"></div>
                                        {{-- Badge Standar --}}
                                        <div class="mt-1">
                                            <span id="weightStandardBadge" class="text-muted"
                                                style="display:none; font-size:0.7rem;">Std: <span
                                                    id="weightStandardDisplay">-</span> gr.</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="align-middle" style="min-width: 280px;">
                                    <hr class="my-2">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="row no-gutters mb-2 defect-row align-items-center">
                                            <div class="col-8 pr-1">
                                                <select class="form-control defect-select font-weight-bold"
                                                    name="defect_types[]" id="defectSelect">
                                                    <option value="">-- Pilih Defect --</option>
                                                </select>
                                            </div>
                                            <div class="col-3 pr-1">
                                                <input type="number" class="form-control defect-qty text-center font-weight-bold"
                                                    name="defect_quantities[]" placeholder="Qty" min="1">
                                            </div>
                                            <div class="col-1 text-center"></div>
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-info mt-1"
                                        style="display: none;">
                                        <i class="fas fa-plus"></i> Tambah Jenis
                                    </button>
                                </td>
                                <!-- Total OK / NG -->
                                <td class="align-middle" style="min-width: 130px;">
                                    <div class="d-flex align-items-center mb-1" style="gap:4px;">
                                        <span class="ok-label px-2 py-1 rounded-left font-weight-bold" style="font-size:0.7rem; border-radius:4px 0 0 4px;">OK</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; font-size:0.78rem;"
                                            name="total_ok" id="total_ok" placeholder="0" min="0" required>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:4px;">
                                        <span class="ng-label px-2 py-1 font-weight-bold" style="font-size:0.7rem; border-radius:4px 0 0 4px;">NG</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; font-size:0.78rem;"
                                            name="total_ng" id="total_ng" placeholder="0" min="0">
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle text-center" style="min-width: 150px;">
                                    <div id="judgmentBadge" class="mb-2 p-3 font-weight-bold h4 rounded d-none shadow-sm"
                                        style="border: 2px solid transparent;">
                                        -
                                    </div>
                                    <input type="hidden" name="judgment" id="judgmentSelect">
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
                                        value="{{ auth()->user()->initials ?? '' }}">
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle" style="min-width: 320px;">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next
                                            Proses: <span class="text-danger">*</span></label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            @foreach($nextProcesses as $opt)
                                                <option value="{{ $opt->name }}">{{ $opt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="6"
                                        style="min-height:140px; min-width:300px; width:100%; resize:both;"
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

    <!-- Bagian Tampilan PDF Berdampingan -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">PCCP DAN DIMENSI</h6>
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
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn mr-1" id="fullStandardBtn"
                                style="display:none;">
                                <i class="fas fa-expand"></i> Full
                            </button>
                            <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download PCCP PDF" style="display:none;">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div id="standardPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <!-- Status default yang diperbarui: pesan ditampilkan ketika tidak ada item yang dipilih -->
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
                        <h6 class="font-weight-bold text-dark mb-0">DIMENSI PART</h6>
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
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn mr-1" id="fullSimilarBtn"
                                style="display:none;">
                                <i class="fas fa-expand"></i> Full
                            </button>
                            <a id="downloadSimilarBtn" class="btn btn-sm btn-info" href="#" download title="Download Dimensi Part PDF" style="display:none;">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div id="similarPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="similarPdfPlaceholder"
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
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

    <!-- Modal Gambar -->
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

    <!-- Modal PDF (Ditambahkan) -->
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
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/fpa.js') }}?v={{ time() }}"></script>
    {{-- Hidden JSON data to resolve IDE lint errors --}}
    <script id="fpa-create-data" type="application/json"
        data-pdf-worker-src="{{ asset('js/vendor/pdf.worker.min.js') }}"
        data-pdf-route-pattern="{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
        data-current-plant="{{ request('plant') ?? auth()->user()->plant_id }}">
        @json($partDimensionStandards)
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataEl = document.getElementById('fpa-create-data');
            if (!dataEl) return;

            const partDimensionStandards = JSON.parse(dataEl.textContent);

            window.initFpaCreate({
                pdfWorkerSrc: dataEl.getAttribute('data-pdf-worker-src'),
                pdfRoutePattern: dataEl.getAttribute('data-pdf-route-pattern'),
                currentPlant: dataEl.getAttribute('data-current-plant'),
                partDimensionStandards: partDimensionStandards,
                initialLock: true
            });
            window.initItemSearch('itemSelect');
        });
    </script>
@endpush
