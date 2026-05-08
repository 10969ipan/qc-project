(function () {
    'use strict';

    const ITEMS = window.__ITEMS__ || {};
    const ROUTES = ITEMS.routes || {};

    const dimensionRowTemplate = (isEdit = false) => `
        <tr>
            <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" placeholder="Contoh: 1, A"></td>
            <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm" placeholder="10.5"></td>
            <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm" placeholder="9.9"></td>
            <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm" placeholder="10.1"></td>
            <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm" placeholder="0.1"></td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;

    $('.add-dimension-row').on('click', function () {
        $('#modal-dimension-table tbody').append(dimensionRowTemplate());
    });

    $(document).on('click', '.add-edit-dimension-row', function () {
        $('#edit-modal-dimension-table tbody').append(dimensionRowTemplate(true));
    });

    $(document).on('click', '.remove-dimension-row', function () {
        const tableBody = $(this).closest('tbody');
        if (tableBody.find('tr').length > 1) {
            $(this).closest('tr').remove();
        }
    });

    $('#modal_plant_select').on('change', function () {
        const selectedPlantUuid = $(this).find(':selected').data('uuid');
        const categorySelect = $('#modal_category_select');

        categorySelect.val('');
        categorySelect.find('option').each(function () {
            const optionPlant = $(this).data('plant');
            if (!optionPlant || optionPlant == selectedPlantUuid) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    const toggleSctField = (selectElement) => {
        const selectedText = $(selectElement).find('option:selected').text().toUpperCase();
        const modal = $(selectElement).closest('.modal');
        const wrapper = modal.find('.sct-field-wrapper');
        const input = wrapper.find('input[name="standard_cycle_time"]');

        if (selectedText.includes('PLATING')) {
            wrapper.show();
        } else {
            wrapper.hide();
            input.val(''); // Clear if not plating
        }
    };

    $('#modal_category_select, #edit_category_id').on('change', function () {
        toggleSctField(this);
    });

    $('.btn-edit-item').on('click', function () {
        const id = $(this).data('id');
        const btn = $(this);

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: ROUTES.edit.replace(':id', id),
            type: 'GET',
            success: function (response) {
                const item = response.item;
                $('#edit_name').val(item.name);
                $('#edit_category_id').val(item.category_id);
                $('#edit_customer').val(item.customer);
                $('#edit_part_number').val(item.part_number);
                $('#edit_sap_code').val(item.sap_code);
                $('#edit_cavity').val(item.cavity || 1);
                $('#edit_weight_standard').val(item.weight_standard);
                $('#edit_standard_cycle_time').val(item.standard_cycle_time);
                $('#edit_defects').val(response.defects_text);
                $('#edit_plant').val(response.plant_code);
                $('#edit_item_id').val(item.id);

                let filesHtml = '';
                if (item.file_paths && item.file_paths.length > 0) {
                    item.file_paths.forEach(function (path, index) {
                        let viewPdfUrl = ROUTES.viewPdf.replace('__ID__', item.id) + '/' + index + '?t=' + Date.now();
                        filesHtml += `
                            <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small">
                                <span class="text-truncate mr-2" style="max-width: 150px;">${path.split('/').pop()}</span>
                                <button type="button" class="badge badge-info border-0 mr-1 view-pdf-btn" data-src="${viewPdfUrl}" data-toggle="modal" data-target="#pdfModal" style="cursor: pointer;">View</button>
                                <button type="button" class="badge badge-danger border-0 btn-delete-pdf" data-id="${item.id}" data-index="${index}" style="cursor: pointer;">Hapus</button>
                            </div>`;
                    });
                } else if (item.file_path) {
                    let viewPdfUrl = ROUTES.viewPdf.replace('__ID__', item.id) + '?t=' + Date.now();
                    filesHtml += `
                        <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small">
                            <span class="text-truncate mr-2" style="max-width: 150px;">${item.file_path.split('/').pop()}</span>
                            <button type="button" class="badge badge-info border-0 mr-1 view-pdf-btn" data-src="${viewPdfUrl}" data-toggle="modal" data-target="#pdfModal" style="cursor: pointer;">View</button>
                            <button type="button" class="badge badge-danger border-0 btn-delete-pdf" data-id="${item.id}" data-index="0" style="cursor: pointer;">Hapus</button>
                        </div>`;
                }
                $('#edit_existing_files').html((item.file_paths || item.file_path) ? '<label class="small font-weight-bold mb-1">Standard PDFs:</label>' + filesHtml : '');

                let similarFileHtml = '';
                if (item.similar_part_file_path) {
                    let viewSimilarPdfUrl = ROUTES.viewPdf.replace('__ID__', item.id) + '/similar?t=' + Date.now();
                    similarFileHtml = `
                        <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small text-primary font-weight-bold">
                            <span class="text-truncate mr-2" style="max-width: 150px;">${item.similar_part_file_path.split('/').pop()}</span>
                            <button type="button" class="badge badge-info border-0 mr-1 view-pdf-btn" data-src="${viewSimilarPdfUrl}" data-toggle="modal" data-target="#pdfModal" style="cursor: pointer;">View</button>
                            <button type="button" class="badge badge-danger border-0 btn-delete-similar-pdf" data-id="${item.id}" style="cursor: pointer;">Hapus</button>
                        </div>`;
                }
                $('#edit_existing_similar_file').html(similarFileHtml ? '<label class="small font-weight-bold mb-1">Dimensi Part PDF Terdaftar:</label>' + similarFileHtml : '');

                let dimHtml = '';
                if (item.dimension_standards && item.dimension_standards.length > 0) {
                    item.dimension_standards.forEach(function (dim) {
                        dimHtml += `
                            <tr>
                                <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" value="${dim.point || ''}"></td>
                                <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm" value="${dim.size || ''}"></td>
                                <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm" value="${dim.min || ''}"></td>
                                <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm" value="${dim.max || ''}"></td>
                                <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm" value="${dim.tolerance || ''}"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                } else {
                    dimHtml = dimensionRowTemplate(true);
                }
                $('#edit-modal-dimension-table tbody').html(dimHtml);

                $('#formEditItem').attr('action', ROUTES.update.replace(':id', id));
                toggleSctField($('#edit_category_id')[0]);
                $('#modalEditItem').modal('show');
                btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
            },
            error: function (xhr) {
                let message = 'Gagal mengambil data item.';
                if (xhr.status === 404) message = 'Item tidak ditemukan.';
                else if (xhr.status === 403) message = 'Anda tidak memiliki akses.';
                else if (xhr.status === 500) message = 'Kesalahan server.';
                Swal.fire('Error', message, 'error');
                btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
            }
        });
    });

    // Client-side validation before submission
    const validateItemForm = (form) => {
        let isValid = true;
        let firstEmptyField = null;
        const errors = [];

        // Reset previous invalid states
        $(form).find('.is-invalid').removeClass('is-invalid');

        // 1. Check Name
        const nameInput = $(form).find('input[name="name"]');
        if (!nameInput.val() || !nameInput.val().trim()) {
            isValid = false;
            nameInput.addClass('is-invalid');
            errors.push('Nama Item');
            if (!firstEmptyField) firstEmptyField = nameInput;
        }

        // 2. Check Category
        const categorySelect = $(form).find('select[name="category_id"]');
        if (!categorySelect.val()) {
            isValid = false;
            categorySelect.addClass('is-invalid');
            errors.push('Kategori');
            if (!firstEmptyField) firstEmptyField = categorySelect;
        }

        // 3. Check Plant (only if it's a select prompted to user)
        const plantSelect = $(form).find('select[name="plant"]');
        if (plantSelect.length > 0 && plantSelect.is(':visible') && !plantSelect.val()) {
            isValid = false;
            plantSelect.addClass('is-invalid');
            errors.push('Plant');
            if (!firstEmptyField) firstEmptyField = plantSelect;
        }

        // 4. Check Files (only for Create form)
        const isCreate = $(form).attr('action') && $(form).attr('action').includes('/store');
        const filesInput = $(form).find('input[name="files[]"]');
        // Optional check for files only if they are truly required by the model/business logic
        if (isCreate && filesInput.length && filesInput[0].hasAttribute('required') && filesInput[0].files.length === 0) {
            isValid = false;
            filesInput.addClass('is-invalid');
            errors.push('Upload PDF Standard');
            if (!firstEmptyField) firstEmptyField = filesInput;
        }

        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: `Mohon isi field yang wajib: ${errors.join(', ')}`,
                confirmButtonColor: '#3085d6'
            }).then(() => {
                if (firstEmptyField) firstEmptyField.focus();
            });
        }

        return isValid;
    };

    // Attach validation to forms
    $(document).on('submit', '#modalTambahItem form, #formEditItem', function (e) {
        if (!validateItemForm(this)) {
            e.preventDefault();
            return false;
        }
    });

})();
