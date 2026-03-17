/**
 * Modul JavaScript Sortir
 * Merangkum logika untuk tampilan Index dan Create
 */

class SortirIndex {
    constructor() {
        this.initializeLiveSearch();
        this.initializeEditModal();
    }

    initializeLiveSearch() {
        let searchTimeout;
        $('#liveSearch').on('input', function () {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val();

            searchTimeout = setTimeout(function () {
                const url = new URL(window.location.href);
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }, 500);
        });
    }

    initializeEditModal() {
        $('.btn-edit-modal').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $('#editModal').modal('show');
            $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                url: url,
                success: function (response) {
                    $('#editModalBody').html(response);
                },
                error: function (xhr) {
                    var message = 'Gagal memuat data checksheet.';
                    if (xhr.status === 404) {
                        message = 'Data checksheet tidak ditemukan.';
                    } else if (xhr.status === 403) {
                        message = 'Anda tidak memiliki akses untuk mengedit checksheet ini.';
                    } else if (xhr.status === 500) {
                        message = 'Terjadi kesalahan pada server.';
                    }
                    $('#editModalBody').html('<div class="alert alert-danger">' + message + '</div>');
                }
            });
        });
    }
}

class SortirCreate {
    constructor(config) {
        this.config = config;
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pageRendering = false;
        this.pageNumPending = null;
        this.scale = 1.0;
        this.canvas = document.getElementById('the-canvas');
        this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
        
        this.timerInterval = null;
        this.totalSeconds = 0;
        this.timerRunning = false;
        this.formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
        
        this.currentPdfIndex = 0;
        this.totalPdfFiles = 0;
        this.currentItemId = null;

        this.init();
    }

    init() {
        this.setupHeartbeat();
        this.setupPdfWorker();
        this.setupInputLock();
        this.setupEventListeners();
        this.setupPdfControls();
        this.setupTimer();
        this.setupFormSubmission();
    }

    setupHeartbeat() {
        if (this.config.heartbeatUrl) {
            setInterval(() => {
                $.get(this.config.heartbeatUrl).catch(function (err) {
                    console.warn("Heartbeat failed, session may be expired");
                });
            }, 10 * 60 * 1000); // 10 minutes
        }
    }

