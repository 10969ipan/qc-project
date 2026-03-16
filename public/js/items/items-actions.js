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

        if (!confirm('Apakah Anda yakin ingin menghapus file ini?')) return;

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
    });

    $(document).on('click', '.btn-delete-similar-pdf', function () {
        const btn = $(this);
        const id = btn.data('id');

        if (!confirm('Apakah Anda yakin ingin menghapus file Dimensi Part ini?')) return;

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
                    alert(response.message);
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
    });

})();
