/**
 * Calibration Verifications Management
 */

$(document).ready(function () {
    // Initialize DataTable
    if ($.fn.DataTable && $('#dataTable').length) {
        try {
            $('#dataTable').DataTable({
                dom: "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                    "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Tidak ada data hasil verifikasi",
                    emptyTable: "Tidak ada data hasil verifikasi",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                initComplete: function(settings, json) {
                    $('#tableLoader').hide();
                    $('#tableContainer').fadeIn(300);
                }
            });
        } catch (e) {
            console.error('DataTables init error:', e);
            $('#tableLoader').hide();
            $('#tableContainer').show();
        }
    }

    // Safety fallback to hide loader and show container if loader is still visible
    setTimeout(function() {
        if ($('#tableLoader').is(':visible')) {
            $('#tableLoader').hide();
            $('#tableContainer').fadeIn(300);
        }
    }, 400);

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

    // --- Modal Verifikasi Baru Logic ---
    $(document).on('change', '.custom-file-input', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    $('#modal_tool_select').on('change', function () {
        var selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#modal_name_alat').val(selected.data('name'));
            $('#modal_serial_number').val(selected.data('serial'));
            $('#modal_rentang_ukur').val(selected.data('range'));
            $('#modal_resolusi').val(selected.data('resolusi'));
            $('#modal_frekuensi_kalibrasi').val(selected.data('frekuensi'));
            
            // Riwayat Kalibrasi: Increment by 1 for new verification
            var currentRiwayat = selected.data('riwayat') || '0';
            var currentCount = parseInt(currentRiwayat.replace(/[^0-9]/g, '')) || 0;
            $('#modal_riwayat_kalibrasi').val((currentCount + 1) + ' Kali');

            $('input[name="merk"]').val('');
            $('input[name="std_toleransi"]').val('');
            $('input[name="acuan_toleransi"]').val('');

            modalUpdateNextCalibrationDate();
        }
    });

    $('#modal_tanggal_verifikasi').on('change', function () {
        modalUpdateNextCalibrationDate();
    });

    function modalUpdateNextCalibrationDate() {
        var selected = $('#modal_tool_select').find('option:selected');
        var verifDate = $('#modal_tanggal_verifikasi').val();

        if (!selected.val() || !selected.data('schedules')) return;

        var schedules = selected.data('schedules');
        if (typeof schedules === 'string') {
            schedules = JSON.parse(schedules);
        }

        if (schedules.length > 0) {
            schedules.sort();
            var referenceDate = verifDate || new Date().toISOString().split('T')[0];
            var nextDate = schedules.find(date => date > referenceDate);
            if (nextDate) {
                $('#modal_next_kalIBRASI').val(nextDate);
            }
        }
    }

    // Modal Add Row
    $('#modal-add-row').on('click', function () {
        var newRow = `
            <tr>
                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm border-0 shadow-sm mx-auto" style="width: 80%;"></td>
                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm border-0 shadow-sm mx-auto calc-input" style="width: 80%;"></td>
                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm border-0 shadow-sm mx-auto calc-input" style="width: 80%;"></td>
                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm border-0 shadow-sm mx-auto" style="width: 80%;" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm text-danger modal-remove-row" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#modal-verification-body').append(newRow);
        modalUpdateRemoveButtons();
    });

    $(document).on('click', '.modal-remove-row', function () {
        $(this).closest('tr').remove();
        modalUpdateRemoveButtons();
    });

    function modalUpdateRemoveButtons() {
        var rowCount = $('#modal-verification-body tr').length;
        if (rowCount <= 1) {
            $('.modal-remove-row').prop('disabled', true);
        } else {
            $('.modal-remove-row').prop('disabled', false);
        }
    }

    // --- Edit Logic ---
    $(document).on('click', '.btn-edit-verif', function () {
        var id = $(this).data('id');
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        var editUrl = window.__CALIBRATION_VERIFICATIONS__.routes.edit.replace(':id', id);

        $.ajax({
            url: editUrl,
            type: 'GET',
            data: { plant: window.__CALIBRATION_VERIFICATIONS__.plantCode },
            success: function (response) {
                var v = response.verification;
                $('#edit_tool_id').val(v.tool_id);
                $('#edit_name_alat').val(v.name_alat);
                $('#edit_merk').val(v.merk);
                $('#edit_serial_number').val(v.serial_number);
                $('#edit_resolusi').val(v.resolusi);
                $('#edit_rentang_ukur').val(v.rentang_ukur);
                $('#edit_frekuensi_kalibrasi').val(v.frekuensi_kalibrasi);
                $('#edit_tanggal_kalibrasi').val(v.tanggal_kalibrasi_formatted || '');
                $('#edit_tanggal_verifikasi').val(v.tanggal_verifikasi_formatted || '');
                $('#edit_next_kalibrasi').val(v.next_kalibrasi_formatted || '');
                $('#edit_judgment').val(v.judgment);
                $('#edit_std_toleransi').val(v.std_toleransi);
                $('#edit_acuan_toleransi').val(v.acuan_toleransi);
                
                // Show current riwayat from tool master
                if (v.tool) {
                    $('#edit_riwayat_kalibrasi').val(v.tool.riwayat_kalibrasi || '-');
                }

                // Reset delete certification flag, file input, and preview area
                $('#edit_delete_certification').val('0');
                $('#edit_cert_file').val('');
                $('#edit_selected_pdf_preview').html('');

                function renderEditPdfBadge(certUrl, certName) {
                    if (certUrl) {
                        var filename = certName || (v.certification_path ? v.certification_path.split('/').pop().replace(/^\d+_/, '') : 'Sertifikat.pdf');
                        $('#edit_existing_pdf').data('certUrl', certUrl).data('certName', filename).html(`
                            <label class="small font-weight-bold mb-1 d-block text-muted">File tersimpan:</label>
                            <div class="d-flex align-items-center p-2 border rounded bg-light x-small shadow-xs" style="overflow:hidden; gap:8px;">
                                <i class="fas fa-file-pdf text-danger flex-shrink-0" style="font-size: 1.1rem;"></i>
                                <span class="text-truncate mr-2 flex-grow-1 small font-weight-bold text-dark" style="min-width:0;" title="${filename}">${filename}</span>
                                <a href="${certUrl}" target="_blank" class="badge badge-info border-0 px-2 py-1 flex-shrink-0" style="cursor: pointer; text-decoration:none;" title="Lihat PDF">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                                <button type="button" class="badge badge-danger border-0 px-2 py-1 flex-shrink-0" id="btn_delete_edit_pdf" style="cursor: pointer;" title="Hapus PDF">
                                    <i class="fas fa-trash-alt mr-1"></i>Hapus
                                </button>
                            </div>
                        `);
                    } else {
                        $('#edit_existing_pdf').data('certUrl', '').data('certName', '').html('');
                    }
                }

                var currentCertUrl = v.certification_path ? (response.certification_url || `/storage/${v.certification_path}`) : null;
                var currentCertName = response.certification_name || (v.certification_path ? v.certification_path.split('/').pop().replace(/^\d+_/, '') : null);
                renderEditPdfBadge(currentCertUrl, currentCertName);

                var rowsHtml = '';
                var nilaiAlat = Array.isArray(v.nilai_alat) ? v.nilai_alat : [v.nilai_alat];
                var nilaiKoreksi = Array.isArray(v.nilai_koreksi) ? v.nilai_koreksi : [v.nilai_koreksi];
                var nilaiKetidakpastian = Array.isArray(v.nilai_ketidakpastian) ? v.nilai_ketidakpastian : [v.nilai_ketidakpastian];
                var hasilVerifikasi = Array.isArray(v.hasil_verifikasi) ? v.hasil_verifikasi : [v.hasil_verifikasi];

                nilaiAlat.forEach(function (val, i) {
                    rowsHtml += `
                        <tr>
                            <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm" value="${val || ''}"></td>
                            <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm calc-input" value="${nilaiKoreksi[i] || ''}"></td>
                            <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm calc-input" value="${nilaiKetidakpastian[i] || ''}"></td>
                            <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm bg-light" value="${hasilVerifikasi[i] || ''}" readonly></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger edit-modal-remove-row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
                $('#edit-modal-verification-body').html(rowsHtml);
                editModalUpdateRemoveButtons();

                var updateUrl = window.__CALIBRATION_VERIFICATIONS__.routes.update.replace(':id', id);
                $('#formEditVerif').attr('action', updateUrl);

                $('#modalEditVerifikasi').modal('show');
                btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                
                // Trigger calculation for all rows after modal is fully shown
                setTimeout(() => {
                    $('#edit-modal-verification-body tr').each(function() {
                        calcHasilVerifikasi($(this));
                    });
                }, 200);
            },
            error: function (xhr) {
                var errorMsg = 'Gagal mengambil data verifikasi.';
                if (xhr.status === 404) errorMsg += ' (Error 404: Data tidak ditemukan)';
                else if (xhr.status === 403) errorMsg += ' (Error 403: Anda tidak memiliki akses)';
                else if (xhr.status === 500) errorMsg += ' (Error 500: Terjadi kesalahan di server)';

                alert(errorMsg);
                btn.prop('disabled', false).html('<i class="fas fa-edit mr-1"></i> EDIT');
            }
        });
    });

    $('#edit-modal-add-row').on('click', function () {
        var newRow = `
            <tr>
                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm border-0 shadow-sm mx-auto" style="width: 80%;"></td>
                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm border-0 shadow-sm mx-auto calc-input" style="width: 80%;"></td>
                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm border-0 shadow-sm mx-auto calc-input" style="width: 80%;"></td>
                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm border-0 shadow-sm mx-auto" style="width: 80%;" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm text-danger edit-modal-remove-row" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#edit-modal-verification-body').append(newRow);
        editModalUpdateRemoveButtons();
    });

    $(document).on('click', '.edit-modal-remove-row', function () {
        $(this).closest('tr').remove();
        editModalUpdateRemoveButtons();
    });

    function editModalUpdateRemoveButtons() {
        var rowCount = $('#edit-modal-verification-body tr').length;
        if (rowCount <= 1) {
            $('.edit-modal-remove-row').prop('disabled', true);
        } else {
            $('.edit-modal-remove-row').prop('disabled', false);
        }
    }

    // Reset file input labels when modals are hidden
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('.custom-file-label').html('Pilih file PDF...');
    });

    function calcHasilVerifikasi(row) {
        var tds = row.find('td');
        var koreksiInput = tds.eq(1).find('input');
        var ketidakpastianInput = tds.eq(2).find('input');
        var hasilInput = tds.eq(3).find('input');

        var koreksiVal = koreksiInput.val() ? koreksiInput.val().trim() : '';
        var ketidakpastianVal = ketidakpastianInput.val() ? ketidakpastianInput.val().trim() : '';

        if (koreksiVal === '' && ketidakpastianVal === '') {
            hasilInput.val('');
        } else {
            var koreksi = parseFloat(koreksiVal) || 0;
            var ketidakpastian = parseFloat(ketidakpastianVal) || 0;
            var hasil = koreksi + ketidakpastian;
            hasilInput.val(parseFloat(hasil.toFixed(6)));
        }
    }

    $(document).on('input', '#modal-verification-body input, #edit-modal-verification-body input', function () {
        var row = $(this).closest('tr');
        var cellIndex = $(this).closest('td').index();
        if (cellIndex === 1 || cellIndex === 2) {
            calcHasilVerifikasi(row);
        }
    });

    // Move modals to body to avoid overflow/z-index issues
    $('.modal').each(function() {
        if ($(this).parent()[0] !== document.body) {
            $(this).appendTo('body');
        }
    });

    $(document).on('click', '.btn-qr-modal', function () {
        var btn = $(this);
        var id = btn.data('id');
        var originalHtml = btn.html();

        if (!id) {
            alert('ID tidak ditemukan. Silakan refresh halaman.');
            return;
        }

        if (!window.__CALIBRATION_VERIFICATIONS__ || !window.__CALIBRATION_VERIFICATIONS__.routes) {
            alert('Konfigurasi halaman tidak ditemukan. Silakan refresh halaman.');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>...');

        var qrDataUrl = window.__CALIBRATION_VERIFICATIONS__.routes.qrData.replace(':id', id);

        $.ajax({
            url: qrDataUrl,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function (response) {
                if (!response || !response.verification) {
                    alert('Respons dari server tidak valid.');
                    btn.prop('disabled', false).html(originalHtml);
                    return;
                }

                var v = response.verification;
                var qrSvgBase64 = response.qr_code;
                var downloadUrl = response.download_url;

                $('#qr-modal-image').html(
                    '<img src="data:image/png;base64,' + qrSvgBase64 + '" id="qr-img" style="width:250px;height:250px;">'
                );
                $('#qr-modal-tool-name').text(v.name_alat || '-');
                $('#qr-modal-serial').text(v.serial_number || '-');
                $('#qr-modal-date').text(v.tanggal_verifikasi
                    ? new Date(v.tanggal_verifikasi).toLocaleDateString('id-ID')
                    : '-');

                if (v.judgment === 'OK' || v.judgment === 'NG') {
                    var badgeClass = v.judgment === 'OK' ? 'success' : 'danger';
                    $('#qr-modal-judgment').html('<span class="badge badge-' + badgeClass + '">' + v.judgment + '</span>');
                } else {
                    $('#qr-modal-judgment').html(v.judgment || '-');
                }

                $('#qr-modal-download-pdf').attr('href', downloadUrl);

                $('#qr-modal-download-img').off('click.qr').on('click.qr', function () {
                    var link = document.createElement('a');
                    link.href = 'data:image/png;base64,' + qrSvgBase64;
                    link.download = 'QR_' + (v.serial_number || 'code') + '.png';
                    link.className = 'no-loader'; // prevent global loader trigger
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(function() { $('#global-loader').fadeOut(); }, 200);
                });

                btn.prop('disabled', false).html(originalHtml);
                $('#modalQrCode').modal('show');
            },
            error: function (xhr) {
                var msg = 'Gagal mengambil data QR.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg += ' (' + xhr.responseJSON.message + ')';
                } else if (xhr.status) {
                    msg += ' (HTTP ' + xhr.status + ')';
                }
                alert(msg);
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // PDF Delete & Undo Delete for File Tersimpan
    $(document).on('click', '#btn_delete_edit_pdf', function () {
        $('#edit_delete_certification').val('1');
        $('#edit_existing_pdf').html(`
            <div class="alert alert-warning py-1 px-2 mb-0 d-flex align-items-center justify-content-between small shadow-xs rounded mt-1">
                <span><i class="fas fa-exclamation-triangle mr-1"></i> File tersimpan akan dihapus saat disimpan.</span>
                <button type="button" class="btn btn-sm btn-link text-dark font-weight-bold p-0 text-decoration-none" id="btn_undo_delete_edit_pdf">
                    <i class="fas fa-undo mr-1"></i> Batal Hapus
                </button>
            </div>
        `);
    });

    $(document).on('click', '#btn_undo_delete_edit_pdf', function () {
        $('#edit_delete_certification').val('0');
        var certUrl = $('#edit_existing_pdf').data('certUrl');
        var certName = $('#edit_existing_pdf').data('certName');
        if (certUrl) {
            var filename = certName || certUrl.split('/').pop().replace(/^\d+_/, '') || 'Sertifikat.pdf';
            $('#edit_existing_pdf').html(`
                <label class="small font-weight-bold mb-1 d-block text-muted">File tersimpan:</label>
                <div class="d-flex align-items-center p-2 border rounded bg-light x-small shadow-xs" style="overflow:hidden; gap:8px;">
                    <i class="fas fa-file-pdf text-danger flex-shrink-0" style="font-size: 1.1rem;"></i>
                    <span class="text-truncate mr-2 flex-grow-1 small font-weight-bold text-dark" style="min-width:0;" title="${filename}">${filename}</span>
                    <a href="${certUrl}" target="_blank" class="badge badge-info border-0 px-2 py-1 flex-shrink-0" style="cursor: pointer; text-decoration:none;" title="Lihat PDF">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <button type="button" class="badge badge-danger border-0 px-2 py-1 flex-shrink-0" id="btn_delete_edit_pdf" style="cursor: pointer;" title="Hapus PDF">
                        <i class="fas fa-trash-alt mr-1"></i>Hapus
                    </button>
                </div>
            `);
        }
    });

    // Helper to render "File dipilih (1):" preview card matching Master Data style
    function renderSelectedPdfCard(file, containerId) {
        var container = $('#' + containerId);
        if (file) {
            var objectUrl = URL.createObjectURL(file);
            container.data('objectUrl', objectUrl).html(`
                <label class="small font-weight-bold mb-1 d-block text-muted">File dipilih (1):</label>
                <div class="d-flex align-items-center p-2 border rounded bg-white shadow-sm" style="gap:8px; font-size:0.78rem;">
                    <i class="fas fa-file-pdf text-danger flex-shrink-0" style="font-size: 1.3rem;"></i>
                    <span class="text-truncate flex-grow-1 font-weight-bold text-dark" style="max-width: 180px;" title="${file.name}">${file.name}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1 font-weight-bold d-flex align-items-center btn-preview-selected" data-url="${objectUrl}" style="font-size:0.75rem;">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 font-weight-bold d-flex align-items-center btn-remove-selected" data-container="${containerId}" style="font-size:0.75rem;">
                        <i class="fas fa-times mr-1"></i> Hapus
                    </button>
                </div>
            `);
        } else {
            var oldUrl = container.data('objectUrl');
            if (oldUrl) URL.revokeObjectURL(oldUrl);
            container.data('objectUrl', '').html('');
        }
    }

    // Edit Modal file input change
    $(document).on('change', '#edit_cert_file', function () {
        if (this.files && this.files.length > 0) {
            $('#edit_delete_certification').val('0');
            renderSelectedPdfCard(this.files[0], 'edit_selected_pdf_preview');
        } else {
            renderSelectedPdfCard(null, 'edit_selected_pdf_preview');
        }
    });

    // Create Modal file input change
    $(document).on('change', '#modal_cert_file', function () {
        if (this.files && this.files.length > 0) {
            renderSelectedPdfCard(this.files[0], 'modal_selected_pdf_preview');
        } else {
            renderSelectedPdfCard(null, 'modal_selected_pdf_preview');
        }
    });

    // Preview newly selected PDF
    $(document).on('click', '.btn-preview-selected', function () {
        var url = $(this).data('url');
        if (url) {
            window.open(url, '_blank');
        }
    });

    // Remove newly selected PDF
    $(document).on('click', '.btn-remove-selected', function () {
        var containerId = $(this).data('container');
        if (containerId === 'edit_selected_pdf_preview') {
            $('#edit_cert_file').val('');
            renderSelectedPdfCard(null, 'edit_selected_pdf_preview');
        } else if (containerId === 'modal_selected_pdf_preview') {
            $('#modal_cert_file').val('');
            renderSelectedPdfCard(null, 'modal_selected_pdf_preview');
        }
    });
});
