@extends('layouts.admin')

@section('title', 'Input Data Sortir')

@section('content')

    @php
        $plant = $plant ?? request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
    @endphp
        @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('sortir', $headerPlantCode, [
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
                            INPUT DATA SORTIR
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
        $plant = strtolower(optional(auth()->user()->plant)->code ?? request('plant') ?? '');
        $tableOptions = range(1, 15);
        if ($plant === 'jakarta') {
            $tableOptions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        }
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Sortir (Item NG dari Sub Assy & In-Process)
            </h6>
        </div>
        <div class="card-body">


            <form action="{{ route('sortir.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <input type="hidden" name="source_type" id="sourceType">
                <input type="hidden" name="source_id" id="sourceId">

                <div class="table-responsive">
                    <table class="table" id="checksheetTable" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2" style="width: 120px;">Standard</th>
                                <th rowspan="2">Item Part (NG)</th>
                                <th rowspan="2">Tanggal / Shift</th>
                                <th rowspan="2">Total Qty</th>
                                <th rowspan="2">Sampling Qty</th>
                                <th rowspan="2" style="min-width: 280px;">Jenis (OK/NG) &amp; Detail NG</th>
                                <th rowspan="2">Total (OK/NG)</th>
                                <th rowspan="2">Judgment</th>
                                <th rowspan="2">Inisial QC</th>
                                <th rowspan="2">DESCRIPTION</th>
                            </tr>
                            <tr></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Standard (PDF) -->
                                <td class="align-middle text-center" id="imageContainer">
                                    <div
                                        style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-image fa-2x text-gray-300"></i>
                                    </div>
                                </td>
                                <!-- Item Part (Hanya NG) -->
                                <td class="align-middle">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part NG</label>
                                        <select class="form-control" name="item_id" id="ngItemSelect" required
                                            style="min-width: 300px;">
                                            <option value="" disabled selected>Pilih Item NG</option>
                                            @foreach($ngItems as $ngItem)
                                                <option value="{{ $ngItem['item_id'] }}"
                                                    data-name="{{ $ngItem['item_name'] }}"
                                                    data-part-number="{{ $ngItem['part_number'] ?? '' }}"
                                                    data-detail="{{ strtoupper(str_replace('_', ' ', $ngItem['source_type'])) }} · {{ $ngItem['date'] }} Shift {{ $ngItem['shift'] }} · Sisa: {{ number_format($ngItem['remaining_qty']) }} pcs{{ !empty($ngItem['next_proses']) ? ' · ' . $ngItem['next_proses'] : '' }}"
                                                    data-source-type="{{ $ngItem['source_type'] }}"
                                                    data-source-id="{{ $ngItem['source_id'] }}"
                                                    data-source-date="{{ $ngItem['date'] }}"
                                                    data-source-shift="{{ $ngItem['shift'] }}"
                                                    data-remaining-qty="{{ $ngItem['remaining_qty'] }}"
                                                    data-files="{{ json_encode($ngItem['file_paths'] ?? ($ngItem['file_path'] ? [$ngItem['file_path']] : [])) }}"
                                                    data-defects="{{ $ngItem['defects'] ?? '' }}">
                                                    {{ $ngItem['item_name'] }} ({{ $ngItem['part_number'] }})
                                                    - {{ strtoupper(str_replace('_', ' ', $ngItem['source_type'])) }}
                                                    @if(!empty($ngItem['next_proses']))
                                                        [{{ $ngItem['next_proses'] }}]
                                                    @endif
                                                    - {{ $ngItem['date'] }} Shift {{ $ngItem['shift'] }}
                                                    | Total: {{ number_format($ngItem['total_qty']) }} pcs
                                                    @if($ngItem['sorted_qty'] > 0)
                                                        | Sudah: {{ number_format($ngItem['sorted_qty']) }}
                                                    @endif
                                                    | Sisa: {{ number_format($ngItem['remaining_qty']) }} pcs
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Hanya menampilkan item NG dengan sisa qty > 0</small>
                                    </div>
                                </td>

                                <!-- Tanggal / Shift -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <input type="date" class="form-control" style="min-width: 110px;" name="date"
                                            value="{{ $defaultDate }}" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <select class="form-control" style="min-width: 80px;" name="shift" required>
                                            <option value="1" {{ ($defaultShift ?? 1) == 1 ? 'selected' : '' }}>Shift 1
                                            </option>
                                            <option value="2" {{ ($defaultShift ?? 1) == 2 ? 'selected' : '' }}>Shift 2
                                            </option>
                                            <option value="3" {{ ($defaultShift ?? 1) == 3 ? 'selected' : '' }}>Shift 3
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <select name="line" class="form-control" style="min-width: 80px;">
                                            <option value="">Pilih Meja (Optional)</option>
                                            @foreach ($tableOptions as $i)
                                                <option value="{{ $i }}">Meja {{ $i }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="total_qty" placeholder="0" min="0" required>
                                </td>

                                <!-- Sampling Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="sampling_qty" placeholder="0" min="0" required>
                                </td>

                                <td class="align-middle" style="min-width: 280px;">

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
                                                <input type="number" class="form-control text-center font-weight-bold"
                                                    name="defect_quantities[]" placeholder="Qty" min="1">
                                            </div>
                                            <div class="col-1 text-center"></div>
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-info btn-sm mt-1">
                                        <i class="fas fa-plus"></i> Tambah Defect
                                    </button>
                                </td>

                                <!-- Total OK / NG -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <div class="d-flex align-items-center mb-1" style="gap:4px;">
                                        <span class="ok-label">OK</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0;" name="total_ok" placeholder="0" min="0" required>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:4px;">
                                        <span class="ng-label">NG</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0;" name="total_ng" placeholder="0" min="0" required>
                                    </div>
                                </td>

                                <!-- Keputusan -->
                                <td class="align-middle text-center" style="min-width: 150px;">
                                    <div id="judgmentBadge" class="mb-2 p-3 font-weight-bold h4 rounded d-none shadow-sm"
                                        style="border: 2px solid transparent;">
                                        -
                                    </div>
                                    <select class="form-control font-weight-bold d-none" name="judgment" id="judgmentSelect"
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

                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" style="min-width: 60px;"
                                        name="operator_initials" placeholder="Inisial"
                                        value="{{ auth()->user()->initials ?? '' }}" required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle" style="min-width: 320px;">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next Proses:</label>
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
                            <i class="fas fa-save fa-sm"></i> Simpan Data Sortir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Penampil PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Standard PDF Viewer</h5>
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
    <script src="{{ asset('js/checksheet/sortir.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initSortirCreate({
                heartbeatUrl: "{{ route('session.ping') }}",
                pdfWorkerSrc: "{{ asset('js/vendor/pdf.worker.min.js') }}",
                pdfUrlPattern: "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
            });
            window.initItemSearch('ngItemSelect', {
                placeholder: 'Cari nama item / part no...'
            });
        });
    </script>
@endpush
