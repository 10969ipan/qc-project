/**
 * Modul Checksheet Plating
 */

class PlatingIndex {
    constructor(config) {
        this.config = config;
        this.init();
    }

    init() {
        this.initLiveSearch();
        this.initAjaxModals();
        this.initAjaxForms();
        this.initQRDetail();
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById('liveSearch');
        if (!liveSearchInput) return;
        
        let searchTimeout;
        liveSearchInput.addEventListener('input', () => {
            const searchTerm = liveSearchInput.value.trim();
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const params = new URLSearchParams(window.location.search);
                if (searchTerm) params.set('search', searchTerm);
                else params.delete('search');
                if (startDate) params.set('start_date', startDate);
                if (endDate) params.set('end_date', endDate);
                params.delete('page');
                window.location.href = this.config.indexRoute + '?' + params.toString();
            }, 500);
        });
    }

    initAjaxModals() {
        $(document).on('click', '.btn-edit-modal', (e) => {
            e.preventDefault();
            const url = $(e.currentTarget).attr('href');
            $('#editModal').modal('show');
            $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
            $.ajax({
                url: url,
                success: (response) => { 
                    $('#editModalBody').html(response);
                    if (window.initPlatingEdit) window.initPlatingEdit();
                },
                error: (xhr) => { 
                    let message = 'Gagal memuat data.';
                    if (xhr.status === 403) message = 'Akses ditolak.';
                    $('#editModalBody').html('<div class="alert alert-danger">' + message + '</div>'); 
                }
            });
        });

        $(document).on('click', '.btn-status-modal', (e) => {
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

    initAjaxForms() {
        $(document).on('submit', '.ajax-form', function (e) {
            const $form = $(this);
            e.preventDefault();

            const $submitBtn = $form.find('button[type="submit"]');
            const $modalErrors = $form.find('#modal-errors');
            const originalBtnHtml = $submitBtn.html();

            $modalErrors.hide().empty();
            $form.find('.is-invalid').removeClass('is-invalid');
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method'),
                data: $form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        $modalErrors.html('<div class="alert alert-danger">' + (response.message || 'Terjadi kesalahan saat menyimpan data.') + '</div>').fadeIn();
                        $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    }
                },
                error: function (xhr) {
                    $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml = '<div class="alert alert-danger"><ul class="mb-0 small">';
                        $.each(errors, function (field, messages) {
                            errorHtml += '<li>' + messages[0] + '</li>';
                            $form.find('[name="' + field + '"]').addClass('is-invalid');
                        });
                        errorHtml += '</ul></div>';
                        $modalErrors.html(errorHtml).fadeIn();
                    } else {
                        const message = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        $modalErrors.html('<div class="alert alert-danger">' + message + '</div>').fadeIn();
                    }
                }
            });
        });
    }

    initQRDetail() {
        $(document).on('click', '.btn-qr-detail', function() {
            const data = $(this).data();
            $('#modal-qr-raw').text(data.qr || '-');
            $('#modal-qr-part').text(data.part || '-');
            $('#modal-qr-supplier').text(data.supplier || '-');
            $('#modal-qr-qty').text(data.qty || '-');
            $('#modal-qr-unique').text(data.unique || '-');
            $('#modal-qr-sap').text(data.sap || '-');
            $('#qrModal').modal('show');
        });
    }
}

class PlatingCreate {
    constructor(config) {
        this.config = config;
        this.timer = { running: false, seconds: 0, interval: null };
        this.pdf = {
            standardDoc: null, standardPage: 1, standardFileIdx: 0, standardFiles: [],
            similarDoc: null, similarPage: 1,
            modalDoc: null, modalPage: 1, modalScale: 1.5, currentModalType: ''
        };
        this.pdfCache = {};
        this.html5QrCode = null;
        this.init();
    }

    init() {
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;
        }
        this.lockInputs(true);
        this.initPdfEvents();
        this.initTimer();
        this.initItemSelection();
        this.initSapSelection();
        this.initDefectManagement();
        this.initJudgmentLogic();
        this.initQRScanner();
        this.initFormSubmit();

