/**
 * Modul Double Tape
 * Menangani tampilan Index dan Create/Edit untuk Checksheet Double Tape
 */

class DoubleTapeIndex {
    constructor(config = {}) {
        this.config = {
            indexRoute: config.indexRoute || "",
            ...config,
        };
        this.init();
    }

    init() {
        this.initLiveSearch();
        this.initModals();
        this.initAjaxForms();
        this.initQRDetail();
        if (this.config && this.config.btnScanId) {
            this.initQRScanner();
        }
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById("liveSearch");
        if (liveSearchInput) {
            let searchTimeout;
            liveSearchInput.addEventListener("input", () => {
                const searchTerm = liveSearchInput.value.trim();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const params = new URLSearchParams(window.location.search);
                    if (searchTerm) params.set("search", searchTerm);
                    else params.delete("search");
                    params.delete("page");
                    window.location.href = `${this.config.indexRoute}?${params.toString()}`;
                }, 500);
            });
        }
    }

    initModals() {
        // Modal Edit
        $(document).on("click", ".btn-edit-modal", (e) => {
            e.preventDefault();
            const url = $(e.currentTarget).attr("href");
            $("#editModal").modal("show");
            $("#editModalBody").html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>',
            );
            $.ajax({
                url: url,
                success: (response) => {
                    $("#editModalBody").html(response);
                },
                error: (xhr) => {
                    let message = "Gagal memuat data.";
                    if (xhr.status === 403) message = "Akses ditolak.";
                    $("#editModalBody").html(
                        '<div class="alert alert-danger">' + message + "</div>",
                    );
                },
            });
        });

        // Modal Status
        $(document).on("click", ".btn-status-modal", (e) => {
            e.preventDefault();
            const url = $(e.currentTarget).attr("href");
            $("#statusModal").modal("show");
            $("#statusModalBody").html(
                '<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div></div>',
            );
            $.ajax({
                url: url,
                success: (response) => {
                    $("#statusModalBody").html(response);
                },
                error: () => {
                    $("#statusModalBody").html(
                        '<div class="alert alert-danger">Gagal memuat data.</div>',
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
                data: $form.serialize(),
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        window.location.reload();
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

            _this.qrScanner._setVideoMirror = function (facingMode) {};

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
        } catch (e) {}
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

class DoubleTapeCreate {
    constructor(config = {}) {
        this.config = {
            pdfWorkerSrc:
                config.pdfWorkerSrc ||
                "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js",
            pdfUrlPattern: config.pdfUrlPattern || "",
            ...config,
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
        this.refStandardFiles = [];
        this.refStandardFileIndex = 0;

        // State Modal PDF
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pageRendering = false;
        this.pageNumPending = null;
        this.scale = 1.5;
        this.currentPdfIndex = 0;
        this.totalPdfFiles = 0;
        this.currentItemId = null;

        // QR Scanner Logic
        this.qrScanner = null;

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
        this.initQRScanner();
        this.initFormSubmission();

        // Inisialisasi awal untuk logic kalkulasi & judgment
        this.calculateTotalNG();
        this.updateJudgment();

        // Picu jika item dipilih saat dimuat
        setTimeout(() => {
            if ($("#itemSelect").val()) {
                $("#itemSelect").trigger("change");
            }
        }, 500);
    }

    initPdfJS() {
        if (typeof pdfjsLib !== "undefined") {
            pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;
        }
    }

    initQRScanner() {
        const _this = this;
        $("#btnScanQR").on("click", () => {
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
                    <small>Pastikan Anda menggunakan koneksi aman (HTTPS atau localhost).</small>
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
        this.parseAndFillQR(decodedText);
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

    parseAndFillQR(decodedText) {
        try {
            $("#qrcodeInput").val(decodedText);
            const parts = decodedText.split("|");
            if (parts.length >= 5) {
                // 1. Validasi QR Duplikat via AJAX
                if (this.config.qrUniqueUrl) {
                    $.get(
                        this.config.qrUniqueUrl,
                        { qrcode: decodedText },
                        (res) => {
                            if (res.success && !res.unique) {
                                Swal.fire(
                                    "QR Sudah Digunakan",
                                    res.message,
                                    "error",
                                );
                            } else {
                                this.processFillQR(decodedText, parts);
                            }
                        },
                    ).fail(() => {
                        this.processFillQR(decodedText, parts);
                    });
                } else {
                    this.processFillQR(decodedText, parts);
                }
            } else {
                Swal.fire(
                    "Format QR Salah",
                    "Data QR tidak sesuai standar (" + decodedText + ")",
                    "warning",
                );
            }
        } catch (e) {
            console.error("Parse QR Error:", e);
            Swal.fire(
                "Error",
                "Gagal memproses data QR: " + e.message,
                "error",
            );
        }
    }

    processFillQR(decodedText, parts) {
        try {
            const partCode = parts[0].trim();
            const supplierId = parts[1].trim();
            const quantity = parts[2].trim();
            const uniqueCode = parts[3].trim();
            const sapCode = parts[4].trim();

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

                Swal.fire({
                    icon: "success",
                    title: "QR Berhasil Discan",
                    text: "Item otomatis terpilih.",
                    timer: 1500,
                    showConfirmButton: false,
                });
            } else {
                Swal.fire(
                    "Info",
                    "Data item QR terbaca, tetapi tidak tersedia untuk plant ini. Silahkan cari manual.",
                    "warning",
                );
            }

            $("#totalQty").val(quantity).trigger("input");
        } catch (e) {
            console.error("Fill QR Error:", e);
            Swal.fire("Error", "Gagal mengisi data QR: " + e.message, "error");
        }
    }

    initInputLocking() {
        this.formInputs = $(
            '#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)',
        );
        this.formInputs.prop("disabled", true);
        $("#checksheetForm").addClass("inputs-locked");
    }

    initTimer() {
        $("#startTimerBtn").on("click", () => {
            if (!this.timerRunning) {
                this.timerRunning = true;
                $("#startTimerBtn")
                    .removeClass("btn-success")
                    .addClass("btn-secondary")
                    .attr("disabled", true)
                    .html('<i class="fas fa-clock"></i> Running...');
                $("#saveBtn").prop("disabled", false);

                // Buka kunci input
                this.formInputs.prop("disabled", false);
                $("#checksheetForm").removeClass("inputs-locked");

                // Logika readonly spesifik — total_ok & total_ng readonly (auto-kalkulasi)
                $("#samplingQty").prop("readonly", this.isFullcheck);
                $('input[name="total_ok"]').prop("readonly", true);
                $('input[name="total_ng"]').prop("readonly", true);

                // Inisialisasi nilai awal: OK = samplingQty, NG = 0
                this.calculateTotalNG();

                this.timerInterval = setInterval(() => {
                    this.totalSeconds++;
                    this.updateTimerDisplay();
                }, 1000);

                $("#itemSelect").focus();
            }
        });
    }

    updateTimerDisplay() {
        const hours = Math.floor(this.totalSeconds / 3600);
        const minutes = Math.floor((this.totalSeconds % 3600) / 60);
        const seconds = this.totalSeconds % 60;
        const text = [hours, minutes, seconds]
            .map((v) => (v < 10 ? "0" + v : v))
            .join(":");
        $("#timerDisplay").text(text);
        $("#cycleTimeInput").val(this.totalSeconds);
    }

    initTypeHandling() {
        $('input[name="check_type_option"]').on("change", (e) => {
            this.isFullcheck = $(e.currentTarget).val() === "fullcheck";

            // Sync hidden field for form submission
            $("#checkTypeHidden").val(
                this.isFullcheck ? "fullcheck" : "sampling",
            );

            // Toggle Judgment column visibility
            if (this.isFullcheck) {
                $(".judgment-header, .judgment-cell").hide();
                $("#samplingQtyHeader").text("Fullcheck Qty");
                // Fullcheck = pengecekan 100%, set judgment berdasarkan NG count
                $("#totalNG").trigger("input");
            } else {
                $(".judgment-header, .judgment-cell").show();
                $("#samplingQtyHeader").text("Sampling Qty");
            }

            if (this.timerRunning) {
                $("#samplingQty").prop("readonly", this.isFullcheck);
            }
            $("#totalQty").trigger("input");
            this.calculateTotalNG();
        });
    }

    updateJudgment() {
        // Trigger manual input untuk update judgment badge
        $("#totalNG").trigger("input");
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
            if (sampleSize >= 50) return { acc: 1, rej: 2 };
            if (sampleSize >= 32) return { acc: 0, rej: 1 };
            if (sampleSize >= 20) return { acc: 0, rej: 1 };
            return { acc: 0, rej: 1 };
        };

        $("#totalQty").on("input", (e) => {
            let lot = parseInt($(e.currentTarget).val()) || 0;
            if (lot > 0) {
                let sample = this.isFullcheck ? lot : getSampleSize(lot);
                $("#samplingQty").val(sample).trigger("input");
            } else {
                $("#samplingQty").val(0).trigger("input");
            }
            // Pastikan total_ok terinisialisasi setelah sampling_qty berubah
            this.calculateTotalNG();
        });

        $("#totalNG, #samplingQty").on("input", () => {
            let total = parseInt($("#samplingQty").val()) || 0;
            let ng = parseInt($("#totalNG").val()) || 0;
            let ok = total - ng;
            $('input[name="total_ok"]').val(ok < 0 ? 0 : ok);

            const judgmentSelect = $("#judgmentSelect");
            const judgmentBadge = $("#judgmentBadge");

            if (total > 0 || ng > 0) {
                const limits = this.isFullcheck
                    ? { acc: 0, rej: 1 }
                    : getAqlLimits(total);

                if (!this.isFullcheck) {
                    $("#acc_val").text(limits.acc);
                    $("#rej_val").text(limits.rej);
                    $("#aql_info").show();
                } else {
                    $("#aql_info").hide();
                }

                // Untuk fullcheck: NG > 0 = NG, NG = 0 = OK
                const isNG = this.isFullcheck ? ng > 0 : ng > limits.acc;

                if (!isNG) {
                    judgmentSelect.val("OK");
                    judgmentBadge
                        .text("OK")
                        .removeClass("d-none text-danger")
                        .addClass("text-success")
                        .css({
                            "border-color": "#28a745",
                            "background-color": "#fff",
                        });
                } else {
                    judgmentSelect.val("NG");
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
                // Default ke OK jika belum ada data
                judgmentSelect.val("OK");
                if (!this.isFullcheck) {
                    $("#aql_info").hide();
                    judgmentBadge.addClass("d-none").text("-");
                    judgmentSelect.val("");
                }
            }

            // Visibilitas Next Proses
            const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;
            if (judgmentSelect.val() === "NG" || ngCount > 0) {
                $("#nextProsesContainer").slideDown();
            } else {
                $("#nextProsesContainer").slideUp();
            }
        });

        $("#judgmentSelect").on("change", (e) => {
            const val = $(e.currentTarget).val();
            const judgmentBadge = $("#judgmentBadge");
            const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;

            if (val === "OK") {
                judgmentBadge
                    .text("OK")
                    .removeClass("d-none text-danger")
                    .addClass("text-success")
                    .css({
                        "border-color": "#28a745",
                        "background-color": "#fff",
                    });
            } else if (val === "NG") {
                judgmentBadge
                    .text("NG")
                    .removeClass("d-none text-success")
                    .addClass("text-danger")
                    .css({
                        "border-color": "#dc3545",
                        "background-color": "#fff",
                    });
            } else {
                judgmentBadge.addClass("d-none").text("-");
            }

            if (val === "NG" || ngCount > 0) {
                $("#nextProsesContainer").show();
            } else {
                $("#nextProsesContainer").hide();
            }
        });
    }

    initSAPCodeAutoSelect() {
        $("#sapCodeInput").on("input", (e) => {
            const sapCode = $(e.currentTarget).val().trim();
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
                    $("#sapCodeInput")
                        .removeClass("is-invalid")
                        .addClass("is-valid");
                } else {
                    $("#sapCodeInput")
                        .removeClass("is-valid")
                        .addClass("is-invalid");
                }
            } else {
                $("#sapCodeInput").removeClass("is-valid is-invalid");
            }
        });
    }

    initItemSelection() {
        $("#itemSelect").on("change", (e) => {
            const selectedOption = $(e.currentTarget).find("option:selected");
            const itemId = selectedOption.val();
            const files = selectedOption.data("files");
            const standardPdf = selectedOption.data("standard");
            const similarPdf = selectedOption.data("similar");
            let defects = selectedOption.data("defects");

            // Perbarui pratinjau Berdampingan
            let parsedFiles = files;
            if (typeof parsedFiles === 'string') { try { parsedFiles = JSON.parse(parsedFiles); } catch(e) { parsedFiles = []; } }
            this.refStandardFiles = Array.isArray(parsedFiles) ? parsedFiles : [];
            this.refStandardFileIndex = 0;
            this.refStandardPageNum = 1;

            if (standardPdf) {
                this.renderPdfToCanvas(
                    standardPdf,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                );
                $("#fullStandardBtn")
                    .attr("data-id", itemId)
                    .attr("data-count", files ? files.length : 1)
                    .show();
                $("#downloadStandardBtn").attr("href", standardPdf).show();
            } else {
                $("#standardPdfCanvas").addClass("d-none").hide();
                $("#standardPdfPlaceholder")
                    .removeClass("d-none")
                    .addClass("d-flex")
                    .find("p")
                    .text("Standard PDF tidak tersedia");
                $("#fullStandardBtn").hide();
                $("#downloadStandardBtn").hide();
            }

            if (similarPdf) {
                this.renderPdfToCanvas(
                    similarPdf,
                    "similarPdfCanvas",
                    "similarPdfPlaceholder",
                    "similarPdfLoading",
                );
                $("#fullSimilarBtn")
                    .attr("data-id", itemId)
                    .data("similar", true)
                    .show();
                $("#downloadSimilarBtn").attr("href", similarPdf).show();
                $("#similarStatusText").text("");
            } else {
                $("#similarPdfCanvas").addClass("d-none").hide();
                $("#similarPdfPlaceholder")
                    .removeClass("d-none")
                    .addClass("d-flex");
                $("#similarStatusText").text(
                    "Referral Similar Part tidak tersedia untuk item ini",
                );
                $("#fullSimilarBtn").hide();
                $("#downloadSimilarBtn").hide();
            }

            // Populasi defect
            const defectSelect = $("#defectSelect");
            defectSelect.html('<option value="">-- Pilih Defect --</option>');

            if (typeof defects === "string") {
                try {
                    defects = JSON.parse(defects);
                } catch (e) {
                    defects = null;
                }
            }

            if (Array.isArray(defects) && defects.length > 0) {
                defects.forEach((d) =>
                    defectSelect.append(`<option value="${d}">${d}</option>`),
                );
                if (
                    !defects.includes("Dimensi") &&
                    !defects.includes("dimension")
                ) {
                    defectSelect.append(
                        '<option value="Dimensi">Dimensi</option>',
                    );
                }
            } else {
                [
                    "BARET",
                    "SILVER",
                    "FLOW",
                    "FLASH",
                    "KOTOR",
                    "DENYUT",
                    "Dimensi",
                ].forEach((d) =>
                    defectSelect.append(`<option value="${d}">${d}</option>`),
                );
            }
            $("#addDefectBtn").show();
        });
    }

    initDefectManagement() {
        $("#addDefectBtn").on("click", () => {
            const firstSelect = $("#defectSelect");
            const clone = $(
                '<div class="input-group mb-2 defect-row">' +
                    '<select class="form-control defect-select" name="defect_types[]">' +
                    firstSelect.html() +
                    "</select>" +
                    '<input type="number" class="form-control defect-qty" name="defect_quantities[]" placeholder="Qty" min="1">' +
                    '<div class="input-group-append"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-minus"></i></button></div>' +
                    "</div>",
            );
            $("#defectContainer").append(clone);
        });

        $(document).on("click", ".btn-remove-row", (e) => {
            $(e.currentTarget).closest(".defect-row").remove();
            this.calculateTotalNG();
        });

        $(document).on("input", ".defect-qty", () => {
            this.calculateTotalNG();
        });
    }

    calculateTotalNG() {
        let totalNG = 0;
        $(".defect-qty").each(function () {
            totalNG += parseInt($(this).val()) || 0;
        });
        // Set total_ng dan total_ok langsung (keduanya readonly, di-set via JS)
        $('input[name="total_ng"]').val(totalNG);
        $("#totalNG").val(totalNG).trigger("input");
    }

    initPDFSideBySide() {
        window.renderPdfToCanvas = (
            url,
            canvasId,
            placeholderId,
            loadingId,
            pageNum = 1,
        ) => {
            this.renderPdfToCanvas(
                url,
                canvasId,
                placeholderId,
                loadingId,
                pageNum,
            );
        };

        $("#zoomInStandard").on("click", () => {
            this.standardZoomLevel += 0.25;
            if (this.refStandardPdfDoc)
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    document.getElementById("standardPdfCanvas"),
                    this.standardZoomLevel,
                    this.refStandardPageNum,
                    "standardPdfCanvas",
                );
        });
        $("#zoomOutStandard").on("click", () => {
            if (this.standardZoomLevel > 0.5) {
                this.standardZoomLevel -= 0.25;
                if (this.refStandardPdfDoc)
                    this.renderPageOnCanvas(
                        this.refStandardPdfDoc,
                        document.getElementById("standardPdfCanvas"),
                        this.standardZoomLevel,
                        this.refStandardPageNum,
                        "standardPdfCanvas",
                    );
            }
        });
        $("#zoomResetStandard").on("click", () => {
            this.standardZoomLevel = 1.0;
            if (this.refStandardPdfDoc)
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    document.getElementById("standardPdfCanvas"),
                    this.standardZoomLevel,
                    this.refStandardPageNum,
                    "standardPdfCanvas",
                );
        });

        $("#zoomInSimilar").on("click", () => {
            this.similarZoomLevel += 0.25;
            if (this.refSimilarPdfDoc)
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    document.getElementById("similarPdfCanvas"),
                    this.similarZoomLevel,
                    this.refSimilarPageNum,
                    "similarPdfCanvas",
                );
        });
        $("#zoomOutSimilar").on("click", () => {
            if (this.similarZoomLevel > 0.5) {
                this.similarZoomLevel -= 0.25;
                if (this.refSimilarPdfDoc)
                    this.renderPageOnCanvas(
                        this.refSimilarPdfDoc,
                        document.getElementById("similarPdfCanvas"),
                        this.similarZoomLevel,
                        this.refSimilarPageNum,
                        "similarPdfCanvas",
                    );
            }
        });
        $("#zoomResetSimilar").on("click", () => {
            this.similarZoomLevel = 1.0;
            if (this.refSimilarPdfDoc)
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    document.getElementById("similarPdfCanvas"),
                    this.similarZoomLevel,
                    this.refSimilarPageNum,
                    "similarPdfCanvas",
                );
        });

        $("#prevStandardPage").on("click", () => {
            if (this.refStandardPageNum > 1) {
                this.renderPdfToCanvas(
                    null,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    this.refStandardPageNum - 1,
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
        $("#nextStandardPage").on("click", () => {
            if (
                this.refStandardPdfDoc &&
                this.refStandardPageNum < this.refStandardPdfDoc.numPages
            ) {
                this.renderPdfToCanvas(
                    null,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    this.refStandardPageNum + 1,
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
        $("#prevSimilarPage").on("click", () => {
            if (this.refSimilarPageNum > 1)
                this.renderPdfToCanvas(
                    null,
                    "similarPdfCanvas",
                    "similarPdfPlaceholder",
                    "similarPdfLoading",
                    this.refSimilarPageNum - 1,
                );
        });
        $("#nextSimilarPage").on("click", () => {
            if (
                this.refSimilarPdfDoc &&
                this.refSimilarPageNum < this.refSimilarPdfDoc.numPages
            )
                this.renderPdfToCanvas(
                    null,
                    "similarPdfCanvas",
                    "similarPdfPlaceholder",
                    "similarPdfLoading",
                    this.refSimilarPageNum + 1,
                );
        });
    }

    renderPdfToCanvas(url, canvasId, placeholderId, loadingId, pageNum = 1) {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext("2d");
        const $placeholder = $("#" + placeholderId);
        const $loading = $("#" + loadingId);
        const $canvas = $(canvas);

        $placeholder.removeClass("d-flex").addClass("d-none");
        $canvas.addClass("d-none").hide();
        $loading.removeClass("d-none").addClass("d-flex");

        if (url === null) {
            if (canvasId === "standardPdfCanvas" && this.refStandardPdfDoc) {
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    canvas,
                    this.standardZoomLevel,
                    pageNum,
                    canvasId,
                );
            } else if (
                canvasId === "similarPdfCanvas" &&
                this.refSimilarPdfDoc
            ) {
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    canvas,
                    this.similarZoomLevel,
                    pageNum,
                    canvasId,
                );
            }
            return;
        }

        if (this.pdfCache[url]) {
            const doc = this.pdfCache[url];
            const zoom =
                canvasId === "standardPdfCanvas"
                    ? this.standardZoomLevel
                    : this.similarZoomLevel;
            this.renderPageOnCanvas(doc, canvas, zoom, pageNum, canvasId);
            return;
        }

        pdfjsLib
            .getDocument(url)
            .promise.then((pdf) => {
                this.pdfCache[url] = pdf;
                const zoom =
                    canvasId === "standardPdfCanvas"
                        ? this.standardZoomLevel
                        : this.similarZoomLevel;
                this.renderPageOnCanvas(pdf, canvas, zoom, pageNum, canvasId);
            })
            .catch((error) => {
                console.error("Error rendering preview PDF:", error);
                $loading.removeClass("d-flex").addClass("d-none");
                $placeholder
                    .removeClass("d-none")
                    .addClass("d-flex")
                    .find("p")
                    .text("Gagal memuat PDF");
            });
    }

    renderPageOnCanvas(pdf, canvas, zoomLevel, pageNum, canvasId) {
        const ctx = canvas.getContext("2d");
        const $loading =
            canvasId === "standardPdfCanvas"
                ? $("#standardPdfLoading")
                : $("#similarPdfLoading");
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
                $canvas.css({ width: "auto", "max-width": "none" });
            } else {
                $canvas.css({ width: "100%", "max-width": "100%" });
            }
            $canvas.css("height", "auto");

            const renderContext = {
                canvasContext: ctx,
                viewport: scaledViewport,
            };
            page.render(renderContext).promise.then(() => {
                $loading.removeClass("d-flex").addClass("d-none");
                $canvas.removeClass("d-none").show();

                if (canvasId === "standardPdfCanvas") {
                    this.refStandardPdfDoc = pdf;
                    this.refStandardPageNum = pageNum;
                    const fileInfo = this.refStandardFiles.length > 1 ? ` (${this.refStandardFileIndex + 1}/${this.refStandardFiles.length})` : '';
                    $("#standardPageInfo").text(
                        "P " + pageNum + "/" + pdf.numPages + fileInfo,
                    );
                    $(".standard-nav-controls").attr(
                        "style",
                        "display: flex !important;",
                    );
                } else if (canvasId === "similarPdfCanvas") {
                    this.refSimilarPdfDoc = pdf;
                    this.refSimilarPageNum = pageNum;
                    $("#similarPageInfo").text(
                        "P " + pageNum + "/" + pdf.numPages,
                    );
                    $(".similar-nav-controls").attr(
                        "style",
                        "display: flex !important;",
                    );
                }
            });
        });
    }

    initPDFModal() {
        this.pdfCanvas = document.getElementById("the-canvas");
        this.pdfCtx = this.pdfCanvas.getContext("2d");

        $("#prevPage").on("click", () => {
            if (this.pageNum <= 1) return;
            this.pageNum--;
            this.queueRenderPage(this.pageNum);
        });

        $("#nextPage").on("click", () => {
            if (this.pageNum >= this.pdfDoc.numPages) return;
            this.pageNum++;
            this.queueRenderPage(this.pageNum);
        });

        $("#pdfZoomIn").on("click", () => {
            this.scale += 0.25;
            this.queueRenderPage(this.pageNum);
        });

        $("#pdfZoomOut").on("click", () => {
            if (this.scale > 0.25) {
                this.scale -= 0.25;
                this.queueRenderPage(this.pageNum);
            }
        });

        $("#pdfZoomReset").on("click", () => {
            this.scale = 1.0;
            this.queueRenderPage(this.pageNum);
        });

        $("#prevPdf").on("click", () => {
            if (this.currentPdfIndex <= 0) return;
            this.currentPdfIndex--;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });

        $("#nextPdf").on("click", () => {
            if (this.currentPdfIndex >= this.totalPdfFiles - 1) return;
            this.currentPdfIndex++;
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });

        $(document).on("click", ".view-pdf-btn", (e) => {
            const btn = $(e.currentTarget);
            this.currentItemId = btn.data("id");
            const isSimilar = btn.data("similar");

            if (isSimilar) {
                this.totalPdfFiles = 1;
                this.currentPdfIndex = "similar";
            } else {
                this.totalPdfFiles = btn.data("count");
                this.currentPdfIndex = 0;
            }

            $("#pdfModal").modal("show");
            this.loadPdf(this.currentItemId, this.currentPdfIndex);
        });
    }

    loadPdf(itemId, index) {
        const url = this.config.pdfUrlPattern
            .replace("ID_PLACEHOLDER", itemId)
            .replace("INDEX_PLACEHOLDER", index);
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pdfCtx.clearRect(
            0,
            0,
            this.pdfCanvas.width,
            this.pdfCanvas.height,
        );
        document.getElementById("pageInfo").textContent = "Loading...";

        if (index === "similar") {
            document.getElementById("pdfInfo").textContent = "Similar Part PDF";
            $("#prevPdf, #nextPdf").hide();
        } else {
            document.getElementById("pdfInfo").textContent =
                `File ${index + 1} of ${this.totalPdfFiles}`;
            if (this.totalPdfFiles <= 1) {
                $("#prevPdf, #nextPdf").hide();
            } else {
                $("#prevPdf, #nextPdf").show();
            }
        }

        pdfjsLib
            .getDocument(url)
            .promise.then((pdfDoc_) => {
                this.pdfDoc = pdfDoc_;
                document.getElementById("pageInfo").textContent =
                    "Page 1 of " + this.pdfDoc.numPages;
                this.renderPage(this.pageNum);
            })
            .catch((reason) => {
                console.error(reason);
                alert("Error loading PDF: " + (reason.message || reason));
            });
    }

    renderPage(num) {
        this.pageRendering = true;
        this.pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: this.scale });
            this.pdfCanvas.height = viewport.height;
            this.pdfCanvas.width = viewport.width;
            const renderContext = {
                canvasContext: this.pdfCtx,
                viewport: viewport,
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
        document.getElementById("pageInfo").textContent =
            `Page ${num} of ${this.pdfDoc.numPages}`;
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
        $("#zoomIn").on("click", () => {
            this.currentZoom += 0.2;
            $("#modalImage").css(
                "transform",
                "scale(" + this.currentZoom + ")",
            );
        });
        $("#zoomOut").on("click", () => {
            if (this.currentZoom > 0.4) {
                this.currentZoom -= 0.2;
                $("#modalImage").css(
                    "transform",
                    "scale(" + this.currentZoom + ")",
                );
            }
        });
        $("#zoomReset").on("click", () => {
            this.currentZoom = 1;
            $("#modalImage").css("transform", "scale(1)");
        });

        $("#imageModal").on("show.bs.modal", (event) => {
            const button = $(event.relatedTarget);
            const image = button.data("image") || button.attr("src");
            const title = button.data("name") || "Detail Gambar";
            const desc = button.data("description") || "";

            $("#modalImage").attr("src", image).css("transform", "scale(1)");
            $("#modalTitle").text(title);
            $("#modalDescription").text(desc);
            this.currentZoom = 1;
        });
    }

    initFormSubmission() {
        $(document).on("submit", "#checksheetForm", (e) => {
            const $form = $(e.target);
            e.preventDefault();

            console.log("Submitting Double Tape Form...");

            const judgment = $("#judgmentSelect").val();
            const nextProses = $("#nextProses").val();
            const itemId = $("#itemSelect").val();
            const totalQty = $('input[name="total_qty"]').val();
            const samplingQty = $('input[name="sampling_qty"]').val();
            const operatorInitials = $('input[name="operator_initials"]').val();

            // 1. Validasi: Item harus dipilih
            if (!itemId) {
                Swal.fire({
                    icon: "warning",
                    title: "Item Belum Dipilih",
                    text: "Silakan pilih item terlebih dahulu!",
                });
                $("#itemSelect").addClass("is-invalid").focus();
                setTimeout(() => $("#itemSelect").removeClass("is-invalid"), 3000);
                return false;
            }

            // 2. Validasi: Total Qty
            if (!totalQty || totalQty <= 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Total Qty Belum Diisi",
                    text: "Silakan isi Total Qty produksi terlebih dahulu!",
                });
                $('input[name="total_qty"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="total_qty"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 3. Validasi: Sampling Qty
            if (!samplingQty || samplingQty <= 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Sampling Qty Belum Diisi",
                    text: "Silakan isi Sampling Qty terlebih dahulu!",
                });
                $('input[name="sampling_qty"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="sampling_qty"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 4. Validasi: Pilihan Defect (NG)
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

            // 8. Validasi: Qty Defect Dimensi Wajib Diisi (jika terpilih)
            let dimensionDefectSelected = false;
            let dimensionQtyEmpty = false;
            $(".defect-select").each(function () {
                const text = $(this).find("option:selected").text().toLowerCase();
                if ($(this).val() === "dimension" || text === "dimensi") {
                    dimensionDefectSelected = true;
                    const qtyInput = $(this).closest(".defect-row").find(".defect-qty");
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

            // 4. Validasi: NG harus pilih Next Proses
            const finalJudgment = $("#judgmentSelect").val() || judgment;
            if (finalJudgment === "NG" && !nextProses) {
                Swal.fire({
                    icon: "warning",
                    title: "Next Proses Wajib Dipilih",
                    text: "Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!",
                    confirmButtonColor: "#3085d6",
                });
                $("#nextProses").addClass("is-invalid").focus();
                setTimeout(() => $("#nextProses").removeClass("is-invalid"), 3000);
                return false;
            }

            // 5. Validasi: Inisial QC
            if (!operatorInitials) {
                Swal.fire({
                    icon: "warning",
                    title: "Inisial Belum Diisi",
                    text: "Silakan isi Inisial QC terlebih dahulu!",
                });
                $('input[name="operator_initials"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="operator_initials"]').removeClass("is-invalid"), 3000);
                return false;
            }

            if (this.timerRunning) {
                clearInterval(this.timerInterval);
                this.timerRunning = false;
                $("#cycleTimeInput").val(this.totalSeconds);
            }

            // Bersihkan defect yang dipilih tapi tidak ada qty atau qty = 0
            $(".defect-row").each(function () {
                const type = $(this).find(".defect-select").val();
                const qty = parseInt($(this).find(".defect-qty").val()) || 0;

                if (type && qty === 0) {
                    $(this).find(".defect-select").val("");
                    $(this).find(".defect-qty").val("");
                }
            });
            // Hitung ulang total NG jika ada manipulasi kolom defect
            this.calculateTotalNG();

            // Normalisasi nilai numerik agar tidak kosong (server butuh integer)
            ["total_qty", "sampling_qty", "total_ok", "total_ng"].forEach(
                (name) => {
                    const input = $(`input[name="${name}"]`);
                    if (input.val() === "" || input.val() === null) {
                        input.val(0);
                    }
                },
            );

            const saveBtn = $("#saveBtn");
            const originalHtml = saveBtn.html();
            saveBtn
                .prop("disabled", true)
                .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            const formData = new FormData(e.currentTarget);

            $.ajax({
                url: $(e.currentTarget).attr("action"),
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    $("#global-loader").hide();
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            text: "Data Berhasil Disimpan",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#6c757d",
                            confirmButtonText: "Lihat Data",
                            cancelButtonText: "Tutup",
                            reverseButtons: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.index_url;
                            } else {
                                $("#checksheetForm")[0].reset();
                                this.resetState();
                            }
                        });
                    }
                },
                error: (xhr) => {
                    $("#global-loader").hide();
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

    resetState() {
        clearInterval(this.timerInterval);
        this.timerRunning = false;
        this.totalSeconds = 0;
        this.updateTimerDisplay();
        $("#startTimerBtn")
            .removeClass("btn-secondary")
            .addClass("btn-success")
            .removeAttr("disabled")
            .html('<i class="fas fa-play"></i> Start');

        this.formInputs.prop("disabled", true);
        $("#checksheetForm").addClass("inputs-locked");
        $("#saveBtn").prop("disabled", true);

        $("#addDefectBtn").hide();
        $("#defectContainer").find(".defect-row").not(":first").remove();
        $("#itemSelect").val("").trigger("change");
        $("#aql_info").hide();
        $("#nextProsesContainer").hide();

        $("#standardPdfCanvas, #similarPdfCanvas").addClass("d-none").hide();
        $("#standardPdfPlaceholder")
            .removeClass("d-none")
            .addClass("d-flex")
            .find("p")
            .text("Pilih Item untuk menampilkan Standard PDF");
        $("#similarPdfPlaceholder")
            .removeClass("d-none")
            .addClass("d-flex")
            .find("p")
            .text("Pilih Item untuk menampilkan Similar Part");
        $("#similarStatusText").text("");
        $("#fullStandardBtn, #fullSimilarBtn").hide();
        $(".standard-nav-controls, .similar-nav-controls").hide();

        this.standardZoomLevel = 1.0;
        this.similarZoomLevel = 1.0;

        $("#judgmentBadge").addClass("d-none").text("-");
        $("#judgmentSelect").val("").removeClass("text-success text-danger");

        // Reset nilai auto-fill
        $('input[name="total_ok"]').val(0).prop("readonly", true);
        $('input[name="total_ng"]').val(0).prop("readonly", true);
        $("#totalNG").val(0);
        $('input[name="total_qty"]').val("");
        $('input[name="sampling_qty"]').val("");
        $(".defect-qty").val("");

        $("#checkTypeSampling").prop("checked", true).trigger("change");

        // Reset QR fields
        $(
            "#qrcodeInput, #partCodeInput, #supplierIdInput, #quantityInput, #uniqueCodeInput, #sapCodeInputHidden",
        ).val("");
        $("#sapCodeInput").removeClass("is-valid is-invalid").val("");

        $("#labelSampling").addClass("active");
        $("#labelFullcheck").removeClass("active");
    }
}

// Global initializers
window.initDoubleTapeIndex = (config) => new DoubleTapeIndex(config);
window.initDoubleTapeCreate = (config) => new DoubleTapeCreate(config);
