(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const config = window.pendingApprovalLoginConfig;
        if (!config || !config.fetchUrl) return;

        const modalEl = document.getElementById('modalPendingApprovalLogin');
        const tbody = document.getElementById('tbodyPendingLoginItems');
        const countText = document.getElementById('pendingLoginCountText');

        if (!modalEl || !tbody) return;

        // Fetch data pending item
        fetch(config.fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(res => {
                if (!res.success || !res.data || res.data.length === 0) {
                    // Jika tidak ada item yang butuh persetujuan user ini, jangan tampilkan modal
                    return;
                }

                // Tampilkan jumlah item
                if (countText) {
                    countText.textContent = `Total: ${res.count} item menunggu approval Anda`;
                }

                // Render isi tabel
                let rowsHtml = '';
                res.data.forEach((item, index) => {
                    let changesHtml = '';
                    const changes = item.pending_changes;

                    if (changes && Object.keys(changes).length > 0) {
                        changesHtml += '<div class="d-flex flex-column" style="gap: 4px; font-size: 0.65rem;">';
                        Object.keys(changes).forEach(key => {
                            const field = changes[key];
                            const beforeVal = (!field.before || field.before === '-') ? 'Tidak Ada Data' : field.before;
                            const afterVal = (!field.after || field.after === '-') ? 'Tidak Ada Data' : field.after;
                            changesHtml += `
                            <div class="p-1 bg-light rounded border border-light text-left" style="line-height: 1.2;">
                                <span class="font-weight-bold text-dark">${escapeHtml(field.label)}:</span>
                                <span class="text-danger text-decoration-line-through ml-1" style="text-decoration: line-through;">${escapeHtml(beforeVal)}</span>
                                <i class="fas fa-arrow-right mx-1 text-muted" style="font-size: 0.55rem;"></i>
                                <span class="text-success font-weight-bold">${escapeHtml(afterVal)}</span>
                            </div>
                        `;
                        });
                        changesHtml += '</div>';
                    } else if (item.is_new) {
                        changesHtml = '<span class="badge badge-success px-2 py-1"><i class="fas fa-plus-circle mr-1"></i> Data Item Baru</span>';
                    } else {
                        changesHtml = '<span class="text-muted font-italic" style="font-size: 0.68rem;">Data diperbarui</span>';
                    }

                    rowsHtml += `
                    <tr class="text-center row-pending-${item.id}">
                        <td class="align-middle">${index + 1}</td>
                        <td class="align-middle text-left font-weight-bold text-gray-800">${escapeHtml(item.name)}</td>
                        <td class="align-middle text-center font-weight-bold text-primary">${escapeHtml(item.part_number)}</td>
                        <td class="align-middle text-left">${escapeHtml(item.customer)}</td>
                        <td class="align-middle text-center"><span class="badge badge-light border text-dark px-2 py-1">${escapeHtml(item.category)}</span></td>
                        <td class="align-middle text-center font-weight-bold text-gray-700">${escapeHtml(item.requester_name || '-')}</td>
                        <td class="align-middle text-left">${changesHtml}</td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-success btn-xs btn-approve-login-modal shadow-sm p-0 d-inline-flex align-items-center justify-content-center"
                                style="width: 26px; height: 26px; font-size: 0.72rem; border-radius: 6px;"
                                data-id="${item.id}" data-name="${escapeHtml(item.name)}" data-tier="${item.tier}" data-tier-label="${item.tier_label}"
                                title="Setujui (Approve)">
                                <i class="fas fa-check"></i>
                            </button>
                        </td>
                    </tr>
                `;
                });

                tbody.innerHTML = rowsHtml;

                // Tampilkan modal Bootstrap jika auto-show bernilai 1 atau dipanggil manual
                const shouldAutoShow = modalEl.getAttribute('data-auto-show') === '1';
                if (shouldAutoShow && typeof $ !== 'undefined' && $.fn.modal) {
                    $('#modalPendingApprovalLogin').modal('show');
                }
            })
            .catch(err => {
                console.warn('Gagal memuat pending item modal:', err);
            });

        // Function globally accessible to open the modal anytime
        window.openPendingApprovalModal = function () {
            if (typeof $ !== 'undefined' && $.fn.modal) {
                $('#modalPendingApprovalLogin').modal('show');
            }
        };

        // Handler Approve langsung dari modal
        $(document).on('click', '.btn-approve-login-modal', function () {
            const btn = $(this);
            const id = btn.data('id');
            const name = btn.data('name');
            const tier = btn.data('tier');
            const tierLabel = btn.data('tier-label') || 'Jenjang';

            Swal.fire({
                title: `Setujui Item?`,
                text: `Setujui item "${name}" sebagai ${tierLabel}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#858796',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    const url = config.approveUrl.replace(':id', id);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: config.csrfToken,
                            tier: tier
                        },
                        success: function (resp) {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Disetujui',
                                    text: resp.message,
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                                if (typeof window.fetchTopNotifications === 'function') {
                                    window.fetchTopNotifications();
                                }
                                // Hapus baris dari tabel
                                const row = $(`.row-pending-${id}`);
                                row.fadeOut(300, function () {
                                    $(this).remove();
                                    const remaining = $('#tbodyPendingLoginItems tr').length;
                                    if (remaining === 0) {
                                        $('#modalPendingApprovalLogin').modal('hide');
                                    }
                                });
                            } else {
                                Swal.fire('Gagal', resp.message || 'Terjadi kesalahan', 'error');
                                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Approve');
                            }
                        },
                        error: function (xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyetujui.';
                            Swal.fire('Error', msg, 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Approve');
                        }
                    });
                }
            });
        });

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
})();
