/**
 * Calibration Tools Management
 */

$(document).ready(function () {
    // Modal Add Schedule rows
    $('#modal-add-schedule-btn').click(function () {
        var html = `
            <div class="input-group input-group-sm mb-2">
                <input type="date" name="schedule_planning[]" class="form-control">
                <div class="input-group-append">
                    <button class="btn btn-danger modal-remove-schedule" type="button">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>`;
        $('#modal-schedule-container').append(html);
    });

    $(document).on('click', '.modal-remove-schedule', function () {
        $(this).closest('.input-group').remove();
    });

    // --- Modal Verifikasi logic ---
    $('.btn-verifikasi').on('click', function () {
        var toolId = $(this).data('tool-id');
        $('#modal_verif_tool_select').val(toolId).trigger('change');
    });

    $('#modal_verif_tool_select').on('change', function () {
        var selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#modal_verif_name_alat').val(selected.data('name'));
            $('#modal_verif_serial_number').val(selected.data('serial'));
            $('#modal_verif_rentang_ukur').val(selected.data('range'));
            $('#modal_verif_resolusi').val(selected.data('resolusi'));
            $('#modal_verif_frekuensi_kalibrasi').val(selected.data('frekuensi'));

            modalVerifUpdateNextCalibrationDate();
        }
    });

    $('#modal_verif_tanggal_verifikasi').on('change', function () {
        modalVerifUpdateNextCalibrationDate();
    });

    function modalVerifUpdateNextCalibrationDate() {
        var selected = $('#modal_verif_tool_select').find('option:selected');
        var verifDate = $('#modal_verif_tanggal_verifikasi').val();

        if (!selected.val() || !selected.data('schedules')) return;

        var schedules = selected.data('schedules');
        if (typeof schedules === 'string') {
            schedules = JSON.parse(schedules);
        }

        if (schedules.length > 0) {
            schedules.sort();
            var referenceDate = verifDate || new Date().toISOString().split('T')[0];
            var nextDate = schedules.find(date => {
                var d = typeof date === 'string' ? date.substring(0, 10) : date;
                return d > referenceDate;
            });
            if (nextDate) {
                $('#modal_verif_next_kalibrasi').val(nextDate.substring(0, 10));
            }
        }
    }

    $('#modal-verif-add-row').on('click', function () {
        var newRow = `
            <tr>
                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm no-autoupper"></td>
                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm no-autoupper"></td>
                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm no-autoupper"></td>
                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm no-autoupper bg-light" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger modal-verif-remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#modal-verif-verification-body').append(newRow);
        modalVerifUpdateRemoveButtons();
    });

    $(document).on('click', '.modal-verif-remove-row', function () {
        $(this).closest('tr').remove();
        modalVerifUpdateRemoveButtons();
    });

    function modalVerifUpdateRemoveButtons() {
        var rowCount = $('#modal-verif-verification-body tr').length;
        if (rowCount <= 1) {
            $('.modal-verif-remove-row').prop('disabled', true);
        } else {
            $('.modal-verif-remove-row').prop('disabled', false);
        }
    }

    // Initialize DataTable if it exists
    if ($.fn.DataTable) {
        var table = $('#dataTable').DataTable({
            dom: "<'row px-2 mb-2 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-end align-items-center'<'year-filter-container mr-3'>f>>" +
                "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            initComplete: function() {
                var selectedYear = window.__CALIBRATION_TOOLS__.year;
                var availableYears = window.__CALIBRATION_TOOLS__.availableYears;
                var plantCode = window.__CALIBRATION_TOOLS__.plantCode;
                var indexRoute = window.__CALIBRATION_TOOLS__.routes.index;

                var yearHtml = '<div class="d-flex align-items-center mr-3">' +
                    '<label class="mb-0 mr-2" style="font-weight: normal; color: #858796;">Tahun:</label>' +
                    '<select id="customYearFilter" class="form-control form-control-sm" style="width: 85px; border-radius: 0.35rem;">';
                
                var allSelected = (selectedYear == 'all') ? 'selected' : '';
                yearHtml += '<option value="all" ' + allSelected + '>All</option>';

                availableYears.forEach(function(y) {
                    var selected = (y == selectedYear && selectedYear != 'all') ? 'selected' : '';
                    yearHtml += '<option value="' + y + '" ' + selected + '>' + y + '</option>';
                });
                
                yearHtml += '</select></div>';
                
                $('.year-filter-container').html(yearHtml);
                
                $('#customYearFilter').on('change', function() {
                    var val = $(this).val();
                    window.location.href = indexRoute + "?plant=" + plantCode + "&year=" + val;
                });
            }
        });
    }

    // PDF Modal
    $('#pdfModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var url = button.data('url');
        var title = button.data('title');

        var modal = $(this);
        modal.find('#pdfModalLabel').text(title);
        modal.find('#pdfFrame').attr('src', url);
        modal.find('#downloadPdf').attr('href', url);
    });

    $('#pdfModal').on('hidden.bs.modal', function () {
        $(this).find('#pdfFrame').attr('src', '');
    });

    // PR Input Change
    $(document).on('change', '.pr-input', function () {
        var input = $(this);
        var toolId = input.data('tool-id');
        var prNumber = input.val();

        $.ajax({
            url: window.__CALIBRATION_TOOLS__.routes.updatePr,
            method: 'POST',
            data: {
                _token: window.__CALIBRATION_TOOLS__.csrf,
                tool_id: toolId,
                pr_number: prNumber
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });

                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memperbarui PR.'
                });
            }
        });
    });

    // Reset PR Click
    $(document).on('click', '.reset-pr', function () {
        var button = $(this);
        var toolId = button.data('tool-id');

        Swal.fire({
            title: 'Reset PR?',
            text: "Nomor dan tanggal PR akan dihapus.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: window.__CALIBRATION_TOOLS__.routes.updatePr,
                    method: 'POST',
                    data: {
                        _token: window.__CALIBRATION_TOOLS__.csrf,
                        tool_id: toolId,
                        pr_number: ""
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'PR telah direset.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    }
                });
            }
        });
    });

    // Edit Tool Logic
    $('.btn-edit-tool').on('click', function () {
        var id = $(this).data('id');
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        var editUrl = window.__CALIBRATION_TOOLS__.routes.edit.replace(':id', id);

        $.ajax({
            url: editUrl,
            type: 'GET',
            data: { plant: window.__CALIBRATION_TOOLS__.plantCode },
            success: function (response) {
                var tool = response.tool;
                $('#edit_bagian').val(tool.bagian);
                $('#edit_name_alat').val(tool.name_alat);
                $('#edit_merk').val(tool.merk);
                $('#edit_serial_number').val(tool.serial_number);
                $('#edit_range').val(tool.range);
                $('#edit_resolusi').val(tool.resolusi);
                $('#edit_tanggal_beli').val(tool.tanggal_beli_formatted ? tool.tanggal_beli_formatted : '');
                $('#edit_frekuensi_kalibrasi').val(tool.frekuensi_kalibrasi);
                $('#edit_jenis_kalibrasi').val(tool.jenis_kalibrasi);
                $('#edit_riwayat_kalibrasi').val(tool.riwayat_kalibrasi);

                if (tool.certification_path) {
                    $('#edit_existing_cert').html(`<a href="/storage/${tool.certification_path}" target="_blank" class="badge badge-info"><i class="fas fa-file-pdf mr-1"></i> Lihat Sertifikat</a>`);
                } else {
                    $('#edit_existing_cert').html('');
                }

                var schHtml = '';
                if (tool.schedules && tool.schedules.length > 0) {
                    tool.schedules.forEach(function (sch) {
                        schHtml += `
                            <tr>
                                <td>
                                    <input type="hidden" name="schedule_ids[]" value="${sch.id}">
                                    <input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${sch.schedule_date_formatted}">
                                </td>
                                <td>
                                    <input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" value="${sch.pr_number || ''}" placeholder="PR Number...">
                                </td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                            </tr>`;
                    });
                } else if (tool.schedule_planning) {
                    schHtml = `
                        <tr>
                            <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${tool.schedule_planning.substring(0, 10)}"></td>
                            <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" placeholder="PR Number..."></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                } else {
                    schHtml = `
                        <tr>
                            <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm"></td>
                            <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" placeholder="PR Number..."></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                }
                $('#edit-schedule-table tbody').html(schHtml);

                var updateUrl = window.__CALIBRATION_TOOLS__.routes.update.replace(':id', id);
                $('#formEditAlat').attr('action', updateUrl);

                $('#modalEditAlat').modal('show');
                btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
            },
            error: function (xhr) {
                var errorMsg = 'Gagal mengambil data alat.';
                if (xhr.status === 404) errorMsg += ' (Error 404: Alat tidak ditemukan)';
                else if (xhr.status === 403) errorMsg += ' (Error 403: Anda tidak memiliki akses)';
                else if (xhr.status === 500) errorMsg += ' (Error 500: Terjadi kesalahan di server)';

                alert(errorMsg);
                btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
            }
        });
    });

    $(document).on('click', '.add-edit-schedule-row', function () {
        var newRow = `
            <tr>
                <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" placeholder="PR Number..."></td>
                <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
            </tr>`;
        $('#edit-schedule-table tbody').append(newRow);
    });

    $(document).on('click', '.remove-schedule-row', function () {
        $(this).closest('tr').remove();
    });

    // Report Problem Logic
    $(document).on('click', '.btn-report-problem', function() {
        var toolId = $(this).data('tool-id');
        var toolName = $(this).data('tool-name');

        $('#problem_tool_id').val(toolId);
        $('#problem_tool_name').val(toolName);

        $('#problem_type').val('');
        $('#action_taken_wrapper').hide();
        $('#action_taken').prop('required', false);
        $('#action_taken').val('');
        $('#rusak_info').hide();
    });

    $('#problem_type').on('change', function() {
        var type = $(this).val();
        if (type === 'ERROR' || type === 'RUSAK') {
            $('#action_taken_wrapper').show();
            $('#action_taken').prop('required', true);

            if (type === 'RUSAK') {
                $('#rusak_info').show();
            } else {
                $('#rusak_info').hide();
            }
        } else {
            $('#action_taken_wrapper').hide();
            $('#action_taken').prop('required', false);
        }
    });

    // Auto-Calc for verification modal (Event delegation)
    document.addEventListener('input', function(e) {
        var input = e.target;
        if (!input || input.tagName !== 'INPUT') return;
        var td = input.closest('td');
        if (!td) return;
        var tr = td.closest('tr');
        if (!tr) return;
        var tbody = tr.closest('tbody');
        if (!tbody) return;
        var tbodyId = tbody.getAttribute('id');
        if (tbodyId !== 'modal-verif-verification-body') return;
        var cells = Array.from(tr.querySelectorAll('td'));
        var cellIndex = cells.indexOf(td);
        if (cellIndex !== 1 && cellIndex !== 2) return;
        var koreksiInput = cells[1] ? cells[1].querySelector('input') : null;
        var ketidakpastianInput = cells[2] ? cells[2].querySelector('input') : null;
        var hasilInput = cells[3] ? cells[3].querySelector('input') : null;
        if (!koreksiInput || !ketidakpastianInput || !hasilInput) return;
        var kv = (koreksiInput.value || '').trim();
        var kpv = (ketidakpastianInput.value || '').trim();
        if (kv === '' && kpv === '') {
            hasilInput.value = '';
        } else {
            hasilInput.value = parseFloat(((parseFloat(kv) || 0) + (parseFloat(kpv) || 0)).toFixed(6));
        }
    });
});

function confirmDeleteTool(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Seluruh riwayat verifikasi alat ini juga akan terhapus dan tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-tool-form-' + id).submit();
        }
    });
}
