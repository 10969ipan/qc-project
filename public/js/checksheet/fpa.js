class FpaIndex {
    constructor(config) {
        this.config = config;
        this.init();
    }

    init() {
        this.initCharCounters();
        this.initLiveSearch();
        this.initModalHandlers();
        this.initAjaxForms();
    }

    initCharCounters() {
        if (!this.config.checksheets) return;
        
        this.config.checksheets.forEach(id => {
            ['kashift', 'supervisor', 'asst_manager', 'manager'].forEach(type => {
                const textarea = document.getElementById(`rejection_remarks${id}${type}`);
                const charCount = document.getElementById(`charCount${id}${type}`);
                if (textarea && charCount) {
                    textarea.addEventListener('input', function() {
                        charCount.textContent = this.value.length;
                    });
                }
            });
        });
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById('liveSearch');
        if (liveSearchInput) {
            let searchTimeout;
            liveSearchInput.addEventListener('keyup', () => {
                const searchTerm = liveSearchInput.value.trim();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    const plant = this.config.plant;

                    const params = new URLSearchParams();
                    if (searchTerm) params.append('search', searchTerm);
                    if (startDate) params.append('start_date', startDate);
                    if (endDate) params.append('end_date', endDate);
                    if (plant) params.append('plant', plant);

                    window.location.href = `${this.config.routes.index}?${params.toString()}`;
                }, 500);
            });
        }
    }

    initModalHandlers() {
        $('.btn-edit-modal').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#editModal').modal('show');
            $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                url: url,
                success: (response) => $('#editModalBody').html(response),
                error: (xhr) => {
                    let message = 'Gagal memuat data checksheet.';
                    if (xhr.status === 404) message = 'Data checksheet tidak ditemukan.';
                    else if (xhr.status === 403) message = 'Anda tidak memiliki akses untuk mengedit checksheet ini.';
                    else if (xhr.status === 500) message = 'Terjadi kesalahan pada server.';
                    $('#editModalBody').html(`<div class="alert alert-danger">${message}</div>`);
                }
            });
        });

        $('.btn-status-modal').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#statusModal').modal('show');
            $('#statusModalBody').html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                url: url,
                success: (response) => $('#statusModalBody').html(response),
                error: (xhr) => {
                    let message = 'Gagal memuat data status approval.';
                    if (xhr.status === 404) message = 'Data tidak ditemukan.';
                    else if (xhr.status === 403) message = 'Anda tidak memiliki akses untuk mengubah status approval ini.';
                    $('#statusModalBody').html(`<div class="alert alert-danger">${message}</div>`);
                }
            });
        });
    }

    initAjaxForms() {
        $(document).on('submit', '.ajax-form', (e) => {
            const $form = $(e.currentTarget);
            if ($form.find('input[name="_method"]').val() === 'DELETE') {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                    return false;
                }
            }

            e.preventDefault();
            const $submitBtn = $form.find('button[type="submit"]');
            const $modalErrors = $form.find('#modal-errors');
            const originalBtnHtml = $submitBtn.html();

            $modalErrors.hide().html('');
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();

            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method'),
                data: $form.serialize(),
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        window.location.href = response.redirect || window.location.href;
                    } else {
                        this.showModalError($modalErrors, response.message || 'Terjadi kesalahan saat menyimpan data.');
                        $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    }
                },
                error: (xhr) => {
                    $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                        if (errors) {
                            $.each(errors, (field, messages) => {
                                errorHtml += `<li>${messages[0]}</li>`;
                                const $input = $form.find(`[name="${field}"]`);
                                if ($input.length) {
                                    $input.addClass('is-invalid');
                                    if (!$input.next('.invalid-feedback').length) {
                                        $input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                                    }
                                }
                            });
                        } else {
                            errorHtml += `<li>${xhr.responseJSON.message || 'Validasi gagal.'}</li>`;
                        }
                        errorHtml += '</ul></div>';
                        this.showModalError($modalErrors, errorHtml);
                    } else {
                        const message = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        this.showModalError($modalErrors, `<div class="alert alert-danger">${message}</div>`);
                    }

                    const $modalBody = $form.closest('.modal-body');
                    if ($modalBody.length) {
                        $modalBody.animate({ scrollTop: 0 }, 'fast');
                    } else {
                        $('html, body').animate({ scrollTop: $form.offset().top - 100 }, 'fast');
                    }
                }
            });
        });
    }

    showModalError($container, html) {
        $container.html(html).fadeIn();
    }
}

class FpaCreate {
    constructor(config) {
        this.config = config;
        this.timer = {
            startTime: null,
            elapsed: 0,
            interval: null,
            isRunning: false
        };
        this.pdf = {
            standard: { doc: null, page: 1, scale: 1.0, rendering: false, pending: null, canvas: null, ctx: null, files: [], currentIndex: 0 },
            similar: { doc: null, page: 1, scale: 1.0, rendering: false, pending: null, canvas: null, ctx: null }
        };
        this.init();
    }

