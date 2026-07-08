$(document).ready(function() {
    if (typeof initItemSearch === 'function') {
        initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...' });
    }

    const config = window.__DURABILITY_PLATING_REPORT__ || {};

    $('.btn-edit-thickness').click(function() {
        let item = $(this).data('item');
        let partName = $(this).data('part');
        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        
        $('#formEditThickness').attr('action', url);
        
        $('#edit_thickness_part_name').val(partName);
        $('#edit_tanggal_cek').val(item.tanggal_cek);
        $('#edit_production_date').val(item.production_date);
        $('#edit_shift').val(item.shift);
        $('#edit_lot_no').val(item.lot_no);
        $('#edit_actual_cr').val(item.actual_cr);
        $('#edit_actual_ni').val(item.actual_ni);
        $('#edit_actual_cu').val(item.actual_cu);
        
        $('#edit_actual_corrodkote_waktu').val(item.actual_corrodkote_waktu);
        $('#edit_standar_jam_corrodkote').val(item.standar_jam_corrodkote);
        
        $('#edit_actual_cass_waktu').val(item.actual_cass_waktu);
        $('#edit_standar_jam_cass').val(item.standar_jam_cass);
        
        $('#edit_actual_salt_spray_waktu').val(item.actual_salt_spray_waktu);
        $('#edit_standar_jam_salt_spray').val(item.standar_jam_salt_spray);
        
        $('#edit_actual_porecount').val(item.actual_porecount);
        
        $('#edit_tgl_masuk').val(item.tgl_masuk);
        if (item.jam_masuk) {
            $('#edit_jam_masuk').val(item.jam_masuk.substring(0, 5));
        }
        $('#edit_tgl_keluar').val(item.tgl_keluar);
        if (item.jam_keluar) {
            $('#edit_jam_keluar').val(item.jam_keluar.substring(0, 5));
        }
        
        $('#edit_result_judgment').val(item.result_judgment ?? '-');
        $('#edit_description').val(item.description);
        
        
        editStdCr = parseFloat($(this).data('stdcr')) || 0;
        editStdNi = parseFloat($(this).data('stdni')) || 0;
        editStdCu = parseFloat($(this).data('stdcu')) || 0;

        let originalBeforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let originalAfterUrl  = item.evidence_after  ? config.baseUrl + item.evidence_after  : null;

        showEvidenceCard('edit_evidence_before_preview', 'edit_evidence_before_preview_wrap',
            'edit_evidence_before_empty', 'btn_delete_evidence_before', 'edit_evidence_before_time',
            originalBeforeUrl, beforeTimeFormatted);
        showEvidenceCard('edit_evidence_after_preview', 'edit_evidence_after_preview_wrap',
            'edit_evidence_after_empty', 'btn_delete_evidence_after', 'edit_evidence_after_time',
            originalAfterUrl, afterTimeFormatted);

        // Store originals on buttons for X restore logic
        $('#btn_delete_evidence_before').data({ originalUrl: originalBeforeUrl, hasNewFile: false });
        $('#btn_delete_evidence_after').data({ originalUrl: originalAfterUrl,  hasNewFile: false });

        // Reset file inputs and delete flags
        $('#input_evidence_before').val('');
        $('#input_evidence_after').val('');
        $('#delete_evidence_before').val('0');
        $('#delete_evidence_after').val('0');

        $('#modalEditThickness').modal('show');
    });

    // Smart X button: if new file staged → cancel & restore original; else → delete DB photo
    function handleDeleteBtn(btnId, previewId, wrapId, emptyId, timeId, inputId, deleteFlagId) {
        $('#' + btnId).on('click', function() {
            var d = $(this).data();
            if (d.hasNewFile) {
                // Cancel new selection → restore original
                $('#' + inputId).val('');
                $(this).data('hasNewFile', false);
                if (d.originalUrl) {
                    $('#' + previewId).attr('src', d.originalUrl);
                    $('#' + wrapId).show();
                    $('#' + emptyId).hide();
                    $('#' + deleteFlagId).val('0');
                } else {
                    $('#' + wrapId).hide();
                    $('#' + emptyId).css('display', 'flex');
                    $(this).addClass('d-none').css('display', '');
                }
            } else {
                // Delete existing DB photo
                $('#' + inputId).val('');
                $('#' + wrapId).hide();
                $('#' + emptyId).css('display', 'flex');
                $(this).addClass('d-none').css('display', '');
                $('#' + deleteFlagId).val('1');
            }
        });
    }
    handleDeleteBtn('btn_delete_evidence_before', 'edit_evidence_before_preview', 'edit_evidence_before_preview_wrap',
        'edit_evidence_before_empty', 'edit_evidence_before_time', 'input_evidence_before', 'delete_evidence_before');
    handleDeleteBtn('btn_delete_evidence_after',  'edit_evidence_after_preview',  'edit_evidence_after_preview_wrap',
        'edit_evidence_after_empty',  'edit_evidence_after_time',  'input_evidence_after',  'delete_evidence_after');

    // Live preview when new file chosen — also show X and mark hasNewFile
    function bindLivePreview(inputId, previewId, wrapId, emptyId, deleteBtnId) {
        $('#' + inputId).off('change.preview').on('change.preview', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + previewId).attr('src', e.target.result);
                    $('#' + wrapId).show();
                    $('#' + emptyId).hide();
                    $('#' + deleteBtnId).data('hasNewFile', true)
                        .removeClass('d-none').css('display', 'flex');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    bindLivePreview('input_evidence_before', 'edit_evidence_before_preview', 'edit_evidence_before_preview_wrap', 'edit_evidence_before_empty', 'btn_delete_evidence_before');
    bindLivePreview('input_evidence_after',  'edit_evidence_after_preview',  'edit_evidence_after_preview_wrap',  'edit_evidence_after_empty',  'btn_delete_evidence_after');

    bindLivePreview('input_new_evidence_before', 'new_evidence_before_preview', 'new_evidence_before_preview_wrap', 'new_evidence_before_empty', 'btn_delete_new_evidence_before');
    bindLivePreview('input_new_evidence_after',  'new_evidence_after_preview',  'new_evidence_after_preview_wrap',  'new_evidence_after_empty',  'btn_delete_new_evidence_after');

    // Simple X handler for New Data Modal
    function handleNewDataDeleteBtn(btnId, previewId, wrapId, emptyId, inputId) {
        $('#' + btnId).on('click', function() {
            $('#' + inputId).val('');
            $('#' + wrapId).hide();
            $('#' + emptyId).css('display', 'flex');
            $(this).addClass('d-none').css('display', '');
        });
    }
    handleNewDataDeleteBtn('btn_delete_new_evidence_before', 'new_evidence_before_preview', 'new_evidence_before_preview_wrap', 'new_evidence_before_empty', 'input_new_evidence_before');
    handleNewDataDeleteBtn('btn_delete_new_evidence_after',  'new_evidence_after_preview',  'new_evidence_after_preview_wrap',  'new_evidence_after_empty',  'input_new_evidence_after');

    const inputModals = ['corrodkote', 'cass', 'salt_spray', 'porecount'];
    inputModals.forEach(t => {
        bindLivePreview(`input_${t}_evidence_before`, `${t}_evidence_before_preview`, `${t}_evidence_before_preview_wrap`, `${t}_evidence_before_empty`, `btn_delete_${t}_evidence_before`);
        bindLivePreview(`input_${t}_evidence_after`,  `${t}_evidence_after_preview`,  `${t}_evidence_after_preview_wrap`,  `${t}_evidence_after_empty`,  `btn_delete_${t}_evidence_after`);
        
        handleNewDataDeleteBtn(`btn_delete_${t}_evidence_before`, `${t}_evidence_before_preview`, `${t}_evidence_before_preview_wrap`, `${t}_evidence_before_empty`, `input_${t}_evidence_before`);
        handleNewDataDeleteBtn(`btn_delete_${t}_evidence_after`,  `${t}_evidence_after_preview`,  `${t}_evidence_after_preview_wrap`,  `${t}_evidence_after_empty`,  `input_${t}_evidence_after`);
    });

    function formatDbDate(dbDateStr) {
        if (!dbDateStr) return null;
        let dateObj = new Date(dbDateStr);
        if (isNaN(dateObj)) return null;
        let d = ('0' + dateObj.getDate()).slice(-2);
        let m = ('0' + (dateObj.getMonth() + 1)).slice(-2);
        let y = dateObj.getFullYear();
        let h = ('0' + dateObj.getHours()).slice(-2);
        let min = ('0' + dateObj.getMinutes()).slice(-2);
        return d + '-' + m + '-' + y + ' ' + h + ':' + min;
    }

    function showEvidenceCard(previewId, wrapId, emptyId, deleteBtnId, timeId, url, time) {
        if (url) {
            $('#' + previewId).attr('src', url);
            $('#' + wrapId).show();
            $('#' + emptyId).hide();
            $('#' + deleteBtnId).removeClass('d-none').css('display', 'flex');
            if(timeId) {
                $('#' + timeId).text(time ? 'Diunggah: ' + time : '').removeClass('d-none');
            }
        } else {
            $('#' + previewId).attr('src', '');
            $('#' + wrapId).hide();
            $('#' + emptyId).css('display', 'flex');
            $('#' + deleteBtnId).addClass('d-none').css('display', '');
            if(timeId) {
                $('#' + timeId).text('').addClass('d-none');
            }
        }
    }

    // Auto judgment logic for Edit Thickness Modal
    var editStdCr = 0, editStdNi = 0, editStdCu = 0;
    $('.edit-actual-thickness-input').on('keyup change', function() {
        var actCr = parseFloat($('#edit_actual_cr').val());
        var actNi = parseFloat($('#edit_actual_ni').val());
        var actCu = parseFloat($('#edit_actual_cu').val());
        
        // Only judge if all inputs are filled and valid numbers
        if (!isNaN(actCr) && !isNaN(actNi) && !isNaN(actCu)) {
            if (actCr >= editStdCr && actNi >= editStdNi && actCu >= editStdCu) {
                $('#edit_result_judgment').val('OK');
            } else {
                $('#edit_result_judgment').val('NG');
            }
        } else {
            $('#edit_result_judgment').val('-');
        }
    });

    $('.btn-input-corrodkote').click(function() {
        let item = $(this).data('item');
        
        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputCorrodkote').attr('action', url);
        
        $('#corrodkote_report_id').val(item.id);
        $('#corrodkote_part_name').val($(this).data('part'));
        $('#corrodkote_customer').val($(this).data('customer'));
        $('#corrodkote_std').val($(this).data('std'));
        $('#corrodkote_standard_time').val($(this).data('time'));
        $('#corrodkote_produksi').val(item.production_date);
        $('#corrodkote_shift').val(item.shift);
        if (item.lot_no) {
            $('#corrodkote_lot').val(item.lot_no).prop('readonly', true).addClass('bg-light');
        } else {
            $('#corrodkote_lot').val('').prop('readonly', false).removeClass('bg-light');
        }

        // Tgl/Jam Keluar selalu readonly (auto-calc)
        $('#corrodkote_tgl_keluar, #corrodkote_jam_keluar').prop('readonly', true);

        // Reset Tgl/Jam Masuk & Keluar
        $('#corrodkote_tgl_masuk, #corrodkote_jam_masuk').val('');
        $('#corrodkote_tgl_keluar').val('');
        $('#corrodkote_jam_keluar').val('');

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl  = item.evidence_after  ? config.baseUrl + item.evidence_after  : null;
        showEvidenceCard('corrodkote_evidence_before_preview', 'corrodkote_evidence_before_preview_wrap', 'corrodkote_evidence_before_empty', 'btn_delete_corrodkote_evidence_before', null, beforeUrl, null);
        showEvidenceCard('corrodkote_evidence_after_preview', 'corrodkote_evidence_after_preview_wrap', 'corrodkote_evidence_after_empty', 'btn_delete_corrodkote_evidence_after', null, afterUrl, null);

        $('#modalInputCorrodkote').modal('show');
    });

    $('.btn-input-cass').click(function() {
        let item = $(this).data('item');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputCass').attr('action', url);

        $('#cass_report_id').val(item.id);
        $('#cass_part_name').val($(this).data('part'));
        $('#cass_customer').val($(this).data('customer'));
        $('#cass_std').val($(this).data('std'));
        $('#cass_standard_time').val($(this).data('time'));
        $('#cass_produksi').val(item.production_date);
        $('#cass_shift').val(item.shift);
        
        if (item.lot_no) {
            $('#cass_lot').val(item.lot_no).prop('readonly', true).addClass('bg-light');
        } else {
            $('#cass_lot').val('').prop('readonly', false).removeClass('bg-light');
        }

        // Tgl/Jam Keluar selalu readonly (auto-calc)
        $('#cass_tgl_keluar, #cass_jam_keluar').prop('readonly', true);

        // Reset Tgl/Jam Masuk & Keluar
        $('#cass_tgl_masuk, #cass_jam_masuk').val('');
        $('#cass_tgl_keluar').val('');
        $('#cass_jam_keluar').val('');

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl  = item.evidence_after  ? config.baseUrl + item.evidence_after  : null;
        showEvidenceCard('cass_evidence_before_preview', 'cass_evidence_before_preview_wrap', 'cass_evidence_before_empty', 'btn_delete_cass_evidence_before', null, beforeUrl, null);
        showEvidenceCard('cass_evidence_after_preview', 'cass_evidence_after_preview_wrap', 'cass_evidence_after_empty', 'btn_delete_cass_evidence_after', null, afterUrl, null);

        $('#modalInputCass').modal('show');
    });

    $('.btn-input-salt-spray').click(function() {
        let item = $(this).data('item');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputSaltSpray').attr('action', url);

        $('#salt_report_id').val(item.id);
        $('#salt_part_name').val($(this).data('part'));
        $('#salt_customer').val($(this).data('customer'));
        $('#salt_std').val($(this).data('std'));
        $('#salt_standard_time').val($(this).data('time'));
        $('#salt_produksi').val(item.production_date);
        $('#salt_shift').val(item.shift);
        
        if (item.lot_no) {
            $('#salt_lot').val(item.lot_no).prop('readonly', true).addClass('bg-light');
        } else {
            $('#salt_lot').val('').prop('readonly', false).removeClass('bg-light');
        }

        // Tgl/Jam Keluar selalu readonly (auto-calc)
        $('#salt_tgl_keluar, #salt_jam_keluar').prop('readonly', true);

        // Reset Tgl/Jam Masuk & Keluar
        $('#salt_tgl_masuk, #salt_jam_masuk').val('');
        $('#salt_tgl_keluar').val('');
        $('#salt_jam_keluar').val('');

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl  = item.evidence_after  ? config.baseUrl + item.evidence_after  : null;
        showEvidenceCard('salt_spray_evidence_before_preview', 'salt_spray_evidence_before_preview_wrap', 'salt_spray_evidence_before_empty', 'btn_delete_salt_spray_evidence_before', null, beforeUrl, null);
        showEvidenceCard('salt_spray_evidence_after_preview', 'salt_spray_evidence_after_preview_wrap', 'salt_spray_evidence_after_empty', 'btn_delete_salt_spray_evidence_after', null, afterUrl, null);

        $('#modalInputSaltSpray').modal('show');
    });

    $('.btn-input-porecount').click(function() {
        let item = $(this).data('item');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputPorecount').attr('action', url);

        $('#porecount_report_id').val(item.id);
        $('#porecount_part_name').val($(this).data('part'));
        $('#porecount_customer').val($(this).data('customer'));
        $('#porecount_std').val($(this).data('std'));
        $('#porecount_standard_min').val($(this).data('stdmin'));
        $('#porecount_produksi').val(item.production_date);
        $('#porecount_shift').val(item.shift);
        
        if (item.lot_no) {
            $('#porecount_lot').val(item.lot_no).prop('readonly', true).addClass('bg-light');
        } else {
            $('#porecount_lot').val('').prop('readonly', false).removeClass('bg-light');
        }

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl  = item.evidence_after  ? config.baseUrl + item.evidence_after  : null;
        showEvidenceCard('porecount_evidence_before_preview', 'porecount_evidence_before_preview_wrap', 'porecount_evidence_before_empty', 'btn_delete_porecount_evidence_before', null, beforeUrl, null);
        showEvidenceCard('porecount_evidence_after_preview', 'porecount_evidence_after_preview_wrap', 'porecount_evidence_after_empty', 'btn_delete_porecount_evidence_after', null, afterUrl, null);

        $('#modalInputPorecount').modal('show');
    });

    // Bulk Delete Logic
    const checkAllBtn = $('#checkAllRows');
    const bulkMenu = $('#bulkActionMenu');
    const bulkSelectedCount = $('#bulkSelectedCount');
    const btnBulkDelete = $('#btnBulkDelete');
    const countDisplay = $('#checkedCountDisplay');

    function updateCount() {
        const checkedCount = $('.row-checkbox:checked').length;
        const totalCheckboxes = $('.row-checkbox').length;
        
        countDisplay.text(checkedCount);
        if (bulkSelectedCount.length > 0) {
            bulkSelectedCount.text(checkedCount);
        }
        
        if (totalCheckboxes > 0) {
            checkAllBtn.prop('checked', checkedCount === totalCheckboxes);
        }

        if (checkedCount > 0) {
            bulkMenu.fadeIn(200);
        } else {
            bulkMenu.fadeOut(200);
        }

        $('.row-checkbox').each(function() {
            const row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                row.addClass('table-primary');
            } else {
                row.removeClass('table-primary');
            }
        });
    }

    if (checkAllBtn.length > 0) {
        checkAllBtn.on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.row-checkbox').prop('checked', isChecked);
            updateCount();
        });
    }

    $('#dataTable tbody').on('change', '.row-checkbox', function(e) {
        e.stopPropagation();
        updateCount();
    });

    if (btnBulkDelete.length > 0) {
        btnBulkDelete.on('click', function() {
            const selectedIds = $('.row-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Hapus ' + selectedIds.length + ' Data?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: config.bulkDestroyUrl,
                        type: 'POST',
                        data: {
                            _token: config.csrfToken,
                            ids: selectedIds,
                            type: config.testType
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menghapus data.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    }

    // Delete SweetAlert
    $('.delete-form').submit(function(e) {
        e.preventDefault();
        let form = this;
        Swal.fire({
            title: 'Hapus Laporan?',
            text: "Laporan yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Dynamically set sticky top for thead rows 2 and 3
    function fixStickyHeaderTops() {
        var $rows = $('#dataTable > thead > tr');
        if ($rows.length < 2) return;
        var row1H = $rows.eq(0).outerHeight();
        var row2H = $rows.length > 2 ? $rows.eq(1).outerHeight() : 0;
        $rows.eq(1).find('th').css('top', row1H + 'px');
        if ($rows.length > 2) {
            $rows.eq(2).find('th').css('top', (row1H + row2H) + 'px');
        }
    }
    
    // Hide loader and show container, then calculate sticky headers
    $('#tableLoader').hide();
    $('#tableContainer').fadeIn('fast', function() {
        fixStickyHeaderTops();
    });
    
    $(window).on('resize', fixStickyHeaderTops);

    // Auto-calculate Tgl/Jam Keluar based on Tgl/Jam Masuk + Standar Jam
    function autoCalculateChamberTime($form) {
        var $tglMasuk  = $form.find('input[name="tgl_masuk"]');
        var $jamMasuk  = $form.find('input[name="jam_masuk"]');
        var $tglKeluar = $form.find('input[name="tgl_keluar"]');
        var $jamKeluar = $form.find('input[name="jam_keluar"]');
        var $standarJam = $form.find('.auto-calc-jam');

        if (!$tglMasuk.length || !$jamMasuk.length || !$tglKeluar.length || !$jamKeluar.length || !$standarJam.length) return;

        var tglMasukVal  = $tglMasuk.val();
        var jamMasukVal  = $jamMasuk.val();
        var standarJamVal = parseFloat($standarJam.val());

        if (tglMasukVal && jamMasukVal && !isNaN(standarJamVal) && standarJamVal > 0) {
            var masukDate = new Date(tglMasukVal + 'T' + jamMasukVal + ':00');
            masukDate.setTime(masukDate.getTime() + standarJamVal * 3600000);
            function pad(n) { return ('0' + n).slice(-2); }
            $tglKeluar.val(masukDate.getFullYear() + '-' + pad(masukDate.getMonth()+1) + '-' + pad(masukDate.getDate()));
            $jamKeluar.val(pad(masukDate.getHours()) + ':' + pad(masukDate.getMinutes()));
        }
    }

    $(document).on('keyup change', '.auto-calc-jam, .auto-calc-trigger', function() {
        var $form = $(this).closest('.modal-content');
        if (!$form.length) { $form = $(this).closest('form'); }
        autoCalculateChamberTime($form);
    });

});
