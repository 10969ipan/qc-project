$(function () {
    'use strict';

    // Handler AJAX Form Submit (Approve, Reject, Update)
    $(document).on("submit", ".ajax-form", function (e) {
        const $form = $(this);
        if ($form.data('submitting')) {
            e.preventDefault();
            return false;
        }
        $form.data('submitting', true);
        e.preventDefault();

        const $submitBtn = $form.find('button[type="submit"]');
        const $modalErrors = $form.find("#modal-errors");
        const originalBtnHtml = $submitBtn.html();

        $modalErrors.hide().html("");
        $form.find(".is-invalid").removeClass("is-invalid");
        $form.find(".invalid-feedback").remove();

        $submitBtn
            .prop("disabled", true)
            .html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Menyimpan...');

        $.ajax({
            url: $form.attr("action"),
            method: $form.attr("method") || "POST",
            data: $form.serialize(),
            dataType: "json",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            complete: function () {
                $form.removeData('submitting');
            },
            success: function (response) {
                // Intersep jika sistem mewajibkan leader sampling
                if (response.requires_leader_sampling && response.checksheet_id) {
                    $submitBtn.prop("disabled", false).html(originalBtnHtml);
                    if (typeof window.openLeaderSamplingModal === 'function') {
                        window.openLeaderSamplingModal(response.checksheet_id);
                    }
                    return;
                }

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Data berhasil disimpan.',
                        timer: 900,
                        showConfirmButton: false
                    }).then(function () {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    $submitBtn.prop("disabled", false).html(originalBtnHtml);
                    const msg = response.message || "Terjadi kesalahan saat memproses data.";
                    if ($modalErrors.length) {
                        $modalErrors.html(msg).fadeIn();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: msg
                        });
                    }
                }
            },
            error: function (xhr) {
                $submitBtn.prop("disabled", false).html(originalBtnHtml);

                if (xhr.status === 419) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesi Kedaluwarsa',
                        text: 'Sesi Anda telah berakhir atau token CSRF kedaluwarsa. Halaman akan dimuat ulang...',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                        window.location.reload();
                    });
                    return;
                }

                if (xhr.responseJSON && xhr.responseJSON.requires_leader_sampling && xhr.responseJSON.checksheet_id) {
                    if (typeof window.openLeaderSamplingModal === 'function') {
                        window.openLeaderSamplingModal(xhr.responseJSON.checksheet_id);
                    }
                    return;
                }

                const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.statusText ? ("Terjadi kesalahan sistem (" + xhr.status + ": " + xhr.statusText + ")") : "Terjadi kesalahan sistem.");

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                    let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                    if (errors) {
                        $.each(errors, function (field, messages) {
                            errorHtml += "<li>" + messages[0] + "</li>";
                            const $input = $form.find('[name="' + field + '"]');
                            if ($input.length) {
                                $input.addClass("is-invalid");
                                if (!$input.next(".invalid-feedback").length) {
                                    $input.after('<div class="invalid-feedback">' + messages[0] + "</div>");
                                }
                            }
                        });
                    } else {
                        errorHtml += "<li>" + errorMsg + "</li>";
                    }
                    errorHtml += "</ul></div>";
                    if ($modalErrors.length) {
                        $modalErrors.html(errorHtml).fadeIn();
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Validasi Gagal', text: errorMsg });
                    }
                } else {
                    if ($modalErrors.length) {
                        $modalErrors.html('<div class="alert alert-danger">' + errorMsg + "</div>").fadeIn();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorMsg
                        });
                    }
                }
            }
        });
    });

    // Modal Detail Alat Ukur Per Point (Single Reusable Dynamic Modal)
    $(document).on('click', '.btn-detail-alat-ukur', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var tools = $btn.data('tools');
        var itemName = $btn.data('item') || '-';
        var partNumber = $btn.data('part-number') || '-';

        if (typeof tools === 'string') {
            try {
                tools = JSON.parse(tools);
            } catch (err) {
                tools = {};
            }
        }

        var subtitleText = itemName + ' (' + partNumber + ')';
        $('#alatUkurModalSubtitle').text(subtitleText);

        var $tbody = $('#alatUkurModalTableBody');
        $tbody.empty();

        if (!tools || typeof tools !== 'object' || Object.keys(tools).length === 0) {
            $tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data alat ukur spesifik per point.</td></tr>');
        } else {
            var sortedPoints = Object.keys(tools).sort(function (a, b) {
                return parseInt(a, 10) - parseInt(b, 10);
            });

            var rowsHtml = '';
            sortedPoints.forEach(function (pt) {
                var toolName = tools[pt];
                if (toolName && String(toolName).trim() !== '') {
                    rowsHtml += '<tr>' +
                        '<td class="align-middle text-center font-weight-bold" style="width: 35%; color: #334155; font-size: 0.68rem; padding: 5px 8px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9;">' +
                            'Ø' + pt +
                        '</td>' +
                        '<td class="align-middle text-left font-weight-bold" style="width: 65%; color: #1e293b; font-size: 0.68rem; padding: 5px 8px; border-bottom: 1px solid #f1f5f9;">' +
                            toolName +
                        '</td>' +
                    '</tr>';
                }
            });

            if (rowsHtml === '') {
                rowsHtml = '<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data alat ukur yang valid.</td></tr>';
            }
            $tbody.html(rowsHtml);
        }

        $('#modalDetailAlatUkur').modal('show');
    });
});
