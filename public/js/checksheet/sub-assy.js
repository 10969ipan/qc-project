class SubAssyIndex {
    constructor(config = {}) {
        this.config = config;
        this.initEventListeners();
    }

    initEventListeners() {
        this.initCharacterCounter();
        this.initLiveSearch();
        this.initModalHandlers();
        this.initAjaxForms();
        this.initQRDetail();
        if (this.config && this.config.btnScanId) {
            this.initQRScanner();
        }
    }

    initCharacterCounter() {
        $(document).on(
            "input",
            'textarea[name="rejection_remarks"]',
            function () {
                const id = $(this).attr("id").replace("rejection_remarks", "");
                $("#charCount" + id).text(this.value.length);
            },
        );
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById("liveSearch");
        if (liveSearchInput) {
            let searchTimeout;
            liveSearchInput.addEventListener("input", function () {
                const searchTerm = this.value.trim();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    const params = new URLSearchParams(window.location.search);
                    if (searchTerm) params.set("search", searchTerm);
                    else params.delete("search");
                    params.delete("page");
                    window.location.href =
                        window.location.pathname + "?" + params.toString();
                }, 500);
            });
        }
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
            const originalBtnHtml = $submitBtn.html();

            $modalErrors.hide().empty();
            $form.find(".is-invalid").removeClass("is-invalid");
            $submitBtn
                .prop("disabled", true)
                .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: $form.attr("action"),
                method: $form.attr("method"),
                data: new FormData($form[0]),
                processData: false,
                contentType: false,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        const swalInstance = window.Swal || (typeof Swal !== 'undefined' ? Swal : null);
                        if (swalInstance && typeof swalInstance.fire === 'function') {
                            swalInstance.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'Data berhasil diperbarui.',
                                showConfirmButton: true,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#4e73df'
                            }).then((result) => {
                                window.location.reload();
                            });
                        } else {
                            alert(response.message || 'Data berhasil diperbarui.');
                            window.location.reload();
                        }
                    } else {
                        $modalErrors
                            .html(
                                '<div class="alert alert-danger">' +
                                (response.message ||
                                    "Terjadi kesalahan saat menyimpan data.") +
                                "</div>",
                            )
                            .fadeIn();
                        $submitBtn
                            .prop("disabled", false)
                            .html(originalBtnHtml);
                    }
                },
                error: function (xhr) {
                    $submitBtn.prop("disabled", false).html(originalBtnHtml);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml =
                            '<div class="alert alert-danger"><ul class="mb-0 small">';
                        $.each(errors, function (field, messages) {
                            errorHtml += "<li>" + messages[0] + "</li>";
                            $form
                                .find('[name="' + field + '"]')
                                .addClass("is-invalid");
                        });
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
                },
            });
        });
    }

    initQRDetail() {
        $(document).on("click", ".btn-qr-detail", function () {
            const data = $(this).data();
            $("#modal-qr-raw").text(data.qr || "-");
            $("#modal-qr-part").text(data.part || "-");
            $("#modal-qr-supplier").text(data.supplier || "-");
            $("#modal-qr-qty").text(data.qty || "-");
            $("#modal-qr-unique").text(data.unique || "-");
            $("#modal-qr-sap").text(data.sap || "-");
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
        this.lastItemId = null;

        // PDF Reference Logic (Dual Column)
        this.pdfCache = {};
        this.standardZoomLevel = 1.0;
        this.similarZoomLevel = 1.0;
        this.refStandardPdfDoc = null;
        this.refSimilarPdfDoc = null;
        this.refStandardPageNum = 1;
        this.refSimilarPageNum = 1;
        this.refStandardFiles = [];
        this.refStandardFileIndex = 0;

        // QR Scanner Logic
        this.qrScanner = null;

        this.isProcessingScan = false;
        this.scanLockTimeout = null;
        this.lastLinePromise = null;

        this.init();
    }

    init() {
        $(document).ready(() => {
            this.setupUI();
            this.initEventListeners();
            this.initPdfJS();
            this.initQRScanner();
            this.initHardwareScanner();
            this.initLinePersistence();
            this.initPDFReference();
            this.calculateTotalNG();
            this.updateJudgment();
        });
    }

    setupUI() {
        const form = $("#checksheetForm");
        this.formInputs = form
            .find("input, select, textarea, button")
            .not("#startTimerBtn")
            .not("#sapCodeInput")
            .not('[type="hidden"]');
        this.formInputs.prop("disabled", true);
        form.addClass("inputs-locked");

        if (!$("#lockStyle").length) {
            $(
                '<style id="lockStyle">#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>',
            ).appendTo("head");
        }

        // Pastikan sapCodeInput tetap enabled dan terfokus saat halaman dimuat
        $("#sapCodeInput").prop("disabled", false).focus();

        // Helper klik untuk memfokuskan kembali input scan bagi pengguna tablet/PDA
        $(document).on("click", ".card-body", (e) => {
            if ($(e.target).closest("input, select, textarea, button").length === 0) {
                $("#sapCodeInput").focus();
            }
        });
    }

    initPdfJS() {
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;
        }
    }

    initEventListeners() {
        const self = this;

        $("#startTimerBtn").click(() => {
            this.startTimer();
        });

        $("#itemSelect").change((e) => this.handleItemChange(e));

        $("#sapCodeInput").on("input", function () {
            const sapCode = $(this).val().trim();
            if (sapCode.length >= 1) {
                let normalize = (str) =>
                    (str || "").replace(/[^A-Za-z0-9]/g, "").toUpperCase();
                let targetSap = normalize(sapCode);

                const matchedOption = $("#itemSelect option").filter(
                    function () {
                        const sCode = normalize(
                            $(this).attr("data-sap-code") ||
                            $(this).data("sap-code"),
                        );
                        return sCode === targetSap;
                    },
                );

                if (matchedOption.length > 0) {
                    $("#itemSelect").val(matchedOption.val()).trigger("change");
                    $("#itemSelect")[0].dispatchEvent(
                        new Event("change", { bubbles: true }),
                    );
                    $(this).removeClass("is-invalid").addClass("is-valid");
                } else {
                    $(this).removeClass("is-valid").addClass("is-invalid");
                }
            } else {
                $(this).removeClass("is-valid is-invalid");
            }
        });

        $('input[name="check_type_option"]').on("change", function () {
            self.isFullcheckMode = $(this).val() === "fullcheck";
            $("#checkTypeInput").val($(this).val());

            const lotSize = parseInt($('input[name="total_qty"]').val()) || 0;
            if (lotSize > 0) {
                const sampleSize = self.isFullcheckMode
                    ? lotSize
                    : self.getSampleSize(lotSize);
                $('input[name="sampling_qty"]')
                    .val(sampleSize)
                    .trigger("input");
            }
        });

        $('input[name="total_qty"]').on("input", function () {
            const lotSize = parseInt($(this).val()) || 0;
            const sampleSize = self.isFullcheckMode
                ? lotSize
                : self.getSampleSize(lotSize);
            $('input[name="sampling_qty"]').val(sampleSize).trigger("input");
        });

        $('input[name="total_ng"], input[name="sampling_qty"]').on(
            "input",
            () => this.updateJudgment(),
        );

        $("#addDefectBtn").click(() => this.handleAddDefect());
        $(document).on("input", ".defect-qty", () => this.calculateTotalNG());
        $(document).on("click", ".remove-defect-btn", (e) =>
            this.handleRemoveDefect(e),
        );

        // Mencegah form tersubmit otomatis saat PDA scanner mengirimkan tombol "Enter"
        $("#checksheetForm").on("keydown", "input", function (e) {
            if (e.key === "Enter" || e.keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $("#checksheetForm").on("submit", (e) => this.handleFormSubmit(e));

        // Referece PDF Modal Full (Backward compatibility or auxiliary)
        $("#prevPage").click(() => this.handlePrevPage());
        $("#nextPage").click(() => this.handleNextPage());
        $("#pdfZoomIn").click(() => this.handlePdfZoom(0.25));
        $("#pdfZoomOut").click(() => this.handlePdfZoom(-0.25));
        $("#pdfZoomReset").click(() => {
            this.scale = 1.0;
            this.queueRenderPage(this.pageNum);
        });
        $("#prevPdf").click(() => this.handlePrevPdf());
        $("#nextPdf").click(() => this.handleNextPdf());

        // Image Modal
        $("#zoomIn").click(() => this.handleImageZoom(0.25));
        $("#zoomOut").click(() => this.handleImageZoom(-0.25));
        $("#zoomReset").click(() => {
            this.currentZoom = 1;
            this.updateImageZoom();
        });
        $("#imageModal").on("show.bs.modal", (e) => this.handleImageModal(e));

        $('button[type="reset"]').click(() => this.resetState());
    }

    initQRScanner() {
        const _this = this;
        $("#btnScanQR").click(() => {
            if (this.unlockAudio) this.unlockAudio();
            $("#qrScannerModal").modal("show");
        });

        $("#qrScannerModal").on("shown.bs.modal", () => {
            const videoElem = document.getElementById("qr-video");

            // Bersihkan instance lama jika ada
            if (this.qrScanner) {
                this.qrScanner.destroy();
                this.qrScanner = null;
            }

            this.qrScanner = new QrScanner(
                videoElem,
                (result) => this.handleQRScanned(result.data),
                {
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                    maxScansPerSecond: 25,
                    preferredCamera: "environment",
                },
            );

            // Override internal mirror logic to prevent auto-mirror
            this.qrScanner._setVideoMirror = function (facingMode) {
                // Do nothing, we handle mirroring manually via CSS
            };

            // Handle manual flip button
            $("#toggleMirrorBtn")
                .off("click")
                .on("click", function () {
                    $(videoElem).toggleClass("mirrored");
                });

            this.qrScanner
                .start()
                .then(() => {
                    // Check if device has flash
                    this.qrScanner.hasFlash().then((hasFlash) => {
                        if (hasFlash) {
                            $("#toggleFlashBtn").removeClass("d-none");
                        }
                    });

                    // Handle flash button
                    $("#toggleFlashBtn")
                        .off("click")
                        .on("click", () => {
                            this.qrScanner.toggleFlash();
                        });

                    // Handle Zoom Control
                    const track =
                        this.qrScanner.$video.srcObject.getVideoTracks()[0];
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
                    console.error("Gagal menjalankan kamera:", err);
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

        $("#qrScannerModal").on("hidden.bs.modal", () => {
            this.stopScanner();
            $("#qr-video").show();
            if ($("#qr-error-msg").length) $("#qr-error-msg").hide();
        });

        // Handler untuk input file
        $("#qr-input-file").on("change", async function (e) {
            if (e.target.files.length === 0) return;
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
    }

    stopScanner() {
        if (this.qrScanner) {
            this.qrScanner.stop();
        }
    }

    handleQRScanned(decodedText) {
        this.playSuccessFeedback();
        this.stopScanner();
        $("#qrScannerModal").modal("hide");

        // Set scan method ke hardware untuk trigger auto-fill dan auto-submit
        $("#scanMethodInput").val("hardware");
        this.startTimer();

        this.parseAndFillQR(decodedText, (success) => {
            if (success) {
                // Auto-submit setelah jeda singkat agar UI terupdate
                setTimeout(() => {
                    $("#checksheetForm").trigger("submit");
                }, 500);
            }
        });
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

    parseAndFillQR(qrString, callback) {
        const parts = qrString.split("|");

        if (parts.length < 5) {
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
                title: "Format QR Tidak Valid",
                text: "Komponen QR Code tidak lengkap atau tidak valid!"
            });
            if (callback) callback(false);
            return;
        }

        try {
            // Validasi QR Duplikat via AJAX ke Database
            if (this.config.qrUniqueUrl) {
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

    processFillQR(decodedText, parts, callback) {
        try {
            const partCode = parts[0].trim();
            const supplierId = parts[1].trim();
            const quantity = parts[2].trim();
            const uniqueCode = parts[3].trim();
            const sapCode = parts[4].trim();

            // WAJIB: Simpan QR raw string ke hidden input agar tersimpan ke database
            $("#qrcodeInput").val(decodedText);
            $("#sapCodeInput").val(sapCode);
            $("#partCodeInput").val(partCode);
            $("#supplierIdInput").val(supplierId);
            $("#quantityInput").val(quantity);
            $("#uniqueCodeInput").val(uniqueCode);
            $("#sapCodeInputHidden").val(sapCode);

            // Melakukan pemindaian lokal di dropdown yang tersedia
            let localFound = false;
            let normalize = (str) =>
                (str || "").replace(/[^A-Za-z0-9]/g, "").toUpperCase();
            let targetPart = normalize(partCode);
            let targetSap = normalize(sapCode);
            const $select = $("#itemSelect");

            $select.find('option[value!=""]').each(function () {
                if (localFound) return;

                let name = normalize(
                    $(this).attr("data-name") || $(this).data("name"),
                );
                let pNum = normalize(
                    $(this).attr("data-part-number") ||
                    $(this).data("part-number"),
                );
                let sCode = normalize(
                    $(this).attr("data-sap-code") || $(this).data("sap-code"),
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
                $select[0].dispatchEvent(
                    new Event("change", { bubbles: true }),
                );

                if (quantity) {
                    $('input[name="total_qty"]').val(quantity).trigger("input");
                }

                // Tampilkan notifikasi hanya di mode manual (tidak ada callback auto-submit)
                // Di mode hardware scan, notifikasi cukup dari "Data Berhasil Disimpan"
                if (!callback) {
                    Swal.fire({
                        icon: "success",
                        title: "QR Berhasil Discan",
                        text: "Item otomatis terpilih.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                }

                // Tunggu AJAX last-line selesai sebelum auto-submit agar meja sudah terisi
                const doCallback = () => { if (callback) callback(true); };
                if (this.lastLinePromise) {
                    this.lastLinePromise.always(doCallback);
                } else {
                    doCallback();
                }
            } else {
                Swal.fire(
                    "Info",
                    "Data item QR terbaca, tetapi tidak ditemukan di master item. Silahkan konfirmasi kepada admin untuk menambahkan data item QR.",
                    "warning",
                );
                if (callback) callback(false);
            }
        } catch (e) {
            console.error("Fill QR Error:", e);
            Swal.fire("Error", "Gagal mengisi data QR: " + e.message, "error");
            if (callback) callback(false);
        }
    }


    initPDFReference() {
        // Navigasi PCCP / Standard
        $("#prevStandardPage").click(() => {
            if (this.refStandardPageNum > 1) {
                this.refStandardPageNum--;
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
            } else if (this.refStandardFileIndex > 0) {
                // Jump to previous file at its last page
                this.refStandardFileIndex--;
                const itemId = $("#itemSelect").val();
                const prevUrl = this.config.pdfUrlPattern
                    .replace("ID_PLACEHOLDER", itemId)
                    .replace("INDEX_PLACEHOLDER", this.refStandardFileIndex);
                pdfjsLib.getDocument(prevUrl).promise.then(pdf => {
                    this.refStandardPageNum = pdf.numPages;
                    this.renderPdfToCanvas(prevUrl, "standardPdfCanvas", "standardPdfPlaceholder", "standardPdfLoading", pdf.numPages);
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
                // Jump to next file at page 1
                this.refStandardFileIndex++;
                const itemId = $("#itemSelect").val();
                const nextUrl = this.config.pdfUrlPattern
                    .replace("ID_PLACEHOLDER", itemId)
                    .replace("INDEX_PLACEHOLDER", this.refStandardFileIndex);
                this.refStandardPageNum = 1;
                this.renderPdfToCanvas(nextUrl, "standardPdfCanvas", "standardPdfPlaceholder", "standardPdfLoading", 1);
            }
        });

        // Navigasi Dimensi
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

        // Zoom Controls
        $("#zoomInStandard").click(() => {
            this.standardZoomLevel += 0.25;
            this.renderPageOnCanvas(
                this.refStandardPdfDoc,
                "standardPdfCanvas",
                this.refStandardPageNum,
            );
        });
        $("#zoomOutStandard").click(() => {
            if (this.standardZoomLevel > 0.5) {
                this.standardZoomLevel -= 0.25;
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
            }
        });
        $("#zoomResetStandard").click(() => {
            this.standardZoomLevel = 1.0;
            this.renderPageOnCanvas(
                this.refStandardPdfDoc,
                "standardPdfCanvas",
                this.refStandardPageNum,
            );
        });

        $("#zoomInSimilar").click(() => {
            this.similarZoomLevel += 0.25;
            this.renderPageOnCanvas(
                this.refSimilarPdfDoc,
                "similarPdfCanvas",
                this.refSimilarPageNum,
            );
        });
        $("#zoomOutSimilar").click(() => {
            if (this.similarZoomLevel > 0.5) {
                this.similarZoomLevel -= 0.25;
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
            }
        });
        $("#zoomResetSimilar").click(() => {
            this.similarZoomLevel = 1.0;
            this.renderPageOnCanvas(
                this.refSimilarPdfDoc,
                "similarPdfCanvas",
                this.refSimilarPageNum,
            );
        });

        // Full Screen Modal Logic (Backward Compatibility)
        this.currentPdfIndexFull = 0;
        this.totalPdfFilesFull = 0;
        this.fullCurrentItemId = null;

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

    handleItemChange(e) {
        const option = $(e.target).find("option:selected");
        const itemId = option.val();
        if (!itemId) return;

        this.currentItemId = itemId;
        const imageUrl = option.data("image");
        const standardUrl = option.data("standard");
        const similarUrl = option.data("similar");
        const files = option.data("files");

        if (itemId === this.lastItemId) {
            // Tetap tampilkan PDF yang sudah ter-render sebelumnya
            $("#standardPdfCanvas, #similarPdfCanvas").show().removeClass("d-none");
            $("#standardPdfPlaceholder, #similarPdfPlaceholder").hide().addClass("d-none");
            this.updateRefNavControls();

            if (standardUrl) {
                $("#downloadStandardBtn").attr("href", standardUrl).show();
            } else {
                $("#downloadStandardBtn").hide();
            }
            if (similarUrl) {
                $("#downloadSimilarBtn").attr("href", similarUrl).show();
            } else {
                $("#downloadSimilarBtn").hide();
            }
        } else {
            this.lastItemId = itemId;

            // Reset display
            $("#standardPdfCanvas, #similarPdfCanvas").hide();
            $("#standardPdfPlaceholder, #similarPdfPlaceholder")
                .removeClass("d-none")
                .addClass("d-flex");
            this.refStandardFiles = files || [];
            this.refStandardFileIndex = 0;
            this.refStandardPageNum = 1;

            // Handle Standard / PCCP
            if (standardUrl) {
                this.renderPdfToCanvas(
                    standardUrl,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                );
                $("#downloadStandardBtn").attr("href", standardUrl).show();
            } else {
                $("#standardPdfPlaceholder p").text(
                    "Standard (PCCP) tidak tersedia",
                );
                $("#downloadStandardBtn").hide();
            }

            // Handle Dimensi Part
            if (similarUrl) {
                this.renderPdfToCanvas(
                    similarUrl,
                    "similarPdfCanvas",
                    "similarPdfPlaceholder",
                    "similarPdfLoading",
                );
                $("#downloadSimilarBtn").attr("href", similarUrl).show();
            } else {
                $("#similarPdfPlaceholder p").text("Similar Part tidak tersedia");
                $("#downloadSimilarBtn").hide();
            }
        }

        this.updateDefectDropdown(option.data("defects"));

        // Ensure plant hidden input is populated
        if (option.data("plant-id")) {
            $('input[name="plant"]').val(option.data("plant-id"));
        }

        this.updateJudgment();

        // ─── Auto-fill Meja berdasarkan data item hari ini ───
        if (itemId && this.config.lastLineUrl) {
            const $lineSelect = $("#line");
            const dateVal = $('input[name="date"]').val() || new Date().toISOString().split("T")[0];
            const plantVal = $('input[name="plant"]').val();

            // Reset sementara sambil loading
            $lineSelect.removeClass("is-valid is-invalid");

            // Simpan promise agar processFillQR bisa menunggu hasilnya sebelum auto-submit
            this.lastLinePromise = $.get(this.config.lastLineUrl, { item_id: itemId, date: dateVal, plant: plantVal })
                .done((res) => {
                    if (res.found && res.line) {
                        // Ada data hari ini → auto-fill meja
                        $lineSelect.val(res.line).trigger("change");
                        $lineSelect.addClass("is-valid");
                        setTimeout(() => $lineSelect.removeClass("is-valid"), 2500);
                    } else {
                        // Belum ada data hari ini → wajib pilih manual
                        $lineSelect.val("");
                        $lineSelect.addClass("is-invalid");
                        setTimeout(() => $lineSelect.removeClass("is-invalid"), 3000);
                    }
                })
                .fail(() => {
                    // Jika API gagal, biarkan user pilih manual
                    $lineSelect.val("");
                    this.lastLinePromise = null;
                });
        } else {
            this.lastLinePromise = null;
        }
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

        $("#defectContainer").html(
            '<div class="row no-gutters mb-2 defect-row align-items-center">' +
            '<div class="col-8 pr-1">' +
            '<select class="form-control defect-select font-weight-bold" name="defect_types[]" id="defectSelect">' +
            '<option value="">-- Pilih Defect --</option>' +
            '</select>' +
            '</div>' +
            '<div class="col-3 pr-1">' +
            '<input type="number" class="form-control defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">' +
            '</div>' +
            '<div class="col-1 text-center"></div>' +
            '</div>',
        );
        const select = $("#defectSelect");

        if (Array.isArray(defectsData) && defectsData.length > 0) {
            $.each(defectsData, (i, v) =>
                select.append(`<option value="${v}">${v}</option>`),
            );
        } else {
            const defaults = [
                { v: "scratch", t: "BARET" },
                { v: "silver", t: "SILVER" },
                { v: "flow", t: "FLOW" },
                { v: "flash", t: "FLASH" },
                { v: "shoot_mold", t: "SHOOT MOLD" },
                { v: "bending", t: "BENDING" },
                { v: "sinkmark", t: "SINKMARK" },
                { v: "dimension", t: "Dimensi" },
            ];
            $.each(defaults, (i, d) =>
                select.append(`<option value="${d.v}">${d.t}</option>`),
            );
        }
    }

    handleAddDefect() {
        if ($(".defect-row").length < 4) {
            const first = $("#defectSelect").html();
            const newRow = $(
                '<div class="row no-gutters mb-2 defect-row align-items-center">' +
                '<div class="col-8 pr-1">' +
                `<select class="form-control defect-select font-weight-bold" name="defect_types[]">${first}</select>` +
                '</div>' +
                '<div class="col-3 pr-1">' +
                '<input type="number" class="form-control defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">' +
                '</div>' +
                '<div class="col-1 text-center">' +
                '<button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button>' +
                '</div>' +
                '</div>',
            );
            $("#defectContainer").append(newRow);
        }
        if ($(".defect-row").length >= 4) $("#addDefectBtn").hide();
    }

    handleRemoveDefect(e) {
        $(e.target).closest(".defect-row").remove();
        this.calculateTotalNG();
        if ($(".defect-row").length < 4) $("#addDefectBtn").show();
    }

    calculateTotalNG() {
        let total = 0;
        $(".defect-qty").each(function () {
            total += parseInt($(this).val()) || 0;
        });
        $('input[name="total_ng"]').val(total).trigger("input");
    }

    updateJudgment() {
        const sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
        const ng = parseInt($('input[name="total_ng"]').val()) || 0;

        $('input[name="total_ok"]').val(Math.max(0, sampling - ng));
        const limits = this.getAqlLimits(sampling);
        $("#acc_val").text(limits.acc);
        $("#rej_val").text(limits.rej);
        $("#aql_info").show();

        const judgmentSelect = $("#judgmentSelect");
        const judgmentBadge = $("#judgmentBadge");

        if (ng > 0 || sampling > 0) {
            if (ng >= limits.rej) {
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
        const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;
        if (judgment === "NG" || ngCount > 0) $("#nextProsesContainer").show();
        else $("#nextProsesContainer").hide();
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
        if (!pn) return "";
        return pn
            .toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, "-")
            .replace(/\s+/g, "")
            .toUpperCase();
    }

    updateTimerDisplay() {
        const h = Math.floor(this.totalSeconds / 3600);
        const m = Math.floor((this.totalSeconds % 3600) / 60);
        const s = this.totalSeconds % 60;
        const pad = (n) => (n < 10 ? "0" + n : n);
        $("#timerDisplay").text(`${pad(h)}:${pad(m)}:${pad(s)}`);
        $("#cycleTimeInput").val(this.totalSeconds);
    }

    startTimer() {
        if (!this.timerRunning) {
            this.timerRunning = true;
            $("#startTimerBtn")
                .removeClass("btn-success")
                .addClass("btn-secondary")
                .attr("disabled", true)
                .html('<i class="fas fa-clock"></i> Running...');
            $("#saveBtn").prop("disabled", false);
            this.formInputs.prop("disabled", false);
            $("#checksheetForm").removeClass("inputs-locked");
            this.timerInterval = setInterval(() => {
                this.totalSeconds++;
                this.updateTimerDisplay();
            }, 1000);
        }
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

        // Toast notification untuk feedback scan hardware (tanpa dialog modal)
        const showToast = (msg, color) => {
            this.showToast(msg, color);
        };

        const processScan = (raw) => {
            raw = (raw || "").trim();
            console.log("Hardware Scan Triggered (Sub Assy):", raw);

            this.isProcessingScan = true;
            clearTimeout(this.scanLockTimeout);

            if (!raw.includes("|")) {
                showToast("❌ Format QR salah (tidak ada |)", "#f87171");
                this.isProcessingScan = false;
                buffer = "";
                return;
            }

            const parts = raw.split("|");
            if (parts.length !== 5) {
                showToast(`❌ Format QR salah (harus 5 bagian, terdeteksi: ${parts.length})`, "#f87171");
                this.isProcessingScan = false;
                buffer = "";
                return;
            }

            // Auto start timer jika belum berjalan
            this.startTimer();

            // Kunci input agar tidak bisa scan kedua sebelum data diproses
            $("#sapCodeInput").val("").prop("disabled", true).css("background", "#f1f5f9");

            showToast("✅ Scan berhasil diproses!", "#4ade80");
            $("#scanMethodInput").val("hardware");

            // Panggil parseAndFillQR dengan callback untuk auto-submit
            this.parseAndFillQR(raw, (success) => {
                if (success) {
                    setTimeout(() => {
                        console.log("Auto-submitting form after successful hardware scan (Sub Assy)...");
                        $("#checksheetForm").trigger("submit");
                        this.scanLockTimeout = setTimeout(() => { this.isProcessingScan = false; }, 2000);
                    }, 100);
                } else {
                    // Unlock kembali jika gagal
                    $("#sapCodeInput").prop("disabled", false).css("background", "");
                    this.isProcessingScan = false;
                }
            });
            buffer = "";
        };

        // ─── Method PDA: Global Capturing Listener ───
        // Menangkap tombol Enter di fase Capturing untuk memblokir submit browser
        window.addEventListener("keydown", (e) => {
            if ((e.key === "Enter" || e.keyCode === 13) && document.activeElement && document.activeElement.id === 'sapCodeInput') {
                console.log("Enter key captured and blocked (Sub Assy PDA Mode)");
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        }, true);

        // ─── Method A: Dedicated input handler for PDA Keyboard Wedge ───
        $("#sapCodeInput").on("keydown", function (e) {
            if (e.key === "Enter" || e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();

                const val = ($(this).val() || "").trim();
                if (val.length > 10 && val.includes("|") && val.split("|").length >= 5) {
                    $(this).val(""); // Clear field
                    processScan(val);
                }
                return false;
            }
        });

        // Fallback untuk PDA yang hanya mengirim input event tanpa Enter
        let sapInputTimeout;
        $("#sapCodeInput").on("input", function () {
            const val = ($(this).val() || "").trim();
            if (val.length > 10 && val.includes("|") && val.split("|").length === 5) {
                clearTimeout(sapInputTimeout);
                sapInputTimeout = setTimeout(() => {
                    const finalVal = ($(this).val() || "").trim();
                    if (finalVal && finalVal === val) {
                        $(this).val("");
                        processScan(finalVal);
                    }
                }, 80);
            }
        });

        // ─── Method B: Global keydown buffer (scanner USB/wireless) ───
        window.addEventListener("keydown", (e) => {
            const currentTime = Date.now();
            const isTerminator = e.key === "Enter" || e.keyCode === 13 ||
                e.key === "Tab" || e.keyCode === 9;

            // Reset buffer jika jeda terlalu lama (ketik manual)
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
            } else if (buffer.length > 10 && buffer.includes("|") && buffer.split("|").length >= 5) {
                e.preventDefault();
                clearTimeout(scanTimeout);
                processScan(buffer);
                buffer = "";
            }

            lastTime = currentTime;

            // Auto-process on timeout untuk scanner tanpa terminator Enter
            clearTimeout(scanTimeout);
            scanTimeout = setTimeout(() => {
                if (buffer.length > 10 && buffer.includes("|") && buffer.split("|").length >= 5) {
                    processScan(buffer);
                    buffer = "";
                }
            }, 500);
        }, true);
    }

    initLinePersistence() {
        const fields = [
            { id: "line", key: "last_subassy_line_selection" },
            { id: "shiftSelect", key: "last_subassy_shift_selection" }
        ];

        fields.forEach(field => {
            const $el = field.id
                ? $("#" + field.id)
                : $(`input[name="${field.name}"], select[name="${field.name}"]`);
            if (!$el.length) return;

            // Load dari localStorage
            const savedVal = localStorage.getItem(field.key);
            if (savedVal && (!$el.val() || $el.val() === "")) {
                $el.val(savedVal).trigger("change");
            }

            // Simpan saat berubah
            $el.on("change input", function () {
                const val = $(this).val();
                if (val) {
                    localStorage.setItem(field.key, val);
                }
            });
        });
    }

    handleFormSubmit(e) {
        e.preventDefault();
        const _this = this;
        const form = e.target;
        const isHardwareScan = $("#scanMethodInput").val() === "hardware";

        // Blokir submit yang bukan dari tombol Save atau hardware scan
        const submitter = (e.originalEvent && e.originalEvent.submitter) || document.activeElement;
        if (!isHardwareScan && (!submitter || (submitter.id !== 'saveBtn' && $(submitter).closest('#saveBtn').length === 0))) {
            console.warn("Submit diblokir karena tidak berasal dari tombol Save.");
            return false;
        }

        const judgment = $("#judgmentSelect").val();
        const nextProses = $("#nextProses").val();
        const itemId = $("#itemSelect").val();
        const line = $("#line").val();
        const totalQty = $('input[name="total_qty"]').val();
        const samplingQty = $('input[name="sampling_qty"]').val();
        const operatorInitials = $('input[name="operator_initials"]').val();

        // 1. Validasi: Item harus dipilih
        if (!itemId) {
            // Blokir alert jika masih dalam proses scan hardware
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

        // 2. Validasi: Meja harus dipilih
        if (!line) {
            Swal.fire({
                icon: "warning",
                title: "Meja Belum Dipilih",
            });
            $("#line").addClass("is-invalid").focus();
            setTimeout(() => $("#line").removeClass("is-invalid"), 3000);
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

        // 5. Validasi: NG harus pilih Next Proses
        if (judgment === "NG" && !nextProses) {
            Swal.fire({
                icon: "warning",
                title: "Next Proses Wajib Dipilih",
            });
            $("#nextProses").addClass("is-invalid").focus();
            setTimeout(() => $("#nextProses").removeClass("is-invalid"), 3000);
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

        // 7. Validasi: Integritas Baris Defect (NG) & Cleanup
        const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;
        const hasAnyNgInput = $(".defect-qty").toArray().some(input => (parseInt($(input).val()) || 0) > 0);

        if (judgment === "NG" || ngCount > 0 || hasAnyNgInput) {
            let defectMissing = false;
            let hasAtLeastOneValidDefect = false;
            let dimensionQtyEmpty = false;

            $(".defect-row").each(function () {
                const typeInput = $(this).find(".defect-select");
                const qtyInput = $(this).find(".defect-qty");
                const type = typeInput.val();
                const text = typeInput.find("option:selected").text().toLowerCase();
                const qty = parseInt(qtyInput.val()) || 0;

                // Case A: Qty ada tapi Type kosong
                if (qty > 0 && !type) {
                    defectMissing = true;
                    typeInput.addClass("is-invalid");
                } else {
                    typeInput.removeClass("is-invalid");
                    if (qty > 0) hasAtLeastOneValidDefect = true;
                }

                // Case B: Type Dimensi tapi Qty kosong
                if (type === 'dimension' || text === 'dimensi') {
                    if (qty <= 0) {
                        dimensionQtyEmpty = true;
                        qtyInput.addClass("is-invalid");
                    } else {
                        qtyInput.removeClass("is-invalid");
                    }
                }

                // Case C: Cleanup baris "sampah"
                if (type && qty === 0 && type !== 'dimension' && text !== 'dimensi') {
                    typeInput.val('');
                    qtyInput.val('');
                }
            });

            if ((judgment === "NG" || ngCount > 0) && !hasAtLeastOneValidDefect) {
                Swal.fire({ icon: "warning", title: "Defect Belum Dipilih" });
                return false;
            }

            if (defectMissing) {
                Swal.fire({ icon: "warning", title: "Jenis Defect Belum Dipilih" });
                return false;
            }

            if (dimensionQtyEmpty) {
                Swal.fire({ icon: 'warning', title: 'Qty Defect Dimensi Wajib Diisi' });
                return false;
            }
        }

        if (this.timerRunning) {
            clearInterval(this.timerInterval);
            this.timerRunning = false;
            $("#cycleTimeInput").val(this.totalSeconds);
        }

        const saveBtn = $("#saveBtn");
        const originalHtml = saveBtn.html();
        saveBtn
            .prop("disabled", true)
            .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        const formData = new FormData(form);
        const actionUrl = $(form).attr("action");

        $.ajax({
            url: actionUrl,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: isHardwareScan ? "QR Berhasil Discan" : "Berhasil",
                        text: isHardwareScan ? "QR berhasil discan & data berhasil disimpan." : "Data Berhasil Disimpan",
                        showCancelButton: !isHardwareScan,
                        confirmButtonText: isHardwareScan ? "OK" : "Lihat Data",
                        timer: isHardwareScan ? 1500 : null,
                        timerProgressBar: isHardwareScan,
                    }).then((result) => {
                        if (result.isConfirmed && !isHardwareScan)
                            window.location.href = response.index_url;
                        else {
                            _this.resetState();
                            if (isHardwareScan) {
                                _this.startTimer();
                            }
                        }
                    });
                }
            },
            error: function (xhr) {
                console.error("Save Error:", xhr);
                let errorMsg = "Gagal menyimpan data.";

                if (xhr.status === 422 && xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const firstError = Object.values(
                            xhr.responseJSON.errors,
                        )[0][0];
                        errorMsg = "Validasi gagal: " + firstError;
                    }
                } else if (xhr.status === 419) {
                    errorMsg =
                        "Sesi telah berakhir (CSRF), silakan segarkan halaman.";
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: "error",
                    title: "Kesalahan",
                    text: errorMsg,
                });
                saveBtn.prop("disabled", false).html(originalHtml);
            },
        });
    }

    restorePersistentFields() {
        const fields = [
            { id: "line", key: "last_subassy_line_selection" },
            { id: "shiftSelect", key: "last_subassy_shift_selection" }
        ];
        fields.forEach(field => {
            const $el = field.id
                ? $("#" + field.id)
                : $(`input[name="${field.name}"], select[name="${field.name}"]`);
            if (!$el.length) return;
            const savedVal = localStorage.getItem(field.key);
            if (savedVal) $el.val(savedVal).trigger("change");
        });
    }

    resetState() {
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

        // Lock form dan tunggu scan berikutnya
        this.formInputs.prop("disabled", true);
        $("#checksheetForm").addClass("inputs-locked");
        $("#saveBtn").prop("disabled", true).html('<i class="fas fa-save fa-sm"></i> Simpan Data');
        $("#scanMethodInput").val("");

        $("#addDefectBtn").hide();
        $(".defect-row").not(":first").remove();
        $("#itemSelect").val("").trigger("change");
        $("#aql_info").hide();
        $("#nextProsesContainer").hide();
        $("#judgmentBadge").addClass("d-none").text("-");
        $("#judgmentSelect").val("");
        // Jangan reset tampilan PDF agar tetap muncul pada halaman untuk kenyamanan scan berulang
        // $("#standardPdfCanvas, #similarPdfCanvas").hide().addClass("d-none");
        // $("#standardPdfPlaceholder, #similarPdfPlaceholder")
        //     .removeClass("d-none")
        //     .addClass("d-flex");
        // this.refStandardPdfDoc = null;
        // this.refSimilarPdfDoc = null;
        // this.updateRefNavControls();
        this.restorePersistentFields();

        // Buka kembali input scan untuk siklus berikutnya
        $("#sapCodeInput").prop("disabled", false).css("background", "");
        this.isProcessingScan = false;
        setTimeout(() => { $("#sapCodeInput").focus(); }, 600);
    }

    handleOpenPdf(e) {
        this.currentItemId = $(e.currentTarget).data("id");
        this.totalPdfFiles = $(e.currentTarget).data("count");
        this.currentPdfIndex = 0;
        $("#pdfModal").modal("show");
        this.loadPdf(this.currentItemId, this.currentPdfIndex);
    }

    loadPdf(id, index) {
        const url = this.config.pdfUrlPattern
            .replace("ID_PLACEHOLDER", id)
            .replace("INDEX_PLACEHOLDER", index);
        this.pdfDoc = null;
        this.pageNum = 1;
        const canvas = document.getElementById("the-canvas");
        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById("pageInfo").textContent = "Loading...";
        document.getElementById("pdfInfo").textContent =
            `File ${index + 1} of ${this.totalPdfFiles}`;

        pdfjsLib.getDocument(url).promise.then(
            (pdf) => {
                this.pdfDoc = pdf;
                document.getElementById("pageInfo").textContent =
                    `Page 1 of ${this.pdfDoc.numPages}`;
                this.renderPage(1);
            },
            (err) => {
                console.error(err);
                alert("Error loading PDF: " + err.message);
            },
        );
    }

    renderPage(num) {
        this.pageRendering = true;
        this.pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: this.scale });
            const canvas = document.getElementById("the-canvas");
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            const ctx = canvas.getContext("2d");
            page.render({
                canvasContext: ctx,
                viewport: viewport,
            }).promise.then(() => {
                this.pageRendering = false;
                if (this.pageNumPending !== null) {
                    this.renderPage(this.pageNumPending);
                    this.pageNumPending = null;
                }
            });
        });
        document.getElementById("pageInfo").textContent =
            `Page ${num} of ${this.pdfDoc.numPages}`;
    }

    queueRenderPage(num) {
        if (this.pageRendering) this.pageNumPending = num;
        else this.renderPage(num);
    }

    handlePrevPage() {
        if (this.pageNum > 1) {
            this.pageNum--;
            this.queueRenderPage(this.pageNum);
        }
    }
    handleNextPage() {
        if (this.pdfDoc && this.pageNum < this.pdfDoc.numPages) {
            this.pageNum++;
            this.queueRenderPage(this.pageNum);
        }
    }
    handlePdfZoom(v) {
        this.scale += v;
        this.queueRenderPage(this.pageNum);
    }
    handlePrevPdf() {
        if (this.currentPdfIndex > 0) {
            this.currentPdfIndex--;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        }
    }
    handleNextPdf() {
        if (this.currentPdfIndex < this.totalPdfFiles - 1) {
            this.currentPdfIndex++;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        }
    }

    handleImageZoom(v) {
        this.currentZoom = (this.currentZoom || 1) + v;
        this.updateImageZoom();
    }
    updateImageZoom() {
        const zoom = this.currentZoom || 1;
        const origin = zoom > 1 ? "top center" : "center center";
        $("#modalImage").css({
            transform: `scale(${zoom})`,
            "transform-origin": origin,
        });
    }
    handleImageModal(e) {
        const btn = $(e.relatedTarget);
        $("#modalImage").attr("src", btn.data("image"));
        $("#modalTitle").text(btn.data("title"));
        $("#modalDescription").text(btn.data("description"));
        this.currentZoom = 1;
        this.updateImageZoom();
    }
}

window.initSubAssyIndex = (config) => new SubAssyIndex(config);
window.initSubAssyCreate = (config) => new SubAssyCreate(config);
