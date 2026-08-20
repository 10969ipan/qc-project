@extends('layouts.admin')

@section('title', 'Incoming Export')

@section('content')
<style>
    .table-responsive {
        max-height: calc(100vh - 220px) !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    
    #checksheetTable td, #checksheetTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }

    #checksheetTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.68rem !important;
        padding: 4px 6px !important;
        white-space: nowrap !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 6px 12px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #e2e8f0;
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #checksheetTable .btn {
        min-width: 0 !important;
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
    #checksheetTable .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    /* Exact sticky heights since headers no longer wrap */
    #checksheetTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 48px !important; 
    }
    #checksheetTable > thead > tr:nth-child(2) > th {
        top: 48px !important; 
        z-index: 104 !important;
        height: 38px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        height: 86px !important;
        z-index: 106 !important;
    }
</style>

    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
    @endphp

<div class="card shadow mb-4">
    <div class="card-header py-3 pt-4 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold" style="color: {{ request('view_mode') === 'verifikasi' ? '#6f42c1' : '#4e73df' }};">
            @if(request('view_mode') === 'verifikasi')
                <i class="fas fa-clipboard-check mr-1"></i> Data Hasil Verifikasi Incoming Export
            @else
                Data Masuk Incoming Export (Input Manual)
            @endif
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('incoming.exports.index') }}" method="GET" class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" style="gap: 8px; overflow-x: auto; white-space: nowrap;" id="filterFormIncoming">
            <input type="hidden" name="plant" value="{{ request('plant') }}">
            @if(request('view_mode'))
                <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
            @endif
            @if(request('entry_method'))
                <input type="hidden" name="entry_method" value="{{ request('entry_method') }}">
            @endif

            <!-- Field: Part -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                <div style="width: 200px;" class="custom-filter-wrapper">
                    <select name="item_id" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none">
                        <option value="">Ketik Material / Part No...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" data-name="{{ $item->name }}" data-part-number="{{ $item->part_number }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} {{ $item->part_number ? '- '.$item->part_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tgl:</label>
                <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0" style="width: 120px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                    <span class="px-1 text-gray-500 small">-</span>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0" style="width: 120px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                </div>
            </div>

            <!-- Field: QR Raw (Khusus Data Hasil Verifikasi) -->
            @if(request('view_mode') === 'verifikasi')
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">QR:</label>
                <div class="input-group input-group-sm shadow-sm rounded" style="width: 200px;">
                    <input type="text" name="qr_raw" id="filterQrRaw" class="form-control border-0"
                        placeholder="Scan/Ketik QR..." value="{{ request('qr_raw') }}" style="font-size: 0.75rem;">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary border-0" id="btnScanQRIndex" title="Scan QR Code" style="min-width: 40px; touch-action: manipulation;">
                            <i class="fas fa-qrcode" style="pointer-events: none;"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                <style>
                    .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                    .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.75rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); }
                    .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
                    .custom-filter-wrapper { position: relative; top: -1px; }
                </style>
                <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data"><i class="fas fa-search fa-sm"></i></button>
                <a href="{{ route('incoming.exports.index', ['plant' => request('plant')]) }}" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter"><i class="fas fa-undo fa-sm"></i></a>
                @if(request('view_mode') !== 'verifikasi')
                    <a href="{{ route('incoming.exports.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Data Hasil Verifikasi"
                        style="background-color: #6f42c1; color: white;">
                        Hasil Verifikasi
                    </a>
                @else
                    <a href="{{ route('incoming.exports.index', ['plant' => request('plant')]) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Kembali ke Data Regular"
                        style="background-color: #6c757d; color: white;">
                        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                    </a>
                @endif
                <a href="{{ route('incoming.exports.export_pdf', request()->query()) }}" class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export to PDF"><i class="fas fa-file-pdf fa-sm"></i></a>
            </div>
        </form>

            <div class="table-responsive">
                <table class="table table-hover" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">QR-Code</th>
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th rowspan="2" class="align-middle">Jam (Before)</th>
                                <th rowspan="2" class="align-middle">Jam (After)</th>
                                <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            @endif
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Tgl Delivery</th>
                            <th rowspan="2" class="align-middle">Lot Qty</th>
                            <th rowspan="2" class="align-middle">Total Check</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inspector</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="2" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            <th rowspan="2" class="no-export align-middle">Actions</th>
                        </tr>
                        <tr class="text-center">
                            <th style="width: 60px; min-width: 60px;">Pcs</th>
                            <th style="min-width: 150px;">Jenis NG</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th style="font-size: 10px;">{{ $plantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                                <th style="font-size: 10px;">Supervisor QC</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checksheets as $cs)
                            <tr class="text-center">
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle">
                                    <button type="button" class="btn btn-sm btn-primary btn-qr-detail" 
                                        data-qr="{{ $cs->qrcode }}"
                                        data-part="{{ $cs->part_code }}"
                                        data-supplier="{{ $cs->supplier_id }}"
                                        data-qty="{{ $cs->quantity }}"
                                        data-unique="{{ $cs->unique_code_id }}"
                                        data-sap="{{ $cs->sap_code ?? '-' }}">
                                        <i class="fas fa-qrcode"></i> View
                                    </button>
                                </td>
                                <td class="align-middle text-nowrap">{{ date('d-m-Y', strtotime($cs->date)) }}</td>
                                @if(request('view_mode') !== 'verifikasi')
                                    <td class="align-middle">{{ $cs->created_at->copy()->subSeconds($cs->cycle_time ?? 0)->format('H:i') }}</td>
                                    <td class="align-middle">{{ $cs->created_at->format('H:i') }}</td>
                                    <td class="align-middle">{{ $cs->cycle_time ?? '-' }}</td>
                                @endif
                                <td class="align-middle text-nowrap d-none">{{ $cs->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap text-left">{{ $cs->item->name }}<br><small class="text-muted">{{ $cs->item->part_number }}</small></td>
                                <td class="align-middle">{{ date('d-m-Y', strtotime($cs->tanggal_delivery)) }}</td>
                                <td class="align-middle">{{ $cs->lot_qty }}</td>
                                <td class="align-middle">{{ $cs->total_check }}</td>
                                <td class="align-middle text-success font-weight-bold">{{ $cs->total_check - $cs->total_ng }}</td>
                                <td class="align-middle text-danger font-weight-bold">{{ $cs->total_ng }}</td>
                                @php $defects = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true); @endphp
                                <td class="p-0 align-middle">
                                    @foreach($defects ?? [] as $d) <div class="border-bottom py-1">{{ $d['qty'] ?? 0 }}</div>
                                    @endforeach
                                </td>
                                <td class="p-0 align-middle">
                                    @foreach($defects ?? [] as $d) <div class="border-bottom py-1 text-nowrap px-1">
                                        {{ $d['type'] ?? '-' }}
                                    </div> @endforeach
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">{{ $cs->judgment }}</span>
                                </td>
                                <td class="align-middle text-uppercase">{{ $cs->operator_initials }}</td>
                                @if(request('view_mode') !== 'verifikasi')
                                    @foreach(['kashift_qc', 'supervisor_qc'] as $lvl)
                                        <td class="align-middle text-center">
                                            @if($cs->$lvl === 'REJECTED')
                                                <span class="badge badge-danger" title="Rejected"><i class="fas fa-times"></i></span>
                                            @elseif($cs->$lvl)
                                                <span class="badge badge-success" title="Approved"><i class="fas fa-check"></i></span>
                                            @else
                                                <span class="badge badge-warning" title="Pending"><i class="fas fa-clock"></i></span>
                                            @endif
                                        </td>
                                    @endforeach
                                @endif
                                <td class="align-middle small">{{ $cs->remarks }}</td>
                                <td class="align-middle text-center">
                                    @if($loop->first && request('view_mode') !== 'verifikasi')
                                        @include('partials.bulk_approve_button')
                                    @endif
                                    <div class="btn-group">
                                        @if(!in_array(auth()->user()->role, ['inspector']))
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm shadow-sm rounded mr-1 btn-edit-export"
                                                style="padding: 2px 6px; font-size: 0.65rem;"
                                                title="Edit"
                                                data-id="{{ $cs->id }}"
                                                data-url="{{ route('incoming.exports.edit', $cs->id) }}"
                                                data-update-url="{{ route('incoming.exports.update', $cs->id) }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('incoming.exports.destroy', $cs->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-outline-danger btn-sm shadow-sm rounded btn-delete" style="padding: 2px 6px; font-size: 0.65rem;" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ request('view_mode') === 'verifikasi' ? 16 : 23 }}" class="py-4 text-muted text-center">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $checksheets->withQueryString()->links() }}</div>
        </div>
    </div>
    @php $bulkApproveRoute = route('incoming.exports.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')

    <!-- QR Detail Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="fas fa-qrcode mr-2"></i> Traceability QR Code
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width: 25%">QR Raw</th>
                            <td id="modal-qr-raw" style="word-break: break-all; font-family: monospace;"></td>
                        </tr>
                        <tr>
                            <th>Part Code</th>
                            <td id="modal-qr-part"></td>
                        </tr>
                        <tr>
                            <th>Supplier ID</th>
                            <td id="modal-qr-supplier"></td>
                        </tr>
                        <tr>
                            <th>Qty</th>
                            <td id="modal-qr-qty"></td>
                        </tr>
                        <tr>
                            <th>Unique ID</th>
                            <td id="modal-qr-unique"></td>
                        </tr>
                        <tr>
                            <th>SAP Code</th>
                            <td id="modal-qr-sap"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Incoming Export --}}
    <div class="modal fade" id="editExportModal" tabindex="-1" role="dialog" aria-labelledby="editExportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); border:0;">
                <div class="modal-header" style="background:#fff; padding: 0.75rem 1.5rem; border-radius:12px 12px 0 0; border-bottom:1px solid #e2e8f0;">
                    <h5 class="modal-title font-weight-bold" id="editExportModalLabel">
                        <i class="fas fa-edit mr-2 text-primary"></i> Edit Data Incoming Export
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background:#f8fafc; padding:1.5rem; max-height:65vh; overflow-y:auto;">
                    <div id="editExportFormContainer">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
                            <p class="mt-2 text-muted">Memuat form edit...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}"></script>
