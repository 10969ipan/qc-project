/**
 * Measuring Tools Management (Per Plant)
 * File: public/js/settings/measuring-tools.js
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        const config = window.settingsConfig || {};
        const csrfToken = config.var_1 || $('meta[name="csrf-token"]').attr('content');
        const storeUrl = config.var_mt_store;
        const baseUrl = config.var_mt_url;
        const syncUrl = config.var_mt_sync;

        // Auto uppercase for tool name inputs
        $('#add_mt_name, #edit_mt_name').on('input', function () {
            $(this).val($(this).val().toUpperCase());
        });

        // 1. Submit Add Measuring Tool
        $('#formAddMeasuringTool').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalBtnHtml = $submitBtn.html();

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: storeUrl,
                type: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (res) {
                    $('#modalAddMeasuringTool').modal('hide');
                    $form[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Alat ukur berhasil ditambahkan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menambahkan alat ukur.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: msg
                    });
                },
                complete: function () {
                    $submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });

        // 2. Open Edit Modal & Populate Form
        $(document).on('click', '.btn-edit-measuring-tool', function (e) {
            e.preventDefault();

            const id = $(this).data('id');
            const plantId = $(this).data('plant-id');
            const name = $(this).data('name');
            const serial = $(this).data('serial') || '';
            const category = $(this).data('category') || 'numeric';
            const order = $(this).data('order') || 0;
            const active = $(this).data('active') == 1;

            $('#edit_mt_id').val(id);
            $('#edit_mt_plant_id').val(plantId);
            $('#edit_mt_name').val(name);
            $('#edit_mt_serial').val(serial);
            $('#edit_mt_category').val(category);
            $('#edit_mt_order').val(order);
            $('#edit_mt_active').prop('checked', active);

            $('#modalEditMeasuringTool').modal('show');
        });

        // 3. Submit Edit Measuring Tool
        $('#formEditMeasuringTool').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const id = $('#edit_mt_id').val();
            const $submitBtn = $form.find('button[type="submit"]');
            const originalBtnHtml = $submitBtn.html();

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memperbarui...');

            $.ajax({
                url: baseUrl + '/' + id,
                type: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (res) {
                    $('#modalEditMeasuringTool').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Alat ukur berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memperbarui alat ukur.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: msg
                    });
                },
                complete: function () {
                    $submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });

        // 4. Delete Measuring Tool (SweetAlert2 as required by AGENTS.md)
        $(document).on('click', '.btn-delete-measuring-tool', function (e) {
            e.preventDefault();

            const id = $(this).data('id');
            const name = $(this).data('name') || 'alat ukur ini';

            Swal.fire({
                title: 'Hapus Alat Ukur?',
                text: `Apakah Anda yakin ingin menghapus '${name}'?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl + '/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.message || 'Alat ukur berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function () {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus alat ukur.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    });
                }
            });
        });

        // 5. Batch Flush / Sync Measuring Tools Cache
        $('#btnSyncMeasuringTools').on('click', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyegarkan...');

            $.ajax({
                url: syncUrl,
                type: 'POST',
                data: {},
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cache Disegarkan',
                        text: res.message || 'Seluruh cache alat ukur berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyegarkan cache alat ukur.'
                    });
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
})(jQuery);
