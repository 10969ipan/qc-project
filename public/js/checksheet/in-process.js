/**
 * Modul JavaScript InProcess
 * Merangkum logika untuk tampilan index dan create In-Process.
 */

class InProcessIndex {
    constructor(config) {
        this.config = config;
        this.init();
    }

    init() {
        this.initCharacterCounter();
        this.initEditModal();
        this.initStatusModal();
        this.initAjaxForms();
        this.initQRDetail();
        if (this.config && this.config.btnScanId) {
            this.initQRScanner();
        }
    }

    initCharacterCounter() {
        const _this = this;
        $(document).on(
            "input",
            'textarea[name="rejection_remarks"]',
            function () {
                const id = $(this).attr("id").replace("rejection_remarks", "");
                $("#charCount" + id).text(this.value.length);
            },
        );
    }

    initEditModal() {
        $(document).on("click", ".btn-edit-modal", function (e) {
            e.preventDefault();
            const url = $(this).attr("href");
            $("#editModal").modal("show");
            $("#editModalBody").html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>',
            );

            $.ajax({
                url: url,
                success: function (response) {
                    $("#editModalBody").html(response);
                },
                error: function (xhr) {
                    let message = "Gagal memuat data checksheet.";
                    if (xhr.status === 404)
                        message = "Data checksheet tidak ditemukan.";
                    else if (xhr.status === 403)
                        message =
                            "Anda tidak memiliki akses untuk mengedit checksheet ini.";
                    else if (xhr.status === 500)
                        message = "Terjadi kesalahan pada server.";
                    $("#editModalBody").html(
                        '<div class="alert alert-danger">' + message + "</div>",
                    );
                },
            });
        });
    }

    initStatusModal() {
        $(document).on("click", ".btn-status-modal", function (e) {
            e.preventDefault();
            const url = $(this).attr("href");
            $("#statusModal").modal("show");
            $("#statusModalBody").html(
                '<div class="text-center py-5"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>',
            );

            $.ajax({
                url: url,
                success: function (response) {
                    $("#statusModalBody").html(response);
                },
                error: function (xhr) {
                    let message = "Gagal memuat data status approval.";
                    if (xhr.status === 404) message = "Data tidak ditemukan.";
                    else if (xhr.status === 403)
                        message =
                            "Anda tidak memiliki akses untuk mengubah status approval ini.";
                    $("#statusModalBody").html(
                        '<div class="alert alert-danger">' + message + "</div>",
                    );
                },
            });
        });
    }

    initAjaxForms() {
        $(document).on("submit", ".ajax-form", function (e) {
            const $form = $(this);

            e.preventDefault();
            const $submitBtn = $form.find('button[type="submit"]');
            const $modalErrors = $form.find("#modal-errors");
            const $originalBtnHtml = $submitBtn.html();

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
                success: function (response) {
                    if (response.success) {
                        window.location.href =
                            response.redirect || window.location.href;
                    } else {
                        $modalErrors
                            .html(
                                response.message ||
                                "Terjadi kesalahan saat menyimpan data.",
                            )
                            .fadeIn();
                        $submitBtn
                            .prop("disabled", false)
                            .html($originalBtnHtml);
                    }
                },
                error: function (xhr) {
                    $submitBtn.prop("disabled", false).html($originalBtnHtml);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml =
                            '<div class="alert alert-danger"><ul class="mb-0">';
                        if (errors) {
                            $.each(errors, function (field, messages) {
                                errorHtml += "<li>" + messages[0] + "</li>";
                                const $input = $form.find(
                                    '[name="' + field + '"]',
                                );
                                if ($input.length) {
                                    $input.addClass("is-invalid");
                                    if (
                                        !$input.next(".invalid-feedback").length
                                    ) {
                                        $input.after(
                                            '<div class="invalid-feedback">' +
                                            messages[0] +
                                            "</div>",
                                        );
                                    }
                                }
                            });
                        } else {
                            errorHtml +=
                                "<li>" +
                                (xhr.responseJSON.message ||
                                    "Validasi gagal.") +
                                "</li>";
                        }
                        errorHtml += "</ul></div>";
                        $modalErrors.html(errorHtml).fadeIn();
                    } else {
                        const message = xhr.responseJSON
                            ? xhr.responseJSON.message
                            : "Terjadi kesalahan sistem.";
                        $modalErrors
                            .html(
                                '<div class="alert alert-danger">' +
                                message +
                                "</div>",
                            )
                            .fadeIn();
                    }
                    const $modalBody = $form.closest(".modal-body");
                    if ($modalBody.length)
                        $modalBody.animate({ scrollTop: 0 }, "fast");
                    else
                        $("html, body").animate(
                            { scrollTop: $form.offset().top - 100 },
                            "fast",
                        );
                },
            });
        });
    }

    initQRDetail() {
        $(document).on("click", ".btn-qr-detail", function () {
            const data = $(this).data();
            let sapCode = data.sap || "-";
            if ((sapCode === "-" || !sapCode) && data.qr) {
                const parts = data.qr.split("|");
                if (parts.length >= 5) sapCode = parts[4].trim();
            }
            $("#modal-qr-raw").text(data.qr || "-");
            $("#modal-qr-part").text(data.part || "-");
            $("#modal-qr-supplier").text(data.supplier || "-");
            $("#modal-qr-qty").text(data.qty || "-");
            $("#modal-qr-unique").text(data.unique || "-");
            $("#modal-qr-sap").text(sapCode);
            $("#qrModal").modal("show");
        });
    }

    initQRScanner() {
        const _this = this;
        $(document).on("click", this.config.btnScanId, (e) => {
            e.preventDefault();
            this.unlockAudio();
            $(this.config.qrScannerModalId).modal("show");
        });

        $(this.config.qrScannerModalId).on("shown.bs.modal", function () {
            const videoElem = document.getElementById("qr-video");

            if (_this.qrScanner) {
                _this.qrScanner.destroy();
                _this.qrScanner = null;
            }

            _this.qrScanner = new QrScanner(
                videoElem,
                (result) => _this.handleQRScanned(result.data),
                {
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                    maxScansPerSecond: 25,
                    preferredCamera: "environment",
                },
            );

            _this.qrScanner._setVideoMirror = function (facingMode) { };

            $("#toggleMirrorBtn")
                .off("click")
                .on("click", function () {
                    $(videoElem).toggleClass("mirrored");
                });

            _this.qrScanner
                .start()
                .then(() => {
                    _this.qrScanner.hasFlash().then((hasFlash) => {
                        if (hasFlash) $("#toggleFlashBtn").removeClass("d-none");
                    });

                    $("#toggleFlashBtn")
                        .off("click")
                        .on("click", function () {
                            _this.qrScanner.toggleFlash();
                        });

                    const track = _this.qrScanner.$video.srcObject.getVideoTracks()[0];
                    const capabilities = track.getCapabilities ? track.getCapabilities() : {};

                    if (capabilities.zoom) {
                        $("#zoomContainer").removeClass("d-none");
                        const $slider = $("#zoomSlider");
                        $slider
                            .attr({
                                min: capabilities.zoom.min,
                                max: capabilities.zoom.max,
                                step: capabilities.zoom.step || 0.1,
                            })
                            .val(track.getSettings().zoom || capabilities.zoom.min);

                        $slider.off("input").on("input", function () {
                            track.applyConstraints({
                                advanced: [{ zoom: parseFloat($(this).val()) }],
                            });
                        });
                    }
                })
                .catch((err) => {
                    console.error("Scanner error", err);
                });
        });

        $("#qr-input-file").on("change", async function (e) {
            if (e.target.files.length == 0) return;
            const imageFile = e.target.files[0];
            $("#qr-video").addClass("d-none");
            $("#qr-reader-results").removeClass("d-none");

            try {
                const result = await QrScanner.scanImage(imageFile, {
                    returnDetailedScanResult: true,
                });
                _this.handleQRScanned(result.data);
            } catch (err) {
                console.error("Error scanning file:", err);
                Swal.fire({
                    icon: "error",
                    title: "Gagal Membaca QR",
                    text: "Sistem tidak menemukan QR Code pada gambar ini.",
                });
            } finally {
                $(this).val("");
                $("#qr-reader-results").addClass("d-none");
                $("#qr-video").removeClass("d-none");
            }
        });

        $(this.config.qrScannerModalId).on("hidden.bs.modal", () => {
            this.stopScanner();
        });
    }

    stopScanner() {
        if (this.qrScanner) {
            this.qrScanner.stop();
        }
    }

    unlockAudio() {
        try {
            if (!this.audioContext) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) this.audioContext = new AudioContext();
            }
            if (this.audioContext && this.audioContext.state === "suspended") {
                this.audioContext.resume();
            }
        } catch (e) {
            console.warn("AudioContext unlock failed", e);
        }
    }

    playSuccessFeedback() {
        try {
            if (navigator.vibrate) navigator.vibrate(100);
            this.unlockAudio();
            if (this.audioContext) {
                const oscillator = this.audioContext.createOscillator();
                const gain = this.audioContext.createGain();
                oscillator.type = "sine";
                oscillator.frequency.setValueAtTime(880, this.audioContext.currentTime);
                gain.gain.setValueAtTime(0, this.audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.2, this.audioContext.currentTime + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.3);
                oscillator.connect(gain);
                gain.connect(this.audioContext.destination);
                oscillator.start();
                oscillator.stop(this.audioContext.currentTime + 0.3);
            }
        } catch (e) { }
    }

    handleQRScanned(decodedText) {
        this.playSuccessFeedback();
        this.stopScanner();
        $(this.config.qrScannerModalId).modal("hide");
        if (this.config.inputQrId) {
            $(this.config.inputQrId).val(decodedText);
            // Auto submit form
            $(this.config.inputQrId).closest("form").submit();
        }
    }
}

class InProcessCreate {
    constructor(config) {
        this.config = config;
        this.qrScanner = null;
        this.timerInterval = null;
        this.totalSeconds = 0;
        this.timerRunning = false;
        this.pdfCache = {};

        // State Referensi PDF
        this.refStandardPdfDoc = null;
        this.refStandardPageNum = 1;
        this.refStandardFileIndex = 0;
        this.refStandardFiles = [];
        this.refSimilarPdfDoc = null;
        this.refSimilarPageNum = 1;
        this.standardZoomLevel = 1.0;
        this.similarZoomLevel = 1.0;

        // Logika perluasan dimensi
        this.currentCavities = 2;
        this.currentPoints = 5;
        this.maxCavities = 50;
        this.maxPoints = 50;

        // Berat cavity maksimal
        this.MAX_WEIGHT_CAV = 8;

        this.isProcessingScan = false;
        this.scanLockTimeout = null;

        this.init();
    }

