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

    // Opsional: Logika judgment otomatis untuk formulir edit jika diperlukan
    $(document).on('input', '.edit-defect-qty, .edit-defect-select', function() {
        let totalNg = 0;
        $('.edit-defect-row').each(function() {
            const qty = parseInt($(this).find('.edit-defect-qty').val()) || 0;
            if ($(this).find('.edit-defect-select').val()) {
                totalNg += qty;
            }
        });
        
        const judgmentSelect = $('#editJudgmentSelect');
        if (judgmentSelect.length) {
            judgmentSelect.val(totalNg > 0 ? 'NG' : 'OK');
        }
    });

    // Hitung otomatis AQL (Sampling Size) dari Total Check pada modal edit
    $(document).on('input change', 'input[name="total_check"]', function() {
        const totalCheck = parseFloat($(this).val()) || 0;
        if (totalCheck > 0) {
            const sampleSize = AQL_TABLE_EDIT.getSampleSize(totalCheck);
            $('input[name="sampling_qty"]').val(sampleSize);
        } else {
            $('input[name="sampling_qty"]').val(0);
        }
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
