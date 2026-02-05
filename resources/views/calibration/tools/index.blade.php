@extends('layouts.admin')

@section('content')
    <style>
        .last-child-no-border:last-child {
            border-bottom: none !important;
        }

        .whitespace-nowrap {
            white-space: nowrap;
        }

        #dataTable {
            font-size: 0.75rem;
            table-layout: fixed;
        }

        #dataTable thead th {
            padding: 0.4rem 0.2rem !important;
            vertical-align: middle;
            font-size: 0.7rem;
        }

        #dataTable td {
            padding: 0.25rem 0.15rem !important;
            white-space: normal !important;
            word-wrap: break-word;
            vertical-align: middle;
        }

        .col-aksi {
            width: 100px !important;
            min-width: 100px !important;
        }

        .col-no {
            width: 25px;
        }

        .col-bagian {
            width: 60px;
        }

        .col-name {
            width: 110px;
        }

        .col-seri {
            width: 80px;
        }

        .col-range {
            width: 60px;
        }

        .col-res {
            width: 50px;
        }

        .col-lokasi {
            width: 70px;
        }

        .col-tgl {
            width: 70px;
        }

        .col-freq {
            width: 70px;
        }

        .col-hist {
            width: 50px;
        }

        .col-jenis {
            width: 60px;
        }

        .col-sch {
            width: 75px;
        }

        .col-pr {
            width: 90px;
        }

        .col-status {
            width: 45px;
        }

        /* Compact elements inside table */
        #dataTable .form-control-sm {
            height: calc(1.5em + 0.25rem + 2px) !important;
            padding: 0.125rem 0.25rem !important;
            font-size: 0.7rem !important;
        }

        #dataTable .btn-sm {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.65rem !important;
        }

        .schedule-item {
            min-height: 40px !important;
            margin-bottom: 2px !important;
            padding-bottom: 2px !important;
        }
    </style>
    <div class="container-fluid">
        <x-plant-header title="Master Data Alat" :plant="$plantCode">
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                    data-target="#modalTambahAlat">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Alat
                </button>
            @endif
        </x-plant-header>

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
                <h6 class="m-0 font-weight-bold text-primary">Daftar Alat Ukur</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.tools.index') }}" method="GET" class="row align-items-end mb-4">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <!-- Pencarian Alat -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Pencarian Alat</label>
                            <input type="text" name="search" class="form-control form-control-sm shadow-sm"
                                placeholder="Nama / No. Seri" value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Jenis Kalibrasi -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Jenis Kalibrasi</label>
                            <select name="jenis_kalibrasi" class="form-control form-control-sm shadow-sm">
                                <option value="">Semua</option>
                                <option value="INTERNAL" {{ request('jenis_kalibrasi') === 'INTERNAL' ? 'selected' : '' }}>
                                    INTERNAL</option>
                                <option value="EKSTERNAL" {{ request('jenis_kalibrasi') === 'EKSTERNAL' ? 'selected' : '' }}>
                                    EKSTERNAL</option>
                            </select>
                        </div>
                    </div>

                    <!-- Planning Dari -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Planning Dari</label>
                            <input type="date" name="start_date" class="form-control form-control-sm shadow-sm"
                                value="{{ request('start_date') }}">
                        </div>
                    </div>

                    <!-- Sampai -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Sampai</label>
                            <input type="date" name="end_date" class="form-control form-control-sm shadow-sm"
                                value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Status</label>
                            <select name="verification_status" class="form-control form-control-sm shadow-sm">
                                <option value="">Semua</option>
                                <option value="ok" {{ request('verification_status') === 'ok' ? 'selected' : '' }}>
                                    Sudah OK</option>
                                <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>
                                    Belum</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-2">
                        <div class="d-flex" style="gap: 5px;">
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-3" title="Cari Data">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                            <a href="{{ route('calibration.tools.index', ['plant' => $plantCode]) }}"
                                class="btn btn-secondary btn-sm shadow-sm px-3" title="Reset Filter">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <hr class="my-4 border-light">

                <table class="table table-bordered table-sm text-center align-middle" id="dataTable" width="100%"
                    cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th class="align-middle col-no">NO.</th>
                            <th class="align-middle col-bagian">BAGIAN</th>
                            <th class="align-middle col-name">NAMA ALAT</th>
                            <th class="align-middle col-seri">NO. SERI</th>
                            <th class="align-middle col-range">RANGE</th>
                            <th class="align-middle col-res">RESOLUSI</th>
                            <th class="align-middle col-lokasi">LOKASI PAKAI</th>
                            <th class="align-middle col-tgl">TANGGAL BELI</th>
                            <th class="align-middle col-freq">FREKUENSI KALIBRASI</th>
                            <th class="align-middle col-hist">RIWAYAT</th>
                            <th class="align-middle col-jenis">JENIS</th>
                            <th class="align-middle col-sch">SCHEDULE</th>
                            <th class="align-middle whitespace-nowrap col-pr">PR</th>
                            <th class="align-middle col-status">STAT</th>
                            <th class="align-middle text-center col-aksi">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tools as $index => $tool)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $tool->bagian }}</td>
                                <td>{{ $tool->name_alat }}</td>
                                <td>{{ $tool->serial_number }}</td>
                                <td>{{ $tool->range }}</td>
                                <td>{{ $tool->resolusi }}</td>
                                <td>{{ $tool->lokasi_pakai }}</td>
                                <td>{{ $tool->tanggal_beli ? $tool->tanggal_beli->format('d/m/Y') : '-' }}</td>
                                <td>{{ $tool->frekuensi_kalibrasi }}</td>
                                <td>{{ $tool->riwayat_kalibrasi ?? '-' }}</td>
                                <td>{{ $tool->jenis_kalibrasi }}</td>
                                <td>
                                    @php
                                        $scheduledStatuses = $tool->getScheduledStatuses(date('Y'));
                                    @endphp
                                    @if(!empty($scheduledStatuses))
                                        @foreach($scheduledStatuses as $item)
                                            <div class="mb-1 pb-1 border-bottom last-child-no-border schedule-item"
                                                style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <span
                                                    class="badge badge-info">{{ \Carbon\Carbon::parse($item->schedule_date)->format('d/m/Y') }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($scheduledStatuses))
                                        @foreach($scheduledStatuses as $item)
                                            <div class="mb-1 pb-1 border-bottom last-child-no-border schedule-item"
                                                style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                @if(!$item->is_ok)
                                                    @if($tool->jenis_kalibrasi !== 'INTERNAL')
                                                        <div class="d-flex align-items-center justify-content-center" style="gap: 5px;">
                                                            <input type="text" class="form-control form-control-sm pr-input text-center"
                                                                data-schedule-id="{{ $item->id }}" placeholder="PR..."
                                                                value="{{ $item->pr_number }}" style="width: 70px;">
                                                            @if($item->pr_number)
                                                                <button type="button" class="btn btn-sm btn-outline-danger reset-pr"
                                                                    data-schedule-id="{{ $item->id }}" title="Reset PR">
                                                                    <i class="fas fa-undo"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="text-muted small">INTERNAL</div>
                                                    @endif
                                                @else
                                                    <div class="font-weight-bold">{{ $item->pr_number ?? '-' }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($scheduledStatuses))
                                        @foreach($scheduledStatuses as $item)
                                            <div class="mb-1 pb-1 border-bottom last-child-no-border schedule-item"
                                                style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                @php
                                                    $planningDate = \Carbon\Carbon::parse($item->schedule_date);
                                                    $today = now()->startOfDay();
                                                    $prDate = $item->pr_date ? \Carbon\Carbon::parse($item->pr_date) : null;

                                                    $icon = '<div class="d-inline-block position-relative" title="Belum PR" style="width: 25px; height: 25px; vertical-align: middle;">' .
                                                        '<i class="fas fa-calendar text-secondary" style="font-size: 1.3rem;"></i>' .
                                                        '<i class="fas fa-clock text-secondary" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.75rem; background: white; border-radius: 50%; box-shadow: 0 0 0 2px white;"></i>' .
                                                        '</div>';
                                                    $isClickable = false;
                                                    $statusText = 'Belum PR';

                                                    if ($item->is_ok) {
                                                        $icon = '<i class="fas fa-check-circle text-success fa-lg" title="Sudah Verifikasi"></i>';
                                                        $statusText = 'Sudah Verifikasi';
                                                        $isClickable = true;
                                                    } elseif ($tool->jenis_kalibrasi === 'INTERNAL') {
                                                        $icon = '<div class="d-inline-block position-relative" title="Siap Verifikasi" style="width: 25px; height: 25px; vertical-align: middle;">' .
                                                            '<i class="fas fa-calendar text-secondary" style="font-size: 1.3rem;"></i>' .
                                                            '<i class="fas fa-clock text-secondary" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.75rem; background: white; border-radius: 50%; box-shadow: 0 0 0 2px white;"></i>' .
                                                            '</div>';
                                                        $statusText = 'Siap Verifikasi';
                                                    } elseif ($item->pr_number) {
                                                        $diffDays = $today->diffInDays($planningDate, false);

                                                        if ($diffDays < 0) {
                                                            $icon = '<i class="fas fa-exclamation-circle text-danger fa-lg" title="Melewati Jadwal"></i>';
                                                            $statusText = 'Melewati Jadwal';
                                                        } elseif ($diffDays >= 30) {
                                                            $icon = '<i class="fas fa-hourglass-half text-info fa-lg" title="On Progress"></i>';
                                                            $statusText = 'On Progress';
                                                        } else {
                                                            $icon = '<i class="fas fa-exclamation-triangle text-warning fa-lg" title="Segera Verifikasi"></i>';
                                                            $statusText = 'Segera Verifikasi';
                                                        }
                                                    } elseif ($today->gt($planningDate)) {
                                                        $icon = '<i class="fas fa-exclamation-circle text-danger fa-lg" title="Melewati Jadwal"></i>';
                                                        $statusText = 'Melewati Jadwal';
                                                    }
                                                @endphp

                                                @if($isClickable)
                                                                    <a href="{{ route('calibration.verifications.index', [
                                                        'plant' => $plantCode,
                                                        'tool_id' => $tool->id,
                                                        'start_date' => \Carbon\Carbon::parse($item->schedule_date)->copy()->startOfMonth()->format('Y-m-d'),
                                                        'end_date' => \Carbon\Carbon::parse($item->schedule_date)->copy()->endOfMonth()->format('Y-m-d')
                                                    ]) }}" style="text-decoration: none;">
                                                                        {!! $icon !!}
                                                                    </a>
                                                @else
                                                    {!! $icon !!}
                                                @endif
                                                <small class="pr-date-display text-muted mt-1" id="pr-date-{{ $item->id }}">
                                                    {{ $item->pr_date ? \Carbon\Carbon::parse($item->pr_date)->format('d/m/Y') : '-' }}
                                                </small>
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center" style="gap: 5px; white-space: nowrap;">
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                                            <button type="button" class="btn btn-sm btn-success btn-verifikasi" data-toggle="modal"
                                                data-target="#modalVerifikasiBaru" data-tool-id="{{ $tool->id }}"
                                                title="Input Verifikasi">
                                                <i class="fas fa-check-circle"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-info btn-edit-tool"
                                                data-id="{{ $tool->id }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDeleteTool('{{ $tool->id }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="delete-tool-form-{{ $tool->id }}"
                                                action="{{ route('calibration.tools.destroy', $tool->id) }}" method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="plant" value="{{ $plantCode }}">
                                            </form>
                                        @endif

                                        @if($tool->certification_path)
                                            <button type="button" class="btn btn-sm btn-primary view-pdf" data-toggle="modal"
                                                data-target="#pdfModal"
                                                data-url="{{ asset('storage/' . $tool->certification_path) }}"
                                                data-title="Sertifikat - {{ $tool->name_alat }}">
                                                <i class="fas fa-file-pdf"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">Tidak ada data alat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PDF Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="pdfModalLabel">Lihat Sertifikat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfFrame" src="" width="100%" height="600px" style="border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <a id="downloadPdf" href="#" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Tambah Alat -->
    <div class="modal fade" id="modalTambahAlat" tabindex="-1" role="dialog" aria-labelledby="modalTambahAlatLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahAlatLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Master Data Alat
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('calibration.tools.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Bagian</label>
                                    <input type="text" name="bagian" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Nama Alat</label>
                                    <input type="text" name="name_alat" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">No. Seri</label>
                                    <input type="text" name="serial_number" class="form-control form-control-sm" required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Range</label>
                                            <input type="text" name="range" class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Resolusi</label>
                                            <input type="text" name="resolusi" class="form-control form-control-sm"
                                                required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Lokasi Pakai</label>
                                    <input type="text" name="lokasi_pakai" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Tanggal Beli</label>
                                    <input type="date" name="tanggal_beli" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Frekuensi Kalibrasi</label>
                                    <input type="text" name="frekuensi_kalibrasi" class="form-control form-control-sm"
                                        placeholder="Contoh: 1 Tahun" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Jenis Kalibrasi</label>
                                    <select name="jenis_kalibrasi" class="form-control form-control-sm" required>
                                        <option value="Internal">Internal</option>
                                        <option value="Eksternal">Eksternal</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2" id="modal-schedule-container">
                                    <label class="small font-weight-bold">Schedule Planning</label>
                                    <div class="input-group input-group-sm mb-2">
                                        <input type="date" name="schedule_planning[]" class="form-control" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" id="modal-add-schedule-btn">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-left mb-2">
                            <label class="small font-weight-bold">Riwayat Kalibrasi</label>
                            <textarea name="riwayat_kalibrasi" class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <div class="form-group text-left mb-0">
                            <label class="small font-weight-bold">Sertifikasi (PDF)</label>
                            <input type="file" name="certification" class="form-control-file" accept=".pdf">
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Edit Alat -->
    <div class="modal fade" id="modalEditAlat" tabindex="-1" role="dialog" aria-labelledby="modalEditAlatLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalEditAlatLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Master Data Alat
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditAlat" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Bagian <span class="text-danger">*</span></label>
                                    <input type="text" name="bagian" id="edit_bagian" class="form-control form-control-sm"
                                        required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Nama Alat <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name_alat" id="edit_name_alat"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">No. Seri <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="serial_number" id="edit_serial_number"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Range</label>
                                            <input type="text" name="range" id="edit_range"
                                                class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Resolusi</label>
                                            <input type="text" name="resolusi" id="edit_resolusi"
                                                class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Lokasi Pakai <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="lokasi_pakai" id="edit_lokasi_pakai"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Tgl. Beli</label>
                                    <input type="date" name="tanggal_beli" id="edit_tanggal_beli"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Freq. Kalibrasi <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="frekuensi_kalibrasi" id="edit_frekuensi_kalibrasi"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Jenis Kalibrasi <span
                                            class="text-danger">*</span></label>
                                    <select name="jenis_kalibrasi" id="edit_jenis_kalibrasi"
                                        class="form-control form-control-sm" required>
                                        <option value="Internal">Internal</option>
                                        <option value="Eksternal">Eksternal</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-left mb-2">
                            <label class="small font-weight-bold">Jadwal Kalibrasi (Planning) <span
                                    class="text-danger">*</span></label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="edit-schedule-table">
                                    <thead class="bg-light small text-center">
                                        <tr>
                                            <th>Tanggal Planning</th>
                                            <th width="40"><button type="button"
                                                    class="btn btn-xs btn-success add-edit-schedule-row"><i
                                                        class="fas fa-plus"></i></button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Will be filled by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group text-left mb-2">
                            <label class="small font-weight-bold">Riwayat Kalibrasi</label>
                            <textarea name="riwayat_kalibrasi" id="edit_riwayat_kalibrasi"
                                class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <div class="form-group text-left mb-0">
                            <label class="small font-weight-bold">Sertifikasi (PDF Baru)</label>
                            <input type="file" name="certification" class="form-control-file" accept=".pdf">
                            <div id="edit_existing_cert" class="mt-1"></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Verifikasi Baru -->
    <div class="modal fade" id="modalVerifikasiBaru" tabindex="-1" role="dialog" aria-labelledby="modalVerifikasiBaruLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow text-left">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalVerifikasiBaruLabel">
                        <i class="fas fa-check-circle mr-2"></i> Input Verifikasi Alat Ukur
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('calibration.verifications.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Pilih Alat Ukur <span
                                            class="text-danger">*</span></label>
                                    <select name="tool_id" id="modal_verif_tool_select" class="form-control form-control-sm"
                                        required>
                                        <option value="">-- Pilih Alat --</option>
                                        @foreach($tools as $t)
                                            <option value="{{ $t->id }}" data-name="{{ $t->name_alat }}"
                                                data-serial="{{ $t->serial_number }}" data-range="{{ $t->range }}"
                                                data-resolusi="{{ $t->resolusi }}"
                                                data-frekuensi="{{ $t->frekuensi_kalibrasi }}"
                                                data-lokasi="{{ $t->lokasi_pakai }}"
                                                data-schedules="{{ json_encode($t->schedules->pluck('schedule_date')->map(fn($d) => $d->format('Y-m-d'))) }}">
                                                {{ $t->name_alat }} ({{ $t->serial_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Nama Alat</label>
                                    <input type="text" name="name_alat" id="modal_verif_name_alat"
                                        class="form-control form-control-sm bg-light" readonly required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Merk <span class="text-danger">*</span></label>
                                    <input type="text" name="merk" class="form-control form-control-sm" required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">No. Seri</label>
                                            <input type="text" name="serial_number" id="modal_verif_serial_number"
                                                class="form-control form-control-sm bg-light" readonly required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Resolusi</label>
                                            <input type="text" name="resolusi" id="modal_verif_resolusi"
                                                class="form-control form-control-sm bg-light" readonly required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Rentang Ukur (Range)</label>
                                    <input type="text" name="rentang_ukur" id="modal_verif_rentang_ukur"
                                        class="form-control form-control-sm bg-light" readonly required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Tgl. Kalibrasi <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_kalibrasi" class="form-control form-control-sm"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Tgl. Verifikasi <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_verifikasi" id="modal_verif_tanggal_verifikasi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Next Kalibrasi <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="next_kalibrasi" id="modal_verif_next_kalibrasi"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Frekuensi Kalibrasi</label>
                                    <input type="text" name="frekuensi_kalibrasi" id="modal_verif_frekuensi_kalibrasi"
                                        class="form-control form-control-sm bg-light" readonly required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Lokasi Penyimpanan</label>
                                    <input type="text" name="lokasi_penyimpanan" id="modal_verif_lokasi_penyimpanan"
                                        class="form-control form-control-sm bg-light" readonly required>
                                </div>
                            </div>
                        </div>

                        {{-- Table Detail Verifikasi --}}
                        <div class="card bg-light mb-3">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold small">Detail Verifikasi</h6>
                                <button type="button" class="btn btn-xs btn-info" id="modal-verif-add-row">
                                    <i class="fas fa-plus"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="bg-white small">
                                            <tr class="text-center">
                                                <th>Nilai Alat</th>
                                                <th>Nilai Koreksi</th>
                                                <th>Ketidakpastian</th>
                                                <th>Hasil Verifikasi</th>
                                                <th style="width: 40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-verif-verification-body">
                                            <tr>
                                                <td><input type="text" name="nilai_alat[]"
                                                        class="form-control form-control-sm" required></td>
                                                <td><input type="text" name="nilai_koreksi[]"
                                                        class="form-control form-control-sm" required></td>
                                                <td><input type="text" name="nilai_ketidakpastian[]"
                                                        class="form-control form-control-sm" required></td>
                                                <td><input type="text" name="hasil_verifikasi[]"
                                                        class="form-control form-control-sm" required></td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger modal-verif-remove-row"
                                                        disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Judgment <span
                                            class="text-danger">*</span></label>
                                    <select name="judgment" class="form-control form-control-sm" required>
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Std. Toleransi <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="std_toleransi" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Acuan Toleransi <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="acuan_toleransi" class="form-control form-control-sm" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Sertifikasi (PDF)</label>
                            <input type="file" name="certification" class="form-control-file small" accept=".pdf">
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Modal Add Schedule rows
            $('#modal-add-schedule-btn').click(function () {
                var html = `
                                                                        <div class="input-group input-group-sm mb-2">
                                                                            <input type="date" name="schedule_planning[]" class="form-control" required>
                                                                            <div class="input-group-append">
                                                                                <button class="btn btn-danger modal-remove-schedule" type="button">
                                                                                    <i class="fas fa-minus"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>`;
                $('#modal-schedule-container').append(html);
            });

            $(document).on('click', '.modal-remove-schedule', function () {
                $(this).closest('.input-group').remove();
            });

            // --- Modal Verifikasi logic ---
            $('.btn-verifikasi').on('click', function () {
                var toolId = $(this).data('tool-id');
                $('#modal_verif_tool_select').val(toolId).trigger('change');
            });

            $('#modal_verif_tool_select').on('change', function () {
                var selected = $(this).find('option:selected');
                if (selected.val()) {
                    $('#modal_verif_name_alat').val(selected.data('name'));
                    $('#modal_verif_serial_number').val(selected.data('serial'));
                    $('#modal_verif_rentang_ukur').val(selected.data('range'));
                    $('#modal_verif_resolusi').val(selected.data('resolusi'));
                    $('#modal_verif_frekuensi_kalibrasi').val(selected.data('frekuensi'));
                    $('#modal_verif_lokasi_penyimpanan').val(selected.data('lokasi'));

                    modalVerifUpdateNextCalibrationDate();
                }
            });

            $('#modal_verif_tanggal_verifikasi').on('change', function () {
                modalVerifUpdateNextCalibrationDate();
            });

            function modalVerifUpdateNextCalibrationDate() {
                var selected = $('#modal_verif_tool_select').find('option:selected');
                var verifDate = $('#modal_verif_tanggal_verifikasi').val();

                if (!selected.val() || !selected.data('schedules')) return;

                var schedules = selected.data('schedules');
                if (typeof schedules === 'string') {
                    schedules = JSON.parse(schedules);
                }

                if (schedules.length > 0) {
                    schedules.sort();
                    var referenceDate = verifDate || new Date().toISOString().split('T')[0];
                    var nextDate = schedules.find(date => date > referenceDate);
                    if (nextDate) {
                        $('#modal_verif_next_kalibrasi').val(nextDate);
                    }
                }
            }

            $('#modal-verif-add-row').on('click', function () {
                var newRow = `
                                                        <tr>
                                                            <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm" required></td>
                                                            <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm" required></td>
                                                            <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm" required></td>
                                                            <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm" required></td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-outline-danger modal-verif-remove-row">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>`;
                $('#modal-verif-verification-body').append(newRow);
                modalVerifUpdateRemoveButtons();
            });

            $(document).on('click', '.modal-verif-remove-row', function () {
                $(this).closest('tr').remove();
                modalVerifUpdateRemoveButtons();
            });

            function modalVerifUpdateRemoveButtons() {
                var rowCount = $('#modal-verif-verification-body tr').length;
                if (rowCount <= 1) {
                    $('.modal-verif-remove-row').prop('disabled', true);
                } else {
                    $('.modal-verif-remove-row').prop('disabled', false);
                }
            }

            // Initialize DataTable if it exists
            if ($.fn.DataTable) {
                $('#dataTable').DataTable({
                    dom: "<'row px-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                        "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                        "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(difilter dari _MAX_ total data)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
            }

            // Using DataTables compatible modal trigger
            $('#pdfModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var url = button.data('url');
                var title = button.data('title');

                var modal = $(this);
                modal.find('#pdfModalLabel').text(title);
                modal.find('#pdfFrame').attr('src', url);
                modal.find('#downloadPdf').attr('href', url);
            });

            // Clear iframe src when modal is closed to stop loading/playback
            $('#pdfModal').on('hidden.bs.modal', function () {
                $(this).find('#pdfFrame').attr('src', '');
            });

            // PR Input Change
            $('.pr-input').on('change', function () {
                var input = $(this);
                var scheduleId = input.data('schedule-id');
                var prNumber = input.val();
                var display = $('#pr-date-' + scheduleId);

                $.ajax({
                    url: "{{ route('calibration.tools.update-pr') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        schedule_id: scheduleId,
                        pr_number: prNumber
                    },
                    success: function (response) {
                        if (response.success) {
                            display.text(response.pr_date);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memperbarui PR.'
                        });
                    }
                });
            });

            // Reset PR Click
            $('.reset-pr').on('click', function () {
                var button = $(this);
                var scheduleId = button.data('schedule-id');

                Swal.fire({
                    title: 'Reset PR?',
                    text: "Nomor dan tanggal PR akan dihapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Reset!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('calibration.tools.update-pr') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                schedule_id: scheduleId,
                                pr_number: "" // Send empty to reset
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: 'PR telah direset.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                }
                            }
                        });
                    }
                });
            });
        });

        function confirmDeleteTool(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Seluruh riwayat verifikasi alat ini juga akan terhapus dan tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-tool-form-' + id).submit();
                }
            });
        }

        // Edit Tool Logic
        $('.btn-edit-tool').on('click', function () {
            var id = $(this).data('id');
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            var editUrl = "{{ route('calibration.tools.edit', ':id') }}".replace(':id', id);

            $.ajax({
                url: editUrl,
                type: 'GET',
                data: { plant: "{{ $plantCode }}" },
                success: function (response) {
                    var tool = response.tool;
                    $('#edit_bagian').val(tool.bagian);
                    $('#edit_name_alat').val(tool.name_alat);
                    $('#edit_serial_number').val(tool.serial_number);
                    $('#edit_range').val(tool.range);
                    $('#edit_resolusi').val(tool.resolusi);
                    $('#edit_lokasi_pakai').val(tool.lokasi_pakai);
                    $('#edit_tanggal_beli').val(tool.tanggal_beli ? tool.tanggal_beli.substring(0, 10) : '');
                    $('#edit_frekuensi_kalibrasi').val(tool.frekuensi_kalibrasi);
                    $('#edit_jenis_kalibrasi').val(tool.jenis_kalibrasi);
                    $('#edit_riwayat_kalibrasi').val(tool.riwayat_kalibrasi);

                    // Certificate
                    if (tool.certification_path) {
                        $('#edit_existing_cert').html(`<a href="/storage/${tool.certification_path}" target="_blank" class="badge badge-info"><i class="fas fa-file-pdf mr-1"></i> Lihat Sertifikat</a>`);
                    } else {
                        $('#edit_existing_cert').html('');
                    }

                    // Schedules
                    var schHtml = '';
                    if (tool.schedules && tool.schedules.length > 0) {
                        tool.schedules.forEach(function (sch) {
                            schHtml += `
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" name="schedule_ids[]" value="${sch.id}">
                                                                    <input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${sch.schedule_date.substring(0, 10)}" required>
                                                                </td>
                                                                <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                                            </tr>`;
                        });
                    } else if (tool.schedule_planning) {
                        schHtml = `
                                                        <tr>
                                                            <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${tool.schedule_planning.substring(0, 10)}" required></td>
                                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                                        </tr>`;
                    } else {
                        schHtml = `
                                                        <tr>
                                                            <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" required></td>
                                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                                        </tr>`;
                    }
                    $('#edit-schedule-table tbody').html(schHtml);

                    // Update form action
                    var url = "{{ route('calibration.tools.update', ':id') }}";
                    url = url.replace(':id', id);
                    $('#formEditAlat').attr('action', url);

                    $('#modalEditAlat').modal('show');
                    btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                },
                error: function (xhr) {
                    var errorMsg = 'Gagal mengambil data alat.';
                    if (xhr.status === 404) errorMsg += ' (Error 404: Alat tidak ditemukan)';
                    else if (xhr.status === 403) errorMsg += ' (Error 403: Anda tidak memiliki akses)';
                    else if (xhr.status === 500) errorMsg += ' (Error 500: Terjadi kesalahan di server)';

                    alert(errorMsg);
                    btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                }
            });
        });

        $(document).on('click', '.add-edit-schedule-row', function () {
            var newRow = `
                                            <tr>
                                                <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" required></td>
                                                <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                            </tr>`;
            $('#edit-schedule-table tbody').append(newRow);
        });

        $(document).on('click', '.remove-schedule-row', function () {
            if ($('#edit-schedule-table tbody tr').length > 1 || $(this).closest('table').attr('id') === 'schedule-table') {
                $(this).closest('tr').remove();
            }
        });
    </script>
@endpush