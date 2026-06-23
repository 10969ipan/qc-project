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
            dom: "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            searching: true,
            lengthChange: false,
            language: {
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
            initComplete: function(settings, json) {
                // ponytail: Prevent FOUC
                $('#tableLoader').hide();
                $('#tableContainer').fadeIn('fast', function() {
                    table.columns.adjust();
                });
            },
            drawCallback: function(settings) {
                // ponytail: Highlight search keywords safely using TreeWalker
                var api = this.api();
                var tbody = api.table().body();
                
                // Unmark previous
                $(tbody).find('mark.hlt').each(function() {
                    $(this).replaceWith(this.childNodes);
                });
                tbody.normalize();

                var searchStr = api.search();
                if (!searchStr) return;

                var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
                if (keywords.length === 0) return;

                keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
                var regex = new RegExp("(" + keywords.join('|') + ")", "gi");

                api.rows({ page: 'current' }).nodes().each(function(row) {
                    $(row).find('td:not(:last-child)').each(function() {
                        var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT, null, false);
                        var nodes = [];
                        while (walker.nextNode()) {
                            nodes.push(walker.currentNode);
                        }
                        nodes.forEach(function(node) {
                            var text = node.nodeValue;
                            if (text.trim() && regex.test(text)) {
                                var span = document.createElement('span');
                                span.innerHTML = text.replace(regex, "<mark class='hlt' style='background-color: #fffa90; color: #000000; font-weight: bold; padding: 0 2px; border-radius: 2px;'>$1</mark>");
                                var frag = document.createDocumentFragment();
                                while (span.firstChild) {
                                    frag.appendChild(span.firstChild);
                                }
                                node.parentNode.replaceChild(frag, node);
                            }
                        });
                    });
                });
            }
        });

        // ponytail: Instant smart search
        $('input[name="search"]').on('keypress', function (e) {
            if (e.which == 13) e.preventDefault();
        });

        $('input[name="search"]').on('keyup input', function () {
            table.search($(this).val()).draw();
        });

        var initialSearch = $('input[name="search"]').val();
        if (initialSearch) {
            table.search(initialSearch).draw();
        }
    }

    // PDF Modal
    $('#pdfModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var url = button.data('url');
        var title = button.data('title');

        var modal = $(this);
        modal.find('#pdfModalLabel').text(title);
        modal.find('#pdfFrame').attr('src', url);
        
        // Append download parameter
        var downloadUrl = url + (url.indexOf('?') !== -1 ? '&' : '?') + 'download=1';
        modal.find('#downloadPdf').attr('href', downloadUrl);
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


    // Keep jQuery handler as fallback for any remaining class-based triggers
    $(document).on('click', '.btn-edit-tool', function () {
        var id = $(this).data('id');
        window.openEditToolModal(id, this);
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
        var toolId = $(this).data('id');
        var toolName = $(this).data('name');

        $('#problem_tool_id').val(toolId);
        $('#problem_tool_name').val(toolName);

        $('#problem_type').val('');
        $('#action_taken_wrapper').hide();
        $('#action_taken').prop('required', false);
        $('#action_taken').val('');
        $('#rusak_info').hide();
        
        $('#modalReportProblem').modal('show');
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
