class SubAssyIndex {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initCharacterCounter();
            this.initLiveSearch();
            this.initModalHandlers();
        });
    }

    initCharacterCounter() {
        const textareas = document.querySelectorAll('textarea[name="rejection_remarks"]');
        textareas.forEach(textarea => {
            const idParts = textarea.id.replace('rejection_remarks', '');
            const countSpan = document.getElementById('charCount' + idParts);
            if (countSpan) {
                textarea.addEventListener('input', function () {
                    countSpan.textContent = this.value.length;
                });
            }
        });
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById('liveSearch');
        if (liveSearchInput) {
            let searchTimeout;
            liveSearchInput.addEventListener('keyup', function () {
                const searchTerm = this.value.trim();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    const params = new URLSearchParams(window.location.search);
                    if (searchTerm) params.set('search', searchTerm);
                    else params.delete('search');
                    params.delete('page');
                    window.location.href = window.location.pathname + '?' + params.toString();
                }, 500);
            });
        }
    }

    initModalHandlers() {
        $('.btn-edit-modal').on('click', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#editModal').modal('show');
            $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                url: url,
                success: function (response) {
                    $('#editModalBody').html(response);
                },
                error: function (xhr) {
                    let message = 'Gagal memuat data checksheet.';
                    if (xhr.status === 404) message = 'Data checksheet tidak ditemukan.';
                    else if (xhr.status === 403) message = 'Anda tidak memiliki akses untuk mengedit checksheet ini.';
                    else if (xhr.status === 500) message = 'Terjadi kesalahan pada server.';
                    $('#editModalBody').html('<div class="alert alert-danger">' + message + '</div>');
                }
            });
        });

        $('.btn-status-modal').on('click', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#statusModal').modal('show');
            $('#statusModalBody').html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                url: url,
                success: function (response) {
                    $('#statusModalBody').html(response);
                },
                error: function (xhr) {
                    let message = 'Gagal memuat data status approval.';
                    if (xhr.status === 404) message = 'Data tidak ditemukan.';
                    else if (xhr.status === 403) message = 'Anda tidak memiliki akses untuk mengubah status approval ini.';
                    $('#statusModalBody').html('<div class="alert alert-danger">' + message + '</div>');
                }
            });
        });
    }
}

class SubAssyCreate {
    constructor(config) {
        this.config = config;
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pageRendering = false;
        this.pageNumPending = null;
        this.scale = 1.0;
        this.timerInterval = null;
        this.totalSeconds = 0;
        this.timerRunning = false;
        this.isFullcheckMode = false;
        this.currentPdfIndex = 0;
        this.totalPdfFiles = 0;
        this.currentItemId = null;

        this.init();
    }

    init() {
        $(document).ready(() => {
            this.setupUI();
            this.initEventListeners();
            this.initPdfJS();
        });
    }

    setupUI() {
        this.formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
        this.formInputs.prop('disabled', true);
        $('#checksheetForm').addClass('inputs-locked');
        if (!$('#lockStyle').length) {
            $('<style id="lockStyle">#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');
        }
    }

