@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <x-plant-header title="Laporan Problem Alat" :plant="$plantCode">
        </x-plant-header>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Laporan Masalah Alat</h6>
                    <div id="filterSource" style="display: none;">
                        <div class="d-flex align-items-center" id="customFilters" style="gap: 10px;">
                            <form action="{{ route('calibration.tools.problem-logs') }}" method="GET" class="form-inline m-0">
                                <input type="hidden" name="plant" value="{{ $plantCode }}">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 5px 0 0 5px;">
                                            <i class="fas fa-calendar-alt text-primary"></i>
                                        </span>
                                    </div>
                                    <select name="year" class="form-control border-left-0" onchange="this.form.submit()" style="border-radius: 0 5px 5px 0;">
                                        @php $currentYear = date('Y'); @endphp
                                        @for($y = $currentYear + 1; $y >= 2024; $y--)
                                            <option value="{{ $y }}" {{ (isset($year) && $year == $y) ? 'selected' : '' }}>
                                                Tahun {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                            <a href="{{ route('calibration.tools.index', ['plant' => $plantCode, 'year' => $year ?? date('Y')]) }}"
                                class="btn btn-sm btn-secondary shadow-sm text-nowrap">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover small" id="problemLogsTable">
                        <thead class="bg-primary text-white text-center">
                            <tr>
                                <th width="30">NO.</th>
                                <th>BAGIAN</th>
                                <th>NAMA ALAT</th>
                                <th>NO. SERI</th>
                                <th>PROBLEM & AKSI</th>
                                <th>OK / NG</th>
                                <th>UPDATE BY</th>
                                <th width="120">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $log->tool->bagian }}</td>
                                    <td>{{ $log->tool->name_alat }}</td>
                                    <td>{{ $log->tool->serial_number }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="pr-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span
                                                        class="badge badge-{{ $log->problem_type === 'RUSAK' ? 'danger' : 'warning' }} badge-pill px-2 mr-2"
                                                        style="font-size: 0.7rem;">
                                                        {{ $log->problem_type }}
                                                    </span>
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($log->reported_date)->format('d M Y') }}
                                                    </small>
                                                </div>

                                                <div class="font-weight-bold text-dark mb-2"
                                                    style="font-size: 0.9rem; line-height: 1.3;">
                                                    {{ $log->description }}
                                                </div>

                                                <div class="small text-secondary">
                                                    <i class="fas fa-tools mr-1 text-muted"></i>
                                                    <span
                                                        class="font-weight-bold text-dark">{{ str_replace('_', ' ', $log->action_taken) }}</span>
                                                </div>
                                            </div>

                                            @if($log->evidence_report)
                                                <div class="text-right">
                                                    <a href="#"
                                                        class="small text-primary text-decoration-none text-nowrap btn-preview-evidence"
                                                        data-title="Bukti Masalah Alat"
                                                        data-src="{{ asset('storage/' . $log->evidence_report) }}">
                                                        <i class="fas fa-paperclip mr-1"></i> Bukti Laporan
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle" style="min-width: 200px;">
                                        @if($log->judgment_status)
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="pr-3 w-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span
                                                            class="badge badge-{{ $log->judgment_status === 'OK' ? 'success' : 'danger' }} badge-pill px-2 mr-2"
                                                            style="font-size: 0.7rem;">
                                                            {{ $log->judgment_status }}
                                                        </span>
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($log->judged_at)->format('d M Y') }}
                                                        </small>
                                                    </div>

                                                    @if($log->judgment_remarks)
                                                        <div class="font-weight-bold text-dark mb-2"
                                                            style="font-size: 0.9rem; line-height: 1.3;">
                                                            "{{ $log->judgment_remarks }}"
                                                        </div>
                                                    @else
                                                        <div class="text-muted mb-2 font-italic small">
                                                            - Tidak ada keterangan -
                                                        </div>
                                                    @endif

                                                    <div class="small text-secondary">
                                                        <i class="fas fa-user-check mr-1 text-muted"></i>
                                                        <span
                                                            class="font-weight-bold text-dark">{{ $log->judgedBy->name ?? 'System' }}</span>
                                                    </div>
                                                </div>

                                                @if($log->evidence_judgment)
                                                    <div class="text-right pl-2">
                                                        <a href="#"
                                                            class="small text-primary text-decoration-none text-nowrap btn-preview-evidence"
                                                            data-title="Bukti Laporan Judgment"
                                                            data-src="{{ asset('storage/' . $log->evidence_judgment) }}">
                                                            <i class="fas fa-paperclip mr-1"></i> Bukti Laporan
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            @if(in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'spv']))
                                                <button type="button" class="btn btn-sm btn-outline-primary px-3 shadow-sm btn-judgment"
                                                    data-toggle="modal" data-target="#modalJudgment" data-id="{{ $log->id }}"
                                                    data-tool="{{ $log->tool->name_alat }}" data-problem="{{ $log->problem_type }}"
                                                    style="border-radius: 20px; font-weight: 600;">
                                                    OK/NG
                                                </button>
                                            @else
                                                <span class="badge badge-light border text-muted px-3 py-1"
                                                    style="border-radius: 20px;">
                                                    <i class="fas fa-hourglass-half mr-1"></i> Menunggu
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="font-weight-bold">{{ $log->user->name }}</div>
                                        <div class="small text-muted">
                                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info shadow-sm btn-edit-log"
                                                data-id="{{ $log->id }}" data-tool="{{ $log->tool->name_alat }}"
                                                data-type="{{ $log->problem_type }}"
                                                data-date="{{ $log->reported_date->format('Y-m-d') }}"
                                                data-desc="{{ $log->description }}" data-action="{{ $log->action_taken }}"
                                                data-evidence="{{ $log->evidence_report ? asset('storage/' . $log->evidence_report) : '' }}"
                                                title="Edit Laporan">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm btn-delete-log"
                                                data-id="{{ $log->id }}" title="Hapus Laporan">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data laporan masalah.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Judgment -->
    <div class="modal fade" id="modalJudgment" tabindex="-1" role="dialog" aria-labelledby="modalJudgmentLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalJudgmentLabel">
                        Judgment
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formJudgment" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="modal-body text-left">
                        <div class="alert alert-info border-left-primary small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Anda sedang melakukan validasi <strong>OK / NG</strong> untuk alat:
                            <div class="mt-1 font-weight-bold text-primary" id="judgment_tool_name"></div>
                            <div class="mt-1 small">Jenis Problem: <span class="badge badge-warning"
                                    id="judgment_problem_type"></span></div>
                        </div>

                        <div class="form-group mb-3 text-center">
                            <label class="small font-weight-bold d-block text-left mb-3">Status Keputusan <span
                                    class="text-danger">*</span></label>
                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons" style="gap: 15px;">
                                <label class="btn btn-outline-success flex-fill border-2 shadow-sm py-2"
                                    style="border-radius: 10px; border-width: 2px;">
                                    <input type="radio" name="judgment_status" id="judgment_ok" value="OK" required
                                        autocomplete="off">
                                    <i class="fas fa-check-circle mr-1"></i> OK
                                </label>
                                <label class="btn btn-outline-danger flex-fill border-2 shadow-sm py-2"
                                    style="border-radius: 10px; border-width: 2px;">
                                    <input type="radio" name="judgment_status" id="judgment_ng" value="NG" required
                                        autocomplete="off">
                                    <i class="fas fa-times-circle mr-1"></i> NG
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Keterangan / Alasan Tambahan</label>
                            <textarea name="judgment_remarks" class="form-control" rows="3"
                                placeholder="Contoh: Alat sudah diperbaiki dan dikalibrasi ulang..."></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Bukti Judgment (Foto / PDF)</label>
                            <input type="file" name="evidence_judgment" class="form-control-file form-control-sm"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Max: 5MB (JPG/PDF)</small>
                        </div>

                        <div id="judgment_warning" class="mt-3 small p-2 rounded bg-light border" style="display: none;">
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

    <!-- Modal Edit Problem Log -->
    <div class="modal fade" id="modalEditProblem" tabindex="-1" role="dialog" aria-labelledby="modalEditProblemLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalEditProblemLabel">
                        <i class="fas fa-edit mr-2"></i>Edit Laporan Masalah
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditProblem" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="modal-body">
                        <div class="alert alert-secondary small py-2 mb-3">
                            Alat: <strong id="edit_tool_name"></strong>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold">Jenis Problem <span class="text-danger">*</span></label>
                            <select name="problem_type" id="edit_problem_type" class="form-control form-control-sm"
                                required>
                                <option value="ERROR">ERROR (Butuh Service)</option>
                                <option value="RUSAK">RUSAK (Broken - Alat tidak bisa dipakai)</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold">Tanggal Ditemukan <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="reported_date" id="edit_reported_date"
                                class="form-control form-control-sm" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold">Deskripsi Masalah <span
                                    class="text-danger">*</span></label>
                            <textarea name="description" id="edit_description" class="form-control form-control-sm" rows="3"
                                required></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Aksi Lanjut <span class="text-danger">*</span></label>
                            <input type="text" name="action_taken" id="edit_action_taken"
                                class="form-control form-control-sm" list="action_suggestions_edit" required
                                placeholder="Contoh: PO GA, Service Internal...">
                            <datalist id="action_suggestions_edit">
                                <option value="SERVICE_INTERNAL">
                                <option value="SERVICE_EXTERNAL">
                                <option value="PO_GA">
                                <option value="REPLACE">
                            </datalist>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm text-white">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<!-- Modal Preview Evidence -->