    init() {
        this.initQRScanner();
        this.initTimer();
        this.initItemSelect();
        this.initDimensionControls();
        this.initWeightControls();
        this.initDefectList();
        this.initFormValidation();
        this.initZoomLogic();
        this.initPDFReference();
        this.initHardwareScanner();
        this.initMachinePersistence();

        // Fitur antrian scan sementara HANYA untuk Karawang
        if (this.config.useQueue) {
            this.initTempQueue();

            const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
            if (queue.length > 0) {
                this.startTimer();
            } else {
                this.lockInputs();
            }
        } else {
            // Jakarta: langsung lock inputs, tidak ada antrian
            this.lockInputs();
        }

        this.checkUrlParams();

        // Auto focus on scan field on load (Tablet optimized)
        this.applyAutoFocus();

        // Click to refocus helper for tablet users
        $(document).on("click", ".card-body", (e) => {
            if ($(e.target).closest("input, select, textarea, button").length === 0) {
                this.applyAutoFocus();
            }
        });
    }

    applyAutoFocus() {
        setTimeout(() => {
            const $input = $("#sapCodeInput");
            if ($input.length && !this.isProcessingScan) {
                // Prevent virtual keyboard on tablets/phones during auto-focus
                $input.attr('inputmode', 'none');
                $input.focus();

                // If user actually taps the input, allow keyboard
                $input.one('mousedown touchstart', function () {
                    $(this).attr('inputmode', 'text');
                });
            }
        }, 600);
    }

    initTempQueue() {
        const _this = this;

        // Render standard queue on page load
        this.renderQueueTable();

        // Bind Save All Queue button (hanya jika element ada)
        if ($("#btnSaveQueue").length) {
            $("#btnSaveQueue").click(() => {
                this.saveQueueSequentially();
            });
        }

        // Bind Clear Queue button (hanya jika element ada)
        if ($("#btnClearQueue").length) {
            $("#btnClearQueue").click(() => {
                this.clearQueue();
            });
        }

        // Bind delete action for individual row items using event delegation
        $(document).on("click", ".btn-delete-queue-item", function () {
            const idx = $(this).data("index");
            _this.deleteQueueItem(idx);
        });
    }

    addToQueue() {
        // Collect dimensions
        const dimensions = {};
        $('.dimension-input').each(function () {
            const name = $(this).attr('name');
            if (name) {
                const match = name.match(/dimensions\[(\d+)\]\[(\d+)\]/);
                if (match) {
                    const cav = match[1];
                    const pt = match[2];
                    if (!dimensions[cav]) dimensions[cav] = {};
                    dimensions[cav][pt] = $(this).val();
                }
            }
        });

        // Collect part weights
        const part_weights = [];
        $('input[name="part_weight[]"]').each(function () {
            part_weights.push($(this).val());
        });

        // Collect defects
        const defect_types = [];
        $('.defect-select').each(function () {
            defect_types.push($(this).val());
        });
        const defect_quantities = [];
        $('.defect-qty').each(function () {
            defect_quantities.push($(this).val());
        });

        // Build queue item object
        const item = {
            plant: $('input[name="plant"]').val(),
            qrcode: $('#qrcodeInput').val(),
            part_code: $('#partCodeInput').val(),
            supplier_id: $('#supplierIdInput').val(),
            quantity: $('#quantityInput').val(),
            unique_code_id: $('#uniqueCodeInput').val(),
            sap_code: $('#sapCodeInputHidden').val(),
            scan_method: "hardware",
            item_id: $('#itemSelect').val(),
            date: $('input[name="date"]').val(),
            shift: $('select[name="shift"]').val(),
            code_machine: $('#code_machine').val(),
            total_qty: $('input[name="total_qty"]').val(),
            sampling_qty: $('input[name="sampling_qty"]').val(),
            operator_initials: $('input[name="operator_initials"]').val(),
            remarks: $('textarea[name="remarks"]').val(),
            total_ok: $('input[name="total_ok"]').val(),
            total_ng: $('input[name="total_ng"]').val(),
            judgment: $('#judgmentSelect').val(),
            next_proses: $('#nextProses').val(),
            cycle_time: $('#cycleTimeInput').val(),
            dimensions: dimensions,
            part_weight: part_weights,
            defect_types: defect_types,
            defect_quantities: defect_quantities,
            itemNameDisplay: $("#itemSelect option:selected").text().trim()
        };

        const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
        queue.push(item);
        localStorage.setItem('inprocess_scan_buffer', JSON.stringify(queue));

        // Reset and restore
        this.resetForm();
        this.restorePersistentFields();

        // Render queue
        this.renderQueueTable();
    }