    init() {
        this.lockInputs();
        this.initTimer();
        this.initPdfHandling();
        this.initDimensionTable();
        this.initWeightHandling();
        this.initSapSelection();
        this.initAqlLogic();
        this.initDefectManagement();
        this.initFormValidation();
    }

    lockInputs() {
        const formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
        formInputs.prop('disabled', true);
        $('#checksheetForm').addClass('inputs-locked');
        this.config.isLocked = true;
    }

    unlockInputs() {
        const formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
        formInputs.prop('disabled', false);
        $('#checksheetForm').removeClass('inputs-locked');
        this.config.isLocked = false;
    }

    initTimer() {
        $('#startTimerBtn').on('click', (e) => {
            if (!this.timer.isRunning) {
                this.startTimer();
                this.unlockInputs();
                $(e.currentTarget).prop('disabled', true).removeClass('btn-success').addClass('btn-secondary').html('<i class="fas fa-clock"></i> Running...');
                $('#saveBtn').prop('disabled', false);
            }
        });
    }

    startTimer() {
        this.timer.isRunning = true;
        this.timer.startTime = Date.now() - (this.timer.elapsed * 1000);
        this.timer.interval = setInterval(() => {
            this.timer.elapsed = Math.floor((Date.now() - this.timer.startTime) / 1000);
            this.updateTimerDisplay();
        }, 1000);
    }

    updateTimerDisplay() {
        const h = Math.floor(this.timer.elapsed / 3600).toString().padStart(2, '0');
        const m = Math.floor((this.timer.elapsed % 3600) / 60).toString().padStart(2, '0');
        const s = (this.timer.elapsed % 60).toString().padStart(2, '0');
        $('#timerDisplay').text(`${h}:${m}:${s}`);
        $('#cycleTimeInput').val(this.timer.elapsed);
    }

    initPdfHandling() {
        this.pdf.standard.canvas = document.getElementById('standardPdfCanvas');
        this.pdf.standard.ctx = this.pdf.standard.canvas.getContext('2d');
        this.pdf.similar.canvas = document.getElementById('similarPdfCanvas');
        this.pdf.similar.ctx = this.pdf.similar.canvas.getContext('2d');

        $('#prevStandardPage').on('click', () => this.changePage('standard', -1));
        $('#nextStandardPage').on('click', () => this.changePage('standard', 1));
        $('#prevSimilarPage').on('click', () => this.changePage('similar', -1));
        $('#nextSimilarPage').on('click', () => this.changePage('similar', 1));

        $('#prevStandardFile').on('click', () => this.changeFile('standard', -1));
        $('#nextStandardFile').on('click', () => this.changeFile('standard', 1));

        $('#fullStandardBtn').on('click', () => this.openFullPdf('standard'));
        $('#fullSimilarBtn').on('click', () => this.openFullPdf('similar'));

        this.initFullPdfModal();
    }

    loadPdf(type, url) {
        const p = this.pdf[type];
        p.doc = null;
        p.page = 1;
        p.rendering = false;
        p.pending = null;
        
        $(`#${type}PdfPlaceholder`).hide();
        $(`#${type}PdfLoading`).removeClass('d-none').addClass('d-flex');
        $(`#${type}PdfCanvas`).addClass('d-none');
        $(`.${type}-nav-controls`).hide();
        $(`#full${type.charAt(0).toUpperCase() + type.slice(1)}Btn`).hide();

        pdfjsLib.getDocument(url).promise.then(pdf => {
            p.doc = pdf;
            $(`#${type}PdfLoading`).removeClass('d-flex').addClass('d-none');
            $(`#${type}PdfCanvas`).removeClass('d-none');
            $(`.${type}-nav-controls`).show();
            if (type === 'standard' && p.files.length > 1) $('.file-nav').show();
            $(`#full${type.charAt(0).toUpperCase() + type.slice(1)}Btn`).show();
            this.renderPdfPage(type, 1);
        }).catch(err => {
            console.error(`Error loading ${type} PDF:`, err);
            $(`#${type}PdfLoading`).removeClass('d-flex').addClass('d-none');
            $(`#${type}PdfPlaceholder`).show().find('p').text(`Error loading PDF: ${err.message}`);
        });
    }

    renderPdfPage(type, num) {
        const p = this.pdf[type];
        p.rendering = true;
        p.doc.getPage(num).then(page => {
            const containerWidth = $(`#${type}PdfContainer`).width() - 40;
            const unscaledViewport = page.getViewport({ scale: 1 });
            const dynamicScale = containerWidth / unscaledViewport.width;
            const viewport = page.getViewport({ scale: dynamicScale });

            p.canvas.height = viewport.height;
            p.canvas.width = viewport.width;

            const renderTask = page.render({ canvasContext: p.ctx, viewport: viewport });
            renderTask.promise.then(() => {
                p.rendering = false;
                if (p.pending !== null) {
                    this.renderPdfPage(type, p.pending);
                    p.pending = null;
                }
            });
            $(`#${type}PageInfo`).text(`P ${num}/${p.doc.numPages}`);
        });
    }

