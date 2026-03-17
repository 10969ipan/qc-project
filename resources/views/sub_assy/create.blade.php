@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET OUTGOING SUB ASSY INJECTION
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
                                {{ $plantCode === 'jakarta' ? 'QC - JKT - F - 034/0' : 'QC-KRW-F-0213' }}
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                {{ $plantCode === 'jakarta' ? '18.02.2022' : '25/03/2015' }}
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi / Tgl</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                {{ $plantCode === 'jakarta' ? '0 / 30-Dec-99' : '3 / 22/12/2025' }}
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
        $plant = strtolower(optional(auth()->user()->plant)->code ?? request('plant') ?? '');
        $tableOptions = range(1, 15);
        if ($plant === 'jakarta') {
            $tableOptions = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
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

    <div class="card shadow mb-4 border-left-warning">
        <a href="#collapseLineStatus" class="d-block card-header py-3" data-toggle="collapse" role="button"
            aria-expanded="true" aria-controls="collapseLineStatus">
            <h6 class="m-0 font-weight-bold text-warning">Control Status Meja (Manual)</h6>
        </a>
        <div class="collapse" id="collapseLineStatus">
            <div class="card-body">
                <form action="{{ route('machine-status.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="line">
                    <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Pilih Meja</label>
                            <select name="number" class="form-control form-control-sm" required>
                                <option value="">- Pilih Meja -</option>
                                @foreach($tableOptions as $i)
                                    <option value="{{ $i }}">MEJA-{{ $i }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Status</label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="normal">NORMAL (Auto)</option>
                                <option value="maintenance">MAINTENANCE (Kuning)</option>
                                <option value="stopped">IDLE (Hitam)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Keterangan (Optional)</label>
                            <input type="text" name="description" class="form-control form-control-sm"
                                placeholder="Keterangan...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-warning btn-sm btn-block">
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Sub Assy</h6>
        </div>
        <div class="card-body">
            <!-- Pemilih Plant untuk Admin -->
            @if(auth()->user()->role === 'admin')
                <form method="GET" action="{{ route('checksheet.sub_assy') }}" class="mb-3">
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

            @php
                $currentPlant = strtolower(request('plant') ?? optional(auth()->user()->plant)->code ?? '');
                $isJakarta = ($currentPlant === 'jakarta');
            @endphp

            {{-- Pilihan Tipe Pengecekan - Hanya untuk Plant Jakarta --}}
            @if($isJakarta)
                <div class="alert alert-info mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="font-weight-bold mb-0"><i class="fas fa-clipboard-check"></i> Tipe Pengecekan:</label>
                        </div>
                        <div class="col-md-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" id="labelSampling">
                                    <input type="radio" name="check_type_option" id="checkTypeSampling" value="sampling"
                                        checked>
                                    <i class="fas fa-chart-pie"></i> Sampling (AQL 0.65)
                                </label>
                                <label class="btn btn-outline-success" id="labelFullcheck">
                                    <input type="radio" name="check_type_option" id="checkTypeFullcheck" value="fullcheck">
                                    <i class="fas fa-check-double"></i> Fullcheck (Export) - 100%
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('checksheet.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <input type="hidden" name="check_type" id="checkTypeInput" value="sampling">
                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <tr class="text-center">
                            <th rowspan="2" style="align-middle">Standard</th>
                            <th rowspan="2" style="align-middle">Item Part</th>
                            <th rowspan="2" style="align-middle">Tanggal / Shift</th>
                            <th rowspan="2" style="align-middle">Total Qty</th>
                            <th rowspan="2" style="align-middle">Sampling Qty</th>
                            <th rowspan="2" style="align-middle; min-width: 280px;">Jenis (OK/NG) & Detail NG</th>
                            <th rowspan="2" style="align-middle">Total (OK/NG)</th>
                            <th rowspan="2" style="align-middle">Judgment</th>
                            <th rowspan="2" style="align-middle">Inisial QC</th>
                            <th rowspan="2" style="align-middle">Keterangan</th>
                        </tr>
                        <tbody>
                            <tr>
                                <!-- Ilustrasi Barang -->
                                <td class="align-middle text-center" id="imageContainer">
                                    <div
                                        style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-image fa-2x text-gray-300"></i>
                                    </div>
                                </td>

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
                                                    data-name="{{ $item->name }}" data-description="{{ $item->description }}"
                                                    data-defects="{{ json_encode($item->defects) }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}">
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
                                    <div class="form-group mb-2">
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
                                    <div class="form-group mb-0">
                                        <label class="sr-only">Meja</label>
                                        <select name="line" id="line" class="form-control" style="min-width: 80px;"
                                            required>
                                            <option value="">Pilih Meja</option>
                                            @foreach ($tableOptions as $i)
                                                <option value="{{ $i }}">Meja {{ $i }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Kuantitas (Total Kuantitas yang diproduksi) -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="total_qty" placeholder="0" min="0" required>
                                </td>

                                <!-- Jumlah Pengecekan Sampel -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="sampling_qty" placeholder="0" min="0" required>
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
                                            Proses:</label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            <option value="CRUSHING">CRUSHING</option>
                                            <option value="SORTIR">SORTIR</option>
                                            <option value="FINISHING">FINISHING</option>
                                            <option value="REPAIR">REPAIR</option>
                                            <option value="MARKING+FINISHING+PACKING">MARKING+FINISHING+PACKING</option>
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
                    <h5 class="modal-title" id="pdfModalLabel">STANDARD (PDF)</h5>
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
    <script src="{{ asset('js/checksheet/sub-assy.js') }}"></script>
    <script>
        $(document).ready(function () {
            window.initSubAssyCreate({
                pdfWorkerSrc: "{{ asset('js/vendor/pdf.worker.min.js') }}",
                pdfUrlPattern: "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
            });
        });
    </script>
@endpush