    renderQueueTable() {
        const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
        const tbody = $("#tempQueueBody");
        tbody.empty();

        if (queue.length === 0) {
            $("#tempQueueCard").addClass("d-none");
            return;
        }

        $("#tempQueueCard").removeClass("d-none");
        $("#queueBadge").text(`${queue.length} Data`);
        $("#queueCountDisplay").text(queue.length);

        queue.forEach((item, index) => {
            const judgmentClass = item.judgment === 'OK' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
            const initialsUpper = (item.operator_initials || '-').toUpperCase();
            const tr = `
                <tr>
                    <td>${index + 1}</td>
                    <td class="text-left font-weight-bold" style="word-break: break-all;">${item.qrcode || '-'}</td>
                    <td>Mesin ${item.code_machine || '-'}</td>
                    <td>${item.total_qty || '0'}</td>
                    <td><span class="${judgmentClass}">${item.judgment || '-'}</span></td>
                    <td>${initialsUpper}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-xs btn-delete-queue-item" data-index="${index}" title="Hapus data">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });
    }

    deleteQueueItem(index) {
        const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
        if (index >= 0 && index < queue.length) {
            queue.splice(index, 1);
            localStorage.setItem('inprocess_scan_buffer', JSON.stringify(queue));
            this.renderQueueTable();
        }
    }

    clearQueue() {
        Swal.fire({
            title: "Kosongkan Daftar Scan?",
            text: "Semua data scan sementara akan dihapus dari browser ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Hapus Semua!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.removeItem('inprocess_scan_buffer');
                this.renderQueueTable();
                this.applyAutoFocus();
            }
        });
    }

    saveQueueSequentially() {
        const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
        if (queue.length === 0) {
            Swal.fire("Daftar Kosong", "Tidak ada data untuk disimpan.", "info");
            return;
        }

        Swal.fire({
            title: 'Pilih Next Proses',
            text: 'Silakan pilih tujuan proses berikutnya:',
            icon: 'question',
            input: 'select',
            inputOptions: {
                'WIP': 'WIP',
                'FG': 'FG'
            },
            inputPlaceholder: '- Pilih Tujuan -',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#858796',
            inputValidator: (value) => {
                if (!value) {
                    return 'Anda harus memilih tujuan proses terlebih dahulu!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const batchNextProses = result.value;
                this.executeSequentialSave(batchNextProses);
            }
        });
    }

    async executeSequentialSave(batchNextProses) {
        const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
        if (queue.length === 0) return;

        $("#saveProgressContainer").removeClass("d-none");
        $("#btnSaveQueue").prop("disabled", true);
        $("#btnClearQueue").prop("disabled", true);
        $(".btn-delete-queue-item").prop("disabled", true);

        let successCount = 0;
        let failedIndex = -1;
        let errorMessage = "";

        const formActionUrl = $("#checksheetForm").attr("action");

        for (let i = 0; i < queue.length; i++) {
            const percent = Math.round((i / queue.length) * 100);
            $("#saveProgressBar").css("width", percent + "%").attr("aria-valuenow", percent).text(percent + "%");
            $("#saveProgressText").text(`Menyimpan data ${i + 1} dari ${queue.length}...`);

            const item = queue[i];
            const formData = new FormData();
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
            formData.append('_token', csrfToken);

            function appendToFormData(fd, data, parentKey) {
                if (data === null || data === undefined) return;

                if (Array.isArray(data)) {
                    data.forEach((val) => {
                        fd.append(parentKey + '[]', val);
                    });
                } else if (typeof data === 'object' && !(data instanceof File)) {
                    Object.keys(data).forEach(key => {
                        const fullKey = parentKey ? `${parentKey}[${key}]` : key;
                        appendToFormData(fd, data[key], fullKey);
                    });
                } else {
                    fd.append(parentKey, data);
                }
            }

            Object.keys(item).forEach(key => {
                if (key === 'itemNameDisplay') return;
                if (key === 'next_proses') return; // Override original value
                appendToFormData(formData, item[key], key);
            });
            formData.append('tujuan', batchNextProses);

            try {
                await new Promise((resolve, reject) => {
                    $.ajax({
                        url: formActionUrl,
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.success) {
                                successCount++;
                                resolve(response);
                            } else {
                                reject(new Error(response.message || "Gagal menyimpan data."));
                            }
                        },
                        error: function (xhr) {
                            const msg = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "Gagal menyimpan data.";
                            reject(new Error(msg));
                        }
                    });
                });
            } catch (error) {
                failedIndex = i;
                errorMessage = error.message;
                break;
            }
        }

        $("#saveProgressContainer").addClass("d-none");
        $("#btnSaveQueue").prop("disabled", false);
        $("#btnClearQueue").prop("disabled", false);
        $(".btn-delete-queue-item").prop("disabled", false);

        if (failedIndex === -1) {
            localStorage.removeItem('inprocess_scan_buffer');

            //  call AMR logic
            if (this.config.plantContext === 'karawang') {
                const machineVal = queue.length > 0 ? queue[0].code_machine : $('#code_machine').val();
                const fromLoc = machineVal ? ('MESIN-' + machineVal) : '';
                const toLoc = batchNextProses;
                if (fromLoc && toLoc && (toLoc === 'WIP' || toLoc === 'FG')) {
                    $.ajax({
                        url: 'http://192.168.230.38:1880/api/bawa-box',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ from: fromLoc.toUpperCase(), to: toLoc.toUpperCase() }),
                        success: function (res) {
                            if (res.ok) {
                                console.log('Memanggil AMR Berhasil. OrderKey:', res.orderKey);
                            } else {
                                console.warn('Respon AMR Gagal:', res);
                            }
                        },
                        error: function (xhr) {
                            console.error('AMR Error:', xhr);
                        }
                    });
                }
            }


            Swal.fire({
                icon: "success",
                title: "Semua Berhasil Disimpan",
                text: `Berhasil menyimpan ${successCount} data ke database!`,
            }).then(() => {
                this.applyAutoFocus();
            });
            this.renderQueueTable();
        } else {
            const remainingQueue = queue.slice(failedIndex);
            localStorage.setItem('inprocess_scan_buffer', JSON.stringify(remainingQueue));

            Swal.fire({
                icon: "error",
                title: `Penyimpanan Terhenti di Data ke-${failedIndex + 1}`,
                text: `Error: ${errorMessage}. Sisa ${remainingQueue.length} data tetap aman di dalam tabel.`,
            }).then(() => {
                this.applyAutoFocus();
            });
            this.renderQueueTable();
        }
    }

    restorePersistentFields() {
        const fields = [
            { id: "code_machine", key: "last_machine_selection" },
            { id: "shiftSelect", key: "last_shift_selection", name: "shift" }
        ];
        fields.forEach(field => {
            const $el = field.id ? $("#" + field.id) : $(`input[name="${field.name}"], select[name="${field.name}"]`);
            if ($el.length) {
                const savedVal = localStorage.getItem(field.key);
                if (savedVal) {
                    $el.val(savedVal).trigger("change");
                }
            }
        });
    }

    lockInputs() {
        this.formInputs = $(
            '#checksheetForm input:not([type="hidden"]):not(#startTimerBtn):not(#sapCodeInput), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)',
        );
        this.formInputs.prop("disabled", true);
        $("#checksheetForm").addClass("inputs-locked");
        if (!$("#inputsLockedStyles").length) {
            $(
                '<style id="inputsLockedStyles">#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>',
            ).appendTo("head");
        }
    }

    unlockInputs() {
        this.formInputs = $(
            '#checksheetForm input:not([type="hidden"]):not(#startTimerBtn):not(#sapCodeInput), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)',
        );
        this.formInputs.prop("disabled", false);
        $("#checksheetForm").removeClass("inputs-locked");
        $("#saveBtn").prop("disabled", false);
        // sapCodeInput is always unlocked - reset placeholder after unlock
        $("#sapCodeInput").prop("disabled", false);
    }

    initQRScanner() {
        const _this = this;
        $("#btnScanQR").click(() => {
            // Unlock AudioContext for mobile browsers
            this.unlockAudio();
            $("#qrScannerModal").modal("show");
        });

        $("#qrScannerModal").on("shown.bs.modal", function () {
            const videoElem = document.getElementById("qr-video");

            // Bersihkan instance lama jika ada
            if (_this.qrScanner) {
                _this.qrScanner.destroy();
                _this.qrScanner = null;
            }

            _this.qrScanner = new QrScanner(
                videoElem,
                (result) => _this.handleQRScanned(result.data),
                {
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                    maxScansPerSecond: 25,
                    preferredCamera: "environment",
                },
            );

            // Override internal mirror logic to prevent auto-mirror
            _this.qrScanner._setVideoMirror = function (facingMode) {
                // Do nothing, we handle mirroring manually via CSS
            };

            // Handle manual flip button
            $("#toggleMirrorBtn")
                .off("click")
                .on("click", function () {
                    $(videoElem).toggleClass("mirrored");
                });

            _this.qrScanner
                .start()
                .then(() => {
                    // Check if device has flash
                    _this.qrScanner.hasFlash().then((hasFlash) => {
                        if (hasFlash) {
                            $("#toggleFlashBtn").removeClass("d-none");
                        }
                    });

                    // Handle flash button
                    $("#toggleFlashBtn")
                        .off("click")
                        .on("click", function () {
                            _this.qrScanner.toggleFlash();
                        });

                    // Handle Zoom Control
                    const track =
                        _this.qrScanner.$video.srcObject.getVideoTracks()[0];
                    const capabilities = track.getCapabilities
                        ? track.getCapabilities()
                        : {};

                    if (capabilities.zoom) {
                        $("#zoomContainer").removeClass("d-none");
                        const $slider = $("#zoomSlider");
                        $slider
                            .attr({
                                min: capabilities.zoom.min,
                                max: capabilities.zoom.max,
                                step: capabilities.zoom.step || 0.1,
                            })
                            .val(
                                track.getSettings().zoom ||
                                capabilities.zoom.min,
                            );

                        $slider.off("input").on("input", function () {
                            track.applyConstraints({
                                advanced: [{ zoom: parseFloat($(this).val()) }],
                            });
                        });
                    }
                })
                .catch((err) => {
                    console.error("Scanner error", err);
                    $("#qr-video").hide();
                    const errorMsg = `<div class="alert alert-warning m-3">
                    <b>Kamera tidak dapat diakses:</b> ${err}<br>
                    <small>Pastikan Anda memberikan izin kamera dan menggunakan koneksi aman (HTTPS atau localhost).</small>
                </div>`;
                    if ($("#qr-error-msg").length === 0) {
                        $('<div id="qr-error-msg"></div>')
                            .insertAfter("#qr-video")
                            .html(errorMsg);
                    } else {
                        $("#qr-error-msg").html(errorMsg).show();
                    }
                });
        });

        $("#qr-input-file").on("change", async function (e) {
            if (e.target.files.length == 0) return;
            const imageFile = e.target.files[0];
            $("#qr-video").addClass("d-none");
            $("#qr-reader-results")
                .removeClass("d-none")
                .find("p")
                .text("Memproses QR dari file...");

            try {
                const result = await QrScanner.scanImage(imageFile, {
                    returnDetailedScanResult: true,
                });
                _this.handleQRScanned(result.data);
            } catch (err) {
                console.error("Error scanning file:", err);
                $("#qr-reader-results").addClass("d-none");
                $("#qr-video").removeClass("d-none");
                Swal.fire({
                    icon: "error",
                    title: "Gagal Membaca QR",
                    text: "Sistem tidak menemukan QR Code pada gambar ini.",
                });
            } finally {
                $(this).val("");
            }
        });

        $("#qrScannerModal").on("hidden.bs.modal", () => {
            this.stopScanner();
            $("#qr-reader-results").addClass("d-none");
            $("#qr-video").removeClass("d-none").show();
            if ($("#qr-error-msg").length) $("#qr-error-msg").hide();
        });
    }

    stopScanner() {
        if (this.qrScanner) {
            this.qrScanner.stop();
        }
    }

    unlockAudio() {
        if (!this.audioContext) {
            const AudioContext =
                window.AudioContext || window.webkitAudioContext;
            if (AudioContext) this.audioContext = new AudioContext();
        }
        if (this.audioContext && this.audioContext.state === "suspended") {
            this.audioContext.resume();
        }
    }

    playSuccessFeedback() {
        try {
            if (navigator.vibrate) navigator.vibrate(100);

            this.unlockAudio();
            if (this.audioContext) {
                const oscillator = this.audioContext.createOscillator();
                const gain = this.audioContext.createGain();
                oscillator.type = "sine";
                oscillator.frequency.setValueAtTime(
                    880,
                    this.audioContext.currentTime,
                );
                gain.gain.setValueAtTime(0, this.audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(
                    0.2,
                    this.audioContext.currentTime + 0.05,
                );
                gain.gain.exponentialRampToValueAtTime(
                    0.01,
                    this.audioContext.currentTime + 0.3,
                );
                oscillator.connect(gain);
                gain.connect(this.audioContext.destination);
                oscillator.start();
                oscillator.stop(this.audioContext.currentTime + 0.3);
            }
        } catch (e) {
            console.warn("Feedback error:", e);
        }
    }

    handleQRScanned(decodedText) {
        this.playSuccessFeedback();
        this.stopScanner();
        $("#qrScannerModal").modal("hide");

        // Set to hardware method to trigger auto-fill dash and allow auto-submit
        $("#scanMethodInput").val("hardware");
        this.startTimer();

        this.parseAndFillQR(decodedText, (success) => {
            if (success) {
                // Auto-submit after a short delay to ensure UI is updated
                setTimeout(() => {
                    $("#checksheetForm").trigger("submit");
                }, 100);
            }
        });
    }

    parseAndFillQR(qrString, callback) {
        const parts = qrString.split("|");

        if (parts.length !== 5) {
            Swal.fire({
                icon: "warning",
                title: "Format QR Salah",
                html: "Data QR tidak sesuai standar.<br><br><b>Format wajib:</b><br><code>customer_part|supplier_id|qty|lot_id-unique_code-cav|kode_sap</code><br><br><b>Contoh:</b><br><code>53209-K3V -N001-AA|1200044|100|PN121225SHDM1A-001-202|7-02-0347</code>"
            });
            if (callback) callback(false);
            return;
        }

        const part_code = (parts[0] || "").trim();
        const supplier_id = (parts[1] || "").trim();
        const quantity = parseInt(parts[2]) || 0;
        const unique_code_id = (parts[3] || "").trim();
        const sap_code = (parts[4] || "").trim();

        if (!part_code || !supplier_id || quantity <= 0 || !unique_code_id || sap_code === "0" || !sap_code) {
            Swal.fire({
                icon: "warning",
                title: "FORMAT QR SALAH!",
                text: "Scan QR Internal, Bukan QR Customer!"
            });
            if (callback) callback(false);
            return;
        }

        // 1. Validasi Unik di Antrean Temporary Sisi Client (Local Storage)
        const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
        const isDuplicateInQueue = queue.some(item => {
            return (parseInt(item.quantity) || 0) === quantity &&
                (item.unique_code_id || "").trim() === unique_code_id;
        });
        if (isDuplicateInQueue) {
            Swal.fire(
                "QR-Code Duplicate",
                `QR Code dengan Qty: ${quantity} dan ID: ${unique_code_id} sudah ada di list!`,
                "error"
            );
            if (callback) callback(false);
            return;
        }

        try {
            // 2. Validasi QR Duplikat via AJAX ke Database
            // FAST PATH: Jika direct save (Jakarta / useQueue=false), kita lewati GET check uniqueness 
            // karena request POST akan memvalidasi keunikan database secara otomatis
            if (this.config.qrUniqueUrl && this.config.useQueue) {
                $.get(this.config.qrUniqueUrl, { qrcode: qrString }, (res) => {
                    if (res.success && !res.unique) {
                        Swal.fire("QR-Code Duplicate", res.message, "error");
                        if (callback) callback(false);
                    } else {
                        this.processFillQR(qrString, parts, callback);
                    }
                }).fail(() => {
                    // Jika API gagal, tetap lanjut ke pemrosesan lokal sebagai fallback
                    this.processFillQR(qrString, parts, callback);
                });
            } else {
                this.processFillQR(qrString, parts, callback);
            }
        } catch (e) {
            console.error("Parse QR Error:", e);
            Swal.fire(
                "Error",
                "Gagal memproses data QR: " + e.message,
                "error",
            );
            if (callback) callback(false);
        }
    }

    processFillQR(qrString, parts, callback) {
        const part_code = parts[0].trim();
        const supplier_id = parts[1].trim();
        const quantity = parts[2].trim();
        const unique_code_id = parts[3].trim();
        const sap_code = parts[4].trim();

        $("#qrcodeInput").val(qrString);
        $("#partCodeInput").val(part_code);
        $("#supplierIdInput").val(supplier_id);
        $("#quantityInput").val(quantity);
        $("#uniqueCodeInput").val(unique_code_id);
        $("#sapCodeInputHidden").val(sap_code);

        if ($("#scanMethodInput").val() === "hardware") {
            this.fillDimensionsWithDash();
        }

        // Melakukan pemindaian lokal di dropdown yang tersedia
        let localFound = false;
        let normalize = (str) =>
            (str || "").replace(/[^A-Za-z0-9]/g, "").toUpperCase();
        let targetPart = normalize(part_code);
        let targetSap = normalize(sap_code);
        const $select = $("#itemSelect");

        $select.find('option[value!=""]').each(function () {
            if (localFound) return;

            let name = normalize(
                $(this).attr("data-name") || $(this).data("name"),
            );
            let pNum = normalize(
                $(this).attr("data-part-number") || $(this).data("part-number"),
            );
            // Fix: Gunakan data-sap_code untuk In-Process
            let sCode = normalize(
                $(this).attr("data-sap_code") || $(this).data("sap_code"),
            );

            if (
                (targetPart && name.includes(targetPart)) ||
                (targetPart && pNum === targetPart) ||
                (targetSap && sCode === targetSap)
            ) {
                $select.val($(this).val());
                localFound = true;
            }
        });

        if (localFound && $select.val()) {
            $select.trigger("change");
            $select[0].dispatchEvent(new Event("change", { bubbles: true }));
            if (quantity)
                $('input[name="total_qty"]').val(quantity).trigger("input");

            if (callback) callback(true);
        } else {
            Swal.fire(
                "Info",
                "Data item QR terbaca, tetapi tidak ditemukan di master item. Silahkan konfirmasi kepada admin untuk menambahkan data item QR.",
                "warning",
            );
            if (callback) callback(false);
        }
    }

    checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const qrFromUrl = urlParams.get("qrcode");
        if (qrFromUrl)
            setTimeout(() => {
                this.parseAndFillQR(qrFromUrl);
            }, 1000);
    }

    initTimer() {
        const _this = this;
        $("#startTimerBtn").click(function () {
            _this.startTimer();
        });
    }

    startTimer() {
        if (!this.timerRunning) {
            this.timerRunning = true;
            $("#startTimerBtn")
                .removeClass("btn-success")
                .addClass("btn-secondary")
                .attr("disabled", true)
                .html('<i class="fas fa-clock"></i> Running...');
            this.unlockInputs();
            this.timerInterval = setInterval(() => {
                this.totalSeconds++;
                this.updateTimerDisplay();
            }, 1000);
        }
    }

    updateTimerDisplay() {
        const hours = Math.floor(this.totalSeconds / 3600);
        const minutes = Math.floor((this.totalSeconds % 3600) / 60);
        const seconds = this.totalSeconds % 60;
        const text =
            (hours < 10 ? "0" + hours : hours) +
            ":" +
            (minutes < 10 ? "0" + minutes : minutes) +
            ":" +
            (seconds < 10 ? "0" + seconds : seconds);
        $("#timerDisplay").text(text);
        $("#cycleTimeInput").val(this.totalSeconds);
    }

    initItemSelect() {
        const _this = this;
        $("#itemSelect").change(function () {
            const selectedOption = $(this).find("option:selected");
            const itemId = $(this).val();
            if (!itemId) return;

            const imageUrl = selectedOption.data("image");
            const files = selectedOption.data("files");
            const name = selectedOption.data("name");
            const description = selectedOption.data("description");
            let defectsData = selectedOption.data("defects");
            const customer = selectedOption.data("customer");
            const weightStandard = selectedOption.data("weight-standard");
            const cavityData = selectedOption.data("cavity");

            // Berat Part Logic
            if (
                customer &&
                (customer.toUpperCase().includes("ASTRA HONDA MOTOR") ||
                    customer.toUpperCase().includes("AHM") ||
                    customer
                        .toUpperCase()
                        .includes("PT. TAKAGI SARI MULTI UTAMA"))
            ) {
                $(".col-berat-part").attr(
                    "style",
                    "display: table-cell !important;",
                );
                const itemCavity = parseInt(cavityData) || 1;
                _this.initWeightCavities(Math.min(itemCavity, 8));
                if (weightStandard) {
                    $("#weightStandardDisplay").text(weightStandard);
                    $("#weightStandardBadge").show();
                } else {
                    $("#weightStandardBadge").hide();
                }
            } else {
                $(".col-berat-part").attr("style", "display: none !important;");
                _this.initWeightCavities(1);
                $("#weightStandardBadge").hide();
            }

            // Logika Referensi PDF
            if (itemId === _this.lastItemId) {
                // Tetap tampilkan PDF yang sudah ter-render sebelumnya
                $("#standardPdfCanvas, #similarPdfCanvas").show().removeClass("d-none");
                $("#standardPdfPlaceholder, #similarPdfPlaceholder").hide().addClass("d-none");
                _this.updateRefNavControls();

                const standardPdf = selectedOption.data("standard");
                const similarPdf = selectedOption.data("similar");
                if (standardPdf) {
                    $("#downloadStandardBtn").attr("href", standardPdf).show();
                    $("#fullStandardBtn").show();
                } else {
                    $("#downloadStandardBtn, #fullStandardBtn").hide();
                }
                if (similarPdf) {
                    $("#downloadSimilarBtn").attr("href", similarPdf).show();
                    $("#fullSimilarBtn").show();
                } else {
                    $("#downloadSimilarBtn, #fullSimilarBtn").hide();
                }
            } else {
                _this.lastItemId = itemId;
                const standardPdf = selectedOption.data("standard");
                const similarPdf = selectedOption.data("similar");
                _this.refStandardPdfDoc = null;
                _this.refStandardPageNum = 1;
                _this.refStandardFileIndex = 0;
                _this.refStandardFiles = files || [];
                _this.refSimilarPdfDoc = null;
                _this.refSimilarPageNum = 1;

                if (standardPdf) {
                    _this.renderPdfToCanvas(
                        standardPdf,
                        "standardPdfCanvas",
                        "standardPdfPlaceholder",
                        "standardPdfLoading",
                        1,
                    );
                    $("#downloadStandardBtn").attr("href", standardPdf).show();
                } else {
                    $("#standardPdfCanvas").addClass("d-none").hide();
                    $("#standardPdfPlaceholder")
                        .removeClass("d-none")
                        .addClass("d-flex")
                        .find("p")
                        .text("Standard PDF tidak tersedia");
                    $(".standard-nav-controls").hide();
                    $("#downloadStandardBtn").hide();
                }

                if (similarPdf) {
                    _this.renderPdfToCanvas(
                        similarPdf,
                        "similarPdfCanvas",
                        "similarPdfPlaceholder",
                        "similarPdfLoading",
                        1,
                    );
                    $("#similarStatusText").text("");
                    $("#downloadSimilarBtn").attr("href", similarPdf).show();
                } else {
                    $("#similarPdfCanvas").addClass("d-none").hide();
                    $("#similarPdfPlaceholder")
                        .removeClass("d-none")
                        .addClass("d-flex");
                    $("#similarStatusText").text(
                        "Referral Dimensi Part tidak tersedia untuk item ini",
                    );
                    $(".similar-nav-controls").hide();
                    $("#downloadSimilarBtn").hide();
                }
                _this.updateRefNavControls();
            }

            // Pembaruan Kontainer Gambar
            const container = $("#imageContainer");
            let htmlContent = "";
            if (files && files.length > 0) {
                htmlContent += `<button type="button" class="btn btn-danger btn-sm view-pdf-btn mb-1" data-id="${itemId}" data-count="${files.length}"><i class="fas fa-file-pdf"></i> PDF (${files.length})</button>`;
            }
            if (imageUrl) {
                htmlContent += `<img src="${imageUrl}" style="max-width: 100px; max-height: 80px; border: 1px solid #dee2e6; cursor: pointer; display:block; margin: 0 auto;" class="img-thumbnail" data-toggle="modal" data-target="#imageModal" data-image="${imageUrl}" data-title="${name}" data-description="${description}">`;
            }
            if ((!files || files.length === 0) && !imageUrl) {
                htmlContent =
                    '<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>';
            }
            container.html(htmlContent);

            // Pembaruan Daftar Defect
            $("#defectContainer").html(
                `<div class="row no-gutters mb-2 defect-row align-items-center">
                    <div class="col-8 pr-1">
                        <select class="form-control defect-select font-weight-bold" name="defect_types[]" id="defectSelect">
                            <option value="">-- Pilih Defect --</option>
                        </select>
                    </div>
                    <div class="col-3 pr-1">
                        <input type="number" class="form-control defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">
                    </div>
                    <div class="col-1 text-center"></div>
                </div>`,
            );
            const defectSelect = $("#defectSelect");
            if (typeof defectsData === "string") {
                try {
                    defectsData = JSON.parse(defectsData);
                } catch (e) {
                    defectsData = [];
                }
            }
            if (Array.isArray(defectsData) && defectsData.length > 0) {
                $.each(defectsData, (i, v) =>
                    defectSelect.append(`<option value="${v}">${v}</option>`),
                );
            } else {
                const defaultDefects = [
                    { v: "scratch", t: "BARET" },
                    { v: "silver", t: "SILVER" },
                    { v: "flow", t: "FLOW" },
                    { v: "flash", t: "FLASH" },
                    { v: "shoot_mold", t: "SHOOT MOLD" },
                    { v: "bending", t: "BENDING" },
                    { v: "sinkmark", t: "SINKMARK" },
                    { v: "dimension", t: "Dimensi" },
                ];
                $.each(defaultDefects, (i, d) =>
                    defectSelect.append(
                        `<option value="${d.v}">${d.t}</option>`,
                    ),
                );
            }
            if (
                !defectSelect.find('option[value="dimension"]').length &&
                !defectSelect.find('option:contains("Dimensi")').length
            ) {
                defectSelect.append(
                    '<option value="dimension">Dimensi</option>',
                );
            }

            // Logika Cavity & Point (Khusus plant Karawang)
            let pointCount = 5;
            const dimStandards = selectedOption.data("dimension-standards");
            if (dimStandards) {
                if (Array.isArray(dimStandards))
                    pointCount = dimStandards.length;
                else if (typeof dimStandards === "object") {
                    const keys = Object.keys(dimStandards).map((k) =>
                        parseInt(k),
                    );
                    if (keys.length > 0) pointCount = Math.max(...keys);
                }
            }
            if (_this.config.plantContext === "karawang") {
                _this.updateCavityRows(cavityData || 1, pointCount);
                _this.toggleManualCavityButtons(true);
            }
            _this.calculateTotalNG();
        });

        $("#sapCodeInput").on("input", function () {
            const sapCode = $(this).val().trim();
            if (sapCode.length >= 1) {
                const matchedOption = $("#itemSelect option").filter(
                    function () {
                        const itemSapCode = $(this).data("sap-code");
                        return (
                            itemSapCode &&
                            itemSapCode.toString().toLowerCase() ===
                            sapCode.toLowerCase()
                        );
                    },
                );
                if (matchedOption.length > 0) {
                    $("#itemSelect").val(matchedOption.val()).trigger("change");
                    $(this).removeClass("is-invalid").addClass("is-valid");
                } else $(this).removeClass("is-valid").addClass("is-invalid");
            } else $(this).removeClass("is-valid is-invalid");
        });

        $('input[name="total_qty"]').on("input", function () {
            const lotSize = parseInt($(this).val()) || 0;
            const sampleSize = _this.getSampleSize(lotSize);
            $('input[name="sampling_qty"]').val(sampleSize).trigger("input");
        });

        $('input[name="total_ng"], input[name="sampling_qty"]').on(
            "input",
            () => this.updateJudgment(),
        );
        $("#judgmentSelect").on("change", function () {
            if ($(this).val() === "OK") _this.autoRemoveDimensionDefect();
            else if ($(this).val() === "NG") _this.autoAddDimensionDefect();
            _this.toggleNextProsesDropdown();
        });
    }

    showToast(msg, color) {
        let $toast = $("#scanToast");
        if (!$toast.length) {
            $toast = $('<div id="scanToast"></div>').css({
                position: "fixed",
                bottom: "20px",
                left: "50%",
                transform: "translateX(-50%)",
                background: "#ffffff",
                padding: "10px 24px",
                borderRadius: "30px",
                fontSize: "0.875rem",
                fontWeight: "600",
                zIndex: 9999,
                pointerEvents: "none",
                boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                border: "1px solid #e2e8f0",
                transition: "opacity 0.3s",
            }).appendTo("body");
        }
        let textColor = "#1e293b";
        let borderColor = "#e2e8f0";
        if (color === "#4ade80") {
            textColor = "#15803d";
            borderColor = "#bbf7d0";
        } else if (color === "#f87171") {
            textColor = "#b91c1c";
            borderColor = "#fecaca";
        }
        $toast.text(msg).css({ color: textColor, borderColor: borderColor, opacity: 1 }).stop(true);
        clearTimeout($toast.data("hideTimer"));
        $toast.data("hideTimer", setTimeout(() => $toast.animate({ opacity: 0 }, 400), 2000));
    }

    initHardwareScanner() {
        let buffer = "";
        let lastTime = Date.now();
        let scanTimeout;

        const processScan = (raw) => {
            raw = (raw || "").trim();
            console.log("Hardware Scan Triggered:", raw);

            // Aktifkan lock agar submit form diblokir sementara
            this.isProcessingScan = true;
            clearTimeout(this.scanLockTimeout);

            if (!raw.includes("|")) {
                this.showToast("❌ Format QR salah (tidak ada |)", "#f87171");
                this.isProcessingScan = false;
                buffer = "";
                return;
            }

            const parts = raw.split("|");
            if (parts.length !== 5) {
                this.showToast(`❌ Format QR salah (harus 5 bagian)`, "#f87171");
                this.isProcessingScan = false;
                buffer = "";
                return;
            }

            // Auto start timer if scan occurs
            if (!this.timerRunning) {
                this.startTimer();
            }

            // Kunci input agar tidak bisa scan kedua sebelum data diproses
            $("#sapCodeInput").val("").prop("disabled", true).css("background", "#f1f5f9");

            this.showToast("✅ Scan berhasil diproses!", "#4ade80");
            $("#scanMethodInput").val("hardware");

            // Panggil parseAndFillQR dengan callback untuk auto-submit
            this.parseAndFillQR(raw, (success) => {
                if (success) {
                    setTimeout(() => {
                        console.log("Auto-submitting form after successful hardware scan...");
                        $("#checksheetForm").trigger("submit");
                        // Lock akan dilepas setelah submit atau timeout
                        this.scanLockTimeout = setTimeout(() => { this.isProcessingScan = false; }, 2000);
                    }, 100);
                } else {
                    this.isProcessingScan = false;
                }
            });
            buffer = "";
        };

        // ─── Method PDA: Global Capturing Listener ───
        // Menangkap tombol Enter di fase Capturing untuk memblokir submit browser secepat mungkin
        window.addEventListener("keydown", (e) => {
            if ((e.key === "Enter" || e.keyCode === 13) && document.activeElement && document.activeElement.id === 'sapCodeInput') {
                console.log("Enter key captured and blocked (PDA Mode)");
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        }, true);

        // ─── Method A: Dedicated input handler for PDA Keyboard Wedge ───
        // Catch the Enter key sent by PDA at the end of the scan
        $("#sapCodeInput").on("keydown", function (e) {
            if (e.key === "Enter" || e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();

                const val = ($(this).val() || "").trim();
                if (val.length > 10 && val.includes("|") && val.split("|").length === 5) {
                    $(this).val(""); // Clear field
                    processScan(val);
                }
                return false;
            }
        });

        // Fallback for PDA scanners that only send input events without Enter
        let sapInputTimeout;
        $("#sapCodeInput").on("input", function () {
            const val = ($(this).val() || "").trim();
            if (val.length > 10 && val.includes("|") && val.split("|").length === 5) {
                clearTimeout(sapInputTimeout);
                sapInputTimeout = setTimeout(() => {
                    const finalVal = ($(this).val() || "").trim();
                    if (finalVal && finalVal === val) { // Ensure typing has stopped
                        $(this).val("");
                        processScan(finalVal);
                    }
                }, 80); // Wait 80ms to ensure the full SAP code is typed
            }
        });

        // ─── Method B: Global keydown buffer (PC wired/wireless scanners) ───
        window.addEventListener("keydown", (e) => {
            const currentTime = Date.now();
            const isTerminator = e.key === "Enter" || e.keyCode === 13 ||
                e.key === "Tab" || e.keyCode === 9;

            // Reset buffer if gap is too long (human typing)
            if (currentTime - lastTime > 1000) {
                buffer = "";
            }

            if (!isTerminator) {
                const char = e.key;
                if (char && char.length === 1) {
                    buffer += char;
                } else if (e.keyCode >= 32 && e.keyCode <= 126) {
                    buffer += String.fromCharCode(e.keyCode);
                }
            } else if (buffer.length > 10 && buffer.includes("|") && buffer.split("|").length === 5) {
                e.preventDefault();
                clearTimeout(scanTimeout);
                processScan(buffer);
                buffer = "";
            }

            lastTime = currentTime;

            // Auto-process on timeout for scanners without Enter terminator
            clearTimeout(scanTimeout);
            scanTimeout = setTimeout(() => {
                if (buffer.length > 10 && buffer.includes("|") && buffer.split("|").length === 5) {
                    processScan(buffer);
                    buffer = "";
                }
            }, 80); // Wait 80ms instead of 500ms
        }, true);
    }


    fillDimensionsWithDash() {
        $(".dimension-input").val("-");
    }

    initMachinePersistence() {
        const _this = this;
        const fields = [
            { id: "code_machine", key: "last_machine_selection" },
            { id: "shiftSelect", key: "last_shift_selection", name: "shift" }
        ];

        fields.forEach(field => {
            const $el = field.id ? $("#" + field.id) : $(`input[name="${field.name}"], select[name="${field.name}"]`);
            if (!$el.length) return;

            // Load from localStorage
            const savedVal = localStorage.getItem(field.key);
            if (savedVal && (!$el.val() || $el.val() === "")) {
                console.log(`[Persistence] Restoring ${field.key}: ${savedVal}`);
                $el.val(savedVal).trigger("change");
            }

            // Save on change/input
            $el.on("change input", function () {
                const val = $(this).val();
                if (val) {
                    localStorage.setItem(field.key, val);
                }
            });
        });
    }

    initDimensionControls() {
        $("#addCavityBtn").click(() => {
            if (this.currentCavities < this.maxCavities) {
                this.currentCavities++;
                let newRow = `<tr class="cavity-row" data-cavity="${this.currentCavities}"><td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${this.currentCavities}</td>`;
                for (let j = 1; j <= this.currentPoints; j++) {
                    newRow += `<td class="point-cell"><input type="text" class="form-control form-control-sm dimension-input" style="min-width: 60px;" name="dimensions[${this.currentCavities}][${j}]" placeholder="P${j}"></td>`;
                }
                newRow += `</tr>`;
                $("#dimensionBody").append(newRow);
            }
        });

        $("#deleteCavityBtn").click(() => {
            if (this.currentCavities > 1) {
                $("#dimensionBody tr:last-child").remove();
                this.currentCavities--;
                this.updateJudgment();
            }
        });

        $("#addPointBtn").click(() => {
            if (this.currentPoints < this.maxPoints) {
                this.currentPoints++;
                $("#dimensionHeadRow").append(
                    `<th class="point-header">Point ${this.currentPoints}</th>`,
                );
                $(".cavity-row").each((index, element) => {
                    const cavityNum = $(element).data("cavity");
                    $(element).append(
                        `<td class="point-cell"><input type="text" class="form-control form-control-sm dimension-input" style="min-width: 60px;" name="dimensions[${cavityNum}][${this.currentPoints}]" placeholder="P${this.currentPoints}"></td>`,
                    );
                });
            }
        });

        $("#deletePointBtn").click(() => {
            if (this.currentPoints > 1) {
                $("#dimensionHeadRow th.point-header:last-child").remove();
                $(".cavity-row").each(function () {
                    $(this).find("td.point-cell:last-child").remove();
                });
                this.currentPoints--;
                this.updateJudgment();
            }
        });

        $(document).on("input", ".dimension-input", function () {
            let val = $(this).val();
            if (val.startsWith("+0")) $(this).val(val.replace(/^\+0/, ""));
        });

        $(document).on("input", ".dimension-input", () =>
            this.validateDimensions(),
        );
    }

    updateCavityRows(cavityCount, pointCount = 5) {
        if (this.config.plantContext !== "karawang") return;
        const tbody = $("#dimensionBody");
        const theadRow = $("#dimensionHeadRow");
        tbody.empty();
        let headerHtml =
            '<th style="min-width: 100px; position: sticky; left: 0; z-index: 2; background: #f8f9fa;">Cavity</th>';
        for (let j = 1; j <= pointCount; j++)
            headerHtml += `<th class="point-header">Point ${j}</th>`;
        theadRow.html(headerHtml);
        for (let i = 1; i <= cavityCount; i++) {
            let rowHtml = `<tr class="cavity-row" data-cavity="${i}"><td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${i}</td>`;
            for (let j = 1; j <= pointCount; j++) {
                rowHtml += `<td class="point-cell"><input type="text" class="form-control form-control-sm dimension-input" style="min-width: 60px;" name="dimensions[${i}][${j}]" placeholder="P${j}"></td>`;
            }
            rowHtml += `</tr>`;
            tbody.append(rowHtml);
        }
        this.currentCavities = cavityCount;
        this.currentPoints = pointCount;
    }

    toggleManualCavityButtons(isDynamic) {
        if (this.config.plantContext !== "karawang") return;
        if (isDynamic)
            $(
                "#addCavityBtn, #deleteCavityBtn, #addPointBtn, #deletePointBtn",
            ).hide();
        else
            $(
                "#addCavityBtn, #deleteCavityBtn, #addPointBtn, #deletePointBtn",
            ).show();
    }

    initWeightControls() {
        const _this = this;
        $(document).on("click", "#addWeightCavBtn", function () {
            const cnt = $("#weightCavContainer .weight-cav-row").length;
            if (cnt >= _this.MAX_WEIGHT_CAV) return;
            $("#weightCavContainer").append(_this.buildWeightCavRow(cnt + 1));
            _this.updateWeightCavBadge();
        });
        $(document).on("click", "#removeWeightCavBtn", function () {
            const rows = $("#weightCavContainer .weight-cav-row");
            if (rows.length <= 1) return;
            rows.last().remove();
            _this.updateWeightCavBadge();
        });
    }

    buildWeightCavRow(cavNum) {
        return `<div class="weight-cav-row" style="display:flex; align-items:center; margin-bottom:6px; gap:8px;"><span style="font-size:0.85rem; font-weight:600; color:#444; white-space:nowrap; min-width:45px;">CAV ${cavNum}</span><input type="number" step="0.01" min="0" class="form-control form-control-sm text-center" name="part_weight[]" placeholder="0.00" style="width:100px; flex:none;"><span style="font-size:0.85rem; color:#666;">gr</span></div>`;
    }

    updateWeightCavBadge() {
        const cnt = $("#weightCavContainer .weight-cav-row").length;
        $("#addWeightCavBtn").prop("disabled", cnt >= this.MAX_WEIGHT_CAV);
        $("#removeWeightCavBtn").prop("disabled", cnt <= 1);
    }

    initWeightCavities(count) {
        count = Math.min(
            Math.max(1, parseInt(count) || 1),
            this.MAX_WEIGHT_CAV,
        );
        const container = $("#weightCavContainer");
        container.empty();
        for (let i = 1; i <= count; i++)
            container.append(this.buildWeightCavRow(i));
        this.updateWeightCavBadge();
    }

    initDefectList() {
        const _this = this;
        $("#addDefectBtn").click(function () {
            const rowCount = $(".defect-row").length;
            if (rowCount < 4) {
                const firstSelect = $("#defectSelect");
                const newRow = $(
                    `<div class="row no-gutters mb-2 defect-row align-items-center">
                        <div class="col-8 pr-1">
                            <select class="form-control defect-select font-weight-bold" name="defect_types[]">${firstSelect.html()}</select>
                        </div>
                        <div class="col-3 pr-1">
                            <input type="number" class="form-control defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">
                        </div>
                        <div class="col-1 text-center">
                            <button class="btn btn-link text-danger p-0 remove-defect-btn" type="button"><i class="fas fa-times-circle"></i></button>
                        </div>
                    </div>`,
                );
                $("#defectContainer").append(newRow);
            }
            if ($(".defect-row").length >= 4) $(this).hide();
        });

        $(document).on("input", ".defect-qty", () => this.calculateTotalNG());
        $(document).on("click", ".remove-defect-btn", function () {
            $(this).closest(".defect-row").remove();
            _this.calculateTotalNG();
            if ($(".defect-row").length < 4) $("#addDefectBtn").show();
        });

        $('input[name="total_ng"]').on("input", function () {
            const ng = parseInt($(this).val()) || 0;
            if (ng >= 1 && $(".defect-row").length < 4)
                $("#addDefectBtn").show();
            else $("#addDefectBtn").hide();
        });
    }

    calculateTotalNG() {
        let total = 0;
        $(".defect-qty").each(function () {
            total += parseInt($(this).val()) || 0;
        });
        $('input[name="total_ng"]').val(total).trigger("input");
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

    updateJudgment() {
        const sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
        const ng = parseInt($('input[name="total_ng"]').val()) || 0;
        const isDimensiInvalid = $(".dimension-input.is-invalid").length > 0;

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

        $('input[name="total_ok"]').val(Math.max(0, sampling - ng));
        const limits = this.getAqlLimits(sampling);
        $("#acc_val").text(limits.acc);
        $("#rej_val").text(limits.rej);
        $("#aql_info").show();

        const judgmentSelect = $("#judgmentSelect");
        const judgmentBadge = $("#judgmentBadge");

        if (ng > 0 || sampling > 0 || isDimensiInvalid) {
            if (isDimensiInvalid || ng >= limits.rej) {
                judgmentSelect
                    .val("NG")
                    .removeClass("text-success")
                    .addClass("text-danger");
                judgmentBadge
                    .text("NG")
                    .removeClass("d-none text-success")
                    .addClass("text-danger")
                    .css({
                        "border-color": "#dc3545",
                        "background-color": "#fff",
                    });
            } else {
                judgmentSelect
                    .val("OK")
                    .removeClass("text-danger")
                    .addClass("text-success");
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
            judgmentSelect.val("").removeClass("text-success text-danger");
            judgmentBadge.addClass("d-none").text("-");
        }
        this.toggleNextProsesDropdown();
    }

    toggleNextProsesDropdown() {
        const judgment = $("#judgmentSelect").val();
        const ngCount = parseInt($("#total_ng").val()) || 0;
        if (judgment === "NG" || ngCount > 0) $("#nextProsesContainer").show();
        else $("#nextProsesContainer").hide();
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
            if (!qtyInput.val() || parseInt(qtyInput.val()) <= 0)
                qtyInput.val(1).trigger("input");
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
            if ($(".defect-row").length < 4) {
                $("#addDefectBtn").trigger("click");
                targetSelect = $(".defect-select").last();
            } else targetSelect = $(".defect-select").first();
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
                if ($(".defect-row").length === 1) {
                    $(this).val("").trigger("change");
                    row.find(".defect-qty").val("");
                } else {
                    row.remove();
                    if ($(".defect-row").length < 4) $("#addDefectBtn").show();
                }
            }
        });
        this.calculateTotalNG();
    }

    initFormValidation() {
        const _this = this;

        // Mencegah form tersubmit otomatis saat PDA scanner mengirimkan tombol "Enter"
        $("#checksheetForm").on("keydown", "input", function (e) {
            if (e.key === "Enter" || e.keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $("#checksheetForm").on("submit", function (e) {
            e.preventDefault();

            const submitter = (e.originalEvent && e.originalEvent.submitter) || document.activeElement;

            const isHardwareScan = $("#scanMethodInput").val() === "hardware";

            if (!isHardwareScan && (!submitter || (submitter.id !== 'saveBtn' && $(submitter).closest('#saveBtn').length === 0))) {
                console.warn("Submit diblokir karena tidak berasal dari tombol Save.");
                return false;
            }

            const judgment = $("#judgmentSelect").val();
            const nextProses = $("#nextProses").val();
            const itemId = $("#itemSelect").val();
            let codeMachine = $("#code_machine").val();

            // FALLBACK: Jika Mesin kosong (karena page reload), coba ambil dari localStorage
            if (!codeMachine) {
                const savedMachine = localStorage.getItem("last_machine_selection");
                if (savedMachine) {
                    console.log("[Persistence] Auto-filling machine from localStorage fallback...");
                    $("#code_machine").val(savedMachine).trigger("change");
                    codeMachine = savedMachine;
                }
            }

            const totalQty = $('input[name="total_qty"]').val();
            const samplingQty = $('input[name="sampling_qty"]').val();
            const operatorInitials = $('input[name="operator_initials"]').val();

            // 1. Validasi: Item harus dipilih
            if (!itemId) {
                // JIKA sedang dalam proses scan (isProcessingScan) atau bukan klik tombol Save,
                // maka abaikan submit ini. Ini adalah kunci untuk memblokir alert "Item Belum Dipilih".
                const isManualClick = submitter && (submitter.id === 'saveBtn' || $(submitter).closest('#saveBtn').length > 0);

                if (this.isProcessingScan || !isManualClick) {
                    console.warn("Submit diblokir: Sedang memproses scan atau bukan klik manual.");
                    return false;
                }

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
                Swal.fire({
                    icon: "warning",
                    title: "Mesin Belum Dipilih",
                });
                $("#code_machine").addClass("is-invalid").focus();
                setTimeout(() => $("#code_machine").removeClass("is-invalid"), 3000);
                return false;
            }

            // 3. Validasi: Total Qty
            if (!totalQty || totalQty <= 0) {
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
                Swal.fire({
                    icon: "warning",
                    title: "Sampling Qty Belum Diisi",
                });
                $('input[name="sampling_qty"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="sampling_qty"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 5. Validasi: NG harus pilih Next Proses (Kecuali jika defect HANYA Dimensi)
            let isOnlyDimensi = true;
            let hasAnyDefect = false;
            $(".defect-row").each(function () {
                const type = $(this).find(".defect-select").val();
                const qty = parseInt($(this).find(".defect-qty").val()) || 0;
                if (qty > 0) {
                    hasAnyDefect = true;
                    if (type !== "dimension") {
                        isOnlyDimensi = false;
                    }
                }
            });

            if (judgment === "NG" && !nextProses && !(hasAnyDefect && isOnlyDimensi)) {
                Swal.fire({
                    icon: "warning",
                    title: "Next Proses Wajib Dipilih",
                });
                $("#nextProses").addClass("is-invalid").focus();
                setTimeout(
                    () => $("#nextProses").removeClass("is-invalid"),
                    3000,
                );
                return false;
            }

            // 6. Validasi: Inisial QC
            if (!operatorInitials) {
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

            if (!_this.checkMandatoryDimensions()) return false;

            let dimensionDefectSelected = false;
            let dimensionQtyEmpty = false;
            $(".defect-select").each(function () {
                const text = $(this)
                    .find("option:selected")
                    .text()
                    .toLowerCase();
                if ($(this).val() === "dimension" || text === "dimensi") {
                    dimensionDefectSelected = true;
                    const qtyInput = $(this)
                        .closest(".defect-row")
                        .find(".defect-qty");
                    if (!qtyInput.val() || parseInt(qtyInput.val()) <= 0) {
                        dimensionQtyEmpty = true;
                        qtyInput.addClass("is-invalid");
                    } else qtyInput.removeClass("is-invalid");
                }
            });

            if (dimensionDefectSelected && dimensionQtyEmpty) {
                Swal.fire({
                    icon: "warning",
                    title: "Qty Defect Dimensi Wajib Diisi",
                });
                return false;
            }

            if (_this.timerRunning) {
                clearInterval(_this.timerInterval);
                _this.timerRunning = false;
                $("#cycleTimeInput").val(_this.totalSeconds);
            }

            // Bersihkan defect yang dipilih tapi tidak ada qty atau qty = 0 (Kecuali Dimensi yang sudah dicek)
            $(".defect-row").each(function () {
                const typeInput = $(this).find(
                    'select[name="defect_types[]"], input[name="defect_types[]"]',
                );
                const qtyInput = $(this).find(
                    'input[name="defect_quantities[]"]',
                );
                const type = typeInput.val();
                const text = $(this)
                    .find("option:selected")
                    .text()
                    .toLowerCase();
                const qty = parseInt(qtyInput.val()) || 0;

                if (
                    type &&
                    qty === 0 &&
                    type !== "dimension" &&
                    text !== "dimensi"
                ) {
                    typeInput.val("");
                    qtyInput.val("");
                }
            });

            // Jika hardware scan DAN useQueue aktif (Karawang): masukkan ke antrian
            if (isHardwareScan && _this.config.useQueue) {
                _this.addToQueue();
                return;
            }

            // Untuk Jakarta (useQueue=false) atau manual: langsung simpan ke database
            const saveBtn = $("#saveBtn");
            const originalHtml = saveBtn.html();
            saveBtn
                .prop("disabled", true)
                .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            const formData = new FormData(this);
            $.ajax({
                url: $(this).attr("action"),
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        if (isHardwareScan) {
                            // FAST TRACK: Tampilkan toast non-blocking, langsung reset dan siap scan berikutnya
                            _this.showToast("✅ Data Berhasil Disimpan", "#4ade80");
                            _this.resetForm();
                            _this.restorePersistentFields();
                            _this.startTimer();
                        } else {
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil",
                                text: "Data Berhasil Disimpan",
                                showCancelButton: true,
                                confirmButtonText: "Lihat Data",
                            }).then((result) => {
                                if (result.isConfirmed)
                                    window.location.href = response.index_url;
                                else {
                                    _this.resetForm();
                                    _this.restorePersistentFields();
                                }
                            });
                        }
                    }
                },
                error: function (xhr) {
                    const errorMsg =
                        xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : "Gagal menyimpan data.";
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: errorMsg,
                    });
                    saveBtn.prop("disabled", false).html(originalHtml);
                },
            });
        });
    }

    resetForm() {
        $("#checksheetForm")[0].reset();
        clearInterval(this.timerInterval);
        this.timerRunning = false;
        this.totalSeconds = 0;
        this.updateTimerDisplay();
        $("#startTimerBtn")
            .removeClass("btn-secondary")
            .addClass("btn-success")
            .removeAttr("disabled")
            .html('<i class="fas fa-play"></i> Start');

        const queue = JSON.parse(localStorage.getItem('inprocess_scan_buffer') || '[]');
        if (this.config.useQueue) {
            // Karawang: lanjutkan berdasarkan antrian
            if (queue.length > 0) {
                this.startTimer();
            } else {
                this.lockInputs();
                $("#saveBtn").prop("disabled", true).html('<i class="fas fa-save fa-sm"></i> Simpan');
            }
        } else {
            // Jakarta: langsung lock & siap scan berikutnya
            this.lockInputs();
            $("#saveBtn").prop("disabled", true).html('<i class="fas fa-save fa-sm"></i> Simpan');
        }

        $("#addDefectBtn").hide();
        $(".defect-row").not(":first").remove();
        $("#imageContainer").html(
            '<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>',
        );
        // Jangan reset tampilan PDF agar tetap muncul pada halaman untuk kenyamanan scan berulang
        // $("#standardPdfCanvas, #similarPdfCanvas").hide();
        // $("#standardPdfPlaceholder")
        //     .show()
        //     .find("p")
        //     .text("Pilih Item untuk menampilkan Standard PDF");
        // $("#similarPdfPlaceholder")
        //     .show()
        //     .find("p")
        //     .text("Pilih Item untuk menampilkan Dimensi Part");
        // $(
        //     "#fullStandardBtn, #fullSimilarBtn, .standard-nav-controls, .similar-nav-controls",
        // ).hide();
        $("#judgmentBadge").addClass("d-none").text("-");
        $("#itemSelect").val("").trigger("change");

        // Buka kembali input scan untuk siklus berikutnya
        $("#sapCodeInput").prop("disabled", false).css("background", "");
        this.isProcessingScan = false;
        this.applyAutoFocus();
    }

    normalizePartNumber(pn) {
        if (!pn) return "";
        return pn
            .toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, "-")
            .replace(/\s+/g, "")
            .toUpperCase();
    }

    normalizeStandardValue(val) {
        if (val === null || val === undefined || val === "") return null;
        return val
            .toString()
            .replace(",", ".")
            .replace(/[\u2012\u2013\u2014\u2212]/g, "-")
            .trim();
    }

    validateDimensions() {
        const _this = this;
        const selectedOption = $("#itemSelect").find("option:selected");
        const itemPartNumber = this.normalizePartNumber(
            selectedOption.data("part-number"),
        );

        // Prioritize standards attached to the option, fallback to global config
        let dimensionStandards = selectedOption.data("dimension-standards");
        if (typeof dimensionStandards === "string") {
            try {
                dimensionStandards = JSON.parse(dimensionStandards);
            } catch (e) {
                dimensionStandards = null;
            }
        }
        if (!dimensionStandards) {
            dimensionStandards = (this.config.partDimensionStandards || {})[
                itemPartNumber
            ];
        }

        $('input[name^="dimensions"]').each(function () {
            const name = $(this).attr("name");
            const match = name.match(/\[(\d+)\]\[(\d+)\]/);
            if (!match) return;
            const point = match[2];

            // Robust lookup for standard
            let standard = null;
            if (dimensionStandards) {
                if (Array.isArray(dimensionStandards)) {
                    standard =
                        dimensionStandards.find(
                            (s) => String(s.point) === String(point),
                        ) || dimensionStandards[point - 1];
                } else {
                    standard = dimensionStandards[point];
                }
            }
            const valStr = $(this).val().trim();
            const value = parseFloat(valStr.replace(",", "."));

            $(this).removeClass("is-invalid is-valid");

            if (standard && valStr !== "" && !isNaN(value)) {
                let isInvalid = false;
                const epsilon = 0.00001;

                // 1. Check Absolute Min/Max
                if (standard.min != null && standard.min !== "") {
                    const minBound = parseFloat(
                        String(standard.min).replace(",", "."),
                    );
                    if (!isNaN(minBound) && value < minBound - epsilon)
                        isInvalid = true;
                }
                if (!isInvalid && standard.max != null && standard.max !== "") {
                    const maxBound = parseFloat(
                        String(standard.max).replace(",", "."),
                    );
                    if (!isNaN(maxBound) && value > maxBound + epsilon)
                        isInvalid = true;
                }

                // 2. Check Size +/- Tolerance
                if (
                    !isInvalid &&
                    standard.size != null &&
                    standard.tolerance != null &&
                    standard.size !== "" &&
                    standard.tolerance !== ""
                ) {
                    const stdSzStr = _this.normalizeStandardValue(
                        standard.size,
                    );
                    if (
                        !stdSzStr.startsWith("+") &&
                        !stdSzStr.startsWith("-")
                    ) {
                        const base = parseFloat(stdSzStr);
                        const tol = _this.normalizeStandardValue(
                            standard.tolerance,
                        );
                        let lb = base,
                            ub = base;

                        if (tol.includes("/")) {
                            tol.split("/").forEach((p) => {
                                p = _this.normalizeStandardValue(p);
                                const fv = parseFloat(p);
                                if (p.startsWith("+") || fv > 0)
                                    ub = base + Math.abs(fv);
                                else if (p.startsWith("-") || fv < 0)
                                    lb = base - Math.abs(fv);
                            });
                        } else if (tol.startsWith("+")) {
                            ub = base + parseFloat(tol.substring(1));
                        } else if (tol.startsWith("-")) {
                            lb = base + parseFloat(tol);
                        } else {
                            const tv = parseFloat(tol);
                            lb = base - tv;
                            ub = base + tv;
                        }

                        if (value < lb - epsilon || value > ub + epsilon)
                            isInvalid = true;
                    }
                }

                // 3. Check Special Size (with prefix)
                if (
                    !isInvalid &&
                    standard.size != null &&
                    standard.size !== ""
                ) {
                    const sz = String(standard.size);
                    if (sz.startsWith("+") || sz.startsWith("-")) {
                        const op = sz.charAt(0);
                        const bound = parseFloat(sz.substring(1));
                        if (!isNaN(bound)) {
                            if (op === "+" && value < bound - epsilon)
                                isInvalid = true;
                            else if (op === "-" && value > bound + epsilon)
                                isInvalid = true;
                        }
                    }
                }

                if (isInvalid) {
                    $(this).addClass("is-invalid");
                } else {
                    $(this).addClass("is-valid");
                }
            }
        });
        this.updateJudgment();
    }

    checkMandatoryDimensions() {
        const selectedOption = $("#itemSelect").find("option:selected");
        const itemPartNumber = this.normalizePartNumber(
            selectedOption.data("part-number"),
        );
        const dimensionStandards = (this.config.partDimensionStandards || {})[
            itemPartNumber
        ];
        if (
            !dimensionStandards ||
            this.config.plantContext === "jakarta" ||
            this.config.plantContext === "karawang"
        )
            return true;

        let allFilled = true;
        let firstEmpty = null;
        $(".dimension-input").each(function () {
            const match = $(this)
                .attr("name")
                .match(/\[(\d+)\]\[(\d+)\]/);
            if (
                match &&
                dimensionStandards[match[2]] &&
                $(this).val().trim() === ""
            ) {
                allFilled = false;
                $(this).addClass("is-invalid");
                if (!firstEmpty) firstEmpty = $(this);
            }
        });
        if (!allFilled) {
            Swal.fire({
                icon: "warning",
                title: "Data Dimensi Belum Lengkap",
                text: "Mohon isi semua kolom dimensi yang memiliki standar!",
            });
            if (firstEmpty) {
                $("html, body").animate(
                    { scrollTop: firstEmpty.offset().top - 200 },
                    500,
                );
                firstEmpty.focus();
            }
            return false;
        }
        return true;
    }

    initZoomLogic() {
        let zoom = 1,
            step = 0.25;
        const updateImageZoom = () => {
            $("#modalImage").css({
                transform: "scale(" + zoom + ")",
                "transform-origin": zoom > 1 ? "top center" : "center center",
            });
        };
        $("#zoomIn").click(() => {
            zoom += step;
            updateImageZoom();
        });
        $("#zoomOut").click(() => {
            if (zoom > step) {
                zoom -= step;
                updateImageZoom();
            }
        });
        $("#zoomReset").click(() => {
            zoom = 1;
            updateImageZoom();
        });
        $("#imageModal").on("show.bs.modal", function (e) {
            const btn = $(e.relatedTarget);
            $(this).find("#modalImage").attr("src", btn.data("image"));
            $(this).find("#modalTitle").text(btn.data("title"));
            $(this).find("#modalDescription").text(btn.data("description"));
            zoom = 1;
            updateImageZoom();
        });
    }

    initPDFReference() {
        const _this = this;
        pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;

        $("#prevStandardPage").click(() => {
            if (this.refStandardPageNum > 1) {
                this.refStandardPageNum--;
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
            } else if (this.refStandardFileIndex > 0) {
                // Switch to previous file and go to its last page
                this.refStandardFileIndex--;
                const itemId = $("#itemSelect").val();
                const prevFileUrl = this.config.pdfUrlPattern
                    .replace("ID_PLACEHOLDER", itemId)
                    .replace("INDEX_PLACEHOLDER", this.refStandardFileIndex);

                pdfjsLib.getDocument(prevFileUrl).promise.then(pdf => {
                    this.refStandardPageNum = pdf.numPages;
                    this.renderPdfToCanvas(
                        prevFileUrl,
                        "standardPdfCanvas",
                        "standardPdfPlaceholder",
                        "standardPdfLoading",
                        this.refStandardPageNum
                    );
                });
            }
        });
        $("#nextStandardPage").click(() => {
            if (
                this.refStandardPdfDoc &&
                this.refStandardPageNum < this.refStandardPdfDoc.numPages
            ) {
                this.refStandardPageNum++;
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
            } else if (this.refStandardFiles && this.refStandardFileIndex < this.refStandardFiles.length - 1) {
                // Switch to next file and go to original page 1
                this.refStandardFileIndex++;
                const itemId = $("#itemSelect").val();
                const nextFileUrl = this.config.pdfUrlPattern
                    .replace("ID_PLACEHOLDER", itemId)
                    .replace("INDEX_PLACEHOLDER", this.refStandardFileIndex);

                this.refStandardPageNum = 1;
                this.renderPdfToCanvas(
                    nextFileUrl,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    1
                );
            }
        });
        $("#prevSimilarPage").click(() => {
            if (this.refSimilarPageNum > 1) {
                this.refSimilarPageNum--;
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
            }
        });
        $("#nextSimilarPage").click(() => {
            if (
                this.refSimilarPdfDoc &&
                this.refSimilarPageNum < this.refSimilarPdfDoc.numPages
            ) {
                this.refSimilarPageNum++;
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
            }
        });

        // Logika Zoom Referensi PDF
        $("#zoomInStandard").click(() => {
            this.standardZoomLevel += 0.25;
            if (this.refStandardPdfDoc)
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
        });
        $("#zoomOutStandard").click(() => {
            if (this.standardZoomLevel > 0.5) {
                this.standardZoomLevel -= 0.25;
                if (this.refStandardPdfDoc)
                    this.renderPageOnCanvas(
                        this.refStandardPdfDoc,
                        "standardPdfCanvas",
                        this.refStandardPageNum,
                    );
            }
        });
        $("#zoomResetStandard").click(() => {
            this.standardZoomLevel = 1.0;
            if (this.refStandardPdfDoc)
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
        });

        $("#zoomInSimilar").click(() => {
            this.similarZoomLevel += 0.25;
            if (this.refSimilarPdfDoc)
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
        });
        $("#zoomOutSimilar").click(() => {
            if (this.similarZoomLevel > 0.5) {
                this.similarZoomLevel -= 0.25;
                if (this.refSimilarPdfDoc)
                    this.renderPageOnCanvas(
                        this.refSimilarPdfDoc,
                        "similarPdfCanvas",
                        this.refSimilarPageNum,
                    );
            }
        });
        $("#zoomResetSimilar").click(() => {
            this.similarZoomLevel = 1.0;
            if (this.refSimilarPdfDoc)
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
        });

        // Logika Modal PDF Layar Penuh
        $(document).on("click", ".view-pdf-btn", function () {
            const itemId = $(this).data("id");
            const isSimilar = $(this).data("similar");
            _this.totalPdfFilesFull = isSimilar ? 1 : $(this).data("count");
            _this.currentPdfIndexFull = isSimilar ? "similar" : 0;
            _this.fullCurrentItemId = itemId;
            $("#pdfModal").modal("show");
            _this.loadFullPdf(itemId, _this.currentPdfIndexFull);
        });

        // Navigasi Modal PDF Layar Penuh
        $("#prevPdf").click(() => {
            if (this.currentPdfIndexFull > 0) {
                this.currentPdfIndexFull--;
                this.loadFullPdf(
                    this.fullCurrentItemId,
                    this.currentPdfIndexFull,
                );
            }
        });
        $("#nextPdf").click(() => {
            if (this.currentPdfIndexFull < this.totalPdfFilesFull - 1) {
                this.currentPdfIndexFull++;
                this.loadFullPdf(
                    this.fullCurrentItemId,
                    this.currentPdfIndexFull,
                );
            }
        });

        let fullPdfDoc = null,
            fullPageNum = 1,
            fullScale = 1.0;
        const fullCanvas = document.getElementById("the-canvas");
        const fullCtx = fullCanvas.getContext("2d");

        $("#prevPage").click(() => {
            if (fullPageNum > 1) {
                fullPageNum--;
                this.renderFullPage(
                    fullPdfDoc,
                    fullCanvas,
                    fullCtx,
                    fullPageNum,
                    fullScale,
                );
            }
        });
        $("#nextPage").click(() => {
            if (fullPdfDoc && fullPageNum < fullPdfDoc.numPages) {
                fullPageNum++;
                this.renderFullPage(
                    fullPdfDoc,
                    fullCanvas,
                    fullCtx,
                    fullPageNum,
                    fullScale,
                );
            }
        });
        $("#pdfZoomIn").click(() => {
            fullScale += 0.25;
            this.renderFullPage(
                fullPdfDoc,
                fullCanvas,
                fullCtx,
                fullPageNum,
                fullScale,
            );
        });
        $("#pdfZoomOut").click(() => {
            if (fullScale > 0.25) {
                fullScale -= 0.25;
                this.renderFullPage(
                    fullPdfDoc,
                    fullCanvas,
                    fullCtx,
                    fullPageNum,
                    fullScale,
                );
            }
        });
        $("#pdfZoomReset").click(() => {
            fullScale = 1.0;
            this.renderFullPage(
                fullPdfDoc,
                fullCanvas,
                fullCtx,
                fullPageNum,
                fullScale,
            );
        });

        this.loadFullPdf = (id, idx) => {
            const url = this.config.pdfUrlPattern
                .replace("ID_PLACEHOLDER", id)
                .replace("INDEX_PLACEHOLDER", idx);
            fullPdfDoc = null;
            fullPageNum = 1;
            fullCtx.clearRect(0, 0, fullCanvas.width, fullCanvas.height);
            $("#pageInfo").text("Loading...");
            if (idx === "similar") {
                $("#pdfInfo").text("Dimensi Part PDF");
                $("#prevPdf, #nextPdf").hide();
            } else {
                $("#pdfInfo").text(
                    `File ${idx + 1} of ${this.totalPdfFilesFull}`,
                );
                $("#prevPdf, #nextPdf").show();
            }

            pdfjsLib
                .getDocument(url)
                .promise.then((pdf) => {
                    fullPdfDoc = pdf;
                    this.renderFullPage(
                        fullPdfDoc,
                        fullCanvas,
                        fullCtx,
                        fullPageNum,
                        fullScale,
                    );
                })
                .catch((err) => {
                    console.error(err);
                    $("#pageInfo").text("Error loading PDF");
                });
        };

        this.renderFullPage = (pdf, canvas, ctx, num, scale) => {
            pdf.getPage(num).then((page) => {
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                page.render({
                    canvasContext: ctx,
                    viewport: viewport,
                }).promise.then(() => {
                    $("#pageInfo").text(`Page ${num} of ${pdf.numPages}`);
                    fullPageNum = num;
                });
            });
        };

        // Fungsi Helper untuk render canvas standard/similar saat Zoom atau Navigasi Page
        this.renderPageOnCanvas = (pdf, canvasId, pageNum) => {
            if (!pdf) return;
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext("2d");
            const $canvas = $(canvas);
            const $loading = $(
                canvasId === "standardPdfCanvas"
                    ? "#standardPdfLoading"
                    : "#similarPdfLoading",
            );

            $canvas.hide();
            $loading.removeClass("d-none").addClass("d-flex");
            this.drawPage(
                pdf,
                canvas,
                ctx,
                $loading,
                $canvas,
                pageNum,
                canvasId,
            );
        };
    }

    renderPdfToCanvas(url, canvasId, placeholderId, loadingId, pageNum = 1) {
        const _this = this;
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext("2d");
        const $placeholder = $("#" + placeholderId);
        const $loading = $("#" + loadingId);
        const $canvas = $(canvas);

        $placeholder.removeClass("d-flex").addClass("d-none");
        $canvas.addClass("d-none").hide();
        $loading.removeClass("d-none").addClass("d-flex");

        if (this.pdfCache[url]) {
            this.drawPage(
                this.pdfCache[url],
                canvas,
                ctx,
                $loading,
                $canvas,
                pageNum,
                canvasId,
            );
            return;
        }

        pdfjsLib
            .getDocument(url)
            .promise.then((pdf) => {
                _this.pdfCache[url] = pdf;
                _this.drawPage(
                    pdf,
                    canvas,
                    ctx,
                    $loading,
                    $canvas,
                    pageNum,
                    canvasId,
                );
            })
            .catch((err) => {
                $loading.removeClass("d-flex").addClass("d-none");
                $placeholder
                    .removeClass("d-none")
                    .addClass("d-flex")
                    .find("p")
                    .text("Gagal memuat PDF");
            });
    }

    drawPage(pdf, canvas, ctx, $loading, $canvas, pageNum, canvasId) {
        const _this = this;
        pdf.getPage(pageNum).then((page) => {
            const containerWidth = $canvas.parent().width() || 500;
            const availableWidth = containerWidth - 40;
            const viewport = page.getViewport({ scale: 1.0 });
            let zoom =
                canvasId === "standardPdfCanvas"
                    ? _this.standardZoomLevel
                    : _this.similarZoomLevel;
            const scale = (availableWidth / viewport.width) * zoom;
            const scaledViewport = page.getViewport({ scale: scale });

            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            if (zoom > 1.0) $canvas.css({ width: "auto", "max-width": "none" });
            else $canvas.css({ width: "100%", "max-width": "100%" });
            $canvas.css("height", "auto");

            page.render({
                canvasContext: ctx,
                viewport: scaledViewport,
            }).promise.then(() => {
                $loading.removeClass("d-flex").addClass("d-none");
                $canvas.removeClass("d-none").show();
                if (canvasId === "standardPdfCanvas") {
                    _this.refStandardPdfDoc = pdf;
                    const fileInfo = _this.refStandardFiles.length > 1 ? ` (${_this.refStandardFileIndex + 1}/${_this.refStandardFiles.length})` : '';
                    $("#standardPageInfo").text(`P ${pageNum}/${pdf.numPages}${fileInfo}`);
                    _this.refStandardPageNum = pageNum;
                } else if (canvasId === "similarPdfCanvas") {
                    _this.refSimilarPdfDoc = pdf;
                    $("#similarPageInfo").text(`P ${pageNum}/${pdf.numPages}`);
                    _this.refSimilarPageNum = pageNum;
                }
                _this.updateRefNavControls();
            });
        });
    }

    updateRefNavControls() {
        if (this.refStandardFiles && this.refStandardFiles.length > 0)
            $(".standard-nav-controls").attr(
                "style",
                "display: flex !important;",
            );
        else $(".standard-nav-controls").hide();
        if (this.refSimilarPdfDoc)
            $(".similar-nav-controls").attr(
                "style",
                "display: flex !important;",
            );
        else $(".similar-nav-controls").hide();
    }
}

// Fungsi inisialisasi Global
window.initInProcessIndex = (config) => new InProcessIndex(config);
window.initInProcessCreate = (config) => new InProcessCreate(config);
