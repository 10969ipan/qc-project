@extends('layouts.admin')

@section('title', 'List Claim Customer')

@section('content')
    <x-plant-header title="List Claim Customer" :plant="request()->get('plant')" />

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Claim Customer</h6>
            @if (!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                <div class="d-flex">
                    <a href="{{ route('admin.customer-claim-records.export', request()->all()) }}" 
                       class="btn btn-danger btn-sm shadow-sm mr-2">
                        <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                        data-target="#modalTambahRecord">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Claim
                    </button>
                </div>
            @else
                <a href="{{ route('admin.customer-claim-records.export', request()->all()) }}" 
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

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-xs" id="dataTable" width="100%" cellspacing="0"
                    style="font-size: 0.75rem;">
                    <thead class="bg-primary text-white text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Claim</th>
                            <th>Customer</th>
                            <th>Plant/UP Cust</th>
                            <th>Type</th>
                            <th>No. Report</th>
                            <th>Source</th>
                            <th>Project</th>
                            <th>Plant IPP</th>
                            <th>Nama Part</th>
                            <th style="min-width: 200px;">Problem</th>
                            <th>Defect</th>
                            <th>Penyimpangan</th>
                            <th>Qty</th>
                            <th>Operator</th>
                            <th>Inspektor</th>
                            <th>Frek</th>
                            <th>% Frek</th>
                            <th style="min-width: 150px;">Action</th>
                            <th>Total Cost</th>
                            <th style="min-width: 150px;">Feedback</th>
                            <th>Stat Feed</th>
                            <th>Stat CM</th>
                            <th>Monitoring</th>
                            <th>Evaluasi</th>
                            <th>Status Mon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="text-center align-middle">{{ $records->firstItem() + $loop->index }}</td>
                                <td class="text-nowrap align-middle text-center">
                                    {{ $record->tanggal_claim ? $record->tanggal_claim->format('d/m/Y') : '-' }}</td>
                                <td class="align-middle">{{ $record->customer }}</td>
                                <td class="align-middle">{{ $record->plant_up_customer }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-{{ $record->claim_type == 'OFFICIAL' ? 'danger' : 'warning' }}">
                                        {{ $record->claim_type }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $record->no_report }}</td>
                                <td class="text-center align-middle small">{{ $record->source_type }}</td>
                                <td class="text-center align-middle">{{ $record->project }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-secondary">{{ $record->plant->code }}</span>
                                </td>
                                <td class="align-middle">{{ $record->nama_part }}</td>
                                <td class="align-middle small">{{ $record->problem }}</td>
                                <td class="align-middle">{{ $record->kategori_defect }}</td>
                                <td class="align-middle">{{ $record->kategori_penyimpangan }}</td>
                                <td class="text-center align-middle font-weight-bold text-primary">{{ $record->qty }}</td>
                                <td class="text-center align-middle">{{ $record->initial_operator }}</td>
                                <td class="text-center align-middle">{{ $record->initial_inspektor }}</td>
                                <td class="text-center align-middle">{{ $record->frek }}</td>
                                <td class="text-center align-middle">{{ $record->persen_frek }}</td>
                                <td class="align-middle small">{{ $record->action_taken }}</td>
                                <td class="text-right align-middle">{{ number_format($record->total_cost, 0, ',', '.') }}</td>
                                <td class="align-middle small">{{ $record->feedback }}</td>
                                <td class="text-center align-middle">{{ $record->status_feedback }}</td>
                                <td class="text-center align-middle">{{ $record->status_cm }}</td>
                                <td class="align-middle">{{ $record->monitoring }}</td>
                                <td class="align-middle small">{{ $record->evaluasi }}</td>
                                <td class="text-center align-middle">
                                    <span
                                        class="badge badge-{{ $record->monitoring_status == 'OPEN' ? 'primary' : 'success' }}">
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
                                            <button type="submit" class="btn btn-danger btn-xs py-0"
                                                onclick="return confirm('Hapus data ini?')">
                                                <i class="fas fa-trash fa-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="text-center py-4 text-muted">Belum ada data claim</td>
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

        .uppercase-input {
            text-transform: uppercase;
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
            $('.uppercase-input').on('input', function () {
                this.value = this.value.toUpperCase();
            });

            $('.btn-edit-record').click(function () {
                const data = $(this).data('json');
                const id = $(this).data('id');
                const form = $('#formEditRecord');
                let action = "{{ route('admin.customer-claim-records.update', ':id') }}";
                form.attr('action', action.replace(':id', id));

                // Fill form fields
                form.find('[name="tanggal_claim"]').val(data.tanggal_claim ? data.tanggal_claim.split('T')[0] : '');
                form.find('[name="customer"]').val(data.customer);
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
                form.find('[name="frek"]').val(data.frek);
                form.find('[name="persen_frek"]').val(data.persen_frek);
                form.find('[name="action_taken"]').val(data.action_taken);
                form.find('[name="total_cost"]').val(data.total_cost);
                form.find('[name="feedback"]').val(data.feedback);
                form.find('[name="status_feedback"]').val(data.status_feedback);
                form.find('[name="status_cm"]').val(data.status_cm);
                form.find('[name="monitoring"]').val(data.monitoring);
                form.find('[name="evaluasi"]').val(data.evaluasi);
                form.find('[name="monitoring_status"]').val(data.monitoring_status);
            });
        });
    </script>
@endpush