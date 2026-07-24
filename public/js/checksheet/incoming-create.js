/**
 * Logika Pembuatan Checksheet Incoming
 * Digunakan untuk Parts, Sub-Parts, Chemicals, Materials, dan Exports
 */

const AQL_TABLE = {
    getSampleSize: function (lotSize) {
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

    },
    getAqlLimits: function (sampleSize) {
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
};

function normalizePartNumber(pn) {
    if (!pn) return '';
    return pn.toString()
        .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
        .replace(/\s+/g, '')
        .toUpperCase();
}

function normalizeStandardValue(val) {
    if (val === null || val === undefined) return "";
    return String(val).replace(/,/g, ".").trim();
}

document.addEventListener('DOMContentLoaded', function () {
    let timerInterval = null;
    let totalSeconds = 0;
    let timerRunning = false;

    // Lock UI on load
    const form = $("#checksheetForm");
    const formInputs = form.find("input, select, textarea, button")
        .not("#startTimerBtn")
        .not('[type="hidden"]');
    
    formInputs.prop("disabled", true);
    form.addClass("inputs-locked");

    if (!$("#lockStyle").length) {
        $('<style id="lockStyle">#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo("head");
    }

    $("#startTimerBtn").on("click", function() {
        if (!timerRunning) {
            unlockInputs();
            
            $(this).removeClass('btn-success').addClass('btn-secondary text-white')
                   .html('<i class="far fa-clock"></i> Running...')
                   .css('cursor', 'default');
            
            timerInterval = setInterval(() => {
                totalSeconds++;
                const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
                const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
                const s = (totalSeconds % 60).toString().padStart(2, '0');
                $('#timerDisplay').text(`${h}:${m}:${s}`);
                $('#cycleTimeInput').val(totalSeconds);
            }, 1000);
            
            timerRunning = true;
        }
    });

    initQrScanner();
    initHardwareScanner();
    initTempQueue();

    // Tangani Pemilihan Item untuk memperbarui Dropdown Defect
    $('#itemSelect').on('change', function () {
        const selected = $(this).find(':selected');
        const defects = selected.data('defects');
        updateDefectOptions(defects);
        fetchOutstandingArrivals($(this).val());
        
        if ($(this).val()) {
            $('#addDefectBtn').show();
            
            // --- Panel Kiri: Standard (file index 0) ---
            const files = selected.data('files');
            const validFiles = Array.isArray(files) ? files.filter(f => f && f.trim && f.trim() !== '') : [];
            
            pdfDoc = null;
            pageNum = 1;
            standardFileIndex = 0;
            standardFiles = validFiles;

            if (validFiles.length > 0) {
                const firstPdfUrl = window.pdfUrlPattern
                    .replace('ID_PLACEHOLDER', $(this).val())
                    .replace('INDEX_PLACEHOLDER', 0);
                renderPdfToCanvas(
                    firstPdfUrl,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    1
                );
            } else {
                $("#standardPdfCanvas").addClass("d-none").hide();
                $("#standardPdfPlaceholder").removeClass("d-none").addClass("d-flex").find("p").first().text("Standard PDF tidak tersedia");
                $(".standard-nav-controls").hide();
            }

            // --- Panel Kanan: Dimensi (file index 1, atau data-similar) ---
            const similarUrl = selected.data('similar') || '';
            similarDoc = null;
            similarPageNum = 1;
            similarZoomLevel = 1.0;

            // Prioritas: data-similar, lalu file index 1 jika ada
            let dimensiUrl = similarUrl;
            if (!dimensiUrl && validFiles.length > 1) {
                dimensiUrl = window.pdfUrlPattern
                    .replace('ID_PLACEHOLDER', $(this).val())
                    .replace('INDEX_PLACEHOLDER', 1);
            }

            if (dimensiUrl) {
                renderSimilarPdfToCanvas(dimensiUrl, 1);
                $("#downloadSimilarBtn").attr('href', dimensiUrl).show();
                $("#fullSimilarBtn").show();
            } else {
                $("#similarPdfCanvas").addClass("d-none").hide();
                $("#similarPdfPlaceholder").removeClass("d-none").addClass("d-flex");
                $("#similarStatusText").text("Dokumen dimensi tidak tersedia");
                $(".similar-nav-controls").hide();
                $("#downloadSimilarBtn").hide();
                $("#fullSimilarBtn").hide();
            }

            // Re-validate dimension when item changes
            if(typeof validateDimensions === 'function') {
                validateDimensions();
            }
        } else {
            $('#addDefectBtn').hide();
            // Reset kedua panel
            $("#standardPdfCanvas").addClass("d-none").hide();
            $("#standardPdfPlaceholder").removeClass("d-none").addClass("d-flex").find("p").first().text("Pilih Item untuk menampilkan Standard PDF");
            $(".standard-nav-controls").hide();
            $("#similarPdfCanvas").addClass("d-none").hide();
            $("#similarPdfPlaceholder").removeClass("d-none").addClass("d-flex");
            $("#similarStatusText").text('');
            $(".similar-nav-controls").hide();
        }
    });


    function updateDefectOptions(defects) {
        const defectSelects = $('.defect-select');
        defectSelects.each(function () {
            const currentVal = $(this).val();
            // Coba cocokkan "-- Pilih Defect --" atau "-- Defect --"
            const firstOption = $(this).find('option').first().text() || '-- Pilih Defect --';
            $(this).empty().append(`<option value="">${firstOption}</option>`);

            if (defects) {
                const defectList = Array.isArray(defects) ? defects : JSON.parse(defects || '[]');
                defectList.forEach(defect => {
                    const val = typeof defect === 'string' ? defect : (defect.name || defect.type);
                    $(this).append(`<option value="${val}">${val}</option>`);
                });
            }
            $(this).val(currentVal);
        });
    }

    // Tambah Baris Defect
    $('#addDefectBtn').on('click', function () {
        const container = $('#defectContainer');
        const firstRow = container.find('.defect-row').first().clone();
        firstRow.find('input').val('');
        firstRow.find('select').val('');
        
        const lastCol = firstRow.find('.action-col');
        lastCol.empty().append('<button type="button" class="btn btn-danger btn-sm shadow-sm remove-defect-btn"><i class="fas fa-times"></i></button>');
        
        container.append(firstRow);
    });

    $(document).on('click', '.remove-defect-btn', function() {
        if ($('.defect-row').length > 1) {
            $(this).closest('.defect-row').remove();
            calculateAndJudge();
        }
    });

    // Hitung Otomatis Komper/Karung dan Ukuran Sampel dari Qty
    $('#lotQtyInput').on('input', function() {
        const qty = parseFloat($(this).val()) || 0;
        
        if (qty > 0) {
            let lotSize = qty;
            
            // Jika modul ini memiliki komperKarungInput (Chemical/Material),
            // maka lot size dihitung berdasarkan qty kg / 25
            if ($('#komperKarungInput').length > 0) {
                lotSize = Math.ceil(qty / 25);
                $('#komperKarungInput').val(lotSize);
            }
            
            const sampleSize = AQL_TABLE.getSampleSize(lotSize);
            $('#totalCheckInput').val(sampleSize).trigger('input');
        } else {
            if ($('#komperKarungInput').length > 0) {
                $('#komperKarungInput').val(0);
            }
            $('#totalCheckInput').val(0).trigger('input');
        }
    });

    // Jika user mengedit manual Komper/Karung
    $('#komperKarungInput').on('input', function() {
        const totalKarung = parseFloat($(this).val()) || 0;
        const sampleSize = AQL_TABLE.getSampleSize(totalKarung);
        $('#totalCheckInput').val(sampleSize).trigger('input');
    });

    // Hitung Total NG dan Atur Judgment Otomatis
    $(document).on('input', '.defect-qty, .defect-select, #totalCheckInput', function () {
        calculateAndJudge();
    });

    function calculateAndJudge() {
        const isDimensiInvalid = window.hasInvalidDimension || false;

        let hasDimensiDefect = false;
        $(".defect-select").each(function () {
            const text = $(this).find("option:selected").text().toLowerCase();
            if (text === "dimensi" || $(this).val() === "dimension") {
                hasDimensiDefect = true;
                return false;
            }
        });

        if (isDimensiInvalid && !hasDimensiDefect) {
            autoAddDimensionDefect();
            return;
        } else if (!isDimensiInvalid && hasDimensiDefect) {
            autoRemoveDimensionDefect();
            return;
        }

        let totalNg = 0;
        $('.defect-row').each(function () {
            const qtyInput = $(this).find('.defect-qty');
            const select = $(this).find('.defect-select');
            const qty = parseInt(qtyInput.val()) || 0;
            if (select.val()) {
                totalNg += qty;
            }
        });
        
        const totalCheck = parseInt($('#totalCheckInput').val()) || 0;
        const aql = AQL_TABLE.getAqlLimits(totalCheck);
        
        $('#totalNgInput').val(totalNg);
        
        const judgment = (totalNg >= aql.rej || isDimensiInvalid) ? 'NG' : 'OK';
        
        $('#judgmentSelect').val(judgment);
        
        const badge = $('#judgmentBadge');
        if (judgment === 'OK') {
            badge.removeClass('d-none border-danger text-danger border-warning text-warning')
                 .addClass('border-success text-success')
                 .text('OK');
        } else {
            badge.removeClass('d-none border-success text-success border-warning text-warning')
                 .addClass('border-danger text-danger')
                 .text('NG');
        }
        
        if (totalCheck > 0) {
            $('#aql_info').show();
            $('#acc_val').text(aql.acc);
            $('#rej_val').text(aql.rej);
        } else {
            $('#aql_info').hide();
        }
    }

    function autoAddDimensionDefect() {
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
                const container = $('#defectContainer');
                const firstRow = container.find('.defect-row').first().clone();
                firstRow.find('input').val('');
                firstRow.find('select').val('');
                const lastCol = firstRow.find('.action-col');
                lastCol.empty().append('<button type="button" class="btn btn-danger btn-sm shadow-sm remove-defect-btn"><i class="fas fa-times"></i></button>');
                container.append(firstRow);
                targetSelect = $(".defect-select").last();
            } else {
                targetSelect = $(".defect-select").first();
            }
        }

        if (targetSelect) {
            let foundVal = "";
            targetSelect.find("option").each(function () {
                if ($(this).val() === "dimension" || $(this).text().toLowerCase() === "dimensi") {
                    foundVal = $(this).val();
                    return false;
                }
            });
            if (!foundVal) {
                targetSelect.append('<option value="dimension">Dimensi</option>');
                foundVal = "dimension";
            }
            targetSelect.val(foundVal).trigger("change");
            targetSelect.closest(".defect-row").find(".defect-qty").val(1).trigger("input");
        }
    }

    function autoRemoveDimensionDefect() {
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
        calculateAndJudge();
    }

    // Pengiriman Formulir AJAX
    $('#checksheetForm').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        
        // Validasi Manual untuk Required Fields
        let isValid = true;
        let missingFields = [];
        
        const fieldNames = {
            'item_id': 'Item Part / Part Name',
            'tanggal_datang': 'Tgl. Kedatangan Supplier',
            'date': 'Tanggal Check QC',
            'shift': 'Shift QC',
            'total_check': 'Total Check',
            'lot_batch_number': 'Lot/Batch Number',
            'quantity_kg': 'Qty (Kg)',
            'komper_karung_kg': 'Komper/Karung',
            'sampling_size_karung_kg': 'Sampling Size',
            'quantity': 'Quantity',
            'sampling_size_pcs': 'Sampling Size',
            'judgment': 'Judgment',
            'operator_initials': 'QC Initials'
        };

        form.find('input[required], select[required], textarea[required]').each(function() {
            // Trim spasi jika berupa string
            let val = $(this).val();
            if (typeof val === 'string') {
                val = val.trim();
            }
            if (!val) {
                isValid = false;
                $(this).addClass('is-invalid');
                const name = $(this).attr('name');
                if (name && fieldNames[name]) {
                    if (!missingFields.includes(fieldNames[name])) {
                        missingFields.push(fieldNames[name]);
                    }
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        const dimensionCheck = checkMandatoryDimensions();
        if (!dimensionCheck.isValid) {
            isValid = false;
            dimensionCheck.missingPoints.forEach(p => missingFields.push('Check Dimensi (' + p + ')'));
        }

        if (!isValid) {
            let errorHtml = 'Pastikan semua kolom yang wajib sudah terisi sebelum menyimpan data.<br><br>';
            if (missingFields.length > 0) {
                errorHtml += '<div class="text-left"><strong class="text-danger">Kolom yang belum diisi:</strong><ul class="text-danger mt-1 mb-0">';
                missingFields.forEach(function(field) {
                    errorHtml += `<li>${field}</li>`;
                });
                errorHtml += '</ul></div>';
            }
            
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap!',
                html: errorHtml,
                confirmButtonColor: '#4e73df'
            }).then(() => {
                if (dimensionCheck.firstEmpty) {
                    $("html, body").animate({ scrollTop: dimensionCheck.firstEmpty.offset().top - 200 }, 500);
                    dimensionCheck.firstEmpty.focus();
                } else {
                    const firstInvalid = form.find('.is-invalid').first();
                    if(firstInvalid.length) {
                        $("html, body").animate({ scrollTop: firstInvalid.offset().top - 200 }, 500);
                        firstInvalid.focus();
                    }
                }
            });
            return false;
        }

        const saveBtn = form.find('button[type="submit"]');
        const originalHtml = saveBtn.html();

        // Bersihkan defect yang dipilih tapi tidak ada qty atau qty = 0
        $('.defect-row').each(function() {
            const typeInput = $(this).find('.defect-select');
            const qtyInput = $(this).find('.defect-qty');
            const type = typeInput.val();
            const qty = parseInt(qtyInput.val()) || 0;
            
            if (type && qty === 0) {
                typeInput.val('');
                qtyInput.val('');
            }
        });

        const doSubmit = function() {
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: new FormData(form[0]),
                processData: false,
                contentType: false,
                success: function (response) {
                    if (typeof $('#global-loader').hide === 'function') {
                        $('#global-loader').hide();
                    }

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Data Berhasil Disimpan',
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
                                resetState(form);
                            }
                        });
                    }
                },
                error: function (xhr) {
                    if (typeof $('#global-loader').hide === 'function') {
                        $('#global-loader').hide();
                    }

                    let errorMsg = 'Gagal menyimpan data.';
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
        };

        checkFirstTimeAndConfirm(doSubmit);
    });

    // --- Barcode / QR Scanner Handler ---
    $('#sapCodeInput').on('keydown input', function(e) {
        if (e.type === 'keydown' && e.which !== 13) return;
        let val = $(this).val().trim();
        if (!val) return;
        let matchedOption = null;

        $('#itemSelect option').each(function() {
            let sap = String($(this).data('sap-code') || '').trim();
            let partNo = normalizePartNumber($(this).data('part-number') || '');
            let name = String($(this).data('name') || '').trim().toLowerCase();
            let searchVal = val.toLowerCase();
            let normVal = normalizePartNumber(val);

            if ((sap && sap === val) || (partNo && partNo === normVal) || (name && name.includes(searchVal))) {
                matchedOption = $(this);
                return false;
            }
        });

        if (matchedOption) {
            $('#itemSelect').val(matchedOption.val()).trigger('change');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Item Ditemukan',
                    text: matchedOption.text(),
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        } else if (e.type === 'keydown' && e.which === 13) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ditemukan',
                    text: 'Item Part dengan kode "' + val + '" tidak ditemukan.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });

    // --- Outstanding Arrivals Helper Functions & Dynamic Balance Calculation ---
    function fetchOutstandingArrivals(itemId) {
        if (!itemId || !window.INCOMING_PART_CONFIG || !window.INCOMING_PART_CONFIG.arrivalsUrl) {
            $('#outstandingArrivalsWrap').addClass('d-none');
            clearArrivalFields();
            return;
        }

        $.ajax({
            url: window.INCOMING_PART_CONFIG.arrivalsUrl,
            data: { item_id: itemId },
            success: function(arrivals) {
                const $wrap = $('#outstandingArrivalsWrap');
                const $tbody = $('#outstandingArrivalsBody');
                $tbody.empty();

                if (arrivals && arrivals.length > 0) {
                    arrivals.forEach(function(arr, index) {
                        const tgl = arr.tanggal_datang ? new Date(arr.tanggal_datang).toLocaleDateString('id-ID') : '-';
                        const checked = index === 0 ? 'checked' : '';
                        $tbody.append(`
                            <tr>
                                <td>
                                    <input type="radio" name="arrival_radio" value="${arr.id}" data-sisa="${arr.qty_sisa}" data-tgl="${arr.tanggal_datang}" data-shift="${arr.shift_datang}" ${checked}>
                                </td>
                                <td>${tgl}</td>
                                <td>Shift ${arr.shift_datang}</td>
                                <td>${arr.qty_datang.toLocaleString('id-ID')} pcs</td>
                                <td class="font-weight-bold text-primary">${arr.qty_sisa.toLocaleString('id-ID')} pcs</td>
                            </tr>
                        `);
                    });

                    if (arrivals.length > 1) {
                        $wrap.removeClass('d-none');
                    } else {
                        $wrap.addClass('d-none');
                    }

                    selectArrival(arrivals[0]);
                } else {
                    $wrap.addClass('d-none');
                    clearArrivalFields();
                }
            },
            error: function() {
                $('#outstandingArrivalsWrap').addClass('d-none');
                clearArrivalFields();
            }
        });
    }

    function selectArrival(arr) {
        if (!arr) return;
        $('#arrivalIdInput').val(arr.id);
        $('#tanggalDatangInput').val(arr.tanggal_datang || '');
        $('#shiftDatangSelect').val(arr.shift_datang || '');
        $('#initialBalanceInput').val(arr.qty_sisa);
        
        updateDynamicBalance();
    }

    function clearArrivalFields() {
        $('#arrivalIdInput').val('');
        $('#initialBalanceInput').val(0);
        $('#tanggalDatangInput').val('');
        $('#shiftDatangSelect').val('');
        $('#qtyBalanceInput').val('');
        $('#maxCheckHint').text('');
        $('#totalCheckInput').removeAttr('max');
        $('#arrivalStatusHint').text('Opsional/Lot Baru');
    }

    function updateDynamicBalance() {
        const arrivalId = $('#arrivalIdInput').val();
        const initialBalance = parseFloat($('#initialBalanceInput').val()) || 0;
        const totalCheck = parseFloat($('#totalCheckInput').val()) || 0;

        if (arrivalId && initialBalance > 0) {
            const remaining = Math.max(0, initialBalance - totalCheck);
            $('#qtyBalanceInput').val(remaining);
            $('#maxCheckHint').text('Maks. check: ' + initialBalance.toLocaleString('id-ID') + ' pcs');
            $('#totalCheckInput').attr('max', initialBalance);
            $('#arrivalStatusHint').text('Kedatangan Terdaftar');

            updateRemarksSelisih(initialBalance, totalCheck);
        } else {
            const enteredBalance = parseFloat($('#qtyBalanceInput').val()) || 0;
            if (enteredBalance > 0 && totalCheck > 0) {
                updateRemarksSelisih(enteredBalance, totalCheck);
            }
            $('#maxCheckHint').text('');
            $('#totalCheckInput').removeAttr('max');
            $('#arrivalStatusHint').text('Opsional/Lot Baru');
        }
    }

    function updateRemarksSelisih(expected, actual) {
        const $remarks = $('textarea[name="remarks"]');
        if (!$remarks.length) return;

        let currentText = $remarks.val() || '';
        currentText = currentText.replace(/\[Selisih:[^\]]+\]\s*/gi, '').trim();

        if (expected > 0 && actual > 0 && expected !== actual) {
            const diff = actual - expected;
            let selisihTag = '';
            if (diff < 0) {
                selisihTag = `[Selisih: -${Math.abs(diff)} pcs (Kurang)]`;
            } else {
                selisihTag = `[Selisih: +${diff} pcs (Lebih)]`;
            }
            $remarks.val(currentText ? `${selisihTag} ${currentText}` : selisihTag);
        } else {
            $remarks.val(currentText);
        }
    }

    $(document).on('input change', '#totalCheckInput, #qtyBalanceInput', function() {
        updateDynamicBalance();
    });

    $(document).on('change', 'input[name="arrival_radio"]', function() {
        const arrivalId = $(this).val();
        const sisa = $(this).data('sisa');
        const tgl = $(this).data('tgl');
        const shift = $(this).data('shift');

        selectArrival({
            id: arrivalId,
            qty_sisa: sisa,
            tanggal_datang: tgl,
            shift_datang: shift
        });
    });

    $('#btnResetArrivalSelection').on('click', function() {
        $('input[name="arrival_radio"]').prop('checked', false);
        clearArrivalFields();
    });

    function checkFirstTimeAndConfirm(callback) {
        const itemId = $('#itemSelect').val();
        const tglDatang = $('#tanggalDatangInput').val();
        const qtyDatang = $('#qtyDatangInput').val();
        const arrivalId = $('#arrivalIdInput').val();

        if (!arrivalId && tglDatang && qtyDatang && parseInt(qtyDatang) > 0 && window.INCOMING_PART_CONFIG && window.INCOMING_PART_CONFIG.checkFirstTimeUrl) {
            $.ajax({
                url: window.INCOMING_PART_CONFIG.checkFirstTimeUrl,
                data: { item_id: itemId },
                success: function(res) {
                    if (!res.is_first_time) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan Kedatangan Berulang!',
                            text: 'Part ini sudah pernah tercatat kedatangannya sebelumnya. Mengisi Qty Kedatangan baru akan membuat Lot Kedatangan baru.',
                            showCancelButton: true,
                            confirmButtonColor: '#4e73df',
                            cancelButtonColor: '#858796',
                            confirmButtonText: 'Ya, Buat Lot Baru & Simpan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                callback();
                            } else {
                                $('#btnSubmitForm').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> SIMPAN DATA');
                            }
                        });
                    } else {
                        callback();
                    }
                },
                error: function() {
                    callback();
                }
            });
        } else {
            callback();
        }
    }

    function resetState(form) {
        form[0].reset();
        $('#itemSelect').val('').trigger('change');
        $('#defectContainer').find('.defect-row').not(':first').remove();
        $('#totalNgInput').val(0);
        $('#judgmentSelect').val('OK');
        $('#aqlInfoBox').empty();
        
        // Khusus untuk Select2 jika digunakan
        if ($.fn.select2) {
            form.find('.select2').trigger('change');
        }
    }

    // --- PDF Viewer Logic ---
    let pdfDoc = null, pageNum = 1, standardZoomLevel = 1.0;
    let standardFiles = [], standardFileIndex = 0;
    let pdfCache = {};

    // Panel Kanan (Similar/Dimensi)
    let similarDoc = null, similarPageNum = 1, similarZoomLevel = 1.0;

    if (typeof pdfjsLib !== 'undefined' && window.pdfWorkerSrc) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = window.pdfWorkerSrc;
    }

    function renderSimilarPdfToCanvas(url, pNum = 1) {
        const canvas = document.getElementById('similarPdfCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const $placeholder = $('#similarPdfPlaceholder');
        const $loading = $('#similarPdfLoading');
        const $canvas = $(canvas);

        $placeholder.removeClass('d-flex').addClass('d-none');
        $canvas.addClass('d-none').hide();
        $loading.removeClass('d-none').addClass('d-flex');

        if (pdfCache[url]) {
            drawSimilarPage(pdfCache[url], canvas, ctx, $loading, $canvas, pNum);
            return;
        }

        pdfjsLib.getDocument(url).promise.then((pdf) => {
            pdfCache[url] = pdf;
            drawSimilarPage(pdf, canvas, ctx, $loading, $canvas, pNum);
        }).catch(() => {
            $loading.removeClass('d-flex').addClass('d-none');
            $placeholder.removeClass('d-none').addClass('d-flex');
            $('#similarStatusText').text('Gagal memuat PDF dimensi');
        });
    }

    function drawSimilarPage(pdf, canvas, ctx, $loading, $canvas, pNum) {
        pdf.getPage(pNum).then((page) => {
            const containerWidth = $canvas.parent().width() || 500;
            const viewport = page.getViewport({ scale: 1.0 });
            const scale = ((containerWidth - 40) / viewport.width) * similarZoomLevel;
            const scaledViewport = page.getViewport({ scale });

            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            if (similarZoomLevel > 1.0) $canvas.css({ width: 'auto', 'max-width': 'none' });
            else $canvas.css({ width: '100%', 'max-width': '100%' });
            $canvas.css('height', 'auto');

            page.render({ canvasContext: ctx, viewport: scaledViewport }).promise.then(() => {
                $loading.removeClass('d-flex').addClass('d-none');
                $canvas.removeClass('d-none').show();
                similarDoc = pdf;
                similarPageNum = pNum;
                $('#similarPageInfo').text(`P ${pNum}/${pdf.numPages}`);
                if (pdf.numPages > 1) $('.similar-nav-controls').attr('style', 'display: flex !important;');
                else $('.similar-nav-controls').hide();
            });
        });
    }

    $('#prevSimilarPage').click(() => {
        if (similarDoc && similarPageNum > 1) {
            similarPageNum--;
            const canvas = document.getElementById('similarPdfCanvas');
            drawSimilarPage(similarDoc, canvas, canvas.getContext('2d'), $('#similarPdfLoading'), $(canvas), similarPageNum);
        }
    });

    $('#nextSimilarPage').click(() => {
        if (similarDoc && similarPageNum < similarDoc.numPages) {
            similarPageNum++;
            const canvas = document.getElementById('similarPdfCanvas');
            drawSimilarPage(similarDoc, canvas, canvas.getContext('2d'), $('#similarPdfLoading'), $(canvas), similarPageNum);
        }
    });

    $('#zoomInSimilar').click(() => {
        similarZoomLevel += 0.25;
        if (similarDoc) {
            const canvas = document.getElementById('similarPdfCanvas');
            drawSimilarPage(similarDoc, canvas, canvas.getContext('2d'), $('#similarPdfLoading'), $(canvas), similarPageNum);
        }
    });
    $('#zoomOutSimilar').click(() => {
        if (similarZoomLevel > 0.5) {
            similarZoomLevel -= 0.25;
            if (similarDoc) {
                const canvas = document.getElementById('similarPdfCanvas');
                drawSimilarPage(similarDoc, canvas, canvas.getContext('2d'), $('#similarPdfLoading'), $(canvas), similarPageNum);
            }
        }
    });
    $('#zoomResetSimilar').click(() => {
        similarZoomLevel = 1.0;
        if (similarDoc) {
            const canvas = document.getElementById('similarPdfCanvas');
            drawSimilarPage(similarDoc, canvas, canvas.getContext('2d'), $('#similarPdfLoading'), $(canvas), similarPageNum);
        }
    });


    function renderPdfToCanvas(url, canvasId, placeholderId, loadingId, pNum = 1) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const $placeholder = $("#" + placeholderId);
        const $loading = $("#" + loadingId);
        const $canvas = $(canvas);

        $placeholder.removeClass("d-flex").addClass("d-none");
        $canvas.addClass("d-none").hide();
        $loading.removeClass("d-none").addClass("d-flex");

        if (pdfCache[url]) {
            drawPage(pdfCache[url], canvas, ctx, $loading, $canvas, pNum);
            return;
        }

        pdfjsLib.getDocument(url).promise.then((pdf) => {
            pdfCache[url] = pdf;
            drawPage(pdf, canvas, ctx, $loading, $canvas, pNum);
        }).catch((err) => {
            $loading.removeClass("d-flex").addClass("d-none");
            $placeholder.removeClass("d-none").addClass("d-flex").find("p").text("Gagal memuat PDF");
        });
    }

    function drawPage(pdf, canvas, ctx, $loading, $canvas, pNum) {
        pdf.getPage(pNum).then((page) => {
            const containerWidth = $canvas.parent().width() || 500;
            const availableWidth = containerWidth - 40;
            const viewport = page.getViewport({ scale: 1.0 });
            const scale = (availableWidth / viewport.width) * standardZoomLevel;
            const scaledViewport = page.getViewport({ scale: scale });

            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            if (standardZoomLevel > 1.0) $canvas.css({ width: "auto", "max-width": "none" });
            else $canvas.css({ width: "100%", "max-width": "100%" });
            $canvas.css("height", "auto");

            page.render({ canvasContext: ctx, viewport: scaledViewport }).promise.then(() => {
                $loading.removeClass("d-flex").addClass("d-none");
                $canvas.removeClass("d-none").show();
                pdfDoc = pdf;
                pageNum = pNum;
                
                const fileInfo = standardFiles.length > 1 ? ` (${standardFileIndex + 1}/${standardFiles.length})` : '';
                $("#standardPageInfo").text(`P ${pageNum}/${pdf.numPages}${fileInfo}`);
                
                if (standardFiles.length > 0) $(".standard-nav-controls").attr("style", "display: flex !important;");
                else $(".standard-nav-controls").hide();
            });
        });
    }

    function renderPageOnCanvas() {
        if (!pdfDoc) return;
        const canvas = document.getElementById("standardPdfCanvas");
        if (!canvas) return;
        const ctx = canvas.getContext("2d");
        const $canvas = $(canvas);
        const $loading = $("#standardPdfLoading");

        $canvas.hide();
        $loading.removeClass("d-none").addClass("d-flex");
        drawPage(pdfDoc, canvas, ctx, $loading, $canvas, pageNum);
    }

    $('#prevStandardPage').click(() => {
        if (pageNum > 1) {
            pageNum--;
            renderPageOnCanvas();
        } else if (standardFileIndex > 0) {
            standardFileIndex--;
            const itemId = $('#itemSelect').val();
            const prevFileUrl = window.pdfUrlPattern.replace("ID_PLACEHOLDER", itemId).replace("INDEX_PLACEHOLDER", standardFileIndex);
            renderPdfToCanvas(prevFileUrl, "standardPdfCanvas", "standardPdfPlaceholder", "standardPdfLoading", 1);
        }
    });

    $('#nextStandardPage').click(() => {
        if (pdfDoc && pageNum < pdfDoc.numPages) {
            pageNum++;
            renderPageOnCanvas();
        } else if (standardFiles && standardFileIndex < standardFiles.length - 1) {
            standardFileIndex++;
            const itemId = $('#itemSelect').val();
            const nextFileUrl = window.pdfUrlPattern.replace("ID_PLACEHOLDER", itemId).replace("INDEX_PLACEHOLDER", standardFileIndex);
            renderPdfToCanvas(nextFileUrl, "standardPdfCanvas", "standardPdfPlaceholder", "standardPdfLoading", 1);
        }
    });

    $("#zoomInStandard").click(() => {
        standardZoomLevel += 0.25;
        if (pdfDoc) renderPageOnCanvas();
    });
    $("#zoomOutStandard").click(() => {
        if (standardZoomLevel > 0.5) {
            standardZoomLevel -= 0.25;
            if (pdfDoc) renderPageOnCanvas();
        }
    });
    $("#zoomResetStandard").click(() => {
        standardZoomLevel = 1.0;
        if (pdfDoc) renderPageOnCanvas();
    });

    // Clear is-invalid class on input
    $('#checksheetForm').on('input change', 'input[required], select[required], textarea[required]', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });

    // --- Dimension Table Logic (Point Only - Vertical) ---
    const maxPoints = 50; // Increased max points since it's vertical

    $('#addPointRowBtn').click(function () {
        const tbody = $('#dimensionBody');
        let currentPoints = tbody.find('tr.point-row').length;
        
        if (currentPoints < maxPoints) {
            currentPoints++;
            const newRow = `
                <tr class="point-row">
                    <td class="text-center font-weight-bold bg-light align-middle point-label" style="font-size:0.7rem;">P${currentPoints}</td>
                    <td class="point-cell p-1">
                        <input type="text" class="dimension-input form-control-sm border-0 shadow-sm w-100 text-center" name="dimensions[]" placeholder="...">
                    </td>
                    <td class="text-center align-middle p-1">
                        <button type="button" class="btn btn-xs btn-danger shadow-sm delete-point-row" title="Hapus Point">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(newRow);
            updatePointLabels();
        } else {
            Swal.fire({ icon: 'warning', title: 'Batas Maksimum', text: 'Maksimal ' + maxPoints + ' point.' });
        }
    });

    $(document).on('click', '.delete-point-row', function () {
        const tbody = $('#dimensionBody');
        if (tbody.find('tr.point-row').length > 1) {
            $(this).closest('tr.point-row').remove();
            updatePointLabels();
        } else {
            Swal.fire({ icon: 'warning', title: 'Minimal 1 Point', text: 'Tidak bisa menghapus semua point.' });
        }
    });

    function updatePointLabels() {
        $('#dimensionBody tr.point-row').each(function(index) {
            $(this).find('.point-label').text('P' + (index + 1));
        });
        validateDimensions();
    }

    $(document).on("input", ".dimension-input", function () {
        let val = $(this).val();
        if (val.startsWith("+0")) $(this).val(val.replace(/^\+0/, ""));
        validateDimensions();
    });

    function validateDimensions() {
        const selectedOption = $("#itemSelect").find("option:selected");
        const dimensionStandardsJson = selectedOption.data("dimension-standards");
        let dimensionStandards = null;
        if (dimensionStandardsJson) {
            dimensionStandards = typeof dimensionStandardsJson === 'string' ? JSON.parse(dimensionStandardsJson) : dimensionStandardsJson;
        }

        let hasInvalidDimension = false;

        $('input[name^="dimensions"]').each(function () {
            const row = $(this).closest('tr.point-row');
            const pointIndex = row.index() + 1;

            let standard = null;
            if (dimensionStandards) {
                if (Array.isArray(dimensionStandards)) {
                    standard = dimensionStandards.find(s => String(s.point) === String(pointIndex)) || dimensionStandards[pointIndex - 1];
                } else {
                    standard = dimensionStandards[pointIndex];
                }
            }

            const valStr = $(this).val().trim();
            const value = parseFloat(valStr.replace(",", "."));

            $(this).removeClass("is-invalid is-valid text-danger text-success");

            if (standard && valStr !== "" && !isNaN(value)) {
                let isInvalid = false;
                const epsilon = 0.00001;

                if (standard.min != null && standard.min !== "") {
                    const minBound = parseFloat(String(standard.min).replace(",", "."));
                    if (!isNaN(minBound) && value < minBound - epsilon) isInvalid = true;
                }
                if (!isInvalid && standard.max != null && standard.max !== "") {
                    const maxBound = parseFloat(String(standard.max).replace(",", "."));
                    if (!isNaN(maxBound) && value > maxBound + epsilon) isInvalid = true;
                }

                if (!isInvalid && standard.size != null && standard.tolerance != null && standard.size !== "" && standard.tolerance !== "") {
                    const stdSzStr = normalizeStandardValue(standard.size);
                    if (!stdSzStr.startsWith("+") && !stdSzStr.startsWith("-")) {
                        const base = parseFloat(stdSzStr);
                        const tol = normalizeStandardValue(standard.tolerance);
                        let lb = base, ub = base;

                        if (tol.includes("/")) {
                            tol.split("/").forEach(p => {
                                p = normalizeStandardValue(p);
                                const fv = parseFloat(p);
                                if (p.startsWith("+") || fv > 0) ub = base + Math.abs(fv);
                                else if (p.startsWith("-") || fv < 0) lb = base - Math.abs(fv);
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

                        if (value < lb - epsilon || value > ub + epsilon) isInvalid = true;
                    }
                }

                if (isInvalid) {
                    $(this).addClass("is-invalid text-danger");
                    hasInvalidDimension = true;
                } else {
                    $(this).addClass("is-valid text-success");
                }
            } else if (valStr !== "" && isNaN(value) && valStr !== "-") {
                 $(this).addClass("is-invalid text-danger");
                 hasInvalidDimension = true;
            } else if (valStr !== "" && !isNaN(value)) {
                $(this).addClass("is-valid text-success");
            }
        });

        window.hasInvalidDimension = hasInvalidDimension;
        calculateAndJudge();
    }

    function checkMandatoryDimensions() {
        const result = { isValid: true, missingPoints: [], firstEmpty: null };
        const selectedOption = $("#itemSelect").find("option:selected");
        const dimensionStandardsJson = selectedOption.data("dimension-standards");
        let dimensionStandards = null;
        if (dimensionStandardsJson) {
            dimensionStandards = typeof dimensionStandardsJson === 'string' ? JSON.parse(dimensionStandardsJson) : dimensionStandardsJson;
        }

        if (!dimensionStandards) return result;

        $(".dimension-input").each(function () {
            const row = $(this).closest('tr.point-row');
            const pointIndex = row.index() + 1;
            
            let standard = null;
            if (Array.isArray(dimensionStandards)) {
                standard = dimensionStandards.find(s => String(s.point) === String(pointIndex)) || dimensionStandards[pointIndex - 1];
            } else {
                standard = dimensionStandards[pointIndex];
            }

            if (standard && $(this).val().trim() === "") {
                result.isValid = false;
                result.missingPoints.push('P' + pointIndex);
                $(this).addClass("is-invalid text-danger");
                if (!result.firstEmpty) result.firstEmpty = $(this);
            }
        });

        return result;
    }

    // ─── AUDIO FEEDBACK LOGIC ───
    let audioCtx = null;
    function unlockAudio() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === "suspended") {
            audioCtx.resume();
        }
    }

    function playSuccessFeedback() {
        try {
            unlockAudio();
            if (audioCtx) {
                const oscillator = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                oscillator.type = "sine";
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                gain.gain.setValueAtTime(0, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.2, audioCtx.currentTime + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                oscillator.connect(gain);
                gain.connect(audioCtx.destination);
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.3);
            }
        } catch (e) {
            console.warn("Feedback error:", e);
        }
    }

    // ─── CAMERA & MODAL QR SCANNER ───
    let qrScanner = null;

    function stopQrScanner() {
        if (qrScanner) {
            qrScanner.stop();
            qrScanner.destroy();
            qrScanner = null;
        }
    }

    function initQrScanner() {
        const btnScan = $('#btnScanQR');
        if (!btnScan.length) return;

        btnScan.on('click', function (e) {
            e.preventDefault();
            unlockAudio();
            $('#qrScannerModal').modal('show');
        });

        $('#qrScannerModal').on('shown.bs.modal', function () {
            const videoElem = document.getElementById("qr-video");
            if (qrScanner) {
                stopQrScanner();
            }

            if (typeof QrScanner !== 'undefined') {
                qrScanner = new QrScanner(
                    videoElem,
                    (result) => handleQRScanned(result.data),
                    {
                        highlightScanRegion: true,
                        highlightCodeOutline: true,
                        maxScansPerSecond: 25,
                        preferredCamera: "environment",
                    }
                );

                qrScanner._setVideoMirror = function (facingMode) { };

                $("#toggleMirrorBtn").off("click").on("click", function () {
                    $(videoElem).toggleClass("mirrored");
                });

                qrScanner.start().then(() => {
                    qrScanner.hasFlash().then((hasFlash) => {
                        if (hasFlash) $("#toggleFlashBtn").removeClass("d-none");
                    });

                    $("#toggleFlashBtn").off("click").on("click", function () {
                        qrScanner.toggleFlash();
                    });

                    const track = qrScanner.$video.srcObject ? qrScanner.$video.srcObject.getVideoTracks()[0] : null;
                    if (track) {
                        const capabilities = track.getCapabilities ? track.getCapabilities() : {};
                        if (capabilities.zoom) {
                            $("#zoomContainer").removeClass("d-none");
                            const $slider = $("#zoomSlider");
                            $slider.attr({
                                min: capabilities.zoom.min,
                                max: capabilities.zoom.max,
                                step: capabilities.zoom.step || 0.1,
                            }).val(track.getSettings().zoom || capabilities.zoom.min);

                            $slider.off("input").on("input", function () {
                                track.applyConstraints({
                                    advanced: [{ zoom: parseFloat($(this).val()) }],
                                });
                            });
                        }
                    }
                }).catch(err => {
                    console.error("Scanner error:", err);
                });
            }
        });

        $("#qr-input-file").on("change", async function (e) {
            if (e.target.files.length === 0) return;
            const imageFile = e.target.files[0];
            $("#qr-video").addClass("d-none");
            $("#qr-reader-results").removeClass("d-none");

            try {
                const result = await QrScanner.scanImage(imageFile, { returnDetailedScanResult: true });
                handleQRScanned(result.data);
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

        $('#qrScannerModal').on('hidden.bs.modal', function () {
            stopQrScanner();
            isProcessingScan = false;
        });
    }

    let isProcessingScan = false;

    function handleQRScanned(decodedText) {
        if (isProcessingScan) return;
        isProcessingScan = true;

        playSuccessFeedback();
        $("#scanMethodInput").val("camera");

        parseAndFillQR(decodedText, function (success) {
            if (success) {
                unlockInputs();
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: 'QR Berhasil Ditambahkan ke List!'
                });
            }

            // Cooldown 1.2 detik agar kamera siap scan QR berikutnya tanpa harus buka-tutup modal
            setTimeout(function () {
                isProcessingScan = false;
            }, 1200);
        });
    }

    // ─── FORMAT QR PARSING & DOUBLE DUPLICATE VALIDATION ───
    function parseAndFillQR(qrString, callback) {
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

        // 1. Validasi duplikat pada localStorage queue
        const queue = JSON.parse(localStorage.getItem('incoming_part_queue') || '[]');
        const isDuplicateInQueue = queue.some(item => {
            return (parseInt(item.quantity) || 0) === quantity &&
                (item.unique_code_id || "").trim() === unique_code_id;
        });

        if (isDuplicateInQueue) {
            Swal.fire(
                "QR-Code Duplicate",
                `QR Code dengan Qty: ${quantity} dan ID: ${unique_code_id} sudah ada di list antrean!`,
                "error"
            );
            if (callback) callback(false);
            return;
        }

        // 2. Validasi duplikat ke database via AJAX jika url tersedia
        const qrUniqueUrl = (window.INCOMING_PART_CONFIG && window.INCOMING_PART_CONFIG.qrUniqueUrl) ? window.INCOMING_PART_CONFIG.qrUniqueUrl : null;
        
        if (qrUniqueUrl) {
            $.get(qrUniqueUrl, { qrcode: qrString }, function (res) {
                if (res.success && !res.unique) {
                    Swal.fire("QR-Code Duplicate", res.message, "error");
                    if (callback) callback(false);
                    return;
                }
                applyParsedQrData(qrString, part_code, supplier_id, quantity, unique_code_id, sap_code, callback);
            }).fail(function() {
                applyParsedQrData(qrString, part_code, supplier_id, quantity, unique_code_id, sap_code, callback);
            });
        } else {
            applyParsedQrData(qrString, part_code, supplier_id, quantity, unique_code_id, sap_code, callback);
        }
    }

    function applyParsedQrData(qrString, part_code, supplier_id, quantity, unique_code_id, sap_code, callback) {
        $('#qrcodeInput').val(qrString);
        $('#partCodeInput').val(part_code);
        $('#supplierIdInput').val(supplier_id);
        $('#quantityInput').val(quantity);
        $('#uniqueCodeInput').val(unique_code_id);
        $('#sapCodeInputHidden').val(sap_code);
        $('#sapCodeInput').val(sap_code);

        let matchedItemValue = null;
        $('#itemSelect option').each(function () {
            const optionSap = $(this).data('sap_code');
            const optionPn = normalizePartNumber($(this).data('part-number'));

            if (optionSap && String(optionSap).trim() === String(sap_code).trim()) {
                matchedItemValue = $(this).val();
                return false;
            }
            if (optionPn && optionPn === normalizePartNumber(part_code)) {
                matchedItemValue = $(this).val();
                return false;
            }
        });

        if (matchedItemValue) {
            $('#itemSelect').val(matchedItemValue).trigger('change');
            
            // Auto add directly to queue list upon scan (persis In-Process)
            setTimeout(function() {
                autoAddScanToQueue();
                if (callback) callback(true);
            }, 150);
        } else {
            Swal.fire({
                icon: "error",
                title: "Item Part Tidak Ditemukan",
                text: `Tidak ada Item Part dengan Kode SAP: ${sap_code} atau Part No: ${part_code}`
            });
            if (callback) callback(false);
        }
    }

    // ─── HARDWARE BARCODE SCANNER LISTENER ───
    function initHardwareScanner() {
        let scanTimeout = null;

        $('#sapCodeInput').on('input keydown', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter') return;
            const val = $(this).val().trim();

            if (val.includes('|')) {
                e.preventDefault();
                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(() => {
                    $('#scanMethodInput').val('hardware');
                    parseAndFillQR(val, function(success) {
                        if (success) {
                            unlockInputs();
                        }
                    });
                }, 100);
            }
        });
    }

    // ─── TEMPORARY QUEUE SYSTEM (LOCAL STORAGE & BATCH AJAX SAVE) ───
    function initTempQueue() {
        renderQueueTable();

        $('#btnSaveQueue').on('click', function () {
            saveQueueSequentially();
        });

        $('#btnClearQueue').on('click', function () {
            clearQueue();
        });

        $(document).on('click', '.btn-delete-queue-item', function () {
            const idx = $(this).data('index');
            deleteQueueItem(idx);
        });
    }

    function autoAddScanToQueue() {
        const itemId = $('#itemSelect').val();
        if (!itemId) return;

        let totalCheck = $('#totalCheckInput').val();
        if (!totalCheck || parseInt(totalCheck) <= 0) {
            totalCheck = $('#quantityInput').val() || 1;
            $('#totalCheckInput').val(totalCheck);
        }

        const defect_types = [];
        $('.defect-select').each(function () {
            if ($(this).val()) defect_types.push($(this).val());
        });
        const defect_quantities = [];
        $('.defect-qty').each(function () {
            if ($(this).val()) defect_quantities.push($(this).val());
        });

        const queueItem = {
            plant_id: $('input[name="plant_id"]').val(),
            arrival_id: $('#arrivalIdInput').val(),
            qrcode: $('#qrcodeInput').val(),
            part_code: $('#partCodeInput').val(),
            supplier_id: $('#supplierIdInput').val(),
            quantity: $('#quantityInput').val(),
            unique_code_id: $('#uniqueCodeInput').val(),
            sap_code: $('#sapCodeInputHidden').val(),
            scan_method: $('#scanMethodInput').val() || "hardware",
            item_id: itemId,
            tanggal_datang: $('#tanggalDatangInput').val(),
            shift_datang: $('#shiftDatangSelect').val(),
            qty_datang: $('#qtyBalanceInput').val(),
            date: $('input[name="date"]').val(),
            shift: $('select[name="shift"]').val(),
            total_check: totalCheck,
            judgment: $('#judgmentSelect').val() || "OK",
            operator_initials: $('input[name="operator_initials"]').val(),
            remarks: $('textarea[name="remarks"]').val(),
            defect_types: defect_types,
            defect_quantities: defect_quantities,
            itemNameDisplay: $("#itemSelect option:selected").text().trim(),
            dimensions: $('input[name^="dimensions"]').map(function() { return $(this).val(); }).get()
        };

        const queue = JSON.parse(localStorage.getItem('incoming_part_queue') || '[]');
        queue.push(queueItem);
        localStorage.setItem('incoming_part_queue', JSON.stringify(queue));

        resetFormForNextInput();
        renderQueueTable();
        applyAutoFocus();
    }

    function applyAutoFocus() {
        setTimeout(function() {
            const $input = $("#sapCodeInput");
            if ($input.length) {
                $input.attr('inputmode', 'none');
                $input.focus();
                $input.one('mousedown touchstart', function () {
                    $(this).attr('inputmode', 'text');
                });
            }
        }, 300);
    }

    function addToQueue() {
        const itemId = $('#itemSelect').val();
        if (!itemId) {
            Swal.fire('Form Belum Lengkap', 'Silakan pilih Item Part terlebih dahulu.', 'warning');
            return;
        }

        const totalCheck = $('#totalCheckInput').val();
        if (!totalCheck || parseInt(totalCheck) <= 0) {
            Swal.fire('Form Belum Lengkap', 'Total Check harus diisi dengan angka lebih dari 0.', 'warning');
            return;
        }

        const defect_types = [];
        $('.defect-select').each(function () {
            if ($(this).val()) defect_types.push($(this).val());
        });
        const defect_quantities = [];
        $('.defect-qty').each(function () {
            if ($(this).val()) defect_quantities.push($(this).val());
        });

        const dimCheck = checkMandatoryDimensions();
        if (!dimCheck.isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Dimensi Belum Lengkap',
                text: `Point dimensi berikut wajib diisi: ${dimCheck.missingPoints.join(', ')}`
            });
            if (dimCheck.firstEmpty) dimCheck.firstEmpty.focus();
            return;
        }

        const queueItem = {
            plant_id: $('input[name="plant_id"]').val(),
            arrival_id: $('#arrivalIdInput').val(),
            qrcode: $('#qrcodeInput').val(),
            part_code: $('#partCodeInput').val(),
            supplier_id: $('#supplierIdInput').val(),
            quantity: $('#quantityInput').val(),
            unique_code_id: $('#uniqueCodeInput').val(),
            sap_code: $('#sapCodeInputHidden').val(),
            scan_method: $('#scanMethodInput').val() || "manual",
            item_id: itemId,
            tanggal_datang: $('#tanggalDatangInput').val(),
            shift_datang: $('#shiftDatangSelect').val(),
            qty_datang: $('#qtyBalanceInput').val(),
            date: $('input[name="date"]').val(),
            shift: $('select[name="shift"]').val(),
            total_check: totalCheck,
            judgment: $('#judgmentSelect').val(),
            operator_initials: $('input[name="operator_initials"]').val(),
            remarks: $('textarea[name="remarks"]').val(),
            defect_types: defect_types,
            defect_quantities: defect_quantities,
            itemNameDisplay: $("#itemSelect option:selected").text().trim(),
            dimensions: $('input[name^="dimensions"]').map(function() { return $(this).val(); }).get()
        };

        const queue = JSON.parse(localStorage.getItem('incoming_part_queue') || '[]');
        queue.push(queueItem);
        localStorage.setItem('incoming_part_queue', JSON.stringify(queue));

        resetFormForNextInput();
        renderQueueTable();
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Ditambahkan',
            text: 'Data berhasil dimasukkan ke daftar antrean scan sementara.',
            timer: 1200,
            showConfirmButton: false
        });
    }

    function renderQueueTable() {
        const queue = JSON.parse(localStorage.getItem('incoming_part_queue') || '[]');
        const tbody = $("#tempQueueBody");
        tbody.empty();

        if (queue.length === 0) {
            $("#tempQueueCard").addClass("d-none");
            return;
        }

        $("#tempQueueCard").removeClass("d-none");
        $("#queueBadge").text(`${queue.length} Data`);
        $("#queueCountDisplay").text(queue.length);

        let totalQtyCheckSum = 0;

        queue.forEach((item, index) => {
            const qtyCheck = parseInt(item.total_check) || 0;
            totalQtyCheckSum += qtyCheck;

            const judgmentClass = item.judgment === 'OK' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
            const initialsUpper = (item.operator_initials || '-').toUpperCase();

            // Format Detail NG
            let defectDetail = '-';
            if (item.total_ng && parseInt(item.total_ng) > 0) {
                defectDetail = `<span class="text-danger font-weight-bold">${item.total_ng} NG</span>`;
            } else if (item.defect_types && item.defect_types.length > 0) {
                defectDetail = `<span class="text-danger font-weight-bold">${item.defect_types.length} NG</span>`;
            }

            const tr = `
                <tr>
                    <td>${index + 1}</td>
                    <td class="text-left font-weight-bold" style="max-width: 180px;">${item.itemNameDisplay || '-'}</td>
                    <td class="text-left font-weight-bold" style="word-break: break-all; max-width: 160px;">${item.qrcode || '-'}</td>
                    <td>${item.date || '-'} (Shift ${item.shift || '-'})</td>
                    <td class="font-weight-bold text-center">${qtyCheck.toLocaleString('id-ID')}</td>
                    <td>${defectDetail}</td>
                    <td><span class="${judgmentClass}">${item.judgment || '-'}</span></td>
                    <td>${initialsUpper}</td>
                    <td class="text-left" style="max-width: 150px;">${item.remarks || '-'}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-xs btn-delete-queue-item" data-index="${index}" title="Hapus data">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });

        $("#totalQtyCheckDisplay").text(totalQtyCheckSum.toLocaleString('id-ID'));
    }

    function deleteQueueItem(index) {
        const queue = JSON.parse(localStorage.getItem('incoming_part_queue') || '[]');
        if (index >= 0 && index < queue.length) {
            queue.splice(index, 1);
            localStorage.setItem('incoming_part_queue', JSON.stringify(queue));
            renderQueueTable();
        }
    }

    function clearQueue() {
        Swal.fire({
            title: "Kosongkan Daftar Scan?",
            text: "Semua data scan sementara akan dihapus dari browser ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#858796",
            confirmButtonText: "Ya, Hapus Semua!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.removeItem('incoming_part_queue');
                renderQueueTable();
            }
        });
    }

    function saveQueueSequentially() {
        const queue = JSON.parse(localStorage.getItem('incoming_part_queue') || '[]');
        if (queue.length === 0) {
            Swal.fire("Daftar Kosong", "Tidak ada data untuk disimpan.", "info");
            return;
        }

        Swal.fire({
            title: "Simpan Semua Data?",
            text: `Sebanyak ${queue.length} data antrean akan disimpan ke database.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#4e73df",
            cancelButtonColor: "#858796",
            confirmButtonText: "Ya, Simpan Semua",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                executeSequentialSave();
            }
        });
    }

    async function executeSequentialSave() {
        const queue = JSON.parse(localStorage.getItem('incoming_part_queue') || '[]');
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
            const percent = Math.round(((i + 1) / queue.length) * 100);
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
                appendToFormData(formData, item[key], key);
            });

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
            localStorage.removeItem('incoming_part_queue');
            renderQueueTable();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disimpan!',
                text: `Seluruh ${successCount} data antrean berhasil disimpan ke database.`,
                confirmButtonColor: '#4e73df'
            }).then(() => {
                window.location.href = window.INCOMING_PART_CONFIG && window.INCOMING_PART_CONFIG.index_url ? window.INCOMING_PART_CONFIG.index_url : window.location.href;
            });
        } else {
            const remainingQueue = queue.slice(failedIndex);
            localStorage.setItem('incoming_part_queue', JSON.stringify(remainingQueue));
            renderQueueTable();

            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan Data',
                html: `Tersimpan: ${successCount} data.<br>Gagal pada baris ke-${failedIndex + 1}: <b>${errorMessage}</b>`
            });
        }
    }

    function unlockInputs() {
        const form = $("#checksheetForm");
        form.find("input, select, textarea, button").not('[type="hidden"]').prop("disabled", false);
        $("#saveBtn").prop("disabled", false);
        $("#btnAddQueueItem").prop("disabled", false);
        form.removeClass("inputs-locked");
    }

    function resetFormForNextInput() {
        $('#qrcodeInput').val('');
        $('#partCodeInput').val('');
        $('#supplierIdInput').val('');
        $('#quantityInput').val('');
        $('#uniqueCodeInput').val('');
        $('#sapCodeInputHidden').val('');
        $('#sapCodeInput').val('');
        $('#totalCheckInput').val('');
        $('textarea[name="remarks"]').val('');
        $('#defectContainer .defect-row').not(':first').remove();
        $('#defectSelect').val('');
        $('.defect-qty').val('');
        $('#judgmentSelect').val('OK').trigger('change');
    }
});