<div class="modal fade" id="modalPreviewEvidence" tabindex="-1" role="dialog"
    aria-labelledby="modalPreviewEvidenceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title small" id="modalPreviewEvidenceLabel">
                    <i class="fas fa-eye mr-2"></i> Preview Bukti
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-light text-center">
                <div id="previewContainer" class="d-flex justify-content-center align-items-center"
                    style="min-height: 400px;">
                    <div class="spinner-border text-primary" role="status" id="previewLoading">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <img id="previewImage" src="" class="img-fluid" style="max-height: 80vh; display: none;">
                    <iframe id="previewPdf" src=""
                        style="width: 100%; height: 80vh; border: none; display: none;"></iframe>
                </div>
            </div>
            <div class="modal-footer bg-dark p-2">
                <a href="#" id="downloadLink" class="btn btn-sm btn-light shadow-sm" target="_blank" download>
                    <i class="fas fa-download mr-1"></i> Download File
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        $(document).ready(function () {
            // ... existing datatable code ...

            // Preview Evidence
            $(document).on('click', '.btn-preview-evidence', function (e) {
                e.preventDefault();
                const src = $(this).data('src');
                const title = $(this).data('title');

                $('#modalPreviewEvidenceLabel').html('<i class="fas fa-eye mr-2"></i> ' + title);
                $('#downloadLink').attr('href', src);

                $('#previewLoading').show();
                $('#previewImage').hide();
                $('#previewPdf').hide();

                $('#modalPreviewEvidence').modal('show');

                // Simple extension check
                const extension = src.split('.').pop().toLowerCase();

                setTimeout(function () {
                    $('#previewLoading').hide();
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                        $('#previewImage').attr('src', src).show();
                    } else if (extension === 'pdf') {
                        $('#previewPdf').attr('src', src).show();
                    } else {
                        // Fallback/Unknown
                        window.open(src, '_blank');
                        $('#modalPreviewEvidence').modal('hide');
                    }
                }, 500); // Simulate/Wait for load
            });

            $('#problemLogsTable').DataTable({
                dom: "<'row px-2 align-items-center'<'col-sm-12 col-md-3'l><'col-sm-12 col-md-9 d-flex justify-content-end align-items-center'f<\"#customFiltersPlaceholder\">>>" +
                    "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                    "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                },
                drawCallback: function() {
                    $('.dataTables_filter').addClass('m-0');
                    $('.dataTables_filter label').addClass('m-0 d-flex align-items-center');
                },
                initComplete: function() {
                    $('#customFilters').appendTo('#customFiltersPlaceholder').css('margin-left', '10px');
                }
            });

            $(document).on('click', '.btn-judgment', function () {
                const id = $(this).data('id');
                const toolName = $(this).data('tool');
                const problemType = $(this).data('problem');

                $('#judgment_tool_name').text(toolName);
                $('#judgment_problem_type').text(problemType);
                $('#formJudgment').attr('action', `/calibration/tools/problem-log/${id}/judgment`);

                // Reset form
                $('input[name="judgment_status"]').prop('checked', false);
                $('textarea[name="judgment_remarks"]').val('');
                $('#judgment_warning').hide();
            });

            $('input[name="judgment_status"]').on('change', function () {
                const status = $(this).val();
                const warningDiv = $('#judgment_warning');

                if (status === 'OK') {
                    warningDiv.html('<i class="fas fa-check-circle text-success mr-1"></i> Alat akan diaktifkan kembali dan jadwal kalibrasi akan dipulihkan.').show();
                    warningDiv.removeClass('border-danger text-danger').addClass('border-success text-success');
                } else {
                    warningDiv.html('<i class="fas fa-exclamation-triangle text-danger mr-1"></i> Alat dan seluruh jadwalnya akan dihapus secara permanen.').show();
                    warningDiv.removeClass('border-success text-success').addClass('border-danger text-danger');
                }
            });

            // Edit Log
            $(document).on('click', '.btn-edit-log', function () {
                const id = $(this).data('id');
                const toolName = $(this).data('tool');
                const type = $(this).data('type');
                const date = $(this).data('date');
                const desc = $(this).data('desc');
                const action = $(this).data('action');
                const evidence = $(this).data('evidence');

                $('#edit_tool_name').text(toolName);
                $('#edit_problem_type').val(type);
                $('#edit_reported_date').val(date);
                $('#edit_description').val(desc);
                $('#edit_action_taken').val(action);

                if (evidence) {
                    $('#current_evidence_link a').attr('href', evidence);
                    $('#current_evidence_link').show();
                } else {
                    $('#current_evidence_link').hide();
                }

                $('#formEditProblem').attr('action', `/calibration/tools/problem-log/${id}`);
                $('#modalEditProblem').modal('show');
            });

            // Delete Log
            $(document).on('click', '.btn-delete-log', function () {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data laporan masalah ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/calibration/tools/problem-log/${id}`;

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';

                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';

                        const plantInput = document.createElement('input');
                        plantInput.type = 'hidden';
                        plantInput.name = 'plant';
                        plantInput.value = '{{ $plantCode }}';

                        const yearInput = document.createElement('input');
                        yearInput.type = 'hidden';
                        yearInput.name = 'year';
                        yearInput.value = '{{ $year }}';

                        form.appendChild(csrf);
                        form.appendChild(method);
                        form.appendChild(plantInput);
                        form.appendChild(yearInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush