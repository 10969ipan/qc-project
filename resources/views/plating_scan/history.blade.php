@extends('layouts.admin')

@section('title', 'Riwayat Scan Plating')

@section('content')
<div class="container-fluid">
    <style>
        .table-responsive {
            border: none !important;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
        }
        
        .custom-plating-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: none !important;
            width: 100% !important;
            table-layout: auto !important;
        }
        
        .custom-plating-table th {
            background-color: #f8fafc !important;
            background-clip: padding-box !important;
            color: #475569 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.65rem !important;
            letter-spacing: 0.2px;
            padding: 6px 10px !important;
            border-left: none !important;
            border-right: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            vertical-align: middle !important;
            line-height: 1.2;
            white-space: nowrap !important;
        }
        
        .custom-plating-table td {
            border-left: none !important;
            border-right: 1px solid #f1f5f9 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            border-top: none !important;
            vertical-align: middle !important;
            color: #334155 !important;
            font-size: 0.68rem !important;
            padding: 4px 6px !important;
        }
        
        .custom-plating-table tr:hover td {
            background-color: #f1f5f9 !important;
        }
        
        .custom-plating-table .badge {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.4rem !important;
            border-radius: 4px !important;
        }
        
        .custom-plating-table .btn-sm {
            font-size: 0.6rem !important;
            padding: 0.15rem 0.3rem !important;
        }

        /* Minimalist Pagination Styling */
        .pagination {
            margin: 0 !important;
            display: flex;
            justify-content: center;
            gap: 4px;
        }
        .pagination li {
            margin: 0 !important;
        }
        .pagination li a, .pagination li span {
            border: 1px solid #e2e8f0 !important;
            color: #64748b !important;
            font-weight: 600 !important;
            font-size: 0.72rem !important;
            padding: 4px 10px !important;
            border-radius: 6px !important;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
        }
        .pagination li a:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        .pagination li.active span {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2) !important;
        }
        .pagination li.disabled span {
            background-color: #f8fafc !important;
            border-color: #f1f5f9 !important;
            color: #94a3b8 !important;
            cursor: not-allowed;
        }

        /* Scoped styles for inline preview container */
        #livePreviewBody .print-pages {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            width: 100%;
        }

        #livePreviewBody .label-container {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            justify-content: flex-start !important;
            align-content: flex-start !important;
            gap: 15px !important;
            width: 715px !important;
            height: 1045px !important;
            background-color: #ffffff !important;
            border: 1px solid #ccc !important;
            padding: 30px !important;
            box-sizing: border-box !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
            position: relative;
        }

        #livePreviewBody .thermal-label {
            background-color: #ffffff;
            width: 320px !important;
            height: 230px !important;
            padding: 8px 10px !important;
            border: 1.5px dashed #000 !important;
            border-radius: 4px;
            box-sizing: border-box;
            position: relative;
            color: #000 !important;
            font-family: 'Courier New', Courier, monospace !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }

        #livePreviewBody .label-header {
            text-align: center;
            font-weight: bold;
            font-size: 13px !important;
            border-bottom: 2px solid #000 !important;
            padding-bottom: 4px !important;
            margin-bottom: 6px !important;
            letter-spacing: 0.5px;
            color: #000 !important;
        }

        #livePreviewBody .label-content {
            display: flex !important;
            align-items: stretch !important;
            gap: 8px !important;
            flex-grow: 1 !important;
        }

        #livePreviewBody .qr-section {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100px !important;
            flex-shrink: 0 !important;
        }

        #livePreviewBody .qr-image {
            width: 90px !important;
            height: 90px !important;
            object-fit: contain;
            border: 1px solid #000 !important;
            padding: 1px !important;
        }

        #livePreviewBody .qr-text {
            font-size: 6.5px !important;
            font-weight: bold !important;
            word-break: break-all;
            text-align: center;
            margin-top: 3px !important;
            color: #000 !important;
            max-width: 90px !important;
            line-height: 1.1 !important;
        }

        #livePreviewBody .details-section {
            flex-grow: 1 !important;
            width: 0 !important;
            display: flex !important;
        }

        #livePreviewBody .details-table {
            width: 100% !important;
            height: 100% !important;
            border-collapse: collapse !important;
            border: 1.5px solid #000 !important;
        }

        #livePreviewBody .details-table td {
            font-family: 'Courier New', Courier, monospace !important;
            font-size: 8px !important;
            font-weight: bold !important;
            padding: 2px 3px !important;
            vertical-align: middle !important;
            color: #000 !important;
            border: 1.5px solid #000 !important;
        }

        #livePreviewBody .details-table td.label {
            width: 45% !important;
            text-transform: uppercase;
            white-space: nowrap;
        }

        #livePreviewBody .details-table td.value {
            width: 55% !important;
            word-break: break-word;
        }

        #livePreviewBody .label-footer {
            margin-top: 6px !important;
            border-top: 1px dashed #000 !important;
            padding-top: 4px !important;
            text-align: center;
            font-size: 7.5px !important;
            font-weight: bold !important;
            color: #000 !important;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <h6 class="mb-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        Riwayat Pemindaian & Transaksi Plating
                    </h6>
                    <ul class="nav nav-pills" id="historyTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold py-1 px-3" style="font-size: 0.72rem;" id="pasang-tab" data-toggle="tab" href="#pasang" role="tab" aria-controls="pasang" aria-selected="true">
                                Plating - Pasang (WIP)
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a class="nav-link font-weight-bold py-1 px-3" style="font-size: 0.72rem;" id="cabut-tab" data-toggle="tab" href="#cabut" role="tab" aria-controls="cabut" aria-selected="false">
                                Plating - Cabut (Split Bucket)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content" id="historyTabContent">
                        
                        <!-- TAB PASANG -->
                        <div class="tab-pane fade show active" id="pasang" role="tabpanel" aria-labelledby="pasang-tab">
                            <div class="table-responsive">
                                <table class="table table-hover custom-plating-table">
                                    <thead>
                                        <tr>
                                            <th>Tgl Pasang</th>
                                            <th>Shift</th>
                                            <th>Customer Part</th>
                                            <th>No PO</th>
                                            <th>Lot Injection/WIP</th>
                                            <th>Qty WIP</th>
                                            <th>Operator</th>
                                            <th>Status Cabut</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pasangRecords as $record)
                                            <tr>
                                                <td class="align-middle font-weight-bold">{{ $record->tanggal_pasang->format('d/m/Y') }}</td>
                                                <td class="align-middle"><span class="badge badge-secondary py-1 px-2">Shift {{ $record->shift }}</span></td>
                                                <td class="align-middle font-weight-bold">{{ $record->customer_part }}</td>
                                                <td class="align-middle text-monospace">{{ $record->no_po ?: '-' }}</td>
                                                <td class="align-middle text-monospace text-nowrap">
                                                    {{ $record->lot_id }}
                                                    <button type="button" class="btn btn-sm btn-light border ml-1 px-2 py-0 btn-show-qr" data-qr="{{ $record->wip_qrcode }}" title="Lihat Data QR">
                                                        <i class="fas fa-qrcode text-info"></i>
                                                    </button>
                                                </td>
                                                <td class="align-middle font-weight-bold">{{ $record->qty }} pcs</td>
                                                <td class="align-middle text-uppercase">{{ $record->inisial_pasang }}</td>
                                                <td class="align-middle">
                                                    @if($record->cabutRecord)
                                                        <span class="badge badge-success py-1 px-2"><i class="fas fa-check-circle mr-1"></i>Sudah Cabut</span>
                                                    @else
                                                        <span class="badge badge-warning py-1 px-2"><i class="fas fa-clock mr-1"></i>Menunggu Cabut</span>
                                                    @endif
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">Belum ada data Plating-Pasang hari ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $pasangRecords->appends(['cabut_page' => request('cabut_page')])->links() }}
                            </div>
                        </div>

                        <!-- TAB CABUT -->
                        <div class="tab-pane fade" id="cabut" role="tabpanel" aria-labelledby="cabut-tab">
                            <div class="table-responsive">
                                <table class="table table-hover custom-plating-table">
                                    <thead>
                                        <tr>
                                            <th>Tgl Cabut</th>
                                            <th>Shift</th>
                                            <th>Customer Part</th>
                                            <th>No PO</th>
                                            <th>Lot Pasang</th>
                                            <th>Qty Original</th>
                                            <th>Operator</th>
                                            <th>Jumlah Bucket</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cabutRecords as $record)
                                            <tr>
                                                <td class="align-middle font-weight-bold">{{ $record->tanggal_cabut->format('d/m/Y') }}</td>
                                                <td class="align-middle"><span class="badge badge-secondary py-1 px-2">Shift {{ $record->shift }}</span></td>
                                                <td class="align-middle font-weight-bold">{{ $record->pasangRecord->customer_part }}</td>
                                                <td class="align-middle text-monospace">{{ $record->no_po ?: '-' }}</td>
                                                <td class="align-middle text-monospace text-nowrap">
                                                    {{ $record->no_lot_original }}
                                                    <button type="button" class="btn btn-sm btn-light border ml-1 px-2 py-0 btn-show-qr" data-qr="{{ $record->pasang_qrcode }}" title="Lihat Data QR">
                                                        <i class="fas fa-qrcode text-info"></i>
                                                    </button>
                                                </td>
                                                <td class="align-middle font-weight-bold">{{ $record->qty_original }} pcs</td>
                                                <td class="align-middle text-uppercase">{{ $record->inisial_cabut ?: '-' }}</td>
                                                <td class="align-middle font-weight-bold text-info">
                                                    {{ $record->splits->count() }} Bucket
                                                    <span class="small d-block text-muted">
                                                        ({{ $record->splits->sum('qty_split') }} pcs)
                                                    </span>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">Belum ada data Plating-Cabut hari ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $cabutRecords->appends(['pasang_page' => request('pasang_page')])->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Print Preview -->
    <div class="modal fade" id="previewLabelModal" tabindex="-1" role="dialog" aria-labelledby="previewLabelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="modal-title mb-0 font-weight-bold text-dark text-uppercase" id="previewLabelModalLabel" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        <i class="fas fa-eye mr-2 text-primary"></i> Pratinjau Label Cetak
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light d-flex flex-column align-items-center" style="min-height: 150px; max-height: 700px; overflow-y: auto;">
                    <div class="print-pages" id="previewLabelBody">
                        <!-- Content loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer py-2 bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold shadow-sm" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-sm btn-primary font-weight-bold shadow-sm" id="btnModalPrintAction">
                        <i class="fas fa-print mr-1"></i> Cetak Label
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal QR Detail -->
    <div class="modal fade" id="qrDetailModal" tabindex="-1" role="dialog" aria-labelledby="qrDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white py-2">
                    <h5 class="modal-title font-weight-bold" id="qrDetailModalLabel" style="font-size: 1rem;">
                        <i class="fas fa-qrcode mr-2"></i> Data QR Terpindai
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2 font-weight-bold" style="font-size: 0.85rem;">String QR Asli:</p>
                    <div class="p-3 bg-light border rounded text-monospace text-dark font-weight-bold" id="qrDetailText" style="word-break: break-all; font-size: 0.9rem;">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function printQrLabel(url) {
        var printWindow = window.open(url, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    }

    function loadInlinePreview(url, shouldScroll = false) {
        $('#btnModalPrintAction').hide();
        $('#previewLabelModal').modal('show');

        const previewUrl = url + (url.indexOf('?') > -1 ? '&' : '?') + 'preview=true';
        
        const iframeHtml = `
            <iframe src="${previewUrl}" style="width: 100%; height: 520px; border: none; overflow: hidden;" title="Print Preview"></iframe>
        `;
        
        $('#previewLabelBody').html(iframeHtml);
        $('#btnModalPrintAction').attr('onclick', `printQrLabel('${url}')`).show();
    }

    $(document).ready(function() {

        // Handler untuk tombol detail QR
        $(document).on('click', '.btn-show-qr', function() {
            var qrString = $(this).data('qr');
            $('#qrDetailText').text(qrString);
            $('#qrDetailModal').modal('show');
        });
    });
</script>
@endpush
