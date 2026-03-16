/**
 * Incoming Checksheet Creation Logic
 * Used across Parts, Sub-Parts, Chemicals, Materials, and Exports
 */
document.addEventListener('DOMContentLoaded', function () {
    // Handle Item Selection to update Defect Dropdown
    $('#itemSelect').on('change', function () {
        const selected = $(this).find(':selected');
        const defects = selected.data('defects');
        updateDefectOptions(defects);
    });

    function updateDefectOptions(defects) {
        const defectSelects = $('.defect-select');
        defectSelects.each(function () {
            const currentVal = $(this).val();
            // Try to match "-- Pilih Defect --" or "-- Defect --"
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

    // Add Defect Row
    $('#addDefectBtn').on('click', function () {
        const firstRow = $('.defect-row').first().clone();
        firstRow.find('input').val('');
        firstRow.find('select').val('');
        $('#defectContainer').append(firstRow);
    });

    // Calculate Total NG and Auto-set Judgment
    $(document).on('input', '.defect-qty, .defect-select', function () {
        let totalNg = 0;
        $('.defect-row').each(function () {
            const qtyInput = $(this).find('.defect-qty');
            const select = $(this).find('.defect-select');
            const qty = parseInt(qtyInput.val()) || 0;
            if (select.val()) {
                totalNg += qty;
            }
        });
        $('#totalNgInput').val(totalNg);
        $('#judgmentSelect').val(totalNg > 0 ? 'NG' : 'OK');
    });

    // AJAX Form Submission
    $('#checksheetForm').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const saveBtn = form.find('button[type="submit"]');
        const originalHtml = saveBtn.html();

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
        
        // Specific for Select2 if used
        if ($.fn.select2) {
            form.find('.select2').trigger('change');
        }
    }
});
