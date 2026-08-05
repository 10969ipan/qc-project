@extends('layouts.admin')

@section('title', 'QR Plating - Cabut')

@section('content')
<div class="container-fluid">
    <style>
        .custom-plating-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: none !important;
            width: 100% !important;
        }
        
        .custom-plating-table th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.65rem !important;
            letter-spacing: 0.2px;
            padding: 8px 10px !important;
            border-right: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            vertical-align: middle !important;
            line-height: 1.2;
        }
        
        .custom-plating-table td {
            border-right: 1px solid #f1f5f9 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle !important;
            color: #334155 !important;
            padding: 6px 8px !important;
        }
        
        .custom-plating-table tr:hover td {
            background-color: #f1f5f9 !important;
        }
    </style>
    <div class="row">
        <div class="col-md-12 col-lg-5" id="formColumn" style="transition: all 0.3s ease;">
            <div class="card shadow-sm border" style="border-color: #e2e8f0; border-radius: 8px;">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3" style="border-bottom: 1px solid #f1f5f9;">
                    <h6 class="mb-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        Plating - Cabut ke Bucket
                    </h6>
                    <a href="{{ route('plating_scan.history') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="font-size: 0.72rem; padding: 0.25rem 0.75rem;">
                        Riwayat Scan
                    </a>
                </div>
                <div class="card-body p-4 bg-white">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 px-3 mb-4" style="border-radius: 6px; font-size: 0.8rem;">
                            <ul class="mb-0 pl-3 font-weight-bold">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('plating_scan.cabut.store') }}" method="POST" id="cabutForm">
                        @csrf

                        <!-- Scan Section -->
                        <div class="form-group mb-4">
                            <label for="pasang_qrcode" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                Data QR Pasang <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" name="pasang_qrcode" id="pasang_qrcode" class="form-control text-monospace" 
                                       placeholder="Pindai QR Pasang di sini..." required value="{{ old('pasang_qrcode') }}" autocomplete="off"
                                       style="font-size: 0.85rem; border-color: #cbd5e1; border-radius: 6px 0 0 6px;">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary font-weight-bold px-3" id="btnScanPasang" style="border-radius: 0 6px 6px 0; font-size: 0.8rem; border-color: #cbd5e1; border-left: none;">
                                        <i class="fas fa-camera mr-1"></i> Scan
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted mt-1" style="font-size: 0.7rem;">Penting: Scan qr internal</small>
                        </div>

                        <!-- Data Fields Section -->
                        <div class="border-top pt-3 mt-4 mb-3">
                            <h6 class="font-weight-bold text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                Metadata & Input Cabut
                            </h6>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="tanggal_cabut" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tanggal Cabut</label>
                                    <input type="date" name="tanggal_cabut" id="tanggal_cabut" class="form-control" 
                                           value="{{ old('tanggal_cabut', $defaultDate) }}" required
                                           style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="shift" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Shift Cabut</label>
                                    <select name="shift" id="shift" class="form-control" required
                                            style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                        <option value="1" {{ old('shift', $defaultShift) == '1' ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ old('shift', $defaultShift) == '2' ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ old('shift', $defaultShift) == '3' ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="inisial_cabut" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Operator Cabut</label>
                                    <input type="text" name="inisial_cabut" id="inisial_cabut" class="form-control text-uppercase" 
                                           value="{{ old('inisial_cabut') }}" required placeholder="Contoh: IP / IO / KL" maxlength="50"
                                           style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                </div>
                            </div>
                        </div>

                        <!-- Autofilled original data -->
                        <div class="p-3 my-3 border rounded" style="background-color: #f8fafc; border-color: #e2e8f0;">
                            <div class="font-weight-bold text-dark mb-2 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Data Asal (Terisi Otomatis)</div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.3px;">No PO</label>
                                        <input type="text" name="no_po" id="no_po" class="form-control bg-white font-weight-bold text-dark" readonly required placeholder="Menunggu scan..."
                                               style="font-size: 0.8rem; border-color: #cbd5e1; border-radius: 6px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.3px;">Lot Pasang</label>
                                        <input type="text" name="no_lot_original" id="no_lot_original" class="form-control bg-white font-weight-bold text-dark text-monospace" readonly required placeholder="Menunggu scan..."
                                               style="font-size: 0.8rem; border-color: #cbd5e1; border-radius: 6px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.3px;">Qty Original</label>
                                        <input type="number" name="qty_original" id="qty_original" class="form-control bg-white font-weight-bold text-dark" readonly required placeholder="0"
                                               style="font-size: 0.8rem; border-color: #cbd5e1; border-radius: 6px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Split Bucket Section -->
                        <div class="border-top pt-3 mt-4 mb-3 d-flex align-items-center justify-content-between">
                            <h6 class="font-weight-bold text-muted text-uppercase mb-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                Split Bucket
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-dark font-weight-bold px-3" id="btnAddSplit" style="font-size: 0.72rem; border-radius: 6px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Bucket
                            </button>
                        </div>

                        <div class="table-responsive border rounded mb-3" style="border-color: #e2e8f0;">
                            <table class="table mb-0 custom-plating-table" id="splitTable">
                                <thead>
                                    <tr>
                                        <th style="width: 8%; text-align: center;">No</th>
                                        <th style="width: 62%;">No Lot Bucket</th>
                                        <th style="width: 20%;">Qty</th>
                                        <th style="width: 10%; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="splitContainer">
                                    <!-- Dynamic rows injected here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3 border rounded d-flex justify-content-between align-items-center mb-4" style="background-color: #f8fafc; border-color: #e2e8f0;">
                            <span class="font-weight-bold text-dark" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.3px;">Status Quantity Bucket:</span>
                            <span class="badge font-weight-bold py-2 px-3" id="qtyStatusBadge" style="font-size: 0.82rem; border-radius: 6px;">
                                Total: <span id="currentTotalSplit">0</span> / <span id="limitTotalSplit">0</span>
                            </span>
                        </div>

                        <div class="text-right mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-outline-secondary font-weight-bold" id="btnSubmit" disabled style="font-size: 0.68rem; padding: 0.35rem 0.85rem; border-radius: 6px; letter-spacing: 0.5px;">
                                <i class="fas fa-qrcode mr-1.5"></i> GENERATE QR
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Print Preview Container -->
        <div class="col-md-12 col-lg-7 mt-4 mt-lg-0" id="livePreviewContainer" style="display: none; transition: all 0.3s ease;">
            <div class="card shadow border-0 rounded-lg h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        Pratinjau Label Cetak Cabut
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary font-weight-bold shadow-sm" id="btnLivePrintAction">
                        <i class="fas fa-print mr-1"></i> Cetak Label Ini
                    </button>
                </div>
                <div class="card-body p-0 bg-light" id="livePreviewBody">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pemindai QR -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="qrScannerModalLabel"><i class="fas fa-qrcode mr-2 text-success"></i>Kamera Scan Plating-Pasang</h5>
                <button type="button" class="close text-danger font-weight-bold" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 text-center bg-dark">
                <div class="position-relative">
                    <div id="qr-reader" style="width: 100%; min-height: 300px; border-radius: 0 0 8px 8px; overflow: hidden;"></div>
                    <button type="button" id="toggleFlashBtn" class="btn btn-sm btn-dark position-absolute d-none" style="top: 10px; left: 10px; opacity: 0.7; z-index: 10;">
                        <i class="fas fa-bolt text-white"></i> Flash
                    </button>
                    <button type="button" id="toggleMirrorBtn" class="btn btn-sm btn-dark position-absolute" style="top: 10px; right: 10px; opacity: 0.7; z-index: 10;">
                        <i class="fas fa-arrows-alt-h text-white"></i> Flip
                    </button>
                </div>
                <style>
                    #qr-reader video.mirrored { transform: scaleX(-1) !important; }
                    #qr-reader video { border-radius: 0 0 8px 8px; object-fit: cover; }
                </style>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/html5-qrcode.min.js') }}"></script>
<script>
    function printQrLabel(url) {
        const iframe = document.querySelector('#livePreviewBody iframe');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } else {
            var printWindow = window.open(url, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    }

    function showPrintPreview(url) {
        $('#livePreviewContainer').show();
        const previewUrl = url + (url.indexOf('?') > -1 ? '&' : '?') + 'preview=true';
        
        const iframeHtml = `
            <iframe src="${previewUrl}" style="width: 100%; border: none; min-height: 100px;" title="Print Preview" onload="this.style.height = this.contentDocument.body.scrollHeight + 'px'"></iframe>
        `;
        $('#livePreviewBody').html(iframeHtml);
        $('#btnLivePrintAction').attr('onclick', `printQrLabel('${url}')`).show();
        
        $('html, body').animate({
            scrollTop: $("#livePreviewContainer").offset().top - 20
        }, 500);
    }

$(document).ready(function() {
    let html5QrCode = null;
    
    let originalLotId = "";
    let originalUniqueCode = "";
    let originalLotPasang = "";
    let originalLotPasangFull = "";
    let splitCount = 0;

    // Fetch Plating Pasang record metadata via AJAX
    function fetchPasangData(qrText) {
        if (!qrText) return;
        $.ajax({
            url: "{{ route('plating_scan.pasang.data') }}",
            type: "GET",
            data: { qr: qrText },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#no_po').val(response.no_po);
                    originalLotId = response.lot_id;
                    originalUniqueCode = response.unique_code;
                    originalLotPasang = response.tanggal_pasang_formatted + response.inisial_pasang_combined + response.shift;
                    originalLotPasangFull = originalLotPasang + '|' + response.qty + '|' + response.jig;
                    
                    $('#no_lot_original').val(originalLotPasangFull);
                    $('#qty_original').val(response.qty);
                    $('#limitTotalSplit').text(response.qty);

                    // Reset splits and add the first default row
                    $('#splitContainer').empty();
                    splitCount = 0;
                    addSplitRow(response.qty);
                    recalculateSplits();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Tidak Ditemukan',
                        text: response.message
                    });
                    resetFormMetadata();
                }
            },
            error: function(xhr) {
                console.error(xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Gagal mengambil data dari database.'
                });
                resetFormMetadata();
            }
        });
    }

    function resetFormMetadata() {
        $('#no_po, #no_lot_original, #qty_original').val('');
        $('#limitTotalSplit').text('0');
        $('#splitContainer').empty();
        originalLotId = "";
        originalUniqueCode = "";
        originalLotPasang = "";
        originalLotPasangFull = "";
        splitCount = 0;
        recalculateSplits();
    }

    // Add split lot row
    function addSplitRow(defaultQty = '') {
        splitCount++;
        const defaultLotSplit = originalLotPasangFull ? originalLotPasangFull : '';

        const rowHtml = `
            <tr id="row_${splitCount}">
                <td class="align-middle font-weight-bold text-secondary text-center" style="font-size: 0.72rem;">${splitCount}</td>
                <td>
                    <input type="text" name="splits[${splitCount}][no_lot_split]" class="form-control form-control-sm text-monospace font-weight-bold split-lot-input" 
                           value="${defaultLotSplit}" required placeholder="Contoh: ${originalLotPasangFull || 'LOT'}"
                           style="font-size: 0.78rem; border-color: #cbd5e1; border-radius: 4px;">
                </td>
                <td>
                    <input type="number" name="splits[${splitCount}][qty_split]" class="form-control form-control-sm font-weight-bold split-qty-input" 
                           value="${defaultQty}" required min="1" placeholder="Qty"
                           style="font-size: 0.78rem; border-color: #cbd5e1; border-radius: 4px;">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" data-id="${splitCount}" style="padding: 0.15rem 0.4rem; font-size: 0.72rem; border-radius: 4px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#splitContainer').append(rowHtml);
    }

    // Recalculate quantities & enable/disable submit
    function recalculateSplits() {
        let totalOriginal = parseInt($('#qty_original').val()) || 0;
        let totalSplit = 0;

        $('.split-qty-input').each(function() {
            totalSplit += parseInt($(this).val()) || 0;
        });

        $('#currentTotalSplit').text(totalSplit);

        const badge = $('#qtyStatusBadge');
        if (totalOriginal > 0 && totalSplit === totalOriginal) {
            badge.removeClass('badge-danger badge-warning').addClass('badge-success');
            $('#btnSubmit').prop('disabled', false);
        } else if (totalOriginal > 0 && totalSplit < totalOriginal) {
            badge.removeClass('badge-success badge-danger').addClass('badge-warning');
            $('#btnSubmit').prop('disabled', false); // Allow submitting under-split, but not exceeding
        } else {
            badge.removeClass('badge-success badge-warning').addClass('badge-danger');
            $('#btnSubmit').prop('disabled', true);
        }

        // Re-number labels
        $('#splitContainer tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Handlers
    $('#pasang_qrcode').on('change input', function() {
        fetchPasangData($(this).val());
    });

    $('#btnAddSplit').on('click', function() {
        if (!originalLotId) {
            Swal.fire({
                icon: 'warning',
                title: 'Scan QR Pasang Terlebih Dahulu',
                text: 'Harap selesaikan Langkah 1 sebelum menambahkan bucket.'
            });
            return;
        }
        addSplitRow();
        recalculateSplits();
    });

    $(document).on('click', '.btn-delete-row', function() {
        const id = $(this).data('id');
        $(`#row_${id}`).remove();
        recalculateSplits();
    });

    $(document).on('input change', '.split-qty-input', function() {
        recalculateSplits();
    });

    // Audio Feedback helper
    let audioContext = null;
    function playBeep() {
        try {
            if (navigator.vibrate) navigator.vibrate(100);
            const audio = new Audio("{{ asset('audio/QR CODE BERHASIL DI SCAN.mp3') }}");
            const promise = audio.play();
            if (promise !== undefined) {
                promise.catch(() => playOscillatorBeep());
            }
        } catch (e) {
            playOscillatorBeep();
        }
    }

    function playOscillatorBeep() {
        try {
            if (!audioContext) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) audioContext = new AudioContextClass();
            }
            if (audioContext && audioContext.state === 'suspended') {
                audioContext.resume();
            }
            if (audioContext) {
                const osc = audioContext.createOscillator();
                const gain = audioContext.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(1000, audioContext.currentTime);
                gain.gain.setValueAtTime(0, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.2, audioContext.currentTime + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);
                osc.connect(gain);
                gain.connect(audioContext.destination);
                osc.start();
                osc.stop(audioContext.currentTime + 0.15);
            }
        } catch (e) {
            console.warn(e);
        }
    }

    // Camera Scan Trigger
    $('#btnScanPasang').on('click', function() {
        $('#qrScannerModal').modal('show');
    });

    $('#qrScannerModal').on('shown.bs.modal', function() {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }

        const config = { 
            fps: 30, // Higher scan rate for faster detection
            qrbox: function(width, height) {
                // Dynamically larger scan zone (75% of viewport width/height)
                var minSize = Math.min(width, height);
                var qrboxSize = Math.floor(minSize * 0.75);
                return { width: qrboxSize, height: qrboxSize };
            },
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true // Hardware accelerated scanning if OS/browser supports it
            },
            formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE, Html5QrcodeSupportedFormats.DATA_MATRIX ]
        };

        html5QrCode.start(
            { facingMode: "environment" }, 
            config,
            (decodedText, decodedResult) => {
                // Validate format (at least 5 parts separated by |)
                const parts = decodedText.split('|');
                if (parts.length < 5) {
                    window.playAppAudio('format_error');
                    html5QrCode.stop().then(() => {
                        $('#qrScannerModal').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Format QR Tidak Valid',
                            text: 'Format QR Pasang tidak valid. Minimal harus berisi 5 bagian terpisah karakter pipe (|).',
                            confirmButtonText: 'Scan Ulang'
                        }).then(() => {
                            $('#qrScannerModal').modal('show');
                        });
                    }).catch(err => {
                        console.log("Failed to stop scanner", err);
                    });
                    return;
                }

                playBeep();

                html5QrCode.stop().then(() => {
                    $('#qrScannerModal').modal('hide');
                    $('#pasang_qrcode').val(decodedText);
                    fetchPasangData(decodedText);
                }).catch(err => {
                    console.log("Failed to stop scanner", err);
                });
            },
            (errorMessage) => {
                // parse error, ignore it
            }
        ).then(() => {
            // Scanner started successfully
            $('#toggleMirrorBtn').off('click').on('click', function() {
                $('#qr-reader video').toggleClass('mirrored');
            });
            
            const track = html5QrCode.getRunningTrackCameraCapabilities();
            if (track && track.torchFeature().isSupported()) {
                $('#toggleFlashBtn').removeClass('d-none');
                $('#toggleFlashBtn').off('click').on('click', () => {
                    const videoTrack = html5QrCode.getRunningTrack();
                    if (videoTrack) {
                        const currentTorch = videoTrack.getSettings().torch;
                        html5QrCode.applyVideoConstraints({ torch: !currentTorch });
                    }
                });
            }
        }).catch(err => {
            console.error("Gagal mengakses kamera:", err);
            alert("Kamera tidak dapat diakses! Pastikan Anda telah memberikan izin kamera dan menggunakan koneksi yang aman (HTTPS/localhost).");
        });
    });

    $('#qrScannerModal').on('hidden.bs.modal', function() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(err => console.error(err));
        }
    });

    @if(session('print_cabut_id'))
    showPrintPreview("{{ route('plating_scan.cabut.qr', session('print_cabut_id')) }}");
    @endif
});
</script>
@endpush
