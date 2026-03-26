/**
 * Calibration Verifications Management
 */

$(document).ready(function () {
    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#dataTable').DataTable({
            dom: "<'row px-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
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

    // --- Modal Verifikasi Baru Logic ---
    $('#modal_tool_select').on('change', function () {
        var selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#modal_name_alat').val(selected.data('name'));
            $('#modal_serial_number').val(selected.data('serial'));
            $('#modal_rentang_ukur').val(selected.data('range'));
            $('#modal_resolusi').val(selected.data('resolusi'));
            $('#modal_frekuensi_kalibrasi').val(selected.data('frekuensi'));

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
                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm calc-input"></td>
                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm calc-input"></td>
                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm bg-light" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger modal-remove-row">
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
    $('.btn-edit-verif').on('click', function () {
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
                $('#edit_tanggal_kalibrasi').val(v.tanggal_kalibrasi ? v.tanggal_kalibrasi.substring(0, 10) : '');
                $('#edit_tanggal_verifikasi').val(v.tanggal_verifikasi ? v.tanggal_verifikasi.substring(0, 10) : '');
                $('#edit_next_kalibrasi').val(v.next_kalibrasi ? v.next_kalibrasi.substring(0, 10) : '');
                $('#edit_judgment').val(v.judgment);
                $('#edit_std_toleransi').val(v.std_toleransi);
                $('#edit_acuan_toleransi').val(v.acuan_toleransi);

                if (v.certification_path) {
                    $('#edit_existing_pdf').html(`<a href="/storage/${v.certification_path}" target="_blank" class="badge badge-info"><i class="fas fa-file-pdf mr-1"></i> Lihat Sertifikat</a>`);
                } else {
                    $('#edit_existing_pdf').html('');
                }

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
                btn.prop('disabled', false).html('<i class="fas fa-edit mr-1"></i> EDIT');
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
                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm calc-input"></td>
                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm calc-input"></td>
                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm bg-light" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger edit-modal-remove-row">
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

    // QR Code Modal - move modal to body to avoid overflow container conflicts
    var $qrModal = $('#modalQrCode');
    if ($qrModal.length && $qrModal.parent()[0] !== document.body) {
        $qrModal.appendTo('body');
    }

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
});