    changePage(type, delta) {
        const p = this.pdf[type];
        if (!p.doc) return;
        const next = p.page + delta;
        if (next < 1 || next > p.doc.numPages) return;
        p.page = next;
        if (p.rendering) p.pending = next;
        else this.renderPdfPage(type, next);
    }

    changeFile(type, delta) {
        const p = this.pdf[type];
        const next = p.currentIndex + delta;
        if (next < 0 || next >= p.files.length) return;
        p.currentIndex = next;
        $(`#${type}FileInfo`).text(`${next + 1}/${p.files.length}`);
        const url = `${this.config.routes.pdfProxy}?path=${encodeURIComponent(p.files[next])}`;
        this.loadPdf(type, url);
    }

    initFullPdfModal() {
        this.modalPdf = { doc: null, page: 1, scale: 1.5, rendering: false, pending: null, canvas: document.getElementById('the-canvas'), ctx: document.getElementById('the-canvas').getContext('2d'), files: [], currentIndex: 0, currentType: 'standard' };

        $('#prevPage').on('click', () => this.changeModalPage(-1));
        $('#nextPage').on('click', () => this.changeModalPage(1));
        $('#pdfZoomIn').on('click', () => { this.modalPdf.scale += 0.25; this.renderModalPage(this.modalPdf.page); });
        $('#pdfZoomOut').on('click', () => { if (this.modalPdf.scale > 0.5) { this.modalPdf.scale -= 0.25; this.renderModalPage(this.modalPdf.page); } });
        $('#pdfZoomReset').on('click', () => { this.modalPdf.scale = 1.5; this.renderModalPage(this.modalPdf.page); });
        
        $('#prevPdf').on('click', () => this.changeModalFile(-1));
        $('#nextPdf').on('click', () => this.changeModalFile(1));
    }

    openFullPdf(type) {
        const source = this.pdf[type];
        if (!source.doc) return;

        this.modalPdf.currentType = type;
        this.modalPdf.doc = source.doc;
        this.modalPdf.page = source.page;
        this.modalPdf.files = type === 'standard' ? source.files : [];
        this.modalPdf.currentIndex = type === 'standard' ? source.currentIndex : 0;
        this.modalPdf.scale = 1.5;

        $('#pdfModal').modal('show');
        this.updateModalFileInfo();
        this.renderModalPage(this.modalPdf.page);
    }

    renderModalPage(num) {
        const m = this.modalPdf;
        m.rendering = true;
        m.doc.getPage(num).then(page => {
            const viewport = page.getViewport({ scale: m.scale });
            m.canvas.height = viewport.height;
            m.canvas.width = viewport.width;
            const renderTask = page.render({ canvasContext: m.ctx, viewport: viewport });
            renderTask.promise.then(() => {
                m.rendering = false;
                if (m.pending !== null) {
                    this.renderModalPage(m.pending);
                    m.pending = null;
                }
            });
            $('#pageInfo').text(`Page ${num} of ${m.doc.numPages}`);
        });
    }

    changeModalPage(delta) {
        const m = this.modalPdf;
        const next = m.page + delta;
        if (next < 1 || next > m.doc.numPages) return;
        m.page = next;
        if (m.rendering) m.pending = next;
        else this.renderModalPage(next);
    }

    updateModalFileInfo() {
        if (this.modalPdf.currentType === 'similar') {
            $('#pdfInfo').text('Dimensi Part PDF');
            $('#prevPdf, #nextPdf').hide();
        } else {
            $('#pdfInfo').text(`File ${this.modalPdf.currentIndex + 1} of ${this.modalPdf.files.length}`);
            $('#prevPdf, #nextPdf').toggle(this.modalPdf.files.length > 1);
        }
    }

    changeModalFile(delta) {
        const m = this.modalPdf;
        const next = m.currentIndex + delta;
        if (next < 0 || next >= m.files.length) return;
        m.currentIndex = next;
        this.updateModalFileInfo();
        const url = `${this.config.routes.pdfProxy}?path=${encodeURIComponent(m.files[next])}`;
        pdfjsLib.getDocument(url).promise.then(pdf => {
            m.doc = pdf;
            m.page = 1;
            this.renderModalPage(1);
            this.pdf.standard.doc = pdf;
            this.pdf.standard.currentIndex = next;
            this.pdf.standard.page = 1;
            this.renderPdfPage('standard', 1);
            $('#standardFileInfo').text(`${next + 1}/${m.files.length}`);
        });
    }

    initDimensionTable() {
        $('#addCavityBtn').on('click', () => this.addCavity());
        $('#deleteCavityBtn').on('click', () => this.deleteLastCavity());
        $('#addPointBtn').on('click', () => this.addPoint());
        $('#deletePointBtn').on('click', () => this.deleteLastPoint());

        $(document).on('input', '.dimension-input', (e) => {
            let val = e.target.value.replace(',', '.');
            if (val.startsWith('+0')) val = val.substring(2);
            e.target.value = val;
            this.validateDimensions();
        });
    }

