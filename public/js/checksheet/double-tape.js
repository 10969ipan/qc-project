/**
 * Modul Double Tape
 * Menangani tampilan Index dan Create/Edit untuk Checksheet Double Tape
 */

class DoubleTapeIndex {
    constructor(config = {}) {
        this.config = {
            indexRoute: config.indexRoute || '',
            ...config
        };
        this.init();
    }

    init() {
        this.initLiveSearch();
        this.initModals();
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById('liveSearch');
        if (liveSearchInput) {
            let searchTimeout;
            liveSearchInput.addEventListener('keyup', () => {
                const searchTerm = liveSearchInput.value.trim();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const params = new URLSearchParams(window.location.search);
                    if (searchTerm) {
                        params.set('search', searchTerm);
                    } else {
                        params.delete('search');
                    }
                    window.location.href = `${this.config.indexRoute}?${params.toString()}`;
                }, 500);
            });
        }
    }

    initModals() {
        // Modal Edit
        $('.btn-edit-modal').on('click', (e) => {
            e.preventDefault();
            const url = $(e.currentTarget).attr('href');
            $('#editModal').modal('show');
            $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
            $.ajax({
                url: url,
                success: (response) => { $('#editModalBody').html(response); },
                error: () => { $('#editModalBody').html('<div class="alert alert-danger">Gagal memuat data.</div>'); }
            });
        });

        // Modal Status
        $('.btn-status-modal').on('click', (e) => {
            e.preventDefault();
            const url = $(e.currentTarget).attr('href');
            $('#statusModal').modal('show');
            $('#statusModalBody').html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div></div>');
            $.ajax({
                url: url,
                success: (response) => { $('#statusModalBody').html(response); },
                error: () => { $('#statusModalBody').html('<div class="alert alert-danger">Gagal memuat data.</div>'); }
            });
        });
    }
}

class DoubleTapeCreate {
    constructor(config = {}) {
        this.config = {
            pdfWorkerSrc: config.pdfWorkerSrc || 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js',
            pdfUrlPattern: config.pdfUrlPattern || '',
            ...config
        };

        // Variabel state
        this.timerInterval = null;
        this.totalSeconds = 0;
        this.timerRunning = false;
        this.isFullcheck = false;
        this.pdfCache = {};
        
        // State Pratinjau PDF
        this.standardZoomLevel = 1.0;
        this.similarZoomLevel = 1.0;
        this.refStandardPdfDoc = null;
        this.refStandardPageNum = 1;
        this.refSimilarPdfDoc = null;
        this.refSimilarPageNum = 1;

        // State Modal PDF
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pageRendering = false;
        this.pageNumPending = null;
        this.scale = 1.5;
        this.currentPdfIndex = 0;
        this.totalPdfFiles = 0;
        this.currentItemId = null;

        this.init();
    }

    init() {
        this.initPdfJS();
        this.initInputLocking();
        this.initTimer();
        this.initTypeHandling();
        this.initAQLCalculations();
        this.initSAPCodeAutoSelect();
        this.initItemSelection();
        this.initDefectManagement();
        this.initPDFSideBySide();
        this.initPDFModal();
        this.initImageZoom();
        this.initFormSubmission();

        // Picu jika item dipilih saat dimuat
        setTimeout(() => {
            if ($('#itemSelect').val()) {
                $('#itemSelect').trigger('change');
            }
        }, 500);
    }

