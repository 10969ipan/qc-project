/**
 * Incoming Checksheet Edit Logic
 * Handles the dynamic defect rows in edit modals
 */
$(document).ready(function () {
    // Delegation for dynamic content loaded via AJAX
    $(document).on('click', '#editAddDefectBtn', function () {
        const container = $('#editDefectContainer');
        const firstRow = container.find('.edit-defect-row').first().clone();
        
        firstRow.find('input').val('');
        firstRow.find('select').val('');
        
        // Ensure remove button exists
        if (firstRow.find('.remove-defect-btn').length === 0) {
            firstRow.append('<div class="input-group-append"><button type="button" class="btn btn-danger remove-defect-btn"><i class="fas fa-trash"></i></button></div>');
        }
        
        container.append(firstRow);
    });

    $(document).on('click', '.remove-defect-btn', function () {
        const rows = $('.edit-defect-row');
        if (rows.length > 1) {
            $(this).closest('.edit-defect-row').remove();
        } else {
            // Just clear the first row if it's the only one
            const row = $(this).closest('.edit-defect-row');
            row.find('input').val('');
            row.find('select').val('');
        }
    });

    // Optional: Auto-judgment logic for edit form if needed
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
