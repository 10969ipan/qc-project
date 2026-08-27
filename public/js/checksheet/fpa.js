class FpaIndex {
    constructor(config) {
        this.config = config;
        this.init();
    }

    init() {
        this.initCharCounters();
        this.initModalHandlers();
        this.initAjaxForms();
        this.initQRDetail();
    }

    initCharCounters() {
        if (!this.config.checksheets) return;

        this.config.checksheets.forEach((id) => {
            ["kashift", "supervisor", "asst_manager", "manager"].forEach(
                (type) => {
                    const textarea = document.getElementById(
                        `rejection_remarks${id}${type}`,
                    );
                    const charCount = document.getElementById(
                        `charCount${id}${type}`,
                    );
                    if (textarea && charCount) {
                        textarea.addEventListener("input", function () {
                            charCount.textContent = this.value.length;
                        });
                    }
                },
            );
        });
    }

    initModalHandlers() {
        $(document).on("click", ".btn-edit-modal", function (e) {
            e.preventDefault();
            const url = $(this).attr("href");
            $("#editModal").modal("show");
            $("#editModalBody").html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>',
            );

            $.ajax({
                url: url,
                success: (response) => $("#editModalBody").html(response),
                error: (xhr) => {
                    let message = "Gagal memuat data checksheet.";
                    if (xhr.status === 404)
                        message = "Data checksheet tidak ditemukan.";
                    else if (xhr.status === 403)
                        message =
                            "Anda tidak memiliki akses untuk mengedit checksheet ini.";
                    else if (xhr.status === 500)
                        message = "Terjadi kesalahan pada server.";
                    $("#editModalBody").html(
                        `<div class="alert alert-danger">${message}</div>`,
                    );
                },
            });
        });

        $(document).on("click", ".btn-status-modal", function (e) {
            e.preventDefault();
            const url = $(this).attr("href");
            $("#statusModal").modal("show");
            $("#statusModalBody").html(
                '<div class="text-center py-5"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>',
            );

            $.ajax({
                url: url,
                success: (response) => $("#statusModalBody").html(response),
                error: (xhr) => {
                    let message = "Gagal memuat data status approval.";
                    if (xhr.status === 404) message = "Data tidak ditemukan.";
                    else if (xhr.status === 403)
                        message =
                            "Anda tidak memiliki akses untuk mengubah status approval ini.";
                    $("#statusModalBody").html(
                        `<div class="alert alert-danger">${message}</div>`,
                    );
                },
            });
        });
    }

    initAjaxForms() {
        $(document).on("submit", ".ajax-form", (e) => {
            const $form = $(e.currentTarget);

            e.preventDefault();
            const $submitBtn = $form.find('button[type="submit"]');
            const $modalErrors = $form.find("#modal-errors");
            const originalBtnHtml = $submitBtn.html();

            $modalErrors.hide().html("");
            $form.find(".is-invalid").removeClass("is-invalid");
            $form.find(".invalid-feedback").remove();

            $submitBtn
                .prop("disabled", true)
                .html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...',
                );

            $.ajax({
                url: $form.attr("action"),
                method: $form.attr("method"),
                data: $form.serialize(),
                dataType: "json",
                success: (response) => {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            text: response.message || "Data berhasil disimpan.",
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            window.location.href =
                                response.redirect || window.location.href;
                        });
                    } else {
                        this.showModalError(
                            $modalErrors,
                            response.message ||
                            "Terjadi kesalahan saat menyimpan data.",
                        );
                        $submitBtn
                            .prop("disabled", false)
                            .html(originalBtnHtml);
                    }
                },
                error: (xhr) => {
                    $submitBtn.prop("disabled", false).html(originalBtnHtml);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml =
                            '<div class="alert alert-danger"><ul class="mb-0">';
                        if (errors) {
                            $.each(errors, (field, messages) => {
                                errorHtml += `<li>${messages[0]}</li>`;
                                const $input = $form.find(`[name="${field}"]`);
                                if ($input.length) {
                                    $input.addClass("is-invalid");
                                    if (
                                        !$input.next(".invalid-feedback").length
                                    ) {
                                        $input.after(
                                            `<div class="invalid-feedback">${messages[0]}</div>`,
                                        );
                                    }
                                }
                            });
                        } else {
                            errorHtml += `<li>${xhr.responseJSON.message || "Validasi gagal."}</li>`;
                        }
                        errorHtml += "</ul></div>";
                        this.showModalError($modalErrors, errorHtml);
                    } else {
                        const message = xhr.responseJSON
                            ? xhr.responseJSON.message
                            : "Terjadi kesalahan sistem.";
                        this.showModalError(
                            $modalErrors,
                            `<div class="alert alert-danger">${message}</div>`,
                        );
                    }

                    const $modalBody = $form.closest(".modal-body");
                    if ($modalBody.length) {
                        $modalBody.animate({ scrollTop: 0 }, "fast");
                    } else {
                        $("html, body").animate(
                            { scrollTop: $form.offset().top - 100 },
                            "fast",
                        );
                    }
                },
            });
        });
    }

    showModalError($container, html) {
        $container.html(html).fadeIn();
    }

    initQRDetail() {
        $(document).on('click', '.btn-qr-detail', function () {
            const data = $(this).data();
            let sapCode = data.sap || '-';
            if ((sapCode === '-' || !sapCode) && data.qr) {
                const parts = data.qr.split('|');
                if (parts.length >= 5) sapCode = parts[4].trim();
            }
            $('#modal-qr-raw').text(data.qr || '-');
            $('#modal-qr-part').text(data.part || '-');
            $('#modal-qr-supplier').text(data.supplier || '-');
            $('#modal-qr-qty').text(data.qty || '-');
            $('#modal-qr-unique').text(data.unique || '-');
            $('#modal-qr-sap').text(sapCode);
            $('#qrModal').modal('show');
        });
    }
}

class FpaCreate {
    constructor(config) {
        console.log("FpaCreate loaded - version 2026-03-25-v2");
        this.config = config;
        this.timer = {
            startTime: null,
            elapsed: 0,
            interval: null,
            isRunning: false,
        };
        this.pdf = {
            standard: {
                doc: null,
                page: 1,
                scale: 1.0,
                rendering: false,
                pending: null,
                canvas: null,
                ctx: null,
                files: [],
                currentIndex: 0,
            },
            similar: {
                doc: null,
                page: 1,
                scale: 1.0,
                rendering: false,
                pending: null,
                canvas: null,
                ctx: null,
            },
        };
        this.init();
    }

    init() {
        if (this.config.pdfWorkerSrc) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;
        }
        this.lockInputs();
        this.initTimer();
        this.initPdfHandling();
        this.initDimensionTable();
        this.initWeightHandling();
        this.initSapSelection();
        this.initAqlLogic();
        this.initDefectManagement();
        this.initFormValidation();

