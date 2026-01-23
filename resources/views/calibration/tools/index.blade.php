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
            width: 125px !important;
            min-width: 125px !important;
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
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                <a href="{{ route('calibration.tools.create', ['plant' => $plantCode]) }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Alat
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
                <h6 class="m-0 font-weight-bold text-primary">Filter Pencarian</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.tools.index') }}" method="GET" class="row align-items-end">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <!-- Input PR / Pencarian -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Input PR / Pencarian</label>
                            <input type="text" name="search" class="form-control" placeholder="No PR, Nama Alat..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Jenis Kalibrasi -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Jenis Kalibrasi</label>
                            <select name="jenis_kalibrasi" class="form-control">
                                <option value="">Semua</option>
                                <option value="Internal" {{ request('jenis_kalibrasi') === 'Internal' ? 'selected' : '' }}>
                                    Internal</option>
                                <option value="Eksternal" {{ request('jenis_kalibrasi') === 'Eksternal' ? 'selected' : '' }}>
                                    Eksternal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Planning Dari -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Planning Dari</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                    </div>

                    <!-- Sampai -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Sampai</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Status</label>
                            <select name="verification_status" class="form-control">
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
                            <button type="submit" class="btn btn-primary flex-fill" title="Cari Data">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('calibration.tools.index', ['plant' => $plantCode]) }}"
                                class="btn btn-secondary flex-fill" title="Reset Filter">
                                <i class="fas fa-undo"></i>
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
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                            <a href="{{ route('calibration.verifications.create', ['plant' => $plantCode, 'tool_id' => $tool->id]) }}"
                                                class="btn btn-sm btn-success" title="Input Verifikasi">
                                                <i class="fas fa-check-circle"></i> VERIF
                                            </a>

                                            <a href="{{ route('calibration.tools.edit', [$tool->id, 'plant' => $plantCode]) }}"
                                                class="btn btn-sm btn-info" title="Edit">
                                                <i class="fas fa-edit"></i> EDIT
                                            </a>

                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDeleteTool('{{ $tool->id }}')" title="Hapus">
                                                <i class="fas fa-trash"></i> DEL
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
    </script>
@endpush