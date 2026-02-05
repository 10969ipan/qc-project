@extends('layouts.admin')

@section('title', 'List Claim Customer')

@section('content')
    <x-plant-header title="List Claim Customer" :plant="request()->get('plant')" />

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Claim Customer</h6>
            @if (!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                <div class="d-flex">
                    <a href="{{ route('admin.customer-claim-records.export', request()->only(['plant', 'start_date', 'end_date', 'customer'])) }}"
                        class="btn btn-danger btn-sm shadow-sm mr-2">
                        <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                        data-target="#modalTambahRecord">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Claim
                    </button>
                </div>
            @else
                <a href="{{ route('admin.customer-claim-records.export', request()->only(['plant', 'start_date', 'end_date', 'customer'])) }}"
                    class="btn btn-danger btn-sm shadow-sm">
                    <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Export PDF
                </a>
            @endif
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('admin.customer-claim-records.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    @if(request('plant'))
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif

                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="small font-weight-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ request('start_date') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="small font-weight-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ request('end_date') }}">
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2">
                        <label class="small font-weight-bold">Customer</label>
                        <input type="text" name="customer" class="form-control form-control-sm uppercase-input"
                            value="{{ request('customer') }}" placeholder="Cari Customer...">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm mr-1">
                            <i class="fas fa-search"> Cari</i>
                        </button>
                        <a href="{{ route('admin.customer-claim-records.index', ['plant' => request('plant')]) }}"
                            class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo"> Reset</i>
                        </a>
                    </div>
                </div>
            </form>

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
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-xs" id="dataTable" width="100%" cellspacing="0"
                    style="font-size: 0.75rem;">
                    <thead class="bg-primary text-white text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Claim</th>
                            <th>Customer</th>
                            <th>Plant / UP (Customer)</th>
                            <th>Officially / Non Officially / Suspect</th>
                            <th>No. Dokumen (Report)</th>
                            <th>Project (NM/MP)</th>
                            <th>Nama Part</th>
                            <th style="min-width: 200px;">Problem</th>
                            <th>Qty (pcs)</th>
                            <th>Kategori Problem</th>
                            <th>Kategori Penyimpangan (4M/IPQ/OTHER)</th>
                            <th>Initial Operator</th>
                            <th>Initial Inspektor</th>
                            <th style="min-width: 150px;">Temporary Action
                            </th>
                            <th>Cost Akomodasi (Rp)</th>
                            <th>Cost Overtime (Rp)</th>
                            <th style="min-width: 150px;">Feedback</th>
                            <th>Status Feedback</th>
                            <th>Status (C/M)</th>
                            <th>Dokumen Evidential</th>
                            <th>Evaluasi Problem</th>
                            <th>Monitoring Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="text-center align-middle">{{ $records->firstItem() + $loop->index }}</td>
                                <td class="text-nowrap align-middle text-center">
                                    {{ $record->tanggal_claim ? $record->tanggal_claim->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle text-uppercase font-weight-bold">{{ $record->customer }}</td>
                                <td class="align-middle">{{ Str::title($record->plant_up_customer) }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-{{ $record->claim_type == 'OFFICIAL' ? 'danger' : 'warning' }}">
                                        {{ $record->claim_type }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $record->no_report }}</td>
                                <td class="text-center align-middle">{{ $record->project }}</td>
                                <td class="align-middle text-uppercase">{{ $record->nama_part }}</td>
                                <td class="align-middle small">{{ Str::title($record->problem) }}</td>
                                <td class="text-center align-middle font-weight-bold text-primary">{{ $record->qty }}</td>
                                <td class="align-middle text-center">{{ Str::title($record->kategori_defect) }}</td>
                                <td class="align-middle text-center text-uppercase small">{{ $record->kategori_penyimpangan }}
                                </td>
                                <td class="text-center align-middle">{{ $record->initial_operator }}</td>
                                <td class="text-center align-middle">{{ $record->initial_inspektor }}</td>
                                <td class="align-middle small text-center">{{ Str::title($record->action_taken) }}</td>
                                <td class="text-right align-middle">{{ number_format($record->total_akomodasi, 0, ',', '.') }}
                                </td>
                                <td class="text-right align-middle">{{ number_format($record->total_overtime, 0, ',', '.') }}
                                </td>
                                <td class="align-middle small">{{ Str::title($record->feedback) }}</td>
                                <td class="text-center align-middle">{{ $record->status_feedback }}</td>
                                <td class="text-center align-middle">{{ Str::title($record->status_cm) }}</td>
                                <td class="text-left align-middle">
                                    @if($record->attachments && is_array($record->attachments))
                                        @foreach($record->attachments as $index => $path)
                                            @php 
                                                $fullFilename = basename($path);
                                                $displayFilename = preg_replace('/^\d+_/', '', $fullFilename);
                                            @endphp
                                            <div class="d-flex align-items-center mb-1 bg-light p-1 rounded border shadow-sm"
                                                style="font-size: 0.7rem;">
                                                <a href="javascript:void(0)" class="text-dark text-truncate mr-auto btn-preview-file"
                                                    style="max-width: 100px; text-decoration: none;"
                                                    data-url="{{ asset('storage/' . $path) }}" data-name="{{ $displayFilename }}"
                                                    title="Preview: {{ $displayFilename }}">
                                                    {{ $displayFilename }}
                                                </a>
                                                <a href="{{ asset('storage/' . $path) }}" download class="text-primary ml-1"
                                                    title="Download">
                                                    <i class="fas fa-download" style="font-size: 0.65rem;"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle small">{{ $record->evaluasi }}</td>
                                <td class="text-center align-middle">
                                    <span
                                        class="badge badge-{{ strtolower($record->monitoring_status) == 'open' ? 'primary' : 'success' }}">
                                        {{ $record->monitoring_status }}
                                    </span>
                                </td>
                                <td class="text-center align-middle text-nowrap">
                                    @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                                        <button type="button" class="btn btn-warning btn-xs py-0 btn-edit-record"
                                            data-toggle="modal" data-target="#modalEditRecord" data-id="{{ $record->id }}"
                                            data-json="{{ json_encode($record) }}">
                                            <i class="fas fa-edit fa-xs"></i>
                                        </button>
                                        <form action="{{ route('admin.customer-claim-records.destroy', $record->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-xs py-0 btn-delete-record">
                                                <i class="fas fa-trash fa-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="26" class="text-center py-4 text-muted">Belum ada data claim</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $records->links() }}
            </div>
        </div>
    </div>

    {{-- Modals for Add/Edit --}}
    @include('customer_claim_records.modals')

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0">
                <div class="modal-header bg-info text-white shadow-sm">
                    <h5 class="modal-title font-weight-bold" id="previewModalLabel">Preview File</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark" id="file_preview_body"
                    style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <!-- Content populated by JS -->
                </div>
                <div class="modal-footer bg-light">
                    <a href="#" id="btn-download-full" download class="btn btn-primary btn-sm mr-auto px-4 shadow-sm">
                        <i class="fas fa-download mr-1"></i> Download File
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .text-xs {
            font-size: 0.75rem;
        }

        .btn-xs {
            padding: 0.1rem 0.3rem;
            font-size: 0.7rem;
        }

        .table-responsive {
            max-height: 700px;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            cursor: default;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // File Preview Logic
            $(document).on('click', '.btn-preview-file', function () {
                const url = $(this).data('url');
                const filename = $(this).data('name');
                const extension = filename.split('.').pop().toLowerCase();
                const previewBody = $('#file_preview_body');

                $('#previewModalLabel').text('Preview: ' + filename);
                $('#btn-download-full').attr('href', url);
                previewBody.html('<div class="text-center p-5 text-white"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');

                if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                    previewBody.html(`<img src="${url}" class="img-fluid d-block mx-auto shadow">`);
                } else if (extension === 'pdf') {
                    previewBody.html(`<iframe src="${url}" width="100%" height="700px" style="border: none;"></iframe>`);
                } else {
                    previewBody.html(`
                            <div class="text-center p-5 text-white">
                                <i class="fas fa-file-alt fa-4x mb-3 text-light"></i>
                                <h5>Preview tidak tersedia</h5>
                                <p>Format file <strong>.${extension}</strong> tidak dapat dipratinjau secara langsung.</p>
                                <a href="${url}" download class="btn btn-primary btn-sm mt-2 px-4">
                                    <i class="fas fa-download mr-1"></i> Download untuk melihat
                                </a>
                            </div>
                        `);
                }
                $('#previewModal').modal('show');
            });


            $('.btn-edit-record').click(function () {
                const data = $(this).data('json');
                const id = $(this).data('id');
                const form = $('#formEditRecord');
                let action = "{{ route('admin.customer-claim-records.update.post', ':id') }}";
                form.attr('action', action.replace(':id', id));

                // Fill form fields
                form.find('[name="tanggal_claim"]').val(data.tanggal_claim ? data.tanggal_claim.split('T')[0] : '');

                // Customer handling
                const customerVal = data.customer || '';
                const selectElement = $('#edit_customer_select');
                const manualElement = $('#edit_customer_manual');
                if (selectElement.find('option[value="' + customerVal + '"]').length > 0) {
                    selectElement.val(customerVal).trigger('change');
                } else {
                    selectElement.val('OTHER').trigger('change');
                    manualElement.val(customerVal);
                }
                form.find('[name="plant_up_customer"]').val(data.plant_up_customer);
                form.find('[name="claim_type"]').val(data.claim_type);
                form.find('[name="no_report"]').val(data.no_report);
                form.find('[name="source_type"]').val(data.source_type);
                form.find('[name="project"]').val(data.project);
                form.find('[name="plant_id"]').val(data.plant_id);
                form.find('[name="nama_part"]').val(data.nama_part);
                form.find('[name="problem"]').val(data.problem);
                form.find('[name="kategori_defect"]').val(data.kategori_defect);
                form.find('[name="kategori_penyimpangan"]').val(data.kategori_penyimpangan);
                form.find('[name="qty"]').val(data.qty);
                form.find('[name="initial_operator"]').val(data.initial_operator);
                form.find('[name="initial_inspektor"]').val(data.initial_inspektor);
                form.find('[name="action_taken"]').val(data.action_taken);
                form.find('[name="total_akomodasi"]').val(data.total_akomodasi);
                form.find('[name="total_overtime"]').val(data.total_overtime);
                form.find('[name="feedback"]').val(data.feedback);
                form.find('[name="status_feedback"]').val(data.status_feedback);
                form.find('[name="status_cm"]').val(data.status_cm);
                form.find('[name="monitoring"]').val(data.monitoring);
                form.find('[name="evaluasi"]').val(data.evaluasi);
                form.find('[name="monitoring_status"]').val(data.monitoring_status);

                // Attachments handling in Edit Modal
                const attachmentList = $('#edit_attachments_list');
                attachmentList.empty();

                if (data.attachments && Array.isArray(data.attachments) && data.attachments.length > 0) {
                    data.attachments.forEach((path, index) => {
                        const fileName = path.split('/').pop().replace(/^\d+_/, '');
                        attachmentList.append(`
                                    <div class="d-flex align-items-center justify-content-between p-2 mb-1 bg-white border rounded small">
                                        <span class="text-truncate mr-2" title="${fileName}">${fileName}</span>
                                        <div class="btn-group">
                                            <a href="/storage/${path}" target="_blank" class="btn btn-info btn-xs" title="View">
                                                <i class="fas fa-eye fa-xs"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-xs btn-delete-attachment" 
                                                    data-id="${id}" data-index="${index}" title="Delete">
                                                <i class="fas fa-trash fa-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                `);
                    });
                } else {
                    attachmentList.append('<div class="text-muted small italic">No files uploaded</div>');
                }
            });

            // AJAX Delete Attachment
            $(document).on('click', '.btn-delete-attachment', function (e) {
                e.preventDefault();
                const btn = $(this);
                const id = btn.data('id');
                const index = btn.data('index');

                Swal.fire({
                    title: 'Hapus file?',
                    text: "File akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('admin.customer-claim-records.attachment.destroy', [':id', ':index']) }}";
                        url = url.replace(':id', id).replace(':index', index);

                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                plant: "{{ request('plant') }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    btn.closest('.d-flex').fadeOut(300, function () {
                                        $(this).remove();
                                        if ($('#edit_attachments_list').children().length === 0) {
                                            $('#edit_attachments_list').append('<div class="text-muted small italic">No files uploaded</div>');
                                        }
                                    });
                                    Swal.fire(
                                        'Terhapus!',
                                        'File telah dihapus.',
                                        'success'
                                    );
                                }
                            },
                            error: function (xhr) {
                                var message = 'Terjadi kesalahan saat menghapus file.';
                                if (xhr.status === 404) {
                                    message = 'File atau record tidak ditemukan.';
                                } else if (xhr.status === 403) {
                                    message = 'Anda tidak memiliki akses untuk menghapus file ini.';
                                }
                                
                                Swal.fire(
                                    'Gagal!',
                                    message,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // SweetAlert Delete Record Confirmation
            $('.btn-delete-record').click(function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Hapus data claim?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
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