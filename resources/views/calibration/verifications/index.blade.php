@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <x-plant-header title="Hasil Verifikasi Alat Ukur" :plant="$plantCode">
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                    data-target="#modalVerifikasiBaru">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Verifikasi Baru
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

        @php
            /** @var \Illuminate\Support\ViewErrorBag $errors */
        @endphp
        @if(isset($errors) && method_exists($errors, 'any') && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif


        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Hasil Verifikasi</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.verifications.index') }}" method="GET" class="row align-items-end mb-4">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control form-control-sm shadow-sm"
                                value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control form-control-sm shadow-sm"
                                value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm btn-block">
                            <i class="fas fa-search mr-1"></i> CARI
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode]) }}"
                            class="btn btn-secondary btn-sm shadow-sm btn-block">
                            <i class="fas fa-sync mr-1"></i> RESET
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('calibration.verifications.pdf', array_merge(request()->all(), ['plant' => $plantCode])) }}"
                            target="_blank" class="btn btn-danger btn-sm shadow-sm btn-block">
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
                            <th class="align-middle">LOKASI PENYIMPANAN</th>
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
                                <td class="align-middle">{{ $v->lokasi_penyimpanan }}</td>
                                <td class="align-middle">
                                    {{ $v->tanggal_kalibrasi ? $v->tanggal_kalibrasi->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle">
                                    {{ $v->tanggal_verifikasi ? $v->tanggal_verifikasi->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle">
                                    {{ $v->next_kalibrasi ? $v->next_kalibrasi->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle">
                                    @if(is_array($v->nilai_alat))
                                        <ul class="list-unstyled mb-0 text-dark">
                                            @foreach($v->nilai_alat as $val)
                                                <li class="border-bottom pb-1 mb-1">{{ $val }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ $v->nilai_alat }}
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if(is_array($v->nilai_koreksi))
                                        <ul class="list-unstyled mb-0 text-dark">
                                            @foreach($v->nilai_koreksi as $val)
                                                <li class="border-bottom pb-1 mb-1">{{ $val }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ $v->nilai_koreksi }}
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if(is_array($v->nilai_ketidakpastian))
                                        <ul class="list-unstyled mb-0 text-dark">
                                            @foreach($v->nilai_ketidakpastian as $val)
                                                <li class="border-bottom pb-1 mb-1">{{ $val }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ $v->nilai_ketidakpastian }}
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if(is_array($v->hasil_verifikasi))
                                        <ul class="list-unstyled mb-0 text-dark">
                                            @foreach($v->hasil_verifikasi as $val)
                                                <li class="border-bottom pb-1 mb-1">{{ $val }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ $v->hasil_verifikasi }}
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $v->judgment === 'OK' ? 'success' : 'danger' }}">
                                        {{ $v->judgment }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $v->std_toleransi }}</td>
                                <td class="align-middle">{{ $v->acuan_toleransi }}</td>
                                <td class="align-middle">
                                    <div class="d-flex justify-content-center" style="gap: 5px;">
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                            <button type="button"
                                                class="btn btn-sm btn-info btn-edit-verif shadow-sm d-flex align-items-center"
                                                data-id="{{ $v->id }}">
                                                <i class="fas fa-edit mr-1"></i> EDIT
                                            </button>
                                            <form
                                                action="{{ route('calibration.verifications.destroy', [$v->id, 'plant' => $plantCode]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger shadow-sm d-flex align-items-center"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash mr-1"></i> HAPUS
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="20" class="text-center">Tidak ada data hasil verifikasi.</td>
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
                    <div class="modal-body">
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
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Lokasi Simpan</label>
                                            <input type="text" name="lokasi_penyimpanan" id="edit_lokasi_penyimpanan"
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
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Judgment <span
                                                    class="text-danger">*</span></label>
                                            <select name="judgment" id="edit_judgment" class="form-control form-control-sm"
                                                required>
                                                <option value="OK">OK</option>
                                                <option value="NG">NG</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Std. Toleransi</label>
                                            <input type="text" name="std_toleransi" id="edit_std_toleransi"
                                                class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Acuan Toleransi</label>
                                            <input type="text" name="acuan_toleransi" id="edit_acuan_toleransi"
                                                class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">Upload PDF Baru (Sertifikat)</label>
                                    <input type="file" name="file_pdf" class="form-control-file" accept=".pdf">
                                    <div id="edit_existing_pdf" class="mt-1"></div>
                                </div>
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
                                            <th>Nilai yang Ditunjukkan Alat</th>
                                            <th>Nilai Koreksi Alat</th>
                                            <th>Nilai Ketidakpastian</th>
                                            <th>Hasil Verifikasi</th>
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
                    <div class="modal-body">
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
                                                data-lokasi="{{ $tool->lokasi_pakai }}"
                                                data-schedules="{{ json_encode($tool->schedules->pluck('schedule_date')->map(fn($d) => $d->format('Y-m-d'))) }}">
                                                {{ $tool->name_alat }} ({{ $tool->serial_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Nama Alat</label>
                                    <input type="text" name="name_alat" id="modal_name_alat"
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
                                            <input type="text" name="serial_number" id="modal_serial_number"
                                                class="form-control form-control-sm bg-light" readonly required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Resolusi</label>
                                            <input type="text" name="resolusi" id="modal_resolusi"
                                                class="form-control form-control-sm bg-light" readonly required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Rentang Ukur (Range)</label>
                                    <input type="text" name="rentang_ukur" id="modal_rentang_ukur"
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
                                        class="form-control form-control-sm bg-light" readonly required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Lokasi Penyimpanan</label>
                                    <input type="text" name="lokasi_penyimpanan" id="modal_lokasi_penyimpanan"
                                        class="form-control form-control-sm bg-light" readonly required>
                                </div>
                            </div>
                        </div>

                        {{-- Table Detail Verifikasi --}}
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
                                                <th>Nilai Alat</th>
                                                <th>Nilai Koreksi</th>
                                                <th>Ketidakpastian</th>
                                                <th>Hasil Verifikasi</th>
                                                <th style="width: 40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-verification-body">
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
                                                        class="btn btn-sm btn-outline-danger modal-remove-row" disabled>
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

            // --- Modal Verifikasi Baru Logic ---
            $('#modal_tool_select').on('change', function () {
                var selected = $(this).find('option:selected');
                if (selected.val()) {
                    $('#modal_name_alat').val(selected.data('name'));
                    $('#modal_serial_number').val(selected.data('serial'));
                    $('#modal_rentang_ukur').val(selected.data('range'));
                    $('#modal_resolusi').val(selected.data('resolusi'));
                    $('#modal_frekuensi_kalibrasi').val(selected.data('frekuensi'));
                    $('#modal_lokasi_penyimpanan').val(selected.data('lokasi'));

                    modalUpdateNextCalibrationDate();
                }
            });

            $('#modal_tanggal_verifikasi').on('change', function () {
                modalUpdateNextCalibrationDate();
            });

            function modalUpdateNextCalibrationDate() {
                var selected = $('#modal_tool_select').find('option:selected');
                var verifDate = $('#modal_tanggal_verifikasi').val();

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
                        $('#modal_next_kalibrasi').val(nextDate);
                    }
                }
            }

            // Modal Add Row
            $('#modal-add-row').on('click', function () {
                var newRow = `
                                        <tr>
                                            <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm" required></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger modal-remove-row">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>`;
                $('#modal-verification-body').append(newRow);
                modalUpdateRemoveButtons();
            });

            // Modal Remove Row
            $(document).on('click', '.modal-remove-row', function () {
                $(this).closest('tr').remove();
                modalUpdateRemoveButtons();
            });

            function modalUpdateRemoveButtons() {
                var rowCount = $('#modal-verification-body tr').length;
                if (rowCount <= 1) {
                    $('.modal-remove-row').prop('disabled', true);
                } else {
                    $('.modal-remove-row').prop('disabled', false);
                }
            }

            // --- Edit Logic ---
            $('.btn-edit-verif').on('click', function () {
                var id = $(this).data('id');
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: `/calibration/verifications/${id}/edit`,
                    type: 'GET',
                    data: { plant: "{{ $plantCode }}" },
                    success: function (response) {
                        var v = response.verification;
                        $('#edit_name_alat').val(v.name_alat);
                        $('#edit_merk').val(v.merk);
                        $('#edit_serial_number').val(v.serial_number);
                        $('#edit_resolusi').val(v.resolusi);
                        $('#edit_rentang_ukur').val(v.rentang_ukur);
                        $('#edit_frekuensi_kalibrasi').val(v.frekuensi_kalibrasi);
                        $('#edit_lokasi_penyimpanan').val(v.lokasi_penyimpanan);
                        $('#edit_tanggal_kalibrasi').val(v.tanggal_kalibrasi ? v.tanggal_kalibrasi.substring(0, 10) : '');
                        $('#edit_tanggal_verifikasi').val(v.tanggal_verifikasi ? v.tanggal_verifikasi.substring(0, 10) : '');
                        $('#edit_next_kalibrasi').val(v.next_kalibrasi ? v.next_kalibrasi.substring(0, 10) : '');
                        $('#edit_judgment').val(v.judgment);
                        $('#edit_std_toleransi').val(v.std_toleransi);
                        $('#edit_acuan_toleransi').val(v.acuan_toleransi);

                        if (v.file_pdf) {
                            $('#edit_existing_pdf').html(`<a href="/storage/${v.file_pdf}" target="_blank" class="badge badge-info"><i class="fas fa-file-pdf mr-1"></i> Lihat Sertifikat</a>`);
                        } else {
                            $('#edit_existing_pdf').html('');
                        }

                        // Fill Measurements
                        var rowsHtml = '';
                        var nilaiAlat = Array.isArray(v.nilai_alat) ? v.nilai_alat : [v.nilai_alat];
                        var nilaiKoreksi = Array.isArray(v.nilai_koreksi) ? v.nilai_koreksi : [v.nilai_koreksi];
                        var nilaiKetidakpastian = Array.isArray(v.nilai_ketidakpastian) ? v.nilai_ketidakpastian : [v.nilai_ketidakpastian];
                        var hasilVerifikasi = Array.isArray(v.hasil_verifikasi) ? v.hasil_verifikasi : [v.hasil_verifikasi];

                        nilaiAlat.forEach(function (val, i) {
                            rowsHtml += `
                                            <tr>
                                                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm" value="${val || ''}" required></td>
                                                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm" value="${nilaiKoreksi[i] || ''}" required></td>
                                                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm" value="${nilaiKetidakpastian[i] || ''}" required></td>
                                                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm" value="${hasilVerifikasi[i] || ''}" required></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger edit-modal-remove-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>`;
                        });
                        $('#edit-modal-verification-body').html(rowsHtml);
                        editModalUpdateRemoveButtons();

                        // Update form action
                        var url = "{{ route('calibration.verifications.update', ':id') }}";
                        url = url.replace(':id', id);
                        $('#formEditVerif').attr('action', url);

                        $('#modalEditVerifikasi').modal('show');
                        btn.prop('disabled', false).html('<i class="fas fa-edit mr-1"></i> EDIT');
                    },
                    error: function () {
                        alert('Gagal mengambil data verifikasi.');
                        btn.prop('disabled', false).html('<i class="fas fa-edit mr-1"></i> EDIT');
                    }
                });
            });

            $('#edit-modal-add-row').on('click', function () {
                var newRow = `
                                    <tr>
                                        <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm" required></td>
                                        <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm" required></td>
                                        <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm" required></td>
                                        <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm" required></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger edit-modal-remove-row">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>`;
                $('#edit-modal-verification-body').append(newRow);
                editModalUpdateRemoveButtons();
            });

            $(document).on('click', '.edit-modal-remove-row', function () {
                $(this).closest('tr').remove();
                editModalUpdateRemoveButtons();
            });

            function editModalUpdateRemoveButtons() {
                var rowCount = $('#edit-modal-verification-body tr').length;
                if (rowCount <= 1) {
                    $('.edit-modal-remove-row').prop('disabled', true);
                } else {
                    $('.edit-modal-remove-row').prop('disabled', false);
                }
            }
        });
    </script>
@endpush