<script>
    $(document).ready(function() {
        if (typeof initItemSearch === 'function') {
            initItemSearch('filterItem', { placeholder: 'Ketik Material / Part No...', maxResults: 50 });
        }

        // QR Detail — event delegation agar kompatibel dengan pagination
        $(document).on('click', '.btn-qr-detail', function() {
            $('#modal-qr-raw').text($(this).data('qr') || '-');
            $('#modal-qr-part').text($(this).data('part') || '-');
            $('#modal-qr-supplier').text($(this).data('supplier') || '-');
            $('#modal-qr-qty').text($(this).data('qty') || '-');
            $('#modal-qr-unique').text($(this).data('unique') || '-');
            $('#modal-qr-sap').text($(this).data('sap') || '-');
            $('#qrModal').modal('show');
        });

        // Edit Export — buka modal, load form via AJAX
        $(document).on('click', '.btn-edit-export', function() {
            const url = $(this).data('url');
            const updateUrl = $(this).data('update-url');
            $('#editExportFormContainer').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat form edit...</p></div>'
            );
            $('#editExportModal').modal('show');
            $.ajax({
                url: url,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(html) {
                    $('#editExportFormContainer').html(html);
                    $('#editExportFormContainer form').attr('action', updateUrl);
                    if (typeof $.fn.select2 !== 'undefined') {
                        $('#editExportFormContainer .select2').select2({ dropdownParent: $('#editExportModal') });
                    }
                },
                error: function() {
                    $('#editExportFormContainer').html('<p class="text-danger text-center py-4">Gagal memuat form. Silakan coba lagi.</p>');
                }
            });
        });

        // Submit form edit via AJAX
        $(document).on('submit', '#editChecksheetForm', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const btn = form.find('[type=submit]');
            const orig = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function() {
                    $('#editExportModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data berhasil diperbarui.', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(orig);
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan perubahan.';
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        });

        // Hapus data
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', allowOutsideClick: false });
                    Swal.showLoading();
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
