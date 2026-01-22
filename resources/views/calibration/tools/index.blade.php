@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Master Data Alat - Plant {{ strtoupper($plantCode) }}</h1>
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                <a href="{{ route('calibration.tools.create', ['plant' => $plantCode]) }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Alat
                </a>
            @endif
        </div>

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
                <h6 class="m-0 font-weight-bold text-primary">Filter Pencarian</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.tools.index') }}" method="GET" class="row align-items-end">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Pencarian</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari Bagian, Nama Alat, No. Seri, dll..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex" style="gap: 5px;">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search fa-sm"></i> Cari
                            </button>
                            <a href="{{ route('calibration.tools.index', ['plant' => $plantCode]) }}"
                                class="btn btn-secondary flex-fill">
                                <i class="fas fa-undo fa-sm"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Alat Ukur</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center align-middle" id="dataTable" width="100%"
                    cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th class="align-middle">NO.</th>
                                <th class="align-middle">BAGIAN</th>
                                <th class="align-middle">NAMA ALAT</th>
                                <th class="align-middle">NO. SERI</th>
                                <th class="align-middle">RANGE</th>
                                <th class="align-middle">RESOLUSI</th>
                                <th class="align-middle">LOKASI PAKAI</th>
                                <th class="align-middle">TANGGAL BELI</th>
                                <th class="align-middle">FREKUENSI KALIBRASI</th>
                                <th class="align-middle">RIWAYAT KALIBRASI</th>
                                <th class="align-middle">JENIS KALIBRASI</th>
                                <th class="align-middle">SCHEDULE PLANNING</th>
                                <th class="align-middle">STATUS</th>
                                <th class="align-middle text-center" style="min-width: 320px;">AKSI</th>
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
                                        @if($tool->schedules->isNotEmpty())
                                            @foreach($tool->schedules->sortBy('schedule_date') as $s)
                                                <span class="badge badge-info">{{ $s->schedule_date->format('d/m/Y') }}</span><br>
                                            @endforeach
                                        @else
                                            <span class="badge badge-secondary">{{ $tool->schedule_planning ? $tool->schedule_planning->format('d/m/Y') : '-' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $status = $tool->status;
                                            $badgeClass = 'secondary';
                                            $statusLabel = 'Unknown';

                                            if ($status === 'overdue') {
                                                $badgeClass = 'danger';
                                                $statusLabel = 'LEWAT JADWAL';
                                            } elseif ($status === 'due_soon') {
                                                $badgeClass = 'warning';
                                                $statusLabel = 'MENDEKATI JADWAL';
                                            } elseif ($status === 'calibrated') {
                                                $badgeClass = 'success';
                                                $statusLabel = 'OK';
                                            }
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center" style="gap: 5px; white-space: nowrap;">
                                            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                                <a href="{{ route('calibration.verifications.create', ['plant' => $plantCode, 'tool_id' => $tool->id]) }}"
                                                    class="btn btn-sm btn-success" title="Input Verifikasi">
                                                    <i class="fas fa-check-circle"></i> VERIFIKASI
                                                </a>

                                                <a href="{{ route('calibration.tools.edit', [$tool->id, 'plant' => $plantCode]) }}"
                                                    class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i> EDIT
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmDeleteTool('{{ $tool->id }}')" title="Hapus">
                                                    <i class="fas fa-trash"></i> HAPUS
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
                                                <button type="button" 
                                                    class="btn btn-sm btn-primary view-pdf" 
                                                    data-toggle="modal" data-target="#pdfModal"
                                                    data-url="{{ asset('storage/' . $tool->certification_path) }}"
                                                    data-title="Sertifikat - {{ $tool->name_alat }}">
                                                    <i class="fas fa-file-pdf"></i> PDF
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
    </script>
@endpush