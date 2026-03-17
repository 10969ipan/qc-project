@extends('layouts.admin')

@section('title', 'Record Claim')

@section('content')
    <x-plant-header title="List Claim Customer" :plant="request()->get('plant')" />

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Claim Customer</h6>
            @if (!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                <div class="d-flex">
                    <a href="{{ route('admin.customer-claim-records.export', request()->only(['plant', 'start_date', 'end_date', 'q'])) }}"
                        class="btn btn-danger btn-sm shadow-sm mr-2 btn-download">
                        <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                        data-target="#modalTambahRecord">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Claim
                    </button>
                </div>
            @else
                <a href="{{ route('admin.customer-claim-records.export', request()->only(['plant', 'start_date', 'end_date', 'q'])) }}"
                    class="btn btn-danger btn-sm shadow-sm btn-download">
                    <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Export PDF
                </a>
            @endif
        </div>
        <div class="card-body py-3">
            <form action="{{ route('admin.customer-claim-records.index') }}" method="GET" class="mb-4">
                @if(request('plant'))
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                @endif
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center mr-3">
                                <label class="small font-weight-bold mb-0 mr-2 text-nowrap">Tgl Awal:</label>
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="{{ request('start_date') }}" style="width: 140px;">
                            </div>
                            <div class="d-flex align-items-center mr-3">
                                <label class="small font-weight-bold mb-0 mr-2 text-nowrap">Tgl Akhir:</label>
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="{{ request('end_date') }}" style="width: 140px;">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm mr-2">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.customer-claim-records.index', ['plant' => request('plant')]) }}"
                                class="btn btn-outline-secondary btn-sm shadow-sm" title="Reset">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-end">
                            <label class="small font-weight-bold mb-0 mr-2">Pencarian:</label>
                            <input type="text" name="q" class="form-control form-control-sm uppercase-input"
                                value="{{ request('q') }}" placeholder="Cari..." style="max-width: 200px;">
                        </div>
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

@if(isset($errors) && (is_object($errors) ? $errors->any() : (is_array($errors) && count($errors) > 0)))
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

            <style>
                #claimTableWrapper {
                    max-height: 75vh;
                    overflow: auto;
                    border: 1px solid #dee2e6;
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
                    font-size: 0.75rem;
                    padding: 10px 6px !important;
                    vertical-align: middle !important;
                    text-align: center;
                    border: 1px solid rgba(255,255,255,0.3) !important;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
                    white-space: nowrap;
                }
            </style>

            <div class="table-responsive" id="claimTableWrapper">
                <table class="table table-bordered table-hover table-sm text-xs" id="dataTable" width="100%" cellspacing="0"
                    style="font-size: 0.75rem;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Claim</th>
                            <th>Customer</th>
                            <th>Plant / UP (Customer)</th>
                            <th>Officially / Non Officially / Suspect</th>
                            <th>No. Dokumen (Report)</th>
                            <th>Project (NM/MP)</th>
                            <th class="col-part">Nama Part</th>
                            <th class="col-problem">Problem</th>
                            <th>Qty (pcs)</th>
                            <th>Kategori Problem</th>
                            <th>Kategori Penyimpangan (4M/IPQ/OTHER)</th>
                            <th>Initial Operator</th>
                            <th>Initial Inspektor</th>
                            <th style="min-width: 150px;">Temporary Action</th>
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
                                <td class="align-middle font-weight-bold">{{ $record->customer }}</td>
                                <td class="align-middle">{{ Str::title($record->plant_up_customer) }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-{{ $record->claim_type == 'OFFICIAL' ? 'danger' : 'warning' }}">
                                        {{ $record->claim_type }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $record->no_report }}</td>
                                <td class="text-center align-middle">{{ $record->project }}</td>
                                <td class="align-middle">{{ $record->nama_part }}</td>
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
                                    @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
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
        .col-part { width: 30% !important; }
        .col-problem { width: 10% !important; }
        .text-xs { font-size: 0.75rem; }
        .btn-xs { padding: 0.1rem 0.3rem; font-size: 0.7rem; }
    </style>
@endpush

@push('scripts')
    <script>
        window.__CUSTOMER_CLAIM_RECORDS__ = {
            baseUrl: "{{ url('admin/customer-claim-records') }}",
            deleteAttachmentUrl: "{{ route('admin.customer-claim-records.attachment.destroy', [':id', ':index']) }}",
            plant: "{{ request('plant') }}"
        };
    </script>
    <script src="{{ asset('js/customer-claim-records/customer-claim-records.js') }}"></script>
@endpush
