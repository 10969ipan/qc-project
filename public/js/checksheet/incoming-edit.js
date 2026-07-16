/**
 * Logika Edit Checksheet Incoming
 * Menangani baris defect dinamis di modal edit
 */
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
});