        // Initialize state
        this.calculateTotalNG();
        this.updateJudgment();
    }

    lockInputs() {
        const formInputs = $(
            '#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)',
        );
        formInputs.prop("disabled", true);
        $("#checksheetForm").addClass("inputs-locked");
        this.config.isLocked = true;
    }

    unlockInputs() {
        const formInputs = $(
            '#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)',
        );
        formInputs.prop("disabled", false);
        $("#checksheetForm").removeClass("inputs-locked");
        this.config.isLocked = false;
    }

    initTimer() {
        $("#startTimerBtn").on("click", (e) => {
            if (!this.timer.isRunning) {
                this.startTimer();
                this.unlockInputs();
                $(e.currentTarget)
                    .prop("disabled", true)
                    .removeClass("btn-success")
                    .addClass("btn-secondary")
                    .html('<i class="fas fa-clock"></i> Running...');
                $("#saveBtn").prop("disabled", false);
            }
        });
    }

    startTimer() {
        this.timer.isRunning = true;
        this.timer.startTime = Date.now() - this.timer.elapsed * 1000;
        this.timer.interval = setInterval(() => {
            this.timer.elapsed = Math.floor(
                (Date.now() - this.timer.startTime) / 1000,
            );
            this.updateTimerDisplay();
        }, 1000);
    }

    updateTimerDisplay() {
        const h = Math.floor(this.timer.elapsed / 3600)
            .toString()
            .padStart(2, "0");
        const m = Math.floor((this.timer.elapsed % 3600) / 60)
            .toString()
            .padStart(2, "0");
        const s = (this.timer.elapsed % 60).toString().padStart(2, "0");
        $("#timerDisplay").text(`${h}:${m}:${s}`);
        $("#cycleTimeInput").val(this.timer.elapsed);
    }

    initPdfHandling() {
        this.pdf.standard.canvas = document.getElementById("standardPdfCanvas");
        this.pdf.standard.ctx = this.pdf.standard.canvas.getContext("2d");
        this.pdf.similar.canvas = document.getElementById("similarPdfCanvas");
        this.pdf.similar.ctx = this.pdf.similar.canvas.getContext("2d");

        $("#prevStandardPage").on("click", () =>
            this.changePage("standard", -1),
        );
        $("#nextStandardPage").on("click", () =>
            this.changePage("standard", 1),
        );
        $("#prevSimilarPage").on("click", () => this.changePage("similar", -1));
        $("#nextSimilarPage").on("click", () => this.changePage("similar", 1));

        $("#prevStandardFile").on("click", () =>
            this.changeFile("standard", -1),
        );
        $("#nextStandardFile").on("click", () =>
            this.changeFile("standard", 1),
        );

        $("#fullStandardBtn").on("click", () => this.openFullPdf("standard"));
        $("#fullSimilarBtn").on("click", () => this.openFullPdf("similar"));

        this.initFullPdfModal();
    }

    loadPdf(type, url) {
        if (!url) {
            console.warn(`No URL provided for ${type} PDF`);
            $(`#${type}PdfLoading`).removeClass("d-flex").addClass("d-none");
            $(`#${type}PdfPlaceholder`)
                .show()
                .find("p")
                .first()
                .text(`File ${type} tidak tersedia.`);
            return;
        }

        const p = this.pdf[type];
        p.doc = null;
        p.page = 1;
        p.rendering = false;
        p.pending = null;

        $(`#${type}PdfPlaceholder`).removeClass("d-flex").addClass("d-none");
        $(`#${type}PdfLoading`).removeClass("d-none").addClass("d-flex");
        $(`#${type}PdfCanvas`).addClass("d-none");
        $(`.${type}-nav-controls`).hide();
        $(`#full${type.charAt(0).toUpperCase() + type.slice(1)}Btn`).hide();
        $(`#download${type.charAt(0).toUpperCase() + type.slice(1)}Btn`).hide();

        pdfjsLib
            .getDocument(url)
            .promise.then((pdf) => {
                p.doc = pdf;
                $(`#${type}PdfLoading`)
                    .removeClass("d-flex")
                    .addClass("d-none");
                $(`#${type}PdfCanvas`).removeClass("d-none").show();
                $(`.${type}-nav-controls`).show();
                if (type === "standard" && p.files.length > 1)
                    $(".file-nav").show();
                $(
                    `#full${type.charAt(0).toUpperCase() + type.slice(1)}Btn`,
                ).show();
                $(`#download${type.charAt(0).toUpperCase() + type.slice(1)}Btn`)
                    .attr("href", url)
                    .show();
                this.renderPdfPage(type, 1);
            })
            .catch((err) => {
                console.error(`Error loading ${type} PDF:`, err);
                $(`#${type}PdfLoading`)
                    .removeClass("d-flex")
                    .addClass("d-none");
                $(`#${type}PdfPlaceholder`)
                    .removeClass("d-none")
                    .addClass("d-flex")
                    .find("p")
                    .first()
                    .text(`Error loading PDF: ${err.message}`);
            });
    }

    renderPdfPage(type, num) {
        const p = this.pdf[type];
        p.rendering = true;
        p.doc.getPage(num).then((page) => {
            const containerWidth = $(`#${type}PdfContainer`).width() - 40;
            const unscaledViewport = page.getViewport({ scale: 1 });
            const dynamicScale = containerWidth / unscaledViewport.width;
            const viewport = page.getViewport({ scale: dynamicScale });

            p.canvas.height = viewport.height;
            p.canvas.width = viewport.width;

            const renderTask = page.render({
                canvasContext: p.ctx,
                viewport: viewport,
            });
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

    getPdfUrl(itemId, index) {
        if (!this.config.pdfRoutePattern) return "";
        return this.config.pdfRoutePattern
            .replace("ID_PLACEHOLDER", itemId)
            .replace("INDEX_PLACEHOLDER", index);
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
        const itemId = $("#itemSelect").val();
        const url = this.getPdfUrl(itemId, next);
        this.loadPdf(type, url);
    }

    initFullPdfModal() {
        this.modalPdf = {
            doc: null,
            page: 1,
            scale: 1.5,
            rendering: false,
            pending: null,
            canvas: document.getElementById("the-canvas"),
            ctx: document.getElementById("the-canvas").getContext("2d"),
            files: [],
            currentIndex: 0,
            currentType: "standard",
        };

        $("#prevPage").on("click", () => this.changeModalPage(-1));
        $("#nextPage").on("click", () => this.changeModalPage(1));
        $("#pdfZoomIn").on("click", () => {
            this.modalPdf.scale += 0.25;
            this.renderModalPage(this.modalPdf.page);
        });
        $("#pdfZoomOut").on("click", () => {
            if (this.modalPdf.scale > 0.5) {
                this.modalPdf.scale -= 0.25;
                this.renderModalPage(this.modalPdf.page);
            }
        });
        $("#pdfZoomReset").on("click", () => {
            this.modalPdf.scale = 1.5;
            this.renderModalPage(this.modalPdf.page);
        });

        $("#prevPdf").on("click", () => this.changeModalFile(-1));
        $("#nextPdf").on("click", () => this.changeModalFile(1));
    }

    openFullPdf(type) {
        const source = this.pdf[type];
        if (!source.doc) return;

        this.modalPdf.currentType = type;
        this.modalPdf.doc = source.doc;
        this.modalPdf.page = source.page;
        this.modalPdf.files = type === "standard" ? source.files : [];
        this.modalPdf.currentIndex =
            type === "standard" ? source.currentIndex : 0;
        this.modalPdf.scale = 1.5;

        $("#pdfModal").modal("show");
        this.updateModalFileInfo();
        this.renderModalPage(this.modalPdf.page);
    }

    renderModalPage(num) {
        const m = this.modalPdf;
        m.rendering = true;
        m.doc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: m.scale });
            m.canvas.height = viewport.height;
            m.canvas.width = viewport.width;
            const renderTask = page.render({
                canvasContext: m.ctx,
                viewport: viewport,
            });
            renderTask.promise.then(() => {
                m.rendering = false;
                if (m.pending !== null) {
                    this.renderModalPage(m.pending);
                    m.pending = null;
                }
            });
            $("#pageInfo").text(`Page ${num} of ${m.doc.numPages}`);
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
        if (this.modalPdf.currentType === "similar") {
            $("#pdfInfo").text("Dimensi Part PDF");
            $("#prevPdf, #nextPdf").hide();
        } else {
            $("#pdfInfo").text(
                `File ${this.modalPdf.currentIndex + 1} of ${this.modalPdf.files.length}`,
            );
            $("#prevPdf, #nextPdf").toggle(this.modalPdf.files.length > 1);
        }
    }

    changeModalFile(delta) {
        const m = this.modalPdf;
        const next = m.currentIndex + delta;
        if (next < 0 || next >= m.files.length) return;
        m.currentIndex = next;
        this.updateModalFileInfo();
        const itemId = $("#itemSelect").val();
        const url = this.getPdfUrl(itemId, next);
        pdfjsLib.getDocument(url).promise.then((pdf) => {
            m.doc = pdf;
            m.page = 1;
            this.renderModalPage(1);
            this.pdf.standard.doc = pdf;
            this.pdf.standard.currentIndex = next;
            this.pdf.standard.page = 1;
            this.renderPdfPage("standard", 1);
            $("#standardFileInfo").text(`${next + 1}/${m.files.length}`);
        });
    }

    initDimensionTable() {
        $("#addCavityBtn").on("click", () => this.addCavity());
        $("#deleteCavityBtn").on("click", () => this.deleteLastCavity());
        $("#addPointBtn").on("click", () => this.addPoint());
        $("#deletePointBtn").on("click", () => this.deleteLastPoint());

        $(document).on("input", ".dimension-input", (e) => {
            let val = e.target.value.replace(",", ".");
            if (val.startsWith("+0")) val = val.substring(2);
            e.target.value = val;
            this.validateDimensions();
        });
    }

    addCavity() {
        const nextIdx = $("#dimensionBody tr").length + 1;
        const pointCount = $("#dimensionHeadRow th").length - 1;
        let rowHtml = `<tr class="cavity-row" data-cavity="${nextIdx}">
            <td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${nextIdx}</td>`;
        for (let j = 1; j <= pointCount; j++) {
            rowHtml += `<td><input type="text" class="form-control form-control-sm dimension-input" name="dimensions[${nextIdx}][${j}]" placeholder="P${j}"></td>`;
        }
        rowHtml += "</tr>";
        $("#dimensionBody").append(rowHtml);
        this.updateWeightCavs();
    }

    deleteLastCavity() {
        if ($("#dimensionBody tr").length > 1) {
            $("#dimensionBody tr:last").remove();
            this.updateWeightCavs();
        }
    }

    addPoint() {
        const nextIdx = $("#dimensionHeadRow th").length;
        $("#dimensionHeadRow").append(
            `<th class="point-header">Point ${nextIdx}</th>`,
        );
        $("#dimensionBody tr").each(function () {
            const cavIdx = $(this).data("cavity");
            $(this).append(
                `<td><input type="text" class="form-control form-control-sm dimension-input" name="dimensions[${cavIdx}][${nextIdx}]" placeholder="P${nextIdx}"></td>`,
            );
        });
    }

    deleteLastPoint() {
        if ($("#dimensionHeadRow th").length > 2) {
            $("#dimensionHeadRow th:last").remove();
            $("#dimensionBody tr").each(function () {
                $(this).find("td:last").remove();
            });
        }
    }

    initWeightHandling() {
        $("#addWeightCavBtn").on("click", () => this.addWeightRow());
        $("#removeWeightCavBtn").on("click", () => this.removeWeightRow());
    }

    updateWeightCavs() {
        const cavCount = $("#dimensionBody tr").length;
        this.renderWeightRows(cavCount);
    }

    renderWeightRows(count) {
        const container = $("#weightCavContainer");
        container.empty();
        for (let i = 1; i <= count; i++) {
            container.append(`
                <div class="input-group input-group-sm mb-1 weight-row" data-index="${i - 1}">
                    <div class="input-group-prepend"><span class="input-group-text py-0" style="font-size:0.65rem;">CAV ${i}</span></div>
                    <input type="text" name="part_weight[]" class="form-control form-control-sm weight-input" placeholder="0.00">
                </div>
            `);
        }
    }

    addWeightRow() {
        const nextIdx = $("#weightCavContainer .weight-row").length + 1;
        $("#weightCavContainer").append(`
            <div class="input-group input-group-sm mb-1 weight-row" data-index="${nextIdx - 1}">
                <div class="input-group-prepend"><span class="input-group-text py-0" style="font-size:0.65rem;">CAV ${nextIdx}</span></div>
                <input type="text" name="part_weight[]" class="form-control form-control-sm weight-input" placeholder="0.00">
            </div>
        `);
    }

    removeWeightRow() {
        if ($("#weightCavContainer .weight-row").length > 0) {
            $("#weightCavContainer .weight-row:last").remove();
        }
    }

    initSapSelection() {
        // Fix: gunakan data-sap_code (underscore) sesuai attribute di blade template
        const normalize = (str) => (str || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();

        $("#sapCodeInput").on("input", (e) => {
            const sapCode = $(e.currentTarget).val().trim();
            const $hiddenInput = $("#sapCodeInputHidden");

            if (sapCode.length >= 1) {
                const targetSap = normalize(sapCode);
                const matchedOption = $("#itemSelect option").filter(function () {
                    // Support both attribute formats
                    const itemSap = normalize(
                        $(this).attr('data-sap_code') || $(this).data('sap_code') ||
                        $(this).attr('data-sap-code') || $(this).data('sap-code')
                    );
                    return itemSap && itemSap === targetSap;
                });

                if (matchedOption.length > 0) {
                    $("#itemSelect").val(matchedOption.val()).trigger("change");
                    $(e.currentTarget).removeClass("is-invalid").addClass("is-valid");
                    $hiddenInput.val(
                        matchedOption.attr('data-sap_code') ||
                        matchedOption.data('sap_code') ||
                        matchedOption.attr('data-sap-code') ||
                        matchedOption.data('sap-code') || ''
                    );
                } else {
                    $(e.currentTarget).removeClass("is-valid").addClass("is-invalid");
                    $hiddenInput.val("");
                }
            } else {
                $(e.currentTarget).removeClass("is-valid is-invalid");
                $hiddenInput.val("");
            }
        });

        $("#itemSelect").on("change", (e) => {
            const $opt = $(e.currentTarget).find(":selected");
            if (!$opt.length || !$opt.val()) return;

            this.handleItemChange($opt);

            // Sync SAP code inputs (support both attribute formats)
            const sapCode =
                $opt.attr('data-sap_code') || $opt.data('sap_code') ||
                $opt.attr('data-sap-code') || $opt.data('sap-code') || '';
            $("#sapCodeInput")
                .val(sapCode)
                .removeClass("is-invalid")
                .addClass(sapCode ? "is-valid" : "");
            $("#sapCodeInputHidden").val(sapCode);
        });
    }

    handleItemChange($opt) {
        let files = $opt.data("files");
        if (typeof files === "string") {
            try { files = JSON.parse(files); } catch (e) { files = []; }
        }
        files = files || [];

        const similar = $opt.data("similar");
        const weightStd = $opt.data("weight-standard");
        const customer = $opt.data("customer") || '';
        const cavityData = $opt.data("cavity");
        const rawPartNum = $opt.data("part-number");
        const itemId = $opt.val();

        this.config.itemPartNumber = this.normalizePartNumber(rawPartNum);
        this.pdf.standard.files = files;
        this.pdf.standard.currentIndex = 0;

        // ── PDF Standard ──────────────────────────────────────────────────────
        if (files.length > 0) {
            $("#standardFileInfo").text(`1/${files.length}`);
            this.loadPdf("standard", this.getPdfUrl(itemId, 0));
        } else {
            $("#standardPdfCanvas").addClass("d-none");
            $("#standardPdfPlaceholder").removeClass("d-none").addClass("d-flex")
                .find("p").first().text("Item ini tidak memiliki file Standard (PCCP).");
            $(".standard-nav-controls, #fullStandardBtn, #downloadStandardBtn").hide();
        }

        // ── PDF Similar ───────────────────────────────────────────────────────
        if (similar) {
            this.loadPdf("similar", similar);
            $("#similarStatusText").text("");
        } else {
            $("#similarPdfCanvas").addClass("d-none");
            $("#similarPdfPlaceholder").removeClass("d-none").addClass("d-flex")
                .find("p").first().text("Item ini tidak memiliki file Dimensi Part.");
            $("#similarStatusText").text("");
            $(".similar-nav-controls, #fullSimilarBtn, #downloadSimilarBtn").hide();
        }

        // ── Berat Part – hanya untuk AHM / PT Takagi (sesuai in-process.js) ──
        const cu = customer.toUpperCase();
        const showWeight = cu.includes('ASTRA HONDA MOTOR') ||
            cu.includes('AHM') ||
            cu.includes('PT. TAKAGI SARI MULTI UTAMA');
        if (showWeight) {
            $(".col-berat-part").attr("style", "display: table-cell !important;");
            const itemCavity = parseInt(cavityData) || 1;
            this.initWeightCavities(Math.min(itemCavity, 8));
            if (weightStd) {
                $("#weightStandardDisplay").text(weightStd);
                $("#weightStandardBadge").show();
            } else {
                $("#weightStandardBadge").hide();
            }
        } else {
            $(".col-berat-part").attr("style", "display: none !important;");
            this.initWeightCavities(1);
            $("#weightStandardBadge").hide();
        }

        // ── Dimension Standards ───────────────────────────────────────────────
        let dimStds = $opt.data("dimension-standards");
        if (typeof dimStds === 'string') {
            try { dimStds = JSON.parse(dimStds); } catch (e) { dimStds = null; }
        }
        // Hitung jumlah points dari standards
        let pointCount = 5;
        if (dimStds) {
            if (Array.isArray(dimStds)) pointCount = dimStds.length;
            else if (typeof dimStds === 'object') {
                const keys = Object.keys(dimStds).map(k => parseInt(k));
                if (keys.length > 0) pointCount = Math.max(...keys);
            }
        }

        // Update cavity rows dinamis jika plantContext === 'karawang'
        if (this.config.plantContext === 'karawang') {
            this.updateCavityRows(cavityData || 1, pointCount);
            $('#addCavityBtn, #deleteCavityBtn, #addPointBtn, #deletePointBtn').hide();
        }

        // Store standards for validation
        this.config.currentDimensionStandards =
            this.config.partDimensionStandards[this.config.itemPartNumber] || {};

        // ── Defect List ───────────────────────────────────────────────────────
        this.updateDefectDropdown($opt.data("defects"));
        this.validateDimensions();
        this.calculateTotalNG();
    }

    updateDefectDropdown(defects) {
        let defectsData = defects;
        if (typeof defectsData === "string") {
            try {
                defectsData = JSON.parse(defectsData);
            } catch (e) {
                defectsData = [];
            }
        }

        const defaults = [
            { v: "BARET", t: "BARET" },
            { v: "SILVER", t: "SILVER" },
            { v: "FLOW", t: "FLOW" },
            { v: "FLASH", t: "FLASH" },
            { v: "SHOOT MOLD", t: "SHOOT MOLD" },
            { v: "BENDING", t: "BENDING" },
            { v: "SINKMARK", t: "SINKMARK" },
            { v: "DIMENSI", t: "Dimensi" },
        ];

        this.defectItems = [];

        if (Array.isArray(defectsData) && defectsData.length > 0) {
            defectsData.forEach((d) => {
                const name = typeof d === "object" ? (d.name || d.t || d.v) : d;
                const key = typeof d === "object" ? (d.v || d.name) : d;
                this.defectItems.push({ key: key, name: name, count: 0 });
            });
        } else {
            defaults.forEach((d) => {
                this.defectItems.push({ key: d.v, name: d.t, count: 0 });
            });
        }

        if (!this.defectItems.some(d => d.key === 'DIMENSI' || d.key === 'dimension' || (d.name && d.name.toLowerCase() === 'dimensi'))) {
            this.defectItems.push({ key: 'DIMENSI', name: 'Dimensi', count: 0 });
        }

        this.renderDefectButtons();
        this.calculateTotalNG();
    }

    renderDefectButtons() {
        if (!this.defectItems) return;
        const sorted = [...this.defectItems].sort((a, b) => b.count - a.count);

        const $container = $("#defectContainer");
        $container.empty();

        if (sorted.length === 0) {
            $container.html('<span class="text-muted small">Pilih Item Part untuk memuat daftar defect</span>');
            return;
        }

        let html = '<div class="d-flex flex-wrap align-items-center" style="gap: 6px;">';
        let totalNgCount = 0;

        sorted.forEach((item) => {
            totalNgCount += item.count;
            const hasCount = item.count > 0;
            const btnClass = hasCount ? 'btn-danger shadow-sm' : 'btn-outline-secondary';
            const badgeClass = hasCount ? 'badge-light text-danger font-weight-bold' : 'badge-secondary';

            html += `
                <div class="defect-btn-wrapper d-inline-flex align-items-center mb-1">
                    <button type="button" class="btn btn-sm ${btnClass} defect-btn-click py-1 px-2" data-key="${item.key}">
                        <span>${item.name}</span>
                        <span class="badge ${badgeClass} ml-1" style="font-size: 0.85rem;">${item.count}</span>
                    </button>
                    ${hasCount ? `
                        <button type="button" class="btn btn-sm btn-outline-danger defect-btn-minus py-1 px-2 ml-1" data-key="${item.key}" title="Kurangi 1">
                            <i class="fas fa-minus"></i>
                        </button>
                    ` : ''}
                </div>
            `;
        });
        html += '</div>';
        html += '<div id="defectHiddenInputs"></div>';

        $container.html(html);

        if (totalNgCount > 0) {
            $("#resetDefectsBtn").show();
        } else {
            $("#resetDefectsBtn").hide();
        }

        this.updateHiddenDefectInputs(sorted);
    }

    updateHiddenDefectInputs(sortedDefects) {
        const $hiddenContainer = $("#defectHiddenInputs");
        $hiddenContainer.empty();

        sortedDefects.forEach((item) => {
            if (item.count > 0) {
                $hiddenContainer.append(
                    `<input type="hidden" name="defect_types[]" value="${item.name}">` +
                    `<input type="hidden" name="defect_quantities[]" value="${item.count}">`
                );
            }
        });
    }

    handleDefectClick(e) {
        e.preventDefault();
        const key = $(e.currentTarget).data("key");
        const item = this.defectItems ? this.defectItems.find((d) => d.key === key || d.name === key) : null;
        if (item) {
            item.count++;
            this.renderDefectButtons();
            this.calculateTotalNG();
        }
    }

    handleDefectMinus(e) {
        e.preventDefault();
        const key = $(e.currentTarget).data("key");
        const item = this.defectItems ? this.defectItems.find((d) => d.key === key || d.name === key) : null;
        if (item && item.count > 0) {
            item.count--;
            this.renderDefectButtons();
            this.calculateTotalNG();
        }
    }

    resetAllDefects() {
        if (this.defectItems) {
            this.defectItems.forEach((d) => (d.count = 0));
        }
        this.renderDefectButtons();
        this.calculateTotalNG();
    }

    normalizeStandardValue(val) {
        if (val === null || val === undefined || val === '') return null;
        return val.toString()
            .replace(',', '.')
            .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
            .trim();
    }

    validateDimensions() {
        const selectedOption = $("#itemSelect").find("option:selected");
        const itemPartNumber = this.normalizePartNumber(selectedOption.data("part-number"));

        // Prioritize standards dari option, fallback ke global config
        let dimensionStandards = selectedOption.data("dimension-standards");
        if (typeof dimensionStandards === 'string') {
            try { dimensionStandards = JSON.parse(dimensionStandards); }
            catch (e) { dimensionStandards = null; }
        }
        if (!dimensionStandards) {
            dimensionStandards = (this.config.partDimensionStandards || {})[itemPartNumber];
        }

        let anyNG = false;

        $('input[name^="dimensions"]').each((_, input) => {
            const $input = $(input);
            const name = $input.attr('name');
            const match = name.match(/\[(\d+)\]\[(\d+)\]/);
            if (!match) return;

            const point = match[2];

            // Robust lookup — support array or object format
            let standard = null;
            if (dimensionStandards) {
                if (Array.isArray(dimensionStandards)) {
                    standard = dimensionStandards.find(s => String(s.point) === String(point))
                        || dimensionStandards[point - 1];
                } else {
                    standard = dimensionStandards[point];
                }
            }

            const valStr = $input.val().trim();
            const value = parseFloat(valStr.replace(',', '.'));

            $input.removeClass('is-invalid is-valid text-danger font-weight-bold');

            if (standard && valStr !== '' && !isNaN(value)) {
                let isInvalid = false;
                const epsilon = 0.00001;

                // 1. Min / Max absolut
                if (standard.min != null && standard.min !== '') {
                    const minBound = parseFloat(String(standard.min).replace(',', '.'));
                    if (!isNaN(minBound) && value < minBound - epsilon) isInvalid = true;
                }
                if (!isInvalid && standard.max != null && standard.max !== '') {
                    const maxBound = parseFloat(String(standard.max).replace(',', '.'));
                    if (!isNaN(maxBound) && value > maxBound + epsilon) isInvalid = true;
                }

                // 2. Size ± Tolerance (dengan split "/" untuk toleransi asimetris)
                if (!isInvalid &&
                    standard.size != null && standard.tolerance != null &&
                    standard.size !== '' && standard.tolerance !== '') {
                    const stdSzStr = this.normalizeStandardValue(standard.size);
                    if (!stdSzStr.startsWith('+') && !stdSzStr.startsWith('-')) {
                        const base = parseFloat(stdSzStr);
                        const tol = this.normalizeStandardValue(standard.tolerance);
                        let lb = base, ub = base;

                        if (tol.includes('/')) {
                            tol.split('/').forEach(p => {
                                p = this.normalizeStandardValue(p);
                                const fv = parseFloat(p);
                                if (p.startsWith('+') || fv > 0) ub = base + Math.abs(fv);
                                else if (p.startsWith('-') || fv < 0) lb = base - Math.abs(fv);
                            });
                        } else if (tol.startsWith('+')) {
                            ub = base + parseFloat(tol.substring(1));
                        } else if (tol.startsWith('-')) {
                            lb = base + parseFloat(tol);
                        } else {
                            const tv = parseFloat(tol);
                            lb = base - tv;
                            ub = base + tv;
                        }

                        if (value < lb - epsilon || value > ub + epsilon) isInvalid = true;
                    }
                }

                // 3. Size dengan prefix +/- (tanpa tolerance)
                if (!isInvalid && standard.size != null && standard.size !== '') {
                    const sz = String(standard.size);
                    if (sz.startsWith('+') || sz.startsWith('-')) {
                        const op = sz.charAt(0);
                        const bound = parseFloat(sz.substring(1));
                        if (!isNaN(bound)) {
                            if (op === '+' && value < bound - epsilon) isInvalid = true;
                            else if (op === '-' && value > bound + epsilon) isInvalid = true;
                        }
                    }
                }

                if (isInvalid) {
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

    updateCavityRows(cavityCount, pointCount = 5) {
        const tbody = $("#dimensionBody");
        const theadRow = $("#dimensionHeadRow");
        tbody.empty();

        let headerHtml = '<th style="min-width:100px;position:sticky;left:0;z-index:2;background:#f8f9fa;">Cavity</th>';
        for (let j = 1; j <= pointCount; j++)
            headerHtml += `<th class="point-header">Point ${j}</th>`;
        theadRow.html(headerHtml);

        for (let i = 1; i <= cavityCount; i++) {
            let rowHtml = `<tr class="cavity-row" data-cavity="${i}"><td class="text-center font-weight-bold bg-light" style="position:sticky;left:0;z-index:1;">Cav ${i}</td>`;
            for (let j = 1; j <= pointCount; j++) {
                rowHtml += `<td class="point-cell"><input type="text" class="form-control form-control-sm dimension-input" style="min-width:60px;" name="dimensions[${i}][${j}]" placeholder="P${j}"></td>`;
            }
            rowHtml += '</tr>';
            tbody.append(rowHtml);
        }
    }

    initWeightCavities(count) {
        count = Math.min(Math.max(1, parseInt(count) || 1), 8);
        const container = $("#weightCavContainer");
        container.empty();
        for (let i = 1; i <= count; i++) {
            container.append(
                `<div class="weight-cav-row" style="display:flex;align-items:center;margin-bottom:6px;gap:8px;">
                    <span style="font-size:0.85rem;font-weight:600;color:#444;white-space:nowrap;min-width:45px;">CAV ${i}</span>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-center" name="part_weight[]" placeholder="0.00" style="width:100px;flex:none;">
                    <span style="font-size:0.85rem;color:#666;">gr</span>
                </div>`
            );
        }
        // Update badge buttons
        const cnt = $("#weightCavContainer .weight-cav-row").length;
        $("#addWeightCavBtn").prop('disabled', cnt >= 8);
        $("#removeWeightCavBtn").prop('disabled', cnt <= 1);
    }

    initAqlLogic() {
        const _this = this;
        $("#total_qty").on("input", function () {
            const lotSize = parseInt($(this).val()) || 0;
            const sampleSize = _this.getSampleSize(lotSize);
            $("#sampling_qty").val(sampleSize).trigger("input");
        });

        $("#sampling_qty, #total_ok, #total_ng").on("input", () =>
            this.updateJudgment(),
        );
    }

    normalizePartNumber(pn) {
        if (!pn) return "";
        return pn
            .toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, "-")
            .replace(/\s+/g, "")
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
        if (lotSize >= 20) return 20;
        return lotSize; // 100% Check for lots < 20
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
        return { acc: 0, rej: 1 }; // Default for smaller samples
    }

    updateAqlRequirement() {
        const total = parseInt($('input[name="total_qty"]').val()) || 0;
        const sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
        const req = this.getSampleSize(total);
        if (req > 0) {
            $('input[name="sampling_qty"]').attr("placeholder", `Min: ${req}`);
            if (sampling < req)
                $('input[name="sampling_qty"]').addClass("is-invalid");
            else $('input[name="sampling_qty"]').removeClass("is-invalid");
        }
        const aql = this.getAqlLimits(sampling);
        $("#acc_val").text(aql.acc);
        $("#rej_val").text(aql.rej);
        $("#aql_info").show();
        this.config.currentAql = aql;
        this.updateJudgment();
    }

    updateJudgment() {
        const sampling = parseInt($("#sampling_qty").val()) || 0;
        const ng = parseInt($("#total_ng").val()) || 0;
        const isDimensiInvalid = $(".dimension-input.is-invalid").length > 0;

        let hasDimensiDefect = false;
        $(".defect-select").each(function () {
            const text = $(this).find("option:selected").text().toLowerCase();
            if (text === "dimensi" || text === "ng dimensi" || $(this).val() === "dimension") {
                hasDimensiDefect = true;
                return false;
            }
        });

        if (isDimensiInvalid && !hasDimensiDefect) {
            this.autoAddDimensionDefect();
            return;
        } else if (!isDimensiInvalid && hasDimensiDefect) {
            this.autoRemoveDimensionDefect();
            return;
        }

        const aql = this.getAqlLimits(sampling);
        $("#acc_val").text(aql.acc);
        $("#rej_val").text(aql.rej);
        $("#aql_info").show();

        const ok = Math.max(0, sampling - ng);
        $("#total_ok").val(ok);

        const judgmentSelect = $("#judgmentSelect");
        const judgmentBadge = $("#judgmentBadge");

        let res = "";
        if (isDimensiInvalid || ng >= aql.rej) {
            res = "NG";
        } else if (ok > 0 && ng <= aql.acc) {
            res = "OK";
        } else if (ng > 0 && ng < aql.rej) {
            res = "OK";
        }

        if (res) {
            judgmentSelect.val(res);
            if (res === "OK") {
                judgmentBadge
                    .text("OK")
                    .removeClass("d-none text-danger")
                    .addClass("text-success")
                    .css({
                        "border-color": "#28a745",
                        "background-color": "#fff",
                    });
            } else {
                judgmentBadge
                    .text("NG")
                    .removeClass("d-none text-success")
                    .addClass("text-danger")
                    .css({
                        "border-color": "#dc3545",
                        "background-color": "#fff",
                    });
            }
        } else {
            judgmentSelect.val("");
            judgmentBadge.addClass("d-none").text("-");
        }

        const isNG = res === "NG";
        const nextProsesCont = $("#nextProsesContainer, #next_proses_container");
        nextProsesCont.toggle(isNG);
        if (isNG) {
            $("#nextProses, #next_proses").removeAttr("required");
        } else {
            $("#nextProses, #next_proses").val("");
        }

        $("#saveBtn").prop("disabled", !res);
    }

    autoAddDimensionDefect() {
        if (!this.defectItems) return;
        let item = this.defectItems.find(d => d.key === 'DIMENSI' || d.key === 'dimension' || (d.name && d.name.toLowerCase() === 'dimensi'));
        if (!item) {
            item = { key: 'DIMENSI', name: 'Dimensi', count: 0 };
            this.defectItems.push(item);
        }
        if (item.count <= 0) {
            item.count = 1;
            this.renderDefectButtons();
            this.calculateTotalNG();
        }
    }

    autoRemoveDimensionDefect() {
        if (!this.defectItems) return;
        let item = this.defectItems.find(d => d.key === 'DIMENSI' || d.key === 'dimension' || (d.name && d.name.toLowerCase() === 'dimensi'));
        if (item && item.count > 0) {
            item.count = 0;
            this.renderDefectButtons();
            this.calculateTotalNG();
        }
    }

    initDefectManagement() {
        $(document).on("click", ".defect-btn-click", (e) => this.handleDefectClick(e));
        $(document).on("click", ".defect-btn-minus", (e) => this.handleDefectMinus(e));
        $(document).on("click", "#resetDefectsBtn", () => this.resetAllDefects());
    }

    calculateTotalNG() {
        let total = 0;
        if (this.defectItems && this.defectItems.length > 0) {
            this.defectItems.forEach((d) => {
                total += (parseInt(d.count) || 0);
            });
        } else {
            $(".defect-qty").each(function () {
                total += parseInt($(this).val()) || 0;
            });
        }
        $("#total_ng").val(total).trigger("input");
    }

    checkMandatoryDimensions() {
        // FPA: field dimensi tidak mandatory — boleh dikosongkan (seperti in-process).
        // Validasi visual (is-invalid/is-valid) tetap berjalan via validateDimensions(),
        // namun tidak memblokir submit.
        return true;
    }


    initFormValidation() {
        const _this = this;
        $("#checksheetForm").on("submit", function (e) {
            e.preventDefault();

            const judgment = $("#judgmentSelect").val();
            const nextProses = $("#nextProses").val();
            const codeMachine = $("#code_machine").val();
            const category = $("#categoryInput").val() || $('select[name="category"]').val();
            const itemId = $("#itemSelect").val();

            // 1. Validasi: Item harus dipilih
            if (!itemId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Item Belum Dipilih',
                });
                $("#itemSelect").addClass("is-invalid").focus();
                setTimeout(() => $("#itemSelect").removeClass("is-invalid"), 3000);
                return false;
            }

            // 2. Validasi: Mesin harus dipilih
            if (!codeMachine) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Mesin Belum Dipilih',
                });
                $("#code_machine").addClass("is-invalid").focus();
                setTimeout(() => $("#code_machine").removeClass("is-invalid"), 3000);
                return false;
            }

            // 2b. Validasi: Kategori harus dipilih
            if (!category) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kategori Belum Dipilih',
                });
                $("#categoryInput, select[name='category']").addClass("is-invalid").focus();
                setTimeout(() => $("#categoryInput, select[name='category']").removeClass("is-invalid"), 3000);
                return false;
            }

            // 3. Validasi: Total Qty
            const totalQty = $("#total_qty").val();
            if (!totalQty || totalQty <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Total Qty Belum Diisi',
                });
                $("#total_qty").addClass("is-invalid").focus();
                setTimeout(() => $("#total_qty").removeClass("is-invalid"), 3000);
                return false;
            }

            // 4. Validasi: Sampling Qty
            const samplingQty = $("#sampling_qty").val();
            if (!samplingQty || samplingQty <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sampling Qty Belum Diisi',
                });
                $("#sampling_qty").addClass("is-invalid").focus();
                setTimeout(() => $("#sampling_qty").removeClass("is-invalid"), 3000);
                return false;
            }

            // 5. Validasi: NG harus pilih Next Proses (Kecuali jika defect HANYA Dimensi)
            const isDimensiType = (t) => !!t && /dimensi|dimension/i.test(t.trim());
            let isOnlyDimensi = true;
            let hasAnyDefect = false;
            if (_this.defectItems) {
                _this.defectItems.forEach((d) => {
                    if (d.count > 0) {
                        hasAnyDefect = true;
                        if (!isDimensiType(d.name || d.key)) {
                            isOnlyDimensi = false;
                        }
                    }
                });
            }

            if (judgment === 'NG' && !nextProses && !(hasAnyDefect && isOnlyDimensi)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Next Proses Wajib Dipilih',
                });
                $("#nextProses").addClass("is-invalid").focus();
                setTimeout(() => $("#nextProses").removeClass("is-invalid"), 3000);
                return false;
            }

            // 6. Validasi: Inisial Operator
            const operatorInitials = $('input[name="operator_initials"]').val();
            if (!operatorInitials) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Inisial Belum Diisi',
                });
                $('input[name="operator_initials"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="operator_initials"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 7. Validasi: Pilihan Defect (NG)
            const ngCount = parseInt($("#total_ng").val()) || 0;
            const hasAnyDefectSelected = (_this.defectItems && _this.defectItems.some(item => item.count > 0)) ||
                $('input[name="defect_quantities[]"]').toArray().some(input => (parseInt($(input).val()) || 0) > 0);

            if ((judgment === "NG" || ngCount > 0) && !hasAnyDefectSelected) {
                Swal.fire({
                    icon: "warning",
                    title: "Defect Belum Dipilih",
                    text: "Silahkan klik tombol jenis defect yang terjadi."
                });
                return false;
            }

            if (!_this.checkMandatoryDimensions()) return false;

            if (_this.timer && _this.timer.isRunning) {
                clearInterval(_this.timer.interval);
                _this.timer.isRunning = false;
                $("#cycleTimeInput").val(_this.timer.elapsed);
            }

            const $saveBtn = $("#saveBtn");
            const originalHtml = $saveBtn.html();
            $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            const formData = new FormData(this);
            $.ajax({
                url: $(this).attr("action"),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data Berhasil Disimpan',
                            showCancelButton: true,
                            confirmButtonText: 'Lihat Data',
                        }).then((result) => {
                            if (result.isConfirmed)
                                window.location.href = response.index_url;
                            else _this.resetForm();
                        });
                    }
                },
                error: function (xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message : 'Gagal menyimpan data.';
                    Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
                    $saveBtn.prop('disabled', false).html(originalHtml);
                },
            });
        });
    }

    resetForm() {
        $("#checksheetForm")[0].reset();

        if (this.timer) {
            clearInterval(this.timer.interval);
            this.timer.isRunning = false;
            this.timer.elapsed = 0;
            this.updateTimerDisplay();
        }

        $("#startTimerBtn")
            .removeClass("btn-secondary").addClass("btn-success")
            .removeAttr("disabled")
            .html('<i class="fas fa-play"></i> Start');

        this.lockInputs();
        $("#saveBtn").prop('disabled', true);
        this.resetAllDefects();

        // Clear standard and similar PDF canvases and reset display
        $("#standardPdfCanvas, #similarPdfCanvas").addClass('d-none').css('display', '');
        $("#standardPdfPlaceholder").show().find('p').text('Pilih Item untuk menampilkan Standard PDF');
        $("#similarPdfPlaceholder").show().find('p').text('Pilih Item untuk menampilkan Dimensi Part');
        $(".standard-nav-controls, .similar-nav-controls, #fullStandardBtn, #fullSimilarBtn").hide();

        // Clear loaded PDF references
        this.pdf.standard.doc = null;
        this.pdf.similar.doc = null;

        $("#judgmentBadge").addClass("d-none").text("-");
        $("#judgmentSelect").val("").removeClass("text-success text-danger");
        
        // Reset item selection and sync search autocomplete widget
        const itemSelect = $("#itemSelect");
        itemSelect.val("");
        if (itemSelect[0] && typeof itemSelect[0]._ipsReset === 'function') {
            itemSelect[0]._ipsReset();
        }
        itemSelect.trigger("change");

        $("#sapCodeInput").val("").removeClass("is-valid is-invalid");
        $("#sapCodeInputHidden").val("");
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
        $("#editAddCavityBtn").on("click", () => this.addCavity());
        $("#editAddPointBtn").on("click", () => this.addPoint());
        $(document).on("input", ".edit-dimension-input", () =>
            this.validateDimensions(),
        );
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
            $("#editDimensionBody").append(newRow);
        } else {
            alert("Maximum 50 cavities reached");
        }
    }

    addPoint() {
        if (this.config.currentPoints < this.config.maxPoints) {
            this.config.currentPoints++;
            $("#editDimensionHeadRow").append(
                `<th class="point-header">Point ${this.config.currentPoints}</th>`,
            );
            $(".edit-cavity-row").each((_, row) => {
                let cavityNum = $(row).data("cavity");
                $(row).append(`<td class="point-cell">
                    <input type="text" class="form-control form-control-sm edit-dimension-input"
                        style="min-width: 60px;"
                        name="dimensions[${cavityNum}][${this.config.currentPoints}]"
                        placeholder="P${this.config.currentPoints}">
                </td>`);
            });
        } else {
            alert("Maximum 50 points reached");
        }
    }

    initAqlLogic() {
        $("#total_qty").on("input", (e) => {
            const lotSize = parseInt($(e.target).val()) || 0;
            const sampleSize = this.getSampleSize(lotSize);
            $("#sampling_qty").val(sampleSize).trigger("input");
        });

        $("#sampling_qty, #total_ng").on("input", () => this.updateJudgment());
        $("#judgmentSelect").on("change", () => this.toggleNextProses());
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

    updateJudgment() {
        const sampling = parseInt($("#sampling_qty").val()) || 0;
        const ng = parseInt($("#total_ng").val()) || 0;
        const isDimensiInvalid =
            $(".edit-dimension-input.is-invalid").length > 0;

        let hasDimensiDefect = false;
        $(".defect-select").each(function () {
            const text = $(this).find("option:selected").text().toLowerCase();
            if (text === "dimensi" || $(this).val() === "dimension") {
                hasDimensiDefect = true;
                return false;
            }
        });

        if (isDimensiInvalid && !hasDimensiDefect) {
            this.autoAddDimensionDefect();
            return;
        } else if (!isDimensiInvalid && hasDimensiDefect) {
            this.autoRemoveDimensionDefect();
            return;
        }

        if (sampling >= ng) {
            $("#total_ok").val(sampling - ng);
        } else {
            $("#total_ok").val(Math.max(0, sampling - ng));
        }

        const limits = this.getAqlLimits(sampling);
        $("#acc_val").text(limits.acc);
        $("#rej_val").text(limits.rej);
        $("#aql_info").show();

        const judgmentVal = $("#judgmentSelect, #judgment");

        if (ng > 0 || sampling > 0 || isDimensiInvalid) {
            if (isDimensiInvalid || ng >= limits.rej) {
                judgmentVal.val("NG");
                judgmentBadge
                    .text("NG")
                    .removeClass("d-none text-success")
                    .addClass("text-danger")
                    .css({
                        "border-color": "#dc3545",
                        "background-color": "#fff",
                    });
            } else {
                judgmentVal.val("OK");
                judgmentBadge
                    .text("OK")
                    .removeClass("d-none text-danger")
                    .addClass("text-success")
                    .css({
                        "border-color": "#28a745",
                        "background-color": "#fff",
                    });
            }
        } else {
            judgmentVal.val("");
            judgmentBadge.addClass("d-none").text("-");
        }
        this.toggleNextProses();
    }

    autoAddDimensionDefect() {
        let foundRow = null;
        $(".defect-select").each(function () {
            const val = $(this).val();
            const text = $(this).find("option:selected").text().toLowerCase();
            if (val === "dimension" || text === "dimensi") {
                foundRow = $(this).closest(".defect-row");
                return false;
            }
        });

        if (foundRow) {
            const qtyInput = foundRow.find(".defect-qty");
            if (!qtyInput.val() || parseInt(qtyInput.val()) <= 0) {
                qtyInput.val(1).trigger("input");
            }
            return;
        }

        let targetSelect = null;
        $(".defect-select").each(function () {
            if ($(this).val() === "") {
                targetSelect = $(this);
                return false;
            }
        });

        if (!targetSelect) {
            $("#editAddDefectBtn").trigger("click");
            targetSelect = $(".defect-select").last();
        }

        if (targetSelect) {
            let foundVal = "";
            targetSelect.find("option").each(function () {
                if (
                    $(this).val() === "dimension" ||
                    $(this).text().toLowerCase() === "dimensi"
                ) {
                    foundVal = $(this).val();
                    return false;
                }
            });
            if (!foundVal) {
                targetSelect.append(
                    '<option value="dimension">Dimensi</option>',
                );
                foundVal = "dimension";
            }
            targetSelect.val(foundVal).trigger("change");
            targetSelect
                .closest(".defect-row")
                .find(".defect-qty")
                .val(1)
                .trigger("input");
            this.calculateTotalNG();
        }
    }

    autoRemoveDimensionDefect() {
        $(".defect-select").each(function () {
            const val = $(this).val();
            const text = $(this).find("option:selected").text().toLowerCase();
            if (val === "dimension" || text === "dimensi") {
                const row = $(this).closest(".defect-row");
                if ($(".defect-row").length > 1) {
                    row.remove();
                } else {
                    row.find(".defect-select").val("");
                    row.find(".defect-qty").val("");
                }
                return false;
            }
        });
        this.calculateTotalNG();
    }

    toggleNextProses() {
        const judgment = $("#judgmentSelect").val() || $("#judgment").val();
        const ngCount = parseInt($("#total_ng").val()) || 0;
        const container = $("#nextProsesContainer, #next_proses_container");
        if (judgment === "NG" || ngCount > 0) {
            container.show();
        } else {
            container.hide();
        }
    }

    normalizePartNumber(pn) {
        if (!pn) return "";
        return pn
            .toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, "-")
            .replace(/\s+/g, "")
            .toUpperCase();
    }

    validateDimensions() {
        const selectedOption = $("#item_id").find("option:selected");
        const rawPartNumber = selectedOption.data("part-number");
        const itemPartNumber = this.normalizePartNumber(rawPartNumber);
        const dimensionStandards =
            this.config.partDimensionStandards[itemPartNumber];

        $(".edit-dimension-input").each((_, input) => {
            const $input = $(input);
            const name = $input.attr("name");
            const match = name.match(/\[(\d+)\]\[(\d+)\]/);
            if (!match) return;

            const point = match[2];
            const standard = dimensionStandards
                ? dimensionStandards[point]
                : null;
            const valStr = $input.val().trim();
            const value = parseFloat(valStr.replace(",", "."));

            if (standard && valStr !== "" && !isNaN(value)) {
                let isInvalid = false;
                if (standard.min !== null && value < standard.min)
                    isInvalid = true;
                if (standard.max !== null && value > standard.max)
                    isInvalid = true;
                if (standard.min === null && standard.max === null) {
                    if (standard.size !== null && standard.tolerance !== null) {
                        const lowerBound = standard.size - standard.tolerance;
                        const upperBound = standard.size + standard.tolerance;
                        if (value < lowerBound || value > upperBound)
                            isInvalid = true;
                    }
                }
                if (isInvalid) {
                    $input.addClass("is-invalid").removeClass("is-valid");
                } else {
                    $input.addClass("is-valid").removeClass("is-invalid");
                }
            } else {
                $input.removeClass("is-invalid is-valid");
            }
        });

        this.updateJudgment();
    }

    initDefectManagement() {
        this.defaultDefects = [
            { value: "scratch", text: "BARET" },
            { value: "silver", text: "SILVER" },
            { value: "flow", text: "FLOW" },
            { value: "flash", text: "FLASH" },
            { value: "shoot_mold", text: "SHOOT MOLD" },
            { value: "bending", text: "BENDING" },
            { value: "sinkmark", text: "SINKMARK" },
            { value: "dimension", text: "Dimensi" },
        ];

        $("#editAddDefectBtn").on("click", () => this.addDefectRow());
        $(document).on("click", ".remove-defect-btn", (e) =>
            this.removeDefectRow(e),
        );
        $(document).on("input", ".defect-qty", () => this.calculateTotalNG());
        $("#total_ng").on("input", () => this.handleNgInput());
    }

    updateDefectOptions() {
        const selectedOption = $("#item_id").find("option:selected");
        let defectsData = selectedOption.data("defects");

        if (typeof defectsData === "string") {
            try {
                defectsData = JSON.parse(defectsData);
            } catch (e) {
                defectsData = [];
            }
        }

        $(".defect-select").each((_, select) => {
            const $select = $(select);
            const currentVal = $select.val();
            $select
                .empty()
                .append('<option value="">-- Pilih Defect --</option>');

            if (Array.isArray(defectsData) && defectsData.length > 0) {
                $.each(defectsData, (_, value) => {
                    const displayValue = value.toLowerCase() === 'dimension' ? 'Dimensi' : value;
                    $select.append(
                        `<option value="${displayValue}">${displayValue}</option>`,
                    );
                });
            } else {
                $.each(this.defaultDefects, (_, defect) => {
                    $select.append(
                        `<option value="${defect.text}">${defect.text}</option>`,
                    );
                });
            }
            if (currentVal) {
                const normalizedVal = currentVal.toLowerCase() === 'dimension' ? 'Dimensi' : currentVal;
                $select.val(normalizedVal);
            }
        });
    }

    addDefectRow() {
        const rowCount = $(".defect-row").length;
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
            $("#editDefectContainer").append(newRow);
            this.updateDefectOptions();
        }
    }

    removeDefectRow(e) {
        $(e.currentTarget).closest(".defect-row").remove();
        this.calculateTotalNG();
    }

    calculateTotalNG() {
        let total = 0;
        $(".defect-qty").each(function () {
            total += parseInt($(this).val()) || 0;
        });
        $("#total_ng").val(total).trigger("input");
        if (total >= 0 || $(".defect-row").length > 0)
            $("#editAddDefectBtn").show();
    }

    handleNgInput() {
        const ng = parseInt($("#total_ng").val()) || 0;
        if (ng > 0) {
            $("#editAddDefectBtn").show();
            if ($(".defect-row").length === 0) this.addDefectRow();
        }
    }

    initWeightHandling() {
        this.editMaxWeightCav = 8;
        $("#editAddWeightCavBtn").on("click", () => this.addWeightCav());
        $("#editRemoveWeightCavBtn").on("click", () => this.removeWeightCav());
        this.updateWeightCavBadge();
    }

    buildWeightCavRow(cavNum, value = "") {
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
        const cnt = $("#editWeightCavContainer .edit-weight-cav-row").length;
        $("#editWeightCavCount").text(`${cnt} Cav`);
        $("#editAddWeightCavBtn").prop(
            "disabled",
            cnt >= this.editMaxWeightCav,
        );
        $("#editRemoveWeightCavBtn").prop("disabled", cnt <= 1);
    }

    addWeightCav() {
        const cnt = $("#editWeightCavContainer .edit-weight-cav-row").length;
        if (cnt >= this.editMaxWeightCav) return;
        $("#editWeightCavContainer").append(this.buildWeightCavRow(cnt + 1));
        this.updateWeightCavBadge();
    }

    removeWeightCav() {
        const rows = $("#editWeightCavContainer .edit-weight-cav-row");
        if (rows.length <= 1) return;
        rows.last().remove();
        this.updateWeightCavBadge();
    }

    initItemHandling() {
        $("#item_id")
            .on("change", (e) => {
                const $opt = $(e.currentTarget).find("option:selected");
                const customer = $opt.data("customer") || "";
                const weightStandard = $opt.data("weight-standard");

                // Berat Part Logic
                if (
                    customer &&
                    (customer.toUpperCase().includes("ASTRA HONDA MOTOR") ||
                        customer.toUpperCase().includes("AHM"))
                ) {
                    $("#editBeratPartContainer").show();
                    if (weightStandard) {
                        $("#editWeightStandardDisplay").text(weightStandard);
                        $("#editWeightStandardBadge").show();
                    } else {
                        $("#editWeightStandardBadge").hide();
                    }
                } else {
                    $("#editBeratPartContainer").hide();
                    $("#editWeightCavContainer input").val("");
                    $("#editWeightStandardBadge").hide();
                }

                // PDF Link Logic
                this.updatePdfLink($opt);

                this.updateDefectOptions();
                this.validateDimensions();
            })
            .trigger("change");
        this.updateDefectOptions();
    }

    initFormValidation() {
        $("#checksheetForm, #editChecksheetForm").on("submit", (e) => {
            const judgment = $("#judgmentSelect").val() || $("#judgment").val();
            const nextProses = $("#nextProses").val() || $("#next_proses").val();
            const itemId = $("#itemSelect").val();
            const codeMachine = $("#code_machine").val();
            const category = $("#categoryInput").val() || $('select[name="category"]').val();
            const totalQty = $('input[name="total_qty"]').val();
            const samplingQty = $('input[name="sampling_qty"]').val();
            const operatorInitials = $('input[name="operator_initials"]').val();

            // 1. Validasi: Item harus dipilih
            if (!itemId) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Item Belum Dipilih",
                });
                $("#itemSelect").addClass("is-invalid").focus();
                setTimeout(() => $("#itemSelect").removeClass("is-invalid"), 3000);
                return false;
            }

            // 2. Validasi: Mesin harus dipilih
            if (!codeMachine) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Mesin Belum Dipilih",
                });
                $("#code_machine").addClass("is-invalid").focus();
                setTimeout(() => $("#code_machine").removeClass("is-invalid"), 3000);
                return false;
            }

            // 2b. Validasi: Kategori harus dipilih
            if (!category) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Kategori Belum Dipilih",
                });
                $("#categoryInput, select[name='category']").addClass("is-invalid").focus();
                setTimeout(() => $("#categoryInput, select[name='category']").removeClass("is-invalid"), 3000);
                return false;
            }

            // 3. Validasi: Total Qty
            if (!totalQty || totalQty <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Total Qty Belum Diisi",
                });
                $('input[name="total_qty"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="total_qty"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 4. Validasi: Sampling Qty
            if (!samplingQty || samplingQty <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Sampling Qty Belum Diisi",
                });
                $('input[name="sampling_qty"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="sampling_qty"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 5. Validasi: NG harus pilih Next Proses (Kecuali jika defect HANYA Dimensi)
            const isDimensiType = (t) => !!t && /dimensi|dimension/i.test(t.trim());
            let isOnlyDimensi = true;
            let hasAnyDefect = false;
            $(".defect-row").each(function () {
                const type = $(this).find(".defect-select").val();
                const qty = parseInt($(this).find(".defect-qty").val()) || 0;
                if (qty > 0) {
                    hasAnyDefect = true;
                    if (!isDimensiType(type)) {
                        isOnlyDimensi = false;
                    }
                }
            });

            if (judgment === "NG" && !nextProses && !(hasAnyDefect && isOnlyDimensi)) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Next Proses Wajib Dipilih",
                    text: "Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!",
                    confirmButtonColor: "#3085d6",
                });
                const $nextProses = $("#nextProses").length ? $("#nextProses") : $("#next_proses");
                $nextProses.addClass("is-invalid").focus();
                setTimeout(() => $nextProses.removeClass("is-invalid"), 3000);
                return false;
            }

            // 6. Validasi: Inisial QC
            if (!operatorInitials) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Inisial QC Wajib Diisi",
                });
                $('input[name="operator_initials"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="operator_initials"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 7. Validasi: Pilihan Defect (NG)
            const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;
            const hasAnyNgInput = $(".defect-qty").toArray().some(input => (parseInt($(input).val()) || 0) > 0);

            if (judgment === "NG" || ngCount > 0 || hasAnyNgInput) {
                let defectMissing = false;
                let hasAtLeastOneValidDefect = false;

                $(".defect-row").each(function () {
                    const type = $(this).find(".defect-select").val();
                    const qty = parseInt($(this).find(".defect-qty").val()) || 0;

                    if (qty > 0) {
                        if (!type) {
                            defectMissing = true;
                            $(this).find(".defect-select").addClass("is-invalid");
                        } else {
                            hasAtLeastOneValidDefect = true;
                        }
                    }
                });

                if ((judgment === "NG" || ngCount > 0) && !hasAtLeastOneValidDefect) {
                    Swal.fire({
                        icon: "warning",
                        title: "Defect Belum Dipilih",
                    });
                    return false;
                }

                if (defectMissing) {
                    Swal.fire({
                        icon: "warning",
                        title: "Jenis Defect Belum Dipilih",
                    });
                    return false;
                }
            }

            $("#editChecksheetForm")
                .find(":input:disabled")
                .each((_, input) => {
                    $(input).prop("disabled", false).addClass("was-disabled");
                });
            $("#loadingOverlay").css("display", "flex");
            $("#btnSubmit").prop("disabled", true);
        });

        $(document).on("ajaxComplete ajaxError", () => {
            $(".was-disabled")
                .prop("disabled", true)
                .removeClass("was-disabled");
            $("#loadingOverlay").hide();
            $("#btnSubmit").prop("disabled", false);
        });
    }

    updatePdfLink($opt) {
        const itemId = $opt.val();
        const $link = $("#view-item-pdf");
        if (itemId && this.config.pdfRoutePattern) {
            const url = this.config.pdfRoutePattern
                .replace("ID_PLACEHOLDER", itemId)
                .replace("INDEX_PLACEHOLDER", "0");
            $link.attr("href", url).removeClass("d-none");
        } else {
            $link.addClass("d-none");
        }
    }
}

window.initFpaIndex = (config) => new FpaIndex(config);
window.initFpaCreate = (config) => new FpaCreate(config);
window.initFpaEdit = (config) => new FpaEdit(config);
