/**
 * Logika Edit Checksheet Incoming
 * Menangani baris defect dinamis di modal edit
 */

const AQL_TABLE_EDIT = {
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

$(document).ready(function () {
    // Delegasi untuk konten dinamis yang dimuat via AJAX
    $(document).on('click', '#editAddDefectBtn', function () {
        const container = $('#editDefectContainer');
        const firstRow = container.find('.edit-defect-row').first().clone();
        
        firstRow.find('input').val('');
        firstRow.find('select').val('');
        
        const lastCol = firstRow.find('.action-col');
        lastCol.empty().append('<button type="button" class="btn btn-danger btn-sm shadow-sm remove-defect-btn"><i class="fas fa-times"></i></button>');
        
        container.append(firstRow);
    });

    $(document).on('click', '.remove-defect-btn', function () {
        const rows = $('.edit-defect-row');
        if (rows.length > 1) {
            $(this).closest('.edit-defect-row').remove();
        } else {
            // Kosongkan baris pertama jika itu satu-satunya
            const row = $(this).closest('.edit-defect-row');
            row.find('input').val('');
            row.find('select').val('');
        }
    });

    // Logika validasi dimensi & judgment otomatis pada modal edit Incoming
    function normalizeEditStd(val) {
        if (val === null || val === undefined || val === '') return null;
        return String(val)
            .replace(',', '.')
            .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
            .replace(/[Ø⌀ø±\u00B1\u00D8\u00F8\u2300]/g, '')
            .trim();
    }

    function recalculateEditJudgment() {
        const isDimInvalid = $('#editChecksheetForm .edit-dimension-input.is-invalid').length > 0;

        let hasDimDefect = false;
        $('#editDefectContainer .edit-defect-select').each(function () {
            const val = ($(this).val() || '').trim().toLowerCase();
            const txt = ($(this).find('option:selected').text() || '').trim().toLowerCase();
            if (val.includes('dimens') || txt.includes('dimens')) {
                hasDimDefect = true;
                return false;
            }
        });

        if (isDimInvalid && !hasDimDefect) {
            let targetRow = null;
            $('#editDefectContainer .edit-defect-row').each(function () {
                if (!$(this).find('.edit-defect-select').val()) {
                    targetRow = $(this);
                    return false;
                }
            });
            if (!targetRow) {
                const newRow = $(`
                    <div class="row no-gutters mb-2 edit-defect-row align-items-center shadow-sm bg-white p-1 rounded">
                        <div class="col-8 pr-1">
                            <select class="form-control form-control-sm edit-defect-select font-weight-bold" name="defect_types[]">
                                <option value="">-- Pilih Defect --</option>
                                <option value="Dimensi" selected>Dimensi</option>
                            </select>
                        </div>
                        <div class="col-3 pr-1">
                            <input type="number" class="form-control form-control-sm edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" value="1" min="1">
                        </div>
                        <div class="col-1 text-center">
                            <button type="button" class="btn btn-danger btn-xs px-2 remove-defect-btn" title="Hapus"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                `);
                $('#editDefectContainer').append(newRow);
            } else {
                const $sel = targetRow.find('.edit-defect-select');
                if ($sel.find('option[value="Dimensi"]').length === 0) {
                    $sel.append('<option value="Dimensi">Dimensi</option>');
                }
                $sel.val('Dimensi');
                targetRow.find('.edit-defect-qty').val(1);
            }
        } else if (!isDimInvalid && hasDimDefect) {
            $('#editDefectContainer .edit-defect-row').each(function () {
                const val = ($(this).find('.edit-defect-select').val() || '').trim().toLowerCase();
                const txt = ($(this).find('.edit-defect-select option:selected').text() || '').trim().toLowerCase();
                if (val.includes('dimens') || txt.includes('dimens')) {
                    if ($('#editDefectContainer .edit-defect-row').length > 1) {
                        $(this).remove();
                    } else {
                        $(this).find('.edit-defect-select').val('');
                        $(this).find('.edit-defect-qty').val('');
                    }
                }
            });
        }

        let totalNg = 0;
        $('#editDefectContainer .edit-defect-row').each(function () {
            const qty = parseInt($(this).find('.edit-defect-qty').val()) || 0;
            if ($(this).find('.edit-defect-select').val()) {
                totalNg += qty;
            }
        });

        const sampleSize = parseInt($('input[name="sampling_size_pcs"], input[name="sampling_qty"]').val()) || 0;
        const aql = AQL_TABLE_EDIT.getAqlLimits(sampleSize);
        const judgment = (isDimInvalid || totalNg >= aql.rej) ? 'NG' : 'OK';

        const $judgmentSelect = $('#judgmentSelect, #editJudgmentSelect');
        if ($judgmentSelect.length) {
            $judgmentSelect.val(judgment);
        }
    }

    function validateEditDimensions() {
        const $itemOpt = $('#editChecksheetForm #item_id option:selected');
        let stds = $itemOpt.data('dimension-standards');
        if (typeof stds === 'string') {
            try { stds = JSON.parse(stds); } catch (e) { stds = null; }
        }

        $('#editChecksheetForm .edit-dimension-input').each(function () {
            const $input = $(this);
            const name = $input.attr('name') || '';
            const matches = name.match(/\[(\d+)\]/g);
            if (!matches || matches.length === 0) return;
            const pt = matches[matches.length - 1].replace(/[\[\]]/g, '');

            let std = null;
            if (stds) {
                if (Array.isArray(stds)) {
                    std = stds.find(function (s, idx) {
                        return String(s.point ?? (idx + 1)) === String(pt);
                    }) || null;
                } else if (typeof stds === 'object') {
                    std = stds[pt] || stds[String(pt)] || null;
                }
            }

            const rawStr = ($input.val() || '').trim();
            const upper = rawStr.toUpperCase();
            const numVal = parseFloat(rawStr.replace(',', '.'));

            $input.removeClass('is-invalid is-valid text-danger text-success');

            if (rawStr === '' || rawStr === '-') {
                return;
            }
            if (upper === 'NG') {
                $input.addClass('is-invalid text-danger');
                return;
            }
            if (upper === 'OK') {
                $input.addClass('is-valid text-success');
                return;
            }

            if (std && !isNaN(numVal)) {
                let isInvalid = false;
                const eps = 0.00001;

                if (std.min !== null && std.min !== undefined && std.min !== '' && std.min !== '-') {
                    const minB = parseFloat(normalizeEditStd(std.min));
                    if (!isNaN(minB) && numVal < minB - eps) isInvalid = true;
                }
                if (!isInvalid && std.max !== null && std.max !== undefined && std.max !== '' && std.max !== '-') {
                    const maxB = parseFloat(normalizeEditStd(std.max));
                    if (!isNaN(maxB) && numVal > maxB + eps) isInvalid = true;
                }
                if (!isInvalid && std.size !== null && std.size !== undefined && std.size !== '' && std.tolerance !== null && std.tolerance !== undefined && std.tolerance !== '') {
                    const szStr = normalizeEditStd(std.size);
                    if (szStr && !szStr.startsWith('+') && !szStr.startsWith('-')) {
                        const base = parseFloat(szStr);
                        const tol = normalizeEditStd(std.tolerance);
                        if (!isNaN(base) && tol) {
                            let lb = base, ub = base;
                            if (tol.includes('/')) {
                                tol.split('/').forEach(function (p) {
                                    p = normalizeEditStd(p);
                                    const fv = parseFloat(p);
                                    if (p.startsWith('+') || fv > 0) ub = base + Math.abs(fv);
                                    else if (p.startsWith('-') || fv < 0) lb = base - Math.abs(fv);
                                });
                            } else if (tol.startsWith('+')) {
                                ub = base + parseFloat(tol.substring(1));
                            } else if (tol.startsWith('-')) {
                                lb = base + parseFloat(tol);
                            } else {
                                const tv = Math.abs(parseFloat(tol));
                                if (!isNaN(tv)) {
                                    lb = base - tv;
                                    ub = base + tv;
                                }
                            }
                            if (numVal < lb - eps || numVal > ub + eps) isInvalid = true;
                        }
                    }
                }
                if (!isInvalid && std.size !== null && std.size !== undefined && std.size !== '') {
                    const szStr = normalizeEditStd(std.size);
                    if (szStr && (szStr.startsWith('+') || szStr.startsWith('-'))) {
                        const op = szStr.charAt(0);
                        const b = parseFloat(szStr.substring(1));
                        if (!isNaN(b)) {
                            if (op === '+' && numVal < b - eps) isInvalid = true;
                            else if (op === '-' && numVal > b + eps) isInvalid = true;
                        }
                    }
                }

                if (isInvalid) {
                    $input.addClass('is-invalid text-danger');
                } else {
                    $input.addClass('is-valid text-success');
                }
            }
        });

        recalculateEditJudgment();
    }

    $(document).on('input change', '.edit-defect-qty, .edit-defect-select', function () {
        recalculateEditJudgment();
    });

    $(document).on('input change', '#editChecksheetForm .edit-dimension-input, #editChecksheetForm #item_id', function () {
        validateEditDimensions();
    });

    validateEditDimensions();

    // Hitung otomatis Qty Balance Sisa dari Qty Kedatangan Awal dikurang Total Check
    function updateEditQtyBalance() {
        const form = $('#editChecksheetForm');
        if (!form.length) return;

        const qtyDatangInput = form.find('input[name="qty_datang"]');
        const totalCheckInput = form.find('input[name="total_check"]');
        const qtyBalanceInput = form.find('input[name="qty_balance_sisa"]');

        if (qtyDatangInput.length && totalCheckInput.length && qtyBalanceInput.length) {
            const qtyDatang = parseFloat(qtyDatangInput.val()) || 0;
            const totalCheck = parseFloat(totalCheckInput.val()) || 0;

            if (qtyDatang > 0 || totalCheck > 0) {
                const remaining = Math.max(0, qtyDatang - totalCheck);
                qtyBalanceInput.val(remaining);
            }
        }
    }

    // Hitung otomatis AQL (Sampling Size) dan Qty Balance Sisa dari Total Check pada modal edit
    $(document).on('input change', 'input[name="total_check"], input[name="qty_datang"]', function() {
        if ($(this).attr('name') === 'total_check') {
            const totalCheck = parseFloat($(this).val()) || 0;
            if (totalCheck > 0) {
                const sampleSize = AQL_TABLE_EDIT.getSampleSize(totalCheck);
                $('input[name="sampling_qty"]').val(sampleSize);
            } else {
                $('input[name="sampling_qty"]').val(0);
            }
        }
        updateEditQtyBalance();
    });

    // Hitung otomatis AQL (Sampling Size) hanya dari input Komper/Karung (untuk Material)
    $('input[name="komper_karung_kg"]').on('input', function() {
        const totalKarung = parseFloat($(this).val()) || 0;
        const sampleSize = AQL_TABLE_EDIT.getSampleSize(totalKarung);
        $('input[name="sampling_size_karung_kg"]').val(sampleSize);
    });

    // Hitung otomatis AQL (Sampling Size) hanya dari input Komp/Jirigen (untuk Chemical)
    $('input[name="komper_jirigen_kg"]').on('input', function() {
        const totalJirigen = parseFloat($(this).val()) || 0;
        const sampleSize = AQL_TABLE_EDIT.getSampleSize(totalJirigen);
        $('input[name="sampling_size_jirigen_kg"]').val(sampleSize);
    });
});