    addCavity() {
        const nextIdx = $('#dimensionBody tr').length + 1;
        const pointCount = $('#dimensionHeadRow th').length - 1;
        let rowHtml = `<tr class="cavity-row" data-cavity="${nextIdx}">
            <td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${nextIdx}</td>`;
        for (let j = 1; j <= pointCount; j++) {
            rowHtml += `<td><input type="text" class="form-control form-control-sm dimension-input" name="dimensions[${nextIdx}][${j}]" placeholder="P${j}"></td>`;
        }
        rowHtml += '</tr>';
        $('#dimensionBody').append(rowHtml);
        this.updateWeightCavs();
    }

    deleteLastCavity() {
        if ($('#dimensionBody tr').length > 1) {
            $('#dimensionBody tr:last').remove();
            this.updateWeightCavs();
        }
    }

    addPoint() {
        const nextIdx = $('#dimensionHeadRow th').length;
        $('#dimensionHeadRow').append(`<th class="point-header">Point ${nextIdx}</th>`);
        $('#dimensionBody tr').each(function() {
            const cavIdx = $(this).data('cavity');
            $(this).append(`<td><input type="text" class="form-control form-control-sm dimension-input" name="dimensions[${cavIdx}][${nextIdx}]" placeholder="P${nextIdx}"></td>`);
        });
    }

    deleteLastPoint() {
        if ($('#dimensionHeadRow th').length > 2) {
            $('#dimensionHeadRow th:last').remove();
            $('#dimensionBody tr').each(function() {
                $(this).find('td:last').remove();
            });
        }
    }

    initWeightHandling() {
        $('#addWeightCavBtn').on('click', () => this.addWeightRow());
        $('#removeWeightCavBtn').on('click', () => this.removeWeightRow());
    }

    updateWeightCavs() {
        const cavCount = $('#dimensionBody tr').length;
        this.renderWeightRows(cavCount);
    }

    renderWeightRows(count) {
        const container = $('#weightCavContainer');
        container.empty();
        for (let i = 1; i <= count; i++) {
            container.append(`
                <div class="input-group input-group-sm mb-1 weight-row" data-index="${i-1}">
                    <div class="input-group-prepend"><span class="input-group-text py-0" style="font-size:0.65rem;">C${i}</span></div>
                    <input type="text" name="part_weight[]" class="form-control form-control-sm weight-input" placeholder="0.00">
                </div>
            `);
        }
    }

    addWeightRow() {
        const nextIdx = $('#weightCavContainer .weight-row').length + 1;
        $('#weightCavContainer').append(`
            <div class="input-group input-group-sm mb-1 weight-row" data-index="${nextIdx-1}">
                <div class="input-group-prepend"><span class="input-group-text py-0" style="font-size:0.65rem;">C${nextIdx}</span></div>
                <input type="text" name="part_weight[]" class="form-control form-control-sm weight-input" placeholder="0.00">
            </div>
        `);
    }

    removeWeightRow() {
        if ($('#weightCavContainer .weight-row').length > 0) {
            $('#weightCavContainer .weight-row:last').remove();
        }
    }

    initSapSelection() {
        $('#sapCodeInput').on('change', (e) => {
            const code = e.target.value.trim().toUpperCase();
            if (!code) return;
            const $option = $(`#itemSelect option[data-sap-code="${code}"]`);
            if ($option.length) {
                $('#itemSelect').val($option.val()).trigger('change');
            } else {
                Swal.fire('Not Found', `Kode SAP "${code}" tidak ditemukan.`, 'warning');
            }
        });

        $('#itemSelect').on('change', (e) => {
            const $opt = $(e.target.selectedOptions[0]);
            this.handleItemChange($opt);
        });
    }

    handleItemChange($opt) {
        const files = $opt.data('files') || [];
        const similar = $opt.data('similar');
        const weightStd = $opt.data('weight-standard');
        const dimStds = $opt.data('dimension-standards');
        const rawPartNumber = $opt.data('part-number');
        const itemId = $opt.val();

        this.config.itemPartNumber = this.normalizePartNumber(rawPartNumber);
        this.pdf.standard.files = files;
        this.pdf.standard.currentIndex = 0;
        if (files.length > 0) {
            $('#standardFileInfo').text(`1/${files.length}`);
            this.loadPdf('standard', `${this.config.routes.pdfProxy}?path=${encodeURIComponent(files[0])}`);
        } else {
            $('#standardPdfPlaceholder').show().find('p').text('Item ini tidak memiliki file Standard (PCCP).');
            $('#standardPdfCanvas').addClass('d-none');
            $('.standard-nav-controls, #fullStandardBtn').hide();
        }

        if (similar) {
            this.loadPdf('similar', similar);
        } else {
            $('#similarPdfPlaceholder').show().find('p').text('Item ini tidak memiliki file Dimensi Part.');
            $('#similarPdfCanvas').addClass('d-none');
            $('.similar-nav-controls, #fullSimilarBtn').hide();
        }

        if (weightStd) {
            $('.col-berat-part').show();
            $('#weightStandardDisplay').text(weightStd);
            $('#weightStandardBadge').show();
        } else {
            $('.col-berat-part').hide();
            $('#weightStandardBadge').hide();
        }

        this.config.currentDimensionStandards = dimStds || {};
        this.updateDefectList($opt.data('defects'));
        this.validateDimensions();
    }

