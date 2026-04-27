<div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header">
                <h5 class="modal-title" id="qrScannerModalLabel"><i class="fas fa-qrcode mr-2 text-primary"></i>QR Code Scanner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 overflow-hidden" style="border-radius: 0 0 12px 12px;">
                <div class="position-relative bg-dark" style="min-height: 300px;">
                    <video id="qr-video" class="w-100" autoplay muted playsinline style="display: block;"></video>
                    
                    <div class="scanner-overlay position-absolute w-100 h-100" style="top:0; left:0; pointer-events:none; border: 2px solid rgba(255,255,255,0.2); box-sizing: border-box;">
                        <div class="scanner-laser position-absolute w-100" style="height: 2px; background: rgba(255, 0, 0, 0.5); top: 50%; box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);"></div>
                    </div>

                    <div class="scanner-controls position-absolute d-flex" style="top: 15px; right: 15px; gap: 10px; z-index: 10;">
                        <button type="button" id="toggleFlashBtn" class="btn btn-sm btn-dark rounded-circle d-none" style="width: 35px; height: 35px; opacity: 0.8;">
                            <i class="fas fa-bolt"></i>
                        </button>
                        <button type="button" id="toggleMirrorBtn" class="btn btn-sm btn-dark rounded-circle" style="width: 35px; height: 35px; opacity: 0.8;">
                            <i class="fas fa-arrows-alt-h"></i>
                        </button>
                    </div>
                </div>

                <style>
                    #qr-video.mirrored { transform: scaleX(-1) !important; }
                    #zoomContainer { background: rgba(0,0,0,0.8); }
                    .scanner-laser {
                        animation: laser-scan 2s infinite ease-in-out;
                    }
                    @keyframes laser-scan {
                        0%, 100% { top: 10%; }
                        50% { top: 90%; }
                    }
                </style>

                <div id="zoomContainer" class="p-3 d-none">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-search-minus text-white mr-3"></i>
                        <input type="range" id="zoomSlider" class="custom-range flex-grow-1" min="1" max="1" step="0.1" value="1">
                        <i class="fas fa-search-plus text-white ml-3"></i>
                    </div>
                </div>

                <div id="qr-reader-results" class="p-4 text-center d-none bg-white">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="sr-only">Memuat...</span>
                    </div>
                    <p class="mb-0 text-muted font-weight-bold">Memproses data QR...</p>
                </div>

                <div class="p-4 bg-light border-top">
                    <div class="d-flex align-items-center mb-2 text-dark font-weight-bold small">
                        <i class="fas fa-file-upload mr-2 text-info"></i> ATAU UNGGAH GAMBAR QR
                    </div>
                    <div class="custom-file custom-file-sm">
                        <input type="file" id="qr-input-file" accept="image/*" class="custom-file-input">
                        <label class="custom-file-label text-muted" for="qr-input-file" style="font-size: 0.75rem;">Pilih gambar QR...</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0">
                <button type="button" class="btn btn-light font-weight-bold text-muted px-4" data-dismiss="modal">BATAL</button>
            </div>
        </div>
    </div>
</div>
