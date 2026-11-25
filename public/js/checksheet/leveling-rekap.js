/**
 * Modul JavaScript untuk Rekap & Monitoring Leveling Inspector Tahunan
 */
$(document).ready(function () {
    $('#rekapYear, #rekapBagian').on('change', function () {
        $('#rekapFilterForm').submit();
    });

    $('#rekapSearchInspector').on('input', function () {
        var q = ($(this).val() || '').toLowerCase().trim();
        $('#rekapLevelingTable tbody tr.js-rekap-row').each(function () {
            var haystack = String($(this).data('search') || '');
            $(this).toggle(!q || haystack.indexOf(q) !== -1);
        });
    });

    // Direct Print Cetak Rekap (Tanpa Buka Halaman / Tab Baru, pola Dandory Quality)
    $(document).on('click', '.btn-print-direct, #btnPrintRekap', function (e) {
        e.preventDefault();
        var printUrl = $(this).attr('href') || $(this).data('print-url');
        if (!printUrl || printUrl === '#' || printUrl === '') return;

        var oldIframe = document.getElementById('silentPrintIframe');
        if (oldIframe) {
            oldIframe.parentNode.removeChild(oldIframe);
        }

        var iframe = document.createElement('iframe');
        iframe.id = 'silentPrintIframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        iframe.style.opacity = '0';
        iframe.src = printUrl;

        document.body.appendChild(iframe);
    });

    // Approval berjenjang 4 tahap (Auditor -> Leader/Karu/Kashift -> SPV -> Asst. Manager)
    function escHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderApprovalRow($row, inspectorId, stages) {
        var approveStage = null;
        var cancelStage = null;

        (stages || []).forEach(function (st) {
            if (!approveStage && st.can_approve) approveStage = st;
            if (!cancelStage && st.can_cancel) cancelStage = st;

            var $cell = $row.find('.js-rekap-approval-stage[data-stage="' + st.stage + '"]');
            if (!$cell.length) return;

            var html = '';
            if (st.approved) {
                html = '<div class="font-weight-bold text-success" style="font-size:0.65rem;">Approved</div>' +
                    '<div class="text-dark font-weight-bold" style="font-size:0.63rem;">' + escHtml(st.approver_name || '-') + '</div>' +
                    (st.approved_at ? '<div class="text-muted" style="font-size:0.6rem;">' + escHtml(st.approved_at) + '</div>' : '');
            } else {
                html = '<div class="text-muted">Pending</div>' +
                    (st.pending_name ? '<div class="text-dark font-weight-bold" style="font-size:0.62rem;">' + escHtml(st.pending_name) + '</div>' : '');
            }

            $cell.html(html);
        });

        var $actionsWrap = $row.find('.js-rekap-approval-actions');
        if ($actionsWrap.length) {
            var actHtml = '';
            if (approveStage) {
                actHtml += '<button type="button" class="btn btn-success btn-sm px-2 py-0 js-rekap-approve-btn" ' +
                    'style="font-size:0.63rem; line-height:1.5;" ' +
                    'data-inspector-id="' + escHtml(inspectorId) + '" data-stage="' + escHtml(approveStage.stage) + '" data-label="' + escHtml(approveStage.label) + '" ' +
                    'title="Approve"><i class="fas fa-check mr-1"></i>Approve</button>';
            }
            if (cancelStage) {
                actHtml += '<button type="button" class="btn btn-outline-dark btn-sm px-2 py-0 js-rekap-cancel-approve-btn" ' +
                    'style="font-size:0.63rem; line-height:1.5;" ' +
                    'data-inspector-id="' + escHtml(inspectorId) + '" data-stage="' + escHtml(cancelStage.stage) + '" data-label="' + escHtml(cancelStage.label) + '" ' +
                    'title="Batal Approval"><i class="fas fa-undo-alt"></i></button>';
            }
            $actionsWrap.html(actHtml || '<span class="text-muted">-</span>');
        }
    }

    function sendRekapApproval($btn, action) {
        var $table = $('#rekapLevelingTable');
        var url = $table.data('approve-url');
        var plant = $table.data('plant');
        var year = $table.data('year');
        var csrf = $table.data('csrf');
        var inspectorId = $btn.data('inspector-id');
        var stage = $btn.data('stage');
        var $row = $btn.closest('tr');
        if (!url || !inspectorId || !stage) return;

        function doPost() {
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.ajax({
                url: url,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                data: {
                    plant_code: plant,
                    year: year,
                    inspector_id: inspectorId,
                    stage: stage,
                    action: action
                }
            })
                .done(function (res) {
                    if (res && Array.isArray(res.approval)) {
                        renderApprovalRow($row, inspectorId, res.approval);
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: (res && res.message) || 'Approval diperbarui.',
                            showConfirmButton: false,
                            timer: 2200
                        });
                    }
                })
                .fail(function (xhr) {
                    var msg = 'Gagal memproses approval.';
                    if (xhr && xhr.responseJSON) {
                        if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        if (xhr.responseJSON.errors) {
                            var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                            if (firstKey && xhr.responseJSON.errors[firstKey][0]) {
                                msg = xhr.responseJSON.errors[firstKey][0];
                            }
                        }
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    } else {
                        alert(msg);
                    }
                    $btn.prop('disabled', false).html(origHtml);
                });
        }

        var confirmTitle = action === 'approve'
            ? 'Approve?'
            : 'Batalkan Approval?';
        var confirmText = action === 'approve'
            ? 'Rekap leveling inspector ini akan disetujui.'
            : 'Approval pada baris ini akan dibatalkan.';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: action === 'approve' ? 'question' : 'warning',
                title: confirmTitle,
                text: confirmText,
                showCancelButton: true,
                confirmButtonColor: action === 'approve' ? '#4e73df' : '#f6c23e',
                cancelButtonColor: '#858796',
                confirmButtonText: action === 'approve' ? 'Ya, Approve' : 'Ya, Batalkan',
                cancelButtonText: 'Batal'
            }).then(function (r) {
                if (r.isConfirmed) doPost();
            });
        } else if (confirm(confirmTitle + '\n' + confirmText)) {
            doPost();
        }
    }

    $(document).on('click', '.js-rekap-approve-btn', function () {
        sendRekapApproval($(this), 'approve');
    });

    $(document).on('click', '.js-rekap-cancel-approve-btn', function () {
        sendRekapApproval($(this), 'cancel');
    });
});
