/**
 * Leader Daily Sampling Dimension Check Handler
 * Digunakan untuk Kashift QC (Karawang) & Kepala Regu (Jakarta)
 * Kepatuhan: Separation of Concerns (RULE.md), UI Standardization, & SweetAlert2
 */

(function ($) {
    'use strict';

    let currentChecksheetId = null;
    let currentStandards = {};
    let currentActivePoints = [];
    let currentActiveCavities = [];
    let currentMaxCavity = 1;
    let isOpeningModal = false;

    function getConfig() {
        return window.leaderSamplingConfig || {};
    }

    // Helper untuk normalisasi angka & karakter simbolik
    function normalizeStandardValue(val) {
        if (val === null || val === undefined) return '';
        return String(val)
            .replace(',', '.')
            .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
            .replace(/[Ø⌀ø±\u00B1\u00D8\u00F8\u2300]/g, '')
            .trim();
    }

    // Menghitung batas toleransi Min dan Max (Min/Max eksplisit atau Size ± Tol seperti halaman create)
    function calculateBounds(std) {
        let minBound = null;
        let maxBound = null;

        if (!std || typeof std !== 'object') return { min: null, max: null };

        // 1. Min / Max eksplisit
        if (std.min !== null && std.min !== undefined && std.min !== '' && std.min !== '-') {
            let m = parseFloat(normalizeStandardValue(std.min));
            if (!isNaN(m)) minBound = m;
        }
        if (std.max !== null && std.max !== undefined && std.max !== '' && std.max !== '-') {
            let m = parseFloat(normalizeStandardValue(std.max));
            if (!isNaN(m)) maxBound = m;
        }

        // 2. Jika min/max belum lengkap, hitung dari Size +/- Tolerance
        if ((minBound === null || maxBound === null) && std.size !== null && std.size !== undefined && std.size !== '' && std.tolerance !== null && std.tolerance !== undefined && std.tolerance !== '') {
            let stdSzStr = normalizeStandardValue(std.size);
            if (!stdSzStr.startsWith('+') && !stdSzStr.startsWith('-')) {
                let base = parseFloat(stdSzStr);
                let tolStr = normalizeStandardValue(std.tolerance);
                if (!isNaN(base)) {
                    let lb = base, ub = base;
                    if (tolStr.includes('/')) {
                        tolStr.split('/').forEach(function (p) {
                            p = normalizeStandardValue(p);
                            let fv = parseFloat(p);
                            if (p.startsWith('+') || fv > 0) ub = base + Math.abs(fv);
                            else if (p.startsWith('-') || fv < 0) lb = base - Math.abs(fv);
                        });
                    } else if (tolStr.startsWith('+')) {
                        ub = base + parseFloat(tolStr.substring(1));
                    } else if (tolStr.startsWith('-')) {
                        lb = base + parseFloat(tolStr);
                    } else {
                        let tv = parseFloat(tolStr);
                        if (!isNaN(tv)) {
                            lb = base - Math.abs(tv);
                            ub = base + Math.abs(tv);
                        }
                    }
                    if (minBound === null) minBound = Math.round(lb * 10000) / 10000;
                    if (maxBound === null) maxBound = Math.round(ub * 10000) / 10000;
                }
            }
        }

        // 3. Special Size prefix
        if ((minBound === null || maxBound === null) && std.size !== null && std.size !== undefined && std.size !== '') {
            let sz = String(std.size).trim();
            if (sz.startsWith('+')) {
                let bound = parseFloat(sz.substring(1));
                if (!isNaN(bound)) minBound = bound;
            } else if (sz.startsWith('-')) {
                let bound = parseFloat(sz.substring(1));
                if (!isNaN(bound)) maxBound = bound;
            }
        }

        return { min: minBound, max: maxBound };
    }

    // Helper untuk normalisasi angka & toleransi
    function parseBound(val) {
        if (val === null || val === undefined || val === '' || val === '-') return null;
        let s = String(val).replace(',', '.').replace(/[^\d.-]/g, '').trim();
        let num = parseFloat(s);
        return isNaN(num) ? null : num;
    }

    /**
     * Buka modal sampling pengukuran dimensi
     */
    window.openLeaderSamplingModal = function (checksheetId) {
        if (isOpeningModal && currentChecksheetId === checksheetId) {
            return;
        }
        isOpeningModal = true;
        currentChecksheetId = checksheetId;
        const config = getConfig();

        if (!config.samplingDataUrl) {
            console.error('leaderSamplingConfig.samplingDataUrl belum dikonfigurasi.');
            return;
        }

        const $modal = $('#modalLeaderDimensionSampling');
        const $loader = $('#modalLeaderSamplingLoader');
        const $tableWrapper = $('#modalLeaderTableWrapper');
        const $alert = $('#modalLeaderSamplingAlert');

        $alert.addClass('d-none');
        $loader.removeClass('d-none');
        $tableWrapper.addClass('d-none');
        $('#btnSubmitLeaderSampling').prop('disabled', true);
        $('#modalInputProgress').text('');

        const roleLabel = config.roleLabel || 'Kashift / Kepala Regu';
        $('#modalLeaderSamplingTitle').text(`Input Sampling Pengukuran Dimensi (${roleLabel})`);
        $('#modalLeaderSamplingSubtitle').text('Memuat data spesifikasi part dan titik ukur...');

        $modal.modal('show');

        const url = config.samplingDataUrl.replace(':id', checksheetId);
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $alert.removeClass('d-none');
                    $('#modalLeaderSamplingAlertMsg').text(res.message || 'Gagal memuat data spesifikasi dimensi.');
                    $loader.addClass('d-none');
                    return;
                }

                const cs = res.checksheet || {};
                $('#modalPartName').text(cs.part_name || '-');
                $('#modalPartNumber').text(cs.part_number || '-');
                $('#modalMachineDate').text(`${cs.code_machine || '-'} / ${cs.date || '-'} (Shift ${cs.shift || '-'})`);
                $('#modalInspectorName').text(cs.inspector || '-');
                $('#modalInspectorTools').text(cs.inspector_tools || '-');
                $('#modalLeaderSamplingSubtitle').text(`Part: ${cs.part_name || '-'} (${cs.part_number || '-'})`);

                // Isi datalist alat ukur
                const $datalist = $('#leaderToolsList');
                $datalist.empty();
                if (Array.isArray(res.tools_list)) {
                    res.tools_list.forEach(function (tool) {
                        $datalist.append($('<option>').attr('value', tool));
                    });
                }

                // Default alat ukur jika belum diisi: samakan dengan inspector atau yang sudah ada
                const defaultTool = res.existing_leader_tool || cs.inspector_tools || '';
                $('#leaderAlatUkurInput').val(defaultTool !== '-' ? defaultTool : '');

                currentStandards = res.standards || {};
                currentActivePoints = res.active_points || [];
                if (Array.isArray(res.active_cavities) && res.active_cavities.length > 0) {
                    currentActiveCavities = res.active_cavities;
                } else {
                    currentActiveCavities = [];
                    let maxCav = Math.max(1, parseInt(res.max_cavity, 10) || 1);
                    for (let i = 1; i <= maxCav; i++) {
                        currentActiveCavities.push(i);
                    }
                }
                currentMaxCavity = currentActiveCavities.length;

                renderSamplingTable(res.existing_leader_check);

                $loader.addClass('d-none');
                $tableWrapper.removeClass('d-none');
                $('#btnSubmitLeaderSampling').prop('disabled', false);
            },
            complete: function () {
                isOpeningModal = false;
            },
            error: function (xhr) {
                $loader.addClass('d-none');
                $alert.removeClass('d-none');
                $('#modalLeaderSamplingAlertMsg').text('Terjadi kesalahan saat memuat data. Silakan coba kembali.');
            }
        });
    };

    /**
     * Render tabel input pengukuran dinamis (seluruh cavity & point)
     */
    function renderSamplingTable(existingData) {
        const $thead = $('#modalLeaderTableHead');
        const $tbody = $('#modalLeaderTableBody');
        $thead.empty();
        $tbody.empty();

        const points = currentActivePoints;
        const stds = currentStandards;

        // Cek baris header: Std, Min, Max, Tol
        let hasStd = false, hasMin = false, hasMax = false, hasTol = false;
        points.forEach(function (pt) {
            const s = stds[pt] || stds[String(pt)] || {};
            const bounds = calculateBounds(s);
            if (s.size !== null && s.size !== undefined && s.size !== '' && s.size !== '-') hasStd = true;
            if (bounds.min !== null || (s.min !== null && s.min !== undefined && s.min !== '' && s.min !== '-')) hasMin = true;
            if (bounds.max !== null || (s.max !== null && s.max !== undefined && s.max !== '' && s.max !== '-')) hasMax = true;
            if (s.tolerance !== null && s.tolerance !== undefined && s.tolerance !== '' && s.tolerance !== '-') hasTol = true;
        });

        if (hasStd) {
            let tr = $('<tr class="std-row">').append(
                $('<th style="min-width: 65px; position: sticky; left: 0; z-index: 2; background: #f8fafc !important; vertical-align: middle;">').text('Std')
            );
            points.forEach(function (pt) {
                const s = stds[pt] || stds[String(pt)] || {};
                tr.append($('<th>').text(s.size ?? '-'));
            });
            $thead.append(tr);
        }

        if (hasMin) {
            let tr = $('<tr class="std-row">').append(
                $('<th style="min-width: 65px; position: sticky; left: 0; z-index: 2; background: #f8fafc !important; vertical-align: middle;">').text('Min')
            );
            points.forEach(function (pt) {
                const s = stds[pt] || stds[String(pt)] || {};
                const bounds = calculateBounds(s);
                let dispMin = bounds.min !== null ? bounds.min : (s.min ?? '-');
                tr.append($('<th>').text(dispMin));
            });
            $thead.append(tr);
        }

        if (hasMax) {
            let tr = $('<tr class="std-row">').append(
                $('<th style="min-width: 65px; position: sticky; left: 0; z-index: 2; background: #f8fafc !important; vertical-align: middle;">').text('Max')
            );
            points.forEach(function (pt) {
                const s = stds[pt] || stds[String(pt)] || {};
                const bounds = calculateBounds(s);
                let dispMax = bounds.max !== null ? bounds.max : (s.max ?? '-');
                tr.append($('<th>').text(dispMax));
            });
            $thead.append(tr);
        }

        if (hasTol) {
            let tr = $('<tr class="std-row">').append(
                $('<th style="min-width: 65px; position: sticky; left: 0; z-index: 2; background: #f8fafc !important; vertical-align: middle;">').text('Tol')
            );
            points.forEach(function (pt) {
                const s = stds[pt] || stds[String(pt)] || {};
                tr.append($('<th>').text(s.tolerance ?? '-'));
            });
            $thead.append(tr);
        }

        // Header Utama (CAVITY, POINT 1, POINT 2, ...) sama seperti create in-process / fpa
        let trHead = $('<tr>').append(
            $('<th style="min-width: 65px; position: sticky; left: 0; z-index: 2; background: #ffffff !important; vertical-align: middle;">')
                .append($('<div class="font-weight-bold" style="font-size: 0.7rem; color: #334155;">').text('CAVITY'))
        );
        points.forEach(function (pt) {
            trHead.append(
                $('<th class="point-header text-center align-middle p-1" style="min-width: 85px; background: #ffffff !important;">')
                    .attr('data-point', pt)
                    .append($('<div class="font-weight-bold" style="font-size: 0.7rem; color: #334155;">').text(`POINT ${pt}`))
            );
        });
        $thead.append(trHead);

        // Baris Input Cavity (Hanya cavity yang diisi oleh inspector)
        let totalInputs = 0;
        let filledInputs = 0;

        const cavities = (currentActiveCavities && currentActiveCavities.length > 0) ? currentActiveCavities : [1];

        cavities.forEach(function (cav) {
            let tr = $('<tr>');
            tr.append(
                $('<td class="text-center font-weight-bold align-middle" style="position: sticky; left: 0; z-index: 1; font-size: 0.72rem; color: #334155; background: #ffffff !important;">')
                    .text(`Cav ${cav}`)
            );

            points.forEach(function (pt) {
                totalInputs++;
                const s = stds[pt] || stds[String(pt)] || {};
                const bounds = calculateBounds(s);

                // Ambil nilai existing jika ada
                let existingVal = '';
                if (existingData) {
                    if (existingData[cav] && existingData[cav][pt] !== undefined) {
                        existingVal = existingData[cav][pt];
                    } else if (existingData[String(cav)] && existingData[String(cav)][String(pt)] !== undefined) {
                        existingVal = existingData[String(cav)][String(pt)];
                    } else if (existingData[`cav${cav}`] && existingData[`cav${cav}`][pt] !== undefined) {
                        existingVal = existingData[`cav${cav}`][pt];
                    }
                }

                if (existingVal !== '' && existingVal !== null && existingVal !== undefined && existingVal !== '-') {
                    filledInputs++;
                }

                const $input = $('<input>')
                    .attr('type', 'text')
                    .addClass('form-control form-control-sm dimension-input leader-dim-input')
                    .attr('placeholder', `P${pt}`)
                    .attr('data-cav', cav)
                    .attr('data-point', pt)
                    .val(existingVal !== '-' ? existingVal : '');

                if (bounds.min !== null) $input.attr('data-min', bounds.min);
                if (bounds.max !== null) $input.attr('data-max', bounds.max);

                // Initial validation check
                validateInputCell($input, false);

                tr.append($('<td class="point-cell p-1 text-center" style="background: #ffffff !important;">').append($input));
            });

            $tbody.append(tr);
        });

        updateSamplingJudgmentAndProgress();
    }

    /**
     * Validasi satu sel input pengukuran terhadap batas min/max dan format OK/NG (Auto Judgment per Titik)
     */
    function validateInputCell($input, updateProgress = true) {
        let rawVal = ($input.val() || '').trim().replace(',', '.');
        if (rawVal.startsWith('+0') && rawVal.length > 2 && rawVal[2] !== '.') {
            rawVal = rawVal.replace(/^\+0/, '');
            $input.val(rawVal);
        }
        const minAttr = $input.attr('data-min');
        const maxAttr = $input.attr('data-max');

        $input.removeClass('is-invalid is-valid border-danger font-weight-bold text-danger text-success');

        if (rawVal === '' || rawVal === '-') {
            if (updateProgress) updateSamplingJudgmentAndProgress();
            return false;
        }

        const upper = rawVal.toUpperCase();
        if (upper === 'OK') {
            $input.addClass('is-valid text-success font-weight-bold');
            if (updateProgress) updateSamplingJudgmentAndProgress();
            return true;
        } else if (upper === 'NG') {
            // Kolom merah tetapi tetap bisa simpan & approval
            $input.addClass('is-invalid text-danger font-weight-bold');
            if (updateProgress) updateSamplingJudgmentAndProgress();
            return true;
        }

        const numVal = parseFloat(rawVal);
        if (isNaN(numVal)) {
            // Teks selain angka/OK/NG ditandai merah
            $input.addClass('is-invalid text-danger font-weight-bold');
            if (updateProgress) updateSamplingJudgmentAndProgress();
            return true;
        }

        let isOut = false;
        const eps = 0.00001;

        if (minAttr !== undefined && minAttr !== null && minAttr !== '') {
            const minVal = parseFloat(minAttr);
            if (!isNaN(minVal) && numVal < (minVal - eps)) {
                isOut = true;
            }
        }

        if (maxAttr !== undefined && maxAttr !== null && maxAttr !== '') {
            const maxVal = parseFloat(maxAttr);
            if (!isNaN(maxVal) && numVal > (maxVal + eps)) {
                isOut = true;
            }
        }

        if (isOut) {
            $input.addClass('is-invalid text-danger font-weight-bold');
        } else {
            $input.addClass('is-valid text-success font-weight-bold');
        }

        if (updateProgress) updateSamplingJudgmentAndProgress();
        return true;
    }

    /**
     * Update real-time Auto Judgment badge dan progress text seperti di halaman create
     */
    function updateSamplingJudgmentAndProgress() {
        let total = 0;
        let filled = 0;
        let hasInvalid = false;

        $('.leader-dim-input').each(function () {
            total++;
            const val = $(this).val().trim();
            if (val !== '') {
                filled++;
            }
            if ($(this).hasClass('is-invalid')) {
                hasInvalid = true;
            }
        });

        const isComplete = (total > 0 && filled === total);

        // Update Progress Text
        $('#modalInputProgress').html(
            `Terisi: <strong>${filled} / ${total}</strong> pengukuran ${isComplete ? '<span class="text-success font-weight-bold ml-1">(Lengkap ✓)</span>' : ''}`
        );

        // Auto Judgment Badge
        const $badge = $('#modalLeaderJudgmentBadge');
        if ($badge.length) {
            if (hasInvalid) {
                // Ada nilai out of spec / NG
                $badge
                    .removeClass('badge-light badge-success border-success text-muted')
                    .addClass('badge-danger')
                    .css({ 'background-color': '#dc3545', 'color': '#ffffff', 'border-color': '#dc3545' })
                    .html('NG');
            } else if (isComplete) {
                // Lengkap dan semua titik in-spec / OK
                $badge
                    .removeClass('badge-light badge-danger border-danger text-muted')
                    .addClass('badge-success')
                    .css({ 'background-color': '#28a745', 'color': '#ffffff', 'border-color': '#28a745' })
                    .html('OK');
            } else {
                // Belum lengkap dan belum ada yang NG
                $badge
                    .removeClass('badge-success badge-danger border-success border-danger')
                    .addClass('badge-light text-muted')
                    .css({ 'background-color': '#f1f5f9', 'color': '#475569', 'border-color': '#cbd5e1' })
                    .html('-');
            }
        }
    }

    // Event listener: realtime input check
    $(document).on('input keyup change', '.leader-dim-input', function () {
        validateInputCell($(this), true);
    });

    /**
     * Handle Submit Form Sampling & Approve
     */
    $(document).on('click', '#btnSubmitLeaderSampling', function (e) {
        e.preventDefault();

        if (!currentChecksheetId) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'ID Checksheet tidak valid.'
            });
            return;
        }

        const alatUkur = $('#leaderAlatUkurInput').val().trim();
        if (!alatUkur) {
            Swal.fire({
                icon: 'warning',
                title: 'Alat Ukur Wajib Diisi',
                text: 'Silakan pilih atau ketik alat ukur sampling yang Anda gunakan.',
                confirmButtonColor: '#3085d6'
            });
            $('#leaderAlatUkurInput').focus();
            return;
        }

        // Validasi seluruh input terisi lengkap
        let missingCount = 0;
        let firstEmpty = null;
        const leaderData = {};

        $('.leader-dim-input').each(function () {
            const $this = $(this);
            const val = $this.val().trim().replace(',', '.');
            const cav = $this.attr('data-cav');
            const pt = $this.attr('data-point');

            if (val === '') {
                missingCount++;
                if (!firstEmpty) firstEmpty = $this;
            } else {
                if (!leaderData[cav]) {
                    leaderData[cav] = {};
                }
                leaderData[cav][pt] = val;
            }
        });

        if (missingCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pengukuran Belum Lengkap',
                text: `Masih ada ${missingCount} titik pengukuran yang belum diisi. Anda wajib mengisi seluruh cavity dan point pada part ini.`,
                confirmButtonColor: '#3085d6'
            });
            if (firstEmpty) {
                firstEmpty.focus();
                firstEmpty.addClass('border-danger');
            }
            return;
        }

        const config = getConfig();
        const approveUrl = config.approveUrl ? config.approveUrl.replace(':id', currentChecksheetId) : null;

        if (!approveUrl) {
            Swal.fire({
                icon: 'error',
                title: 'Konfigurasi Error',
                text: 'URL Approval belum ditentukan.'
            });
            return;
        }

        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

        // Siapkan payload POST
        const payload = {
            _token: config.csrfToken || $('meta[name="csrf-token"]').attr('content'),
            type: 'kashift',
            leader_dimension_check: JSON.stringify(leaderData),
            leader_alat_ukur: alatUkur
        };

        $.ajax({
            url: approveUrl,
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#modalLeaderDimensionSampling').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disetujui',
                        text: 'Sampling pengukuran mandiri berhasil disimpan dan data telah di-approve.',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(function () {
                        window.location.reload();
                    });
                } else {
                    $btn.prop('disabled', false).html(originalText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: res.message || 'Terjadi kesalahan saat memproses approval.'
                    });
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html(originalText);
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
                let errMsg = 'Terjadi kesalahan sistem saat menyimpan approval.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    errMsg = 'Terjadi kesalahan sistem (' + xhr.status + ': ' + xhr.statusText + ')';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errMsg
                });
            }
        });
    });

    /**
     * Intersep response AJAX form reguler: jika memerlukan leader sampling, langsung buka modal!
     */
    $(document).ajaxSuccess(function (event, xhr, settings) {
        let res = xhr.responseJSON;
        if (!res && xhr.responseText) {
            try { res = JSON.parse(xhr.responseText); } catch (e) {}
        }
        if (res && res.requires_leader_sampling && res.checksheet_id) {
            window.openLeaderSamplingModal(res.checksheet_id);
        }
    });

    $(document).ajaxError(function (event, xhr, settings, thrownError) {
        let res = xhr.responseJSON;
        if (!res && xhr.responseText) {
            try { res = JSON.parse(xhr.responseText); } catch (e) {}
        }
        if (res && res.requires_leader_sampling && res.checksheet_id) {
            window.openLeaderSamplingModal(res.checksheet_id);
        }
    });

    $(document).on('hidden.bs.modal', '#modalLeaderDimensionSampling', function () {
        isOpeningModal = false;
        currentChecksheetId = null;
    });

})(jQuery);