    updateDefectList(defects) {
        const $select = $('#defectSelect');
        $select.empty().append('<option value="">-- Pilih Defect --</option>');
        if (defects && typeof defects === 'object') {
            $.each(defects, (key, value) => {
                $select.append(`<option value="${value}">${value}</option>`);
            });
            $('#addDefectBtn').show();
        } else {
            $('#addDefectBtn').hide();
        }
    }

    validateDimensions() {
        if (!this.config.currentDimensionStandards) return;
        const stds = this.config.currentDimensionStandards;
        let anyNG = false;

        $('.dimension-input').each((_, input) => {
            const $input = $(input);
            const val = parseFloat($input.val().replace(',', '.'));
            const nameMatch = $input.attr('name').match(/\[\d+\]\[(\d+)\]/);
            if (!nameMatch) return;
            const pIdx = nameMatch[1];
            const std = stds[pIdx];

            $input.removeClass('is-invalid is-valid text-danger font-weight-bold');
            if (isNaN(val)) return;

            if (std) {
                let isNG = false;
                if (std.min !== null && val < parseFloat(std.min)) isNG = true;
                if (std.max !== null && val > parseFloat(std.max)) isNG = true;
                if (!isNG && std.min === null && std.max === null && std.size !== null && std.tolerance !== null) {
                    const min = parseFloat(std.size) - parseFloat(std.tolerance);
                    const max = parseFloat(std.size) + parseFloat(std.tolerance);
                    if (val < min || val > max) isNG = true;
                }

                if (isNG) {
                    $input.addClass('is-invalid text-danger font-weight-bold');
                    anyNG = true;
                } else {
                    $input.addClass('is-valid');
                }
            }
        });
        this.config.dimensionNG = anyNG;
        this.updateJudgment();
    }

    initAqlLogic() {
        $('input[name="total_qty"], input[name="sampling_qty"]').on('input', () => this.updateAqlRequirement());
        $('input[name="total_ok"], input[name="total_ng"]').on('input', () => this.updateJudgment());
        $('#checkOK').on('change', (e) => {
            if (e.target.checked) $('input[name="total_ng"]').val(0);
            this.updateJudgment();
        });
    }

    normalizePartNumber(pn) {
        if (!pn) return '';
        return pn.toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
            .replace(/\s+/g, '')
            .toUpperCase();
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
        if (lotSize >= 91) return 20;
        if (lotSize >= 51) return 13;
        if (lotSize >= 26) return 8;
        if (lotSize >= 16) return 5;
        if (lotSize >= 9) return 3;
        if (lotSize >= 2) return 2;
        return 0;
    }

    getAqlLimits(sampleSize) {
        if (sampleSize >= 1250) return { acc: 14, rej: 15 };
        if (sampleSize >= 800) return { acc: 10, rej: 11 };
        if (sampleSize >= 500) return { acc: 7, rej: 8 };
        if (sampleSize >= 315) return { acc: 5, rej: 6 };
        if (sampleSize >= 200) return { acc: 3, rej: 4 };
        if (sampleSize >= 125) return { acc: 2, rej: 3 };
        if (sampleSize >= 80) return { acc: 1, rej: 2 };
        if (sampleSize >= 20) return { acc: 0, rej: 1 };
        return { acc: 0, rej: 1 };
    }

    updateAqlRequirement() {
        const total = parseInt($('input[name="total_qty"]').val()) || 0;
        const sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
        const req = this.getSampleSize(total);
        if (req > 0) {
            $('input[name="sampling_qty"]').attr('placeholder', `Min: ${req}`);
            if (sampling < req) $('input[name="sampling_qty"]').addClass('is-invalid');
            else $('input[name="sampling_qty"]').removeClass('is-invalid');
        }
        const aql = this.getAqlLimits(sampling);
        $('#acc_val').text(aql.acc);
        $('#rej_val').text(aql.rej);
        $('#aql_info').show();
        this.config.currentAql = aql;
        this.updateJudgment();
    }

    updateJudgment() {
        const sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
        const ng = parseInt($('input[name="total_ng"]').val()) || 0;
        const isCheckOkChecked = $('#checkOK').is(':checked');
        const isDimensiInvalid = $('.dimension-input.is-invalid').length > 0;
        const aql = this.getAqlLimits(sampling);

        const ok = Math.max(0, sampling - ng);
        $('input[name="total_ok"]').val(ok);

        let res = "";
        if (isCheckOkChecked) {
            res = "OK";
        } else if (isDimensiInvalid || ng >= aql.rej) {
            res = "NG";
        } else if (ok > 0 && ng <= aql.acc) {
            res = "OK";
        } else if (ng > 0 && ng < aql.rej) {
            res = "OK";
        }

        $('#judgmentSelect').val(res).removeClass('d-none text-success text-danger');
        if (res === "OK") $('#judgmentSelect').addClass('text-success');
        else if (res === "NG") $('#judgmentSelect').addClass('text-danger');

        const isNG = (res === "NG");
        $('#nextProsesContainer').toggle(isNG);
        if (isNG) $('#nextProses').attr('required', true);
        else $('#nextProses').removeAttr('required').val('');

        $('#saveBtn').prop('disabled', !res);
    }

