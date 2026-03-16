(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const config = window.__CATEGORIES__ || {};

        $('.btn-edit-category').on('click', function () {
            const id = $(this).data('id');
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: config.edit_url.replace(':id', id),
                type: 'GET',
                success: function (response) {
                    $('#edit_category_name').val(response.category.name);
                    $('#edit_plant_name').val(response.plant ? response.plant.name.toUpperCase() : '-');

                    const updateUrl = config.update_url.replace(':id', id);
                    $('#formEditCategory').attr('action', updateUrl);

                    $('#modalEditCategory').modal('show');
                    btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
                },
                error: function (xhr) {
                    let message = 'Gagal mengambil data kategori.';
                    if (xhr.status === 404) {
                        message = 'Kategori tidak ditemukan.';
                    } else if (xhr.status === 403) {
                        message = 'Anda tidak memiliki akses untuk mengedit kategori ini.';
                    } else if (xhr.status === 500) {
                        message = 'Terjadi kesalahan pada server saat mengambil data.';
                    }
                    alert(message);
                    btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
                }
            });
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Data kategori ini akan dihapus permanen!',
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
        });
    });
})();
