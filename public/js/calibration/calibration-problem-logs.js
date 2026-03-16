/**
 * Calibration Problem Logs Management
 */

$(document).ready(function () {
    // Preview Evidence
    $(document).on('click', '.btn-preview-evidence', function (e) {
        e.preventDefault();
        const src = $(this).data('src');
        const title = $(this).data('title');

        $('#modalPreviewEvidenceLabel').html('<i class="fas fa-eye mr-2"></i> ' + title);
        $('#downloadLink').attr('href', src);

        $('#previewLoading').show();
        $('#previewImage').hide();
        $('#previewPdf').hide();

        $('#modalPreviewEvidence').modal('show');

        const extension = src.split('.').pop().toLowerCase();

        setTimeout(function () {
            $('#previewLoading').hide();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                $('#previewImage').attr('src', src).show();
            } else if (extension === 'pdf') {
                $('#previewPdf').attr('src', src).show();
            } else {
                window.open(src, '_blank');
                $('#modalPreviewEvidence').modal('hide');
            }
        }, 500);
    });

    if ($.fn.DataTable) {
        $('#problemLogsTable').DataTable({
            dom: "<'row px-2 align-items-center'<'col-sm-12 col-md-3'l><'col-sm-12 col-md-9 d-flex justify-content-end align-items-center'f<\"#customFiltersPlaceholder\">>>" +
                "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
            },
            drawCallback: function() {
                $('.dataTables_filter').addClass('m-0');
                $('.dataTables_filter label').addClass('m-0 d-flex align-items-center');
            },
            initComplete: function() {
                $('#customFilters').appendTo('#customFiltersPlaceholder').css('margin-left', '10px');
            }
        });
    }

    $(document).on('click', '.btn-judgment', function () {
        const id = $(this).data('id');
        const toolName = $(this).data('tool');
        const problemType = $(this).data('problem');

        $('#judgment_tool_name').text(toolName);
        $('#judgment_problem_type').text(problemType);
        
        var judgmentUrl = window.__CALIBRATION_PROBLEM_LOGS__.routes.judgment.replace(':id', id);
        $('#formJudgment').attr('action', judgmentUrl);

        $('input[name="judgment_status"]').prop('checked', false);
        $('textarea[name="judgment_remarks"]').val('');
        $('#judgment_warning').hide();
    });

    $('input[name="judgment_status"]').on('change', function () {
        const status = $(this).val();
        const warningDiv = $('#judgment_warning');

        if (status === 'OK') {
            warningDiv.html('<i class="fas fa-check-circle text-success mr-1"></i> Alat akan diaktifkan kembali dan jadwal kalibrasi akan dipulihkan.').show();
            warningDiv.removeClass('border-danger text-danger').addClass('border-success text-success');
        } else {
            warningDiv.html('<i class="fas fa-exclamation-triangle text-danger mr-1"></i> Alat dan seluruh jadwalnya akan dihapus secara permanen.').show();
            warningDiv.removeClass('border-success text-success').addClass('border-danger text-danger');
        }
    });

    // Edit Log
    $(document).on('click', '.btn-edit-log', function () {
        const id = $(this).data('id');
        const toolName = $(this).data('tool');
        const type = $(this).data('type');
        const date = $(this).data('date');
        const desc = $(this).data('desc');
        const action = $(this).data('action');
        const evidence = $(this).data('evidence');

        $('#edit_tool_name').text(toolName);
        $('#edit_problem_type').val(type);
        $('#edit_reported_date').val(date);
        $('#edit_description').val(desc);
        $('#edit_action_taken').val(action);

        if (evidence) {
            $('#current_evidence_link a').attr('href', evidence);
            $('#current_evidence_link').show();
        } else {
            $('#current_evidence_link').hide();
        }

        var updateUrl = window.__CALIBRATION_PROBLEM_LOGS__.routes.update.replace(':id', id);
        $('#formEditProblem').attr('action', updateUrl);
        $('#modalEditProblem').modal('show');
    });

    // Delete Log
    $(document).on('click', '.btn-delete-log', function () {
        const id = $(this).data('id');
        const plantCode = window.__CALIBRATION_PROBLEM_LOGS__.plantCode;
        const year = window.__CALIBRATION_PROBLEM_LOGS__.year;
        const csrf = window.__CALIBRATION_PROBLEM_LOGS__.csrf;
        const deleteUrl = window.__CALIBRATION_PROBLEM_LOGS__.routes.delete.replace(':id', id);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data laporan masalah ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrf;

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                const plantInput = document.createElement('input');
                plantInput.type = 'hidden';
                plantInput.name = 'plant';
                plantInput.value = plantCode;

                const yearInput = document.createElement('input');
                yearInput.type = 'hidden';
                yearInput.name = 'year';
                yearInput.value = year;

                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                form.appendChild(plantInput);
                form.appendChild(yearInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