    initDefectManagement() {
        $('#addDefectBtn').on('click', () => {
            const newRow = $('.defect-row:first').clone();
            newRow.find('input').val('');
            newRow.find('select').val('');
            newRow.append('<div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-defect"><i class="fas fa-minus"></i></button></div>');
            $('#defectContainer').append(newRow);
            this.config.hasDefects = true;
            this.updateJudgment();
        });

        $(document).on('click', '.remove-defect', (e) => {
            $(e.currentTarget).closest('.defect-row').remove();
            this.config.hasDefects = $('.defect-row').length > 1 || $('.defect-select').val() !== "";
            this.updateJudgment();
        });

        $(document).on('change', '.defect-select, .defect-qty', () => {
            let hasAny = false;
            $('.defect-row').each(function() {
                if ($(this).find('.defect-select').val() !== "") hasAny = true;
            });
            this.config.hasDefects = hasAny;
            this.updateJudgment();
        });
    }

    initFormValidation() {
        $('#checksheetForm').on('submit', (e) => {
            const sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
            const total = parseInt($('input[name="total_qty"]').val()) || 0;
            const required = this.getSampleSize(total);

            if (sampling < required) {
                if (!confirm(`Jumlah sampling (${sampling}) kurang dari standar AQL (${required}). Tetap simpan?`)) {
                    e.preventDefault();
                    return false;
                }
            }

            const isNG = $('#judgmentSelect').val() === 'NG';
            if (isNG && !$('#nextProses').val()) {
                Swal.fire('Error', 'Silakan pilih Next Proses untuk judgment NG.', 'error');
                e.preventDefault();
                return false;
            }

            $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        });
    }
}

class FpaEdit {
    constructor(config) {
        this.config = config;
        this.init();
    }

    init() {
        this.initDimensionTable();
        this.initAqlLogic();
        this.initDefectManagement();
        this.initWeightHandling();
        this.initItemHandling();
        this.initFormValidation();
        this.validateDimensions();
    }

    initDimensionTable() {
        $('#editAddCavityBtn').on('click', () => this.addCavity());
        $('#editAddPointBtn').on('click', () => this.addPoint());
        $(document).on('input', '.edit-dimension-input', () => this.validateDimensions());
    }

    addCavity() {
        if (this.config.currentCavities < this.config.maxCavities) {
            this.config.currentCavities++;
            let newRow = `<tr class="edit-cavity-row" data-cavity="${this.config.currentCavities}">
                <td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${this.config.currentCavities}</td>`;
            for (let j = 1; j <= this.config.currentPoints; j++) {
                newRow += `<td class="point-cell">
                    <input type="text" class="form-control form-control-sm edit-dimension-input" 
                        style="min-width: 60px;"
                        name="dimensions[${this.config.currentCavities}][${j}]" 
                        placeholder="P${j}">
                </td>`;
            }
            newRow += `</tr>`;
            $('#editDimensionBody').append(newRow);
        } else {
            alert('Maximum 30 cavities reached');
        }
    }

    addPoint() {
        if (this.config.currentPoints < this.config.maxPoints) {
            this.config.currentPoints++;
            $('#editDimensionHeadRow').append(`<th class="point-header">Point ${this.config.currentPoints}</th>`);
            $('.edit-cavity-row').each((_, row) => {
                let cavityNum = $(row).data('cavity');
                $(row).append(`<td class="point-cell">
                    <input type="text" class="form-control form-control-sm edit-dimension-input" 
                        style="min-width: 60px;"
                        name="dimensions[${cavityNum}][${this.config.currentPoints}]" 
                        placeholder="P${this.config.currentPoints}">
                </td>`);
            });
        } else {
            alert('Maximum 30 points reached');
        }
    }

    getAqlLimits(sampleSize) {
        if (sampleSize >= 1250) return { acc: 14, rej: 15 };
        if (sampleSize >= 800) return { acc: 10, rej: 11 };
        if (sampleSize >= 500) return { acc: 7, rej: 8 };
        if (sampleSize >= 315) return { acc: 5, rej: 6 };
        if (sampleSize >= 200) return { acc: 3, rej: 4 };
        if (sampleSize >= 125) return { acc: 2, rej: 3 };
        if (sampleSize >= 80) return { acc: 1, rej: 2 };
        if (sampleSize >= 20) return { acc: 0, rej: 1 };
        return { acc: 0, rej: 1 };
    }

