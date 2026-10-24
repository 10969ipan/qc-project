(function () {
    'use strict';

    const ITEMS = window.__ITEMS__ || {};
    const ROUTES = ITEMS.routes || {};

    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        const form = this.closest('form');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data item ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).on('click', '.btn-delete-pdf', function () {
        const btn = $(this);
        const id = btn.data('id');
        const index = btn.data('index');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "File PDF ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const originalText = btn.text();
                btn.text('...').prop('disabled', true);

                const url = ROUTES.deletePdf.replace('__ID__', id).replace('__INDEX__', index);

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: ITEMS.csrfToken },
                    success: function (response) {
                        if (response.success) {
                            btn.closest('div').remove();
                        } else {
                            alert('Gagal menghapus file: ' + response.message);
                            btn.text(originalText).prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        let msg = 'Terjadi kesalahan saat menghapus file.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg += ' ' + xhr.responseJSON.message;
                        }
                        alert(msg);
                        btn.text(originalText).prop('disabled', false);
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-delete-similar-pdf', function () {
        const btn = $(this);
        const id = btn.data('id');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "File PDF Dimensi Part ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const originalText = btn.text();
                btn.text('...').prop('disabled', true);

                const url = ROUTES.deleteSimilarPdf.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: ITEMS.csrfToken },
                    success: function (response) {
                        if (response.success) {
                            btn.closest('div').remove();
                            $('#edit_existing_similar_file').empty();
                            
                            Swal.fire(
                                'Terhapus!',
                                response.message,
                                'success'
                            );
                        } else {
                            alert('Gagal menghapus file: ' + response.message);
                            btn.text(originalText).prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        let msg = 'Terjadi kesalahan saat menghapus file.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg += ' ' + xhr.responseJSON.message;
                        }
                        alert(msg);
                        btn.text(originalText).prop('disabled', false);
                    }
                });
            }
        });
    });

    // Handle Item Approval by specific Tier (Spv. QC, Asst. Mgr Quality, Mgr. Quality)
    $(document).on('click', '.btn-approve-tier', function () {
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');
        const tier = btn.data('tier');
        const tierLabel = btn.data('tier-label') || 'Jenjang';

        Swal.fire({
            title: `Approve ${tierLabel}?`,
            text: `Apakah Anda yakin ingin menyetujui item "${name}" sebagai ${tierLabel}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1cc88a',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true);
                const url = ROUTES.approve.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: ITEMS.csrfToken,
                        tier: tier
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disetujui',
                                text: res.message,
                                timer: 1400,
                                showConfirmButton: false
                            });
                            // Refresh halaman setelah jeda singkat agar seluruh state tabel dan counter filter tersinkron sempurna
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                            btn.prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memproses persetujuan.';
                        Swal.fire('Error', msg, 'error');
                        btn.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Handle Item Approval by Admin (All Tiers)
    $(document).on('click', '.btn-approve-item', function () {
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');

        Swal.fire({
            title: 'Setujui Semua Jenjang?',
            text: `Item "${name}" akan disetujui untuk semua jenjang (Spv, Asst Mgr, Mgr) dan langsung aktif.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1cc88a',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-check-double mr-1"></i> Ya, Setujui Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true);
                const url = ROUTES.approve.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: ITEMS.csrfToken, tier: 'all' },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disetujui',
                                text: res.message,
                                timer: 1400,
                                showConfirmButton: false
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                            btn.prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memproses persetujuan.';
                        Swal.fire('Error', msg, 'error');
                        btn.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Handle Item Rejection by Supervisor or above
    $(document).on('click', '.btn-reject-item', function () {
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');

        Swal.fire({
            title: 'Tolak Master Item?',
            text: `Masukkan alasan penolakan untuk item "${name}":`,
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan penolakan di sini...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-times mr-1"></i> Tolak Item',
            cancelButtonText: 'Batal',
            inputValidator: (val) => {
                if (!val || !val.trim()) {
                    return 'Alasan penolakan wajib diisi!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true);
                const url = ROUTES.reject.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: ITEMS.csrfToken,
                        reason: result.value
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Item Ditolak',
                                text: res.message,
                                timer: 1400,
                                showConfirmButton: false
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                            btn.prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menolak item.';
                        Swal.fire('Error', msg, 'error');
                        btn.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Elegant Table Loading Animation (In-Process & Kakotora Pattern)
    $(document).ready(function () {
        const loader = $('#tableLoader');
        const container = $('#tableContainer');
        if (loader.length && container.length) {
            setTimeout(function () {
                loader.fadeOut(180, function () {
                    container.fadeIn(220);
                });
            }, 80);
        }
    });

})();
