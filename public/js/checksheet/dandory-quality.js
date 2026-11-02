/**
 * Checksheet Dandory Quality (QC-KRW-F-0094 / QC-JKT-F-0094)
 * Client-Side Controller & Interactive Matrix Handler
 *
 * Mengikuti prinsip Ponytail: Lean, cepat, zero redundant overhead,
 * dan mematuhi aturan strict arsitektur proyek (Zero inline script di Blade).
 */
$(document).ready(function () {
    'use strict';

    // 1. Setup CSRF Token untuk seluruh request AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 2. Baca Konfigurasi Runtime dari Elemen Tersembunyi #dandory-config
    var cfgEl = document.getElementById('dandory-config');
    var config = cfgEl ? {
        plant: cfgEl.dataset.plant || 'karawang',
        date: cfgEl.dataset.date || '',
        shift: cfgEl.dataset.shift || '',
        month: cfgEl.dataset.month || '',
        year: cfgEl.dataset.year || '',
        userRole: cfgEl.dataset.userRole || '',
        operatorId: cfgEl.dataset.operatorId || '',
        awalStoreUrl: cfgEl.dataset.awalStoreUrl || '/checksheet/dandory/awal',
        akhirBaseUrl: cfgEl.dataset.akhirBaseUrl || '/checksheet/dandory',
        partPresetUrl: cfgEl.dataset.partPresetUrl || '/checksheet/dandory/part-preset',
        bulkVerifyUrl: cfgEl.dataset.bulkVerifyUrl || '/checksheet/dandory/bulk-verify',
        bulkDestroyUrl: cfgEl.dataset.bulkDestroyUrl || '/checksheet/dandory/bulk-destroy',
        opCfgSaveUrl: cfgEl.dataset.opCfgSaveUrl || '/checksheet/dandory/operator-config',
        tablesSaveUrl: cfgEl.dataset.tablesSaveUrl || '/checksheet/dandory/tables',
        masterItemUrl: cfgEl.dataset.masterItemUrl || '/checksheet/dandory/master-item',
        csrfToken: cfgEl.dataset.csrfToken || '',
        opConfigs: (function() {
            try { return JSON.parse(cfgEl.dataset.opConfigs || '{}'); } catch(e) { return {}; }
        })(),
        bagianCategories: (function() {
            try { return JSON.parse(cfgEl.dataset.bagianCategories || '{}'); } catch(e) { return {}; }
        })()
    } : {};

    // 3. Inisialisasi Searchable Dropdown untuk Pemilihan Part
    if (typeof initItemSearch === 'function') {
        initItemSearch('awalItemId', {
            placeholder: 'Ketik nama part / no part...',
            maxResults: 60
        });
    }

    // 4. Direct Print Cetak (Tanpa Buka Halaman / Tab Baru)
    $(document).on('click', '.btn-print-direct, #btnCetakDandory', function (e) {
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

    // 5. Panel Standar Referensi (Jika ada di DOM)
    if ($('#btnToggleStdPanel').length) {
        $('#btnToggleStdPanel').on('click', function () {
            $('#panelStdRef').toggle(150);
        });
    }

    // Hover Highlight antara Header Kolom Matriks dan Baris Panel Standar
    $(document).on('mouseenter', '.th-num-hover', function () {
        var phase = $(this).data('phase');
        var order = $(this).data('order');
        $('.std-row-hover[data-phase="' + phase + '"][data-order="' + order + '"]').addClass('bg-warning text-dark font-weight-bold');
    }).on('mouseleave', '.th-num-hover', function () {
        var phase = $(this).data('phase');
        var order = $(this).data('order');
        $('.std-row-hover[data-phase="' + phase + '"][data-order="' + order + '"]').removeClass('bg-warning text-dark font-weight-bold');
    });

    $(document).on('mouseenter', '.std-row-hover', function () {
        var phase = $(this).data('phase');
        var order = $(this).data('order');
        $('.th-num-hover[data-phase="' + phase + '"][data-order="' + order + '"]').addClass('bg-warning text-dark');
    }).on('mouseleave', '.std-row-hover', function () {
        var phase = $(this).data('phase');
        var order = $(this).data('order');
        $('.th-num-hover[data-phase="' + phase + '"][data-order="' + order + '"]').removeClass('bg-warning text-dark');
    });

    // 5. Smart Preset Saat Part Dipilih pada Modal Awal Dandory
    $('#awalItemId').on('change', function () {
        var itemId = $(this).val();
        if (!itemId) return;

        // Ambil histori sesi checksheet part ini sebelumnya (jika ada)
        $.ajax({
            url: config.partPresetUrl,
            type: 'GET',
            data: {
                operator_id: config.operatorId,
                item_id: itemId
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.success && res.preset && res.preset.awal_checks) {
                    var presetMap = res.preset.awal_checks;
                    var hasCustomPreset = false;

                    // Terapkan nilai preset ke radio button modal
                    Object.keys(presetMap).forEach(function (order) {
                        var val = presetMap[order];
                        var radio = $('input[name="awal_checks[' + order + ']"][value="' + val + '"]');
                        if (radio.length) {
                            radio.prop('checked', true);
                            var parentGroup = radio.closest('.btn-group-toggle');
                            parentGroup.find('label.btn').removeClass('active');
                            radio.closest('label.btn').addClass('active');

                            if (val === 'NA' || val === 'NG') {
                                hasCustomPreset = true;
                            }
                        }
                    });

                    if (hasCustomPreset && typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'info',
                            title: 'Smart Preset: Item - (NA) part ini dimuat otomatis.'
                        });
                    }
                }
            }
        });
    });

    // 6. Tombol Cepat "Set Semua ✓ (Dilakukan)"
    // Modal Awal
    $('#btnSetAllAwalOk').on('click', function () {
        $('#formAwalDandory input[type="radio"][value="OK"]').each(function () {
            $(this).prop('checked', true);
            var group = $(this).closest('.btn-group-toggle');
            group.find('label.btn').removeClass('active');
            $(this).closest('label.btn').addClass('active');
        });
    });

    // Modal Akhir
    $('#btnSetAllAkhirOk').on('click', function () {
        $('#formAkhirDandory input[type="radio"][value="OK"]').each(function () {
            $(this).prop('checked', true);
            var group = $(this).closest('.btn-group-toggle');
            group.find('label.btn').removeClass('active');
            $(this).closest('label.btn').addClass('active');
        });
    });

    // 7. Simpan Sesi Awal Dandory (Quick Modal 1)
    $('#formAwalDandory').on('submit', function (e) {
        e.preventDefault();

        var tableNo = $('#awalTableNo').val();
        if (!tableNo) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Meja Terlebih Dahulu',
                    text: 'Silakan pilih nomor meja tempat pemeriksaan aktual berlangsung.',
                    confirmButtonColor: '#4e73df',
                    confirmButtonText: 'Mengerti'
                }).then(function () {
                    $('#awalTableNo').focus();
                });
            } else {
                alert('Silakan pilih nomor meja tempat pemeriksaan aktual berlangsung.');
                $('#awalTableNo').focus();
            }
            return;
        }

        var itemId = $('#awalItemId').val();
        if (!itemId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Part Terlebih Dahulu',
                    text: 'Silakan ketik atau pilih nama part yang akan diperiksa.',
                    confirmButtonColor: '#4e73df',
                    confirmButtonText: 'Mengerti'
                }).then(function () {
                    var searchInput = $('.item-search-input, #awalItemId_search, #awalItemId');
                    if (searchInput.length) {
                        searchInput.first().focus();
                    }
                });
            } else {
                alert('Silakan ketik atau pilih nama part yang akan diperiksa.');
                $('#awalItemId').focus();
            }
            return;
        }

        var btnSubmit = $('#btnSubmitAwal');
        var originalBtnHtml = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        var formData = $(this).serializeArray();
        formData.push({ name: 'plant_code', value: config.plant });

        $.ajax({
            url: config.awalStoreUrl,
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#modalAwalDandory').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1400,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    btnSubmit.prop('disabled', false).html(originalBtnHtml);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function (xhr) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: errMsg });
                } else {
                    alert(errMsg);
                }
            }
        });
    });

    // 8. Buka Modal Selesaikan Akhir Dandory (Quick Modal 2)
    $(document).on('click', '.btn-open-akhir', function () {
        var id = $(this).data('id');
        var partName = $(this).data('part');
        var itemId = $(this).data('item-id');

        $('#akhirDandoryId').val(id);
        $('#akhirPartName').text(partName);
        $('#akhirNotes').val('');

        // Reset radio button modal akhir ke OK semua secara default
        $('#btnSetAllAkhirOk').trigger('click');

        // Jika part ini memiliki preset akhir, ambil
        if (itemId) {
            $.ajax({
                url: config.partPresetUrl,
                type: 'GET',
                data: {
                    operator_id: config.operatorId,
                    item_id: itemId
                },
                dataType: 'json',
                success: function (res) {
                    if (res && res.success && res.preset && res.preset.akhir_checks) {
                        var presetMap = res.preset.akhir_checks;
                        Object.keys(presetMap).forEach(function (order) {
                            var val = presetMap[order];
                            var radio = $('input[name="akhir_checks[' + order + ']"][value="' + val + '"]');
                            if (radio.length) {
                                radio.prop('checked', true);
                                var group = radio.closest('.btn-group-toggle');
                                group.find('label.btn').removeClass('active');
                                radio.closest('label.btn').addClass('active');
                            }
                        });
                    }
                }
            });
        }

        $('#modalAkhirDandory').modal('show');
    });

    // Simpan Modal Akhir Dandory
    $('#formAkhirDandory').on('submit', function (e) {
        e.preventDefault();

        var dandoryId = $('#akhirDandoryId').val();
        if (!dandoryId) return;

        var btnSubmit = $('#btnSubmitAkhir');
        var originalBtnHtml = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        var postUrl = config.akhirBaseUrl + '/' + dandoryId + '/akhir';

        $.ajax({
            url: postUrl,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#modalAkhirDandory').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sesi Selesai',
                            text: res.message,
                            timer: 1400,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    btnSubmit.prop('disabled', false).html(originalBtnHtml);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function (xhr) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: errMsg });
                } else {
                    alert(errMsg);
                }
            }
        });
    });

    // 9. Klik Langsung Sel Matriks Interaktif (Mode 2: Inline Grid Matrix Toggle)
    // Siklus Transisi: Kosong -> ✓ (OK) -> ✕ (NG) -> - (NA) -> Kosong
    $(document).on('click', '.dandory-cell', function () {
        var cell = $(this);
        var id = cell.data('id');
        var phase = cell.data('phase');
        var order = cell.data('order');
        var currentStatus = cell.data('status') || '';

        // Tentukan nilai berikutnya
        var nextStatus = null;
        var nextChar = '';
        var nextClass = '';

        if (!currentStatus || currentStatus === '') {
            nextStatus = 'OK';
            nextChar = '✓';
            nextClass = 'cell-ok';
        } else if (currentStatus === 'OK') {
            nextStatus = 'NG';
            nextChar = '✕';
            nextClass = 'cell-ng';
        } else if (currentStatus === 'NG') {
            nextStatus = 'NA';
            nextChar = '-';
            nextClass = 'cell-na';
        } else if (currentStatus === 'NA') {
            nextStatus = null;
            nextChar = '';
            nextClass = '';
        }

        // Simpan nilai lama untuk rollback jika error
        var oldChar = cell.text().trim();
        var oldStatus = currentStatus;
        var oldClass = cell.attr('class');

        // Optimistic UI Update (Responsif tanpa delay)
        cell.removeClass('cell-ok cell-ng cell-na');
        if (nextClass) cell.addClass(nextClass);
        cell.text(nextChar);
        cell.data('status', nextStatus || '');

        var postUrl = config.akhirBaseUrl + '/' + id + '/toggle-cell';

        $.ajax({
            url: postUrl,
            type: 'POST',
            data: {
                phase: phase,
                order_no: order,
                status: nextStatus
            },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    // Rollback UI
                    cell.attr('class', oldClass);
                    cell.text(oldChar);
                    cell.data('status', oldStatus);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                }
            },
            error: function (xhr) {
                // Rollback UI
                cell.attr('class', oldClass);
                cell.text(oldChar);
                cell.data('status', oldStatus);
                var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal memperbarui sel.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Kunci Akses', text: errMsg });
                } else {
                    alert(errMsg);
                }
            }
        });
    });

    // 10. Seleksi Baris Massal (Check All & Floating Bulk Menu Standar In-Process)
    var checkAllBtn = $('#checkAllRows');
    var countDisplay = $('#checkedCountDisplay');
    var bulkMenu = $('#bulkActionMenu');
    var bulkSelectedCount = $('#bulkSelectedCount');
    var btnBulkApproveFloating = $('#btnBulkApproveFloating');
    var btnBulkDeleteFloating = $('#btnBulkDeleteFloating');

    function updateBulkSelection() {
        var rowCheckboxes = $('.row-checkbox');
        var checkedBoxes = $('.row-checkbox:checked');
        var checkedCount = checkedBoxes.length;

        if (countDisplay.length) countDisplay.text(checkedCount);
        if (bulkSelectedCount.length) bulkSelectedCount.text(checkedCount);

        if (rowCheckboxes.length > 0 && checkAllBtn.length > 0) {
            checkAllBtn.prop('checked', checkedCount === rowCheckboxes.length);
        }

        if (checkedCount > 0) {
            bulkMenu.fadeIn(200);
        } else {
            bulkMenu.fadeOut(200);
        }

        rowCheckboxes.each(function () {
            var row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                row.css('background-color', 'rgba(78, 115, 223, 0.06)');
            } else {
                row.css('background-color', '');
            }
        });
    }

    if (checkAllBtn.length > 0) {
        checkAllBtn.on('change', function () {
            var isChecked = $(this).prop('checked');
            $('.row-checkbox').prop('checked', isChecked);
            updateBulkSelection();
        });
    }

    $(document).on('change', '.row-checkbox', function () {
        updateBulkSelection();
    });

    // Verifikasi Massal via Floating Menu
    btnBulkApproveFloating.on('click', function () {
        var checkedBoxes = $('.row-checkbox:checked');
        var selectedIds = checkedBoxes.map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        var incompleteBoxes = checkedBoxes.filter('[data-complete="0"]');
        if (incompleteBoxes.length === checkedBoxes.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Bisa Approval',
                text: 'Semua data checksheet yang dipilih belum melengkapi pemeriksaan Dandory Awal dan Akhir. Approval hanya dapat dilakukan jika pemeriksaan sudah lengkap.',
                confirmButtonColor: '#4e73df',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        var confirmHtml = '<p class="mb-2">Sebanyak <strong>' + selectedIds.length + ' data</strong> sesi pemeriksaan Dandory yang dipilih akan diverifikasi sekaligus atas nama Anda.</p>';
        if (incompleteBoxes.length > 0) {
            confirmHtml += '<div class="alert alert-warning py-1 px-2 small mb-0 text-left"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>Perhatian:</strong> Ada ' + incompleteBoxes.length + ' data yang belum lengkap (Dandory Awal/Akhir) dan akan otomatis dilewati.</div>';
        }

        Swal.fire({
            title: 'Verifikasi Data Terpilih?',
            html: confirmHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1cc88a',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-check-double mr-1"></i> Ya, Verifikasi!',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: config.bulkVerifyUrl,
                    type: 'POST',
                    data: { ids: selectedIds },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Memproses Verifikasi...',
                            text: 'Mohon tunggu sejenak.',
                            allowOutsideClick: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Verifikasi Berhasil',
                                text: res.message,
                                timer: 1400,
                                showConfirmButton: false
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function (xhr) {
                        var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal verifikasi massal.';
                        Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: errMsg });
                    }
                });
            }
        });
    });

    // Hapus Massal via Floating Menu (Admin Only)
    btnBulkDeleteFloating.on('click', function () {
        var selectedIds = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Hapus Data Terpilih?',
            text: 'PERINGATAN: ' + selectedIds.length + ' data sesi checksheet Dandory yang dipilih akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: config.bulkDestroyUrl,
                    type: 'POST',
                    data: { ids: selectedIds },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Menghapus Data...',
                            allowOutsideClick: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Dihapus',
                                text: res.message,
                                timer: 1400,
                                showConfirmButton: false
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function (xhr) {
                        var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus data terpilih.';
                        Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
                    }
                });
            }
        });
    });

    // Helper: Render HTML Tombol Kolom Aksi Sesuai Status Approval
    function buildActionButtonsHtml(id, isApproved, isRejected, partName, isComplete) {
        if (typeof isComplete === 'undefined') isComplete = true;
        var html = '<div class="dandory-action-group">';
        if (isApproved) {
            html += '<button type="button" class="btn btn-outline-secondary btn-xs rounded-pill px-2 shadow-xs btn-unapprove-row" data-id="' + id + '" style="font-size:0.6rem; padding: 2px 6px;" title="Batalkan Approval"><i class="fas fa-undo mr-1"></i> Batal</button>';
        } else {
            // Tombol Verifikasi Leader (Approve & Reject): Hanya muncul jika Dandory Awal & Akhir sudah lengkap
            if (isComplete) {
                html += '<button type="button" class="btn btn-success btn-xs rounded-pill px-2 shadow-xs btn-approve-row" data-id="' + id + '" style="font-size:0.6rem; padding: 2px 6px;" title="Approve Sesi Dandory"><i class="fas fa-check mr-1"></i> Approve</button>';
                html += '<button type="button" class="btn btn-danger btn-xs rounded-pill px-2 shadow-xs btn-reject-row" data-id="' + id + '" data-part="' + (partName || '') + '" style="font-size:0.6rem; padding: 2px 6px;" title="' + (isRejected ? 'Ubah Alasan Reject' : 'Reject Sesi Dandory') + '"><i class="fas fa-' + (isRejected ? 'edit' : 'times') + ' mr-1"></i> ' + (isRejected ? 'Ubah Reject' : 'Reject') + '</button>';
            }
        }
        if (config.userRole === 'admin') {
            html += '<button type="button" class="btn btn-outline-danger btn-xs rounded-circle shadow-xs btn-delete-dandory" data-id="' + id + '" data-part="' + (partName || '') + '" style="width:24px; height:24px; padding:0;" title="Hapus Sesi Dandory"><i class="fas fa-trash-alt fa-xs"></i></button>';
        }
        html += '</div>';
        return html;
    }

    // 11. Approve Single Row (Skema Approval Checksheet)
    $(document).on('click', '.btn-approve-row', function (e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var cell = $('#approvalCell_' + id);
        var actionCell = $('#actionCell_' + id);

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Approve...');

        $.ajax({
            url: config.akhirBaseUrl + '/' + id + '/verify',
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    var html = '<span class="badge badge-success px-2 py-1 shadow-sm"><i class="fas fa-check-double mr-1"></i> APPROVED</span>' +
                               '<br><small class="text-muted">oleh ' + (res.leader_name || '') + '</small>' +
                               (res.leader_time ? '<br><small class="text-muted">' + res.leader_time + '</small>' : '');
                    cell.html(html);

                    actionCell.html(buildActionButtonsHtml(id, true, false, null));

                    var checkbox = $('#checkRow' + id);
                    if (checkbox.length) {
                        checkbox.data('approved', '1');
                    }

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    Toast.fire({
                        icon: 'success',
                        title: res.message
                    });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Approve');
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Approve');
                var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal approve baris ini.';
                Swal.fire({ icon: 'warning', title: 'Belum Bisa Approval', text: errMsg });
            }
        });
    });

    // 12. Batal Approval Single Row
    $(document).on('click', '.btn-unapprove-row', function (e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var cell = $('#approvalCell_' + id);
        var actionCell = $('#actionCell_' + id);

        Swal.fire({
            title: 'Batalkan Approval?',
            text: 'Status sesi pemeriksaan ini akan dikembalikan menjadi PENDING.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Batalkan Approval',
            cancelButtonText: 'Kembali'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: config.akhirBaseUrl + '/' + id + '/verify',
                    type: 'POST',
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            var isComplete = $('#checkRow' + id).data('complete') == '1';
                            var html = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> PENDING</span>';
                            cell.html(html);

                            actionCell.html(buildActionButtonsHtml(id, false, false, null, isComplete));

                            var checkbox = $('#checkRow' + id);
                            if (checkbox.length) {
                                checkbox.data('approved', '0');
                            }

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            Toast.fire({
                                icon: 'info',
                                title: res.message
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function (xhr) {
                        var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal membatalkan approval.';
                        Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: errMsg });
                    }
                });
            }
        });
    });

    // 13. Reject Pemeriksaan oleh Leader
    $(document).on('click', '.btn-reject-row', function (e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var partName = btn.data('part') || 'Part ini';
        var cell = $('#approvalCell_' + id);
        var actionCell = $('#actionCell_' + id);

        Swal.fire({
            title: 'Reject Sesi Dandory',
            html: '<div class="text-left">' +
                  '<p class="small text-muted mb-2">Tuliskan alasan penolakan/ketidaksesuaian untuk pemeriksaan Dandory <strong>' + partName + '</strong>:</p>' +
                  '<textarea id="swalRejectRemarks" class="form-control form-control-sm" rows="3" placeholder="Contoh: Parameter item 3 belum sesuai standard..."></textarea>' +
                  '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-times mr-1"></i> Reject Data',
            cancelButtonText: 'Batal',
            preConfirm: function () {
                var remarks = $('#swalRejectRemarks').val().trim();
                if (!remarks) {
                    Swal.showValidationMessage('Alasan penolakan / keterangan wajib diisi!');
                    return false;
                }
                return remarks;
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                var remarks = result.value;
                $.ajax({
                    url: config.akhirBaseUrl + '/' + id + '/reject',
                    type: 'POST',
                    data: { remarks: remarks },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Memproses Reject...',
                            allowOutsideClick: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        if (res.success) {
                            var html = '<span class="badge badge-danger px-2 py-1 shadow-sm"><i class="fas fa-times-circle mr-1"></i> REJECTED</span>' +
                                       '<br><small class="text-muted">oleh ' + (res.rejector_name || '') + '</small>' +
                                       (res.rejected_at ? '<br><small class="text-muted">' + res.rejected_at + '</small>' : '') +
                                       (res.rejection_remarks ? '<br><small class="text-danger font-italic d-inline-block text-truncate" style="max-width:110px;" title="Alasan: ' + res.rejection_remarks + '">* ' + res.rejection_remarks + '</small>' : '');
                            cell.html(html);

                            var isComplete = $('#checkRow' + id).data('complete') == '1';
                            actionCell.html(buildActionButtonsHtml(id, false, true, partName, isComplete));

                            var checkbox = $('#checkRow' + id);
                            if (checkbox.length) {
                                checkbox.data('approved', '0');
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Status Diperbarui',
                                text: res.message,
                                timer: 1400,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function (xhr) {
                        var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal me-reject baris ini.';
                        Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: errMsg });
                    }
                });
            }
        });
    });

    // 12. Hapus Sesi Dandory
    $(document).on('click', '.btn-delete-dandory', function () {
        var id = $(this).data('id');
        var partName = $(this).data('part');
        var row = $('#row-dandory-' + id);

        var confirmCallback = function () {
            var deleteUrl = config.akhirBaseUrl + '/' + id;
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        row.fadeOut(250, function () {
                            $(this).remove();
                            // Jika tabel menjadi kosong
                            if ($('.table-dandory tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                        if (typeof Swal !== 'undefined') {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            Toast.fire({ icon: 'success', title: res.message });
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    }
                },
                error: function (xhr) {
                    var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus data.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: errMsg });
                    } else {
                        alert(errMsg);
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Sesi Dandory?',
                text: 'Anda akan menghapus data pemeriksaan untuk part: ' + partName,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus Data',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    confirmCallback();
                }
            });
        } else {
            if (confirm('Hapus sesi pemeriksaan Dandory untuk: ' + partName + '?')) {
                confirmCallback();
            }
        }
    });

    // Helper centang checkbox kategori
    function setOperatorCategoryCheckboxes(categoryIds) {
        var catSet = {};
        (categoryIds || []).forEach(function (id) {
            catSet[parseInt(id, 10)] = true;
        });
        $('.cfg-cat-checkbox').each(function () {
            var val = parseInt($(this).val(), 10);
            $(this).prop('checked', !!catSet[val]);
        });
    }

    // A. Ketika ganti Operator di modal: sinkronkan bagian & kategori miliknya / riwayat bagian
    $('#cfgOperatorId').on('change', function () {
        var opId = $(this).val();
        var opData = config.opConfigs ? config.opConfigs[opId] : null;
        if (opData) {
            if (opData.bagian) {
                $('#cfgBagian').val(opData.bagian);
            }
            var cats = opData.allowed_categories || [];
            // Jika belum ada kategori tapi posisi/bagiannya sudah ada riwayat kategori, ikuti riwayat posisi
            if (cats.length === 0 && opData.bagian && config.bagianCategories && config.bagianCategories[opData.bagian]) {
                cats = config.bagianCategories[opData.bagian];
            }
            setOperatorCategoryCheckboxes(cats);
        }
    });

    // B. Ketika ganti Bagian di modal: otomatis ikuti kategori yang pernah dipilih pada bagian tersebut
    $('#cfgBagian').on('change', function () {
        var selectedBagian = $(this).val();
        if (selectedBagian && config.bagianCategories && config.bagianCategories[selectedBagian]) {
            var cats = config.bagianCategories[selectedBagian];
            setOperatorCategoryCheckboxes(cats);

            // Tampilkan notifikasi halus di bawah dropdown
            $('#cfgInheritedFeedback').remove();
            var feedback = $(
                '<div id="cfgInheritedFeedback" class="text-primary font-weight-bold mt-1" style="font-size: 0.65rem;">' +
                '   <i class="fas fa-magic mr-1"></i> Kategori otomatis mengikuti riwayat posisi ' + selectedBagian +
                '</div>'
            );
            $('#cfgBagian').after(feedback);
            setTimeout(function () {
                feedback.fadeOut(400, function () { $(this).remove(); });
            }, 3000);
        }
    });

    // C. Saat modal dibuka: jika belum ada kategori yang dicentang, cek apakah bagian saat ini punya riwayat kategori
    $('#modalOperatorMeja').on('shown.bs.modal', function () {
        var currentBagian = $('#cfgBagian').val();
        var anyChecked = $('.cfg-cat-checkbox:checked').length > 0;
        if (!anyChecked && currentBagian && config.bagianCategories && config.bagianCategories[currentBagian]) {
            setOperatorCategoryCheckboxes(config.bagianCategories[currentBagian]);
        }
    });

    // 13. Kelola Operator Config (Posisi & Kategori Part)
    $('#formOperatorConfig').on('submit', function (e) {
        e.preventDefault();

        var btnSubmit = $('#btnSaveOperatorConfig');
        var originalBtnHtml = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: config.opCfgSaveUrl,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                if (res.success) {
                    var savedOpId = $('#cfgOperatorId').val();
                    var savedBagian = $('#cfgBagian').val();
                    var savedCats = $('.cfg-cat-checkbox:checked').map(function () {
                        return parseInt($(this).val(), 10);
                    }).get();

                    if (config.opConfigs) {
                        if (!config.opConfigs[savedOpId]) config.opConfigs[savedOpId] = {};
                        config.opConfigs[savedOpId].bagian = savedBagian;
                        config.opConfigs[savedOpId].allowed_categories = savedCats;
                    }
                    if (config.bagianCategories && savedBagian && savedCats.length > 0) {
                        config.bagianCategories[savedBagian] = savedCats;
                    }

                    $('#modalOperatorMeja').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Disimpan',
                            text: res.message,
                            timer: 1400,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                }
            },
            error: function (xhr) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan pengaturan operator.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Kesalahan', text: errMsg });
                }
            }
        });
    });

    // 14. Kelola Daftar Meja Plant (Tambah & Hapus Meja)
    $('#btnAddTableItem').on('click', function () {
        var count = $('#tableListContainer .table-item-row').length + 1;
        var newRow = $(
            '<div class="input-group input-group-sm mb-1 table-item-row">' +
            '    <input type="text" name="tables[]" class="form-control form-control-sm border" placeholder="Contoh: Meja ' + count + '" required>' +
            '    <div class="input-group-append">' +
            '        <button type="button" class="btn btn-outline-danger btn-remove-table" title="Hapus Meja">' +
            '            <i class="fas fa-times"></i>' +
            '        </button>' +
            '    </div>' +
            '</div>'
        );
        $('#tableListContainer').append(newRow);
        newRow.find('input').focus();
    });

    $(document).on('click', '.btn-remove-table', function () {
        if ($('#tableListContainer .table-item-row').length <= 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Minimal 1 Meja', text: 'Plant harus memiliki minimal satu nomor meja.' });
            }
            return;
        }
        $(this).closest('.table-item-row').remove();
    });

    $('#formPlantTables').on('submit', function (e) {
        e.preventDefault();

        var btnSubmit = $('#btnSavePlantTables');
        var originalBtnHtml = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: config.tablesSaveUrl,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                if (res.success) {
                    $('#modalOperatorMeja').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Daftar Meja Disimpan',
                            text: res.message,
                            timer: 1400,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                }
            },
            error: function (xhr) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan daftar meja.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Kesalahan', text: errMsg });
                }
            }
        });
    });

    // 15. Kelola Master Item Parameter Standar ISO (SPV Ke Atas)
    $(document).on('click', '.btn-edit-master', function () {
        var id = $(this).data('id');
        var phase = $(this).data('phase');
        var order = $(this).data('order');
        var itemCheck = $(this).data('check');
        var standard = $(this).data('standard');

        $('#masterItemId').val(id);
        $('#masterItemPhase').val(phase);
        $('#masterItemOrder').val(order);
        $('#masterItemCheck').val(itemCheck);
        $('#masterItemStandard').val(standard);

        $('#formMasterItemTitle').text('Edit Parameter #' + order + ' (' + phase.toUpperCase() + ')');
        $('#btnCancelEditMaster').removeClass('d-none');
    });

    $('#btnCancelEditMaster').on('click', function () {
        $('#formMasterItem')[0].reset();
        $('#masterItemId').val('');
        $('#formMasterItemTitle').text('Tambah Parameter');
        $(this).addClass('d-none');
    });

    $('#formMasterItem').on('submit', function (e) {
        e.preventDefault();

        var itemId = $('#masterItemId').val();
        var isEdit = !!itemId;
        var targetUrl = isEdit ? (config.masterItemUrl + '/' + itemId) : config.masterItemUrl;
        var methodType = isEdit ? 'PUT' : 'POST';

        var btnSubmit = $('#btnSaveMasterItem');
        var originalBtnHtml = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: targetUrl,
            type: methodType,
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                if (res.success) {
                    $('#modalMasterItem').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Parameter Disimpan',
                            text: res.message,
                            timer: 1400,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                }
            },
            error: function (xhr) {
                btnSubmit.prop('disabled', false).html(originalBtnHtml);
                var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan master parameter.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Kesalahan', text: errMsg });
                }
            }
        });
    });

    // Hapus Master Item Parameter Standar (Khusus Admin)
    $(document).on('click', '.btn-delete-master', function () {
        var id = $(this).data('id');
        var checkName = $(this).data('check');
        var deleteUrl = config.masterItemUrl + '/' + id;

        var doDelete = function () {
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        $('#modalMasterItem').modal('hide');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Dihapus',
                                text: res.message,
                                timer: 1400,
                                showConfirmButton: false
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    }
                },
                error: function (xhr) {
                    var errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus parameter.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: errMsg });
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Parameter Standar?',
                text: 'Parameter "' + checkName + '" akan dihapus dari checksheet Dandory.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus Parameter',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        } else {
            if (confirm('Hapus parameter standar: ' + checkName + '?')) {
                doDelete();
            }
        }
    });
});