    updateJudgment() {
        const sampling = parseInt($('#sampling_qty').val()) || 0;
        const ng = parseInt($('#total_ng').val()) || 0;
        const isDimensiInvalid = $('.edit-dimension-input.is-invalid').length > 0;

        if (sampling >= ng) {
            $('#total_ok').val(sampling - ng);
        } else {
            $('#total_ok').val(Math.max(0, sampling - ng));
        }

        const limits = this.getAqlLimits(sampling);
        const judgmentSelect = $('#judgment');

        let res = "";
        if (ng > 0 || sampling > 0 || isDimensiInvalid) {
            if (isDimensiInvalid || ng >= limits.rej) {
                res = "NG";
            } else if (ng <= limits.acc) {
                res = "OK";
            } else {
                res = "NG";
            }
        }
        
        if (res) {
            judgmentSelect.val(res).removeClass('d-none text-success text-danger');
            if (res === 'OK') judgmentSelect.addClass('text-success');
            else judgmentSelect.addClass('text-danger');
        }
        this.toggleNextProses();
    }

    toggleNextProses() {
        const judgment = $('#judgment').val();
        const ngCount = parseInt($('#total_ng').val()) || 0;
        const container = $('#nextProsesContainer');
        if (judgment === 'NG' || ngCount > 0) {
            container.show();
        } else {
            container.hide();
        }
    }

    normalizePartNumber(pn) {
        if (!pn) return '';
        return pn.toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
            .replace(/\s+/g, '')
            .toUpperCase();
    }

    validateDimensions() {
        const selectedOption = $('#item_id').find('option:selected');
        const rawPartNumber = selectedOption.data('part-number');
        const itemPartNumber = this.normalizePartNumber(rawPartNumber);
        const dimensionStandards = this.config.partDimensionStandards[itemPartNumber];

        $('.edit-dimension-input').each((_, input) => {
            const $input = $(input);
            const name = $input.attr('name');
            const match = name.match(/\[(\d+)\]\[(\d+)\]/);
            if (!match) return;

            const point = match[2];
            const standard = dimensionStandards ? dimensionStandards[point] : null;
            const valStr = $input.val().trim();
            const value = parseFloat(valStr.replace(',', '.'));

            if (standard && valStr !== '' && !isNaN(value)) {
                let isInvalid = false;
                if (standard.min !== null && value < standard.min) isInvalid = true;
                if (standard.max !== null && value > standard.max) isInvalid = true;
                if (standard.min === null && standard.max === null) {
                    if (standard.size !== null && standard.tolerance !== null) {
                        const lowerBound = standard.size - standard.tolerance;
                        const upperBound = standard.size + standard.tolerance;
                        if (value < lowerBound || value > upperBound) isInvalid = true;
                    }
                }
                if (isInvalid) $input.addClass('is-invalid');
                else $input.removeClass('is-invalid');
            } else {
                $input.removeClass('is-invalid');
            }
        });

        this.updateJudgment();
    }

    initAqlLogic() {
        $('#sampling_qty, #total_ng').on('input', () => this.updateJudgment());
        $('#judgment').on('change', () => this.toggleNextProses());
    }

    initDefectManagement() {
        this.defaultDefects = [
            { value: 'scratch', text: 'BARET' },
            { value: 'silver', text: 'SILVER' },
            { value: 'flow', text: 'FLOW' },
            { value: 'flash', text: 'FLASH' },
            { value: 'shoot_mold', text: 'SHOOT MOLD' },
            { value: 'bending', text: 'BENDING' },
            { value: 'sinkmark', text: 'SINKMARK' },
            { value: 'dimension', text: 'Dimensi' }
        ];

        $('#editAddDefectBtn').on('click', () => this.addDefectRow());
        $(document).on('click', '.remove-defect-btn', (e) => this.removeDefectRow(e));
        $(document).on('input', '.defect-qty', () => this.calculateTotalNG());
        $('#total_ng').on('input', () => this.handleNgInput());
    }

    updateDefectOptions() {
        const selectedOption = $('#item_id').find('option:selected');
        let defectsData = selectedOption.data('defects');

        if (typeof defectsData === 'string') {
            try { defectsData = JSON.parse(defectsData); } catch (e) { defectsData = []; }
        }

        $('.defect-select').each((_, select) => {
            const $select = $(select);
            const currentVal = $select.val();
            $select.empty().append('<option value="">-- Pilih Defect --</option>');

            if (Array.isArray(defectsData) && defectsData.length > 0) {
                $.each(defectsData, (_, value) => {
                    $select.append(`<option value="${value}">${value}</option>`);
                });
            } else {
                $.each(this.defaultDefects, (_, defect) => {
                    $select.append(`<option value="${defect.text}">${defect.text}</option>`);
                });
            }
            if (currentVal) $select.val(currentVal);
        });
    }

