@extends('layouts.admin')

@php
    $plantCode = strtolower($plantCode ?: 'karawang');
    $isJkt = in_array($plantCode, ['jakarta', 'jkt']);
    $docHeader = \App\Models\GeneralSetting::getDocHeader('leveling_inspector', $plantCode, [
        'no_dokumen' => $isJkt ? 'QC-JKT-F-052' : 'PI-KRW-F-052',
        'tgl_terbit' => date('d/m/Y'),
        'revisi'     => '-',
        'halaman'    => '1 / 1',
    ]);
    $pageTitle = 'Leveling Inspector' . ($selectedAudit ? ' - ' . ($selectedAudit->inspector->name ?? '-') : '');
@endphp

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-2 border-0">
        <div class="card-body p-2">

            <!-- 1. Header Dokumen ISO dengan Signatures per Plant (seragam dengan Kepatuhan Operator) -->
            <div class="mb-2">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                LEVELING INSPECTOR
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; padding:0 !important; vertical-align:top; white-space:nowrap;">
                            <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                <tr style="height:100%;">
                                    <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                                        <table style="border-collapse:collapse; border:1px solid #dee2e6; font-size:0.65rem; background:#fff; height:100%; width:100%;">
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:700; color:#212529; white-space:nowrap;">{{ $docHeader['no_dokumen'] }}</td>
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
                                                <tr>
                                                    <td rowspan="3" style="border:1px solid #dee2e6; padding:2px; vertical-align:middle; text-align:center; width:28px;">
                                                        <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:0.58rem; font-weight:400; margin:0 auto; color:#6c757d;">
                                                            {{ date('d-M-y') }}
                                                        </div>
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        @if($isJkt)
                                                            <img src="{{ asset('signatures/suli.png') }}" alt="Masuli" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        @else
                                                            <img src="{{ asset('signatures/arif.png') }}" alt="Arief H" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        @endif
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">{{ $isJkt ? 'Masuli' : 'Arief H' }}</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Iwan S</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Desti K</td>
                                                </tr>
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

            <link rel="stylesheet" href="{{ asset('css/checksheet/leveling-inspector.css') }}?v={{ time() }}">

            <!-- Config JSON via data-* (bebas inline script) -->
            <div id="leveling-config" class="d-none"
                 data-plant="{{ $plantCode }}"
                 data-audit-id="{{ $selectedAudit ? $selectedAudit->id : '' }}"
                 data-today="{{ \Carbon\Carbon::today()->toDateString() }}"
                 data-store-audit-url="{{ route('checksheet.leveling_inspector.audit.store') }}"
                 data-delete-audit-base="{{ url('/checksheet/leveling-inspector/audit') }}"
                 data-save-result-url="{{ route('checksheet.leveling_inspector.result.save') }}"
                 data-bulk-result-url="{{ route('checksheet.leveling_inspector.result.bulk') }}"
                 data-upload-evidence-url="{{ route('checksheet.leveling_inspector.result.evidence') }}"
                 data-delete-evidence-base="{{ url('/checksheet/leveling-inspector/result') }}"
                 data-lookup-inspectors-url="{{ route('checksheet.leveling_inspector.lookup.inspectors') }}"
                 data-lookup-audits-url="{{ route('checksheet.leveling_inspector.lookup.audits') }}"
                 data-lookup-bagian-url="{{ route('checksheet.leveling_inspector.lookup.bagian') }}"
                 data-lookup-parts-url="{{ route('checksheet.leveling_inspector.lookup.parts') }}"
                 data-ng-list-url="{{ route('checksheet.leveling_inspector.ng.list') }}"
                 data-ng-print-url="{{ route('checksheet.leveling_inspector.ng.print') }}"
                 data-ng-base-url="{{ url('/checksheet/leveling-inspector/ng') }}"
                 data-master-list-url="{{ route('checksheet.leveling_inspector.master_item.list') }}"
                 data-master-store-url="{{ route('checksheet.leveling_inspector.master_item.store') }}"
                 data-master-base-url="{{ url('/checksheet/leveling-inspector/master-item') }}"
                 data-print-url="{{ $selectedAudit ? route('checksheet.leveling_inspector.print', ['plant' => $plantCode, 'audit_id' => $selectedAudit->id]) : '' }}"
            ></div>

            <!-- 2. Bar Filter & Tombol Aksi -->
            <form action="{{ route('checksheet.leveling_inspector.index') }}" method="GET"
                  id="levelingFilterForm"
                  class="d-flex flex-wrap align-items-end bg-white p-0 rounded mb-3"
                  style="gap: 12px;">
                <input type="hidden" name="plant" value="{{ $plantCode }}">

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Tanggal</label>
                    <input type="date" name="date" id="filterDate" value="{{ $date }}"
                           class="form-control form-control-sm filter-select-custom" style="width: 140px;">
                </div>

                @php
                    $selectedInspectorObj = $inspectorId ? $inspectors->firstWhere('id', (int) $inspectorId) : null;
                    $selectedInspectorName = $selectedInspectorObj ? $selectedInspectorObj->name : '';
                    $selectedAuditLabel = $selectedAudit
                        ? ($selectedAudit->audit_date->format('d/m/Y') . ' — ' . ($selectedAudit->inspector->name ?? '-') . ' — ' . $selectedAudit->part_name . ($selectedAudit->part_number ? ' (' . $selectedAudit->part_number . ')' : ''))
                        : '';
                @endphp

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Inspector</label>
                    <div class="position-relative" style="width: 225px;">
                        <input type="hidden" name="inspector_id" id="filterInspectorId" value="{{ $inspectorId ?? '' }}">
                        <div class="lvl-filter-input-group {{ $inspectorId ? 'has-clear' : '' }}" id="filterInspectorGroup">
                            <input type="text" id="filterInspectorSearch"
                                   class="form-control form-control-sm filter-select-custom"
                                   value="{{ $selectedInspectorName }}"
                                   placeholder="Ketik nama inspector..."
                                   autocomplete="off">
                            <button type="button" id="btnClearFilterInspector"
                                    class="lvl-filter-clear {{ $inspectorId ? '' : 'd-none' }}"
                                    title="Tampilkan semua inspector">&times;</button>
                        </div>
                        <div id="filterInspectorDropdown" class="lvl-search-dropdown"></div>
                    </div>
                </div>

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Riwayat Audit</label>
                    <div class="position-relative" style="width: 325px;">
                        <input type="hidden" name="audit_id" id="filterAuditId" value="{{ $selectedAudit ? $selectedAudit->id : '' }}">
                        <div class="lvl-filter-input-group {{ $selectedAudit ? 'has-clear' : '' }}" id="filterAuditGroup">
                            <input type="text" id="filterAuditSearch"
                                   class="form-control form-control-sm filter-select-custom"
                                   value="{{ $selectedAuditLabel }}"
                                   placeholder="Ketik nama inspector / part / tanggal..."
                                   autocomplete="off">
                            <button type="button" id="btnClearFilterAudit"
                                    class="lvl-filter-clear {{ $selectedAudit ? '' : 'd-none' }}"
                                    title="Reset pilihan audit">&times;</button>
                        </div>
                        <div id="filterAuditDropdown" class="lvl-search-dropdown"></div>
                    </div>
                </div>

                <div class="ml-auto d-flex flex-wrap align-items-center mt-auto" style="gap: 6px;">
                    @if($canManageAuditTools)
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm js-toggle-new-audit" id="btnNewAudit">
                            <i class="fas fa-plus fa-sm mr-1"></i> Audit Baru
                        </button>

                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm"
                                data-toggle="modal" data-target="#ngListModal" id="btnNgListModal">
                            Daftar Temuan NG <span id="openNgCountText">{{ $openNgCount > 0 ? '(' . $openNgCount . ')' : '' }}</span>
                        </button>

                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm"
                                data-toggle="modal" data-target="#masterItemModal" id="btnMasterItemModal">
                            <i class="fas fa-cog fa-sm mr-1"></i> Master Point
                        </button>
                    @endif

                    <a href="{{ route('checksheet.leveling_inspector.rekap', ['plant' => $plantCode]) }}"
                       class="btn btn-info btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;">
                        <i class="fas fa-chart-bar fa-sm mr-1"></i> Rekap Tahunan
                    </a>

                    @if($selectedAudit)
                        <a href="{{ route('checksheet.leveling_inspector.print', ['plant' => $plantCode, 'audit_id' => $selectedAudit->id]) }}"
                           id="btnPrintLeveling"
                           class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center btn-print-direct no-loader"
                           style="height: 31px;"
                           title="Cetak Dokumen Leveling Inspector">
                            <i class="fas fa-print fa-sm mr-1"></i> Cetak
                        </a>

                        @if($canEditSelectedAudit)
                            <button type="button" id="btnDeleteAudit"
                                    data-audit-id="{{ $selectedAudit->id }}"
                                    class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center"
                                    style="height: 31px;"
                                    title="Hapus sesi audit ini">
                                <i class="fas fa-trash-alt fa-sm mr-1"></i> Hapus
                            </button>
                        @endif
                    @endif
                </div>
            </form>

            @if($canManageAuditTools)
            <!-- Panel Inline Audit Baru (slide-down di bawah filter, tanpa modal) -->
            <div id="newAuditPanel" class="d-none bg-white border rounded p-3 mb-3 shadow-xs">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                        Buat Audit Leveling Inspector Baru
                    </div>
                    <button type="button" class="close js-close-new-audit" aria-label="Close" style="font-size:1.1rem; line-height:1;">&times;</button>
                </div>
                <form id="newAuditForm" novalidate>
                    <div class="row align-items-start">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small font-weight-bold lvl-label-black mb-1">Nama Inspector <span class="text-danger">*</span></label>
                            <input type="hidden" name="inspector_id" id="auditInspectorId" required>
                            <div class="position-relative">
                                <input type="text" id="auditInspectorSearch" class="form-control form-control-sm border bg-white"
                                       placeholder="Ketik nama inspector..." autocomplete="off">
                                <div id="auditInspectorDropdown" class="lvl-search-dropdown"></div>
                            </div>
                        </div>

                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="small font-weight-bold lvl-label-black mb-1">Proses / Line <span class="text-danger">*</span></label>
                            <select name="bagian" id="auditBagian" class="form-control form-control-sm border bg-white" required>
                                <option value="">-- Otomatis dari Kepatuhan --</option>
                                @foreach($bagianOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                            <small id="auditBagianHint" class="text-muted d-block mt-1" style="font-size:0.68rem;"></small>
                        </div>

                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="small font-weight-bold lvl-label-black mb-1">Meja / Mesin</label>
                            <select name="meja_mesin" id="auditMejaMesin"
                                    class="form-control form-control-sm border bg-white"
                                    data-meja-options='@json($mejaOptions)'
                                    data-mesin-options='@json($mesinOptions)'>
                                <option value="">-- Pilih Meja / Mesin --</option>
                                <optgroup label="Meja">
                                    @foreach($mejaOptions as $tbl)
                                        <option value="{{ $tbl }}">{{ $tbl }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Mesin">
                                    @foreach($mesinOptions as $msn)
                                        <option value="{{ $msn }}">{{ $msn }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small font-weight-bold lvl-label-black mb-1">Nama Part / No. Part <span class="text-danger">*</span></label>
                            <input type="hidden" name="item_id" id="auditItemId" required>
                            <div class="position-relative">
                                <input type="text" id="auditPartSearch" class="form-control form-control-sm border bg-white"
                                       placeholder="Pilih inspector dulu, lalu ketik part..." autocomplete="off" disabled>
                                <div id="auditPartDropdown" class="lvl-search-dropdown"></div>
                            </div>
                            <small id="auditPartHint" class="text-muted d-block mt-1" style="font-size:0.68rem;"></small>
                        </div>

                        <div class="col-md-2 d-flex align-items-end justify-content-md-end" style="padding-top: 22px; gap: 6px;">
                            <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm" id="btnSubmitNewAudit">Mulai Audit</button>
                            <button type="button" class="btn btn-light border btn-sm px-2 js-close-new-audit">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <!-- 3. Informasi Header Audit (Tanggal, Proses/Line/Meja/Mesin, Part, Inspector, Auditor) -->
            <div class="row bg-white p-2 rounded border mx-0 mb-3 shadow-xs" style="font-size: 0.78rem;">
                <div class="col-md-2 mb-1 mb-md-0">
                    <span class="text-muted d-block" style="font-size:0.68rem;">Tanggal</span>
                    <strong class="text-dark">{{ $selectedAudit ? $selectedAudit->audit_date->format('d/m/Y') : \Carbon\Carbon::today()->format('d/m/Y') }}</strong>
                </div>
                <div class="col-md-3 mb-1 mb-md-0">
                    <span class="text-muted d-block" style="font-size:0.68rem;">Proses / Line / Meja / Mesin</span>
                    <strong class="text-dark">{{ $selectedAudit ? $selectedAudit->proses_line_label : '-' }}</strong>
                </div>
                <div class="col-md-3 mb-1 mb-md-0">
                    <span class="text-muted d-block" style="font-size:0.68rem;">Nama Part / No. Part</span>
                    <strong class="text-dark">
                        @if($selectedAudit)
                            {{ $selectedAudit->part_name }}@if($selectedAudit->part_number) / {{ $selectedAudit->part_number }}@endif
                        @else
                            -
                        @endif
                    </strong>
                </div>
                <div class="col-md-2 mb-1 mb-md-0">
                    <span class="text-muted d-block" style="font-size:0.68rem;">Nama Inspector</span>
                    <strong class="text-dark">{{ $selectedAudit ? ($selectedAudit->inspector->name ?? '-') : '-' }}</strong>
                </div>
                <div class="col-md-2">
                    <span class="text-muted d-block" style="font-size:0.68rem;">Auditor</span>
                    <strong class="text-dark">{{ $selectedAudit ? $selectedAudit->auditor_name : auth()->user()->name }}</strong>
                </div>
            </div>

            <!-- 4. Tabel Point Check (Header 2-Tingkat: Result -> OK/NG & Level) -->
            @if(!$selectedAudit)
                <div class="text-center py-5 border rounded bg-light">
                    <div class="text-muted mb-2" style="font-size:0.9rem;">
                        Belum ada data audit Leveling Inspector yang ditemukan{{ !empty($date) ? ' untuk tanggal ' . \Carbon\Carbon::parse($date)->format('d/m/Y') : '' }}.
                    </div>
                    @if($canManageAuditTools)
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm js-toggle-new-audit">
                            <i class="fas fa-plus fa-sm mr-1"></i> Mulai Audit Baru
                        </button>
                    @endif
                </div>
            @else
                @if(!$canEditSelectedAudit)
                    <div class="small text-muted mb-2">
                        <i class="fas fa-lock mr-1"></i>Mode Lihat Saja — data audit ini hanya dapat diubah oleh auditor pembuat (<strong class="text-dark">{{ $selectedAudit->auditor_name }}</strong>) atau level SPV ke atas.
                    </div>
                @endif
                <div class="table-responsive lvl-table-wrapper">
                    <table class="table table-hover" id="levelingTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                @if($canEditSelectedAudit)
                                    <th rowspan="2" class="lvl-col-check">
                                        <input type="checkbox" id="lvlCheckAll" title="Pilih Semua Point Check">
                                    </th>
                                @endif
                                <th rowspan="2" class="lvl-col-prinsip">PRINSIP</th>
                                <th rowspan="2" class="lvl-col-level">LEVEL</th>
                                <th rowspan="2" class="lvl-col-point">POINT CHECK</th>
                                <th rowspan="2" class="lvl-col-guidance">GUIDANCE PENILAIAN</th>
                                <th colspan="2">RESULT</th>
                                <th rowspan="2" class="lvl-col-evidence">EVIDENCE</th>
                            </tr>
                            <tr>
                                <th class="lvl-col-okng">OK / NG</th>
                                <th class="lvl-col-result-level">LEVEL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(empty($prinsipGroups))
                                <tr>
                                    <td colspan="{{ $canEditSelectedAudit ? 8 : 7 }}" class="text-center text-muted py-4">
                                        Belum ada Master Point Check aktif.
                                        @if($canManageAuditTools)
                                            <a href="javascript:void(0)" class="font-weight-bold text-primary ml-1" data-toggle="modal" data-target="#masterItemModal">
                                                Tambah melalui Kelola Master Point Check
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                @foreach($prinsipGroups as $pGroup)
                                    @php $isFirstPrinsipRow = true; @endphp
                                    @foreach($pGroup['levels'] as $lGroup)
                                        @php $isFirstLevelRow = true; @endphp
                                        @foreach($lGroup['rows'] as $row)
                                            @php
                                                $item = $row['item'];
                                                $res  = $row['result'];
                                                $val  = $res ? $res->result : null;
                                                $evUrls = $res ? array_map(fn($p) => \Illuminate\Support\Facades\Storage::url($p), $res->getEvidencePathsList()) : [];
                                                $hasEv = count($evUrls) > 0;
                                                $ngStatus = $res ? $res->ng_status : null;
                                                $ngStatusLabel = '';
                                                $ngStatusClass = '';
                                                if ($val === 'NG') {
                                                    if ($ngStatus === 'closed') {
                                                        $ngStatusLabel = 'Closed';
                                                        $ngStatusClass = 'text-success font-weight-bold';
                                                    } elseif ($ngStatus === 'waiting_verify') {
                                                        $ngStatusLabel = 'Waiting Verify';
                                                        $ngStatusClass = 'text-warning font-weight-bold';
                                                    } else {
                                                        $ngStatusLabel = 'Open';
                                                        $ngStatusClass = 'text-danger font-weight-bold';
                                                    }
                                                }
                                            @endphp
                                            <tr data-item-id="{{ $item->id }}"
                                                data-prinsip="{{ $pGroup['prinsip'] }}"
                                                data-level="{{ $lGroup['level'] }}"
                                                data-result-id="{{ $res ? $res->id : '' }}"
                                                data-ng-status="{{ $ngStatus ?? '' }}"
                                                data-has-correction="{{ ($res && ($res->corrective_action || $res->why_1 || $res->root_cause)) ? '1' : '0' }}"
                                                data-evidence-note="{{ $res ? ($res->evidence_note ?? '') : '' }}"
                                                class="{{ $val === 'NG' ? 'row-ng' : '' }}">

                                                @if($canEditSelectedAudit)
                                                    <td class="lvl-col-check">
                                                        <input type="checkbox" class="js-row-check" value="{{ $item->id }}" data-item-id="{{ $item->id }}">
                                                    </td>
                                                @endif

                                                @if($isFirstPrinsipRow)
                                                    <td rowspan="{{ $pGroup['rowspan'] }}" class="lvl-col-prinsip lvl-prinsip-{{ strtolower($pGroup['prinsip']) }}">
                                                        <div class="lvl-prinsip-text">{{ $pGroup['prinsip'] }}</div>
                                                    </td>
                                                @endif

                                                @if($isFirstLevelRow)
                                                    <td rowspan="{{ $lGroup['rowspan'] }}" class="lvl-col-level">{{ $lGroup['level'] }}</td>
                                                @endif

                                                <td class="lvl-col-point">
                                                    <span class="font-weight-bold mr-1">{{ $row['row_num'] }}.</span>{{ $item->point_check }}
                                                </td>

                                                <td class="lvl-col-guidance">
                                                    <div class="lvl-guidance-content">{!! $row['guidance_html'] !!}</div>
                                                </td>

                                                <td class="lvl-col-okng">
                                                    @if($canEditSelectedAudit)
                                                        <div class="js-result-btns align-items-center justify-content-center {{ $val ? 'd-none' : 'd-inline-flex' }}" style="gap: 4px;">
                                                            <button type="button"
                                                                    class="btn btn-outline-success lvl-btn-result js-result-btn"
                                                                    data-item-id="{{ $item->id }}"
                                                                    data-val="OK">OK</button>
                                                            <button type="button"
                                                                    class="btn btn-outline-danger lvl-btn-result js-result-btn"
                                                                    data-item-id="{{ $item->id }}"
                                                                    data-val="NG">NG</button>
                                                        </div>
                                                        <div class="js-result-display font-weight-bold {{ $val ? '' : 'd-none' }} {{ $val === 'OK' ? 'text-success' : ($val === 'NG' ? 'text-danger' : '') }}"
                                                             style="cursor:pointer; font-size:0.85rem; letter-spacing:0.3px;"
                                                             title="Klik untuk ubah hasil"
                                                             data-item-id="{{ $item->id }}">{{ $val }}</div>
                                                    @else
                                                        @if($val)
                                                            <div class="font-weight-bold {{ $val === 'OK' ? 'text-success' : 'text-danger' }}" style="font-size:0.85rem; letter-spacing:0.3px;">{{ $val }}</div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    @endif
                                                    <div class="mt-1 js-ng-status-text {{ $ngStatusClass }}" style="font-size:0.65rem;">
                                                        {{ $ngStatusLabel }}
                                                    </div>
                                                </td>

                                                @if($isFirstPrinsipRow)
                                                    @php
                                                        $lvlVal = $pGroup['achieved_level'];
                                                        $lvlColor = $lvlVal === '3' ? 'text-success' : ($lvlVal === '2' ? 'text-primary' : ($lvlVal === '1' ? 'text-dark' : ($lvlVal === '0' ? 'text-danger' : 'text-muted')));
                                                    @endphp
                                                    <td rowspan="{{ $pGroup['rowspan'] }}"
                                                        class="lvl-col-result-level js-level-result {{ $lvlColor }}"
                                                        data-result-level="{{ $pGroup['prinsip'] }}">
                                                        {{ $lvlVal }}
                                                    </td>
                                                    @php $isFirstPrinsipRow = false; @endphp
                                                @endif
                                                @php $isFirstLevelRow = false; @endphp

                                                <td class="lvl-col-evidence">
                                                    @php $evNote = $res ? trim((string) ($res->evidence_note ?? '')) : ''; @endphp
                                                    @if($canEditSelectedAudit)
                                                        <div class="lvl-ev-card">
                                                            <div class="lvl-ev-head">
                                                                <span class="lvl-ev-title">Keterangan</span>
                                                                <button type="button"
                                                                        class="lvl-ev-save-note js-save-ev-note d-none"
                                                                        data-item-id="{{ $item->id }}"
                                                                        data-result-id="{{ $res ? $res->id : '' }}">Simpan</button>
                                                            </div>
                                                            <textarea rows="1"
                                                                      class="lvl-ev-note-input js-ev-note-field"
                                                                      maxlength="1000"
                                                                      data-item-id="{{ $item->id }}"
                                                                      placeholder="Tulis keterangan evidence...">{{ $evNote }}</textarea>

                                                            <div class="js-pending-upload-bar lvl-pending-bar d-none"></div>

                                                            <div class="js-upload-status lvl-uploading-bar d-none">
                                                                <i class="fas fa-spinner fa-spin text-secondary"></i>
                                                                <span>Mengupload foto...</span>
                                                            </div>

                                                            <div class="js-evidence-box lvl-uploaded-wrap {{ $hasEv ? '' : 'd-none' }}" data-item-id="{{ $item->id }}">
                                                                <div class="lvl-uploaded-bar js-uploaded-items">
                                                                    @foreach($evUrls as $eIdx => $eUrl)
                                                                        <div class="lvl-uploaded-item">
                                                                            <div class="lvl-uploaded-left">
                                                                                <div class="lvl-photo-wrap lvl-photo-wrap-sm lvl-uploaded-thumb-wrap js-preview-photo" data-src="{{ $eUrl }}" title="Klik untuk melihat foto">
                                                                                    <img src="{{ $eUrl }}" alt="Evidence" class="lvl-thumb">
                                                                                    <span class="lvl-photo-eye"><i class="fas fa-eye"></i></span>
                                                                                </div>
                                                                                <span class="lvl-uploaded-name js-preview-photo" style="cursor:pointer;" data-src="{{ $eUrl }}" title="{{ basename($eUrl) }}">Foto #{{ $eIdx + 1 }}</span>
                                                                            </div>
                                                                            <div class="lvl-uploaded-actions">
                                                                                @if($loop->first)
                                                                                    <label class="lvl-reupload-link js-smart-upload-trigger" title="Tambah / Upload Foto">
                                                                                        + Upload
                                                                                        <input type="file"
                                                                                               class="d-none js-evidence-input js-smart-capture-input"
                                                                                               accept="image/*"
                                                                                               multiple
                                                                                               data-item-id="{{ $item->id }}">
                                                                                    </label>
                                                                                    <span class="lvl-action-divider"></span>
                                                                                @endif
                                                                                <button type="button"
                                                                                        class="lvl-trash-btn js-delete-evidence"
                                                                                        title="Hapus Foto Ini"
                                                                                        data-result-id="{{ $res ? $res->id : '' }}"
                                                                                        data-item-id="{{ $item->id }}"
                                                                                        data-index="{{ $eIdx }}">
                                                                                    <i class="far fa-trash-alt"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>

                                                            <label class="lvl-dropzone js-evidence-dropzone js-smart-upload-trigger {{ $hasEv ? 'd-none' : '' }}"
                                                                   data-item-id="{{ $item->id }}"
                                                                   title="Upload Foto Evidence">
                                                                <span class="lvl-dz-icon">
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                                                    </svg>
                                                                </span>
                                                                <span class="lvl-dz-text">
                                                                    <span class="lvl-dz-accent">Klik untuk upload</span><span class="lvl-dz-or"> atau drag &amp; drop</span>
                                                                </span>
                                                                <span class="lvl-dz-sub">JPG, PNG, WEBP &middot; Maks 10 MB</span>
                                                                <input type="file"
                                                                       class="d-none js-evidence-input js-smart-capture-input"
                                                                       accept="image/*"
                                                                       multiple
                                                                       data-item-id="{{ $item->id }}">
                                                            </label>

                                                            <div class="js-evidence-required text-danger font-weight-bold mt-1 text-center {{ ($val === 'NG' && !$hasEv) ? '' : 'd-none' }}" style="font-size:0.62rem;">
                                                                Wajib upload foto
                                                            </div>
                                                        </div>
                                                    @else
                                                        @if($evNote !== '')
                                                            <div class="text-left text-dark mb-1" style="font-size:0.66rem; line-height:1.3;">
                                                                <strong>Ket:</strong> {{ $evNote }}
                                                            </div>
                                                        @endif
                                                        @if($hasEv)
                                                            <div class="d-flex flex-wrap justify-content-center" style="gap: 6px;">
                                                                @foreach($evUrls as $eUrl)
                                                                    <div class="lvl-photo-wrap lvl-photo-wrap-sm js-preview-photo" data-src="{{ $eUrl }}" title="Klik untuk melihat foto">
                                                                        <img src="{{ $eUrl }}" alt="Evidence" class="lvl-thumb" style="max-width:95px; max-height:68px;">
                                                                        <span class="lvl-photo-eye"><i class="fas fa-eye"></i></span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @elseif($evNote === '')
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- 5. Ringkasan Skor (Sticky di bawah layar agar progress selalu terlihat) -->
                <div class="d-flex flex-wrap justify-content-between align-items-center lvl-summary-sticky px-3 py-2 mt-2">
                    <div class="d-flex flex-wrap" style="gap: 18px;">
                        <div>Total OK: <strong class="text-success" id="sumTotalOk">{{ $summary['total_ok'] }}</strong></div>
                        <div>Total NG: <strong class="text-danger" id="sumTotalNg">{{ $summary['total_ng'] }}</strong></div>
                        <div>Belum Diisi: <strong class="text-muted" id="sumTotalEmpty">{{ $summary['total_empty'] }}</strong></div>
                    </div>
                    <div>
                        Rata-rata Level:
                        <strong class="text-primary" id="sumScore">{{ $summary['score'] !== null ? $summary['score'] : '-' }}</strong>
                    </div>
                </div>

                @if($canEditSelectedAudit)
                    <!-- Floating Bulk Action Bar (muncul saat > 1 point check diceklis) -->
                    <div id="lvlBulkBar" class="lvl-bulk-bar" role="region" aria-label="Aksi Massal Point Check">
                        <span><strong id="lvlBulkCount">0</strong> point check dipilih</span>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 font-weight-bold js-bulk-action" data-val="OK">
                            OK Semua
                        </button>
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 font-weight-bold js-bulk-action" data-val="NG">
                            NG Semua
                        </button>
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-3" id="btnBulkClear">
                            Batal
                        </button>
                    </div>
                @endif
            @endif

        </div>
    </div>
</div>

<!-- MODAL 2: DAFTAR TEMUAN NG, INPUT PERBAIKAN & VERIFIKASI (1 MODAL, SWITCH PANEL) -->
<div class="modal fade" id="ngListModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content lvl-modal-content">
            <div class="modal-header lvl-modal-header py-3 px-4">
                <h5 class="modal-title font-weight-bold text-gray-800 mb-0" id="ngModalTitle" style="font-size: 1rem;">Daftar Temuan NG & Verifikasi Perbaikan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body lvl-modal-body px-4 py-3">
                <!-- PANEL A: DAFTAR TEMUAN NG -->
                <div id="ngPanelList">
                    <div class="d-flex flex-wrap align-items-end mb-3 bg-white p-2 rounded border" style="gap: 10px;">
                        <div>
                            <label class="small font-weight-bold mb-1 d-block">Status</label>
                            <select id="ngFilterStatus" class="form-control form-control-sm filter-select-custom" style="width:150px;">
                                <option value="">Semua Status</option>
                                <option value="open">Open</option>
                                <option value="waiting_verify">Waiting Verify</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="small font-weight-bold mb-1 d-block">Dari Tanggal</label>
                            <input type="date" id="ngFilterDateFrom" class="form-control form-control-sm filter-select-custom" style="width:140px;">
                        </div>
                        <div>
                            <label class="small font-weight-bold mb-1 d-block">Sampai Tanggal</label>
                            <input type="date" id="ngFilterDateTo" class="form-control form-control-sm filter-select-custom" style="width:140px;">
                        </div>
                        <div>
                            <label class="small font-weight-bold mb-1 d-block">Inspector</label>
                            <div class="position-relative" style="width: 210px;">
                                <input type="hidden" id="ngFilterInspector" value="">
                                <div class="lvl-filter-input-group" id="ngFilterInspectorGroup">
                                    <input type="text" id="ngFilterInspectorSearch"
                                           class="form-control form-control-sm filter-select-custom"
                                           placeholder="Ketik nama inspector..."
                                           autocomplete="off">
                                    <button type="button" id="btnClearNgInspector"
                                            class="lvl-filter-clear d-none"
                                            title="Semua inspector">&times;</button>
                                </div>
                                <div id="ngFilterInspectorDropdown" class="lvl-search-dropdown"></div>
                            </div>
                        </div>
                        <div>
                            <button type="button" id="btnReloadNgList" class="btn btn-primary btn-sm px-3" style="height:31px;">Filter</button>
                        </div>
                        <div class="ml-auto">
                            <button type="button" id="btnPrintNgList" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height:31px;">
                                <i class="fas fa-print fa-sm mr-1"></i> Cetak
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded border p-2 table-responsive">
                        <table class="table table-hover lvl-modal-table" id="ngFindingsTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Inspector / Bagian</th>
                                    <th style="min-width:165px;">Nama Part / Part No</th>
                                    <th>Prinsip / Level</th>
                                    <th style="min-width:170px;">Point Check</th>
                                    <th>Foto Temuan</th>
                                    <th style="min-width:210px;">Tindakan Perbaikan</th>
                                    <th>Foto Perbaikan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="ngFindingsBody">
                                <tr><td colspan="10" class="text-center text-muted py-3">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PANEL B: INPUT PERBAIKAN NG -->
                <div id="ngPanelCorrect" class="d-none">
                    <form id="ngCorrectForm" enctype="multipart/form-data" novalidate>
                        <input type="hidden" id="ngCorrectResultId">
                        <div class="form-row align-items-start mb-2">
                            <div class="col-md-9 mb-2">
                                <label class="small font-weight-bold lvl-label-black">Point Check NG</label>
                                <div class="form-control form-control-sm bg-light border font-weight-bold text-dark" style="height:auto; min-height:31px; line-height:1.4;" id="ngCorrectPointText">-</div>
                                <div class="text-danger small mt-1 d-none" id="ngCorrectRejectNote"></div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold lvl-label-black">Tanggal Perbaikan <span class="text-danger">*</span></label>
                                <input type="date" name="corrective_date" id="ngCorrectDate" value="{{ \Carbon\Carbon::today()->toDateString() }}"
                                       class="form-control form-control-sm border shadow-xs" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="small font-weight-bold lvl-label-black">Why 1 <span class="text-danger">*</span></label>
                                <div class="lvl-rte-wrapper">
                                    <div class="lvl-rte-toolbar lvl-rte-toolbar-sm">
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="bold" title="Tebal (Ctrl+B)"><i class="fas fa-bold"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="italic" title="Miring (Ctrl+I)"><i class="fas fa-italic"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="underline" title="Garis Bawah (Ctrl+U)"><i class="fas fa-underline"></i></button>
                                        <span class="lvl-rte-sep"></span>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertOrderedList" title="Daftar Angka"><i class="fas fa-list-ol"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertUnorderedList" title="Daftar Poin"><i class="fas fa-list-ul"></i></button>
                                        <span class="lvl-rte-sep"></span>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="removeFormat" title="Hapus Format"><i class="fas fa-remove-format"></i></button>
                                    </div>
                                    <div id="ngCorrectWhy1Editor" class="lvl-rte-editor lvl-rte-compact" contenteditable="true" data-sync-target="#ngCorrectWhy1" data-placeholder="Wajib diisi..."></div>
                                </div>
                                <textarea name="why_1" id="ngCorrectWhy1" class="d-none" required></textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="small font-weight-bold lvl-label-black">Why 2</label>
                                <div class="lvl-rte-wrapper">
                                    <div class="lvl-rte-toolbar lvl-rte-toolbar-sm">
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="bold" title="Tebal (Ctrl+B)"><i class="fas fa-bold"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="italic" title="Miring (Ctrl+I)"><i class="fas fa-italic"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="underline" title="Garis Bawah (Ctrl+U)"><i class="fas fa-underline"></i></button>
                                        <span class="lvl-rte-sep"></span>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertOrderedList" title="Daftar Angka"><i class="fas fa-list-ol"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertUnorderedList" title="Daftar Poin"><i class="fas fa-list-ul"></i></button>
                                        <span class="lvl-rte-sep"></span>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="removeFormat" title="Hapus Format"><i class="fas fa-remove-format"></i></button>
                                    </div>
                                    <div id="ngCorrectWhy2Editor" class="lvl-rte-editor lvl-rte-compact" contenteditable="true" data-sync-target="#ngCorrectWhy2" data-placeholder="Opsional..."></div>
                                </div>
                                <textarea name="why_2" id="ngCorrectWhy2" class="d-none"></textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="small font-weight-bold lvl-label-black">Why 3</label>
                                <div class="lvl-rte-wrapper">
                                    <div class="lvl-rte-toolbar lvl-rte-toolbar-sm">
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="bold" title="Tebal (Ctrl+B)"><i class="fas fa-bold"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="italic" title="Miring (Ctrl+I)"><i class="fas fa-italic"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="underline" title="Garis Bawah (Ctrl+U)"><i class="fas fa-underline"></i></button>
                                        <span class="lvl-rte-sep"></span>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertOrderedList" title="Daftar Angka"><i class="fas fa-list-ol"></i></button>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertUnorderedList" title="Daftar Poin"><i class="fas fa-list-ul"></i></button>
                                        <span class="lvl-rte-sep"></span>
                                        <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="removeFormat" title="Hapus Format"><i class="fas fa-remove-format"></i></button>
                                    </div>
                                    <div id="ngCorrectWhy3Editor" class="lvl-rte-editor lvl-rte-compact" contenteditable="true" data-sync-target="#ngCorrectWhy3" data-placeholder="Opsional..."></div>
                                </div>
                                <textarea name="why_3" id="ngCorrectWhy3" class="d-none"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold lvl-label-black">Root Cause <span class="text-danger">*</span></label>
                            <div class="lvl-rte-wrapper">
                                <div class="lvl-rte-toolbar lvl-rte-toolbar-sm">
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="bold" title="Tebal (Ctrl+B)"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="italic" title="Miring (Ctrl+I)"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="underline" title="Garis Bawah (Ctrl+U)"><i class="fas fa-underline"></i></button>
                                    <span class="lvl-rte-sep"></span>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertOrderedList" title="Daftar Angka"><i class="fas fa-list-ol"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertUnorderedList" title="Daftar Poin"><i class="fas fa-list-ul"></i></button>
                                    <span class="lvl-rte-sep"></span>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="removeFormat" title="Hapus Format"><i class="fas fa-remove-format"></i></button>
                                </div>
                                <div id="ngCorrectRootCauseEditor" class="lvl-rte-editor lvl-rte-compact" contenteditable="true" data-sync-target="#ngCorrectRootCause" data-placeholder="Jelaskan akar masalah (Root Cause)..."></div>
                            </div>
                            <textarea name="root_cause" id="ngCorrectRootCause" class="d-none" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold lvl-label-black">Tindakan Perbaikan <span class="text-danger">*</span></label>
                            <div class="lvl-rte-wrapper">
                                <div class="lvl-rte-toolbar lvl-rte-toolbar-sm">
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="bold" title="Tebal (Ctrl+B)"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="italic" title="Miring (Ctrl+I)"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="underline" title="Garis Bawah (Ctrl+U)"><i class="fas fa-underline"></i></button>
                                    <span class="lvl-rte-sep"></span>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertOrderedList" title="Daftar Angka"><i class="fas fa-list-ol"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertUnorderedList" title="Daftar Poin"><i class="fas fa-list-ul"></i></button>
                                    <span class="lvl-rte-sep"></span>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="removeFormat" title="Hapus Format"><i class="fas fa-remove-format"></i></button>
                                </div>
                                <div id="ngCorrectActionEditor" class="lvl-rte-editor lvl-rte-compact" style="min-height: 105px;" contenteditable="true" data-sync-target="#ngCorrectAction" data-placeholder="Jelaskan tindakan perbaikan yang sudah dilakukan..."></div>
                            </div>
                            <textarea name="corrective_action" id="ngCorrectAction" class="d-none" required></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold lvl-label-black d-block mb-1">
                                Foto Evidence Perbaikan <span class="text-danger">*</span>
                                <span class="font-weight-normal text-muted ml-1">(Bisa lebih dari 1 foto, maks 10 MB/foto)</span>
                            </label>
                            <div class="lvl-correct-upload-box p-2">
                                <div id="ngCorrectEvidenceHint" class="small text-muted px-1 mb-1"></div>
                                <div id="ngCorrectPreviewList" class="lvl-correct-preview-grid pt-1 mb-2"></div>
                                <label class="lvl-dropzone js-correct-dropzone js-smart-upload-trigger mb-0" title="Upload Foto Evidence Perbaikan">
                                    <span class="lvl-dz-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                    </span>
                                    <span class="lvl-dz-text">
                                        <span class="lvl-dz-accent">Klik untuk upload</span><span class="lvl-dz-or"> atau drag &amp; drop</span>
                                    </span>
                                    <span class="lvl-dz-sub">JPG, PNG, WEBP &middot; Maks 10 MB</span>
                                    <input type="file" id="ngCorrectEvidenceInput" class="d-none js-smart-capture-input" accept="image/*" multiple>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- PANEL C: VERIFIKASI PERBAIKAN NG -->
                <div id="ngPanelVerify" class="d-none" style="font-size:0.82rem;">
                    <input type="hidden" id="ngVerifyResultId">
                    <div class="bg-white border rounded p-3 mb-3">
                        <div class="mb-2"><strong>Point Check:</strong> <span id="ngVerifyPointText"></span></div>
                        <div class="mb-2" id="ngVerifyWhyBlock"></div>
                        <div class="mb-2"><strong>Root Cause:</strong> <span id="ngVerifyRootCauseText"></span></div>
                        <div class="mb-2"><strong>Tindakan Perbaikan:</strong> <span id="ngVerifyActionText"></span></div>
                        <div class="row mt-3">
                            <div class="col-md-6 text-center">
                                <div class="small font-weight-bold text-danger mb-2">Foto Temuan (Before)</div>
                                <div id="ngVerifyBeforePhoto" class="d-flex flex-wrap justify-content-center" style="gap: 8px;"></div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="small font-weight-bold text-success mb-2">Foto Perbaikan (After)</div>
                                <div id="ngVerifyAfterPhoto" class="d-flex flex-wrap justify-content-center" style="gap: 8px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold lvl-label-black">Alasan Reject (wajib diisi jika klik Reject)</label>
                        <textarea id="ngVerifyRejectReason" rows="3" class="form-control border shadow-xs" style="font-size: 0.84rem;" placeholder="Isi alasan jika perbaikan belum sesuai..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer lvl-modal-footer py-2 px-4">
                <div id="ngFooterList">
                    <button type="button" class="btn btn-light border btn-sm px-3" data-dismiss="modal">Tutup</button>
                </div>
                <div id="ngFooterCorrect" class="d-none">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="button" class="btn btn-light border btn-sm px-3 js-ng-back-to-list">Batal</button>
                        <button type="submit" form="ngCorrectForm" class="btn btn-primary btn-sm px-4 shadow-sm" id="btnSubmitNgCorrect">Simpan Perbaikan</button>
                    </div>
                </div>
                <div id="ngFooterVerify" class="d-none">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="button" class="btn btn-light border btn-sm px-3 js-ng-back-to-list">Batal</button>
                        <button type="button" class="btn btn-danger btn-sm px-3" id="btnRejectNgVerify">Reject</button>
                        <button type="button" class="btn btn-success btn-sm px-4 shadow-sm" id="btnApproveNgVerify">Approve</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: KELOLA MASTER POINT CHECK (DINAMIS) -->
<div class="modal fade" id="masterItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content lvl-modal-content">
            <div class="modal-header lvl-modal-header py-3 px-4">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1rem;">Kelola Master Point Check Leveling Inspector</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body lvl-modal-body px-4 py-3">
                <form id="masterItemForm" class="bg-white border rounded p-3 mb-3" novalidate>
                    <input type="hidden" id="masterItemId">
                    <div class="font-weight-bold text-primary mb-3 pb-1" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;" id="masterFormTitle">
                        TAMBAH POINT CHECK BARU
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold lvl-label-black">Prinsip <span class="text-danger">*</span></label>
                            <select name="prinsip" id="masterPrinsip" class="form-control form-control-sm border shadow-xs" required>
                                <option value="">-- Pilih Prinsip --</option>
                                <option value="MSK">MSK</option>
                                <option value="KT">KT</option>
                                <option value="PNG">PNG</option>
                                <option value="PSSP">PSSP</option>
                                <option value="KAO">KAO</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold lvl-label-black">Level <span class="text-danger">*</span></label>
                            <select name="level" id="masterLevel" class="form-control form-control-sm border shadow-xs" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold lvl-label-black">Urutan</label>
                            <input type="number" name="order_no" id="masterOrderNo" min="1"
                                   class="form-control form-control-sm border shadow-xs" placeholder="Auto">
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-12 mb-2">
                            <label class="small font-weight-bold lvl-label-black">Point Check <span class="text-danger">*</span></label>
                            <textarea name="point_check" id="masterPointCheck" rows="2"
                                      class="form-control form-control-sm border shadow-xs"
                                      placeholder="Tulis isi point check (tanpa nomor, penomoran 1, 2, 3 otomatis)..." required></textarea>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="small font-weight-bold lvl-label-black">Guidance Penilaian</label>
                            <div class="lvl-rte-wrapper">
                                <div class="lvl-rte-toolbar lvl-rte-toolbar-sm" id="guidanceRteToolbar">
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="bold" title="Tebal (Ctrl+B)"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="italic" title="Miring (Ctrl+I)"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="underline" title="Garis Bawah (Ctrl+U)"><i class="fas fa-underline"></i></button>
                                    <span class="lvl-rte-sep"></span>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertOrderedList" title="Daftar Angka"><i class="fas fa-list-ol"></i></button>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertUnorderedList" title="Daftar Poin"><i class="fas fa-list-ul"></i></button>
                                    <span class="lvl-rte-sep"></span>
                                    <button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="removeFormat" title="Hapus Format"><i class="fas fa-remove-format"></i></button>
                                </div>
                                <div id="masterGuidanceEditor"
                                     class="lvl-rte-editor"
                                     contenteditable="true"
                                     data-placeholder="Tulis panduan penilaian di sini..."></div>
                            </div>
                            <textarea name="guidance" id="masterGuidance" class="d-none"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end align-items-center mt-2 pt-2 border-top" style="gap: 8px;">
                        <button type="button" class="btn btn-light border btn-sm px-3 d-none" id="btnCancelEditMaster">Batal Edit</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" id="btnSaveMasterItem">Simpan Point Check</button>
                    </div>
                </form>

                <div class="bg-white border rounded p-2">
                    <table class="table table-hover lvl-modal-table" id="masterItemsTable" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:65px;">Prinsip</th>
                                <th class="text-center" style="width:50px;">Level</th>
                                <th class="text-center" style="width:50px;">Urut</th>
                                <th>Point Check</th>
                                <th>Guidance Penilaian</th>
                                <th class="text-center" style="width:75px;">Status</th>
                                <th class="text-center" style="width:130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="masterItemsBody">
                            <tr><td colspan="7" class="text-center text-muted py-3">Memuat master point check...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer lvl-modal-footer py-2 px-4">
                <button type="button" class="btn btn-light border btn-sm px-3" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- LIGHTBOX OVERLAY PREVIEW FOTO (BUKAN BOOTSTRAP MODAL) -->
<div id="lvlLightbox" class="lvl-lightbox-overlay" role="dialog" aria-modal="true" aria-label="Preview Evidence">
    <button type="button" id="lvlLightboxClose" class="lvl-lightbox-close" title="Tutup (ESC)">&times;</button>
    <img id="lvlLightboxImg" src="" alt="Preview Evidence" class="lvl-lightbox-img">
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
<script src="{{ asset('js/checksheet/leveling-inspector.js') }}?v={{ time() }}"></script>
@endpush