        // Inisialisasi awal untuk logic kalkulasi & judgment
        this.updateJudgment();
    }

    lockInputs(lock) {
        const inputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
        inputs.prop('disabled', lock);
        if (lock) {
            $('#checksheetForm').addClass('inputs-locked');
        } else {
            $('#checksheetForm').removeClass('inputs-locked');
            $('input[name="total_ok"]').prop('readonly', true);
        }
    }

    initTimer() {
        const updateDisplay = () => {
            const h = Math.floor(this.timer.seconds / 3600);
            const m = Math.floor((this.timer.seconds % 3600) / 60);
            const s = this.timer.seconds % 60;
            const text = [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
            $('#timerDisplay').text(text);
            $('#cycleTimeInput').val(this.timer.seconds);
        };

        $('#startTimerBtn').click(() => {
            if (!this.timer.running) {
                this.timer.running = true;
                $('#startTimerBtn').removeClass('btn-success').addClass('btn-secondary').prop('disabled', true).html('<i class="fas fa-clock"></i> Running...');
                $('#saveBtn').prop('disabled', false);
                this.lockInputs(false);
                this.timer.interval = setInterval(() => { this.timer.seconds++; updateDisplay(); }, 1000);
                $('#itemSelect').focus();
            }
        });
    }

    initQRScanner() {
        $('#btnScanQR').on('click', () => {
            $('#qrScannerModal').modal('show');
        });

        $('#qrScannerModal').on('shown.bs.modal', () => {
            $('#qr-reader-results').addClass('d-none');
            
            // Bersihkan instance lama jika ada
            if (this.html5QrCode) {
                try {
                    this.html5QrCode.clear();
                } catch (e) {
                    console.error("Gagal membersihkan scanner:", e);
                }
            }
            
            this.html5QrCode = new Html5Qrcode("qr-reader");

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            this.html5QrCode.start(
                { facingMode: "environment" }, 
                config, 
                (decodedText) => this.handleQRScanned(decodedText)
            ).catch(err => {
                console.error("Gagal menjalankan kamera:", err);
                $('#qr-reader').html(`<div class="alert alert-warning m-3">
                    <b>Kamera tidak dapat diakses:</b> ${err}<br>
                    <small>Pastikan Anda menggunakan koneksi aman (HTTPS atau localhost).</small>
                </div>`);
            });
        });

        $('#qrScannerModal').on('hidden.bs.modal', () => {
            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().catch(err => console.error(err));
            }
        });
    }

    handleQRScanned(decodedText) {
        if (this.html5QrCode) {
            this.html5QrCode.stop().then(() => {
                $('#qrScannerModal').modal('hide');
                this.parseAndFillQR(decodedText);
            }).catch(err => {
                console.error(err);
                $('#qrScannerModal').modal('hide');
                this.parseAndFillQR(decodedText);
            });
        }
    }

    parseAndFillQR(decodedText) {
        $('#qrcodeInput').val(decodedText);
        const parts = decodedText.split('|');
        if (parts.length >= 5) {
            const partCode = parts[0].trim();
            const supplierId = parts[1].trim();
            const quantity = parts[2].trim();
            const uniqueCode = parts[3].trim();
            const sapCode = parts[4].trim();

            $('#sapCodeInput').val(sapCode);
            $('#partCodeInput').val(partCode);
            $('#supplierIdInput').val(supplierId);
            $('#quantityInput').val(quantity);
            $('#uniqueCodeInput').val(uniqueCode);
            $('#sapCodeInputHidden').val(sapCode);

            // AJAX Lookup
            if (this.config.itemSearchUrl) {
                $.ajax({
                    url: this.config.itemSearchUrl,
                    method: 'GET',
                    data: { part_number: partCode, sap_code: sapCode },
                    success: (response) => {
                        if (response.success && response.item) {
                            $('#itemSelect').val(response.item.id).trigger('change');
                            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                            Toast.fire({ icon: 'success', title: 'Item otomatis terpilih: ' + response.item.name });
                        } else {
                            $('#sapCodeInput').trigger('input');
                        }
                    },
                    error: () => {
                        $('#sapCodeInput').trigger('input');
                    }
                });
            } else {
                $('#sapCodeInput').trigger('input');
            }

            $('#totalQty').val(quantity).trigger('input');
            
            if (!this.config.itemSearchUrl) {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                Toast.fire({ icon: 'success', title: 'Data QR dimuat: ' + uniqueCode });
            }
        } else {
            Swal.fire('Format QR Salah', 'Data QR tidak sesuai standar (' + decodedText + ')', 'warning');
        }
    }

    initItemSelection() {
        $('#itemSelect').change(() => {
            const selected = $('#itemSelect option:selected');
            const defectSelect = $('#defectSelect');
            defectSelect.html('<option value="">-- Pilih Defect --</option>');
            
            let defectList = defects;
            if (typeof defectList === 'string') {
                try { defectList = JSON.parse(defectList); } catch (e) { defectList = null; }
            }

            if (Array.isArray(defectList) && defectList.length > 0) {
                defectList.forEach(d => defectSelect.append(`<option value="${d}">${d}</option>`));
            } else {
                ['BARET', 'SILVER', 'FLOW', 'FLASH', 'KOTOR', 'DENYUT'].forEach(d => defectSelect.append(`<option value="${d}">${d}</option>`));
            }

            this.updatePdfViews(selected);
            $('#addDefectBtn').show();
        });
    }

    initSapSelection() {
        $('#sapCodeInput').on('input', (e) => {
            const sapCode = $(e.target).val().trim();
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

    initPdfEvents() {
        $('#prevStandardPage').click(() => {
            if (this.pdf.standardPage > 1) {
                this.pdf.standardPage--;
                this.renderPdfToCanvas(this.pdf.standardDoc, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', this.pdf.standardPage);
            }
        });

        $('#nextStandardPage').click(() => {
            if (this.pdf.standardDoc && this.pdf.standardPage < this.pdf.standardDoc.numPages) {
                this.pdf.standardPage++;
                this.renderPdfToCanvas(this.pdf.standardDoc, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', this.pdf.standardPage);
            }
        });

        $('#prevStandardFile').click(() => {
            if (this.pdf.standardFileIdx > 0) {
                this.pdf.standardFileIdx--;
                this.pdf.standardPage = 1;
                const url = this.config.pdfRoute.replace('ID_PLACEHOLDER', $('#itemSelect').val()).replace('INDEX_PLACEHOLDER', this.pdf.standardFileIdx);
                this.loadPdf(url, 'standard');
                $('#standardFileInfo').text((this.pdf.standardFileIdx + 1) + '/' + this.pdf.standardFiles.length);
            }
        });

        $('#nextStandardFile').click(() => {
            if (this.pdf.standardFileIdx < this.pdf.standardFiles.length - 1) {
                this.pdf.standardFileIdx++;
                this.pdf.standardPage = 1;
                const url = this.config.pdfRoute.replace('ID_PLACEHOLDER', $('#itemSelect').val()).replace('INDEX_PLACEHOLDER', this.pdf.standardFileIdx);
                this.loadPdf(url, 'standard');
                $('#standardFileInfo').text((this.pdf.standardFileIdx + 1) + '/' + this.pdf.standardFiles.length);
            }
        });

        $('#prevSimilarPage').click(() => {
            if (this.pdf.similarPage > 1) {
                this.pdf.similarPage--;
                this.renderPdfToCanvas(this.pdf.similarDoc, 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', this.pdf.similarPage);
            }
        });

        $('#nextSimilarPage').click(() => {
            if (this.pdf.similarDoc && this.pdf.similarPage < this.pdf.similarDoc.numPages) {
                this.pdf.similarPage++;
                this.renderPdfToCanvas(this.pdf.similarDoc, 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', this.pdf.similarPage);
            }
        });

        $('.view-pdf-btn').click((e) => {
            this.pdf.currentModalType = $(e.currentTarget).attr('id') === 'fullStandardBtn' ? 'standard' : 'similar';
            this.pdf.modalDoc = this.pdf.currentModalType === 'standard' ? this.pdf.standardDoc : this.pdf.similarDoc;
            this.pdf.modalPage = this.pdf.currentModalType === 'standard' ? this.pdf.standardPage : this.pdf.similarPage;
            this.pdf.modalScale = 1.5;

            $('#pdfModal').modal('show');

            if (this.pdf.currentModalType === 'standard') {
                if (this.pdf.standardFiles.length > 1) {
                    $('#prevPdf, #nextPdf').show();
                    $('#pdfInfo').text('File ' + (this.pdf.standardFileIdx + 1) + ' of ' + this.pdf.standardFiles.length);
                } else {
                    $('#prevPdf, #nextPdf').hide();
                    $('#pdfInfo').text('Standard PDF');
                }
            } else {
                $('#prevPdf, #nextPdf').hide();
                $('#pdfInfo').text('Similar Part PDF');
            }

            setTimeout(() => this.renderModalPage(this.pdf.modalPage), 200);
        });

        $('#prevPage').click(() => { if (this.pdf.modalPage > 1) { this.pdf.modalPage--; this.renderModalPage(this.pdf.modalPage); } });
        $('#nextPage').click(() => { if (this.pdf.modalDoc && this.pdf.modalPage < this.pdf.modalDoc.numPages) { this.pdf.modalPage++; this.renderModalPage(this.pdf.modalPage); } });
        $('#pdfZoomIn').click(() => { this.pdf.modalScale += 0.25; this.renderModalPage(this.pdf.modalPage); });
        $('#pdfZoomOut').click(() => { if (this.pdf.modalScale > 0.5) { this.pdf.modalScale -= 0.25; this.renderModalPage(this.pdf.modalPage); } });
        $('#pdfZoomReset').click(() => { this.pdf.modalScale = 1.5; this.renderModalPage(this.pdf.modalPage); });

        $('#prevPdf').click(() => { if (this.pdf.currentModalType === 'standard' && this.pdf.standardFileIdx > 0) { $('#prevStandardFile').click(); setTimeout(() => { this.pdf.modalDoc = this.pdf.standardDoc; this.pdf.modalPage = 1; $('#pdfInfo').text('File ' + (this.pdf.standardFileIdx + 1) + ' of ' + this.pdf.standardFiles.length); this.renderModalPage(1); }, 500); } });
        $('#nextPdf').click(() => { if (this.pdf.currentModalType === 'standard' && this.pdf.standardFileIdx < this.pdf.standardFiles.length - 1) { $('#nextStandardFile').click(); setTimeout(() => { this.pdf.modalDoc = this.pdf.standardDoc; this.pdf.modalPage = 1; $('#pdfInfo').text('File ' + (this.pdf.standardFileIdx + 1) + ' of ' + this.pdf.standardFiles.length); this.renderModalPage(1); }, 500); } });
    }

    updatePdfViews(selected) {
        let files = selected.data('files');
        this.pdf.standardFiles = [];
        try { this.pdf.standardFiles = typeof files === 'string' ? JSON.parse(files) : (files || []); } catch (e) { }

        const standardUrl = selected.data('standard');
        const similarUrl = selected.data('similar');

        if (standardUrl && this.pdf.standardFiles.length > 0) {
            this.pdf.standardFileIdx = 0;
            this.pdf.standardPage = 1;
            this.loadPdf(standardUrl, 'standard');
            $('.standard-nav-controls').show();
            if (this.pdf.standardFiles.length > 1) {
                $('.standard-nav-controls .file-nav').show();
                $('#standardFileInfo').text('1/' + this.pdf.standardFiles.length);
            } else {
                $('.standard-nav-controls .file-nav').hide();
            }
        } else {
            $('#standardPdfCanvas').hide();
            $('#standardPdfPlaceholder').show().find('p').text('Standard PDF tidak tersedia');
            $('.standard-nav-controls').hide();
            $('#fullStandardBtn').hide();
        }

        if (similarUrl) {
            this.pdf.similarPage = 1;
            this.loadPdf(similarUrl, 'similar');
            $('.similar-nav-controls').show();
            $('#similarStatusText').text('');
        } else {
            $('#similarPdfCanvas').hide();
            $('#similarPdfPlaceholder').show().find('p').text('Similar Part tidak tersedia');
            $('.similar-nav-controls').hide();
            $('#fullSimilarBtn').hide();
        }
    }

    loadPdf(url, type) {
        if (this.pdfCache[url]) {
            if (type === 'standard') this.pdf.standardDoc = this.pdfCache[url];
            else this.pdf.similarDoc = this.pdfCache[url];
            this.renderPdfToCanvas(this.pdfCache[url], type === 'standard' ? 'standardPdfCanvas' : 'similarPdfCanvas', type === 'standard' ? 'standardPdfPlaceholder' : 'similarPdfPlaceholder', type === 'standard' ? 'standardPdfLoading' : 'similarPdfLoading', 1);
        } else {
            pdfjsLib.getDocument(url).promise.then(pdf => {
                this.pdfCache[url] = pdf;
                if (type === 'standard') this.pdf.standardDoc = pdf;
                else this.pdf.similarDoc = pdf;
                this.renderPdfToCanvas(pdf, type === 'standard' ? 'standardPdfCanvas' : 'similarPdfCanvas', type === 'standard' ? 'standardPdfPlaceholder' : 'similarPdfPlaceholder', type === 'standard' ? 'standardPdfLoading' : 'similarPdfLoading', 1);
            }).catch(err => {
                const placeholder = type === 'standard' ? 'standardPdfPlaceholder' : 'similarPdfPlaceholder';
                $(`#${placeholder}`).show().find('p').text('Gagal memuat PDF');
            });
        }
    }

    renderPdfToCanvas(pdf, canvasId, placeholderId, loadingId, pageNum) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !pdf) return;
        const ctx = canvas.getContext('2d');
        const $placeholder = $('#' + placeholderId);
        const $loading = $('#' + loadingId);
        const $canvas = $(canvas);

        $placeholder.hide();
        $canvas.hide();
        $loading.show();

        pdf.getPage(pageNum).then(page => {
            const containerWidth = $canvas.parent().width() || 500;
            const viewport = page.getViewport({ scale: 1.0 });
            const scale = (containerWidth - 40) / viewport.width;
            const scaledViewport = page.getViewport({ scale: scale });

            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            $canvas.css('width', '100%');

            page.render({ canvasContext: ctx, viewport: scaledViewport }).promise.then(() => {
                $loading.hide();
                $canvas.show();
                if (canvasId === 'standardPdfCanvas') {
                    $('#standardPageInfo').text('P ' + pageNum + '/' + pdf.numPages);
                    $('#fullStandardBtn').show();
                } else if (canvasId === 'similarPdfCanvas') {
                    $('#similarPageInfo').text('P ' + pageNum + '/' + pdf.numPages);
                    $('#fullSimilarBtn').show();
                }
            });
        });
    }

    renderModalPage(num) {
        const canvas = document.getElementById('modalPdfCanvas');
        if (!canvas || !this.pdf.modalDoc) return;
        const ctx = canvas.getContext('2d');
        this.pdf.modalDoc.getPage(num).then(page => {
            const viewport = page.getViewport({ scale: this.pdf.modalScale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
                $('#pageInfo').text('Page ' + num + ' of ' + this.pdf.modalDoc.numPages);
            });
        });
    }

    initDefectManagement() {
        $('#addDefectBtn').click(() => {
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

        $(document).on('input', '.defect-qty', () => this.calculateTotalNG());
    }

    calculateTotalNG() {
        let total = 0;
        $('.defect-qty').each(function () { total += parseInt($(this).val()) || 0; });
        $('#totalNG').val(total).trigger('input');
    }

    initJudgmentLogic() {
        $('#totalQty, #totalNG').on('input', () => this.updateJudgment());
        
        $('#judgmentSelect').change((e) => {
            const val = $(e.target).val();
            const badge = $('#judgmentBadge');
            
            if (val === 'OK') {
                badge.text('OK').removeClass('d-none text-danger border-danger').addClass('text-success border-success').css('background', '#f0fdf4');
            } else if (val === 'NG') {
                badge.text('NG').removeClass('d-none text-success border-success').addClass('text-danger border-danger').css('background', '#fef2f2');
                $('#nextProsesContainer').show();
            } else {
                badge.addClass('d-none').text('-');
                $('#nextProsesContainer').hide();
            }
            
            if (val !== 'NG' && parseInt($('input[name="total_ng"]').val()) === 0) {
                $('#nextProsesContainer').hide();
            }
        });
    }

    getSampleSize(lotSize) {
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
    }

    getAqlLimits(sampleSize) {
        if (sampleSize >= 1250) return { acc: 14, rej: 15 };
        if (sampleSize >= 800) return { acc: 10, rej: 11 };
        if (sampleSize >= 500) return { acc: 7, rej: 8 };
        if (sampleSize >= 315) return { acc: 5, rej: 6 };
        if (sampleSize >= 200) return { acc: 3, rej: 4 };
        if (sampleSize >= 125) return { acc: 2, rej: 3 };
        if (sampleSize >= 80) return { acc: 1, rej: 2 };
        if (sampleSize >= 50) return { acc: 1, rej: 2 };
        if (sampleSize >= 32) return { acc: 0, rej: 1 };
        if (sampleSize >= 20) return { acc: 0, rej: 1 };
        return { acc: 0, rej: 1 };
    }

    normalizePartNumber(pn) {
        if (!pn) return '';
        return pn.toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
            .replace(/\s+/g, '')
            .toUpperCase();
    }

    updateJudgment() {
        const totalQty = parseInt($('#totalQty').val()) || 0;
        const ng = parseInt($('#totalNG').val()) || 0;
        const isPlatingChecked = $('#checkOK').is(':checked');

        // Catatan: Perencanaan menyarankan AQL 0.65.
        // Jika pemeriksaan 100% digunakan, sampleSize = totalQty.
        // Jika AQL 0.65 digunakan, sampleSize dicari.
        // Kode asli menggunakan pemeriksaan 100% (totalQty - ng).
        
        const sampleSize = this.getSampleSize(totalQty);
        const limits = this.getAqlLimits(sampleSize);

        // Perbarui Info UI jika diperlukan (menambahkan placeholder jika tidak ada)
        if (!$('#aql_info').length) {
            $('.bg-primary.text-white tr:first').after('<tr id="aql_info" class="bg-light text-dark small"><td colspan="12">AQL Standard: 0.65 | Sample: <span id="sample_val">-</span> | Acc: <span id="acc_val">-</span> | Rej: <span id="rej_val">-</span></td></tr>');
        }
        $('#sample_val').text(sampleSize);
        $('#acc_val').text(limits.acc);
        $('#rej_val').text(limits.rej);
        $('#aql_info').toggle(totalQty > 0);

        const ok = Math.max(0, totalQty - ng);
        $('input[name="total_ok"]').val(ok);

        if (totalQty > 0) {
            if (ng >= limits.rej) {
                $('#judgmentSelect').val('NG').trigger('change');
            } else {
                $('#judgmentSelect').val('OK').trigger('change');
            }
        } else {
            $('#judgmentSelect').val('');
        }
    }

    initFormSubmit() {
        $(document).on('submit', '#checksheetForm', (e) => {
            const $form = $(e.target);
            e.preventDefault();

            console.log('Submitting Plating Form...');
            const judgment = $('#judgmentSelect').val();
            const nextProses = $('#nextProses').val();

            if (judgment === 'NG' && !nextProses) {
                Swal.fire({ icon: 'warning', title: 'Next Proses Wajib Dipilih', text: 'Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!' });
                $('#nextProses').focus();
                return false;
            }

            if (this.timer.running) {
                clearInterval(this.timer.interval);
                this.timer.running = false;
            }

            // Bersihkan defect yang diinput tapi qty = 0 / kosong
            $('.defect-row').each(function() {
                const typeInput = $(this).find('input[name="defect_types[]"], select[name="defect_types[]"]');
                const qtyInput = $(this).find('input[name="defect_quantities[]"]');
                const type = typeInput.val();
                const qty = parseInt(qtyInput.val()) || 0;
                
                if (type && qty === 0) {
                    typeInput.val('');
                    qtyInput.val('');
                }
            });

            const saveBtn = $('#saveBtn');
            const originalHtml = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: $(e.target).attr('action'),
                method: 'POST',
                data: new FormData(e.target),
                processData: false,
                contentType: false,
                success: (res) => {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data Berhasil Disimpan', showCancelButton: true, confirmButtonText: 'Lihat Data' }).then((result) => {
                            if (result.isConfirmed) window.location.href = res.index_url;
                            else this.resetState();
                        });
                    }
                },
                error: (xhr) => {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal menyimpan data.' });
                    saveBtn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    }

    resetState() {
        if (this.timer.interval) clearInterval(this.timer.interval);
        this.timer = { running: false, seconds: 0, interval: null };
        $('#timerDisplay').text('00:00:00');
        $('#startTimerBtn').removeClass('btn-secondary').addClass('btn-success').prop('disabled', false).html('<i class="fas fa-play"></i> Start');
        
        this.lockInputs(true);
        $('#checksheetForm')[0].reset();
        $('#defectContainer').find('.defect-row').not(':first').remove();
        $('#imageContainer').html('<div style="width:100px; height:100px; background-color:#f8f9fa; border:1px solid #dee2e6; display:flex; align-items:center; justify-content:center; margin:0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
        $('#itemSelect').val('').trigger('change');
        $('#aql_info').hide();
        $('#nextProsesContainer').hide();
        $('#judgmentBadge').addClass('d-none').text('-');
        $('#judgmentSelect').val('');

        $('#standardPdfCanvas, #similarPdfCanvas').hide();
        $('#standardPdfPlaceholder').show().find('p').text('Pilih Item untuk menampilkan Standard PDF');
        $('#similarPdfPlaceholder').show().find('p').text('Pilih Item untuk menampilkan Similar Part');
        $('#fullStandardBtn, #fullSimilarBtn').hide();
        $('.standard-nav-controls, .similar-nav-controls').hide();
        
        this.pdf = { ...this.pdf, standardDoc: null, standardPage: 1, standardFileIdx: 0, standardFiles: [], similarDoc: null, similarPage: 1 };

        // Reset QR fields
        $('#qrcodeInput, #partCodeInput, #supplierIdInput, #quantityInput, #uniqueCodeInput, #sapCodeInputHidden').val('');
        $('#sapCodeInput').removeClass('is-valid is-invalid').val('');
    }
}

class PlatingEdit {
    constructor() {
        this.init();
    }

    init() {
        if ($('.select2').length) {
            $('.select2').select2({ theme: 'bootstrap4', dropdownParent: $('#editModal') });
        }
        $(document).on('input', '#editTotalQty', (e) => {
            $('#editSamplingQty').val($(e.target).val()).trigger('input');
        });

        $(document).on('input', '#editTotalNG, #editSamplingQty', () => {
            const total = parseInt($('#editSamplingQty').val()) || 0;
            const ng = parseInt($('#editTotalNG').val()) || 0;
            const ok = Math.max(0, total - ng);
            $('input[name="total_ok"]').val(ok);
            $('#editJudgment').val(ng > 0 ? 'NG' : 'OK').trigger('change');
        });

        $(document).on('change', '#editJudgment', (e) => {
            if ($(e.target).val() === 'NG') $('#editNextProses').slideDown();
            else $('#editNextProses').slideUp();
        });
    }
}

window.initPlatingIndex = (config) => new PlatingIndex(config);
window.initPlatingCreate = (config) => new PlatingCreate(config);
window.initPlatingEdit = () => new PlatingEdit();