    addDefectRow() {
        const rowCount = $('.defect-row').length;
        if (rowCount < 5) {
            const newRow = $(`<div class="input-group mb-2 defect-row">
                <select class="form-control form-control-sm defect-select" name="defect_types[]">
                    <option value="">-- Pilih Defect --</option>
                </select>
                <input type="number" class="form-control form-control-sm defect-qty" name="defect_quantities[]" placeholder="Qty" min="1" style="max-width: 80px;">
                <div class="input-group-append">
                    <button class="btn btn-danger btn-xs remove-defect-btn" type="button"><i class="fas fa-minus"></i></button>
                </div>
            </div>`);
            $('#editDefectContainer').append(newRow);
            this.updateDefectOptions();
        }
    }

    removeDefectRow(e) {
        $(e.currentTarget).closest('.defect-row').remove();
        this.calculateTotalNG();
    }

    calculateTotalNG() {
        let total = 0;
        $('.defect-qty').each((_, input) => {
            total += parseInt($(input).val()) || 0;
        });
        $('#total_ng').val(total).trigger('input');
        if (total >= 0 || $('.defect-row').length > 0) $('#editAddDefectBtn').show();
    }

    handleNgInput() {
        const ng = parseInt($('#total_ng').val()) || 0;
        if (ng > 0) {
            $('#editAddDefectBtn').show();
            if ($('.defect-row').length === 0) this.addDefectRow();
        }
    }

    initWeightHandling() {
        this.editMaxWeightCav = 8;
        $('#editAddWeightCavBtn').on('click', () => this.addWeightCav());
        $('#editRemoveWeightCavBtn').on('click', () => this.removeWeightCav());
        this.updateWeightCavBadge();
    }

    buildWeightCavRow(cavNum, value = '') {
        return `<div class="input-group input-group-sm mb-1 edit-weight-cav-row">
            <div class="input-group-prepend">
                <span class="input-group-text" style="min-width:60px; justify-content:center; font-weight:600;">CAV ${cavNum}</span>
            </div>
            <input type="number" step="0.01" min="0" class="form-control text-center"
                name="part_weight[]" placeholder="0.00" value="${value}">
            <div class="input-group-append">
                <span class="input-group-text text-muted small">gr</span>
            </div>
        </div>`;
    }

    updateWeightCavBadge() {
        const cnt = $('#editWeightCavContainer .edit-weight-cav-row').length;
        $('#editWeightCavCount').text(`${cnt} Cav`);
        $('#editAddWeightCavBtn').prop('disabled', cnt >= this.editMaxWeightCav);
        $('#editRemoveWeightCavBtn').prop('disabled', cnt <= 1);
    }

    addWeightCav() {
        const cnt = $('#editWeightCavContainer .edit-weight-cav-row').length;
        if (cnt >= this.editMaxWeightCav) return;
        $('#editWeightCavContainer').append(this.buildWeightCavRow(cnt + 1));
        this.updateWeightCavBadge();
    }

    removeWeightCav() {
        const rows = $('#editWeightCavContainer .edit-weight-cav-row');
        if (rows.length <= 1) return;
        rows.last().remove();
        this.updateWeightCavBadge();
    }

    initItemHandling() {
        $('#item_id').on('change', () => {
            const $opt = $('#item_id').find('option:selected');
            const customer = $opt.data('customer');
            const weightStandard = $opt.data('weight-standard');

            if (customer && (customer.toUpperCase().includes('ASTRA HONDA MOTOR') || customer.toUpperCase().includes('AHM'))) {
                $('#editBeratPartContainer').show();
                if (weightStandard) {
                    $('#editWeightStandardDisplay').text(weightStandard);
                    $('#editWeightStandardBadge').show();
                } else {
                    $('#editWeightStandardBadge').hide();
                }
            } else {
                $('#editBeratPartContainer').hide();
                $('#editWeightCavContainer input').val('');
                $('#editWeightStandardBadge').hide();
            }

            this.updateDefectOptions();
            this.validateDimensions();
        }).trigger('change');
        this.updateDefectOptions();
    }

    initFormValidation() {
        $('#editChecksheetForm').on('submit', (e) => {
            const judgment = $('#judgment').val();
            const nextProses = $('#next_proses').val();

            if (judgment === 'NG' && !nextProses) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Next Proses Wajib Dipilih',
                    text: 'Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!',
                    confirmButtonColor: '#3085d6'
                });
                const $nextProses = $('#next_proses');
                $nextProses.addClass('is-invalid').focus();
                setTimeout(() => $nextProses.removeClass('is-invalid'), 3000);
                return false;
            }

            $('#editChecksheetForm').find(':input:disabled').each((_, input) => {
                $(input).prop('disabled', false).addClass('was-disabled');
            });
            $('#loadingOverlay').css('display', 'flex');
            $('#btnSubmit').prop('disabled', true);
        });

        $(document).on('ajaxComplete ajaxError', () => {
            $('.was-disabled').prop('disabled', true).removeClass('was-disabled');
            $('#loadingOverlay').hide();
            $('#btnSubmit').prop('disabled', false);
        });
    }
}

window.initFpaIndex = (config) => new FpaIndex(config);
window.initFpaCreate = (config) => new FpaCreate(config);
window.initFpaEdit = (config) => new FpaEdit(config);
