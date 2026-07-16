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
            formInputs.prop("disabled", false);
            $("#saveBtn").prop("disabled", false);
            form.removeClass("inputs-locked");
            
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

    // Tangani Pemilihan Item untuk memperbarui Dropdown Defect
    $('#itemSelect').on('change', function () {
        const selected = $(this).find(':selected');
        const defects = selected.data('defects');
        updateDefectOptions(defects);
        
        if ($(this).val()) {
            $('#addDefectBtn').show();
            
            // PDF Logic
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
                $("#standardPdfPlaceholder").removeClass("d-none").addClass("d-flex").find("p").text("Standard PDF tidak tersedia");
                $(".standard-nav-controls").hide();
            }
        } else {
            $('#addDefectBtn').hide();
            $("#standardPdfCanvas").addClass("d-none").hide();
            $("#standardPdfPlaceholder").removeClass("d-none").addClass("d-flex").find("p").text("Pilih Item untuk menampilkan Standard PDF");
            $(".standard-nav-controls").hide();
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

    // Hitung Otomatis Komper/Karung dan Ukuran Sampel dari Qty (Kg)
    // Asumsi: 1 karung = 25 kg
    $('#lotQtyInput').on('input', function() {
        const qtyKg = parseFloat($(this).val()) || 0;
        
        if (qtyKg > 0) {
            const totalKarung = Math.ceil(qtyKg / 25);
            $('#komperKarungInput').val(totalKarung);
            
            const sampleSize = AQL_TABLE.getSampleSize(totalKarung);
            $('#totalCheckInput').val(sampleSize).trigger('input');
        } else {
            $('#komperKarungInput').val(0);
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
        
        const judgment = (totalNg >= aql.rej) ? 'NG' : 'OK';
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

    // Pengiriman Formulir AJAX
    $('#checksheetForm').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        
        // Validasi Manual untuk Required Fields
        let isValid = true;
        let missingFields = [];
        
        const fieldNames = {
            'item_id': 'Material Name',
            'tanggal_datang': 'Tgl Datang',
            'expired_date': 'Expired Date',
            'date': 'Tanggal Check',
            'lot_batch_number': 'Lot/Batch Number',
            'quantity_kg': 'Qty (Kg)',
            'komper_karung_kg': 'Komper/Karung',
            'sampling_size_karung_kg': 'Sampling Size',
            'judgment': 'Judgment',
            'operator_initials': 'QC'
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

        if (!isValid) {
            let errorHtml = 'Pastikan semua kolom yang wajib sudah terisi sebelum menyimpan data.<br><br>';
            if (missingFields.length > 0) {
                errorHtml += '<div class="text-left"><strong class="text-danger">Kolom yang belum diisi:</strong><ul class="text-danger mt-1">';
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

        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
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
    });

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

    if (typeof pdfjsLib !== 'undefined' && window.pdfWorkerSrc) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = window.pdfWorkerSrc;
    }

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
});