    initPdfJS() {
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;
        }
    }

    initInputLocking() {
        this.formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
        this.formInputs.prop('disabled', true);
        $('#checksheetForm').addClass('inputs-locked');
    }

    initTimer() {
        $('#startTimerBtn').on('click', () => {
            if (!this.timerRunning) {
                this.timerRunning = true;
                $('#startTimerBtn').removeClass('btn-success').addClass('btn-secondary').attr('disabled', true).html('<i class="fas fa-clock"></i> Running...');
                $('#saveBtn').prop('disabled', false);

                // Buka kunci input
                this.formInputs.prop('disabled', false);
                $('#checksheetForm').removeClass('inputs-locked');

                // Logika readonly spesifik
                $('#samplingQty').prop('readonly', this.isFullcheck);
                $('input[name="total_ok"]').prop('readonly', true);

                this.timerInterval = setInterval(() => {
                    this.totalSeconds++;
                    this.updateTimerDisplay();
                }, 1000);

                $('#itemSelect').focus();
            }
        });
    }

    updateTimerDisplay() {
        const hours = Math.floor(this.totalSeconds / 3600);
        const minutes = Math.floor((this.totalSeconds % 3600) / 60);
        const seconds = this.totalSeconds % 60;
        const text = [hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":");
        $('#timerDisplay').text(text);
        $('#cycleTimeInput').val(this.totalSeconds);
    }

    initTypeHandling() {
        $('input[name="check_type_option"]').on('change', (e) => {
            this.isFullcheck = ($(e.currentTarget).val() === 'fullcheck');
            if (this.timerRunning) {
                $('#samplingQty').prop('readonly', this.isFullcheck);
            }
            $('#totalQty').trigger('input');
            $('#totalNG').trigger('input');
        });
    }

    initAQLCalculations() {
        const getSampleSize = (lotSize) => {
            if (lotSize >= 500001) return 1250;
            if (lotSize >= 150001) return 800;
            if (lotSize >= 35001) return 500;
            if (lotSize >= 10001) return 315;
            if (lotSize >= 3201) return 200;
            if (lotSize >= 1201) return 125;
            if (lotSize >= 501) return 80;
            if (lotSize >= 281) return 50;
            if (lotSize >= 151) return 32;
            if (lotSize >= 20) return 20;
            return lotSize;
        };

        const getAqlLimits = (sampleSize) => {
            if (sampleSize >= 1250) return { acc: 14, rej: 15 };
            if (sampleSize >= 800) return { acc: 10, rej: 11 };
            if (sampleSize >= 500) return { acc: 7, rej: 8 };
            if (sampleSize >= 315) return { acc: 5, rej: 6 };
            if (sampleSize >= 200) return { acc: 3, rej: 4 };
            if (sampleSize >= 125) return { acc: 2, rej: 3 };
            if (sampleSize >= 80) return { acc: 1, rej: 2 };
            if (sampleSize >= 20) return { acc: 0, rej: 1 };
            return { acc: 0, rej: 1 };
        };

        $('#totalQty').on('input', (e) => {
            let lot = parseInt($(e.currentTarget).val()) || 0;
            if (lot > 0) {
                let sample = this.isFullcheck ? lot : getSampleSize(lot);
                $('#samplingQty').val(sample).trigger('input');
            } else {
                $('#samplingQty').val(0).trigger('input');
            }
        });

        $('#totalNG, #samplingQty').on('input', () => {
            let total = parseInt($('#samplingQty').val()) || 0;
            let ng = parseInt($('#totalNG').val()) || 0;
            let ok = total - ng;
            $('input[name="total_ok"]').val(ok < 0 ? 0 : ok);

            const judgmentSelect = $('#judgmentSelect');
            const judgmentBadge = $('#judgmentBadge');

            if (total > 0 || ng > 0) {
                const limits = this.isFullcheck ? { acc: 0, rej: 1 } : getAqlLimits(total);

                if (!this.isFullcheck) {
                    $('#acc_val').text(limits.acc);
                    $('#rej_val').text(limits.rej);
                    $('#aql_info').show();
                } else {
                    $('#aql_info').hide();
                }

                if (ng <= limits.acc) {
                    judgmentSelect.val('OK');
                    judgmentBadge.text('OK').removeClass('d-none text-danger').addClass('text-success')
                        .css({ 'border-color': '#28a745', 'background-color': '#fff' });
                } else {
                    judgmentSelect.val('NG');
                    judgmentBadge.text('NG').removeClass('d-none text-success').addClass('text-danger')
                        .css({ 'border-color': '#dc3545', 'background-color': '#fff' });
                }
            } else {
                $('#aql_info').hide();
                judgmentSelect.val('');
                judgmentBadge.addClass('d-none').text('-');
            }

            // Visibilitas Next Proses
            const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;
            if (judgmentSelect.val() === 'NG' || ngCount > 0) {
                $('#nextProsesContainer').slideDown();
            } else {
                $('#nextProsesContainer').slideUp();
            }
        });

        $('#judgmentSelect').on('change', (e) => {
            const val = $(e.currentTarget).val();
            const judgmentBadge = $('#judgmentBadge');
            const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;

            if (val === 'OK') {
                judgmentBadge.text('OK').removeClass('d-none text-danger').addClass('text-success')
                    .css({ 'border-color': '#28a745', 'background-color': '#fff' });
            } else if (val === 'NG') {
                judgmentBadge.text('NG').removeClass('d-none text-success').addClass('text-danger')
                    .css({ 'border-color': '#dc3545', 'background-color': '#fff' });
            } else {
                judgmentBadge.addClass('d-none').text('-');
            }

            if (val === 'NG' || ngCount > 0) {
                $('#nextProsesContainer').show();
            } else {
                $('#nextProsesContainer').hide();
            }
        });
    }

    initSAPCodeAutoSelect() {
        $('#sapCodeInput').on('input', (e) => {
            const sapCode = $(e.currentTarget).val().trim();
            if (sapCode.length >= 1) {
                const matchedOption = $('#itemSelect option').filter(function () {
                    const itemSapCode = $(this).data('sap-code');
                    return itemSapCode && itemSapCode.toString().toLowerCase() === sapCode.toLowerCase();
                });
                if (matchedOption.length > 0) {
                    $('#itemSelect').val(matchedOption.val()).trigger('change');
                    $('#sapCodeInput').removeClass('is-invalid').addClass('is-valid');
                } else {
                    $('#sapCodeInput').removeClass('is-valid').addClass('is-invalid');
                }
            } else {
                $('#sapCodeInput').removeClass('is-valid is-invalid');
            }
        });
    }

    initItemSelection() {
        $('#itemSelect').on('change', (e) => {
            const selectedOption = $(e.currentTarget).find('option:selected');
            const itemId = selectedOption.val();
            const files = selectedOption.data('files');
            const standardPdf = selectedOption.data('standard');
            const similarPdf = selectedOption.data('similar');
            let defects = selectedOption.data('defects');

            // Perbarui pratinjau Berdampingan
            if (standardPdf) {
                this.renderPdfToCanvas(standardPdf, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading');
                $('#fullStandardBtn').attr('data-id', itemId).attr('data-count', files ? files.length : 1).show();
            } else {
                $('#standardPdfCanvas').addClass('d-none').hide();
                $('#standardPdfPlaceholder').removeClass('d-none').addClass('d-flex').find('p').text('Standard PDF tidak tersedia');
                $('#fullStandardBtn').hide();
            }

            if (similarPdf) {
                this.renderPdfToCanvas(similarPdf, 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading');
                $('#fullSimilarBtn').attr('data-id', itemId).data('similar', true).show();
                $('#similarStatusText').text('');
            } else {
                $('#similarPdfCanvas').addClass('d-none').hide();
                $('#similarPdfPlaceholder').removeClass('d-none').addClass('d-flex');
                $('#similarStatusText').text('Referral Similar Part tidak tersedia untuk item ini');
                $('#fullSimilarBtn').hide();
            }

            // Populasi defect
            const defectSelect = $('#defectSelect');
            defectSelect.html('<option value="">-- Pilih Defect --</option>');

            if (typeof defects === 'string') {
                try { defects = JSON.parse(defects); } catch (e) { defects = null; }
            }

            if (Array.isArray(defects) && defects.length > 0) {
                defects.forEach(d => defectSelect.append(`<option value="${d}">${d}</option>`));
                if (!defects.includes('Dimensi') && !defects.includes('dimension')) {
                    defectSelect.append('<option value="Dimensi">Dimensi</option>');
                }
            } else {
                ['BARET', 'SILVER', 'FLOW', 'FLASH', 'KOTOR', 'DENYUT', 'Dimensi'].forEach(d => defectSelect.append(`<option value="${d}">${d}</option>`));
            }
            $('#addDefectBtn').show();
        });
    }

    initDefectManagement() {
        $('#addDefectBtn').on('click', () => {
            const firstSelect = $('#defectSelect');
            const clone = $('<div class="input-group mb-2 defect-row">' +
                '<select class="form-control defect-select" name="defect_types[]">' + firstSelect.html() + '</select>' +
                '<input type="number" class="form-control defect-qty" name="defect_quantities[]" placeholder="Qty" min="1">' +
                '<div class="input-group-append"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-minus"></i></button></div>' +
                '</div>');
            $('#defectContainer').append(clone);
        });

        $(document).on('click', '.btn-remove-row', (e) => {
            $(e.currentTarget).closest('.defect-row').remove();
            this.calculateTotalNG();
        });

        $(document).on('input', '.defect-qty', () => {
            this.calculateTotalNG();
        });
    }

    calculateTotalNG() {
        let totalNG = 0;
        $('.defect-qty').each(function () {
            totalNG += parseInt($(this).val()) || 0;
        });
        $('#totalNG').val(totalNG).trigger('input');
    }


    initPDFSideBySide() {
        window.renderPdfToCanvas = (url, canvasId, placeholderId, loadingId, pageNum = 1) => {
            this.renderPdfToCanvas(url, canvasId, placeholderId, loadingId, pageNum);
        };

        $('#zoomInStandard').on('click', () => {
            this.standardZoomLevel += 0.25;
            if (this.refStandardPdfDoc) this.renderPageOnCanvas(this.refStandardPdfDoc, document.getElementById('standardPdfCanvas'), this.standardZoomLevel, this.refStandardPageNum, 'standardPdfCanvas');
        });
        $('#zoomOutStandard').on('click', () => {
            if (this.standardZoomLevel > 0.5) {
                this.standardZoomLevel -= 0.25;
                if (this.refStandardPdfDoc) this.renderPageOnCanvas(this.refStandardPdfDoc, document.getElementById('standardPdfCanvas'), this.standardZoomLevel, this.refStandardPageNum, 'standardPdfCanvas');
            }
        });
        $('#zoomResetStandard').on('click', () => {
            this.standardZoomLevel = 1.0;
            if (this.refStandardPdfDoc) this.renderPageOnCanvas(this.refStandardPdfDoc, document.getElementById('standardPdfCanvas'), this.standardZoomLevel, this.refStandardPageNum, 'standardPdfCanvas');
        });

        $('#zoomInSimilar').on('click', () => {
            this.similarZoomLevel += 0.25;
            if (this.refSimilarPdfDoc) this.renderPageOnCanvas(this.refSimilarPdfDoc, document.getElementById('similarPdfCanvas'), this.similarZoomLevel, this.refSimilarPageNum, 'similarPdfCanvas');
        });
        $('#zoomOutSimilar').on('click', () => {
            if (this.similarZoomLevel > 0.5) {
                this.similarZoomLevel -= 0.25;
                if (this.refSimilarPdfDoc) this.renderPageOnCanvas(this.refSimilarPdfDoc, document.getElementById('similarPdfCanvas'), this.similarZoomLevel, this.refSimilarPageNum, 'similarPdfCanvas');
            }
        });
        $('#zoomResetSimilar').on('click', () => {
            this.similarZoomLevel = 1.0;
            if (this.refSimilarPdfDoc) this.renderPageOnCanvas(this.refSimilarPdfDoc, document.getElementById('similarPdfCanvas'), this.similarZoomLevel, this.refSimilarPageNum, 'similarPdfCanvas');
        });

        $('#prevStandardPage').on('click', () => {
            if (this.refStandardPageNum > 1) this.renderPdfToCanvas(null, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', this.refStandardPageNum - 1);
        });
        $('#nextStandardPage').on('click', () => {
            if (this.refStandardPdfDoc && this.refStandardPageNum < this.refStandardPdfDoc.numPages) this.renderPdfToCanvas(null, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', this.refStandardPageNum + 1);
        });
        $('#prevSimilarPage').on('click', () => {
            if (this.refSimilarPageNum > 1) this.renderPdfToCanvas(null, 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', this.refSimilarPageNum - 1);
        });
        $('#nextSimilarPage').on('click', () => {
            if (this.refSimilarPdfDoc && this.refSimilarPageNum < this.refSimilarPdfDoc.numPages) this.renderPdfToCanvas(null, 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', this.refSimilarPageNum + 1);
        });
    }

    renderPdfToCanvas(url, canvasId, placeholderId, loadingId, pageNum = 1) {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        const $placeholder = $('#' + placeholderId);
        const $loading = $('#' + loadingId);
        const $canvas = $(canvas);

        $placeholder.removeClass('d-flex').addClass('d-none');
        $canvas.addClass('d-none').hide();
        $loading.removeClass('d-none').addClass('d-flex');

        if (url === null) {
            if (canvasId === 'standardPdfCanvas' && this.refStandardPdfDoc) {
                this.renderPageOnCanvas(this.refStandardPdfDoc, canvas, this.standardZoomLevel, pageNum, canvasId);
            } else if (canvasId === 'similarPdfCanvas' && this.refSimilarPdfDoc) {
                this.renderPageOnCanvas(this.refSimilarPdfDoc, canvas, this.similarZoomLevel, pageNum, canvasId);
            }
            return;
        }

        if (this.pdfCache[url]) {
            const doc = this.pdfCache[url];
            const zoom = (canvasId === 'standardPdfCanvas') ? this.standardZoomLevel : this.similarZoomLevel;
            this.renderPageOnCanvas(doc, canvas, zoom, pageNum, canvasId);
            return;
        }

        pdfjsLib.getDocument(url).promise.then((pdf) => {
            this.pdfCache[url] = pdf;
            const zoom = (canvasId === 'standardPdfCanvas') ? this.standardZoomLevel : this.similarZoomLevel;
            this.renderPageOnCanvas(pdf, canvas, zoom, pageNum, canvasId);
        }).catch((error) => {
            console.error('Error rendering preview PDF:', error);
            $loading.removeClass('d-flex').addClass('d-none');
            $placeholder.removeClass('d-none').addClass('d-flex').find('p').text('Gagal memuat PDF');
        });
    }

    renderPageOnCanvas(pdf, canvas, zoomLevel, pageNum, canvasId) {
        const ctx = canvas.getContext('2d');
        const $loading = (canvasId === 'standardPdfCanvas') ? $('#standardPdfLoading') : $('#similarPdfLoading');
        const $canvas = $(canvas);

        pdf.getPage(pageNum).then((page) => {
            const containerWidth = $canvas.parent().width() || 500;
            const availableWidth = containerWidth - 40;
            const viewport = page.getViewport({ scale: 1.0 });
            const scale = (availableWidth / viewport.width) * zoomLevel;
            const scaledViewport = page.getViewport({ scale: scale });

            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;

            if (zoomLevel > 1.0) {
                $canvas.css({ 'width': 'auto', 'max-width': 'none' });
            } else {
                $canvas.css({ 'width': '100%', 'max-width': '100%' });
            }
            $canvas.css('height', 'auto');

            const renderContext = { canvasContext: ctx, viewport: scaledViewport };
            page.render(renderContext).promise.then(() => {
                $loading.removeClass('d-flex').addClass('d-none');
                $canvas.removeClass('d-none').show();

                if (canvasId === 'standardPdfCanvas') {
                    this.refStandardPdfDoc = pdf;
                    this.refStandardPageNum = pageNum;
                    $('#standardPageInfo').text('P ' + pageNum + '/' + pdf.numPages);
                    $('.standard-nav-controls').attr('style', 'display: flex !important;');
                } else if (canvasId === 'similarPdfCanvas') {
                    this.refSimilarPdfDoc = pdf;
                    this.refSimilarPageNum = pageNum;
                    $('#similarPageInfo').text('P ' + pageNum + '/' + pdf.numPages);
                    $('.similar-nav-controls').attr('style', 'display: flex !important;');
                }
            });
        });
    }

    initPDFModal() {
        this.pdfCanvas = document.getElementById('the-canvas');
        this.pdfCtx = this.pdfCanvas.getContext('2d');

        $('#prevPage').on('click', () => {
            if (this.pageNum <= 1) return;
            this.pageNum--;
            this.queueRenderPage(this.pageNum);
        });

        $('#nextPage').on('click', () => {
            if (this.pageNum >= this.pdfDoc.numPages) return;
            this.pageNum++;
            this.queueRenderPage(this.pageNum);
        });

        $('#pdfZoomIn').on('click', () => {
            this.scale += 0.25;
            this.queueRenderPage(this.pageNum);
        });

        $('#pdfZoomOut').on('click', () => {
            if (this.scale > 0.25) {
                this.scale -= 0.25;
                this.queueRenderPage(this.pageNum);
            }
        });

        $('#pdfZoomReset').on('click', () => {
            this.scale = 1.0;
            this.queueRenderPage(this.pageNum);
        });

        $('#prevPdf').on('click', () => {
            if (this.currentPdfIndex <= 0) return;
            this.currentPdfIndex--;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });

        $('#nextPdf').on('click', () => {
            if (this.currentPdfIndex >= this.totalPdfFiles - 1) return;
            this.currentPdfIndex++;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });

        $(document).on('click', '.view-pdf-btn', (e) => {
            const btn = $(e.currentTarget);
            this.currentItemId = btn.data('id');
            const isSimilar = btn.data('similar');

            if (isSimilar) {
                this.totalPdfFiles = 1;
                this.currentPdfIndex = 'similar';
            } else {
                this.totalPdfFiles = btn.data('count');
                this.currentPdfIndex = 0;
            }

            $('#pdfModal').modal('show');
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });
    }

    loadPdf(itemId, index) {
        const url = this.config.pdfUrlPattern.replace('ID_PLACEHOLDER', itemId).replace('INDEX_PLACEHOLDER', index);
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pdfCtx.clearRect(0, 0, this.pdfCanvas.width, this.pdfCanvas.height);
        document.getElementById('pageInfo').textContent = 'Loading...';

        if (index === 'similar') {
            document.getElementById('pdfInfo').textContent = 'Similar Part PDF';
            $('#prevPdf, #nextPdf').hide();
        } else {
            document.getElementById('pdfInfo').textContent = `File ${index + 1} of ${this.totalPdfFiles}`;
            if (this.totalPdfFiles <= 1) {
                $('#prevPdf, #nextPdf').hide();
            } else {
                $('#prevPdf, #nextPdf').show();
            }
        }

        pdfjsLib.getDocument(url).promise.then((pdfDoc_) => {
            this.pdfDoc = pdfDoc_;
            document.getElementById('pageInfo').textContent = 'Page 1 of ' + this.pdfDoc.numPages;
            this.renderPage(this.pageNum);
        }).catch((reason) => {
            console.error(reason);
            alert('Error loading PDF: ' + (reason.message || reason));
        });
    }

    renderPage(num) {
        this.pageRendering = true;
        this.pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: this.scale });
            this.pdfCanvas.height = viewport.height;
            this.pdfCanvas.width = viewport.width;
            const renderContext = { canvasContext: this.pdfCtx, viewport: viewport };
            const renderTask = page.render(renderContext);
            renderTask.promise.then(() => {
                this.pageRendering = false;
                if (this.pageNumPending !== null) {
                    this.renderPage(this.pageNumPending);
                    this.pageNumPending = null;
                }
            });
        });
        document.getElementById('pageInfo').textContent = `Page ${num} of ${this.pdfDoc.numPages}`;
    }

    queueRenderPage(num) {
        if (this.pageRendering) {
            this.pageNumPending = num;
        } else {
            this.renderPage(num);
        }
    }

    initImageZoom() {
        this.currentZoom = 1;
        $('#zoomIn').on('click', () => {
            this.currentZoom += 0.2;
            $('#modalImage').css('transform', 'scale(' + this.currentZoom + ')');
        });
        $('#zoomOut').on('click', () => {
            if (this.currentZoom > 0.4) {
                this.currentZoom -= 0.2;
                $('#modalImage').css('transform', 'scale(' + this.currentZoom + ')');
            }
        });
        $('#zoomReset').on('click', () => {
            this.currentZoom = 1;
            $('#modalImage').css('transform', 'scale(1)');
        });

        $('#imageModal').on('show.bs.modal', (event) => {
            const button = $(event.relatedTarget);
            const image = button.data('image') || button.attr('src');
            const title = button.data('name') || "Detail Gambar";
            const desc = button.data('description') || "";

            $('#modalImage').attr('src', image).css('transform', 'scale(1)');
            $('#modalTitle').text(title);
            $('#modalDescription').text(desc);
            this.currentZoom = 1;
        });
    }

    initFormSubmission() {
        $('#checksheetForm').on('submit', (e) => {
            e.preventDefault();

            const judgment = $('#judgmentSelect').val();
            const nextProses = $('#nextProses').val();

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

            const saveBtn = $('#saveBtn');
            const originalHtml = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            const formData = new FormData(e.currentTarget);

            $.ajax({
                url: $(e.currentTarget).attr('action'),
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
                    const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data.';
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
        $('#startTimerBtn').removeClass('btn-secondary').addClass('btn-success').removeAttr('disabled').html('<i class="fas fa-play"></i> Start');

        this.formInputs.prop('disabled', true);
        $('#checksheetForm').addClass('inputs-locked');
        $('#saveBtn').prop('disabled', true);

        $('#addDefectBtn').hide();
        $('#defectContainer').find('.defect-row').not(':first').remove();
        $('#itemSelect').val('').trigger('change');
        $('#aql_info').hide();
        $('#nextProsesContainer').hide();

        $('#standardPdfCanvas, #similarPdfCanvas').addClass('d-none').hide();
        $('#standardPdfPlaceholder').removeClass('d-none').addClass('d-flex').find('p').text('Pilih Item untuk menampilkan Standard PDF');
        $('#similarPdfPlaceholder').removeClass('d-none').addClass('d-flex').find('p').text('Pilih Item untuk menampilkan Similar Part');
        $('#similarStatusText').text('');
        $('#fullStandardBtn, #fullSimilarBtn').hide();
        $('.standard-nav-controls, .similar-nav-controls').hide();

        this.standardZoomLevel = 1.0;
        this.similarZoomLevel = 1.0;

        $('#judgmentBadge').addClass('d-none').text('-');
        $('#judgmentSelect').removeClass('text-success text-danger');

        $('#checkTypeSampling').prop('checked', true).trigger('change');
        $('#labelSampling').addClass('active');
        $('#labelFullcheck').removeClass('active');
    }
}

// Global initializers
window.initDoubleTapeIndex = (config) => new DoubleTapeIndex(config);
window.initDoubleTapeCreate = (config) => new DoubleTapeCreate(config);