    setupPdfWorker() {
        if (window.pdfjsLib && this.config.pdfWorkerSrc) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;
        }
    }

    setupInputLock() {
        this.formInputs.prop('disabled', true);
        $('#checksheetForm').addClass('inputs-locked');
        if ($('#input-lock-style').length === 0) {
            $('<style id="input-lock-style">#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');
        }
    }

    setupEventListeners() {
        // Event Listeners untuk Isi Otomatis
        $('input[name="total_qty"]').on('input', () => {
            var lotSize = parseInt($('input[name="total_qty"]').val()) || 0;
            var sampleSize = lotSize; // Sortir is 100% check
            $('input[name="sampling_qty"]').val(sampleSize);
            // Isi otomatis OK dan NG
            $('input[name="total_ok"]').val(sampleSize);
            $('input[name="total_ng"]').val(0);
            $('#checkOK').prop('checked', true);
            this.updateJudgment();
        });

        $('#checkOK').on('change', () => {
            if ($('#checkOK').is(':checked')) {
                var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
                $('input[name="total_ok"]').val(sampling);
                $('input[name="total_ng"]').val(0);
                this.updateJudgment();
            }
        });

        // Sinkronisasi Dua Arah
        $('input[name="sampling_qty"]').on('input', () => {
            var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
            var ok = parseInt($('input[name="total_ok"]').val()) || 0;
            $('input[name="total_ng"]').val(Math.max(0, sampling - ok));
            this.updateJudgment();
        });

        $('input[name="total_ok"]').on('input', () => {
            var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
            var ok = parseInt($('input[name="total_ok"]').val()) || 0;
            $('input[name="total_ng"]').val(Math.max(0, sampling - ok));
            this.updateJudgment();
        });

        $('input[name="total_ng"]').on('input', () => {
            var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
            var ng = parseInt($('input[name="total_ng"]').val()) || 0;
            $('input[name="total_ok"]').val(Math.max(0, sampling - ng));
            this.updateJudgment();
        });

        // Memperbarui kuantitas defect juga memicu pembaruan NG
        $(document).on('input', 'input[name="defect_quantities[]"]', () => {
            var totalNG = 0;
            $('input[name="defect_quantities[]"]').each(function () {
                totalNG += parseInt($(this).val()) || 0;
            });
            $('input[name="total_ng"]').val(totalNG).trigger('input');
        });

        // Isi otomatis tipe sumber, id sumber, dan total_qty saat item dipilih
        $('#ngItemSelect').on('change', () => {
            var selectedOption = $('#ngItemSelect').find('option:selected');
            $('#sourceType').val(selectedOption.data('source-type'));
            $('#sourceId').val(selectedOption.data('source-id'));

            // Perbarui Kontainer Gambar/PDF
            var files = selectedOption.data('files');
            var itemId = selectedOption.val();
            var container = $('#imageContainer');

            if (files && files.length > 0) {
                container.html('<button type="button" class="btn btn-danger btn-sm view-pdf-btn" data-id="' + itemId + '" data-count="' + files.length + '"><i class="fas fa-file-pdf"></i> PDF (' + files.length + ')</button>');
            } else {
                container.html('<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
            }

            // Isi otomatis total_qty dengan sisa qty dan atur batas maksimal
            var remainingQty = parseInt(selectedOption.data('remaining-qty')) || 0;
            var totalQtyInput = $('input[name="total_qty"]');
            totalQtyInput.val(remainingQty);

            // Juga perbarui sampling_qty, total_ok, total_ng
            $('input[name="sampling_qty"]').val(remainingQty);
            $('input[name="total_ok"]').val(remainingQty);
            $('input[name="total_ng"]').val(0);
            $('#checkOK').prop('checked', true);
            this.updateJudgment();
        });

        // Validasi total_qty tidak melebihi sisa qty - tampilkan konfirmasi
        $('input[name="total_qty"]').on('change', () => {
            var input = $('input[name="total_qty"]');
            var selectedOption = $('#ngItemSelect').find('option:selected');
            var remainingQty = parseInt(selectedOption.data('remaining-qty')) || 0;
            var val = parseInt(input.val()) || 0;

            if (remainingQty > 0 && val > remainingQty) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Qty Melebihi Sisa',
                    html: 'Qty yang diinput (<b>' + val + ' pcs</b>) melebihi sisa qty tercatat (<b>' + remainingQty + ' pcs</b>).<br><br>Apakah Anda yakin ingin melanjutkan?',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Reset ke Sisa Qty'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        input.val(remainingQty);
                        $('input[name="sampling_qty"]').val(remainingQty);
                        $('input[name="total_ok"]').val(remainingQty);
                        $('input[name="total_ng"]').val(0);
                        this.updateJudgment();
                    } else {
                        $('input[name="sampling_qty"]').val(val);
                        $('input[name="total_ok"]').val(val);
                        $('input[name="total_ng"]').val(0);
                        this.updateJudgment();
                    }
                });
            }
        });

        // Manajemen baris defect
        $('#addDefectBtn').on('click', () => {
            var newRow = `
                <div class="input-group mb-2 defect-row">
                    <input type="text" class="form-control" style="min-width: 180px;" name="defect_types[]" placeholder="Jenis Defect">
                    <input type="number" class="form-control" style="min-width: 100px;" name="defect_quantities[]" placeholder="Qty" min="1">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger btn-sm remove-defect"><i class="fas fa-times"></i></button>
                    </div>
                </div>`;
            $('#defectContainer').append(newRow);
        });

        $(document).on('click', '.remove-defect', (e) => {
            $(e.currentTarget).closest('.defect-row').remove();
            $('input[name="defect_quantities[]"]').first().trigger('input');
        });

        $('#judgmentSelect').on('change', () => {
            this.toggleNextProsesDropdown();
        });
    }

    updateJudgment() {
        var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
        var ng = parseInt($('input[name="total_ng"]').val()) || 0;

        $('#aql_info').hide();

        var judgmentSelect = $('#judgmentSelect');
        if (sampling > 0 || ng > 0) {
            if (ng > 0) {
                judgmentSelect.val('NG').removeClass('text-success').addClass('text-danger');
            } else {
                judgmentSelect.val('OK').removeClass('text-danger').addClass('text-success');
            }
        } else {
            judgmentSelect.val('').removeClass('text-success text-danger');
        }

        this.toggleNextProsesDropdown();
    }

    toggleNextProsesDropdown() {
        var judgment = $('#judgmentSelect').val();
        if (judgment === 'NG') {
            $('#nextProsesContainer').slideDown();
        } else {
            $('#nextProsesContainer').slideUp();
            $('#nextProses').val('');
        }
    }

    setupPdfControls() {
        if (!this.canvas) return;

        document.getElementById('prevPage').addEventListener('click', () => {
            if (this.pageNum <= 1) return;
            this.pageNum--;
            this.queueRenderPage(this.pageNum);
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            if (this.pdfDoc && this.pageNum >= this.pdfDoc.numPages) return;
            this.pageNum++;
            this.queueRenderPage(this.pageNum);
        });

        document.getElementById('pdfZoomIn').addEventListener('click', () => {
            this.scale += 0.25;
            this.queueRenderPage(this.pageNum);
        });

        document.getElementById('pdfZoomOut').addEventListener('click', () => {
            if (this.scale > 0.25) {
                this.scale -= 0.25;
                this.queueRenderPage(this.pageNum);
            }
        });

        document.getElementById('pdfZoomReset').addEventListener('click', () => {
            this.scale = 1.0;
            this.queueRenderPage(this.pageNum);
        });

        document.getElementById('prevPdf').addEventListener('click', () => {
            if (this.currentPdfIndex <= 0) return;
            this.currentPdfIndex--;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });

        document.getElementById('nextPdf').addEventListener('click', () => {
            if (this.currentPdfIndex >= this.totalPdfFiles - 1) return;
            this.currentPdfIndex++;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });

        $(document).on('click', '.view-pdf-btn', (e) => {
            this.currentItemId = $(e.currentTarget).data('id');
            this.totalPdfFiles = $(e.currentTarget).data('count');
            this.currentPdfIndex = 0;
            $('#pdfModal').modal('show');
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });
    }

    renderPage(num) {
        this.pageRendering = true;
        this.pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: this.scale });
            this.canvas.height = viewport.height;
            this.canvas.width = viewport.width;

            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };
            const renderTask = page.render(renderContext);

            renderTask.promise.then(() => {
                this.pageRendering = false;
                if (this.pageNumPending !== null) {
                    this.renderPage(this.pageNumPending);
                    this.pageNumPending = null;
                }
            });
        });
        document.getElementById('pageInfo').textContent = 'Page ' + num + ' of ' + this.pdfDoc.numPages;
    }

    queueRenderPage(num) {
        if (this.pageRendering) {
            this.pageNumPending = num;
        } else {
            this.renderPage(num);
        }
    }

    loadPdf(itemId, index) {
        if (!this.config.pdfUrlPattern) return;
        
        const url = this.config.pdfUrlPattern.replace('ID_PLACEHOLDER', itemId).replace('INDEX_PLACEHOLDER', index);

        this.pdfDoc = null;
        this.pageNum = 1;
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        document.getElementById('pageInfo').textContent = 'Loading...';
        document.getElementById('pdfInfo').textContent = `File ${index + 1} of ${this.totalPdfFiles}`;

        pdfjsLib.getDocument(url).promise.then((pdfDoc_) => {
            this.pdfDoc = pdfDoc_;
            document.getElementById('pageInfo').textContent = 'Page 1 of ' + this.pdfDoc.numPages;
            this.renderPage(this.pageNum);
        }).catch((reason) => {
            console.error(reason);
            let errorMsg = 'Error loading PDF. ';
            if (reason.name === 'MissingPDFException') {
                errorMsg += 'The PDF file could not be found on the server.';
            } else {
                errorMsg += reason.message || reason;
            }
            document.getElementById('pageInfo').textContent = 'Error: ' + reason.name;
            alert(errorMsg);
        });
    }

    setupTimer() {
        $('#startTimerBtn').on('click', () => {
            if (!this.timerRunning) {
                this.timerRunning = true;
                $('#startTimerBtn').removeClass('btn-success').addClass('btn-secondary').prop('disabled', true);
                $('#startTimerBtn').html('<i class="fas fa-clock"></i> Running...');
                $('#saveBtn').prop('disabled', false);

                // === BUKA KUNCI SEMUA INPUT ===
                this.formInputs.prop('disabled', false);
                $('#checksheetForm').removeClass('inputs-locked');

                this.timerInterval = setInterval(() => {
                    this.totalSeconds++;
                    this.updateTimerDisplay();
                }, 1000);
            }
        });
    }

    updateTimerDisplay() {
        var hours = Math.floor(this.totalSeconds / 3600);
        var minutes = Math.floor((this.totalSeconds % 3600) / 60);
        var seconds = this.totalSeconds % 60;
        var text = [hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":");
        $('#timerDisplay').text(text);
        $('#cycleTimeInput').val(this.totalSeconds);
    }

    setupFormSubmission() {
        $('#checksheetForm').on('submit', (e) => {
            e.preventDefault();

            var judgment = $('#judgmentSelect').val();
            var nextProses = $('#nextProses').val();

            if (judgment === 'NG' && !nextProses) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Next Proses Wajib Dipilih',
                    text: 'Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!',
                    confirmButtonColor: '#3085d6'
                });
                $('#nextProses').focus();
                return false;
            }

            if (this.timerRunning) {
                clearInterval(this.timerInterval);
                this.timerRunning = false;
                $('#cycleTimeInput').val(this.totalSeconds);
            }

            var saveBtn = $('#saveBtn');
            var originalHtml = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            var formData = new FormData($('#checksheetForm')[0]);

            $.ajax({
                url: $('#checksheetForm').attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    $('#global-loader').hide();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data Berhasil Disimpan',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Lihat Data',
                            cancelButtonText: 'Tutup',
                            reverseButtons: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.index_url;
                            } else {
                                $('#checksheetForm')[0].reset();
                                this.resetState();
                            }
                        });
                    }
                },
                error: (xhr) => {
                    $('#global-loader').hide();
                    var errorMsg = 'Gagal menyimpan data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                    saveBtn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    }

    resetState() {
        clearInterval(this.timerInterval);
        this.timerRunning = false;
        this.totalSeconds = 0;
        this.updateTimerDisplay();
        $('#startTimerBtn').removeClass('btn-secondary').addClass('btn-success').prop('disabled', false).html('<i class="fas fa-play"></i> Start');

        this.formInputs.prop('disabled', true);
        $('#checksheetForm').addClass('inputs-locked');
        $('#saveBtn').prop('disabled', true);

        $('#defectContainer').find('.defect-row').not(':first').remove();
        $('#imageContainer').html('<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
        $('#ngItemSelect').val('').trigger('change');
        $('#nextProsesContainer').hide();
    }
}

// Fungsi inisialisasi Global
window.initSortirIndex = function () {
    return new SortirIndex();
};

window.initSortirCreate = function (config) {
    return new SortirCreate(config);
};