    initPdfJS() {
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;
        }
    }

    initEventListeners() {
        const self = this;

        $('#startTimerBtn').click(function () {
            if (!self.timerRunning) {
                self.timerRunning = true;
                $(this).removeClass('btn-success').addClass('btn-secondary').attr('disabled', true).html('<i class="fas fa-clock"></i> Running...');
                $('#saveBtn').prop('disabled', false);

                self.formInputs.prop('disabled', false);
                $('#checksheetForm').removeClass('inputs-locked');

                self.timerInterval = setInterval(() => {
                    self.totalSeconds++;
                    self.updateTimerDisplay();
                }, 1000);
            }
        });

        $('#itemSelect').change((e) => this.handleItemChange(e));

        $('#sapCodeInput').on('input', function () {
            const sapCode = $(this).val().trim().toLowerCase();
            if (sapCode.length >= 1) {
                const matchedOption = $('#itemSelect option').filter(function () {
                    const itemSapCode = String($(this).data('sap-code')).toLowerCase();
                    return itemSapCode === sapCode;
                });

                if (matchedOption.length > 0) {
                    $('#itemSelect').val(matchedOption.val()).trigger('change');
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('input[name="check_type_option"]').on('change', function () {
            self.isFullcheckMode = ($(this).val() === 'fullcheck');
            $('#checkTypeInput').val($(this).val());

            const lotSize = parseInt($('input[name="total_qty"]').val()) || 0;
            if (lotSize > 0) {
                const sampleSize = self.isFullcheckMode ? lotSize : self.getSampleSize(lotSize);
                $('input[name="sampling_qty"]').val(sampleSize).trigger('input');
            }
        });

        $('input[name="total_qty"]').on('input', function () {
            const lotSize = parseInt($(this).val()) || 0;
            const sampleSize = self.isFullcheckMode ? lotSize : self.getSampleSize(lotSize);
            $('input[name="sampling_qty"]').val(sampleSize).trigger('input');
        });

        $('input[name="total_ng"], input[name="sampling_qty"]').on('input', () => this.updateJudgment());

        $('#checkOK').change(function () {
            if ($(this).is(':checked')) {
                $('#judgmentSelect').val('OK');
            }
        });

        $('#addDefectBtn').click(() => this.handleAddDefect());
        $(document).on('input', '.defect-qty', () => this.calculateTotalNG());
        $(document).on('click', '.remove-defect-btn', (e) => this.handleRemoveDefect(e));

        $('#checksheetForm').on('submit', (e) => this.handleFormSubmit(e));

        $('#prevPage').click(() => this.handlePrevPage());
        $('#nextPage').click(() => this.handleNextPage());
        $('#pdfZoomIn').click(() => this.handlePdfZoom(0.25));
        $('#pdfZoomOut').click(() => this.handlePdfZoom(-0.25));
        $('#pdfZoomReset').click(() => { this.scale = 1.0; this.queueRenderPage(this.pageNum); });
        $('#prevPdf').click(() => this.handlePrevPdf());
        $('#nextPdf').click(() => this.handleNextPdf());
        $(document).on('click', '.view-pdf-btn', (e) => this.handleOpenPdf(e));

        $('#zoomIn').click(() => this.handleImageZoom(0.25));
        $('#zoomOut').click(() => this.handleImageZoom(-0.25));
        $('#zoomReset').click(() => { this.currentZoom = 1; this.updateImageZoom(); });
        $('#imageModal').on('show.bs.modal', (e) => this.handleImageModal(e));

        $('button[type="reset"]').click(() => this.resetState());
    }

    handleItemChange(e) {
        const option = $(e.target).find('option:selected');
        const imageUrl = option.data('image');
        const fileUrl = option.data('file');
        const files = option.data('files');
        const name = option.data('name');
        const description = option.data('description');
        const defectsData = option.data('defects');
        const itemId = option.val();

        const container = $('#imageContainer');
        let html = '';

        if (files && files.length > 0) {
            html += `<button type="button" class="btn btn-danger btn-sm view-pdf-btn mb-1" data-id="${itemId}" data-count="${files.length}"><i class="fas fa-file-pdf"></i> PDF (${files.length})</button>`;
        } else if (fileUrl) {
            html += `<button type="button" class="btn btn-danger btn-sm view-pdf-btn mb-1" data-id="${itemId}" data-count="1"><i class="fas fa-file-pdf"></i> PDF</button>`;
        }

        if (imageUrl) {
            html += `<img src="${imageUrl}" style="max-width: 100px; max-height: 80px; border: 1px solid #dee2e6; cursor: pointer; display:block; margin: 0 auto;" class="img-thumbnail" data-toggle="modal" data-target="#imageModal" data-image="${imageUrl}" data-title="${name}" data-description="${description}">`;
        }

        if (!html) {
            html = '<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>';
        }

        if ((files && files.length > 0 || fileUrl) && imageUrl) {
            container.html(`<div class="d-flex flex-column align-items-center">${html}</div>`);
        } else {
            container.html(html);
        }

        this.updateDefectDropdown(defectsData);
        this.calculateTotalNG();
    }

    updateDefectDropdown(defects) {
        let defectsData = defects;
        if (typeof defectsData === 'string') {
            try { defectsData = JSON.parse(defectsData); } catch (e) { defectsData = []; }
        }

        $('#defectContainer').html('<div class="input-group mb-2 defect-row"><select class="form-control defect-select" name="defect_types[]" id="defectSelect"><option value="">-- Pilih Defect --</option></select><input type="number" class="form-control defect-qty" name="defect_quantities[]" placeholder="Qty" min="1" style="max-width: 80px;"></div>');
        const select = $('#defectSelect');

        if (Array.isArray(defectsData) && defectsData.length > 0) {
            $.each(defectsData, (i, v) => select.append(`<option value="${v}">${v}</option>`));
        } else {
            const defaults = [
                { v: 'scratch', t: 'BARET' }, { v: 'silver', t: 'SILVER' }, { v: 'flow', t: 'FLOW' },
                { v: 'flash', t: 'FLASH' }, { v: 'shoot_mold', t: 'SHOOT MOLD' },
                { v: 'bending', t: 'BENDING' }, { v: 'sinkmark', t: 'SINKMARK' }, { v: 'dimension', t: 'Dimensi' }
            ];
            $.each(defaults, (i, d) => select.append(`<option value="${d.v}">${d.t}</option>`));
        }
    }

    handleAddDefect() {
        if ($('.defect-row').length < 4) {
            const first = $('#defectSelect').html();
            const newRow = $(`<div class="input-group mb-2 defect-row"><select class="form-control defect-select" style="min-width: 180px;" name="defect_types[]">${first}</select><input type="number" class="form-control defect-qty" style="min-width: 100px;" name="defect_quantities[]" placeholder="Qty" min="1"><div class="input-group-append"><button class="btn btn-danger btn-sm remove-defect-btn" type="button"><i class="fas fa-minus"></i></button></div></div>`);
            $('#defectContainer').append(newRow);
        }
        if ($('.defect-row').length >= 4) $('#addDefectBtn').hide();
    }

    handleRemoveDefect(e) {
        $(e.target).closest('.defect-row').remove();
        this.calculateTotalNG();
        if ($('.defect-row').length < 4) $('#addDefectBtn').show();
    }

    calculateTotalNG() {
        let total = 0;
        $('.defect-qty').each(function () {
            total += parseInt($(this).val()) || 0;
        });
        $('input[name="total_ng"]').val(total).trigger('input');
    }

    updateJudgment() {
        const sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
        const ng = parseInt($('input[name="total_ng"]').val()) || 0;

        $('input[name="total_ok"]').val(Math.max(0, sampling - ng));

        const limits = this.getAqlLimits(sampling);
        $('#acc_val').text(limits.acc);
        $('#rej_val').text(limits.rej);
        $('#aql_info').show();

        const select = $('#judgmentSelect');
        if (ng > 0 || sampling > 0) {
            select.val(ng <= limits.acc ? 'OK' : 'NG');
        } else {
            select.val('');
        }

        if (select.val() === 'NG' || ng > 0) $('#nextProsesContainer').show();
        else $('#nextProsesContainer').hide();
    }

    getSampleSize(lot) {
        if (lot >= 500001) return 1250;
        if (lot >= 150001) return 800;
        if (lot >= 35001) return 500;
        if (lot >= 10001) return 315;
        if (lot >= 3201) return 200;
        if (lot >= 1201) return 125;
        if (lot >= 501) return 80;
        if (lot >= 281) return 50;
        if (lot >= 151) return 32;
        if (lot >= 91) return 20;
        if (lot >= 51) return 13;
        if (lot >= 26) return 8;
        if (lot >= 16) return 5;
        if (lot >= 9) return 3;
        if (lot >= 2) return 2;
        return 0;
    }

    getAqlLimits(sample) {
        if (sample >= 1250) return { acc: 14, rej: 15 };
        if (sample >= 800) return { acc: 10, rej: 11 };
        if (sample >= 500) return { acc: 7, rej: 8 };
        if (sample >= 315) return { acc: 5, rej: 6 };
        if (sample >= 200) return { acc: 3, rej: 4 };
        if (sample >= 125) return { acc: 2, rej: 3 };
        if (sample >= 80) return { acc: 1, rej: 2 };
        if (sample >= 20) return { acc: 0, rej: 1 };
        return { acc: 0, rej: 1 };
    }

    normalizePartNumber(pn) {
        if (!pn) return '';
        return pn.toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
            .replace(/\s+/g, '')
            .toUpperCase();
    }

    updateTimerDisplay() {
        const h = Math.floor(this.totalSeconds / 3600);
        const m = Math.floor((this.totalSeconds % 3600) / 60);
        const s = this.totalSeconds % 60;
        const pad = (n) => n < 10 ? '0' + n : n;
        $('#timerDisplay').text(`${pad(h)}:${pad(m)}:${pad(s)}`);
        $('#cycleTimeInput').val(this.totalSeconds);
    }

    handleFormSubmit(e) {
        e.preventDefault();
        const judgment = $('#judgmentSelect').val();
        const nextProses = $('#nextProses').val();

        if (judgment === 'NG' && !nextProses) {
            Swal.fire({ icon: 'warning', title: 'Next Proses Wajib Dipilih', text: 'Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!', confirmButtonColor: '#3085d6' });
            $('#nextProses').focus();
            return false;
        }

        if (this.timerRunning) {
            clearInterval(this.timerInterval);
            this.timerRunning = false;
        }

        const saveBtn = $('#saveBtn');
        const originalHtml = saveBtn.html();
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: $('#checksheetForm').attr('action'),
            method: 'POST',
            data: new FormData($('#checksheetForm')[0]),
            processData: false,
            contentType: false,
            success: (res) => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil', text: 'Data Berhasil Disimpan',
                        showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Lihat Data', cancelButtonText: 'Tutup'
                    }).then((result) => {
                        if (result.isConfirmed) window.location.href = res.index_url;
                        else {
                            $('#checksheetForm')[0].reset();
                            this.resetState();
                        }
                    });
                }
            },
            error: (xhr) => {
                const msg = xhr.responseJSON?.message || 'Gagal menyimpan data.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                saveBtn.prop('disabled', false).html(originalHtml);
            }
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
        $('.defect-row').not(':first').remove();
        $('#imageContainer').html('<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
        $('#itemSelect').val('').trigger('change');
    }

    handleOpenPdf(e) {
        this.currentItemId = $(e.currentTarget).data('id');
        this.totalPdfFiles = $(e.currentTarget).data('count');
        this.currentPdfIndex = 0;
        $('#pdfModal').modal('show');
        this.loadPdf(this.currentItemId, this.currentPdfIndex);
    }

    loadPdf(id, index) {
        const url = this.config.pdfUrlPattern.replace('ID_PLACEHOLDER', id).replace('INDEX_PLACEHOLDER', index);
        this.pdfDoc = null;
        this.pageNum = 1;
        const canvas = document.getElementById('the-canvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('pageInfo').textContent = 'Loading...';
        document.getElementById('pdfInfo').textContent = `File ${index + 1} of ${this.totalPdfFiles}`;

        pdfjsLib.getDocument(url).promise.then((pdf) => {
            this.pdfDoc = pdf;
            document.getElementById('pageInfo').textContent = `Page 1 of ${this.pdfDoc.numPages}`;
            this.renderPage(1);
        }, (err) => {
            console.error(err);
            alert('Error loading PDF: ' + err.message);
        });
    }

    renderPage(num) {
        this.pageRendering = true;
        this.pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: this.scale });
            const canvas = document.getElementById('the-canvas');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            const ctx = canvas.getContext('2d');
            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
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
        if (this.pageRendering) this.pageNumPending = num;
        else this.renderPage(num);
    }

    handlePrevPage() { if (this.pageNum > 1) { this.pageNum--; this.queueRenderPage(this.pageNum); } }
    handleNextPage() { if (this.pdfDoc && this.pageNum < this.pdfDoc.numPages) { this.pageNum++; this.queueRenderPage(this.pageNum); } }
    handlePdfZoom(v) { this.scale += v; this.queueRenderPage(this.pageNum); }
    handlePrevPdf() { if (this.currentPdfIndex > 0) { this.currentPdfIndex--; this.loadPdf(this.currentItemId, this.currentPdfIndex); } }
    handleNextPdf() { if (this.currentPdfIndex < this.totalPdfFiles - 1) { this.currentPdfIndex++; this.loadPdf(this.currentItemId, this.currentPdfIndex); } }

    handleImageZoom(v) { this.currentZoom = (this.currentZoom || 1) + v; this.updateImageZoom(); }
    updateImageZoom() {
        const zoom = this.currentZoom || 1;
        const origin = zoom > 1 ? 'top center' : 'center center';
        $('#modalImage').css({ transform: `scale(${zoom})`, 'transform-origin': origin });
    }
    handleImageModal(e) {
        const btn = $(e.relatedTarget);
        $('#modalImage').attr('src', btn.data('image'));
        $('#modalTitle').text(btn.data('title'));
        $('#modalDescription').text(btn.data('description'));
        this.currentZoom = 1;
        this.updateImageZoom();
    }
}

window.initSubAssyIndex = () => new SubAssyIndex();
window.initSubAssyCreate = (config) => new SubAssyCreate(config);
