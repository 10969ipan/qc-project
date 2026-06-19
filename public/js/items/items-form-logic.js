(function () {
    'use strict';

    const ITEMS = window.__ITEMS__ || {};
    const ROUTES = ITEMS.routes || {};

    /* ============================================================
     *  UTILITY: Build a DataTransfer from a list of File objects
     *  so we can replace an input's FileList programmatically.
     * ============================================================ */
    function rebuildFileList(files) {
        const dt = new DataTransfer();
        files.forEach(f => dt.items.add(f));
        return dt.files;
    }

    /* ============================================================
     *  UTILITY: Format bytes to human-readable size
     * ============================================================ */
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    /* ============================================================
     *  CLIENT-SIDE PDF PREVIEW
     *  Attaches to a <input type="file" multiple accept=".pdf">
     *  Renders a list of selected files with:
     *    - file name & size
     *    - "Preview" button (opens PDF in the existing pdfModal)
     *    - "Hapus" button (removes the file from the input selection)
     *
     *  @param {string} inputId   - ID of the file input element
     *  @param {string} previewId - ID of the container div to render previews into
     *  @param {boolean} isMultiple - whether the input supports multiple files
     * ============================================================ */
    function initClientPdfPreview(inputId, previewId, isMultiple) {
        const input = document.getElementById(inputId);
        const previewContainer = document.getElementById(previewId);
        if (!input || !previewContainer) return;

        // Track selected File objects in a JS array so we can remove individually
        let selectedFiles = [];

        function renderPreviews() {
            previewContainer.innerHTML = '';
            if (selectedFiles.length === 0) return;

            const label = document.createElement('label');
            label.className = 'small font-weight-bold mb-1 d-block text-muted';
            label.textContent = isMultiple
                ? `File dipilih (${selectedFiles.length}):`
                : 'File dipilih:';
            previewContainer.appendChild(label);

            selectedFiles.forEach(function (file, idx) {
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center mb-1 p-2 border rounded bg-white shadow-sm';
                row.style.cssText = 'gap:6px; font-size:0.78rem;';

                // PDF icon
                const icon = document.createElement('i');
                icon.className = 'fas fa-file-pdf text-danger';
                icon.style.fontSize = '1.1rem';
                row.appendChild(icon);

                // File name + size
                const nameSpan = document.createElement('span');
                nameSpan.className = 'text-truncate flex-grow-1';
                nameSpan.style.maxWidth = '180px';
                nameSpan.title = file.name;
                nameSpan.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
                row.appendChild(nameSpan);

                // Preview button
                const previewBtn = document.createElement('button');
                previewBtn.type = 'button';
                previewBtn.className = 'btn btn-xs btn-outline-primary py-0 px-2';
                previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview';
                previewBtn.addEventListener('click', function () {
                    const objectUrl = URL.createObjectURL(file);
                    // Trigger the existing PDF modal viewer
                    // items-pdf-viewer.js listens for view-pdf-btn clicks that set data-src
                    // We trigger it manually by setting the src and dispatching the modal
                    const pdfViewer = document.getElementById('the-canvas');
                    if (pdfViewer) {
                        // Use the global renderPdf function from items-pdf-viewer.js
                        if (typeof window.renderPdfFromUrl === 'function') {
                            window.renderPdfFromUrl(objectUrl);
                        } else {
                            // Fallback: open in new tab
                            window.open(objectUrl, '_blank');
                            return;
                        }
                    }
                    $('#pdfModal').modal('show');
                    // Revoke after modal is hidden
                    $('#pdfModal').one('hidden.bs.modal', function () {
                        URL.revokeObjectURL(objectUrl);
                    });
                });
                row.appendChild(previewBtn);

                // Remove button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-xs btn-outline-danger py-0 px-2';
                removeBtn.innerHTML = '<i class="fas fa-times"></i> Hapus';
                removeBtn.addEventListener('click', function () {
                    selectedFiles.splice(idx, 1);
                    // Rebuild the input's file list
                    try {
                        input.files = rebuildFileList(selectedFiles);
                    } catch (e) {
                        // DataTransfer not supported – clear and re-attach to keep consistent
                        input.value = '';
                    }
                    renderPreviews();
                });
                row.appendChild(removeBtn);

                previewContainer.appendChild(row);
            });
        }

        input.addEventListener('change', function () {
            const newFiles = Array.from(this.files);
            if (isMultiple) {
                // Merge newly selected files with existing ones (avoid duplicates by name+size)
                newFiles.forEach(function (newFile) {
                    const isDuplicate = selectedFiles.some(
                        f => f.name === newFile.name && f.size === newFile.size
                    );
                    if (!isDuplicate) selectedFiles.push(newFile);
                });
            } else {
                selectedFiles = newFiles.slice(0, 1);
            }
            // Rebuild the input's file list to reflect merged selection
            try {
                input.files = rebuildFileList(selectedFiles);
            } catch (e) { /* ignore if browser doesn't support */ }
            renderPreviews();
        });

        // Also expose a reset function so the modal reset can clear it
        input._previewReset = function () {
            selectedFiles = [];
            renderPreviews();
        };
    }

    /* ============================================================
     *  DIMENSION ROW TEMPLATES
     * ============================================================ */
    const dimensionRowTemplate = () => `
        <tr>
            <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" readonly style="background:#f8f9fa; color:#495057;"></td>
            <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm"></td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;

    /* Renumber all Point/No inputs in a table body sequentially */
    function renumberDimensionRows(tableBodySelector) {
        $(tableBodySelector).find('tr').each(function (i) {
            $(this).find('input[name="dimension_points[]"]').val(i + 1);
        });
    }

    $('.add-dimension-row').on('click', function () {
        $('#modal-dimension-table tbody').append(dimensionRowTemplate());
        renumberDimensionRows('#modal-dimension-table tbody');
    });

    $(document).on('click', '.add-edit-dimension-row', function () {
        $('#edit-modal-dimension-table tbody').append(dimensionRowTemplate());
        renumberDimensionRows('#edit-modal-dimension-table tbody');
    });

    $(document).on('click', '.remove-dimension-row', function () {
        const tableBody = $(this).closest('tbody');
        if (tableBody.find('tr').length > 1) {
            $(this).closest('tr').remove();
            renumberDimensionRows(tableBody);
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

    /* ============================================================
     *  EDIT ITEM BUTTON — Load item data via AJAX
     * ============================================================ */
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

                // ---- Render existing Standard PDF files ----
                let filesHtml = '';
                if (item.file_paths && item.file_paths.length > 0) {
                    item.file_paths.forEach(function (path, index) {
                        let viewPdfUrl = ROUTES.viewPdf.replace('__ID__', item.id) + '/' + index + '?t=' + Date.now();
                        filesHtml += `
                            <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small" style="overflow:hidden;">
                                <i class="fas fa-file-pdf text-danger mr-1 flex-shrink-0"></i>
                                <span class="text-truncate mr-2 flex-grow-1" style="min-width:0;" title="${path.split('/').pop()}">${path.split('/').pop()}</span>
                                <button type="button" class="badge badge-info border-0 mr-1 view-pdf-btn flex-shrink-0" data-src="${viewPdfUrl}" data-toggle="modal" data-target="#pdfModal" style="cursor: pointer;">View</button>
                                <button type="button" class="badge badge-danger border-0 btn-delete-pdf flex-shrink-0" data-id="${item.id}" data-index="${index}" style="cursor: pointer;">Hapus</button>
                            </div>`;
                    });
                } else if (item.file_path) {
                    let viewPdfUrl = ROUTES.viewPdf.replace('__ID__', item.id) + '?t=' + Date.now();
                    filesHtml += `
                        <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small" style="overflow:hidden;">
                            <i class="fas fa-file-pdf text-danger mr-1 flex-shrink-0"></i>
                            <span class="text-truncate mr-2 flex-grow-1" style="min-width:0;" title="${item.file_path.split('/').pop()}">${item.file_path.split('/').pop()}</span>
                            <button type="button" class="badge badge-info border-0 mr-1 view-pdf-btn flex-shrink-0" data-src="${viewPdfUrl}" data-toggle="modal" data-target="#pdfModal" style="cursor: pointer;">View</button>
                            <button type="button" class="badge badge-danger border-0 btn-delete-pdf flex-shrink-0" data-id="${item.id}" data-index="0" style="cursor: pointer;">Hapus</button>
                        </div>`;
                }
                $('#edit_existing_files').html(
                    (item.file_paths || item.file_path)
                        ? '<label class="small font-weight-bold mb-1 d-block text-muted">File tersimpan:</label>' + filesHtml
                        : ''
                );

                // Reset new-file preview area and file input
                $('#edit_preview_new_files').html('');
                const editFilesInput = document.getElementById('edit_files_input');
                if (editFilesInput) {
                    editFilesInput.value = '';
                    if (editFilesInput._previewReset) editFilesInput._previewReset();
                }

                // ---- Render existing Similar Part PDF ----
                let similarFileHtml = '';
                if (item.similar_part_file_path) {
                    let viewSimilarPdfUrl = ROUTES.viewPdf.replace('__ID__', item.id) + '/similar?t=' + Date.now();
                    similarFileHtml = `
                        <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small" style="overflow:hidden;">
                            <i class="fas fa-file-alt text-info mr-1 flex-shrink-0"></i>
                            <span class="text-truncate mr-2 flex-grow-1" style="min-width:0;" title="${item.similar_part_file_path.split('/').pop()}">${item.similar_part_file_path.split('/').pop()}</span>
                            <button type="button" class="badge badge-info border-0 mr-1 view-pdf-btn flex-shrink-0" data-src="${viewSimilarPdfUrl}" data-toggle="modal" data-target="#pdfModal" style="cursor: pointer;">View</button>
                            <button type="button" class="badge badge-danger border-0 btn-delete-similar-pdf flex-shrink-0" data-id="${item.id}" style="cursor: pointer;">Hapus</button>
                        </div>`;
                }
                $('#edit_existing_similar_file').html(
                    similarFileHtml
                        ? '<label class="small font-weight-bold mb-1 d-block text-muted">Dimensi Part PDF tersimpan:</label>' + similarFileHtml
                        : ''
                );

                // Reset similar new-file preview
                $('#edit_preview_new_similar').html('');
                const editSimilarInput = document.getElementById('edit_similar_input');
                if (editSimilarInput) {
                    editSimilarInput.value = '';
                    if (editSimilarInput._previewReset) editSimilarInput._previewReset();
                }

                // ---- Dimension standards ----
                let dimHtml = '';
                if (item.dimension_standards && item.dimension_standards.length > 0) {
                    item.dimension_standards.forEach(function (dim, idx) {
                        dimHtml += `
                            <tr>
                                <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" value="${idx + 1}" readonly style="background:#f8f9fa; color:#495057;"></td>
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
                    dimHtml = dimensionRowTemplate();
                }
                $('#edit-modal-dimension-table tbody').html(dimHtml);
                renumberDimensionRows('#edit-modal-dimension-table tbody');

                $('#formEditItem').attr('action', ROUTES.update.replace(':id', id));
                toggleSctField($('#edit_category_id')[0]);
                $('#modalEditItem').modal('show');
                btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
            },
            error: function (xhr) {
                let message = 'Gagal mengambil data item.';
                if (xhr.status === 404) message = 'Item tidak ditemukan.';
                else if (xhr.status === 403) message = 'Anda tidak memiliki akses.';
                else if (xhr.status === 500) message = 'Kesalahan server.';
                Swal.fire('Error', message, 'error');
                btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
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

    /* ============================================================
     *  INITIALIZE CLIENT-SIDE PDF PREVIEWS
     *  Run after DOM ready. We use setTimeout to ensure the
     *  modal HTML is already in the DOM.
     * ============================================================ */
    $(document).ready(function () {
        // -- Tambah Item modal --
        initClientPdfPreview('tambah_files_input', 'tambah_preview_files', true);
        initClientPdfPreview('tambah_similar_input', 'tambah_preview_similar', false);

        // -- Edit Item modal (new files being selected) --
        initClientPdfPreview('edit_files_input', 'edit_preview_new_files', true);
        initClientPdfPreview('edit_similar_input', 'edit_preview_new_similar', false);

        // Reset Tambah form previews when modal is hidden
        $('#modalTambahItem').on('hidden.bs.modal', function () {
            const tfInput = document.getElementById('tambah_files_input');
            if (tfInput) { tfInput.value = ''; if (tfInput._previewReset) tfInput._previewReset(); }
            const tsInput = document.getElementById('tambah_similar_input');
            if (tsInput) { tsInput.value = ''; if (tsInput._previewReset) tsInput._previewReset(); }
        });

        // Reset Edit new-file previews when modal is hidden
        $('#modalEditItem').on('hidden.bs.modal', function () {
            const efInput = document.getElementById('edit_files_input');
            if (efInput) { efInput.value = ''; if (efInput._previewReset) efInput._previewReset(); }
            const esInput = document.getElementById('edit_similar_input');
            if (esInput) { esInput.value = ''; if (esInput._previewReset) esInput._previewReset(); }
        });
    });

})();
