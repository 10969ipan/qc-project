$(document).ready(function () {
    if (typeof initItemSearch === 'function') {
        initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...' });
    }

    const config = window.__DURABILITY_PLATING_REPORT__ || {};
    var editStdCr = 0, editStdNi = 0, editStdCu = 0;

    $('.btn-edit-thickness').click(function () {
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
        // Data 1
        $('#edit_actual_cr').val(item.actual_cr);
        $('#edit_actual_ni').val(item.actual_ni);
        $('#edit_actual_cu').val(item.actual_cu);

        $('#edit_actual_corrodkote_waktu').val(item.actual_corrodkote_waktu);
        $('#edit_standar_jam_corrodkote').val(item.standar_jam_corrodkote);
        $('#edit_aktual_corrosion').val(item.aktual_corrosion);

        $('#edit_actual_cass_waktu').val(item.actual_cass_waktu);
        $('#edit_standar_jam_cass').val(item.standar_jam_cass);
        $('#edit_aktual_rn').val(item.aktual_rn || '');

        $('#edit_actual_salt_spray_waktu').val(item.actual_salt_spray_waktu);
        $('#edit_standar_jam_salt_spray').val(item.standar_jam_salt_spray);

        $('#edit_actual_porecount').val(item.actual_porecount);

        let rj = item.result_judgment;
        let rjTrial = item.result_judgment_trial;
        let desc = item.description;
        let descTrial = item.description_trial;
        if (config.testType === 'corrodkote') {
            rj = item.result_judgment_corrodkote;
            rjTrial = item.result_judgment_corrodkote_trial;
            desc = item.description_corrodkote;
            descTrial = item.description_corrodkote_trial;
        } else if (config.testType === 'cass') {
            rj = item.result_judgment_cass;
            rjTrial = item.result_judgment_cass_trial;
            desc = item.description_cass;
            descTrial = item.description_cass_trial;
        } else if (config.testType === 'salt_spray') {
            rj = item.result_judgment_salt_spray;
            rjTrial = item.result_judgment_salt_spray_trial;
            desc = item.description_salt_spray;
            descTrial = item.description_salt_spray_trial;
        } else if (config.testType === 'porecount') {
            rj = item.result_judgment_porecount;
            rjTrial = item.result_judgment_porecount_trial;
            desc = item.description_porecount;
            descTrial = item.description_porecount_trial;
        }
        $('#edit_result_judgment').val(rj ?? '-');
        $('#edit_description').val(desc || '');

        // Data 2 (Trial)
        $('#edit_actual_cr_trial').val(item.actual_cr_trial ?? '');
        $('#edit_actual_ni_trial').val(item.actual_ni_trial ?? '');
        $('#edit_actual_cu_trial').val(item.actual_cu_trial ?? '');

        $('#edit_actual_corrodkote_waktu_trial').val(item.actual_corrodkote_waktu_trial ?? '');
        $('#edit_standar_jam_corrodkote_trial').val(item.standar_jam_corrodkote_trial ?? '');
        $('#edit_aktual_corrosion_trial').val(item.aktual_corrosion_trial ?? '');

        $('#edit_actual_cass_waktu_trial').val(item.actual_cass_waktu_trial ?? '');
        $('#edit_standar_jam_cass_trial').val(item.standar_jam_cass_trial ?? '');
        $('#edit_aktual_rn_trial').val(item.aktual_rn_trial ?? '');

        $('#edit_actual_salt_spray_waktu_trial').val(item.actual_salt_spray_waktu_trial ?? '');
        $('#edit_standar_jam_salt_spray_trial').val(item.standar_jam_salt_spray_trial ?? '');

        $('#edit_actual_porecount_trial').val(item.actual_porecount_trial ?? '');

        $('#edit_result_judgment_trial').val(rjTrial ?? '-');
        $('#edit_description_trial').val(descTrial || '');

        $('#edit_tgl_masuk').val(item.tgl_masuk);
        if (item.jam_masuk) {
            $('#edit_jam_masuk').val(item.jam_masuk.substring(0, 5));
        }
        $('#edit_tgl_keluar').val(item.tgl_keluar);
        if (item.jam_keluar) {
            $('#edit_jam_keluar').val(item.jam_keluar.substring(0, 5));
        }


        let stdCr = $(this).attr('data-stdcr') !== undefined && $(this).attr('data-stdcr') !== '' ? $(this).attr('data-stdcr') : ($(this).data('stdcr') || '-');
        let stdNi = $(this).attr('data-stdni') !== undefined && $(this).attr('data-stdni') !== '' ? $(this).attr('data-stdni') : ($(this).data('stdni') || '-');
        let stdCu = $(this).attr('data-stdcu') !== undefined && $(this).attr('data-stdcu') !== '' ? $(this).attr('data-stdcu') : ($(this).data('stdcu') || '-');

        $('#edit_std_cr_display, #edit_std_cr_display_2, #edit_std_cr_display_single').text(stdCr);
        $('#edit_std_ni_display, #edit_std_ni_display_2, #edit_std_ni_display_single').text(stdNi);
        $('#edit_std_cu_display, #edit_std_cu_display_2, #edit_std_cu_display_single').text(stdCu);

        $('#edit_actual_cr_text').text(item.actual_cr || '-');
        $('#edit_actual_ni_text').text(item.actual_ni || '-');
        $('#edit_actual_cu_text').text(item.actual_cu || '-');
        $('#edit_actual_cr_text_2').text(item.actual_cr_trial || '-');
        $('#edit_actual_ni_text_2').text(item.actual_ni_trial || '-');
        $('#edit_actual_cu_text_2').text(item.actual_cu_trial || '-');

        editStdCr = parseFloat(stdCr) || 0;
        editStdNi = parseFloat(stdNi) || 0;
        editStdCu = parseFloat(stdCu) || 0;

        if (typeof window.calculateEditThicknessJudgment === 'function') {
            window.calculateEditThicknessJudgment();
        }

        let originalBeforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let originalAfterUrl = item.evidence_after ? config.baseUrl + item.evidence_after : null;
        let originalAfterTrialUrl = item.evidence_after_trial ? config.baseUrl + item.evidence_after_trial : null;

        let beforeTimeFormatted = formatDbDate(item.evidence_before_uploaded_at);
        let afterTimeFormatted = formatDbDate(item.evidence_after_uploaded_at);
        let afterTrialTimeFormatted = formatDbDate(item.evidence_after_trial_uploaded_at);

        showEvidenceCard('edit_evidence_before_preview', 'edit_evidence_before_preview_wrap',
            'edit_evidence_before_empty', 'btn_delete_evidence_before', 'edit_evidence_before_time',
            originalBeforeUrl, beforeTimeFormatted);
        showEvidenceCard('edit_evidence_after_preview', 'edit_evidence_after_preview_wrap',
            'edit_evidence_after_empty', 'btn_delete_evidence_after', 'edit_evidence_after_time',
            originalAfterUrl, afterTimeFormatted);
        showEvidenceCard('edit_evidence_after_trial_preview', 'edit_evidence_after_trial_preview_wrap',
            'edit_evidence_after_trial_empty', 'btn_delete_evidence_after_trial', 'edit_evidence_after_trial_time',
            originalAfterTrialUrl, afterTrialTimeFormatted);

        // Store originals on buttons for X restore logic
        $('#btn_delete_evidence_before').data({ originalUrl: originalBeforeUrl, hasNewFile: false });
        $('#btn_delete_evidence_after').data({ originalUrl: originalAfterUrl, hasNewFile: false });
        $('#btn_delete_evidence_after_trial').data({ originalUrl: originalAfterTrialUrl, hasNewFile: false });

        // Reset file inputs and delete flags
        $('#input_evidence_before').val('');
        $('#input_evidence_after').val('');
        $('#input_evidence_after_trial').val('');
        $('#delete_evidence_before').val('0');
        $('#delete_evidence_after').val('0');
        $('#delete_evidence_after_trial').val('0');

        updateLastUpdatedLog('thickness_last_updated_info', item, 'thickness');
        $('#modalEditThickness').modal('show');
    });

    // Smart X button: if new file staged → cancel & restore original; else → delete DB photo
    function handleDeleteBtn(btnId, previewId, wrapId, emptyId, timeId, inputId, deleteFlagId) {
        $('#' + btnId).on('click', function () {
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
    handleDeleteBtn('btn_delete_evidence_after', 'edit_evidence_after_preview', 'edit_evidence_after_preview_wrap',
        'edit_evidence_after_empty', 'edit_evidence_after_time', 'input_evidence_after', 'delete_evidence_after');
    handleDeleteBtn('btn_delete_evidence_after_trial', 'edit_evidence_after_trial_preview', 'edit_evidence_after_trial_preview_wrap',
        'edit_evidence_after_trial_empty', 'edit_evidence_after_trial_time', 'input_evidence_after_trial', 'delete_evidence_after_trial');

    // Live preview when new file chosen — also show X and mark hasNewFile
    function bindLivePreview(inputId, previewId, wrapId, emptyId, deleteBtnId) {
        $('#' + inputId).off('change.preview').on('change.preview', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
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
    bindLivePreview('input_evidence_after', 'edit_evidence_after_preview', 'edit_evidence_after_preview_wrap', 'edit_evidence_after_empty', 'btn_delete_evidence_after');
    bindLivePreview('input_evidence_after_trial', 'edit_evidence_after_trial_preview', 'edit_evidence_after_trial_preview_wrap', 'edit_evidence_after_trial_empty', 'btn_delete_evidence_after_trial');

    bindLivePreview('input_new_evidence_before', 'new_evidence_before_preview', 'new_evidence_before_preview_wrap', 'new_evidence_before_empty', 'btn_delete_new_evidence_before');
    bindLivePreview('input_new_evidence_after', 'new_evidence_after_preview', 'new_evidence_after_preview_wrap', 'new_evidence_after_empty', 'btn_delete_new_evidence_after');
    bindLivePreview('input_new_evidence_after_trial', 'new_evidence_after_trial_preview', 'new_evidence_after_trial_preview_wrap', 'new_evidence_after_trial_empty', 'btn_delete_new_evidence_after_trial');

    // Simple X handler for New Data Modal
    function handleNewDataDeleteBtn(btnId, previewId, wrapId, emptyId, inputId) {
        $('#' + btnId).on('click', function () {
            $('#' + inputId).val('');
            $('#' + wrapId).hide();
            $('#' + emptyId).css('display', 'flex');
            $(this).addClass('d-none').css('display', '');
        });
    }
    handleNewDataDeleteBtn('btn_delete_new_evidence_before', 'new_evidence_before_preview', 'new_evidence_before_preview_wrap', 'new_evidence_before_empty', 'input_new_evidence_before');
    handleNewDataDeleteBtn('btn_delete_new_evidence_after', 'new_evidence_after_preview', 'new_evidence_after_preview_wrap', 'new_evidence_after_empty', 'input_new_evidence_after');
    handleNewDataDeleteBtn('btn_delete_new_evidence_after_trial', 'new_evidence_after_trial_preview', 'new_evidence_after_trial_preview_wrap', 'new_evidence_after_trial_empty', 'input_new_evidence_after_trial');

    const inputModals = ['corrodkote', 'cass', 'salt_spray', 'porecount'];
    inputModals.forEach(t => {
        bindLivePreview(`input_${t}_evidence_before`, `${t}_evidence_before_preview`, `${t}_evidence_before_preview_wrap`, `${t}_evidence_before_empty`, `btn_delete_${t}_evidence_before`);
        bindLivePreview(`input_${t}_evidence_after`, `${t}_evidence_after_preview`, `${t}_evidence_after_preview_wrap`, `${t}_evidence_after_empty`, `btn_delete_${t}_evidence_after`);
        bindLivePreview(`input_${t}_evidence_after_trial`, `${t}_evidence_after_trial_preview`, `${t}_evidence_after_trial_preview_wrap`, `${t}_evidence_after_trial_empty`, `btn_delete_${t}_evidence_after_trial`);

        handleNewDataDeleteBtn(`btn_delete_${t}_evidence_before`, `${t}_evidence_before_preview`, `${t}_evidence_before_preview_wrap`, `${t}_evidence_before_empty`, `input_${t}_evidence_before`);
        handleNewDataDeleteBtn(`btn_delete_${t}_evidence_after`, `${t}_evidence_after_preview`, `${t}_evidence_after_preview_wrap`, `${t}_evidence_after_empty`, `input_${t}_evidence_after`);
        handleNewDataDeleteBtn(`btn_delete_${t}_evidence_after_trial`, `${t}_evidence_after_trial_preview`, `${t}_evidence_after_trial_preview_wrap`, `${t}_evidence_after_trial_empty`, `input_${t}_evidence_after_trial`);
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

    function updateLastUpdatedLog(elementId, item, testType) {
        if (!elementId || !item) return;
        let userName = null;
        if (item.updated_by && item.updated_by.name) {
            userName = item.updated_by.name;
        } else {
            if (testType === 'corrodkote' && item.analis_corrodkote && item.analis_corrodkote.name) {
                userName = item.analis_corrodkote.name;
            } else if (testType === 'cass' && item.analis_cass && item.analis_cass.name) {
                userName = item.analis_cass.name;
            } else if (testType === 'salt_spray' && item.analis_salt_spray && item.analis_salt_spray.name) {
                userName = item.analis_salt_spray.name;
            } else if (testType === 'porecount' && item.analis_porecount && item.analis_porecount.name) {
                userName = item.analis_porecount.name;
            } else if (item.analis && item.analis.name) {
                userName = item.analis.name;
            }
        }

        let timeFormatted = formatDbDate(item.updated_at || item.created_at);

        if (userName && timeFormatted) {
            $('#' + elementId).html(`<i class="fas fa-history mr-1 text-muted"></i> Terakhir diupdate oleh <strong>${userName}</strong> (${timeFormatted})`);
        } else if (userName) {
            $('#' + elementId).html(`<i class="fas fa-history mr-1 text-muted"></i> Terakhir diupdate oleh <strong>${userName}</strong>`);
        } else {
            $('#' + elementId).html('');
        }
    }

    function showEvidenceCard(previewId, wrapId, emptyId, deleteBtnId, timeId, url, time) {
        if (url) {
            $('#' + previewId).attr('src', url);
            $('#' + wrapId).show();
            $('#' + emptyId).hide();
            $('#' + deleteBtnId).removeClass('d-none').css('display', 'flex');
            if (timeId) {
                $('#' + timeId).text(time ? 'Diunggah: ' + time : '').removeClass('d-none');
            }
        } else {
            $('#' + previewId).attr('src', '');
            $('#' + wrapId).hide();
            $('#' + emptyId).css('display', 'flex');
            $('#' + deleteBtnId).addClass('d-none').css('display', '');
            if (timeId) {
                $('#' + timeId).text('').addClass('d-none');
            }
        }
    }

    // Auto judgment logic for Edit Thickness Modal
    window.calculateEditThicknessJudgment = function () {
        // Data 1
        var actCr = parseFloat($('#edit_actual_cr').val());
        var actNi = parseFloat($('#edit_actual_ni').val());
        var actCu = parseFloat($('#edit_actual_cu').val());

        if (!isNaN(actCr) && !isNaN(actNi) && !isNaN(actCu)) {
            if (actCr >= editStdCr && actNi >= editStdNi && actCu >= editStdCu) {
                $('#edit_result_judgment').val('OK');
            } else {
                $('#edit_result_judgment').val('NG');
            }
        }

        // Data 2
        var actCrTrial = parseFloat($('#edit_actual_cr_trial').val());
        var actNiTrial = parseFloat($('#edit_actual_ni_trial').val());
        var actCuTrial = parseFloat($('#edit_actual_cu_trial').val());

        if (!isNaN(actCrTrial) && !isNaN(actNiTrial) && !isNaN(actCuTrial)) {
            if (actCrTrial >= editStdCr && actNiTrial >= editStdNi && actCuTrial >= editStdCu) {
                $('#edit_result_judgment_trial').val('OK');
            } else {
                $('#edit_result_judgment_trial').val('NG');
            }
        }
    };

    $(document).on('keyup change input', '.edit-actual-thickness-input, #edit_actual_cr, #edit_actual_ni, #edit_actual_cu, #edit_actual_cr_trial, #edit_actual_ni_trial, #edit_actual_cu_trial', function () {
        window.calculateEditThicknessJudgment();
    });

    $(document).on('click', '.btn-edit-approval', function () {
        let id = $(this).data('id');
        let spvQc = $(this).data('supervisor-qc') || '';
        let spvPlating = $(this).data('supervisor-plating') || '';
        let asstQc = $(this).data('asst-manager-qc') || '';
        let asstPlating = $(this).data('asst-manager-plating') || '';
        let partName = $(this).data('part') || '-';
        let lotNo = $(this).data('lot') || '-';

        $('#edit_approval_part_name').val(partName);
        $('#edit_approval_lot_no').val(lotNo);

        $('#edit_status_supervisor_qc').val(spvQc === 'REJECTED' ? 'REJECTED' : (spvQc ? 'Approved' : ''));
        $('#edit_status_supervisor_plating').val(spvPlating === 'REJECTED' ? 'REJECTED' : (spvPlating ? 'Approved' : ''));
        $('#edit_status_asst_manager_qc').val(asstQc === 'REJECTED' ? 'REJECTED' : (asstQc ? 'Approved' : ''));
        $('#edit_status_asst_manager_plating').val(asstPlating === 'REJECTED' ? 'REJECTED' : (asstPlating ? 'Approved' : ''));

        let url = (config.updateApprovalUrl || '/qc/standard-performance-tests/:id/update-approval').replace(':id', id);
        $('#formEditApproval').attr('action', url);

        $('#modalEditApproval').modal('show');
    });

    $('.btn-input-corrodkote').click(function () {
        let item = $(this).data('item');
        let form = $('#formInputCorrodkote');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        form.attr('action', url);

        $('#corrodkote_report_id').val(item.id);
        $('#corrodkote_part_name').val($(this).data('part'));
        $('#corrodkote_customer').val($(this).data('customer'));
        $('#corrodkote_std').val($(this).data('std'));
        $('#corrodkote_standard_time').val($(this).data('time'));

        let cu1 = item.actual_cu || '-';
        let ni1 = item.actual_ni || '-';
        let cr1 = item.actual_cr || '-';
        let cu2 = item.actual_cu_trial || '-';
        let ni2 = item.actual_ni_trial || '-';
        let cr2 = item.actual_cr_trial || '-';
        $('#corrodkote_thickness_ref_1').text(`Cu: ${cu1} | Ni: ${ni1} | Cr: ${cr1}`);
        $('#corrodkote_thickness_ref_2').text(`Cu: ${cu2} | Ni: ${ni2} | Cr: ${cr2}`);

        $('#corrodkote_produksi').val(item.production_date);
        $('#corrodkote_shift').val(item.shift);
        if (item.lot_no) {
            $('#corrodkote_lot').val(item.lot_no).prop('readonly', true).addClass('bg-light');
        } else {
            $('#corrodkote_lot').val('').prop('readonly', false).removeClass('bg-light');
        }

        // Tgl/Jam Masuk & Keluar Chamber
        let hasCorr = item.actual_corrodkote_waktu && item.actual_corrodkote_waktu !== '-';
        form.find('[name="tgl_masuk"]').val(hasCorr ? (item.tgl_masuk || '') : '');
        form.find('[name="jam_masuk"]').val(hasCorr ? (item.jam_masuk || '') : '');
        form.find('[name="tgl_keluar"]').val(hasCorr ? (item.tgl_keluar || '') : '');
        form.find('[name="jam_keluar"]').val(hasCorr ? (item.jam_keluar || '') : '');

        // Data 1 Fields
        form.find('[name="actual_corrodkote_waktu"]').val(item.actual_corrodkote_waktu && item.actual_corrodkote_waktu !== '-' ? item.actual_corrodkote_waktu : '');
        form.find('[name="standar_jam_corrodkote"]').val(item.standar_jam_corrodkote && item.standar_jam_corrodkote !== '-' ? item.standar_jam_corrodkote : ($(this).data('time') || ''));
        form.find('[name="aktual_corrosion"]').val(item.aktual_corrosion || '');
        form.find('[name="result_judgment_corrodkote"]').val(item.result_judgment_corrodkote || '');
        form.find('[name="description_corrodkote"]').val(item.description_corrodkote || '');

        // Data 2 Fields
        form.find('[name="actual_corrodkote_waktu_trial"]').val(item.actual_corrodkote_waktu_trial && item.actual_corrodkote_waktu_trial !== '-' ? item.actual_corrodkote_waktu_trial : '');
        form.find('[name="standar_jam_corrodkote_trial"]').val(item.standar_jam_corrodkote_trial && item.standar_jam_corrodkote_trial !== '-' ? item.standar_jam_corrodkote_trial : ($(this).data('time') || ''));
        form.find('[name="aktual_corrosion_trial"]').val(item.aktual_corrosion_trial || '');
        form.find('[name="result_judgment_corrodkote_trial"]').val(item.result_judgment_corrodkote_trial || '-');
        form.find('[name="description_corrodkote_trial"]').val(item.description_corrodkote_trial || '');

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl = item.evidence_after ? config.baseUrl + item.evidence_after : null;
        let afterTrialUrl = item.evidence_after_trial ? config.baseUrl + item.evidence_after_trial : null;
        showEvidenceCard('corrodkote_evidence_before_preview', 'corrodkote_evidence_before_preview_wrap', 'corrodkote_evidence_before_empty', 'btn_delete_corrodkote_evidence_before', null, beforeUrl, null);
        showEvidenceCard('corrodkote_evidence_after_preview', 'corrodkote_evidence_after_preview_wrap', 'corrodkote_evidence_after_empty', 'btn_delete_corrodkote_evidence_after', null, afterUrl, null);
        showEvidenceCard('corrodkote_evidence_after_trial_preview', 'corrodkote_evidence_after_trial_preview_wrap', 'corrodkote_evidence_after_trial_empty', 'btn_delete_corrodkote_evidence_after_trial', null, afterTrialUrl, null);

        calculateTestAutoJudgment(form);
        updateLastUpdatedLog('corrodkote_last_updated_info', item, 'corrodkote');
        $('#modalInputCorrodkote').modal('show');
    });

    $('.btn-input-cass').click(function () {
        let item = $(this).data('item');
        let form = $('#formInputCass');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        form.attr('action', url);

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

        // Tgl/Jam Masuk & Keluar Chamber
        let hasCass = item.actual_cass_waktu && item.actual_cass_waktu !== '-';
        form.find('[name="tgl_masuk"]').val(hasCass ? (item.tgl_masuk || '') : '');
        form.find('[name="jam_masuk"]').val(hasCass ? (item.jam_masuk || '') : '');
        form.find('[name="tgl_keluar"]').val(hasCass ? (item.tgl_keluar || '') : '');
        form.find('[name="jam_keluar"]').val(hasCass ? (item.jam_keluar || '') : '');

        // Data 1 Fields
        form.find('[name="actual_cass_waktu"]').val(item.actual_cass_waktu && item.actual_cass_waktu !== '-' ? item.actual_cass_waktu : '');
        form.find('[name="standar_jam_cass"]').val(item.standar_jam_cass && item.standar_jam_cass !== '-' ? item.standar_jam_cass : ($(this).data('time') || ''));
        form.find('[name="aktual_rn"]').val(item.aktual_rn || '');
        form.find('[name="result_judgment_cass"]').val(item.result_judgment_cass || '');
        form.find('[name="description_cass"]').val(item.description_cass || '');

        // Data 2 Fields
        form.find('[name="actual_cass_waktu_trial"]').val(item.actual_cass_waktu_trial && item.actual_cass_waktu_trial !== '-' ? item.actual_cass_waktu_trial : '');
        form.find('[name="standar_jam_cass_trial"]').val(item.standar_jam_cass_trial && item.standar_jam_cass_trial !== '-' ? item.standar_jam_cass_trial : ($(this).data('time') || ''));
        form.find('[name="aktual_rn_trial"]').val(item.aktual_rn_trial || '');
        form.find('[name="result_judgment_cass_trial"]').val(item.result_judgment_cass_trial || '-');
        form.find('[name="description_cass_trial"]').val(item.description_cass_trial || '');

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl = item.evidence_after ? config.baseUrl + item.evidence_after : null;
        let afterTrialUrl = item.evidence_after_trial ? config.baseUrl + item.evidence_after_trial : null;
        showEvidenceCard('cass_evidence_before_preview', 'cass_evidence_before_preview_wrap', 'cass_evidence_before_empty', 'btn_delete_cass_evidence_before', null, beforeUrl, null);
        showEvidenceCard('cass_evidence_after_preview', 'cass_evidence_after_preview_wrap', 'cass_evidence_after_empty', 'btn_delete_cass_evidence_after', null, afterUrl, null);
        showEvidenceCard('cass_evidence_after_trial_preview', 'cass_evidence_after_trial_preview_wrap', 'cass_evidence_after_trial_empty', 'btn_delete_cass_evidence_after_trial', null, afterTrialUrl, null);

        calculateTestAutoJudgment(form);
        updateLastUpdatedLog('cass_last_updated_info', item, 'cass');
        $('#modalInputCass').modal('show');
    });

    $('.btn-input-salt-spray').click(function () {
        let item = $(this).data('item');
        let form = $('#formInputSaltSpray');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        form.attr('action', url);

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

        // Tgl/Jam Masuk & Keluar Chamber
        let hasSalt = item.actual_salt_spray_waktu && item.actual_salt_spray_waktu !== '-';
        form.find('[name="tgl_masuk"]').val(hasSalt ? (item.tgl_masuk || '') : '');
        form.find('[name="jam_masuk"]').val(hasSalt ? (item.jam_masuk || '') : '');
        form.find('[name="tgl_keluar"]').val(hasSalt ? (item.tgl_keluar || '') : '');
        form.find('[name="jam_keluar"]').val(hasSalt ? (item.jam_keluar || '') : '');

        // Data 1 Fields
        form.find('[name="actual_salt_spray_waktu"]').val(item.actual_salt_spray_waktu && item.actual_salt_spray_waktu !== '-' ? item.actual_salt_spray_waktu : '');
        form.find('[name="standar_jam_salt_spray"]').val(item.standar_jam_salt_spray && item.standar_jam_salt_spray !== '-' ? item.standar_jam_salt_spray : ($(this).data('time') || ''));
        form.find('[name="result_judgment_salt_spray"]').val(item.result_judgment_salt_spray || '');
        form.find('[name="description_salt_spray"]').val(item.description_salt_spray || '');

        // Data 2 Fields
        form.find('[name="actual_salt_spray_waktu_trial"]').val(item.actual_salt_spray_waktu_trial && item.actual_salt_spray_waktu_trial !== '-' ? item.actual_salt_spray_waktu_trial : '');
        form.find('[name="standar_jam_salt_spray_trial"]').val(item.standar_jam_salt_spray_trial && item.standar_jam_salt_spray_trial !== '-' ? item.standar_jam_salt_spray_trial : ($(this).data('time') || ''));
        form.find('[name="result_judgment_salt_spray_trial"]').val(item.result_judgment_salt_spray_trial || '-');
        form.find('[name="description_salt_spray_trial"]').val(item.description_salt_spray_trial || '');

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl = item.evidence_after ? config.baseUrl + item.evidence_after : null;
        let afterTrialUrl = item.evidence_after_trial ? config.baseUrl + item.evidence_after_trial : null;
        showEvidenceCard('salt_spray_evidence_before_preview', 'salt_spray_evidence_before_preview_wrap', 'salt_spray_evidence_before_empty', 'btn_delete_salt_spray_evidence_before', null, beforeUrl, null);
        showEvidenceCard('salt_spray_evidence_after_preview', 'salt_spray_evidence_after_preview_wrap', 'salt_spray_evidence_after_empty', 'btn_delete_salt_spray_evidence_after', null, afterUrl, null);
        showEvidenceCard('salt_spray_evidence_after_trial_preview', 'salt_spray_evidence_after_trial_preview_wrap', 'salt_spray_evidence_after_trial_empty', 'btn_delete_salt_spray_evidence_after_trial', null, afterTrialUrl, null);

        calculateTestAutoJudgment(form);
        updateLastUpdatedLog('salt_spray_last_updated_info', item, 'salt_spray');
        $('#modalInputSaltSpray').modal('show');
    });

    $('.btn-input-porecount').click(function () {
        let item = $(this).data('item');
        let form = $('#formInputPorecount');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        form.attr('action', url);

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

        let poreDate = item.tanggal_cek || '';
        if (poreDate && item.tgl_masuk && poreDate === item.tgl_masuk) {
            poreDate = '';
        }
        form.find('[name="tanggal_test"]').val(poreDate || new Date().toISOString().slice(0, 10));

        // Data 1 Fields
        form.find('[name="actual_porecount"]').val(item.actual_porecount && item.actual_porecount !== '-' ? item.actual_porecount : '');
        form.find('[name="result_judgment_porecount"]').val(item.result_judgment_porecount || '');
        form.find('[name="description_porecount"]').val(item.description_porecount || '');

        // Data 2 Fields
        form.find('[name="actual_porecount_trial"]').val(item.actual_porecount_trial && item.actual_porecount_trial !== '-' ? item.actual_porecount_trial : '');
        form.find('[name="result_judgment_porecount_trial"]').val(item.result_judgment_porecount_trial || '-');
        form.find('[name="description_porecount_trial"]').val(item.description_porecount_trial || '');

        let beforeUrl = item.evidence_before ? config.baseUrl + item.evidence_before : null;
        let afterUrl = item.evidence_after ? config.baseUrl + item.evidence_after : null;
        let afterTrialUrl = item.evidence_after_trial ? config.baseUrl + item.evidence_after_trial : null;
        showEvidenceCard('porecount_evidence_before_preview', 'porecount_evidence_before_preview_wrap', 'porecount_evidence_before_empty', 'btn_delete_porecount_evidence_before', null, beforeUrl, null);
        showEvidenceCard('porecount_evidence_after_preview', 'porecount_evidence_after_preview_wrap', 'porecount_evidence_after_empty', 'btn_delete_porecount_evidence_after', null, afterUrl, null);
        showEvidenceCard('porecount_evidence_after_trial_preview', 'porecount_evidence_after_trial_preview_wrap', 'porecount_evidence_after_trial_empty', 'btn_delete_porecount_evidence_after_trial', null, afterTrialUrl, null);

        calculateTestAutoJudgment(form);
        updateLastUpdatedLog('porecount_last_updated_info', item, 'porecount');
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

        $('.row-checkbox').each(function () {
            const row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                row.addClass('table-primary');
            } else {
                row.removeClass('table-primary');
            }
        });
    }

    if (checkAllBtn.length > 0) {
        checkAllBtn.on('change', function () {
            const isChecked = $(this).prop('checked');
            $('.row-checkbox').prop('checked', isChecked);
            updateCount();
        });
    }

    $('#dataTable tbody').on('change', '.row-checkbox', function (e) {
        e.stopPropagation();
        updateCount();
    });

    if (btnBulkDelete.length > 0) {
        btnBulkDelete.on('click', function () {
            const selectedIds = $('.row-checkbox:checked').map(function () {
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
                        success: function (response) {
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
                        error: function (xhr) {
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

    const btnBulkCopy = $('#btnBulkCopy');
    if (btnBulkCopy.length > 0) {
        btnBulkCopy.on('click', function () {
            const selectedIds = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Salin ' + selectedIds.length + ' Data?',
                text: "Data laporan yang dipilih akan diduplikasi.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#36b9cc',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Salin Data!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyalin...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: config.bulkCopyUrl,
                        type: 'POST',
                        data: {
                            _token: config.csrfToken,
                            ids: selectedIds
                        },
                        success: function (response) {
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
                        error: function () {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menyalin data.', 'error');
                        }
                    });
                }
            });
        });
    }

    const btnBulkCancel = $('#btnBulkCancel');
    if (btnBulkCancel.length > 0) {
        btnBulkCancel.on('click', function () {
            $('.row-checkbox, #checkAllRows').prop('checked', false);
            updateCount();
        });
    }



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
    $('#tableContainer').fadeIn('fast', function () {
        fixStickyHeaderTops();
    });

    $(window).on('resize', fixStickyHeaderTops);

    // Auto-calculate Tgl/Jam Keluar based on Tgl/Jam Masuk + Standar Jam
    function autoCalculateChamberTime($form) {
        var $tglMasuk = $form.find('input[name="tgl_masuk"]');
        var $jamMasuk = $form.find('input[name="jam_masuk"]');
        var $tglKeluar = $form.find('input[name="tgl_keluar"]');
        var $jamKeluar = $form.find('input[name="jam_keluar"]');
        var $standarJam = $form.find('.auto-calc-jam');

        if (!$tglMasuk.length || !$jamMasuk.length || !$tglKeluar.length || !$jamKeluar.length || !$standarJam.length) return;

        var tglMasukVal = $tglMasuk.val();
        var jamMasukVal = $jamMasuk.val();
        var standarJamVal = parseFloat($standarJam.val());

        if (tglMasukVal && jamMasukVal && !isNaN(standarJamVal) && standarJamVal > 0) {
            var masukDate = new Date(tglMasukVal + 'T' + jamMasukVal + ':00');
            masukDate.setTime(masukDate.getTime() + standarJamVal * 3600000);
            function pad(n) { return ('0' + n).slice(-2); }
            $tglKeluar.val(masukDate.getFullYear() + '-' + pad(masukDate.getMonth() + 1) + '-' + pad(masukDate.getDate()));
            $jamKeluar.val(pad(masukDate.getHours()) + ':' + pad(masukDate.getMinutes()));
        }
    }

    $(document).on('keyup change', '.auto-calc-jam, .auto-calc-trigger', function () {
        var $form = $(this).closest('.modal-content');
        if (!$form.length) { $form = $(this).closest('form'); }
        autoCalculateChamberTime($form);
    });

    // Auto calculate judgment for Data 1 and Data 2 in test modals
    function calculateTestAutoJudgment($form) {
        // 1. Corrodkote
        let actCorr1 = parseFloat($form.find('input[name="actual_corrodkote_waktu"]').val());
        let stdCorr1 = parseFloat($form.find('input[name="standar_jam_corrodkote"]').val());
        if (!isNaN(actCorr1) && !isNaN(stdCorr1)) {
            $form.find('select[name="result_judgment_corrodkote"]').val(actCorr1 >= stdCorr1 ? 'OK' : 'NG');
        }
        let actCorr2 = parseFloat($form.find('input[name="actual_corrodkote_waktu_trial"]').val());
        let stdCorr2 = parseFloat($form.find('input[name="standar_jam_corrodkote_trial"]').val()) || stdCorr1;
        if (!isNaN(actCorr2) && !isNaN(stdCorr2)) {
            $form.find('select[name="result_judgment_corrodkote_trial"]').val(actCorr2 >= stdCorr2 ? 'OK' : 'NG');
        }

        // 2. CASS
        let actCass1 = parseFloat($form.find('input[name="actual_cass_waktu"]').val());
        let stdCass1 = parseFloat($form.find('input[name="standar_jam_cass"]').val());
        if (!isNaN(actCass1) && !isNaN(stdCass1)) {
            $form.find('select[name="result_judgment_cass"]').val(actCass1 >= stdCass1 ? 'OK' : 'NG');
        }
        let actCass2 = parseFloat($form.find('input[name="actual_cass_waktu_trial"]').val());
        let stdCass2 = parseFloat($form.find('input[name="standar_jam_cass_trial"]').val()) || stdCass1;
        if (!isNaN(actCass2) && !isNaN(stdCass2)) {
            $form.find('select[name="result_judgment_cass_trial"]').val(actCass2 >= stdCass2 ? 'OK' : 'NG');
        }

        // 3. Salt Spray
        let actSalt1 = parseFloat($form.find('input[name="actual_salt_spray_waktu"]').val());
        let stdSalt1 = parseFloat($form.find('input[name="standar_jam_salt_spray"]').val());
        if (!isNaN(actSalt1) && !isNaN(stdSalt1)) {
            let $select1 = $form.find('select[name="result_judgment_salt_spray"]');
            if (!$select1.length) $select1 = $form.find('select[name="result_judgment"]');
            if (actSalt1 >= stdSalt1) {
                $select1.val('OK');
            } else if (!$select1.val() || $select1.val() === 'OK' || $select1.val() === '-') {
                $select1.val('NG - White Rust');
            }
        }
        let actSalt2 = parseFloat($form.find('input[name="actual_salt_spray_waktu_trial"]').val());
        let stdSalt2 = parseFloat($form.find('input[name="standar_jam_salt_spray_trial"]').val()) || stdSalt1;
        if (!isNaN(actSalt2) && !isNaN(stdSalt2)) {
            let $select2 = $form.find('select[name="result_judgment_salt_spray_trial"]');
            if (actSalt2 >= stdSalt2) {
                $select2.val('OK');
            } else if (!$select2.val() || $select2.val() === 'OK' || $select2.val() === '-') {
                $select2.val('NG - White Rust');
            }
        }

        // 4. Porecount
        let actPore1 = parseFloat($form.find('input[name="actual_porecount"]').val());
        let stdPoreMin = parseFloat($('#porecount_standard_min').val());
        if (!isNaN(actPore1) && !isNaN(stdPoreMin)) {
            $form.find('select[name="result_judgment_porecount"]').val(actPore1 >= stdPoreMin ? 'OK' : 'NG');
        }
        let actPore2 = parseFloat($form.find('input[name="actual_porecount_trial"]').val());
        if (!isNaN(actPore2) && !isNaN(stdPoreMin)) {
            $form.find('select[name="result_judgment_porecount_trial"]').val(actPore2 >= stdPoreMin ? 'OK' : 'NG');
        }

        syncSaltSprayRustDropdowns($form);
    }

    function syncSaltSprayRustDropdowns($container) {
        if (!$container || !$container.length) return;
        $container.find('select[name="result_judgment_salt_spray"], select[name="result_judgment_salt_spray_trial"], select[name="result_judgment"]').not('#filterResultJudgment, .custom-filter-wrapper select').each(function () {
            let $select = $(this);
            let val = $select.val() || '';
            let $group = $select.closest('.form-group').next('.salt-spray-rust-group');
            if (!$group.length) {
                $group = $select.closest('.card-body, .form-group').find('.salt-spray-rust-group');
            }

            if (val.indexOf('NG') !== -1) {
                $group.show();
                let $rustSelect = $group.find('.salt-spray-rust-type');
                if (val === 'NG - Red Rust') {
                    $rustSelect.val('Red Rust');
                } else {
                    $rustSelect.val('White Rust');
                    if (val === 'NG') {
                        $select.val('NG - White Rust');
                    }
                }
            } else {
                $group.hide();
            }
        });
    }

    $(document).on('change', '.salt-spray-rust-type', function () {
        let rust = $(this).val() || 'White Rust';
        let $group = $(this).closest('.salt-spray-rust-group');
        let $mainSelect = $group.prev('.form-group').find('select');
        if ($mainSelect.length) {
            let targetVal = 'NG - ' + rust;
            $mainSelect.val(targetVal);
        }
    });

    $(document).on('change', 'select[name="result_judgment_salt_spray"], select[name="result_judgment_salt_spray_trial"], select[name="result_judgment"]', function () {
        if ($(this).is('#filterResultJudgment') || $(this).closest('.custom-filter-wrapper').length) return;
        let $container = $(this).closest('.modal-content');
        if ($container.length) {
            syncSaltSprayRustDropdowns($container);
        }
    });

    $(document).on('keyup change input', '#modalInputCorrodkote input, #modalInputCass input, #modalInputSaltSpray input, #modalInputPorecount input, #formAddData input', function () {
        var $form = $(this).closest('.modal-content');
        if (!$form.length) { $form = $(this).closest('form'); }
        calculateTestAutoJudgment($form);
    });

    $(document).on('input change blur', '#add_part_search', function () {
        let val = $(this).val();
        let $hidden = $('#hidden_standard_performance_test_id');
        let matchedOption = $('#masterPartList option').filter(function () {
            return $(this).val() === val;
        });

        if (matchedOption.length) {
            let stdId = matchedOption.attr('data-id') || matchedOption.data('id');
            $hidden.val(stdId);

            let saltTime = matchedOption.attr('data-salt-spray') || matchedOption.data('salt-spray');
            let cassTime = matchedOption.attr('data-cass') || matchedOption.data('cass');
            let corrTime = matchedOption.attr('data-corrodkote') || matchedOption.data('corrodkote');
            let poreMin = matchedOption.attr('data-porecount') || matchedOption.data('porecount');

            let $modal = $(this).closest('.modal-content, form');
            $modal.find('input[name="standar_jam_salt_spray"], input[name="standar_jam_salt_spray_trial"]').val(saltTime || '');
            $modal.find('input[name="standar_jam_cass"], input[name="standar_jam_cass_trial"]').val(cassTime || '');
            $modal.find('input[name="standar_jam_corrodkote"], input[name="standar_jam_corrodkote_trial"]').val(corrTime || '');
            $modal.find('input[name="standard_porecount_min"]').val(poreMin || '');

            if (typeof calculateTestAutoJudgment === 'function') {
                calculateTestAutoJudgment($modal);
            }
        } else {
            $hidden.val('');
        }
    });



    // ponytail: Dynamic body-append positioning for action dropdown menu.
    // Fixes table container clipping, z-index sticky header coverage, and ensures fixed compact width (200px).
    $(document).on('show.bs.dropdown', '.table-responsive .dropdown, #tableContainer .dropdown', function () {
        var $dropdown = $(this);
        var $menu = $dropdown.children('.dropdown-menu');
        if (!$menu.length) return;

        $menu.data('parent', $dropdown);
        $('body').append($menu);

        // Explicit compact width and display setup
        $menu.css({
            'display': 'block',
            'width': '200px',
            'min-width': '200px',
            'max-width': '200px',
            'position': 'absolute',
            'z-index': '1095',
            'margin': '0'
        });

        var eOffset = $dropdown.offset();
        var btnWidth = $dropdown.outerWidth();
        var btnHeight = $dropdown.outerHeight();
        var menuWidth = 200;
        var menuHeight = $menu.outerHeight() || 250;

        var windowScrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        var windowBottom = windowScrollTop + windowHeight;

        var top = eOffset.top + btnHeight + 2;
        var left = eOffset.left + btnWidth - menuWidth;

        // Auto flip up if bottom of viewport is reached
        if (top + menuHeight > windowBottom - 10 && eOffset.top - menuHeight > windowScrollTop + 10) {
            top = eOffset.top - menuHeight - 2;
        }

        if (left < 10) left = 10;

        $menu.css({
            'top': top + 'px',
            'left': left + 'px'
        });
    });

    $(document).on('hide.bs.dropdown', '.table-responsive .dropdown, #tableContainer .dropdown', function () {
        var $dropdown = $(this);
        var $menu = $('body').children('.dropdown-menu').filter(function () {
            return $(this).data('parent') && $(this).data('parent').is($dropdown);
        });

        if ($menu.length) {
            $menu.css({
                'display': '',
                'width': '',
                'min-width': '',
                'max-width': '',
                'position': '',
                'top': '',
                'left': '',
                'z-index': '',
                'margin': ''
            });
            $dropdown.append($menu);
        }
    });

    $(window).on('scroll resize', function () {
        $('body > .dropdown-menu').each(function () {
            var $parent = $(this).data('parent');
            if ($parent && $parent.length) {
                $parent.removeClass('show');
                $(this).removeClass('show').hide();
                $parent.append($(this));
            }
        });
    });

});
