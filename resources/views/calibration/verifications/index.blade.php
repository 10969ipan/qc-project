@extends('layouts.admin')

@section('title', 'Hasil Verifikasi')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-body py-3">
                <div class="row align-items-start">
                    <div class="col-md-8 border-right">
                        <div class="d-flex align-items-center mb-3">
                            <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase mr-3">
                                HASIL VERIFIKASI ALAT UKUR
                            </h1>
                            <span class="badge badge-{{ strtolower($plantCode) === 'jakarta' ? 'info' : 'primary' }}"
                                style="font-size: 0.8rem;">
                                <i class="fas fa-building mr-1"></i>
                                Plant {{ ucfirst($plantCode) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <div class="col p-0" style="max-width: 280px;">
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                    {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-238' : 'QC-KRW-F-238' }}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tanggal Terbit
                                </div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: 14/07/2025</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi Ke-</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: -</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tanggal Revisi
                                </div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: -</div>
                            </div>
                            <div class="row">
                                <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Halaman</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: -</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Sesuai dengan implementasi SCHEDULE KALIBRASI ALAT UKUR */
            .table-responsive {
                max-height: 75vh !important;
                overflow: auto !important;
                border: 1px solid #dee2e6 !important;
            }

            #dataTable {
                border-collapse: separate !important;
                border-spacing: 0 !important;
            }

            #dataTable thead th {
                position: -webkit-sticky !important;
                position: sticky !important;
                top: 0 !important;
                z-index: 100 !important;
                background-color: #4e73df !important;
                color: white !important;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 0.8rem;
                padding: 10px 5px !important;
                border: 1px solid #ffffff44 !important;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
            }

            #dataTable tbody tr:hover {
                background-color: rgba(78, 115, 223, 0.08) !important;
            }

            #dataTable tbody tr:hover td {
                background-color: rgba(78, 115, 223, 0.08) !important;
            }
        </style>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
                <strong><i class="fas fa-exclamation-circle mr-1"></i> Kesalahan Sistem:</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @php
            /** @var \Illuminate\Support\ViewErrorBag $errors */
        @endphp
        @if(isset($errors) && method_exists($errors, 'any') && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Terjadi kesalahan validasi:</strong>
                <ul class="mb-0 mt-1 pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            @if(session('modal') == 'edit')
            {{-- Edit modal script moved to external JS --}}

            @endif
        @endif


        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Data Hasil Verifikasi</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.verifications.index') }}" method="GET" class="row align-items-end mb-4">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Tahun</label>
                            <select name="year" class="form-control form-control-sm shadow-sm">
                                @php
                                    $currentYear = date('Y');
                                    $selectedYear = request('year', $currentYear);
                                @endphp
                                @for($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control form-control-sm shadow-sm"
                                value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control form-control-sm shadow-sm"
                                value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end" style="gap: 10px;">
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                            <i class="fas fa-search mr-1"></i> CARI
                        </button>
                        <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode]) }}"
                            class="btn btn-secondary btn-sm shadow-sm px-4">
                            <i class="fas fa-sync mr-1"></i> RESET
                        </a>
                        <a href="{{ route('calibration.verifications.pdf', array_merge(request()->all(), ['plant' => $plantCode])) }}"
                            target="_blank" class="btn btn-danger btn-sm shadow-sm px-4">
                            <i class="fas fa-file-pdf mr-1"></i> EXPORT PDF
                        </a>
                    </div>
                </form>

                <hr class="my-4 border-light">

                <table class="table table-bordered text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="align-middle">NO.</th>
                            <th class="align-middle">SERTIFIKASI</th>
                            <th class="align-middle">NAMA ALAT</th>
                            <th class="align-middle">MERK</th>
                            <th class="align-middle">NO. SERI</th>
                            <th class="align-middle">RENTANG UKUR</th>
                            <th class="align-middle">RESOLUSI</th>
                            <th class="align-middle">FREKUENSI KALIBRASI</th>
                            <th class="align-middle">TANGGAL KALIBRASI</th>
                            <th class="align-middle">TANGGAL VERIFIKASI</th>
                            <th class="align-middle">NEXT KALIBRASI</th>
                            <th class="align-middle">NILAI ALAT</th>
                            <th class="align-middle">NILAI KOREKSI</th>
                            <th class="align-middle">NILAI KETIDAKPASTIAN</th>
                            <th class="align-middle">HASIL VERIFIKASI</th>
                            <th class="align-middle">JUDGEMENT</th>
                            <th class="align-middle">STD. TOLERANSI</th>
                            <th class="align-middle">ACUAN TOLERANSI</th>
                            <th class="align-middle">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verifications as $index => $v)
                            <tr>
                                <td class="align-middle">{{ $index + 1 }}</td>
                                <td class="text-center align-middle">
                                    @if($v->certification_path)
                                        <button type="button" class="btn btn-sm btn-primary view-pdf" data-toggle="modal"
                                            data-target="#pdfModal" data-url="{{ asset('storage/' . $v->certification_path) }}"
                                            data-title="Sertifikat - {{ $v->name_alat }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle">{{ $v->name_alat }}</td>
                                <td class="align-middle">{{ $v->merk }}</td>
                                <td class="align-middle">{{ $v->serial_number }}</td>
                                <td class="align-middle">{{ $v->rentang_ukur }}</td>
                                <td class="align-middle">{{ $v->resolusi }}</td>
                                <td class="align-middle">{{ $v->frekuensi_kalibrasi }}</td>
                                <td class="align-middle">
                                    {{ $v->tanggal_kalibrasi ? \Carbon\Carbon::parse($v->tanggal_kalibrasi)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle">
                                    {{ $v->tanggal_verifikasi ? \Carbon\Carbon::parse($v->tanggal_verifikasi)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle">
                                    {{ $v->next_kalibrasi ? \Carbon\Carbon::parse($v->next_kalibrasi)->format('d/m/Y') : '-' }}
                                </td>
                                @php
                                    $arrAlat = is_array($v->nilai_alat) ? $v->nilai_alat : [$v->nilai_alat];
                                    $arrKoreksi = is_array($v->nilai_koreksi) ? $v->nilai_koreksi : [$v->nilai_koreksi];
                                    $arrKetidakpastian = is_array($v->nilai_ketidakpastian) ? $v->nilai_ketidakpastian : [$v->nilai_ketidakpastian];
                                    $arrHasil = is_array($v->hasil_verifikasi) ? $v->hasil_verifikasi : [$v->hasil_verifikasi];
                                    $maxRows = max(count($arrAlat), count($arrKoreksi), count($arrKetidakpastian), count($arrHasil));
                                @endphp
                                <td class="align-middle p-0">
                                    @for($i = 0; $i < $maxRows; $i++)
                                        <div
                                            style="height: 52px; display: flex; align-items: center; justify-content: center; padding: 0 4px; text-align: center; overflow: hidden;{{ $i < $maxRows - 1 ? ' border-bottom: 1px solid #dee2e6;' : '' }}">
                                            {{ $arrAlat[$i] ?? '' }}
                                        </div>
                                    @endfor
                                </td>
                                <td class="align-middle p-0">
                                    @for($i = 0; $i < $maxRows; $i++)
                                        <div
                                            style="height: 52px; display: flex; align-items: center; justify-content: center; padding: 0 4px; text-align: center; overflow: hidden;{{ $i < $maxRows - 1 ? ' border-bottom: 1px solid #dee2e6;' : '' }}">
                                            {{ $arrKoreksi[$i] ?? '' }}
                                        </div>
                                    @endfor
                                </td>
                                <td class="align-middle p-0">
                                    @for($i = 0; $i < $maxRows; $i++)
                                        <div
                                            style="height: 52px; display: flex; align-items: center; justify-content: center; padding: 0 4px; text-align: center; overflow: hidden;{{ $i < $maxRows - 1 ? ' border-bottom: 1px solid #dee2e6;' : '' }}">
                                            {{ $arrKetidakpastian[$i] ?? '' }}
                                        </div>
                                    @endfor
                                </td>
                                <td class="align-middle p-0">
                                    @for($i = 0; $i < $maxRows; $i++)
                                        <div
                                            style="height: 52px; display: flex; align-items: center; justify-content: center; padding: 0 4px; text-align: center; overflow: hidden;{{ $i < $maxRows - 1 ? ' border-bottom: 1px solid #dee2e6;' : '' }}">
                                            {{ $arrHasil[$i] ?? '' }}
                                        </div>
                                    @endfor
                                </td>
                                <td class="align-middle">
                                    @if($v->judgment === 'OK')
                                        <span class="badge badge-success">OK</span>
                                    @elseif($v->judgment === 'NG')
                                        <span class="badge badge-danger">NG</span>
                                    @else
                                        {{ $v->judgment ?: '-' }}
                                    @endif
                                </td>
                                <td class="align-middle">{{ $v->std_toleransi }}</td>
                                <td class="align-middle">{{ $v->acuan_toleransi }}</td>
                                <td class="align-middle">
                                    <div class="d-flex justify-content-center" style="gap: 5px;">
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                                            <button type="button"
                                                class="btn btn-sm btn-info btn-edit-verif shadow-sm d-flex align-items-center"
                                                data-id="{{ $v->id }}">
                                                <i class="fas fa-edit mr-1"></i> EDIT
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-dark btn-qr-modal shadow-sm d-flex align-items-center"
                                                data-id="{{ $v->id }}">
                                                <i class="fas fa-qrcode mr-1"></i> QR
                                            </button>
                                            <form
                                                action="{{ route('calibration.verifications.destroy', [$v->id, 'plant' => $plantCode]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="year" value="{{ $year }}">
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger shadow-sm d-flex align-items-center btn-delete">
                                                    <i class="fas fa-trash mr-1"></i> HAPUS
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="19" class="text-center">Tidak ada data hasil verifikasi.</td>
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
    <!-- Modal Verifikasi Baru -->
    <!-- Modal Edit Verifikasi -->
    <div class="modal fade" id="modalEditVerifikasi" tabindex="-1" role="dialog" aria-labelledby="modalEditVerifikasiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow text-left">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditVerifikasiLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Verifikasi Alat Ukur
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditVerif" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="tool_id" id="edit_tool_id">
                    <div class="modal-body">
                        @if($errors->any() && session('modal') == 'edit')
                            <div class="alert alert-danger px-2 py-1 small">
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Nama Alat</label>
                                    <input type="text" name="name_alat" id="edit_name_alat"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Merk <span class="text-danger">*</span></label>
                                    <input type="text" name="merk" id="edit_merk" class="form-control form-control-sm"
                                        required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">No. Seri</label>
                                            <input type="text" name="serial_number" id="edit_serial_number"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Resolusi</label>
                                            <input type="text" name="resolusi" id="edit_resolusi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Rentang Ukur (Range)</label>
                                    <input type="text" name="rentang_ukur" id="edit_rentang_ukur"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Freq. Kalibrasi</label>
                                            <input type="text" name="frekuensi_kalibrasi" id="edit_frekuensi_kalibrasi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Tgl. Kalibrasi <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_kalibrasi" id="edit_tanggal_kalibrasi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Tgl. Verifikasi <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_verifikasi" id="edit_tanggal_verifikasi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Next Kalibrasi <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="next_kalibrasi" id="edit_next_kalibrasi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Judgment <span
                                                    class="text-danger">*</span></label>
                                            <select name="judgment" id="edit_judgment" class="form-control form-control-sm"
                                                required>
                                                <option value="-">-</option>
                                                <option value="OK">OK</option>
                                                <option value="NG">NG</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Std. Toleransi</label>
                                            <input type="text" name="std_toleransi" id="edit_std_toleransi"
                                                class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Acuan Toleransi</label>
                                            <input type="text" name="acuan_toleransi" id="edit_acuan_toleransi"
                                                class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">Upload PDF Baru (Sertifikat)</label>
                                    <input type="file" name="certification" class="form-control-file" accept=".pdf">
                                    <div id="edit_existing_pdf" class="mt-1"></div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="form-group mb-0">
                                <label class="font-weight-bold small d-flex justify-content-between align-items-center">
                                    Data Pengukuran & Koreksi
                                    <button type="button" class="btn btn-xs btn-success" id="edit-modal-add-row">
                                        <i class="fas fa-plus"></i> Tambah Baris
                                    </button>
                                </label>
                                <div class="table-responsive mt-2">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="bg-light small">
                                            <tr class="text-center">
                                                <th style="min-width: 120px;">Nilai Ditunjukkan Alat</th>
                                                <th style="min-width: 120px;">Nilai Koreksi Alat</th>
                                                <th style="min-width: 120px;">Nilai Ketidakpastian</th>
                                                <th style="min-width: 150px;">Hasil Verifikasi<br><small
                                                        class="text-muted">(Koreksi +
                                                        Ketidakpastian)</small></th>
                                                <th width="40"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="edit-modal-verification-body">
                                            {{-- Will be filled by JS --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning btn-sm px-4 shadow-sm">
                                <i class="fas fa-save mr-1"></i> Update
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

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
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="modal-body">
                        @if($errors->any() && session('modal') == 'create')
                            <div class="alert alert-danger px-2 py-1 small">
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Pilih Alat Ukur <span
                                            class="text-danger">*</span></label>
                                    <select name="tool_id" id="modal_tool_select" class="form-control form-control-sm"
                                        required>
                                        <option value="">-- Pilih Alat --</option>
                                        @foreach($tools as $tool)
                                            <option value="{{ $tool->id }}" data-name="{{ $tool->name_alat }}"
                                                data-serial="{{ $tool->serial_number }}" data-range="{{ $tool->range }}"
                                                data-resolusi="{{ $tool->resolusi }}"
                                                data-frekuensi="{{ $tool->frekuensi_kalibrasi }}"
                                                data-schedules="{{ json_encode($tool->schedules->pluck('schedule_date')->map(fn($d) => $d->format('Y-m-d'))) }}">
                                                {{ $tool->name_alat }} ({{ $tool->serial_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Nama Alat</label>
                                    <input type="text" name="name_alat" id="modal_name_alat"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Merk <span class="text-danger">*</span></label>
                                    <input type="text" name="merk" class="form-control form-control-sm" required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">No. Seri</label>
                                            <input type="text" name="serial_number" id="modal_serial_number"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Resolusi</label>
                                            <input type="text" name="resolusi" id="modal_resolusi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Rentang Ukur (Range)</label>
                                    <input type="text" name="rentang_ukur" id="modal_rentang_ukur"
                                        class="form-control form-control-sm" required>
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
                                            <input type="date" name="tanggal_verifikasi" id="modal_tanggal_verifikasi"
                                                class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Next Kalibrasi <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="next_kalibrasi" id="modal_next_kalibrasi"
                                        class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Frekuensi Kalibrasi</label>
                                    <input type="text" name="frekuensi_kalibrasi" id="modal_frekuensi_kalibrasi"
                                        class="form-control form-control-sm" required>
                                </div>
                            </div>
                        </div>

                        {{-- Table Detail Verifikasi --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="card bg-light mb-3">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold small">Detail Verifikasi</h6>
                                        <button type="button" class="btn btn-xs btn-info" id="modal-add-row">
                                            <i class="fas fa-plus"></i> Tambah Baris
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm mb-0">
                                                <thead class="bg-white small">
                                                    <tr class="text-center">
                                                        <th style="min-width: 120px;">Nilai Alat</th>
                                                        <th style="min-width: 120px;">Nilai Koreksi</th>
                                                        <th style="min-width: 120px;">Ketidakpastian</th>
                                                        <th style="min-width: 150px;">Hasil Verifikasi<br><small
                                                                class="text-muted">(Koreksi +
                                                                Ketidakpastian)</small></th>
                                                        <th style="width: 40px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modal-verification-body">
                                                    <tr>
                                                        <td><input type="text" name="nilai_alat[]"
                                                                class="form-control form-control-sm"></td>
                                                        <td><input type="text" name="nilai_koreksi[]"
                                                                class="form-control form-control-sm calc-input"></td>
                                                        <td><input type="text" name="nilai_ketidakpastian[]"
                                                                class="form-control form-control-sm calc-input"></td>
                                                        <td><input type="text" name="hasil_verifikasi[]"
                                                                class="form-control form-control-sm bg-light" readonly></td>
                                                        <td class="text-center">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger modal-remove-row"
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
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Judgment <span
                                            class="text-danger">*</span></label>
                                    <select name="judgment" class="form-control form-control-sm" required>
                                        <option value="-">-</option>
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Std. Toleransi <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="std_toleransi" class="form-control form-control-sm" required
                                        placeholder="Input Std. Toleransi">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Acuan Toleransi <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="acuan_toleransi" class="form-control form-control-sm" required
                                        placeholder="Input Acuan Toleransi">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">Sertifikasi (PDF)</label>
                                    <input type="file" name="certification" class="form-control-file small" accept=".pdf">
                                </div>
                            </div>
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

    <style>
        .modal-body label {
            margin-bottom: 4px;
        }

        .table-responsive {
            border-radius: 4px;
        }
    </style>



    <!-- Modal QR Code -->
    <div class="modal fade" id="modalQrCode" tabindex="-1" role="dialog" aria-labelledby="modalQrCodeLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalQrCodeLabel">
                        <i class="fas fa-qrcode mr-2"></i> Label QR Code Verifikasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <div id="qr-modal-image" class="mb-4 bg-white d-inline-block p-3 rounded shadow-sm">
                        <!-- QR Image will be injected here -->
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <table class="table table-sm table-borderless m-0 text-left">
                                <tr>
                                    <th width="40%">Nama Alat</th>
                                    <td>: <span id="qr-modal-tool-name" class="font-weight-bold"></span></td>
                                </tr>
                                <tr>
                                    <th>Serial Number</th>
                                    <td>: <span id="qr-modal-serial"></span></td>
                                </tr>
                                <tr>
                                    <th>Tgl Verifikasi</th>
                                    <td>: <span id="qr-modal-date"></span></td>
                                </tr>
                                <tr>
                                    <th>Judgment</th>
                                    <td>: <span id="qr-modal-judgment"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <p class="text-muted small mb-4">
                        <i class="fas fa-mobile-alt mr-1"></i> Scan QR di atas untuk mendownload Laporan Hasil Lengkap.
                    </p>

                    <div class="row no-gutters p-0" style="gap: 10px;">
                        <div class="col">
                            <button type="button" id="qr-modal-download-img" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-download mr-1"></i> DOWNLOAD GAMBAR QR
                            </button>
                        </div>
                        <div class="col">
                            <a href="#" id="qr-modal-download-pdf" target="_blank"
                                class="btn btn-outline-danger btn-block shadow-sm no-loader">
                                <i class="fas fa-file-pdf mr-1"></i> DOWNLOAD PDF (2-PAGE)
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">TUTUP</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.__CALIBRATION_VERIFICATIONS__ = {
            plantCode: "{{ $plantCode }}",
            year: "{{ $year ?? date('Y') }}",
            csrf: "{{ csrf_token() }}",
            routes: {
                edit: "{{ route('calibration.verifications.edit', ':id') }}",
                update: "{{ route('calibration.verifications.update', ':id') }}",
                qrData: "{{ route('calibration.verifications.qr-data', ':id') }}"
            }
        };
    </script>
    <script src="{{ asset('js/calibration/calibration-verifications.js') }}?v={{ filemtime(public_path('js/calibration/calibration-verifications.js')) }}"></script>
    <script>
        $(document).ready(function() {
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
