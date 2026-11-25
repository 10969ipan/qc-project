/**
 * Modul JavaScript untuk Checksheet Leveling Inspector
 * Mematuhi standar eksternal JS (bebas inline script pada Blade)
 */
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var cfgEl = document.getElementById('leveling-config');
    var cfg = cfgEl ? {
        plant: cfgEl.dataset.plant || 'karawang',
        auditId: cfgEl.dataset.auditId || '',
        today: cfgEl.dataset.today || '',
        storeAuditUrl: cfgEl.dataset.storeAuditUrl || '',
        deleteAuditBase: cfgEl.dataset.deleteAuditBase || '',
        saveResultUrl: cfgEl.dataset.saveResultUrl || '',
        bulkResultUrl: cfgEl.dataset.bulkResultUrl || '',
        uploadEvidenceUrl: cfgEl.dataset.uploadEvidenceUrl || '',
        deleteEvidenceBase: cfgEl.dataset.deleteEvidenceBase || '',
        lookupInspectorsUrl: cfgEl.dataset.lookupInspectorsUrl || '',
        lookupAuditsUrl: cfgEl.dataset.lookupAuditsUrl || '',
        lookupBagianUrl: cfgEl.dataset.lookupBagianUrl || '',
        lookupPartsUrl: cfgEl.dataset.lookupPartsUrl || '',
        ngListUrl: cfgEl.dataset.ngListUrl || '',
        ngPrintUrl: cfgEl.dataset.ngPrintUrl || '',
        ngBaseUrl: cfgEl.dataset.ngBaseUrl || '',
        masterListUrl: cfgEl.dataset.masterListUrl || '',
        masterStoreUrl: cfgEl.dataset.masterStoreUrl || '',
        masterBaseUrl: cfgEl.dataset.masterBaseUrl || '',
        printUrl: cfgEl.dataset.printUrl || ''
    } : {};

    var MAX_FILE_BYTES = 10 * 1024 * 1024; // 10 MB
    var masterChanged = false;
    var ngDataCache = {};

    function isMobileOrTablet() {
        var ua = navigator.userAgent || '';
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua)) {
            return true;
        }
        if (/Macintosh/i.test(ua) && navigator.maxTouchPoints && navigator.maxTouchPoints > 1) {
            return true;
        }
        if (!/Windows NT/i.test(ua) && window.matchMedia && window.matchMedia('(pointer: coarse)').matches) {
            return true;
        }
        if (window.matchMedia && window.matchMedia('(max-width: 1024px) and (pointer: coarse)').matches) {
            return true;
        }
        return false;
    }

    function syncSmartCaptureInputs($scope) {
        var useCamera = isMobileOrTablet();
        var $inputs = $scope ? $scope.find('.js-smart-capture-input') : $('.js-smart-capture-input');
        $inputs.each(function () {
            if (useCamera) {
                this.setAttribute('capture', 'environment');
                this.removeAttribute('multiple');
            } else {
                this.removeAttribute('capture');
                this.setAttribute('multiple', 'multiple');
            }
        });
    }

    syncSmartCaptureInputs();
    $(window).on('resize', function () {
        syncSmartCaptureInputs();
    });
    $(document).on('mousedown touchstart', '.js-smart-upload-trigger', function () {
        syncSmartCaptureInputs($(this));
    });

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toast(icon, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon || 'success',
                title: message,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true
            });
        } else {
            alert(message);
        }
    }

    function notify(type, message) {
        if (type === 'success') {
            toast('success', message);
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: type === 'error' ? 'Gagal' : 'Perhatian',
                text: message,
                showConfirmButton: true
            });
        } else {
            alert(message);
        }
    }

    function extractError(xhr, fallback) {
        if (xhr && xhr.responseJSON) {
            if (xhr.responseJSON.errors) {
                var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                if (firstKey && xhr.responseJSON.errors[firstKey][0]) {
                    return xhr.responseJSON.errors[firstKey][0];
                }
            }
            if (xhr.responseJSON.message) {
                return xhr.responseJSON.message;
            }
        }
        return fallback || 'Terjadi kesalahan saat memproses permintaan.';
    }

    // -------------------------------------------------------------------------
    // 1. Filter Bar — Ketik Cari Inspector & Ketik Cari Riwayat Audit (AJAX)
    // -------------------------------------------------------------------------
    var filterInspTimer = null;
    var filterAuditTimer = null;

    function loadFilterInspectors(q) {
        var $dd = $('#filterInspectorDropdown');
        $dd.html('<div class="lvl-search-item text-muted">Mencari...</div>').show();

        $.get(cfg.lookupInspectorsUrl, { plant: cfg.plant, q: q || '' })
            .done(function (res) {
                var rows = (res && res.data) ? res.data : [];
                var html = '<div class="lvl-search-item font-weight-bold text-primary js-pick-filter-inspector" data-id="" data-name="">-- Semua Inspector --</div>';
                if (!rows.length) {
                    html += '<div class="lvl-search-item text-muted">Inspector tidak ditemukan</div>';
                } else {
                    rows.forEach(function (u) {
                        html += '<div class="lvl-search-item js-pick-filter-inspector" data-id="' + u.id + '" data-name="' + escHtml(u.name) + '">' + escHtml(u.name) + '</div>';
                    });
                }
                $dd.html(html);
            })
            .fail(function () {
                $dd.html('<div class="lvl-search-item text-danger">Gagal memuat data inspector</div>');
            });
    }

    function loadFilterAudits(q) {
        var $dd = $('#filterAuditDropdown');
        $dd.html('<div class="lvl-search-item text-muted">Mencari riwayat audit...</div>').show();

        $.get(cfg.lookupAuditsUrl, {
            plant: cfg.plant,
            q: q || '',
            date: $('#filterDate').val() || '',
            inspector_id: $('#filterInspectorId').val() || ''
        })
            .done(function (res) {
                var rows = (res && res.data) ? res.data : [];
                if (!rows.length) {
                    $dd.html('<div class="lvl-search-item text-muted">Riwayat audit tidak ditemukan</div>');
                    return;
                }
                var html = '';
                rows.forEach(function (a) {
                    html += '<div class="lvl-search-item js-pick-filter-audit" data-id="' + a.id + '" data-label="' + escHtml(a.label) + '">' + escHtml(a.label) + '</div>';
                });
                $dd.html(html);
            })
            .fail(function () {
                $dd.html('<div class="lvl-search-item text-danger">Gagal memuat riwayat audit</div>');
            });
    }

    $('#filterDate').on('change', function () {
        $('#filterAuditId').val('');
        $('#levelingFilterForm').submit();
    });

    $('#filterInspectorSearch').on('focus', function () {
        loadFilterInspectors($(this).val());
    }).on('input', function () {
        var val = $(this).val();
        $('#btnClearFilterInspector').toggleClass('d-none', !val);
        $('#filterInspectorGroup').toggleClass('has-clear', !!val);
        clearTimeout(filterInspTimer);
        filterInspTimer = setTimeout(function () {
            loadFilterInspectors(val);
        }, 180);
    });

    $(document).on('click', '.js-pick-filter-inspector', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#filterInspectorId').val(id || '');
        $('#filterInspectorSearch').val(name || '');
        $('#filterInspectorDropdown').hide();
        $('#filterDate').val('');
        $('#filterAuditId').val('');
        $('#levelingFilterForm').submit();
    });

    $('#btnClearFilterInspector').on('click', function () {
        $('#filterInspectorId').val('');
        $('#filterInspectorSearch').val('');
        $('#filterDate').val('');
        $('#filterAuditId').val('');
        $('#levelingFilterForm').submit();
    });

    $('#filterAuditSearch').on('focus', function () {
        // Saat fokus, tampilkan daftar terbaru sesuai filter aktif
        loadFilterAudits('');
    }).on('input', function () {
        var val = $(this).val();
        $('#btnClearFilterAudit').toggleClass('d-none', !val);
        $('#filterAuditGroup').toggleClass('has-clear', !!val);
        clearTimeout(filterAuditTimer);
        filterAuditTimer = setTimeout(function () {
            loadFilterAudits(val);
        }, 180);
    });

    $(document).on('click', '.js-pick-filter-audit', function () {
        $('#filterAuditId').val($(this).data('id'));
        $('#filterAuditSearch').val($(this).data('label'));
        $('#filterAuditDropdown').hide();
        $('#levelingFilterForm').submit();
    });

    $('#btnClearFilterAudit').on('click', function () {
        $('#filterAuditId').val('');
        $('#filterAuditSearch').val('');
        $('#btnClearFilterAudit').addClass('d-none');
        $('#filterAuditGroup').removeClass('has-clear');
        loadFilterAudits('');
        $('#filterAuditSearch').trigger('focus');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#filterInspectorSearch, #filterInspectorDropdown, #btnClearFilterInspector').length) {
            $('#filterInspectorDropdown').hide();
        }
        if (!$(e.target).closest('#filterAuditSearch, #filterAuditDropdown, #btnClearFilterAudit').length) {
            $('#filterAuditDropdown').hide();
        }
    });

    // Direct Print Cetak (Tanpa Buka Halaman / Tab Baru, pola Dandory Quality)
    $(document).on('click', '.btn-print-direct, #btnPrintLeveling', function (e) {
        e.preventDefault();
        var printUrl = $(this).attr('href') || $(this).data('print-url') || cfg.printUrl;
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

    $('#btnDeleteAudit').on('click', function () {
        var auditId = $(this).data('audit-id');
        if (!auditId || !cfg.deleteAuditBase) return;

        function doDeleteAudit() {
            $.ajax({
                url: cfg.deleteAuditBase + '/' + auditId,
                type: 'POST',
                data: { _method: 'DELETE', plant: cfg.plant }
            })
                .done(function (res) {
                    toast('success', (res && res.message) || 'Audit berhasil dihapus.');
                    setTimeout(function () {
                        window.location.href = (res && res.redirect_url) ? res.redirect_url : window.location.pathname + '?plant=' + encodeURIComponent(cfg.plant);
                    }, 700);
                })
                .fail(function (xhr) {
                    notify('error', extractError(xhr, 'Gagal menghapus data audit.'));
                });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Sesi Audit Ini?',
                text: 'Seluruh hasil penilaian dan foto evidence pada sesi audit ini akan ikut terhapus permanen.',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus Audit',
                cancelButtonText: 'Batal'
            }).then(function (r) {
                if (r.isConfirmed) doDeleteAudit();
            });
        } else if (confirm('Hapus seluruh data sesi audit ini?')) {
            doDeleteAudit();
        }
    });

    // -------------------------------------------------------------------------
    // 2. Modal Audit Baru (Searchable Inspector -> Auto Bagian -> Searchable Dandory Part)
    // -------------------------------------------------------------------------
    var inspectorTimer = null;
    var partTimer = null;

    function loadInspectorsDropdown(q) {
        var $dd = $('#auditInspectorDropdown');
        $dd.html('<div class="lvl-search-item text-muted">Mencari...</div>').show();

        $.get(cfg.lookupInspectorsUrl, { plant: cfg.plant, q: q || '' })
            .done(function (res) {
                var rows = (res && res.data) ? res.data : [];
                if (!rows.length) {
                    $dd.html('<div class="lvl-search-item text-muted">Inspector tidak ditemukan</div>');
                    return;
                }
                var html = '';
                rows.forEach(function (u) {
                    html += '<div class="lvl-search-item js-pick-inspector" data-id="' + u.id + '" data-name="' + escHtml(u.name) + '">' + escHtml(u.name) + '</div>';
                });
                $dd.html(html);
            })
            .fail(function () {
                $dd.html('<div class="lvl-search-item text-danger">Gagal memuat data inspector</div>');
            });
    }

    function isMachineBasedBagian(bagian) {
        var b = String(bagian || '').trim().toLowerCase();
        return b.indexOf('inproses') !== -1 || b.indexOf('in process') !== -1 || b.indexOf('in-process') !== -1;
    }

    function formatMejaMesinOption(raw, isMachine) {
        var val = String(raw || '').trim();
        if (!val) return '';
        if (isMachine) {
            return val.replace(/^meja\b/i, 'Mesin');
        }
        return val.replace(/^mesin\b/i, 'Meja');
    }

    function refreshMejaMesinOptions(bagian, preferredVal) {
        var $sel = $('#auditMejaMesin');
        if (!$sel.length) return;

        var rawList = $sel.data('plant-tables');
        if (!Array.isArray(rawList)) {
            try {
                rawList = JSON.parse($sel.attr('data-plant-tables') || '[]');
            } catch (e) {
                rawList = [];
            }
        }

        var isMachine = isMachineBasedBagian(bagian);
        var targetVal = (typeof preferredVal !== 'undefined')
            ? formatMejaMesinOption(preferredVal, isMachine)
            : formatMejaMesinOption($sel.val(), isMachine);

        var optionsHtml = '<option value="">-- Pilih ' + (isMachine ? 'Mesin' : 'Meja / Mesin') + ' --</option>';
        var matched = false;

        rawList.forEach(function (tbl) {
            var formatted = formatMejaMesinOption(tbl, isMachine);
            if (!formatted) return;
            if (targetVal && formatted.toLowerCase() === targetVal.toLowerCase()) {
                matched = true;
                targetVal = formatted;
            }
            optionsHtml += '<option value="' + escHtml(formatted) + '">' + escHtml(formatted) + '</option>';
        });

        if (targetVal && !matched) {
            optionsHtml += '<option value="' + escHtml(targetVal) + '">' + escHtml(targetVal) + '</option>';
        }

        $sel.html(optionsHtml);
        $sel.val(targetVal || '');
    }

    $('#auditBagian').on('change', function () {
        refreshMejaMesinOptions($(this).val());
    });

    function loadPartsDropdown(q, autoFillBagian) {
        var inspectorId = $('#auditInspectorId').val();
        if (!inspectorId) return;

        var $dd = $('#auditPartDropdown');
        if (!autoFillBagian) {
            $dd.html('<div class="lvl-search-item text-muted">Mencari part...</div>').show();
        }

        $.get(cfg.lookupPartsUrl, { plant: cfg.plant, inspector_id: inspectorId, q: q || '' })
            .done(function (res) {
                if (autoFillBagian) {
                    if (res && res.bagian) {
                        if ($('#auditBagian option[value="' + res.bagian.replace(/"/g, '\\"') + '"]').length === 0) {
                            $('#auditBagian').append('<option value="' + escHtml(res.bagian) + '">' + escHtml(res.bagian) + '</option>');
                        }
                        $('#auditBagian').val(res.bagian);
                        $('#auditBagianHint').text('Terisi otomatis dari jadwal Kepatuhan Operator.');
                    } else {
                        $('#auditBagian').val('');
                        $('#auditBagianHint').text('Jadwal kepatuhan belum memiliki bagian, silakan pilih manual.');
                    }
                    refreshMejaMesinOptions(
                        (res && res.bagian) ? res.bagian : '',
                        (res && res.default_table) ? res.default_table : ''
                    );
                }

                var rows = (res && res.data) ? res.data : [];
                if (res && res.fallback_all) {
                    $('#auditPartHint').text('Kategori dandory belum diset, menampilkan semua part plant ini.');
                } else {
                    $('#auditPartHint').text('Part difilter sesuai kategori Dandory inspector terpilih.');
                }

                if (!rows.length) {
                    $dd.html('<div class="lvl-search-item text-muted">Part tidak ditemukan</div>');
                    return;
                }
                var html = '';
                rows.forEach(function (it) {
                    html += '<div class="lvl-search-item js-pick-part" data-id="' + escHtml(it.id) + '" data-label="' + escHtml(it.label) + '">' + escHtml(it.label) + '</div>';
                });
                $dd.html(html);
            })
            .fail(function () {
                if (autoFillBagian) {
                    $('#auditBagianHint').text('');
                }
                $dd.html('<div class="lvl-search-item text-danger">Gagal memuat data part</div>');
            });
    }

    function resetNewAuditForm() {
        $('#auditInspectorId').val('');
        $('#auditInspectorSearch').val('');
        $('#auditBagian').val('');
        $('#auditBagianHint').text('');
        refreshMejaMesinOptions('', '');
        $('#auditItemId').val('');
        $('#auditPartSearch').val('').prop('disabled', true).attr('placeholder', 'Pilih inspector dulu, lalu ketik part...');
        $('#auditPartHint').text('');
        $('#auditInspectorDropdown, #auditPartDropdown').hide();
    }

    $(document).on('click', '.js-toggle-new-audit', function () {
        var $panel = $('#newAuditPanel');
        if ($panel.hasClass('d-none')) {
            $panel.removeClass('d-none');
            $('#auditInspectorSearch').trigger('focus');
        } else {
            $panel.addClass('d-none');
            resetNewAuditForm();
        }
    });

    $(document).on('click', '.js-close-new-audit', function () {
        $('#newAuditPanel').addClass('d-none');
        resetNewAuditForm();
    });

    $('#auditInspectorSearch').on('focus', function () {
        loadInspectorsDropdown($(this).val());
    }).on('input', function () {
        $('#auditInspectorId').val('');
        $('#auditItemId').val('');
        $('#auditPartSearch').val('').prop('disabled', true);
        clearTimeout(inspectorTimer);
        var val = $(this).val();
        inspectorTimer = setTimeout(function () {
            loadInspectorsDropdown(val);
        }, 200);
    });

    $(document).on('click', '.js-pick-inspector', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#auditInspectorId').val(id);
        $('#auditInspectorSearch').val(name);
        $('#auditInspectorDropdown').hide();

        // Reset part & aktifkan pencarian part
        $('#auditItemId').val('');
        $('#auditPartSearch').val('').prop('disabled', false).attr('placeholder', 'Ketik nama part / no. part...');
        $('#auditPartHint').text('');
        $('#auditBagianHint').text('Mengambil bagian & daftar part...');

        // 1 request gabungan untuk auto-fill Bagian + preload daftar Part Dandory
        loadPartsDropdown('', true);
    });

    $('#auditPartSearch').on('focus', function () {
        if ($('#auditInspectorId').val()) {
            loadPartsDropdown($(this).val());
        }
    }).on('input', function () {
        $('#auditItemId').val('');
        clearTimeout(partTimer);
        var val = $(this).val();
        partTimer = setTimeout(function () {
            loadPartsDropdown(val);
        }, 200);
    });

    $(document).on('click', '.js-pick-part', function () {
        $('#auditItemId').val($(this).data('id'));
        $('#auditPartSearch').val($(this).data('label'));
        $('#auditPartDropdown').hide();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#auditInspectorSearch, #auditInspectorDropdown').length) {
            $('#auditInspectorDropdown').hide();
        }
        if (!$(e.target).closest('#auditPartSearch, #auditPartDropdown').length) {
            $('#auditPartDropdown').hide();
        }
    });

    $('#newAuditForm').on('submit', function (e) {
        e.preventDefault();
        var inspectorId = $('#auditInspectorId').val();
        var bagian = $('#auditBagian').val();
        var mejaMesin = ($('#auditMejaMesin').val() || '').trim();
        var itemId = $('#auditItemId').val();

        var missing = [];
        if (!inspectorId) missing.push('Nama Inspector');
        if (!bagian) missing.push('Proses / Line');
        if (!itemId) missing.push('Nama Part / No. Part');

        if (missing.length) {
            notify('warning', 'Mohon lengkapi field berikut: ' + missing.join(', ') + '.');
            if (!inspectorId) $('#auditInspectorSearch').trigger('focus');
            else if (!bagian) $('#auditBagian').trigger('focus');
            else if (!itemId) $('#auditPartSearch').trigger('focus');
            return;
        }

        var $btn = $('#btnSubmitNewAudit').prop('disabled', true).text('Menyimpan...');
        $.post(cfg.storeAuditUrl, {
            plant: cfg.plant,
            inspector_id: inspectorId,
            bagian: bagian,
            meja_mesin: mejaMesin,
            item_id: itemId
        })
            .done(function (res) {
                if (res && res.redirect_url) {
                    window.location.href = res.redirect_url;
                } else {
                    window.location.reload();
                }
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal membuat audit baru.'));
                $btn.prop('disabled', false).text('Mulai Audit');
            });
    });

    // -------------------------------------------------------------------------
    // 3. Toggle OK / NG pada Tabel Point Check + Update Result Level per PRINSIP
    // -------------------------------------------------------------------------
    function updateLevelResultCell(prinsip, lvlVal) {
        var $cell = $('.js-level-result[data-result-level="' + prinsip + '"]');
        if (!$cell.length) return;
        $cell.removeClass('text-success text-primary text-dark text-danger text-muted');
        if (lvlVal === '3') $cell.addClass('text-success');
        else if (lvlVal === '2') $cell.addClass('text-primary');
        else if (lvlVal === '1') $cell.addClass('text-dark');
        else if (lvlVal === '0') $cell.addClass('text-danger');
        else $cell.addClass('text-muted');
        $cell.text(lvlVal);
    }

    function updateOpenNgCounter(count) {
        $('#openNgCountText').text(count > 0 ? '(' + count + ')' : '');
    }

    function applyRowResultUI($row, resultId, resultVal, ngStatus) {
        $row.attr('data-result-id', resultId || '');
        $row.attr('data-ng-status', ngStatus || '');
        $row.find('.js-delete-evidence').attr('data-result-id', resultId || '');

        var $btnWrap = $row.find('.js-result-btns');
        var $display = $row.find('.js-result-display');
        var $statusTxt = $row.find('.js-ng-status-text');
        var $reqTxt = $row.find('.js-evidence-required');
        var hasPhoto = $row.find('.js-evidence-box img').length > 0;

        if (resultVal === 'OK' || resultVal === 'NG') {
            $btnWrap.removeClass('d-inline-flex').addClass('d-none');
            $display.removeClass('d-none text-success text-danger')
                .addClass(resultVal === 'OK' ? 'text-success' : 'text-danger')
                .text(resultVal);
        } else {
            $display.addClass('d-none').removeClass('text-success text-danger').text('');
            $btnWrap.removeClass('d-none').addClass('d-inline-flex');
        }

        if (resultVal === 'NG') {
            $row.addClass('row-ng');
            $statusTxt.removeClass('text-success text-warning text-danger font-weight-bold');
            if (ngStatus === 'closed') {
                $statusTxt.addClass('text-success font-weight-bold').text('Closed');
            } else if (ngStatus === 'waiting_verify') {
                $statusTxt.addClass('text-warning font-weight-bold').text('Waiting Verify');
            } else {
                $statusTxt.addClass('text-danger font-weight-bold').text('Open');
            }
            $reqTxt.toggleClass('d-none', hasPhoto);
        } else {
            $row.removeClass('row-ng');
            $row.attr('data-has-correction', '0');
            $statusTxt.text('');
            $reqTxt.addClass('d-none');
        }

        return hasPhoto;
    }

    function sendResultUpdate($row, itemId, nextVal) {
        if (!cfg.auditId) return;
        $row.find('.js-result-btn').prop('disabled', true);

        $.post(cfg.saveResultUrl, {
            audit_id: cfg.auditId,
            check_item_id: itemId,
            result: nextVal
        })
            .done(function (res) {
                if (!res || !res.success) return;

                var hasPhoto = applyRowResultUI($row, res.result_id, res.result, res.ng_status);
                if (res.result === 'NG' && !hasPhoto) {
                    toast('warning', 'Wajib upload foto evidence untuk temuan NG!');
                }

                updateLevelResultCell(res.prinsip, res.achieved_level);

                if (res.summary) {
                    $('#sumTotalOk').text(res.summary.total_ok);
                    $('#sumTotalNg').text(res.summary.total_ng);
                    $('#sumTotalEmpty').text(res.summary.total_empty);
                    $('#sumScore').text(res.summary.score !== null ? res.summary.score : '-');
                }
                if (typeof res.open_ng_count !== 'undefined') {
                    updateOpenNgCounter(res.open_ng_count);
                }
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal menyimpan hasil penilaian.'));
            })
            .always(function () {
                $row.find('.js-result-btn').prop('disabled', false);
            });
    }

    function confirmChangeProtectedNg($row, actionLabel, onConfirm) {
        var ngStatus = ($row.attr('data-ng-status') || '').toLowerCase();
        var hasCorrection = $row.attr('data-has-correction') === '1';
        var isNgRow = $row.hasClass('row-ng') || ngStatus !== '';

        if (!isNgRow) {
            onConfirm();
            return;
        }

        var isPreparedOrVerified = (ngStatus === 'waiting_verify' || ngStatus === 'closed' || hasCorrection);
        var title = isPreparedOrVerified
            ? 'Peringatan! Data Perbaikan NG Akan Terhapus'
            : 'Ubah Status Temuan NG?';
        var text = isPreparedOrVerified
            ? 'Point check ini sudah memiliki data perbaikan / siap verifikasi di Daftar Temuan NG. Mengubah judgment akan menghapus data temuan NG beserta Why Analysis, Root Cause, tindakan & foto perbaikan tersebut secara permanen.'
            : 'Point check ini sedang berstatus NG dan tercatat di Daftar Temuan NG. Mengubah judgment akan menghapus data temuan NG ini dari daftar.';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                showCancelButton: true,
                focusCancel: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: actionLabel || 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then(function (r) {
                if (r.isConfirmed) onConfirm();
            });
        } else if (confirm(title + '\n\n' + text)) {
            onConfirm();
        }
    }

    $(document).on('click', '.js-result-btn', function () {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var itemId = $btn.data('item-id');
        var targetVal = $btn.data('val');

        if (targetVal === 'OK') {
            confirmChangeProtectedNg($row, 'Ya, Ubah ke OK', function () {
                sendResultUpdate($row, itemId, targetVal);
            });
            return;
        }

        sendResultUpdate($row, itemId, targetVal);
    });

    // Klik teks OK / NG untuk mengubah kembali pilihan (konfirmasi jika baris NG / sudah punya data perbaikan)
    $(document).on('click', '.js-result-display', function () {
        var $el = $(this);
        var $row = $el.closest('tr');
        var itemId = $el.data('item-id');

        confirmChangeProtectedNg($row, 'Ya, Ubah Judgment', function () {
            sendResultUpdate($row, itemId, null);
        });
    });

    // -------------------------------------------------------------------------
    // 3B. Checkbox Multi-Select + Floating Bulk Action Bar (muncul saat > 1 dipilih)
    // -------------------------------------------------------------------------
    function refreshBulkBar() {
        var $checked = $('#levelingTable tbody .js-row-check:checked');
        var count = $checked.length;
        var total = $('#levelingTable tbody .js-row-check').length;

        $('#lvlCheckAll').prop('checked', total > 0 && count === total);
        $('#lvlBulkCount').text(count);
        $('#lvlBulkBar').toggleClass('is-visible', count > 1);
    }

    function clearAllChecks() {
        $('#lvlCheckAll').prop('checked', false);
        $('#levelingTable tbody .js-row-check').prop('checked', false);
        $('#levelingTable tbody tr').removeClass('row-selected');
        refreshBulkBar();
    }

    $('#lvlCheckAll').on('change', function () {
        var checked = $(this).is(':checked');
        $('#levelingTable tbody .js-row-check').prop('checked', checked);
        $('#levelingTable tbody tr[data-item-id]').toggleClass('row-selected', checked);
        refreshBulkBar();
    });

    $(document).on('change', '.js-row-check', function () {
        $(this).closest('tr').toggleClass('row-selected', $(this).is(':checked'));
        refreshBulkBar();
    });

    $('#btnBulkClear').on('click', function () {
        clearAllChecks();
    });

    function executeBulkSave(itemIds, targetVal) {
        if (!cfg.auditId || !cfg.bulkResultUrl || !itemIds.length) return;
        var $activeBtn = $('.js-bulk-action[data-val="' + targetVal + '"]');
        var origText = $activeBtn.text();
        $('.js-bulk-action').prop('disabled', true);
        $activeBtn.text('Menyimpan...');

        $.post(cfg.bulkResultUrl, {
            audit_id: cfg.auditId,
            check_item_ids: itemIds,
            result: targetVal
        })
            .done(function (res) {
                if (!res || !res.success) return;

                var missingPhotoCount = 0;
                (res.updated_rows || []).forEach(function (u) {
                    var $row = $('#levelingTable tbody tr[data-item-id="' + u.item_id + '"]');
                    if ($row.length) {
                        var hasPhoto = applyRowResultUI($row, u.result_id, u.result, u.ng_status);
                        if (u.result === 'NG' && !hasPhoto) {
                            missingPhotoCount++;
                        }
                    }
                });

                if (res.achieved_levels) {
                    Object.keys(res.achieved_levels).forEach(function (prinsipKey) {
                        updateLevelResultCell(prinsipKey, res.achieved_levels[prinsipKey]);
                    });
                }

                if (res.summary) {
                    $('#sumTotalOk').text(res.summary.total_ok);
                    $('#sumTotalNg').text(res.summary.total_ng);
                    $('#sumTotalEmpty').text(res.summary.total_empty);
                    $('#sumScore').text(res.summary.score !== null ? res.summary.score : '-');
                }
                if (typeof res.open_ng_count !== 'undefined') {
                    updateOpenNgCounter(res.open_ng_count);
                }

                clearAllChecks();

                if (targetVal === 'NG' && missingPhotoCount > 0) {
                    toast('warning', (res.message || 'Berhasil.') + ' ' + missingPhotoCount + ' baris NG wajib upload foto evidence!');
                } else {
                    toast('success', res.message || 'Penilaian massal berhasil disimpan.');
                }
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal menyimpan penilaian massal.'));
            })
            .always(function () {
                $activeBtn.text(origText);
                $('.js-bulk-action').prop('disabled', false);
            });
    }

    $(document).on('click', '.js-bulk-action', function () {
        var targetVal = $(this).data('val');
        var $checked = $('#levelingTable tbody .js-row-check:checked');
        var itemIds = [];
        var ngCount = 0;
        var preparedNgCount = 0;

        $checked.each(function () {
            var $row = $(this).closest('tr');
            itemIds.push($(this).data('item-id'));
            if (targetVal === 'OK') {
                var st = ($row.attr('data-ng-status') || '').toLowerCase();
                var hasCorr = $row.attr('data-has-correction') === '1';
                if ($row.hasClass('row-ng') || st !== '') {
                    ngCount++;
                    if (st === 'waiting_verify' || st === 'closed' || hasCorr) {
                        preparedNgCount++;
                    }
                }
            }
        });

        if (!itemIds.length) return;

        if (targetVal === 'OK' && ngCount > 0) {
            var title = preparedNgCount > 0
                ? 'Peringatan! Data Perbaikan NG Akan Terhapus'
                : 'Ubah ' + ngCount + ' Baris NG ke OK?';
            var text = preparedNgCount > 0
                ? 'Terdapat ' + preparedNgCount + ' baris NG yang sudah memiliki data perbaikan / siap verifikasi (total ' + ngCount + ' baris NG). Mengubah ke OK akan menghapus data di Daftar Temuan NG beserta Why Analysis, Root Cause, tindakan & foto perbaikannya secara permanen.'
                : 'Terdapat ' + ngCount + ' baris berstatus NG yang terpilih. Mengubah ke OK akan menghapus data tersebut dari Daftar Temuan NG.';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: text,
                    showCancelButton: true,
                    focusCancel: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Set OK Semua',
                    cancelButtonText: 'Batal'
                }).then(function (r) {
                    if (r.isConfirmed) executeBulkSave(itemIds, targetVal);
                });
            } else if (confirm(title + '\n\n' + text)) {
                executeBulkSave(itemIds, targetVal);
            }
            return;
        }

        executeBulkSave(itemIds, targetVal);
    });

    // -------------------------------------------------------------------------
    // 4. Upload & Hapus Multi Foto Evidence (Keterangan di atas + Dropzone Card)
    // -------------------------------------------------------------------------
    function autoResizeNoteField(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = Math.max(26, el.scrollHeight) + 'px';
    }

    $('.js-ev-note-field').each(function () {
        autoResizeNoteField(this);
    });

    function renderEvidenceGallery($row, resultId, itemId, urls) {
        var $box = $row.find('.js-evidence-box');
        var $itemsWrap = $box.find('.js-uploaded-items');
        var $dropzone = $row.find('.js-evidence-dropzone');
        var list = Array.isArray(urls) ? urls : [];

        if (!list.length) {
            $itemsWrap.empty();
            $box.addClass('d-none');
            $dropzone.removeClass('d-none');
            if ($row.hasClass('row-ng')) {
                $row.find('.js-evidence-required').removeClass('d-none');
            }
            return;
        }

        var html = '';
        list.forEach(function (u, idx) {
            var reuploadPart = '';
            if (idx === 0) {
                reuploadPart = '<label class="lvl-reupload-link js-smart-upload-trigger" title="Tambah / Upload Foto">' +
                    '+ Upload' +
                    '<input type="file" class="d-none js-evidence-input js-smart-capture-input" accept="image/*" multiple data-item-id="' + escHtml(itemId) + '">' +
                    '</label>' +
                    '<span class="lvl-action-divider"></span>';
            }

            html += '<div class="lvl-uploaded-item">' +
                '<div class="lvl-uploaded-left">' +
                '<div class="lvl-photo-wrap lvl-photo-wrap-sm lvl-uploaded-thumb-wrap js-preview-photo" data-src="' + escHtml(u) + '" title="Klik untuk melihat foto">' +
                '<img src="' + escHtml(u) + '" alt="Evidence" class="lvl-thumb">' +
                '<span class="lvl-photo-eye"><i class="fas fa-eye"></i></span>' +
                '</div>' +
                '<span class="lvl-uploaded-name js-preview-photo" style="cursor:pointer;" data-src="' + escHtml(u) + '">Foto #' + (idx + 1) + '</span>' +
                '</div>' +
                '<div class="lvl-uploaded-actions">' +
                reuploadPart +
                '<button type="button" class="lvl-trash-btn js-delete-evidence" title="Hapus Foto Ini" ' +
                'data-result-id="' + escHtml(resultId) + '" data-item-id="' + escHtml(itemId) + '" data-index="' + idx + '">' +
                '<i class="far fa-trash-alt"></i>' +
                '</button>' +
                '</div>' +
                '</div>';
        });

        $itemsWrap.html(html);
        syncSmartCaptureInputs($itemsWrap);
        $dropzone.addClass('d-none');
        $box.removeClass('d-none');
        $row.find('.js-evidence-required').addClass('d-none');
    }

    var pendingEvidenceUploads = {};

    function renderEvidenceNoteUI($row, resultId, note) {
        var clean = (note || '').trim();
        $row.attr('data-evidence-note', clean);
        var $field = $row.find('.js-ev-note-field');
        $field.val(clean).removeClass('is-invalid-note');
        autoResizeNoteField($field[0]);
        $row.find('.js-save-ev-note').attr('data-result-id', resultId || '').addClass('d-none');
    }

    function executeEvidenceUpload($row, itemId, files, evidenceNote, inputEl) {
        var formData = new FormData();
        formData.append('audit_id', cfg.auditId);
        formData.append('check_item_id', itemId);
        if (evidenceNote) {
            formData.append('evidence_note', evidenceNote);
        }
        files.forEach(function (f) {
            formData.append('evidences[]', f);
        });

        $row.find('.js-pending-upload-bar').addClass('d-none').empty();
        $row.find('.js-evidence-dropzone, .js-evidence-box').addClass('d-none');
        $row.find('.js-upload-status').removeClass('d-none');

        $.ajax({
            url: cfg.uploadEvidenceUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        })
            .done(function (res) {
                if (!res || !res.success) return;
                delete pendingEvidenceUploads[itemId];
                $row.attr('data-result-id', res.result_id);
                renderEvidenceGallery($row, res.result_id, itemId, res.evidence_urls || (res.evidence_url ? [res.evidence_url] : []));
                renderEvidenceNoteUI($row, res.result_id, res.evidence_note || evidenceNote);
                toast('success', (res && res.message) || 'Foto evidence berhasil diupload.');
            })
            .fail(function (xhr) {
                var hasExisting = $row.find('.js-uploaded-items .lvl-uploaded-item').length > 0;
                if (hasExisting) {
                    $row.find('.js-evidence-box').removeClass('d-none');
                } else {
                    $row.find('.js-evidence-dropzone').removeClass('d-none');
                }
                notify('error', extractError(xhr, 'Gagal upload foto evidence.'));
            })
            .always(function () {
                $row.find('.js-upload-status').addClass('d-none');
                if (inputEl) inputEl.value = '';
            });
    }

    function validateEvidenceFiles(rawFiles) {
        var files = rawFiles ? Array.prototype.slice.call(rawFiles) : [];
        if (!files.length) return null;

        if (files.length > 10) {
            notify('warning', 'Maksimal 10 foto sekaligus dalam sekali upload.');
            return null;
        }

        for (var i = 0; i < files.length; i++) {
            if (files[i].size > MAX_FILE_BYTES) {
                notify('warning', 'Ukuran foto "' + files[i].name + '" melebihi batas maksimal 10 MB.');
                return null;
            }
            if (files[i].type && !/^image\/(jpeg|jpg|png|webp)$/.test(files[i].type)) {
                notify('warning', 'Format file "' + files[i].name + '" harus JPG, PNG, atau WEBP.');
                return null;
            }
        }
        return files;
    }

    function handleSelectedEvidenceFiles($row, itemId, rawFiles, inputEl) {
        if (!cfg.auditId) return;
        var files = validateEvidenceFiles(rawFiles);
        if (!files) {
            if (inputEl) inputEl.value = '';
            return;
        }

        var typedNote = ($row.find('.js-ev-note-field').val() || '').trim();
        var savedNote = ($row.attr('data-evidence-note') || '').trim();
        var effectiveNote = typedNote || savedNote;

        if (effectiveNote !== '') {
            executeEvidenceUpload($row, itemId, files, effectiveNote, inputEl);
            return;
        }

        pendingEvidenceUploads[itemId] = files;
        if (inputEl) inputEl.value = '';

        $row.find('.js-evidence-dropzone').addClass('d-none');
        var $pendingBar = $row.find('.js-pending-upload-bar');
        $pendingBar.html(
            '<span><i class="fas fa-paperclip mr-1"></i><strong>' + files.length + ' foto</strong> siap upload</span>' +
            '<span class="d-inline-flex align-items-center" style="gap:4px;">' +
            '<button type="button" class="btn btn-primary btn-sm py-0 px-2 js-confirm-pending-upload" style="font-size:0.62rem;" data-item-id="' + escHtml(itemId) + '">Upload</button>' +
            '<button type="button" class="btn btn-light border btn-sm py-0 px-1 js-cancel-pending-upload" style="font-size:0.62rem;" data-item-id="' + escHtml(itemId) + '">&times;</button>' +
            '</span>'
        ).removeClass('d-none');

        var $noteField = $row.find('.js-ev-note-field');
        $noteField.addClass('is-invalid-note').attr('placeholder', 'Wajib isi keterangan lalu klik Upload...').focus();
        toast('warning', 'Isi keterangan di atas terlebih dahulu lalu klik Upload.');
    }

    $(document).on('change', '.js-evidence-input', function () {
        var $row = $(this).closest('tr');
        var itemId = $(this).data('item-id');
        handleSelectedEvidenceFiles($row, itemId, this.files, this);
    });

    // Drag & Drop pada Dropzone Evidence utama & Dropzone Perbaikan NG
    $(document).on('dragenter dragover', '.js-evidence-dropzone, .js-correct-dropzone', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('is-dragover');
    });

    $(document).on('dragleave dragend', '.js-evidence-dropzone, .js-correct-dropzone', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('is-dragover');
    });

    $(document).on('drop', '.js-evidence-dropzone', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $dz = $(this).removeClass('is-dragover');
        var dt = e.originalEvent && e.originalEvent.dataTransfer;
        if (!dt || !dt.files || !dt.files.length) return;
        var $row = $dz.closest('tr');
        var itemId = $dz.data('item-id');
        handleSelectedEvidenceFiles($row, itemId, dt.files, null);
    });

    $(document).on('drop', '.js-correct-dropzone', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('is-dragover');
        var dt = e.originalEvent && e.originalEvent.dataTransfer;
        if (!dt || !dt.files || !dt.files.length) return;
        addCorrectiveFiles(dt.files);
    });

    $(document).on('click', '.js-confirm-pending-upload', function () {
        var $row = $(this).closest('tr');
        var itemId = $(this).attr('data-item-id');
        var note = ($row.find('.js-ev-note-field').val() || '').trim();
        if (!note) {
            $row.find('.js-ev-note-field').addClass('is-invalid-note').focus();
            toast('warning', 'Keterangan evidence wajib diisi!');
            return;
        }
        var files = pendingEvidenceUploads[itemId];
        if (!files || !files.length) {
            $row.find('.js-pending-upload-bar').addClass('d-none').empty();
            $row.find('.js-evidence-dropzone').removeClass('d-none');
            return;
        }
        $row.find('.js-ev-note-field').removeClass('is-invalid-note');
        executeEvidenceUpload($row, itemId, files, note, null);
    });

    $(document).on('click', '.js-cancel-pending-upload', function () {
        var $row = $(this).closest('tr');
        var itemId = $(this).attr('data-item-id');
        delete pendingEvidenceUploads[itemId];
        $row.find('.js-pending-upload-bar').addClass('d-none').empty();
        $row.find('.js-ev-note-field').removeClass('is-invalid-note').attr('placeholder', 'Tulis keterangan evidence...');
        var hasExisting = $row.find('.js-uploaded-items .lvl-uploaded-item').length > 0;
        if (hasExisting) {
            $row.find('.js-evidence-box').removeClass('d-none');
        } else {
            $row.find('.js-evidence-dropzone').removeClass('d-none');
        }
    });

    function saveExistingEvidenceNote($row) {
        var resultId = $row.attr('data-result-id');
        var hasPhotos = $row.find('.js-uploaded-items .lvl-uploaded-item').length > 0;
        var $field = $row.find('.js-ev-note-field');
        var newNote = ($field.val() || '').trim();
        var prevNote = ($row.attr('data-evidence-note') || '').trim();

        if (!resultId || !hasPhotos || newNote === prevNote) {
            $row.find('.js-save-ev-note').addClass('d-none');
            return;
        }
        if (!newNote) {
            $field.val(prevNote);
            autoResizeNoteField($field[0]);
            $row.find('.js-save-ev-note').addClass('d-none');
            toast('warning', 'Keterangan evidence tidak boleh kosong.');
            return;
        }

        var $saveBtn = $row.find('.js-save-ev-note').prop('disabled', true).text('...');
        $.post(cfg.deleteEvidenceBase + '/' + resultId + '/evidence-note', {
            _method: 'PATCH',
            plant: cfg.plant,
            evidence_note: newNote
        })
            .done(function (res) {
                renderEvidenceNoteUI($row, resultId, (res && res.evidence_note) || newNote);
                toast('success', (res && res.message) || 'Keterangan evidence diperbarui.');
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal memperbarui keterangan evidence.'));
            })
            .always(function () {
                $saveBtn.prop('disabled', false).text('Simpan');
            });
    }

    $(document).on('input', '.js-ev-note-field', function () {
        autoResizeNoteField(this);
        var $row = $(this).closest('tr');
        $(this).removeClass('is-invalid-note');
        var hasPhotos = $row.find('.js-uploaded-items .lvl-uploaded-item').length > 0;
        var prevNote = ($row.attr('data-evidence-note') || '').trim();
        var currNote = ($(this).val() || '').trim();
        $row.find('.js-save-ev-note').toggleClass('d-none', !(hasPhotos && currNote !== '' && currNote !== prevNote));
    });

    $(document).on('keydown', '.js-ev-note-field', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            var $row = $(this).closest('tr');
            var itemId = $(this).data('item-id');
            if (pendingEvidenceUploads[itemId] && pendingEvidenceUploads[itemId].length) {
                e.preventDefault();
                $row.find('.js-confirm-pending-upload').trigger('click');
                return;
            }
            if ($row.find('.js-uploaded-items .lvl-uploaded-item').length > 0) {
                e.preventDefault();
                $(this).blur();
            }
        }
    });

    $(document).on('blur', '.js-ev-note-field', function () {
        var $row = $(this).closest('tr');
        saveExistingEvidenceNote($row);
    });

    $(document).on('mousedown', '.js-save-ev-note', function (e) {
        e.preventDefault();
        saveExistingEvidenceNote($(this).closest('tr'));
    });

    $(document).on('click', '.js-delete-evidence', function () {
        var $btn = $(this);
        var resultId = $btn.attr('data-result-id');
        var itemId = $btn.attr('data-item-id');
        var idx = $btn.attr('data-index');
        var $row = $btn.closest('tr');
        if (!resultId) return;

        function doDelete() {
            $.ajax({
                url: cfg.deleteEvidenceBase + '/' + resultId + '/evidence',
                type: 'POST',
                data: { _method: 'DELETE', plant: cfg.plant, index: idx }
            })
                .done(function (res) {
                    var remaining = (res && Array.isArray(res.evidence_urls)) ? res.evidence_urls : [];
                    renderEvidenceGallery($row, resultId, itemId, remaining);
                    if (remaining.length === 0) {
                        renderEvidenceNoteUI($row, resultId, '');
                    }
                    if ($row.hasClass('row-ng') && remaining.length === 0) {
                        toast('warning', 'Foto dihapus. Baris NG wajib upload foto evidence!');
                    } else {
                        toast('success', 'Foto evidence berhasil dihapus.');
                    }
                })
                .fail(function (xhr) {
                    notify('error', extractError(xhr, 'Gagal menghapus foto evidence.'));
                });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Foto Evidence?',
                text: 'Foto evidence yang dipilih akan dihapus.',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(function (r) {
                if (r.isConfirmed) doDelete();
            });
        } else if (confirm('Hapus foto evidence ini?')) {
            doDelete();
        }
    });

    function closeLightbox() {
        $('#lvlLightbox').removeClass('is-open');
        $('#lvlLightboxImg').attr('src', '');
    }

    $(document).on('click', '.js-preview-photo', function () {
        var src = $(this).data('src') || $(this).attr('src');
        if (!src) return;
        $('#lvlLightboxImg').attr('src', src);
        $('#lvlLightbox').addClass('is-open');
    });

    $(document).on('click', '#lvlLightboxClose', function (e) {
        e.stopPropagation();
        closeLightbox();
    });

    $(document).on('click', '#lvlLightbox', function (e) {
        if (e.target === this) {
            closeLightbox();
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#lvlLightbox').hasClass('is-open')) {
            closeLightbox();
        }
    });

    // -------------------------------------------------------------------------
    // 5. Modal Daftar Temuan NG & Verifikasi Perbaikan (tanpa badge)
    // -------------------------------------------------------------------------
    function renderNgStatusText(status) {
        if (status === 'closed') return '<span class="text-success font-weight-bold">Closed</span>';
        if (status === 'waiting_verify') return '<span class="text-warning font-weight-bold">Waiting Verify</span>';
        return '<span class="text-danger font-weight-bold">Open</span>';
    }

    function buildPhotoWithEye(url, styleAttr, isSmall) {
        var wrapCls = 'lvl-photo-wrap js-preview-photo' + (isSmall ? ' lvl-photo-wrap-sm' : '');
        var stylePart = styleAttr ? (' style="' + styleAttr + '"') : '';
        return '<div class="' + wrapCls + '" data-src="' + escHtml(url) + '" title="Klik untuk melihat foto">' +
            '<img src="' + escHtml(url) + '" alt="Evidence" class="lvl-thumb"' + stylePart + '>' +
            '<span class="lvl-photo-eye"><i class="fas fa-eye"></i></span>' +
            '</div>';
    }

    function buildNgCorrTextHtml(r) {
        function isRichTextEmpty(html) {
            return !$('<div>').html(html || '').text().trim();
        }

        var corrParts = [];
        if (r.why_1 && !isRichTextEmpty(r.why_1)) corrParts.push('<div class="lvl-guidance-content"><strong>Why 1:</strong> ' + r.why_1 + '</div>');
        if (r.why_2 && !isRichTextEmpty(r.why_2)) corrParts.push('<div class="lvl-guidance-content"><strong>Why 2:</strong> ' + r.why_2 + '</div>');
        if (r.why_3 && !isRichTextEmpty(r.why_3)) corrParts.push('<div class="lvl-guidance-content"><strong>Why 3:</strong> ' + r.why_3 + '</div>');
        if (r.root_cause && !isRichTextEmpty(r.root_cause)) corrParts.push('<div class="lvl-guidance-content"><strong>Root Cause:</strong> ' + r.root_cause + '</div>');
        if (r.corrective_action && !isRichTextEmpty(r.corrective_action)) corrParts.push('<div class="lvl-guidance-content"><strong>Tindakan:</strong> ' + r.corrective_action + '</div>');

        var hasCorr = corrParts.length > 0;
        var bodyHtml = hasCorr ? corrParts.join('') : '<span class="text-muted">-</span>';
        if (r.corrective_date) {
            bodyHtml += '<div class="text-muted" style="font-size:0.64rem;">Tgl: ' + escHtml(r.corrective_date) + '</div>';
        }
        if (r.corrector_name) {
            bodyHtml += '<div class="text-muted" style="font-size:0.64rem;">By: ' + escHtml(r.corrector_name) + '</div>';
        }
        if (r.reject_reason) {
            bodyHtml += '<div class="text-danger" style="font-size:0.64rem;">Alasan reject: ' + escHtml(r.reject_reason) + '</div>';
        }

        if (hasCorr && r.ng_status !== 'closed') {
            return '<div class="lvl-inline-cell-head">' +
                '<div class="flex-grow-1">' + bodyHtml + '</div>' +
                '<button type="button" class="lvl-inline-edit-btn js-inline-edit-corr" data-id="' + r.id + '" title="Edit Tindakan Perbaikan">' +
                '<i class="fas fa-pencil-alt"></i>' +
                '</button>' +
                '</div>';
        }
        return bodyHtml;
    }

    function buildNgCorrPhotosHtml(r) {
        var corrEvList = (Array.isArray(r.corrective_evidence_urls) && r.corrective_evidence_urls.length)
            ? r.corrective_evidence_urls
            : (r.corrective_evidence_url ? [r.corrective_evidence_url] : []);

        var photosHtml = corrEvList.length
            ? '<div class="d-flex flex-wrap justify-content-center" style="gap:4px;">' +
            corrEvList.map(function (u) {
                return buildPhotoWithEye(u, 'max-width:68px; max-height:48px;', true);
            }).join('') +
            '</div>'
            : '<span class="text-muted">-</span>';

        if ((corrEvList.length > 0 || r.ng_status === 'waiting_verify') && r.ng_status !== 'closed') {
            return '<div class="lvl-inline-cell-head">' +
                '<div class="flex-grow-1">' + photosHtml + '</div>' +
                '<button type="button" class="lvl-inline-edit-btn js-inline-edit-corr-photos" data-id="' + r.id + '" title="Ubah Foto Perbaikan">' +
                '<i class="fas fa-pencil-alt"></i>' +
                '</button>' +
                '</div>';
        }
        return photosHtml;
    }

    function loadNgFindings() {
        var $tbody = $('#ngFindingsBody');
        $tbody.html('<tr><td colspan="10" class="text-center text-muted py-3">Memuat daftar temuan NG...</td></tr>');

        $.get(cfg.ngListUrl, {
            plant: cfg.plant,
            status: $('#ngFilterStatus').val(),
            date_from: $('#ngFilterDateFrom').val(),
            date_to: $('#ngFilterDateTo').val(),
            inspector_id: $('#ngFilterInspector').val()
        })
            .done(function (res) {
                var rows = (res && res.data) ? res.data : [];
                if (typeof res.open_ng_count !== 'undefined') {
                    updateOpenNgCounter(res.open_ng_count);
                }

                ngDataCache = {};
                if (!rows.length) {
                    $tbody.html('<tr><td colspan="10" class="text-center text-muted py-3">Tidak ada data temuan NG.</td></tr>');
                    return;
                }

                var html = '';
                rows.forEach(function (r) {
                    ngDataCache[r.id] = r;
                    var evList = (Array.isArray(r.evidence_urls) && r.evidence_urls.length) ? r.evidence_urls : (r.evidence_url ? [r.evidence_url] : []);

                    var evHtml = evList.length
                        ? (r.evidence_note ? '<div class="text-left text-dark mb-1" style="font-size:0.64rem; line-height:1.25;"><strong>Ket:</strong> ' + escHtml(r.evidence_note) + '</div>' : '') +
                        '<div class="d-flex flex-wrap justify-content-center" style="gap:4px;">' +
                        evList.map(function (u) {
                            return buildPhotoWithEye(u, 'max-width:68px; max-height:48px;', true);
                        }).join('') +
                        '</div>'
                        : '<span class="text-danger">Belum ada</span>';

                    var corrText = buildNgCorrTextHtml(r);
                    var corrEvHtml = buildNgCorrPhotosHtml(r);

                    var partHtml = '<strong>' + escHtml(r.part_name || r.part_label || '-') + '</strong>';
                    if (r.part_number) {
                        partHtml += '<div class="text-muted text-nowrap" style="font-size:0.66rem;">' + escHtml(r.part_number) + '</div>';
                    }

                    var actionHtml = '';
                    if (r.ng_status === 'open') {
                        actionHtml = '<button type="button" class="btn btn-primary btn-sm px-2 py-1 js-open-correct-ng" style="font-size:0.65rem;" data-id="' + r.id + '">Input Perbaikan</button>';
                    } else if (r.ng_status === 'waiting_verify') {
                        if (r.can_verify) {
                            actionHtml = '<button type="button" class="btn btn-success btn-sm px-2 py-1 js-open-verify-ng" style="font-size:0.65rem;" data-id="' + r.id + '">Verifikasi</button>';
                        } else {
                            actionHtml = '<span class="text-muted" style="font-size:0.65rem;">Menunggu ' + escHtml(r.auditor_name) + '</span>';
                        }
                    } else {
                        var verifyInfoHtml = '<div class="text-left">' +
                            '<div class="text-dark font-weight-bold" style="font-size:0.66rem; line-height:1.25;">Verified</div>' +
                            (r.verified_at ? '<div class="text-muted" style="font-size:0.62rem; line-height:1.25;">' + escHtml(r.verified_at) + '</div>' : '') +
                            '<div class="text-dark" style="font-size:0.62rem; line-height:1.25;">By ' + escHtml(r.verifier_name || '-') + '</div>' +
                            '</div>';
                        var unverifyBtnHtml = r.can_unverify
                            ? '<button type="button" class="lvl-inline-edit-btn js-unverify-ng ml-2" data-id="' + r.id + '" title="Batal Verifikasi"><i class="fas fa-undo-alt"></i></button>'
                            : '';
                        actionHtml = '<div class="d-inline-flex align-items-center justify-content-between" style="gap:6px;">' + verifyInfoHtml + unverifyBtnHtml + '</div>';
                    }

                    html += '<tr data-ng-id="' + r.id + '">' +
                        '<td class="text-nowrap">' + escHtml(r.audit_date) + '</td>' +
                        '<td><strong>' + escHtml(r.inspector_name) + '</strong><div class="text-muted" style="font-size:0.64rem;">' + escHtml(r.bagian) + '</div></td>' +
                        '<td style="min-width:165px;">' + partHtml + '</td>' +
                        '<td class="text-center text-nowrap"><strong>' + escHtml(r.prinsip) + '</strong> / LEVEL ' + escHtml(r.level) + '</td>' +
                        '<td>' + escHtml(r.point_check) + '</td>' +
                        '<td class="text-center">' + evHtml + '</td>' +
                        '<td class="js-ng-corr-cell" data-id="' + r.id + '">' + corrText + '</td>' +
                        '<td class="text-center js-ng-corr-photo-cell" data-id="' + r.id + '">' + corrEvHtml + '</td>' +
                        '<td class="text-center text-nowrap">' + renderNgStatusText(r.ng_status) + '</td>' +
                        '<td class="text-center text-nowrap">' + actionHtml + '</td>' +
                        '</tr>';
                });

                $tbody.html(html);
            })
            .fail(function (xhr) {
                $tbody.html('<tr><td colspan="10" class="text-center text-danger py-3">' + escHtml(extractError(xhr, 'Gagal memuat temuan NG.')) + '</td></tr>');
            });
    }

    function buildSimpleRteHtml(editorClass, initialHtml, placeholder) {
        return '<div class="lvl-rte-wrapper">' +
            '<div class="lvl-rte-toolbar lvl-rte-toolbar-sm">' +
                '<button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="bold" title="Tebal"><i class="fas fa-bold"></i></button>' +
                '<button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="italic" title="Miring"><i class="fas fa-italic"></i></button>' +
                '<button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="underline" title="Garis Bawah"><i class="fas fa-underline"></i></button>' +
                '<span class="lvl-rte-sep"></span>' +
                '<button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertOrderedList" title="Daftar Angka"><i class="fas fa-list-ol"></i></button>' +
                '<button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="insertUnorderedList" title="Daftar Poin"><i class="fas fa-list-ul"></i></button>' +
                '<span class="lvl-rte-sep"></span>' +
                '<button type="button" class="lvl-rte-btn js-rte-cmd" data-cmd="removeFormat" title="Hapus Format"><i class="fas fa-remove-format"></i></button>' +
            '</div>' +
            '<div class="lvl-rte-editor lvl-rte-inline ' + editorClass + '" contenteditable="true" data-placeholder="' + escHtml(placeholder || '') + '">' + (initialHtml || '') + '</div>' +
        '</div>';
    }

    function readRteValue($el) {
        var html = ($el.html() || '').trim();
        var plain = $('<div>').html(html).text().trim();
        return plain ? html : '';
    }

    // Inline edit Tindakan Perbaikan pada tabel Daftar Temuan NG
    $(document).on('click', '.js-inline-edit-corr', function () {
        var id = $(this).data('id');
        var r = ngDataCache[id];
        if (!r) return;
        var $cell = $(this).closest('.js-ng-corr-cell');

        var formHtml = '<div class="lvl-inline-edit-box">' +
            '<div class="mb-1">' +
            '<label>Tanggal Perbaikan <span class="text-danger">*</span></label>' +
            '<input type="date" class="form-control form-control-sm js-ie-date" value="' + escHtml(r.corrective_date_iso || cfg.today) + '">' +
            '</div>' +
            '<div class="mb-1">' +
            '<label>Why 1 <span class="text-danger">*</span></label>' +
            buildSimpleRteHtml('js-ie-why1', r.why_1 || '', 'Wajib diisi...') +
            '</div>' +
            '<div class="mb-1">' +
            '<label>Why 2</label>' +
            buildSimpleRteHtml('js-ie-why2', r.why_2 || '', 'Opsional...') +
            '</div>' +
            '<div class="mb-1">' +
            '<label>Why 3</label>' +
            buildSimpleRteHtml('js-ie-why3', r.why_3 || '', 'Opsional...') +
            '</div>' +
            '<div class="mb-1">' +
            '<label>Root Cause <span class="text-danger">*</span></label>' +
            buildSimpleRteHtml('js-ie-root', r.root_cause || '', 'Wajib diisi...') +
            '</div>' +
            '<div class="mb-2">' +
            '<label>Tindakan Perbaikan <span class="text-danger">*</span></label>' +
            buildSimpleRteHtml('js-ie-action', r.corrective_action || '', 'Wajib diisi...') +
            '</div>' +
            '<div class="d-flex justify-content-end" style="gap:6px;">' +
            '<button type="button" class="btn btn-light border btn-sm py-0 px-2 js-cancel-inline-corr" style="font-size:0.7rem;" data-id="' + id + '">Batal</button>' +
            '<button type="button" class="btn btn-primary btn-sm py-0 px-2 js-save-inline-corr" style="font-size:0.7rem;" data-id="' + id + '">Simpan</button>' +
            '</div>' +
            '</div>';

        $cell.html(formHtml);
    });

    $(document).on('click', '.js-cancel-inline-corr', function () {
        var id = $(this).data('id');
        var r = ngDataCache[id];
        if (!r) return;
        $(this).closest('.js-ng-corr-cell').html(buildNgCorrTextHtml(r));
    });

    $(document).on('click', '.js-save-inline-corr', function () {
        var $btn = $(this);
        var id = $btn.data('id');
        var $cell = $btn.closest('.js-ng-corr-cell');

        var payload = {
            _method: 'PATCH',
            corrective_date: ($cell.find('.js-ie-date').val() || '').trim(),
            why_1: readRteValue($cell.find('.js-ie-why1')),
            why_2: readRteValue($cell.find('.js-ie-why2')),
            why_3: readRteValue($cell.find('.js-ie-why3')),
            root_cause: readRteValue($cell.find('.js-ie-root')),
            corrective_action: readRteValue($cell.find('.js-ie-action'))
        };

        var missing = [];
        if (!payload.corrective_date) missing.push('Tanggal Perbaikan');
        if (!payload.why_1) missing.push('Why 1');
        if (!payload.root_cause) missing.push('Root Cause');
        if (!payload.corrective_action) missing.push('Tindakan Perbaikan');
        if (missing.length) {
            notify('warning', 'Mohon lengkapi: ' + missing.join(', ') + '.');
            return;
        }

        $btn.prop('disabled', true).text('Menyimpan...');
        $.post(cfg.ngBaseUrl + '/' + id + '/correct-text', payload)
            .done(function (res) {
                if (res && res.data && ngDataCache[id]) {
                    $.extend(ngDataCache[id], res.data);
                }
                $cell.html(buildNgCorrTextHtml(ngDataCache[id]));
                toast('success', (res && res.message) || 'Tindakan perbaikan diperbarui.');
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal menyimpan perubahan tindakan perbaikan.'));
                $btn.prop('disabled', false).text('Simpan');
            });
    });

    // Inline edit Foto Perbaikan pada tabel Daftar Temuan NG
    var inlinePhotoState = {};

    function renderInlinePhotoEditor($cell, id) {
        var st = inlinePhotoState[id];
        if (!st) return;

        var thumbsHtml = '';
        st.existing.forEach(function (item) {
            thumbsHtml += '<div class="lvl-inline-photo-item">' +
                buildPhotoWithEye(item.url, '', true) +
                '<button type="button" class="lvl-inline-photo-remove js-inline-rm-existing-photo" data-id="' + id + '" data-idx="' + item.origIdx + '" title="Hapus Foto">&times;</button>' +
                '</div>';
        });
        st.added.forEach(function (item, aIdx) {
            thumbsHtml += '<div class="lvl-inline-photo-item">' +
                buildPhotoWithEye(item.previewUrl, '', true) +
                '<button type="button" class="lvl-inline-photo-remove js-inline-rm-added-photo" data-id="' + id + '" data-aidx="' + aIdx + '" title="Hapus Foto Baru">&times;</button>' +
                '</div>';
        });

        if (!thumbsHtml) {
            thumbsHtml = '<div class="text-danger text-center w-100 py-1" style="font-size:0.66rem;">Minimal 1 foto diperlukan.</div>';
        }

        var html = '<div class="lvl-inline-edit-box lvl-inline-photo-box">' +
            '<div class="lvl-inline-photo-grid">' + thumbsHtml + '</div>' +
            '<div class="lvl-inline-photo-upload-row">' +
            '<label class="btn btn-outline-primary btn-sm lvl-inline-photo-upload-btn js-smart-upload-trigger" title="Upload Foto Perbaikan">' +
            '<i class="fas fa-upload mr-1"></i>Upload' +
            '<input type="file" class="d-none js-inline-add-corr-photo js-smart-capture-input" data-id="' + id + '" accept="image/*" multiple>' +
            '</label>' +
            '</div>' +
            '<div class="lvl-inline-photo-footer">' +
            '<button type="button" class="btn btn-light border btn-sm js-cancel-inline-photos" data-id="' + id + '">Batal</button>' +
            '<button type="button" class="btn btn-primary btn-sm js-save-inline-photos" data-id="' + id + '">Simpan</button>' +
            '</div>' +
            '</div>';

        $cell.html(html);
        syncSmartCaptureInputs($cell);
    }

    function cleanupInlinePhotoState(id) {
        var st = inlinePhotoState[id];
        if (st && st.added) {
            st.added.forEach(function (it) {
                try { URL.revokeObjectURL(it.previewUrl); } catch (e) { }
            });
        }
        delete inlinePhotoState[id];
    }

    $(document).on('click', '.js-inline-edit-corr-photos', function () {
        var id = $(this).data('id');
        var r = ngDataCache[id];
        if (!r) return;
        cleanupInlinePhotoState(id);

        var urls = (Array.isArray(r.corrective_evidence_urls) && r.corrective_evidence_urls.length)
            ? r.corrective_evidence_urls
            : (r.corrective_evidence_url ? [r.corrective_evidence_url] : []);

        inlinePhotoState[id] = {
            existing: urls.map(function (u, idx) { return { origIdx: idx, url: u }; }),
            added: []
        };
        renderInlinePhotoEditor($(this).closest('.js-ng-corr-photo-cell'), id);
    });

    $(document).on('click', '.js-inline-rm-existing-photo', function () {
        var id = $(this).data('id');
        var origIdx = parseInt($(this).attr('data-idx'), 10);
        var st = inlinePhotoState[id];
        if (!st) return;
        st.existing = st.existing.filter(function (it) { return it.origIdx !== origIdx; });
        renderInlinePhotoEditor($(this).closest('.js-ng-corr-photo-cell'), id);
    });

    $(document).on('click', '.js-inline-rm-added-photo', function () {
        var id = $(this).data('id');
        var aIdx = parseInt($(this).attr('data-aidx'), 10);
        var st = inlinePhotoState[id];
        if (!st || !st.added[aIdx]) return;
        try { URL.revokeObjectURL(st.added[aIdx].previewUrl); } catch (e) { }
        st.added.splice(aIdx, 1);
        renderInlinePhotoEditor($(this).closest('.js-ng-corr-photo-cell'), id);
    });

    $(document).on('change', '.js-inline-add-corr-photo', function () {
        var id = $(this).data('id');
        var st = inlinePhotoState[id];
        var $cell = $(this).closest('.js-ng-corr-photo-cell');
        if (!st) return;

        var incoming = this.files ? Array.prototype.slice.call(this.files) : [];
        for (var i = 0; i < incoming.length; i++) {
            var f = incoming[i];
            if (st.existing.length + st.added.length >= 10) {
                notify('warning', 'Maksimal 10 foto perbaikan.');
                break;
            }
            if (f.size > MAX_FILE_BYTES) {
                notify('warning', 'Ukuran foto "' + f.name + '" maksimal 10 MB.');
                continue;
            }
            if (f.type && !/^image\/(jpeg|jpg|png|webp)$/.test(f.type)) {
                notify('warning', 'Format file "' + f.name + '" harus JPG, PNG, atau WEBP.');
                continue;
            }
            st.added.push({ file: f, previewUrl: URL.createObjectURL(f) });
        }
        this.value = '';
        renderInlinePhotoEditor($cell, id);
    });

    $(document).on('click', '.js-cancel-inline-photos', function () {
        var id = $(this).data('id');
        cleanupInlinePhotoState(id);
        var r = ngDataCache[id];
        if (!r) return;
        $(this).closest('.js-ng-corr-photo-cell').html(buildNgCorrPhotosHtml(r));
    });

    $(document).on('click', '.js-save-inline-photos', function () {
        var $btn = $(this);
        var id = $btn.data('id');
        var st = inlinePhotoState[id];
        var $cell = $btn.closest('.js-ng-corr-photo-cell');
        if (!st) return;

        if (st.existing.length + st.added.length < 1) {
            notify('warning', 'Minimal harus ada 1 foto evidence perbaikan.');
            return;
        }

        var fd = new FormData();
        st.existing.forEach(function (it) {
            fd.append('keep_indices[]', it.origIdx);
        });
        st.added.forEach(function (it) {
            fd.append('new_evidences[]', it.file);
        });

        $btn.prop('disabled', true).text('Menyimpan...');
        $.ajax({
            url: cfg.ngBaseUrl + '/' + id + '/correct-photos',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false
        })
            .done(function (res) {
                cleanupInlinePhotoState(id);
                if (res && Array.isArray(res.corrective_evidence_urls) && ngDataCache[id]) {
                    ngDataCache[id].corrective_evidence_urls = res.corrective_evidence_urls;
                    ngDataCache[id].corrective_evidence_url = res.corrective_evidence_urls[0] || null;
                }
                $cell.html(buildNgCorrPhotosHtml(ngDataCache[id]));
                toast('success', (res && res.message) || 'Foto perbaikan berhasil diperbarui.');
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal menyimpan foto perbaikan.'));
                $btn.prop('disabled', false).text('Simpan');
            });
    });

    function switchNgPanel(panel) {
        $('#ngPanelList, #ngPanelCorrect, #ngPanelVerify').addClass('d-none');
        $('#ngFooterList, #ngFooterCorrect, #ngFooterVerify').addClass('d-none');

        if (panel === 'correct') {
            $('#ngPanelCorrect, #ngFooterCorrect').removeClass('d-none');
            $('#ngModalTitle').text('Input Perbaikan Temuan NG');
        } else if (panel === 'verify') {
            $('#ngPanelVerify, #ngFooterVerify').removeClass('d-none');
            $('#ngModalTitle').text('Verifikasi Perbaikan Temuan NG');
        } else {
            $('#ngPanelList, #ngFooterList').removeClass('d-none');
            $('#ngModalTitle').text('Daftar Temuan NG & Verifikasi Perbaikan');
        }

        $('#ngListModal .modal-body').scrollTop(0);
    }

    $(document).on('click', '.js-ng-back-to-list', function () {
        switchNgPanel('list');
    });

    $('#ngListModal').on('shown.bs.modal', function () {
        switchNgPanel('list');
        loadNgFindings();
    }).on('hidden.bs.modal', function () {
        switchNgPanel('list');
    });

    $('#btnReloadNgList').on('click', function () {
        loadNgFindings();
    });

    $('#ngFilterStatus, #ngFilterDateFrom, #ngFilterDateTo').on('change', function () {
        loadNgFindings();
    });

    // Searchable Inspector pada Modal Daftar Temuan NG (ketik nama + tombol clear kanan)
    var ngInspTimer = null;

    function loadNgFilterInspectors(q) {
        var $dd = $('#ngFilterInspectorDropdown');
        $dd.html('<div class="lvl-search-item text-muted">Mencari...</div>').show();

        $.get(cfg.lookupInspectorsUrl, { plant: cfg.plant, q: q || '' })
            .done(function (res) {
                var rows = (res && res.data) ? res.data : [];
                var html = '<div class="lvl-search-item font-weight-bold text-primary js-pick-ng-inspector" data-id="" data-name="">-- Semua Inspector --</div>';
                if (!rows.length) {
                    html += '<div class="lvl-search-item text-muted">Inspector tidak ditemukan</div>';
                } else {
                    rows.forEach(function (u) {
                        html += '<div class="lvl-search-item js-pick-ng-inspector" data-id="' + u.id + '" data-name="' + escHtml(u.name) + '">' + escHtml(u.name) + '</div>';
                    });
                }
                $dd.html(html);
            })
            .fail(function () {
                $dd.html('<div class="lvl-search-item text-danger">Gagal memuat data inspector</div>');
            });
    }

    $('#ngFilterInspectorSearch').on('focus', function () {
        loadNgFilterInspectors($(this).val());
    }).on('input', function () {
        var val = $(this).val();
        $('#btnClearNgInspector').toggleClass('d-none', !val);
        $('#ngFilterInspectorGroup').toggleClass('has-clear', !!val);
        if (!val && $('#ngFilterInspector').val()) {
            $('#ngFilterInspector').val('');
            loadNgFindings();
        }
        clearTimeout(ngInspTimer);
        ngInspTimer = setTimeout(function () {
            loadNgFilterInspectors(val);
        }, 180);
    });

    $(document).on('click', '.js-pick-ng-inspector', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#ngFilterInspector').val(id || '');
        $('#ngFilterInspectorSearch').val(name || '');
        $('#btnClearNgInspector').toggleClass('d-none', !id);
        $('#ngFilterInspectorGroup').toggleClass('has-clear', !!id);
        $('#ngFilterInspectorDropdown').hide();
        loadNgFindings();
    });

    $('#btnClearNgInspector').on('click', function () {
        $('#ngFilterInspector').val('');
        $('#ngFilterInspectorSearch').val('');
        $('#btnClearNgInspector').addClass('d-none');
        $('#ngFilterInspectorGroup').removeClass('has-clear');
        $('#ngFilterInspectorDropdown').hide();
        loadNgFindings();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#ngFilterInspectorSearch, #ngFilterInspectorDropdown, #btnClearNgInspector').length) {
            $('#ngFilterInspectorDropdown').hide();
        }
    });

    // Direct Print Daftar Temuan NG & Verifikasi Perbaikan (tanpa tab baru)
    $('#btnPrintNgList').on('click', function () {
        if (!cfg.ngPrintUrl) return;
        var params = $.param({
            plant: cfg.plant,
            status: $('#ngFilterStatus').val() || '',
            date_from: $('#ngFilterDateFrom').val() || '',
            date_to: $('#ngFilterDateTo').val() || '',
            inspector_id: $('#ngFilterInspector').val() || ''
        });
        var printUrl = cfg.ngPrintUrl + '?' + params;

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

    var correctiveSelectedFiles = [];
    var correctivePreviewUrls = [];

    function revokeCorrectivePreviewUrls() {
        correctivePreviewUrls.forEach(function (u) {
            try { URL.revokeObjectURL(u); } catch (e) { }
        });
        correctivePreviewUrls = [];
    }

    function formatFileSize(bytes) {
        if (!bytes) return '';
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function renderCorrectivePreviews() {
        var $list = $('#ngCorrectPreviewList');
        revokeCorrectivePreviewUrls();
        $list.empty();

        if (!correctiveSelectedFiles.length) {
            $('#ngCorrectEvidenceHint').html('<span class="text-muted">Belum ada foto dipilih</span>');
            $list.html('<div class="lvl-correct-preview-empty"><i class="far fa-image mr-1"></i>Belum ada foto evidence perbaikan. Klik area <strong>Upload</strong> di bawah atau drag &amp; drop foto.</div>');
            return;
        }

        var hintHtml = '<span class="text-success font-weight-bold">' + correctiveSelectedFiles.length + ' foto siap diupload</span>';
        if (correctiveSelectedFiles.length > 1) {
            hintHtml += ' <button type="button" class="btn btn-link btn-sm text-danger p-0 ml-2 js-clear-all-correct-files" style="font-size:0.75rem; vertical-align:baseline;">Hapus Semua</button>';
        }
        $('#ngCorrectEvidenceHint').html(hintHtml);

        correctiveSelectedFiles.forEach(function (file, idx) {
            var url = URL.createObjectURL(file);
            correctivePreviewUrls.push(url);

            var $card = $('<div class="lvl-correct-preview-card"></div>');
            var $imgWrap = $('<div class="lvl-correct-preview-img-wrap lvl-photo-wrap js-preview-photo" title="Klik untuk perbesar foto"></div>')
                .attr('data-src', url);
            var $img = $('<img class="lvl-correct-preview-img">')
                .attr('src', url)
                .attr('alt', file.name || ('Foto ' + (idx + 1)));
            $imgWrap.append($img).append('<span class="lvl-photo-eye"><i class="fas fa-eye"></i></span>');

            var $meta = $('<div class="lvl-correct-preview-meta"></div>')
                .attr('title', file.name || '')
                .text((file.name || ('Foto ' + (idx + 1))) + (file.size ? ' (' + formatFileSize(file.size) + ')' : ''));

            var $actions = $('<div class="lvl-correct-preview-actions"></div>');
            var $view = $('<button type="button" class="btn btn-outline-primary btn-sm js-preview-photo" title="Perbesar Foto"><i class="fas fa-search-plus mr-1"></i>Lihat</button>')
                .attr('data-src', url);
            var $del = $('<button type="button" class="btn btn-outline-danger btn-sm js-remove-correct-file" title="Hapus Foto Ini"><i class="fas fa-trash-alt mr-1"></i>Hapus</button>')
                .attr('data-idx', idx);
            $actions.append($view).append($del);

            $card.append($imgWrap).append($meta).append($actions);
            $list.append($card);
        });
    }

    function addCorrectiveFiles(fileList) {
        var incoming = fileList ? Array.prototype.slice.call(fileList) : [];
        if (!incoming.length) return;

        for (var i = 0; i < incoming.length; i++) {
            var f = incoming[i];
            if (correctiveSelectedFiles.length >= 10) {
                notify('warning', 'Maksimal 10 foto perbaikan.');
                break;
            }
            if (f.size > MAX_FILE_BYTES) {
                notify('warning', 'Ukuran foto "' + f.name + '" maksimal 10 MB.');
                continue;
            }
            if (f.type && !/^image\/(jpeg|jpg|png|webp)$/.test(f.type)) {
                notify('warning', 'Format file "' + f.name + '" harus JPG, PNG, atau WEBP.');
                continue;
            }
            correctiveSelectedFiles.push(f);
        }
        renderCorrectivePreviews();
    }

    $('#ngCorrectEvidenceInput').on('change', function () {
        addCorrectiveFiles(this.files);
        this.value = '';
    });

    $(document).on('click', '.js-remove-correct-file', function () {
        var idx = parseInt($(this).attr('data-idx'), 10);
        if (!isNaN(idx) && idx >= 0 && idx < correctiveSelectedFiles.length) {
            correctiveSelectedFiles.splice(idx, 1);
            renderCorrectivePreviews();
        }
    });

    $(document).on('click', '.js-clear-all-correct-files', function () {
        correctiveSelectedFiles = [];
        $('#ngCorrectEvidenceInput').val('');
        renderCorrectivePreviews();
    });

    $(document).on('click', '.js-open-correct-ng', function () {
        var id = $(this).data('id');
        var item = ngDataCache[id];
        if (!item) return;

        $('#ngCorrectResultId').val(id);
        $('#ngCorrectPointText').text('[' + item.prinsip + ' - LEVEL ' + item.level + '] ' + item.point_check);
        if (item.reject_reason) {
            $('#ngCorrectRejectNote').text('Catatan reject sebelumnya: ' + item.reject_reason).removeClass('d-none');
        } else {
            $('#ngCorrectRejectNote').addClass('d-none').text('');
        }
        $('#ngCorrectDate').val(cfg.today);
        $('#ngCorrectWhy1').val(item.why_1 || '');
        $('#ngCorrectWhy1Editor').html(item.why_1 || '');
        $('#ngCorrectWhy2').val(item.why_2 || '');
        $('#ngCorrectWhy2Editor').html(item.why_2 || '');
        $('#ngCorrectWhy3').val(item.why_3 || '');
        $('#ngCorrectWhy3Editor').html(item.why_3 || '');
        $('#ngCorrectRootCause').val(item.root_cause || '');
        $('#ngCorrectRootCauseEditor').html(item.root_cause || '');
        $('#ngCorrectAction').val(item.corrective_action || '');
        $('#ngCorrectActionEditor').html(item.corrective_action || '');
        correctiveSelectedFiles = [];
        $('#ngCorrectEvidenceInput').val('');
        renderCorrectivePreviews();
        switchNgPanel('correct');
    });

    $('#ngCorrectForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#ngCorrectResultId').val();
        var corrDate = $('#ngCorrectDate').val();
        var why1 = readRteValue($('#ngCorrectWhy1Editor'));
        var why2 = readRteValue($('#ngCorrectWhy2Editor'));
        var why3 = readRteValue($('#ngCorrectWhy3Editor'));
        var rootCause = readRteValue($('#ngCorrectRootCauseEditor'));
        var corrAction = readRteValue($('#ngCorrectActionEditor'));
        $('#ngCorrectWhy1').val(why1);
        $('#ngCorrectWhy2').val(why2);
        $('#ngCorrectWhy3').val(why3);
        $('#ngCorrectRootCause').val(rootCause);
        $('#ngCorrectAction').val(corrAction);
        var files = correctiveSelectedFiles.slice();

        var missing = [];
        if (!corrDate) missing.push('Tanggal Perbaikan');
        if (!why1) missing.push('Why 1');
        if (!rootCause) missing.push('Root Cause');
        if (!corrAction) missing.push('Tindakan Perbaikan');
        if (!files.length) missing.push('Foto Evidence Perbaikan');

        if (missing.length) {
            notify('warning', 'Mohon lengkapi field berikut: ' + missing.join(', ') + '.');
            if (!files.length && corrDate && why1 && rootCause && corrAction) {
                toast('warning', 'Wajib upload minimal 1 foto evidence perbaikan!');
            }
            return;
        }

        var fd = new FormData();
        fd.append('corrective_date', corrDate);
        fd.append('why_1', why1);
        fd.append('why_2', why2);
        fd.append('why_3', why3);
        fd.append('root_cause', rootCause);
        fd.append('corrective_action', corrAction);
        files.forEach(function (f) {
            fd.append('corrective_evidences[]', f);
        });

        var $btn = $('#btnSubmitNgCorrect').prop('disabled', true).text('Menyimpan...');
        $.ajax({
            url: cfg.ngBaseUrl + '/' + id + '/correct',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false
        })
            .done(function (res) {
                switchNgPanel('list');
                notify('success', (res && res.message) || 'Perbaikan tersimpan.');
                loadNgFindings();
                // Update status text & data-ng-status di tabel utama jika barisnya tampil
                var $mainRow = $('#levelingTable tbody tr[data-result-id="' + id + '"]');
                if ($mainRow.length) {
                    $mainRow.attr('data-ng-status', 'waiting_verify');
                    $mainRow.attr('data-has-correction', '1');
                    $mainRow.find('.js-ng-status-text')
                        .removeClass('text-danger text-success')
                        .addClass('text-warning font-weight-bold')
                        .text('Waiting Verify');
                }
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal menyimpan perbaikan NG.'));
            })
            .always(function () {
                $btn.prop('disabled', false).text('Simpan Perbaikan');
            });
    });

    $(document).on('click', '.js-open-verify-ng', function () {
        var id = $(this).data('id');
        var item = ngDataCache[id];
        if (!item) return;

        $('#ngVerifyResultId').val(id);
        $('#ngVerifyPointText').text('[' + item.prinsip + ' - LEVEL ' + item.level + '] ' + item.point_check);

        var whyHtml = '';
        if (item.why_1) whyHtml += '<div class="lvl-guidance-content"><strong>Why 1:</strong> ' + item.why_1 + '</div>';
        if (item.why_2) whyHtml += '<div class="lvl-guidance-content"><strong>Why 2:</strong> ' + item.why_2 + '</div>';
        if (item.why_3) whyHtml += '<div class="lvl-guidance-content"><strong>Why 3:</strong> ' + item.why_3 + '</div>';
        $('#ngVerifyWhyBlock').html(whyHtml || '<span class="text-muted">-</span>');
        $('#ngVerifyRootCauseText').html(item.root_cause ? '<span class="lvl-guidance-content">' + item.root_cause + '</span>' : '-');

        var verifyActionInfo = item.corrective_action ? '<span class="lvl-guidance-content">' + item.corrective_action + '</span>' : '-';
        if (item.corrector_name) {
            verifyActionInfo += ' <span class="text-muted">(By: ' + escHtml(item.corrector_name) + (item.corrective_date ? ', ' + escHtml(item.corrective_date) : '') + ')</span>';
        }
        $('#ngVerifyActionText').html(verifyActionInfo);

        var beforeList = (Array.isArray(item.evidence_urls) && item.evidence_urls.length) ? item.evidence_urls : (item.evidence_url ? [item.evidence_url] : []);
        var afterList = (Array.isArray(item.corrective_evidence_urls) && item.corrective_evidence_urls.length) ? item.corrective_evidence_urls : (item.corrective_evidence_url ? [item.corrective_evidence_url] : []);

        $('#ngVerifyBeforePhoto').html(
            beforeList.length
                ? beforeList.map(function (u) {
                    return buildPhotoWithEye(u, 'max-width:140px; max-height:110px;', false);
                }).join('')
                : '<span class="text-muted">Tidak ada foto</span>'
        );
        $('#ngVerifyAfterPhoto').html(
            afterList.length
                ? afterList.map(function (u) {
                    return buildPhotoWithEye(u, 'max-width:140px; max-height:110px;', false);
                }).join('')
                : '<span class="text-muted">Tidak ada foto</span>'
        );
        $('#ngVerifyRejectReason').val('');
        switchNgPanel('verify');
    });

    function submitNgVerify(action) {
        var id = $('#ngVerifyResultId').val();
        var reason = $('#ngVerifyRejectReason').val().trim();
        if (action === 'reject' && !reason) {
            notify('warning', 'Alasan reject wajib diisi.');
            return;
        }

        $('#btnApproveNgVerify, #btnRejectNgVerify').prop('disabled', true);
        $.post(cfg.ngBaseUrl + '/' + id + '/verify', {
            action: action,
            reject_reason: reason
        })
            .done(function (res) {
                switchNgPanel('list');
                notify('success', (res && res.message) || 'Verifikasi berhasil.');
                loadNgFindings();
                var $mainRow = $('#levelingTable tbody tr[data-result-id="' + id + '"]');
                if ($mainRow.length) {
                    $mainRow.attr('data-ng-status', action === 'approve' ? 'closed' : 'open');
                    var $st = $mainRow.find('.js-ng-status-text').removeClass('text-danger text-warning text-success');
                    if (action === 'approve') {
                        $st.addClass('text-success font-weight-bold').text('Closed');
                    } else {
                        $st.addClass('text-danger font-weight-bold').text('Open');
                    }
                }
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal memverifikasi temuan NG.'));
            })
            .always(function () {
                $('#btnApproveNgVerify, #btnRejectNgVerify').prop('disabled', false);
            });
    }

    $('#btnApproveNgVerify').on('click', function () { submitNgVerify('approve'); });
    $('#btnRejectNgVerify').on('click', function () { submitNgVerify('reject'); });

    $(document).on('click', '.js-unverify-ng', function () {
        var $btn = $(this);
        var id = $btn.data('id');
        if (!id) return;

        function doUnverify() {
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.post(cfg.ngBaseUrl + '/' + id + '/verify', { action: 'unverify' })
                .done(function (res) {
                    toast('success', (res && res.message) || 'Verifikasi dibatalkan.');
                    loadNgFindings();
                    var $mainRow = $('#levelingTable tbody tr[data-result-id="' + id + '"]');
                    if ($mainRow.length) {
                        $mainRow.attr('data-ng-status', 'waiting_verify');
                        $mainRow.find('.js-ng-status-text')
                            .removeClass('text-danger text-success')
                            .addClass('text-warning font-weight-bold')
                            .text('Waiting Verify');
                    }
                })
                .fail(function (xhr) {
                    notify('error', extractError(xhr, 'Gagal membatalkan verifikasi.'));
                    $btn.prop('disabled', false).html('<i class="fas fa-undo-alt"></i>');
                });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Batalkan Verifikasi?',
                text: 'Status temuan NG akan kembali ke Waiting Verify agar perbaikan dapat diedit atau diverifikasi ulang.',
                showCancelButton: true,
                confirmButtonColor: '#f6c23e',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak'
            }).then(function (r) {
                if (r.isConfirmed) doUnverify();
            });
        } else if (confirm('Batalkan verifikasi temuan NG ini?')) {
            doUnverify();
        }
    });

    // -------------------------------------------------------------------------
    // 6. Modal Kelola Master Point Check (Dinamis CRUD + Rich Text Guidance)
    // -------------------------------------------------------------------------
    var masterItemsCache = {};
    var $guidanceEditor = $('#masterGuidanceEditor');

    function syncRteEditor($editor) {
        if (!$editor || !$editor.length) return;
        var target = $editor.data('sync-target');
        if (target) {
            $(target).val($editor.html());
        } else if ($editor.is($guidanceEditor)) {
            $('#masterGuidance').val($editor.html());
        }
    }

    function resolveRteEditor($trigger) {
        var $scoped = $trigger.closest('.lvl-rte-wrapper').find('.lvl-rte-editor').first();
        return $scoped.length ? $scoped : $guidanceEditor;
    }

    // Toolbar Rich Text Editor (scoped ke masing-masing .lvl-rte-wrapper)
    $(document).on('mousedown', '.js-rte-cmd, .js-rte-color, .js-rte-block', function (e) {
        // Cegah hilangnya seleksi teks pada editor saat tombol toolbar diklik
        e.preventDefault();
    });

    $(document).on('click', '.js-rte-cmd', function () {
        var cmd = $(this).data('cmd');
        var $ed = resolveRteEditor($(this));
        if (!cmd || !$ed.length) return;
        $ed.trigger('focus');
        document.execCommand(cmd, false, null);
        syncRteEditor($ed);
    });

    $(document).on('click', '.js-rte-color', function () {
        var cmd = $(this).data('cmd');
        var val = $(this).data('val');
        var $ed = resolveRteEditor($(this));
        if (!cmd || !$ed.length) return;
        $ed.trigger('focus');
        document.execCommand(cmd, false, val);
        syncRteEditor($ed);
    });

    $(document).on('change', '.js-rte-color-input', function () {
        var cmd = $(this).data('cmd') || 'foreColor';
        var val = $(this).val();
        var $ed = resolveRteEditor($(this));
        if (!$ed.length) return;
        $ed.trigger('focus');
        document.execCommand(cmd, false, val);
        syncRteEditor($ed);
    });

    $(document).on('click', '.js-rte-block', function () {
        var tag = $(this).data('tag');
        var $ed = resolveRteEditor($(this));
        if (!tag || !$ed.length) return;
        $ed.trigger('focus');
        document.execCommand('formatBlock', false, tag);
        syncRteEditor($ed);
    });

    $(document).on('input blur keyup', '.lvl-rte-editor', function () {
        syncRteEditor($(this));
    });

    function resetMasterForm() {
        $('#masterItemId').val('');
        $('#masterFormTitle').text('TAMBAH POINT CHECK BARU');
        $('#masterPrinsip').val('');
        $('#masterLevel').val('1');
        $('#masterOrderNo').val('');
        $('#masterPointCheck').val('');
        $('#masterGuidance').val('');
        $guidanceEditor.html('');
        $('#btnSaveMasterItem').text('Simpan Point Check');
        $('#btnCancelEditMaster').addClass('d-none');
    }

    function loadMasterItems() {
        var $tbody = $('#masterItemsBody');
        $tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Memuat master point check...</td></tr>');

        $.get(cfg.masterListUrl, { plant: cfg.plant })
            .done(function (res) {
                var rows = (res && res.data) ? res.data : [];
                masterItemsCache = {};
                if (!rows.length) {
                    $tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Belum ada master point check. Silakan tambah melalui form di atas.</td></tr>');
                    return;
                }

                var html = '';
                rows.forEach(function (it) {
                    masterItemsCache[it.id] = it;
                    var stHtml = it.is_active
                        ? '<span class="text-success font-weight-bold">Aktif</span>'
                        : '<span class="text-muted font-weight-bold">Nonaktif</span>';
                    var toggleLabel = it.is_active ? 'Nonaktifkan' : 'Aktifkan';
                    var toggleCls = it.is_active ? 'text-danger' : 'text-success';
                    var guidanceCell = it.guidance_html
                        ? '<div class="lvl-guidance-content">' + it.guidance_html + '</div>'
                        : '<span class="text-muted">-</span>';

                    html += '<tr>' +
                        '<td class="text-center font-weight-bold">' + escHtml(it.prinsip) + '</td>' +
                        '<td class="text-center font-weight-bold">' + escHtml(it.level) + '</td>' +
                        '<td class="text-center">' + escHtml(it.order_no) + '</td>' +
                        '<td>' + escHtml(it.point_check) + '</td>' +
                        '<td style="vertical-align:top;">' + guidanceCell + '</td>' +
                        '<td class="text-center">' + stHtml + '</td>' +
                        '<td class="text-center text-nowrap">' +
                        '<div class="d-inline-flex align-items-center justify-content-center" style="gap: 10px;">' +
                        '<button type="button" class="btn btn-link p-0 font-weight-bold text-primary js-edit-master" style="font-size:0.72rem;" data-id="' + it.id + '">Edit</button>' +
                        '<button type="button" class="btn btn-link p-0 font-weight-bold ' + toggleCls + ' js-toggle-master" style="font-size:0.72rem;" data-id="' + it.id + '">' + toggleLabel + '</button>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';
                });
                $tbody.html(html);
            })
            .fail(function (xhr) {
                $tbody.html('<tr><td colspan="7" class="text-center text-danger py-3">' + escHtml(extractError(xhr, 'Gagal memuat master point check.')) + '</td></tr>');
            });
    }

    $('#masterItemModal').on('shown.bs.modal', function () {
        loadMasterItems();
    }).on('hidden.bs.modal', function () {
        resetMasterForm();
        if (masterChanged) {
            window.location.reload();
        }
    });

    $('#btnCancelEditMaster').on('click', function () {
        resetMasterForm();
    });

    $(document).on('click', '.js-edit-master', function () {
        var id = $(this).data('id');
        var it = masterItemsCache[id];
        if (!it) return;

        $('#masterItemId').val(it.id);
        $('#masterFormTitle').text('EDIT POINT CHECK #' + it.id);
        $('#masterPrinsip').val(it.prinsip);
        $('#masterLevel').val(it.level);
        $('#masterOrderNo').val(it.order_no);
        $('#masterPointCheck').val(it.point_check);

        var editorHtml = (it.guidance_html && it.guidance_html !== '<span class="text-muted">-</span>')
            ? it.guidance_html
            : (it.guidance || '');
        $guidanceEditor.html(editorHtml);
        $('#masterGuidance').val(editorHtml);

        $('#btnSaveMasterItem').text('Update Point Check');
        $('#btnCancelEditMaster').removeClass('d-none');

        var $modalBody = $('#masterItemModal .modal-body');
        if ($modalBody.length) {
            $modalBody.animate({ scrollTop: 0 }, 200);
        }
    });

    $('#masterItemForm').on('submit', function (e) {
        e.preventDefault();
        var prinsipVal = ($('#masterPrinsip').val() || '').trim();
        var levelVal = ($('#masterLevel').val() || '').trim();
        var pointCheckVal = ($('#masterPointCheck').val() || '').trim();

        var missing = [];
        if (!prinsipVal) missing.push('Prinsip');
        if (!levelVal) missing.push('Level');
        if (!pointCheckVal) missing.push('Point Check');

        if (missing.length) {
            notify('warning', 'Mohon lengkapi field berikut: ' + missing.join(', ') + '.');
            if (!prinsipVal) $('#masterPrinsip').trigger('focus');
            else if (!levelVal) $('#masterLevel').trigger('focus');
            else if (!pointCheckVal) $('#masterPointCheck').trigger('focus');
            return;
        }

        var id = $('#masterItemId').val();
        var url = id ? (cfg.masterBaseUrl + '/' + id) : cfg.masterStoreUrl;
        var guidanceHtml = $guidanceEditor.html().trim();
        $('#masterGuidance').val(guidanceHtml);

        var payload = {
            prinsip: prinsipVal,
            level: levelVal,
            order_no: $('#masterOrderNo').val() || null,
            point_check: pointCheckVal,
            guidance: guidanceHtml
        };
        if (id) {
            payload._method = 'PUT';
        }

        var $btn = $('#btnSaveMasterItem').prop('disabled', true);
        $.post(url, payload)
            .done(function (res) {
                masterChanged = true;
                resetMasterForm();
                loadMasterItems();
                notify('success', (res && res.message) || 'Master Point Check tersimpan.');
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal menyimpan Master Point Check.'));
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
    });

    $(document).on('click', '.js-toggle-master', function () {
        var id = $(this).data('id');
        $.post(cfg.masterBaseUrl + '/' + id + '/toggle', { plant: cfg.plant })
            .done(function (res) {
                masterChanged = true;
                loadMasterItems();
                toast('success', (res && res.message) || 'Status Master Point Check berhasil diubah.');
            })
            .fail(function (xhr) {
                notify('error', extractError(xhr, 'Gagal mengubah status Master Point Check.'));
            });
    });
});
