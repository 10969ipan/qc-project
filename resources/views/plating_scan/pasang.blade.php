@extends('layouts.admin')

@section('title', 'QR Plating - Pasang')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-lg-5" id="formColumn" style="transition: all 0.3s ease;">
            <div class="card shadow-sm border" style="border-color: #e2e8f0; border-radius: 8px;">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3" style="border-bottom: 1px solid #f1f5f9;">
                    <h6 class="mb-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        Plating - Pasang KE JIG
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

                    <form action="{{ route('plating_scan.pasang.store') }}" method="POST" id="pasangForm">
                        @csrf

                        <!-- Scan Section -->
                        <div class="form-group mb-4">
                            <label for="wip_qrcode" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                Data QR WIP <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" name="wip_qrcode" id="wip_qrcode" class="form-control text-monospace" 
                                       placeholder="Pindai QR WIP di sini..." required value="{{ old('wip_qrcode') }}" autocomplete="off"
                                       style="font-size: 0.85rem; border-color: #cbd5e1; border-radius: 6px 0 0 6px;">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary font-weight-bold px-3" id="btnScanWIP" style="border-radius: 0 6px 6px 0; font-size: 0.8rem; border-color: #cbd5e1; border-left: none;">
                                        <i class="fas fa-camera mr-1"></i> Scan
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted mt-1" style="font-size: 0.7rem;">Penting: Scan qr internal</small>
                        </div>

                        <!-- Data Fields Section -->
                        <div class="border-top pt-3 mt-4 mb-3">
                            <h6 class="font-weight-bold text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                Data Input
                            </h6>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tanggal_pasang" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tanggal Pasang</label>
                                    <input type="date" name="tanggal_pasang" id="tanggal_pasang" class="form-control" 
                                           value="{{ old('tanggal_pasang', $defaultDate) }}" required
                                           style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="shift" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Shift</label>
                                    <select name="shift" id="shift" class="form-control" required
                                            style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                        <option value="1" {{ old('shift', $defaultShift) == '1' ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ old('shift', $defaultShift) == '2' ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ old('shift', $defaultShift) == '3' ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="inisial_pasang" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Inisial Pasang (Operator)</label>
                                    <input type="text" name="inisial_pasang" id="inisial_pasang" class="form-control text-uppercase" 
                                           value="{{ old('inisial_pasang') }}" required placeholder="Contoh: IP / IO / KL" maxlength="50"
                                           style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Customer Part</label>
                                    <input type="text" id="display_customer_part" class="form-control bg-light font-weight-bold text-secondary" readonly placeholder="-"
                                           style="font-size: 0.82rem; border-color: #e2e8f0; border-radius: 6px;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="no_po" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">No PO</label>
                                    <input type="text" name="no_po" id="no_po" class="form-control" 
                                           value="{{ old('no_po') }}" required placeholder="Masukkan No PO..."
                                           style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="no_lot" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">No Lot</label>
                                    <input type="text" name="no_lot" id="no_lot" class="form-control" 
                                           value="{{ old('no_lot') }}" required placeholder="Masukkan No Lot..."
                                           style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="qty" class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pcs (Quantity)</label>
                                    <input type="number" name="qty" id="qty" class="form-control" 
                                           value="{{ old('qty') }}" required placeholder="Masukkan jumlah qty..." min="1"
                                           style="font-size: 0.82rem; border-color: #cbd5e1; border-radius: 6px;">
                                    <div id="wip_qty_info" class="mt-2" style="font-size: 0.72rem; line-height: 1.3;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-outline-secondary font-weight-bold" id="btnSubmit" style="font-size: 0.68rem; padding: 0.35rem 0.85rem; border-radius: 6px; letter-spacing: 0.5px;">
                                <i class="fas fa-qrcode mr-1.5"></i>  GENERATE QR 
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
                        Preview Label Cetak Pasang
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
                <h5 class="modal-title font-weight-bold" id="qrScannerModalLabel"><i class="fas fa-qrcode mr-2 text-primary"></i>Kamera Scan WIP</h5>
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

    // Parse WIP QR code and fill displays
    function parseWIPQR(qrText) {
        if (!qrText) {
            $('#display_customer_part').val('');
            $('#wip_qty_info').text('');
            $('#qty').attr('max', '');
            return;
        }
        const parts = qrText.split('|');
        if (parts.length >= 5) {
            $('#display_customer_part').val(parts[0]);
            
            // Query remaining quantity via AJAX
            $.getJSON("{{ route('plating_scan.wip_info') }}", { qr: qrText }, function(data) {
                if (data.success) {
                    $('#display_customer_part').val(data.customer_part);
                    $('#wip_qty_info').html(
                        `<i class="fas fa-info-circle mr-1"></i> Sisa Qty WIP: <strong>${data.remaining_qty}</strong> pcs (Total WIP: ${data.original_qty} pcs)`
                    ).removeClass('text-danger').addClass('text-info');
                    $('#qty').attr('max', data.remaining_qty);
                    
                    if (data.remaining_qty === 0) {
                        $('#wip_qty_info').html(
                            `<i class="fas fa-exclamation-triangle mr-1"></i> Sisa Qty WIP: <strong>0</strong> pcs (WIP lot ini sudah habis terpakai!)`
                        ).removeClass('text-info').addClass('text-danger');
                    }
                } else {
                    $('#wip_qty_info').text(data.message).removeClass('text-info').addClass('text-danger');
                    $('#qty').attr('max', '');
                }
            }).fail(function() {
                $('#wip_qty_info').text('Gagal memverifikasi data WIP ke server.').removeClass('text-info').addClass('text-danger');
                $('#qty').attr('max', '');
            });
        } else {
            $('#display_customer_part').val('');
            $('#wip_qty_info').text('');
            $('#qty').attr('max', '');
        }
    }

    // Event input manually
    $('#wip_qrcode').on('input', function() {
        parseWIPQR($(this).val());
    });

    if ($('#wip_qrcode').val()) {
        parseWIPQR($('#wip_qrcode').val());
    }

    // Audio Feedback helper
    let audioContext = null;
    function playBeep() {
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

    // QR scanner trigger
    $('#btnScanWIP').on('click', function() {
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
                playBeep();
                
                // Validate format (at least 5 parts separated by |)
                const parts = decodedText.split('|');
                if (parts.length < 5) {
                    html5QrCode.stop().then(() => {
                        $('#qrScannerModal').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Format QR Tidak Valid',
                            text: 'Format QR WIP tidak valid. Minimal harus berisi 5 bagian terpisah karakter pipe (|).',
                            confirmButtonText: 'Scan Ulang'
                        }).then(() => {
                            $('#qrScannerModal').modal('show');
                        });
                    }).catch(err => {
                        console.log("Failed to stop scanner", err);
                    });
                    return;
                }

                html5QrCode.stop().then(() => {
                    $('#qrScannerModal').modal('hide');
                    $('#wip_qrcode').val(decodedText);
                    parseWIPQR(decodedText);
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

    @if(session('print_pasang_id'))
    showPrintPreview("{{ route('plating_scan.pasang.qr', session('print_pasang_id')) }}");
    @endif
});
</script>
@endpush
