@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <x-plant-header title="Hasil Verifikasi Alat Ukur" :plant="$plantCode">
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                <a href="{{ route('calibration.verifications.create', ['plant' => $plantCode]) }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Verifikasi Baru
                </a>
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
                <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.verifications.index') }}" method="GET" class="row align-items-end mb-4">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="col-md-3">
                        <label>Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> CARI
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode]) }}"
                            class="btn btn-secondary btn-block">
                            <i class="fas fa-sync"></i> RESET
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('calibration.verifications.pdf', array_merge(request()->all(), ['plant' => $plantCode])) }}"
                            target="_blank" class="btn btn-danger btn-block">
                            <i class="fas fa-file-pdf"></i> EXPORT PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Hasil Verifikasi</h6>
            </div>
            <div class="card-body">
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
                                            <a href="{{ route('calibration.verifications.edit', [$v->id, 'plant' => $plantCode]) }}"
                                                class="btn btn-sm btn-info shadow-sm d-flex align-items-center">
                                                <i class="fas fa-edit mr-1"></i> EDIT
                                            </a>
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
        });
    </script>
@endpush