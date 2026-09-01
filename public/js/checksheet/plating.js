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
        if (this.config && this.config.btnScanId) {
            this.initQRScanner();
        }
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById("liveSearch");
        if (!liveSearchInput) return;

        let searchTimeout;
        liveSearchInput.addEventListener("input", () => {
            const searchTerm = liveSearchInput.value.trim();
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const startDate = document.getElementById("start_date").value;
                const endDate = document.getElementById("end_date").value;
                const params = new URLSearchParams(window.location.search);
                if (searchTerm) params.set("search", searchTerm);
                else params.delete("search");
                if (startDate) params.set("start_date", startDate);
                if (endDate) params.set("end_date", endDate);
                params.delete("page");
                window.location.href =
                    this.config.indexRoute + "?" + params.toString();
            }, 500);
        });
    }

    initAjaxModals() {
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
                    if (window.initPlatingEdit) window.initPlatingEdit();
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
        $(document).on("click submit focus", ".ajax-form", function () {
            if (!$(this).attr("novalidate")) {
                $(this).attr("novalidate", "novalidate");
            }
        });

        $(document).on("submit", ".ajax-form", function (e) {
            const $form = $(this);
            e.preventDefault();

            const $submitBtn = $form.find('button[type="submit"]');
            const $modalErrors = $form.find("#modal-errors");
            const originalBtnHtml = $submitBtn.html();

            $modalErrors.hide().empty();
            $form.find(".is-invalid").removeClass("is-invalid");

            // Client-side validation check for required fields
            let emptyRequiredFields = [];
            $form.find("input[required], select[required], textarea[required]").each(function () {
                const val = $(this).val();
                if (!val || (typeof val === "string" && val.trim() === "")) {
                    $(this).addClass("is-invalid");
                    let fieldName = $(this).attr("data-field-name") || $(this).attr("placeholder");
                    if (!fieldName) {
                        const $parentGroup = $(this).closest(".form-group, .col-md-6, .col-6, td");
                        let $label = $parentGroup.find("label").first();
                        if ($label.length) {
                            fieldName = $label.text().replace(/\*/g, "").trim();
                        }
                    }
                    if (!fieldName) {
                        fieldName = $(this).attr("name");
                    }
                    if (fieldName && !emptyRequiredFields.includes(fieldName)) {
                        emptyRequiredFields.push(fieldName);
                    }
                }
            });

            if (emptyRequiredFields.length > 0) {
                let errorList = emptyRequiredFields.map(f => '<li class="mb-1"><strong>' + f + '</strong> wajib diisi.</li>').join('');
                let htmlMessage = '<div class="text-left"><p class="text-muted small mb-2">Mohon lengkapi kolom berikut sebelum menyimpan:</p><ul class="pl-3 mb-0 small text-danger">' + errorList + '</ul></div>';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap!',
                        html: htmlMessage,
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#e74a3b'
                    });
                } else {
                    alert("Mohon lengkapi semua field yang wajib diisi:\n- " + emptyRequiredFields.join("\n- "));
                }
                return false;
            }

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
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'Data berhasil disimpan.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            window.location.reload();
                        }
                    } else {
                        const msg = response.message || "Terjadi kesalahan saat menyimpan data.";
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan!',
                                text: msg,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#e74a3b'
                            });
                        } else {
                            alert(msg);
                        }
                        $submitBtn
                            .prop("disabled", false)
                            .html(originalBtnHtml);
                    }
                },
                error: function (xhr) {
                    $submitBtn.prop("disabled", false).html(originalBtnHtml);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorList = [];
                        $.each(errors, function (field, messages) {
                            errorList.push('<li class="mb-1">' + messages[0] + '</li>');
                            $form
                                .find('[name="' + field + '"]')
                                .addClass("is-invalid");
                        });
                        let htmlMessage = '<div class="text-left"><p class="text-muted small mb-2">Terjadi kesalahan validasi:</p><ul class="pl-3 mb-0 small text-danger">' + errorList.join('') + '</ul></div>';
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal!',
                                html: htmlMessage,
                                confirmButtonText: 'Perbaiki Input',
                                confirmButtonColor: '#e74a3b'
                            });
                        } else {
                            alert("Validasi Gagal:\n- " + Object.values(errors).map(e => e[0]).join("\n- "));
                        }
                    } else {
                        const message = xhr.responseJSON
                            ? xhr.responseJSON.message
                            : "Terjadi kesalahan sistem.";
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan!',
                                text: message,
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#858796'
                            });
                        } else {
                            alert(message);
                        }
                    }
                },
            });
        });
    }

    initQRDetail() {
        // ── QR Parsers ─────────────────────────────────────────────────────────

        // WIP / QC Verifikasi: PartCode|PO|Qty|LotCode|SapCode  (5 segments)
        const parseWipQr = (qr) => {
            if (!qr || qr === '-') return null;
            const p = qr.split('|');
            if (p.length < 5) return null;
            return { part: p[0], po: p[1], qty: p[2], lot: p[3], sap: p[4] };
        };

        // Plating Pasang: PartCode|LotId|UniqueCode|DateOpsShift|Qty|JIG-xxx  (6 segments, last starts with JIG-)
        const parsePasangQr = (qr) => {
            if (!qr || qr === '-') return null;
            const p = qr.split('|');
            if (p.length < 6 || !p[5].startsWith('JIG-')) return null;
            const raw = p[3]; // e.g. "04062026IPKLJK1"
            let dateStr = '-', ops = '-';
            if (raw.length >= 8) {
                const dd = raw.substring(0, 2);
                const mm = raw.substring(2, 4);
                const yy = raw.substring(4, 8);
                dateStr = `${dd}-${mm}-${yy}`;
                const tail = raw.substring(8); // e.g. "IPKLJK1"
                if (tail.length > 0) {
                    const shift = tail.slice(-1);
                    const oper = tail.slice(0, -1) || '-';
                    ops = `${oper} / Shift ${shift}`;
                }
            }
            return { part: p[0], lot: p[1], unique: p[2], date: dateStr, ops: ops, qty: p[4], jig: p[5] };
        };

        // Plating Cabut: PartCode|PO|QtyOrig|LotCabut|QtySplit|CBT-xxx  (6 segments, last starts with CBT-)
        const parseCabutQr = (qr) => {
            if (!qr || qr === '-') return null;
            const p = qr.split('|');
            if (p.length < 6 || !p[5].startsWith('CBT-')) return null;
            const raw = p[3]; // e.g. "04062026AJ2"
            let dateStr = '-', ops = '-';
            if (raw.length >= 8) {
                const dd = raw.substring(0, 2);
                const mm = raw.substring(2, 4);
                const yy = raw.substring(4, 8);
                dateStr = `${dd}-${mm}-${yy}`;
                const tail = raw.substring(8); // e.g. "AJ2"
                if (tail.length > 0) {
                    const shift = tail.slice(-1);
                    const oper = tail.slice(0, -1) || '-';
                    ops = `${oper} / Shift ${shift}`;
                }
            }
            return { part: p[0], po: p[1], qtyOrig: p[2], date: dateStr, ops: ops, qtySplit: p[4], bucket: p[5] };
        };

        // ── Click Handler ──────────────────────────────────────────────────────
        $(document).on("click", ".btn-qr-detail", function () {
            const data = $(this).data();
            const wipQr = data.qrWip || '-';
            const pasangQr = data.qrPasang || '-';
            const cabutQr = data.qrCabut || '-';
            const qcQr = data.qrQc || '-';

            // Populate raw QR strings
            $("#modal-trace-wip").text(wipQr);
            $("#modal-trace-pasang").text(pasangQr);
            $("#modal-trace-cabut").text(cabutQr);
            $("#modal-trace-qc").text(qcQr);

            // ── Stage 1: WIP ──────────────────────────────────────────────────
            const wip = parseWipQr(wipQr);
            if (wip) {
                $("#trace-wip-part").text(wip.part);
                $("#trace-wip-po").text(wip.po);
                $("#trace-wip-qty").text(wip.qty + ' pcs');
                $("#trace-wip-lot").text(wip.lot);
                $("#trace-wip-sap").text(wip.sap);
                $("#trace-detail-wip").removeClass('d-none');
            } else {
                $("#trace-detail-wip").addClass('d-none');
            }

            // ── Stage 2: Plating Pasang ───────────────────────────────────────
            const pasang = parsePasangQr(pasangQr);
            if (pasang) {
                $("#trace-pasang-part").text(pasang.part);
                $("#trace-pasang-lot").text(pasang.lot);
                $("#trace-pasang-unique").text(pasang.unique);
                $("#trace-pasang-date").text(pasang.date);
                $("#trace-pasang-ops").text(pasang.ops);
                $("#trace-pasang-qty").text(pasang.qty + ' pcs');
                $("#trace-pasang-jig").text(pasang.jig);
                $("#trace-detail-pasang").removeClass('d-none');
            } else {
                $("#trace-detail-pasang").addClass('d-none');
            }

            // ── Stage 3: Plating Cabut ────────────────────────────────────────
            const cabut = parseCabutQr(cabutQr);
            if (cabut) {
                $("#trace-cabut-part").text(cabut.part);
                $("#trace-cabut-po").text(cabut.po);
                $("#trace-cabut-qty-orig").text(cabut.qtyOrig + ' pcs');
                $("#trace-cabut-date").text(cabut.date);
                $("#trace-cabut-ops").text(cabut.ops);
                $("#trace-cabut-qty-split").text(cabut.qtySplit + ' pcs');
                $("#trace-cabut-bucket").text(cabut.bucket);
                $("#trace-detail-cabut").removeClass('d-none');
            } else {
                $("#trace-detail-cabut").addClass('d-none');
            }

            // ── Stage 4: QC Verifikasi (same format as WIP) ───────────────────
            const qc = parseWipQr(qcQr);
            if (qc) {
                $("#trace-qc-part").text(qc.part);
                $("#trace-qc-po").text(qc.po);
                $("#trace-qc-qty").text(qc.qty + ' pcs');
                $("#trace-qc-lot").text(qc.lot);
                $("#trace-qc-sap").text(qc.sap);
                $("#trace-detail-qc").removeClass('d-none');
            } else {
                $("#trace-detail-qc").addClass('d-none');
            }

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
            const soundUrl = window.appAudioSuccessUrl || '/audio/QR%20CODE%20BERHASIL%20DI%20SCAN.mp3';
            const audio = new Audio(soundUrl);
            const promise = audio.play();
            if (promise !== undefined) {
                promise.catch(() => this.playBeepFallback());
            }
        } catch (e) {
            this.playBeepFallback();
        }
    }

    playBeepFallback() {
        try {
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

class PlatingCreate {
    constructor(config) {
        this.config = config;
        this.timer = { running: false, seconds: 0, interval: null };
        this.pdf = {
            standardDoc: null,
            standardPage: 1,
            standardFileIdx: 0,
            standardFiles: [],
            similarDoc: null,
            similarPage: 1,
            modalDoc: null,
            modalPage: 1,
            modalScale: 1.5,
            currentModalType: "",
        };
        this.pdfCache = {};
        this.qrScanner = null;
        this.init();
    }

    init() {
        if (typeof pdfjsLib !== "undefined") {
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
        this.initHardwareScanner();

        // Inisialisasi mode berdasarkan input tersembunyi is_scanned (default: manual mode)
        this.setScanMode(false);

        // Inisialisasi awal untuk logic kalkulasi & judgment
        this.updateJudgment();
    }

    /**
     * Mengatur mode scan vs manual.
     * Scan mode: kolom Injection dan Plating disembunyikan (dikosongkan, tidak required).
     * Manual mode: semua kolom ditampilkan dan bisa diisi.
     */
    setScanMode(isScanned) {
        const $tdProcess1 = $('#tdInjection');
        const $tdProcess2 = $('#tdPlating');
        const $thProcess1 = $('#thInjection');
        const $thProcess2 = $('#thPlating');

        // Field dengan atribut data-scan-optional akan di-clear dan tidak required saat scan
        const $scanOptionalFields = $('[data-scan-optional="1"]');

        if (isScanned) {
            // SCAN MODE: sembunyikan kolom Injection dan Plating
            $tdProcess1.hide();
            $tdProcess2.hide();
            $thProcess1.hide();
            $thProcess2.hide();

            // Kosongkan nilai dan hapus required pada field-field opsional saat scan
            $scanOptionalFields.each(function() {
                $(this).removeAttr('required');
                if (this.tagName === 'INPUT') {
                    $(this).val('');
                }
            });

            // Tandai form dalam mode scan
            $('#isScannedInput').val('1');

            // Tampilkan badge info di bawah tabel
            if ($('#scanModeInfo').length === 0) {
                $('<div id="scanModeInfo" class="alert alert-info py-2 px-3 mt-2 mb-0 small">' +
                    '<i class="fas fa-qrcode mr-1"></i> <strong>Mode Scan:</strong> Kolom Injection &amp; Plating tidak diisi otomatis. ' +
                    'Data Quality (Tgl/Shift) tetap tersedia untuk diisi.' +
                '</div>').insertAfter('#checksheetTable');
            } else {
                $('#scanModeInfo').show();
            }
        } else {
            // MANUAL MODE: tampilkan semua kolom
            $tdProcess1.show();
            $tdProcess2.show();
            $thProcess1.show();
            $thProcess2.show();

            // Sembunyikan badge info scan mode
            $('#scanModeInfo').hide();

            // Tandai form dalam mode manual
            $('#isScannedInput').val('0');
        }
    }

    lockInputs(lock) {
        const inputs = $(
            '#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)',
        );
        inputs.prop("disabled", lock);
        if (lock) {
            $("#checksheetForm").addClass("inputs-locked");
        } else {
            $("#checksheetForm").removeClass("inputs-locked");
            $('input[name="total_ok"]').prop("readonly", true);
        }
    }

    initTimer() {
        const updateDisplay = () => {
            const h = Math.floor(this.timer.seconds / 3600);
            const m = Math.floor((this.timer.seconds % 3600) / 60);
            const s = this.timer.seconds % 60;
            const text = [h, m, s].map((v) => (v < 10 ? "0" + v : v)).join(":");
            $("#timerDisplay").text(text);
            $("#cycleTimeInput").val(this.timer.seconds);
        };

        $("#startTimerBtn").click(() => {
            if (!this.timer.running) {
                this.timer.running = true;
                $("#startTimerBtn")
                    .removeClass("btn-success")
                    .addClass("btn-secondary")
                    .prop("disabled", true)
                    .html('<i class="fas fa-clock"></i> Running...');
                $("#saveBtn").prop("disabled", false);
                this.lockInputs(false);
                this.timer.interval = setInterval(() => {
                    this.timer.seconds++;
                    updateDisplay();
                }, 1000);
                $("#itemSelect").focus();
            }
        });
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
        if (this.isProcessingScan) return;
        this.isProcessingScan = true;

        this.parseAndFillQR(decodedText, (success) => {
            if (success) {
                this.playSuccessFeedback();
                this.lockInputs(false);
                // Aktifkan mode scan: sembunyikan kolom Injection & Plating
                this.setScanMode(true);
                setTimeout(() => {
                    $("#checksheetForm").submit();
                }, 800);
            } else {
                setTimeout(() => {
                    this.isProcessingScan = false;
                }, 2000);
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
            const soundUrl = window.appAudioSuccessUrl || '/audio/QR%20CODE%20BERHASIL%20DI%20SCAN.mp3';
            const audio = new Audio(soundUrl);
            const promise = audio.play();
            if (promise !== undefined) {
                promise.catch(() => this.playBeepFallback());
            }
        } catch (e) {
            this.playBeepFallback();
        }
    }

    playBeepFallback() {
        try {
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

    parseAndFillQR(decodedText, callback) {
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
                                window.playAppAudio('duplicate_saved');
                                Swal.fire(
                                    "QR-Code Duplicate",
                                    res.message,
                                    "error",
                                );
                                if (callback) callback(false);
                            } else {
                                const filled = this.processFillQR(decodedText, parts);
                                if (callback) callback(filled);
                            }
                        },
                    ).fail(() => {
                        const filled = this.processFillQR(decodedText, parts);
                        if (callback) callback(filled);
                    });
                } else {
                    const filled = this.processFillQR(decodedText, parts);
                    if (callback) callback(filled);
                }
            } else {
                window.playAppAudio('format_error');
                Swal.fire(
                    "Format QR Salah",
                    "Data QR tidak sesuai standar (" + decodedText + ")",
                    "warning",
                );
                if (callback) callback(false);
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

    processFillQR(decodedText, parts) {
        try {
            let partCode = '';
            let supplierId = '';
            let quantity = '';
            let uniqueCode = '';
            let sapCode = '';
            let lotCabut = '';

            if (parts.length >= 5) {
                partCode = parts[0].trim();
                supplierId = parts[1].trim();
                quantity = parts[2].trim();
                uniqueCode = parts[3].trim();
                sapCode = parts[4].trim();
            } else if (parts.length === 3) {
                partCode = parts[0].trim();
                quantity = parts[1].trim();
                sapCode = parts[2].trim();
            } else {
                partCode = parts[0].trim();
                sapCode = partCode;
            }

            $("#sapCodeInput").val(sapCode);
            $("#partCodeInput").val(partCode);
            $("#supplierIdInput").val(supplierId);
            $("#quantityInput").val(quantity);
            $("#uniqueCodeInput").val(uniqueCode);
            $("#sapCodeInputHidden").val(sapCode);
            $("#isScannedInput").val("1");

            // Auto-fill no_lot, plating_date, plating_shift if lotCabut is available
            if (lotCabut) {
                $("#noLotInput").val(lotCabut);

                if (lotCabut.includes("-")) {
                    const lotParts = lotCabut.split("-");
                    if (lotParts.length === 3) {
                        const dateStr = lotParts[0]; // e.g. 04062026
                        if (dateStr.length === 8) {
                            const day = dateStr.substring(0, 2);
                            const month = dateStr.substring(2, 4);
                            const year = dateStr.substring(4, 8);
                            const formattedDate = `${year}-${month}-${day}`;
                            $("#platingDateInput").val(formattedDate);
                        }
                        const shiftStr = lotParts[2]; // e.g. 1
                        if (shiftStr === "1" || shiftStr === "2" || shiftStr === "3") {
                            $("#platingShiftInput").val(shiftStr);
                        }
                    }
                } else if (lotCabut.length >= 9) {
                    const dateStr = lotCabut.substring(0, 8);
                    const shiftStr = lotCabut.substring(lotCabut.length - 1);
                    if (/^\d{8}$/.test(dateStr)) {
                        const day = dateStr.substring(0, 2);
                        const month = dateStr.substring(2, 4);
                        const year = dateStr.substring(4, 8);
                        const formattedDate = `${year}-${month}-${day}`;
                        $("#platingDateInput").val(formattedDate);
                    }
                    if (shiftStr === "1" || shiftStr === "2" || shiftStr === "3") {
                        $("#platingShiftInput").val(shiftStr);
                    }
                }
            }

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

                Swal.fire({
                    icon: "success",
                    title: "QR Berhasil Discan",
                    text: "Item otomatis terpilih.",
                    timer: 1500,
                    showConfirmButton: false,
                });
                return true;
            } else {
                window.playAppAudio('item_not_found');
                Swal.fire(
                    "Info",
                    "Data item QR terbaca, tetapi tidak ditemukan di master item. Silahkan konfirmasi kepada admin untuk menambahkan data item QR.",
                    "warning",
                );
                return false;
            }
        } catch (e) {
            console.error("Fill QR Error:", e);
            Swal.fire("Error", "Gagal mengisi data QR: " + e.message, "error");
            return false;
        }
    }

    initItemSelection() {
        $("#itemSelect").change(() => {
            const selected = $("#itemSelect option:selected");
            let defectList = selected.data("defects") || selected.attr("data-defects");
            this.updateDefectDropdown(defectList);
            this.updatePdfViews(selected);
        });
    }

    initSapSelection() {
        $("#sapCodeInput").on("input", (e) => {
            const sapCode = $(e.target).val().trim();
            if (sapCode.includes("|")) {
                // Ignore raw QR scans, they will be processed by initHardwareScanner
                return;
            }
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

    initPdfEvents() {
        $("#prevStandardPage").click(() => {
            if (this.pdf.standardPage > 1) {
                this.pdf.standardPage--;
                this.renderPdfToCanvas(
                    this.pdf.standardDoc,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    this.pdf.standardPage,
                );
            }
        });

        $("#nextStandardPage").click(() => {
            if (
                this.pdf.standardDoc &&
                this.pdf.standardPage < this.pdf.standardDoc.numPages
            ) {
                this.pdf.standardPage++;
                this.renderPdfToCanvas(
                    this.pdf.standardDoc,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    this.pdf.standardPage,
                );
            }
        });

        $("#prevStandardFile").click(() => {
            if (this.pdf.standardFileIdx > 0) {
                this.pdf.standardFileIdx--;
                this.pdf.standardPage = 1;
                const url = this.config.pdfRoute
                    .replace("ID_PLACEHOLDER", $("#itemSelect").val())
                    .replace("INDEX_PLACEHOLDER", this.pdf.standardFileIdx);
                this.loadPdf(url, "standard");
                $("#standardFileInfo").text(
                    this.pdf.standardFileIdx +
                    1 +
                    "/" +
                    this.pdf.standardFiles.length,
                );
                $("#downloadStandardBtn").attr("href", url);
            }
        });

        $("#nextStandardFile").click(() => {
            if (this.pdf.standardFileIdx < this.pdf.standardFiles.length - 1) {
                this.pdf.standardFileIdx++;
                this.pdf.standardPage = 1;
                const url = this.config.pdfRoute
                    .replace("ID_PLACEHOLDER", $("#itemSelect").val())
                    .replace("INDEX_PLACEHOLDER", this.pdf.standardFileIdx);
                this.loadPdf(url, "standard");
                $("#standardFileInfo").text(
                    this.pdf.standardFileIdx +
                    1 +
                    "/" +
                    this.pdf.standardFiles.length,
                );
                $("#downloadStandardBtn").attr("href", url);
            }
        });

        $("#prevSimilarPage").click(() => {
            if (this.pdf.similarPage > 1) {
                this.pdf.similarPage--;
                this.renderPdfToCanvas(
                    this.pdf.similarDoc,
                    "similarPdfCanvas",
                    "similarPdfPlaceholder",
                    "similarPdfLoading",
                    this.pdf.similarPage,
                );
            }
        });

        $("#nextSimilarPage").click(() => {
            if (
                this.pdf.similarDoc &&
                this.pdf.similarPage < this.pdf.similarDoc.numPages
            ) {
                this.pdf.similarPage++;
                this.renderPdfToCanvas(
                    this.pdf.similarDoc,
                    "similarPdfCanvas",
                    "similarPdfPlaceholder",
                    "similarPdfLoading",
                    this.pdf.similarPage,
                );
            }
        });

        $(".view-pdf-btn").click((e) => {
            this.pdf.currentModalType =
                $(e.currentTarget).attr("id") === "fullStandardBtn"
                    ? "standard"
                    : "similar";
            this.pdf.modalDoc =
                this.pdf.currentModalType === "standard"
                    ? this.pdf.standardDoc
                    : this.pdf.similarDoc;
            this.pdf.modalPage =
                this.pdf.currentModalType === "standard"
                    ? this.pdf.standardPage
                    : this.pdf.similarPage;
            this.pdf.modalScale = 1.5;

            $("#pdfModal").modal("show");

            if (this.pdf.currentModalType === "standard") {
                if (this.pdf.standardFiles.length > 1) {
                    $("#prevPdf, #nextPdf").show();
                    $("#pdfInfo").text(
                        "File " +
                        (this.pdf.standardFileIdx + 1) +
                        " of " +
                        this.pdf.standardFiles.length,
                    );
                } else {
                    $("#prevPdf, #nextPdf").hide();
                    $("#pdfInfo").text("Standard PDF");
                }
            } else {
                $("#prevPdf, #nextPdf").hide();
                $("#pdfInfo").text("Similar Part PDF");
            }

            setTimeout(() => this.renderModalPage(this.pdf.modalPage), 200);
        });

        $("#prevPage").click(() => {
            if (this.pdf.modalPage > 1) {
                this.pdf.modalPage--;
                this.renderModalPage(this.pdf.modalPage);
            }
        });
        $("#nextPage").click(() => {
            if (
                this.pdf.modalDoc &&
                this.pdf.modalPage < this.pdf.modalDoc.numPages
            ) {
                this.pdf.modalPage++;
                this.renderModalPage(this.pdf.modalPage);
            }
        });
        $("#pdfZoomIn").click(() => {
            this.pdf.modalScale += 0.25;
            this.renderModalPage(this.pdf.modalPage);
        });
        $("#pdfZoomOut").click(() => {
            if (this.pdf.modalScale > 0.5) {
                this.pdf.modalScale -= 0.25;
                this.renderModalPage(this.pdf.modalPage);
            }
        });
        $("#pdfZoomReset").click(() => {
            this.pdf.modalScale = 1.5;
            this.renderModalPage(this.pdf.modalPage);
        });

        $("#prevPdf").click(() => {
            if (
                this.pdf.currentModalType === "standard" &&
                this.pdf.standardFileIdx > 0
            ) {
                $("#prevStandardFile").click();
                setTimeout(() => {
                    this.pdf.modalDoc = this.pdf.standardDoc;
                    this.pdf.modalPage = 1;
                    $("#pdfInfo").text(
                        "File " +
                        (this.pdf.standardFileIdx + 1) +
                        " of " +
                        this.pdf.standardFiles.length,
                    );
                    this.renderModalPage(1);
                }, 500);
            }
        });
        $("#nextPdf").click(() => {
            if (
                this.pdf.currentModalType === "standard" &&
                this.pdf.standardFileIdx < this.pdf.standardFiles.length - 1
            ) {
                $("#nextStandardFile").click();
                setTimeout(() => {
                    this.pdf.modalDoc = this.pdf.standardDoc;
                    this.pdf.modalPage = 1;
                    $("#pdfInfo").text(
                        "File " +
                        (this.pdf.standardFileIdx + 1) +
                        " of " +
                        this.pdf.standardFiles.length,
                    );
                    this.renderModalPage(1);
                }, 500);
            }
        });
    }

    updatePdfViews(selected) {
        let files = selected.data("files");
        this.pdf.standardFiles = [];
        try {
            this.pdf.standardFiles =
                typeof files === "string" ? JSON.parse(files) : files || [];
        } catch (e) { }

        const standardUrl = selected.data("standard");
        const similarUrl = selected.data("similar");

        if (standardUrl && this.pdf.standardFiles.length > 0) {
            this.pdf.standardFileIdx = 0;
            this.pdf.standardPage = 1;
            this.loadPdf(standardUrl, "standard");
            $(".standard-nav-controls").show();
            if (this.pdf.standardFiles.length > 1) {
                $(".standard-nav-controls .file-nav").show();
                $("#standardFileInfo").text(
                    "1/" + this.pdf.standardFiles.length,
                );
            } else {
                $(".standard-nav-controls .file-nav").hide();
            }
            $("#downloadStandardBtn").attr("href", standardUrl).show();
        } else {
            $("#standardPdfCanvas").addClass("d-none").hide();
            $("#standardPdfPlaceholder")
                .removeClass("d-none")
                .addClass("d-flex")
                .show()
                .find("p")
                .text("Standard PDF tidak tersedia");
            $(".standard-nav-controls").hide();
            $("#fullStandardBtn").hide();
            $("#downloadStandardBtn").hide();
        }

        if (similarUrl) {
            this.pdf.similarPage = 1;
            this.loadPdf(similarUrl, "similar");
            $(".similar-nav-controls").show();
            $("#similarStatusText").text("");
            $("#downloadSimilarBtn").attr("href", similarUrl).show();
        } else {
            $("#similarPdfCanvas").addClass("d-none").hide();
            $("#similarPdfPlaceholder")
                .removeClass("d-none")
                .addClass("d-flex")
                .show()
                .find("p")
                .text("Similar Part tidak tersedia");
            $(".similar-nav-controls").hide();
            $("#fullSimilarBtn").hide();
            $("#downloadSimilarBtn").hide();
        }
    }

    loadPdf(url, type) {
        const placeholderId =
            type === "standard"
                ? "standardPdfPlaceholder"
                : "similarPdfPlaceholder";
        const loadingId =
            type === "standard" ? "standardPdfLoading" : "similarPdfLoading";
        const canvasId =
            type === "standard" ? "standardPdfCanvas" : "similarPdfCanvas";

        if (this.pdfCache[url]) {
            if (type === "standard") this.pdf.standardDoc = this.pdfCache[url];
            else this.pdf.similarDoc = this.pdfCache[url];
            this.renderPdfToCanvas(
                this.pdfCache[url],
                canvasId,
                placeholderId,
                loadingId,
                1,
            );
        } else {
            const $placeholder = $("#" + placeholderId);
            const $loading = $("#" + loadingId);
            const $canvas = $("#" + canvasId);

            $placeholder.removeClass("d-flex").addClass("d-none");
            $canvas.addClass("d-none").hide();
            $loading.removeClass("d-none").addClass("d-flex");

            pdfjsLib
                .getDocument(url)
                .promise.then((pdf) => {
                    this.pdfCache[url] = pdf;
                    if (type === "standard") this.pdf.standardDoc = pdf;
                    else this.pdf.similarDoc = pdf;
                    this.renderPdfToCanvas(
                        pdf,
                        canvasId,
                        placeholderId,
                        loadingId,
                        1,
                    );
                })
                .catch((err) => {
                    $loading.removeClass("d-flex").addClass("d-none");
                    $placeholder
                        .removeClass("d-none")
                        .addClass("d-flex")
                        .show()
                        .find("p")
                        .text("Gagal memuat PDF");
                });
        }
    }

    renderPdfToCanvas(pdf, canvasId, placeholderId, loadingId, pageNum) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !pdf) return;
        const ctx = canvas.getContext("2d");
        const $placeholder = $("#" + placeholderId);
        const $loading = $("#" + loadingId);
        const $canvas = $(canvas);

        $placeholder.removeClass("d-flex").addClass("d-none");
        $canvas.addClass("d-none").hide();
        $loading.removeClass("d-none").addClass("d-flex");

        pdf.getPage(pageNum).then((page) => {
            const containerWidth = $canvas.parent().width() || 500;
            const viewport = page.getViewport({ scale: 1.0 });
            const scale = (containerWidth - 40) / viewport.width;
            const scaledViewport = page.getViewport({ scale: scale });

            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            $canvas.css("width", "100%");

            page.render({
                canvasContext: ctx,
                viewport: scaledViewport,
            }).promise.then(() => {
                $loading.removeClass("d-flex").addClass("d-none");
                $canvas.removeClass("d-none").show();
                if (canvasId === "standardPdfCanvas") {
                    $("#standardPageInfo").text(
                        "P " + pageNum + "/" + pdf.numPages,
                    );
                    $("#fullStandardBtn").show();
                } else if (canvasId === "similarPdfCanvas") {
                    $("#similarPageInfo").text(
                        "P " + pageNum + "/" + pdf.numPages,
                    );
                    $("#fullSimilarBtn").show();
                }
            });
        });
    }

    renderModalPage(num) {
        const canvas = document.getElementById("modalPdfCanvas");
        if (!canvas || !this.pdf.modalDoc) return;
        const ctx = canvas.getContext("2d");
        this.pdf.modalDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: this.pdf.modalScale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            page.render({
                canvasContext: ctx,
                viewport: viewport,
            }).promise.then(() => {
                $("#pageInfo").text(
                    "Page " + num + " of " + this.pdf.modalDoc.numPages,
                );
            });
        });
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
            { v: "KOTOR", t: "KOTOR" },
            { v: "DENYUT", t: "DENYUT" },
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
        $("#totalNG").val(total).trigger("input");
    }

    initJudgmentLogic() {
        $("#totalQty, #totalNG").on("input", () => this.updateJudgment());

        $("#judgmentSelect").change((e) => {
            const val = $(e.target).val();
            const badge = $("#judgmentBadge");

            if (val === "OK") {
                badge
                    .text("OK")
                    .removeClass("d-none text-danger border-danger")
                    .addClass("text-success border-success")
                    .css("background", "#f0fdf4");
            } else if (val === "NG") {
                badge
                    .text("NG")
                    .removeClass("d-none text-success border-success")
                    .addClass("text-danger border-danger")
                    .css("background", "#fef2f2");
                $("#nextProsesContainer").show();
            } else {
                badge.addClass("d-none").text("-");
                $("#nextProsesContainer").hide();
            }

            if (
                val !== "NG" &&
                parseInt($('input[name="total_ng"]').val()) === 0
            ) {
                $("#nextProsesContainer").hide();
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
        if (!pn) return "";
        return pn
            .toString()
            .replace(/[\u2012\u2013\u2014\u2212]/g, "-")
            .replace(/\s+/g, "")
            .toUpperCase();
    }

    updateJudgment() {
        const totalQty = parseInt($("#totalQty").val()) || 0;
        const ng = parseInt($("#totalNG").val()) || 0;
        const isPlatingChecked = $("#checkOK").is(":checked");

        // Catatan: Perencanaan menyarankan AQL 0.65.
        // Jika pemeriksaan 100% digunakan, sampleSize = totalQty.
        // Jika AQL 0.65 digunakan, sampleSize dicari.
        // Kode asli menggunakan pemeriksaan 100% (totalQty - ng).

        const sampleSize = this.getSampleSize(totalQty);
        const limits = this.getAqlLimits(sampleSize);

        // Perbarui Info UI jika diperlukan (menambahkan placeholder jika tidak ada)
        if (!$("#aql_info").length) {
            $(".bg-primary.text-white tr:first").after(
                '<tr id="aql_info" class="bg-light text-dark small"><td colspan="12">AQL Standard: 0.65 | Sample: <span id="sample_val">-</span> | Acc: <span id="acc_val">-</span> | Rej: <span id="rej_val">-</span></td></tr>',
            );
        }
        $("#sample_val").text(sampleSize);
        $("#acc_val").text(limits.acc);
        $("#rej_val").text(limits.rej);
        $("#aql_info").toggle(totalQty > 0);

        const ok = Math.max(0, totalQty - ng);
        $('input[name="total_ok"]').val(ok);

        const isScanned = $("#isScannedInput").val() === "1";

        // Toggle whole column visibility
        $(".judgment-column").toggle(isScanned);

        // Judgment hidden always as per request
        $("#judgmentSelect").addClass("d-none");
        $("#judgmentBadge").toggleClass("d-none", !isScanned);

        if (totalQty > 0) {
            const result = ng > 0 ? "NG" : "OK";
            $("#judgmentSelect").val(result).trigger("change");

            // Update Badge Style (for Scan mode)
            $("#judgmentBadge").text(result).removeClass('badge-success badge-danger text-white text-dark text-success text-danger');
            if (result === "NG") {
                $("#judgmentBadge").addClass("text-danger").css({
                    "background-color": "#fff",
                    "border-color": "#dc3545"
                });
            } else {
                $("#judgmentBadge").addClass("text-success").css({
                    "background-color": "#fff",
                    "border-color": "#28a745"
                });
            }
        } else {
            $("#judgmentSelect").val("");
            $("#judgmentBadge").removeClass("text-white text-dark text-success text-danger").text("-").css({
                "background-color": "#f8f9fc",
                "color": "#5a5c69",
                "border-color": "#dddfeb"
            });
        }

        // Next Proses visibility/validation logic
        if ($("#judgmentSelect").val() === "NG" || ng > 0) {
            $("#nextProsesContainer").show();
        } else {
            $("#nextProsesContainer").hide();
        }
    }

    initFormSubmit() {
        const _this = this;
        $(document).on("submit", "#checksheetForm", (e) => {
            const $form = $(e.target);
            e.preventDefault();

            console.log("Submitting Plating Form...");
            const judgment = $("#judgmentSelect").val();
            const nextProses = $("#nextProses").val();
            const itemId = $("#itemSelect").val();
            const line = $('select[name="line"]').val();
            const totalQty = $('input[name="total_qty"]').val();
            const operatorInitials = $('input[name="operator_initials"]').val();

            // 1. Validasi: Item harus dipilih
            if (!itemId) {
                Swal.fire({
                    icon: "warning",
                    title: "Item Belum Dipilih",

                });
                $("#itemSelect").addClass("is-invalid").focus();
                setTimeout(() => $("#itemSelect").removeClass("is-invalid"), 3000);
                return false;
            }

            // 1b. Validasi: Lot ID (Injection) jika tidak dalam mode scan
            const isScanned = $("#isScannedInput").val() === "1";
            if (!isScanned) {
                const injDate = $("#injectionDateInput").val();
                const injShift = $("#injectionShiftInput").val();
                const injInitials = $("#injectionInitialsInput").val();

                if (!injDate || !injShift || !injInitials) {
                    Swal.fire({
                        icon: "warning",
                        title: "Lot ID Belum Lengkap",
                        text: "Silahkan isi Tanggal, Shift, dan Inisial Lot ID terlebih dahulu.",
                        confirmButtonText: "Perbaiki Input",
                        confirmButtonColor: "#e74a3b"
                    });
                    if (!injDate) $("#injectionDateInput").addClass("is-invalid").focus();
                    else if (!injShift) $("#injectionShiftInput").addClass("is-invalid").focus();
                    else if (!injInitials) $("#injectionInitialsInput").addClass("is-invalid").focus();
                    setTimeout(() => {
                        $("#injectionDateInput, #injectionShiftInput, #injectionInitialsInput").removeClass("is-invalid");
                    }, 3000);
                    return false;
                }
            }

            // 2. Validasi: Meja harus dipilih
            if (!line) {
                Swal.fire({
                    icon: "warning",
                    title: "Meja Belum Dipilih",
                });
                $('select[name="line"]').addClass("is-invalid").focus();
                setTimeout(() => $('select[name="line"]').removeClass("is-invalid"), 3000);
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

            // 4. Validasi: Inisial QC
            if (!operatorInitials) {
                Swal.fire({
                    icon: "warning",
                    title: "Inisial QC Wajib Diisi",
                });
                $('input[name="operator_initials"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="operator_initials"]').removeClass("is-invalid"), 3000);
                return false;
            }

            // 4. Validasi: Pilihan Defect (NG)
            const ngCount = parseInt($('input[name="total_ng"]').val()) || 0;
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

            // 4. Validasi: NG harus pilih Next Proses
            if (judgment === "NG" && !nextProses) {
                Swal.fire({
                    icon: "warning",
                    title: "Next Proses Wajib Dipilih",
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
                });
                $('input[name="operator_initials"]').addClass("is-invalid").focus();
                setTimeout(() => $('input[name="operator_initials"]').removeClass("is-invalid"), 3000);
                return false;
            }

            if (_this.timer.running) {
                clearInterval(_this.timer.interval);
                _this.timer.running = false;
            }

            // Bersihkan defect yang diinput tapi qty = 0 / kosong
            $(".defect-row").each(function () {
                const typeInput = $(this).find(
                    'input[name="defect_types[]"], select[name="defect_types[]"]',
                );
                const qtyInput = $(this).find(
                    'input[name="defect_quantities[]"]',
                );
                const type = typeInput.val();
                const qty = parseInt(qtyInput.val()) || 0;

                if (type && qty === 0) {
                    typeInput.val("");
                    qtyInput.val("");
                }
            });

            const saveBtn = $("#saveBtn");
            const originalHtml = saveBtn.html();
            saveBtn
                .prop("disabled", true)
                .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: $(e.target).attr("action"),
                method: "POST",
                data: new FormData(e.target),
                processData: false,
                contentType: false,
                success: (res) => {
                    if (res.success) {
                        const isModalOpen = $("#qrScannerModal").is(":visible");
                        if (isModalOpen) {
                            Swal.fire({
                                icon: "success",
                                title: "Data Berhasil Disimpan",
                                text: "Silahkan scan QR berikutnya...",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 1500
                            });
                            _this.resetState();
                            _this.setScanMode(true);
                            setTimeout(() => {
                                _this.isProcessingScan = false;
                            }, 1500);
                        } else {
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil",
                                text: "Data Berhasil Disimpan",
                                showCancelButton: true,
                                confirmButtonText: "Lihat Data",
                            }).then((result) => {
                                if (result.isConfirmed)
                                    window.location.href = res.index_url;
                                else _this.resetState();
                            });
                        }
                    }
                },
                error: (xhr) => {
                    _this.isProcessingScan = false;
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            xhr.responseJSON?.message ||
                            "Gagal menyimpan data.",
                    });
                    saveBtn.prop("disabled", false).html(originalHtml);
                },
            });
        });
    }

    resetState() {
        if (this.timer.interval) clearInterval(this.timer.interval);
        this.timer = { running: false, seconds: 0, interval: null };
        $("#timerDisplay").text("00:00:00");
        $("#startTimerBtn")
            .removeClass("btn-secondary")
            .addClass("btn-success")
            .prop("disabled", false)
            .html('<i class="fas fa-play"></i> Start');

        const isModalOpen = $("#qrScannerModal").is(":visible");
        if (!isModalOpen) {
            this.lockInputs(true);
        } else {
            this.lockInputs(false);
        }
        $("#checksheetForm")[0].reset();
        this.resetAllDefects();
        $("#imageContainer").html(
            '<div style="width:100px; height:100px; background-color:#f8f9fa; border:1px solid #dee2e6; display:flex; align-items:center; justify-content:center; margin:0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>',
        );
        $("#itemSelect").val("").trigger("change");
        $("#aql_info").hide();
        $("#nextProsesContainer").hide();
        $("#judgmentBadge").addClass("d-none").text("-");
        $("#judgmentSelect").val("");

        $("#standardPdfCanvas, #similarPdfCanvas").hide();
        $("#standardPdfPlaceholder")
            .show()
            .find("p")
            .text("Pilih Item untuk menampilkan Standard PDF");
        $("#similarPdfPlaceholder")
            .show()
            .find("p")
            .text("Pilih Item untuk menampilkan Similar Part");
        $("#fullStandardBtn, #fullSimilarBtn").hide();
        $(".standard-nav-controls, .similar-nav-controls").hide();

        this.pdf = {
            ...this.pdf,
            standardDoc: null,
            standardPage: 1,
            standardFileIdx: 0,
            standardFiles: [],
            similarDoc: null,
            similarPage: 1,
        };

        // Reset QR fields
        $(
            "#qrcodeInput, #partCodeInput, #supplierIdInput, #quantityInput, #uniqueCodeInput, #sapCodeInputHidden",
        ).val("");
        $("#sapCodeInput").removeClass("is-valid is-invalid").val("");
        $("#isScannedInput").val("0");
    }

    showToast(msg, color) {
        let $toast = $("#scanner-toast");
        if (!$toast.length) {
            $toast = $(
                `<div id="scanner-toast" style="position:fixed; top:20px; right:20px; z-index:9999; padding:12px 24px; border-radius:8px; color:white; font-weight:bold; font-size:14px; box-shadow:0 4px 12px rgba(0,0,0,0.15); opacity:0; pointer-events:none;"></div>`,
            ).appendTo("body");
        }
        $toast.text(msg).css("background-color", color || "#333");
        $toast.stop().animate({ opacity: 1 }, 200);
        clearTimeout($toast.data("hideTimer"));
        $toast.data("hideTimer", setTimeout(() => $toast.animate({ opacity: 0 }, 400), 2000));
    }

    initHardwareScanner() {
        let buffer = "";
        let lastTime = Date.now();
        let scanTimeout;

        const processScan = (raw) => {
            raw = (raw || "").trim();
            console.log("Hardware Scan Triggered (Plating):", raw);

            // Aktifkan lock agar submit form diblokir sementara
            this.isProcessingScan = true;
            clearTimeout(this.scanLockTimeout);

            if (!raw.includes("|")) {
                window.playAppAudio('format_error');
                this.showToast("❌ Format QR salah (tidak ada |)", "#f87171");
                this.isProcessingScan = false;
                buffer = "";
                return;
            }

            const parts = raw.split("|");
            if (parts.length < 5) {
                window.playAppAudio('format_error');
                this.showToast(`❌ Format QR salah`, "#f87171");
                this.isProcessingScan = false;
                buffer = "";
                return;
            }

            // Kunci input agar tidak bisa scan kedua sebelum data diproses
            $("#sapCodeInput").val("").prop("disabled", true).css("background", "#f1f5f9");

            // Panggil parseAndFillQR
            this.parseAndFillQR(raw, (success) => {
                if (success) {
                    this.playSuccessFeedback();
                    this.lockInputs(false);
                    // Aktifkan mode scan: sembunyikan kolom Injection & Plating
                    this.setScanMode(true);
                    setTimeout(() => {
                        $("#checksheetForm").submit();
                    }, 1200);
                }
                // Biarkan input bisa di-scan lagi setelah 1.5 detik
                setTimeout(() => {
                    $("#sapCodeInput").prop("disabled", false).css("background", "");
                    this.isProcessingScan = false;
                }, 1500);
            });
            buffer = "";
        };

        // Capturing Listener to prevent Enter key on sapCodeInput from submitting form
        window.addEventListener("keydown", (e) => {
            if ((e.key === "Enter" || e.keyCode === 13) && document.activeElement && document.activeElement.id === 'sapCodeInput') {
                console.log("Enter key captured and blocked (PDA Mode)");
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        }, true);

        // Catch the Enter key sent by PDA at the end of the scan
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

        // Fallback for PDA scanners that only send input events without Enter
        let sapInputTimeout;
        $("#sapCodeInput").on("input", function () {
            const val = ($(this).val() || "").trim();
            if (val.length > 10 && val.includes("|") && val.split("|").length >= 5) {
                clearTimeout(sapInputTimeout);
                sapInputTimeout = setTimeout(() => {
                    const finalVal = ($(this).val() || "").trim();
                    if (finalVal && finalVal === val) { // Ensure typing has stopped
                        $(this).val("");
                        processScan(finalVal);
                    }
                }, 80); // Wait 80ms to ensure the full QR is typed
            }
        });

        // Global keydown buffer (PC wired/wireless scanners)
        window.addEventListener("keydown", (e) => {
            const currentTime = Date.now();
            const isTerminator = e.key === "Enter" || e.keyCode === 13 ||
                e.key === "Tab" || e.keyCode === 9;

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

            clearTimeout(scanTimeout);
            scanTimeout = setTimeout(() => {
                if (buffer.length > 10 && buffer.includes("|") && buffer.split("|").length >= 5) {
                    processScan(buffer);
                    buffer = "";
                }
            }, 80);
        }, true);
    }
}

class PlatingEdit {
    constructor() {
        this.init();
    }

    init() {
        if ($(".select2").length) {
            $(".select2").select2({
                theme: "bootstrap4",
                dropdownParent: $("#editModal"),
            });
        }
        $(document).on("input", "#editTotalQty", (e) => {
            $("#editSamplingQty").val($(e.target).val()).trigger("input");
        });

        $(document).on("input", "#editTotalNG, #editSamplingQty", () => {
            const total = parseInt($("#editSamplingQty").val()) || 0;
            const ng = parseInt($("#editTotalNG").val()) || 0;
            const ok = Math.max(0, total - ng);
            $('input[name="total_ok"]').val(ok);
            $("#editJudgment")
                .val(ng > 0 ? "NG" : "OK")
                .trigger("change");
        });

        $(document).on("change", "#editJudgment", (e) => {
            if ($(e.target).val() === "NG") $("#editNextProses").slideDown();
            else $("#editNextProses").slideUp();
        });
    }
}

window.initPlatingIndex = (config) => new PlatingIndex(config);
window.initPlatingCreate = (config) => new PlatingCreate(config);
window.initPlatingEdit = () => new PlatingEdit();
