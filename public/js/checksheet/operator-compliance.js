/**
 * Modul JavaScript buat kelola fitur Checksheet Kepatuhan Operator
 */
$(document).ready(function () {
    // Set CSRF Token otomatis buat semua request AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Baca konfigurasi URL & data dari elemen HTML tersembunyi (bebas dari inline JS)
    var _cfgEl = document.getElementById('op-compliance-config');
    var config = _cfgEl ? {
        checksheetId: _cfgEl.dataset.checksheetId || _cfgEl.getAttribute('data-checksheet-id') || null,
        plant: _cfgEl.dataset.plant || _cfgEl.getAttribute('data-plant') || null,
        month: _cfgEl.dataset.month || _cfgEl.getAttribute('data-month') || null,
        year: _cfgEl.dataset.year || _cfgEl.getAttribute('data-year') || null,
        toggleUrl: _cfgEl.dataset.toggleUrl || _cfgEl.getAttribute('data-toggle-url') || '/checksheet/kepatuhan-operator/toggle',
        verifyUrl: _cfgEl.dataset.verifyUrl || _cfgEl.getAttribute('data-verify-url') || '/checksheet/kepatuhan-operator/verify',
        problemStoreUrl: _cfgEl.dataset.problemStoreUrl || _cfgEl.getAttribute('data-problem-store-url') || '/checksheet/kepatuhan-operator/problem',
        problemBaseUrl: _cfgEl.dataset.problemBaseUrl || _cfgEl.getAttribute('data-problem-base-url') || '/checksheet/kepatuhan-operator/problem',
        masterItemStoreUrl: _cfgEl.dataset.masterItemStoreUrl || _cfgEl.getAttribute('data-master-item-store-url') || '/checksheet/kepatuhan-operator/master-item',
        masterItemBaseUrl: _cfgEl.dataset.masterItemBaseUrl || _cfgEl.getAttribute('data-master-item-base-url') || '/checksheet/kepatuhan-operator/master-item',
        scheduleStoreUrl: _cfgEl.dataset.scheduleStoreUrl || _cfgEl.getAttribute('data-schedule-store-url') || '/checksheet/kepatuhan-operator/schedule',
        scheduleBaseUrl: _cfgEl.dataset.scheduleBaseUrl || _cfgEl.getAttribute('data-schedule-base-url') || '/checksheet/kepatuhan-operator/schedule',
        monthName: _cfgEl.dataset.monthName || _cfgEl.getAttribute('data-month-name') || ''
    } : (window.opComplianceConfig || {}); // Cadangan aman

    // Buka kembali modal Kelola Jadwal jika ditandai di sessionStorage setelah reload
    try {
        if (sessionStorage.getItem('reopenScheduleModal') === 'true') {
            sessionStorage.removeItem('reopenScheduleModal');
            $('#kelolaOperatorModal').modal('show');
            var savedOpId = sessionStorage.getItem('reopenScheduleOperatorId');
            if (savedOpId) {
                sessionStorage.removeItem('reopenScheduleOperatorId');
                $('#schOperatorId').val(savedOpId).trigger('change');
            }
        }
    } catch (e) { }

    let pendingNgCell = null;
    let ngProblemSubmitted = false;

    // Fungsi buat balikin status sel ke kosong kalau modal masalah dibatalin atau ditutup tanpa disimpan
    function revertPendingNgCell() {
        if (!pendingNgCell || ngProblemSubmitted) return;

        var cell = pendingNgCell;
        pendingNgCell = null; // Langsung kosongkan biar nggak dieksekusi 2x

        var checksheetId = cell.data('checksheet-id') || config.checksheetId;
        var itemId = cell.data('item-id');
        var day = cell.data('day');

        // Balikin tampilan sel di browser jadi kosong dulu (optimistic UI)
        cell.removeClass('cell-ok cell-ng cell-na').text('');
        cell.data('status', null);

        // Kirim request ke backend buat kosongkan status di database
        $.ajax({
            url: config.toggleUrl || '/checksheet/kepatuhan-operator/toggle',
            type: "POST",
            data: {
                checksheet_id: checksheetId,
                item_id: itemId,
                day: day,
                status: null
            },
            success: function (res) {
                if (res.success) {
                    // Update tampilan skor harian di tabel
                    $('#dailyOk_' + day).text(res.day_stats.ok);
                    var pctText = res.day_stats.pct !== null ? res.day_stats.pct + '%' : '-';
                    $('#dailyPct_' + day).text(pctText);

                    if (res.day_stats.pct !== null && res.day_stats.pct < 100) {
                        $('#dailyPct_' + day).removeClass('text-primary').addClass('text-danger');
                    } else {
                        $('#dailyPct_' + day).removeClass('text-danger').addClass('text-primary');
                    }

                    // Update statistik total bulanan
                    $('#totalOkMonth').text(res.monthly_stats.total_ok);
                    $('#monthlyPctTotal').text(res.monthly_stats.monthly_pct + '%');

                    // Update persentase rata-rata baris item
                    if (res.item_pct !== undefined) {
                        var itemPctText = res.item_pct !== '-' ? res.item_pct + '%' : '-';
                        cell.siblings('.score-cell').text(itemPctText);
                    }
                }
            },
            complete: function () {
                cell.data('is-busy', false);
            }
        });
    }

    // Fungsi bantu buat cari data masalah yang terhubung sama item tertentu
    function getProblemForItem(itemId) {
        var prob = null;
        $('.btn-edit-prob').each(function () {
            if ($(this).data('item-id') == itemId) {
                prob = {
                    id: $(this).data('id'),
                    desc: $(this).data('desc')
                };
                return false;
            }
        });
        return prob;
    }

    // Fungsi utama buat ngirim perubahan status sel ke server via AJAX
    function processCellToggle(cell, checksheetId, itemId, day, nextSt) {
        if (nextSt === 'NG') {
            pendingNgCell = cell;
            ngProblemSubmitted = false;
        } else {
            pendingNgCell = null;
        }

        // Update tampilan sel langsung di browser biar responsif (Optimistic UI)
        cell.removeClass('cell-ok cell-ng cell-na').text('');
        if (nextSt === 'OK') {
            cell.addClass('cell-ok').text('✓');
        } else if (nextSt === 'NG') {
            cell.addClass('cell-ng').text('✕');
        } else if (nextSt === 'NA') {
            cell.addClass('cell-na').text('-');
        }
        cell.data('status', nextSt);

        // Kirim perubahan ke backend
        $.ajax({
            url: config.toggleUrl || '/checksheet/kepatuhan-operator/toggle',
            type: "POST",
            data: {
                checksheet_id: checksheetId,
                item_id: itemId,
                day: day,
                status: nextSt
            },
            success: function (res) {
                if (res.success) {
                    // Update skor harian di bagian bawah tabel
                    $('#dailyOk_' + day).text(res.day_stats.ok);
                    var pctText = res.day_stats.pct !== null ? res.day_stats.pct + '%' : '-';
                    $('#dailyPct_' + day).text(pctText);

                    if (res.day_stats.pct !== null && res.day_stats.pct < 100) {
                        $('#dailyPct_' + day).removeClass('text-primary').addClass('text-danger');
                    } else {
                        $('#dailyPct_' + day).removeClass('text-danger').addClass('text-primary');
                    }

                    // Update total statistik bulanan
                    $('#totalOkMonth').text(res.monthly_stats.total_ok);
                    $('#monthlyPctTotal').text(res.monthly_stats.monthly_pct + '%');

                    // Update skor rata-rata per baris item
                    if (res.item_pct !== undefined) {
                        var itemPctText = res.item_pct !== '-' ? res.item_pct + '%' : '-';
                        cell.siblings('.score-cell').text(itemPctText);
                    }

                    // Kalau status diubah jadi NG, otomatis tampilkan modal input masalah abnormal
                    if (nextSt === 'NG') {
                        var curM = config.month || (new Date().getMonth() + 1);
                        var curY = config.year || new Date().getFullYear();
                        var autoDate = curY + '-' + String(curM).padStart(2, '0') + '-' + String(day).padStart(2, '0');

                        $('#problem_id').val('');
                        $('#problem_item_id').val(itemId);
                        $('#problem_date').val(autoDate);
                        $('#target_date').val('');
                        $('#problem_description').val('');
                        $('#corrective_action').val('');
                        $('#pic_name').val('');
                        $('input[name="status"][value="Open"]').prop('checked', true);
                        $('#formProblemTitle').html('<i class="fas fa-plus-circle mr-1"></i> FORM INPUT ITEM MASALAH ABNORMAL');

                        $('#problemLogModal').modal('show');
                    }
                }
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Anda belum dijadwalkan (Plan) untuk mengisi data pada tanggal ini.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Belum Jadwal Input',
                            text: msg
                        });
                    } else {
                        alert(msg);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memperbarui data matriks. Silakan refresh.'
                        });
                    } else {
                        alert('Gagal memperbarui data matriks. Silakan refresh.');
                    }
                }
            },
            complete: function () {
                cell.data('is-busy', false);
            }
        });
    }

    // Fungsi update angka badge merah di tombol Daftar Riwayat Masalah
    function updateProblemCountBadge() {
        var count = $('#problemTableBody tr:has(.btn-del-prob)').length;
        var $badge = $('#problemCountBadge');
        if ($badge.length === 0) {
            $('#btnProblemLogModal').append('<span class="badge badge-light ml-1" id="problemCountBadge">0</span>');
            $badge = $('#problemCountBadge');
        }

        $badge.text(count);
        if (count > 0) {
            $badge.show().css('display', 'inline-block');
        } else {
            $badge.hide();
        }
    }

    // Event handler klik sel ceklis harian (Siklus toggle: Kosong -> NA (-) -> OK (✓) -> NG (✕) -> Kosong)
    $('.day-cell').on('click', function () {
        var cell = $(this);

        // Jika sel bukan jadwal plan (disabled), tampilkan SweetAlert sekali saja dan cegah AJAX
        if (cell.hasClass('disabled-cell') || cell.data('is-plan') == '0') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Jadwal Input',
                    text: 'Anda belum dijadwalkan (Plan) untuk mengisi data pada tanggal ini.'
                });
            } else {
                alert('Belum jadwal input.');
            }
            return;
        }

        // Cegah klik beruntun / double click cepat saat request AJAX sebelumnya masih berlangsung
        if (cell.data('is-busy')) {
            return;
        }
        cell.data('is-busy', true);

        var checksheetId = cell.data('checksheet-id') || config.checksheetId;
        var itemId = cell.data('item-id');
        var day = cell.data('day');
        var currentSt = cell.data('status');

        // Putaran perubahan status: Kosong -> NA (-) -> OK (✓) -> NG (✕) -> Kosong
        var nextSt = null;
        if (!currentSt) {
            nextSt = 'NA';  // Klik 1: Kosong menjadi NA (-)
        } else if (currentSt === 'NA') {
            nextSt = 'OK';  // Klik 2: NA (-) menjadi OK (✓)
        } else if (currentSt === 'OK') {
            nextSt = 'NG';  // Klik 3: OK (✓) menjadi NG (✕)
        } else if (currentSt === 'NG') {
            nextSt = null;  // Klik 4: NG (✕) kembali Kosong (null)
        }

        // Jika tadinya NG dan mau diubah/dihapus statusnya
        if (currentSt === 'NG') {
            var linkedProb = getProblemForItem(itemId);
            if (linkedProb) {
                var displayDesc = linkedProb.desc || '';
                if (displayDesc.length > 60) displayDesc = displayDesc.substring(0, 60) + '...';

                // Tanyakan ke user apakah catatan masalah di tabel bawah juga mau dihapus
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus Item Masalah Terkait?',
                        html: 'Status temuan <b>NG (X)</b> pada item ini telah diubah.<br>Apakah Anda juga ingin menghapus catatan <b>Item Masalah</b> ("' + displayDesc + '") yang ada di Daftar Riwayat?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmColor: '#dc3545',
                        cancelColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Masalah',
                        cancelButtonText: 'Biarkan Tersimpan'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            // Hapus masalah dari database, hapus baris dari tabel, dan kosongkan status sel
                            $.ajax({
                                url: (config.problemBaseUrl || '/checksheet/kepatuhan-operator/problem') + '/' + linkedProb.id,
                                type: "DELETE",
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (delRes) {
                                    var $targetRow = $('.btn-edit-prob[data-id="' + linkedProb.id + '"], .btn-del-prob[data-id="' + linkedProb.id + '"]').closest('tr');
                                    $targetRow.remove();
                                    if ($('#problemTableBody tr:has(.btn-del-prob)').length === 0) {
                                        $('#problemTableBody').html('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada daftar item masalah yang tercatat.</td></tr>');
                                    }
                                    updateProblemCountBadge();

                                    processCellToggle(cell, checksheetId, itemId, day, null);
                                }
                            });
                        } else {
                            // Biarkan tersimpan: Data masalah TETAP ADA & perubahan sel dibatalkan
                        }
                    });
                    return; // Hentikan eksekusi biar diproses di callback SweetAlert
                }
            }
        }

        processCellToggle(cell, checksheetId, itemId, day, nextSt);
    });

    // Jalankan penanganan batal sel kalau modal masalah ditutup
    $('#problemLogModal').on('hide.bs.modal hidden.bs.modal', function () {
        revertPendingNgCell();
    });

    // Tangkap klik tombol tutup / X di modal masalah
    $(document).on('click', '#problemLogModal [data-dismiss="modal"], #problemLogModal .close', function () {
        revertPendingNgCell();
    });

    // Efek efek getar (shake) kalau user klik di luar area modal (backdrop click)
    $('#problemLogModal').on('click', function (e) {
        if ($(e.target).is('#problemLogModal')) {
            var modalContent = $(this).find('.modal-content');
            var closeBtn = $(this).find('.close');

            modalContent.removeClass('modal-shake').addClass('modal-shake');
            closeBtn.addClass('close-btn-highlight');

            setTimeout(function () {
                modalContent.removeClass('modal-shake');
                closeBtn.removeClass('close-btn-highlight');
            }, 600);
        }
    });

    // Format tanggal jam verifikasi biar rapi 2 baris
    function formatVerifDate(dateStr) {
        if (!dateStr) return '';
        var parts = $.trim(dateStr).split(' ');
        if (parts.length >= 2) {
            var d = parts[0].replace(/\/20(\d\d)$/, '/$1'); // 25/09/2026 -> 25/09/26
            var t = parts[1];
            return d + '<br>' + t;
        }
        return dateStr;
    }

    // Checkbox Verifikasi Harian Leader / Kashift
    $(document).on('change', '.verify-daily-checkbox', function () {
        var cb = $(this);
        var day = cb.data('day');
        var isChecked = cb.is(':checked') ? 1 : 0;
        var checksheetId = cb.data('checksheet-id') || config.checksheetId;

        $.ajax({
            url: config.verifyUrl || '/checksheet/kepatuhan-operator/verify',
            type: "POST",
            data: {
                checksheet_id: checksheetId,
                type: 'leader',
                day: day,
                checked: isChecked
            },
            success: function (res) {
                if (res.success) {
                    var detailDiv = $('#leaderDetail_' + day);
                    if (res.checked) {
                        detailDiv.html(
                            '<div class="text-success font-weight-bold" style="font-size:0.46rem; white-space:nowrap; line-height:1.2;">✓ Approved</div>' +
                            '<div class="text-dark font-weight-bold" style="font-size:0.48rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="' + res.user_name + '">' + res.user_name + '</div>' +
                            '<div class="text-muted" style="font-size:0.45rem; line-height:1.1; margin-top:1px;">' + formatVerifDate(res.date) + '</div>'
                        );
                    } else {
                        detailDiv.html('');
                    }
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memperbarui verifikasi harian leader.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                } else {
                    alert(msg);
                }
                cb.prop('checked', !isChecked);
            }
        });
    });

    // Checkbox Verifikasi Mingguan SPV / Karu
    $(document).on('change', '.verify-weekly-checkbox', function () {
        var cb = $(this);
        var week = cb.data('week');
        var isChecked = cb.is(':checked') ? 1 : 0;
        var checksheetId = cb.data('checksheet-id') || config.checksheetId;

        $.ajax({
            url: config.verifyUrl || '/checksheet/kepatuhan-operator/verify',
            type: "POST",
            data: {
                checksheet_id: checksheetId,
                type: 'spv',
                week: week,
                checked: isChecked
            },
            success: function (res) {
                if (res.success) {
                    var span = $('#spvText_W' + week);
                    var detail = $('#spvDetail_W' + week);
                    if (res.checked) {
                        span.removeClass('text-muted').addClass('text-success').text('✓ Approved');
                        detail.html('<span class="text-success font-weight-bold">' + res.user_name + '</span> <span class="text-muted ml-1">(' + res.date + ')</span>');
                    } else {
                        span.removeClass('text-success').addClass('text-muted').text('Minggu-' + week);
                        detail.html('');
                    }
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memperbarui verifikasi mingguan SPV.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                } else {
                    alert(msg);
                }
                cb.prop('checked', !isChecked);
            }
        });
    });

    // Checkbox Verifikasi Bulanan Manager / Asst Manager
    $(document).on('change', '.verify-monthly-checkbox', function () {
        var cb = $(this);
        var isChecked = cb.is(':checked') ? 1 : 0;
        var checksheetId = cb.data('checksheet-id') || config.checksheetId;

        $.ajax({
            url: config.verifyUrl || '/checksheet/kepatuhan-operator/verify',
            type: "POST",
            data: {
                checksheet_id: checksheetId,
                type: 'mgr',
                checked: isChecked
            },
            success: function (res) {
                if (res.success) {
                    var textSpan = $('#mgrStatusText');
                    var detailSpan = $('#mgrDetailText');
                    var defaultText = textSpan.data('default-text') || config.monthName || 'Bulan Present';

                    if (res.checked) {
                        textSpan.removeClass('text-muted').addClass('text-success').text('✓ Approved');
                        detailSpan.html('<span class="text-success font-weight-bold">' + res.user_name + '</span> <span class="text-muted ml-1">(' + res.date + ')</span>');
                    } else {
                        textSpan.removeClass('text-success').addClass('text-muted').text(defaultText);
                        detailSpan.html('');
                    }
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memperbarui verifikasi bulanan.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                } else {
                    alert(msg);
                }
                cb.prop('checked', !isChecked);
            }
        });
    });

    // Submit form input/edit item masalah (problem log)
    $('#problemForm').on('submit', function (e) {
        e.preventDefault();
        ngProblemSubmitted = true;

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);

        $.ajax({
            url: config.problemStoreUrl || '/checksheet/kepatuhan-operator/problem',
            type: "POST",
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Data item masalah berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    $btn.prop('disabled', false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: res.message || 'Gagal menyimpan item masalah.'
                        });
                    } else {
                        alert('Gagal menyimpan item masalah.');
                    }
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false);
                ngProblemSubmitted = false;
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan item masalah.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: msg
                    });
                } else {
                    alert(msg);
                }
            }
        });
    });

    // Tombol Edit Item Masalah di tabel riwayat
    $(document).on('click', '.btn-edit-prob', function () {
        var btn = $(this);
        $('#problem_id').val(btn.data('id'));
        $('#problem_date').val(btn.data('date'));
        $('#problem_item_id').val(btn.data('item-id'));
        $('#pic_name').val(btn.data('pic'));
        $('#target_date').val(btn.data('target'));
        $('#problem_description').val(btn.data('desc'));
        $('#corrective_action').val(btn.data('action'));
        var st = btn.data('status');
        if (st === 'Close') st = 'Closed';
        if (st === 'In Progres') st = 'In Progress';
        $('input[name="status"][value="' + st + '"]').prop('checked', true);

        $('#formProblemTitle').html('<i class="fas fa-edit mr-1"></i> EDIT ITEM MASALAH ABNORMAL');
        $('#problemLogModal .modal-body').animate({ scrollTop: 0 }, 'fast');
    });

    // Tombol Hapus Item Masalah di tabel riwayat (pake konfirmasi SweetAlert2)
    $(document).on('click', '.btn-del-prob', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var baseUrl = config.problemBaseUrl || '/checksheet/kepatuhan-operator/problem';

        var doDelete = function () {
            $.ajax({
                url: baseUrl + '/' + id,
                type: "DELETE",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (res.success) {
                        var $targetRow = $('.btn-edit-prob[data-id="' + id + '"], .btn-del-prob[data-id="' + id + '"]').closest('tr');
                        $targetRow.remove();
                        if ($('#problemTableBody tr:has(.btn-del-prob)').length === 0) {
                            $('#problemTableBody').html('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada daftar item masalah yang tercatat.</td></tr>');
                        }
                        updateProblemCountBadge();

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Item masalah berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: res.message || 'Gagal menghapus item masalah.'
                            });
                        } else {
                            alert('Gagal menghapus item masalah.');
                        }
                    }
                },
                error: function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Gagal menghapus item masalah.'
                        });
                    } else {
                        alert('Gagal menghapus item masalah.');
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Item Masalah?',
                text: 'Apakah Anda yakin ingin menghapus item masalah ini? Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        }
    });

    // Reset isi form item masalah saat modal ditutup
    $('#problemLogModal').on('hidden.bs.modal', function () {
        $('#problem_id').val('');
        $('#problem_item_id').val('');
        $('#problem_description').val('');
        $('#corrective_action').val('');
        $('#pic_name').val('');
        $('#target_date').val('');
        $('input[name="status"][value="Open"]').prop('checked', true);
        $('#formProblemTitle').html('<i class="fas fa-plus-circle mr-1"></i> FORM INPUT ITEM MASALAH ABNORMAL');
    });

    // Submit Form Tambah / Edit Master Item Audit (Khusus Admin)
    $('#masterItemForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#master_item_id').val();
        var baseUrl = config.masterItemBaseUrl || '/checksheet/kepatuhan-operator/master-item';
        var url = id ? baseUrl + '/' + id : (config.masterItemStoreUrl || baseUrl);
        var type = id ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: type,
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    location.reload();
                }
            }
        });
    });

    // Tombol Edit Master Item Audit (Khusus Admin)
    $(document).on('click', '.btn-edit-mi', function () {
        var btn = $(this);
        $('#master_item_id').val(btn.data('id'));
        $('#mi_prinsip_dasar').val(btn.data('prinsip'));
        $('#mi_item_check').val(btn.data('item'));
        $('#mi_standard').val(btn.data('standard'));
        $('#mi_order_no').val(btn.data('order'));

        $('#masterItemFormTitle').html('<i class="fas fa-edit mr-1"></i> EDIT ITEM AUDIT');
    });

    // Tombol Hapus Master Item Audit (Khusus Admin)
    $(document).on('click', '.btn-del-mi', function () {
        if (!confirm('Yakin ingin menghapus item audit ini?')) return;
        var id = $(this).data('id');
        var baseUrl = config.masterItemBaseUrl || '/checksheet/kepatuhan-operator/master-item';
        $.ajax({
            url: baseUrl + '/' + id,
            type: "DELETE",
            success: function (res) {
                if (res.success) {
                    location.reload();
                }
            }
        });
    });

    // Tombol Cetak Langsung (pake hidden iframe biar nggak perlu buka tab baru)
    $(document).on('click', '#btnCetakCompliance', function (e) {
        e.preventDefault();
        var printUrl = $(this).data('print-url');
        if (!printUrl) return;

        // Hapus iframe cetak yang lama kalau ada
        $('#compliancePrintIframe').remove();

        var $iframe = $('<iframe>', {
            id: 'compliancePrintIframe',
            src: printUrl,
            style: 'visibility: hidden; position: fixed; right: 0; bottom: 0; width: 0; height: 0; border: none;'
        });

        $('body').append($iframe);

        $iframe.on('load', function () {
            var frameWin = this.contentWindow;
            if (frameWin) {
                frameWin.focus();
                frameWin.print();
            }
        });
    });

    // Tombol Cetak Schedule (Print View Langsung di Halaman Itu)
    $(document).on('click', '#btnCetakSchedule', function (e) {
        e.preventDefault();
        window.print();
    });

    // Tombol Sinkronkan Riwayat ke Schedule (Plan P)
    $(document).on('click', '#btnSyncHistoricalSchedule', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Sinkronkan Data Riwayat?',
            text: 'Sistem akan memeriksa seluruh checksheet aktual dan membuatkan jadwal Plan (P) pada tanggal-tanggal yang pernah diisi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Ya, Sinkronkan!',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Sinkronisasi...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/checksheet/kepatuhan-operator/schedule/sync-historical',
                    method: 'POST',
                    dataType: 'json',
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sinkronisasi Berhasil!',
                            text: res.message,
                            confirmButtonColor: '#4e73df'
                        }).then(function () {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat sinkronisasi.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Sinkron',
                            text: msg,
                            confirmButtonColor: '#e74a3b'
                        });
                    }
                });
            }
        });
    });

    // Filter tabel schedule langsung saat mengetik di input Cari Operator
    $(document).on('input', '#scheduleSearchInput', function () {
        var query = $(this).val().toLowerCase().trim();
        if (!query) {
            $('.op-schedule-grid tbody tr').show();
            return;
        }
        $('.op-schedule-grid tbody tr.sch-op-row').each(function () {
            var $planRow = $(this);
            var $actRow = $planRow.next('tr.sch-op-row-sub');
            var opName = ($planRow.data('op-name') || '').toString().toLowerCase();
            var bagian = ($planRow.data('bagian') || '').toString().toLowerCase();
            if (opName.indexOf(query) !== -1 || bagian.indexOf(query) !== -1) {
                $planRow.show();
                $actRow.show();
            } else {
                $planRow.hide();
                $actRow.hide();
            }
        });
    });

    // Trigger picker saat input tanggal diklik atau fokus
    $(document).on('click focus', '.sch-date-input', function () {
        if (typeof this.showPicker === 'function') {
            try { this.showPicker(); } catch (e) { }
        }
    });

    // Filter tabel daftar jadwal berdasarkan Operator / Inspector yang dipilih
    function filterScheduleTable() {
        var selectedOpId = $('#schOperatorId').val();
        var $allRows = $('#tableSchedules tbody tr.sch-row');

        $('#tableSchedules tbody tr.sch-info-row').remove();

        if (!selectedOpId) {
            $allRows.hide();
            $('#tableSchedules tbody').append(
                '<tr class="sch-info-row"><td colspan="5" class="text-center text-muted font-italic py-4">' +
                '<i class="fas fa-info-circle mr-1 text-primary"></i> Silakan pilih Operator / Inspector pada form di sebelah kiri untuk melihat daftar jadwal.' +
                '</td></tr>'
            );
            return;
        }

        var $matchingRows = $allRows.filter('[data-operator-id="' + selectedOpId + '"]');

        if ($matchingRows.length > 0) {
            $allRows.hide();
            $matchingRows.show();
        } else {
            $allRows.hide();
            $('#tableSchedules tbody').append(
                '<tr class="sch-info-row"><td colspan="5" class="text-center text-muted font-italic py-4">' +
                '<i class="fas fa-calendar-times mr-1 text-warning"></i> Belum ada jadwal yang di-set untuk operator ini.' +
                '</td></tr>'
            );
        }
    }

    // Trigger filter saat memilih Operator / Inspector di form
    $(document).on('change', '#schOperatorId', function () {
        var opId = $(this).val();
        filterScheduleTable();

        if (opId) {
            // Auto-pilih bagian jika operator memiliki bagian default
            var opBagian = $(this).find('option:selected').data('bagian');
            if (opBagian && $('#schBagian option[value="' + opBagian + '"]').length > 0) {
                $('#schBagian').val(opBagian);
            } else {
                // Alternatif: ambil dari jadwal terakhir operator ini
                var existingBagian = $('#tableSchedules tbody tr.sch-row[data-operator-id="' + opId + '"]:first td:nth-child(2)').text().trim();
                if (existingBagian && $('#schBagian option[value="' + existingBagian + '"]').length > 0) {
                    $('#schBagian').val(existingBagian);
                }
            }
        }
    });

    // Inisialisasi filter saat modal kelola operator dibuka
    $('#kelolaOperatorModal').on('shown.bs.modal', function () {
        var savedOpId = sessionStorage.getItem('reopenScheduleOperatorId');
        if (savedOpId) {
            sessionStorage.removeItem('reopenScheduleOperatorId');
            $('#schOperatorId').val(savedOpId).trigger('change');
        } else {
            filterScheduleTable();
        }
    });

    // Tambah baris tanggal plan baru saat klik tombol +
    $(document).on('click', '#btnAddDateRow', function (e) {
        e.preventDefault();
        var newRowHtml = '<div class="input-group input-group-sm sch-date-row mt-2">' +
            '<input type="date" class="form-control form-control-sm border-0 shadow-sm sch-date-input" style="cursor: pointer; background-color: #fff;" onclick="try{this.showPicker()}catch(e){}">' +
            '<div class="input-group-append">' +
            '<button class="btn btn-outline-danger border-0 shadow-sm btn-remove-date-row" type="button" title="Hapus Tanggal" style="width: 34px; padding: 0;">' +
            '<i class="fas fa-times"></i>' +
            '</button>' +
            '</div>' +
            '</div>';
        $('#schDatesContainer').append(newRowHtml);

        var $newInput = $('#schDatesContainer .sch-date-row:last-child .sch-date-input');
        $newInput.focus();
        if (typeof $newInput[0].showPicker === 'function') {
            try { $newInput[0].showPicker(); } catch (e) { }
        }
    });

    // Hapus baris tanggal plan
    $(document).on('click', '.btn-remove-date-row', function (e) {
        e.preventDefault();
        $(this).closest('.sch-date-row').remove();
    });

    // Tombol Edit Jadwal Operator
    $(document).on('click', '.btn-edit-schedule', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var id = $btn.data('id');
        var opId = $btn.data('operator-id');
        var bagian = $btn.data('bagian');
        var shift = $btn.data('shift');
        var date = $btn.data('date');

        $('#schEditId').val(id);
        $('#schOperatorId').val(opId);
        filterScheduleTable();

        // Beri visual highlight pada baris yang sedang diedit
        $('.sch-row').removeClass('table-warning font-weight-bold');
        $btn.closest('.sch-row').addClass('table-warning font-weight-bold');

        if (bagian) {
            $('#schBagian').val(bagian);
        }
        if (shift) {
            $('#schShift').val(shift);
        }

        // Set baris tanggal menjadi 1 baris khusus untuk tanggal yang diedit
        $('#schDatesContainer').html(
            '<div class="sch-date-row">' +
            '<input type="date" class="form-control form-control-sm border-0 shadow-sm sch-date-input" value="' + date + '" style="cursor: pointer; background-color: #fff;" onclick="try{this.showPicker()}catch(e){}">' +
            '</div>'
        );
        $('#schDatesHint').hide();

        // Ubah tampilan form ke mode Edit
        $('#formScheduleTitle').html('<i class="fas fa-edit mr-1 text-primary"></i> Edit Jadwal');
        $('#btnSaveSchedule').html('<i class="fas fa-check mr-1"></i> Update Jadwal');
        $('#btnCancelEditSchedule').removeClass('d-none');

        // Scroll halus ke form jika di layar kecil
        $('#kelolaOperatorModal .modal-body').animate({ scrollTop: 0 }, 'fast');
    });

    // Fungsi reset form jadwal kembali ke mode Tambah Baru
    function resetScheduleForm() {
        $('#schEditId').val('');
        $('.sch-row').removeClass('table-warning font-weight-bold');
        $('#formScheduleTitle').html('Form Jadwal');
        $('#btnSaveSchedule').html('<i class="fas fa-save mr-1"></i> Simpan Jadwal');
        $('#btnCancelEditSchedule').addClass('d-none');
        $('#schDatesHint').show();
        $('#schDatesContainer').html(
            '<div class="input-group input-group-sm sch-date-row">' +
            '<input type="date" class="form-control form-control-sm border-0 shadow-sm sch-date-input" style="cursor: pointer; background-color: #fff;" onclick="try{this.showPicker()}catch(e){}">' +
            '<div class="input-group-append">' +
            '<button type="button" class="btn btn-primary shadow-sm" id="btnAddDateRow" title="Tambah Tanggal Plan" style="width: 34px; padding: 0;">' +
            '<i class="fas fa-plus"></i>' +
            '</button>' +
            '</div>' +
            '</div>'
        );
    }

    // Tombol Batal Edit
    $(document).on('click', '#btnCancelEditSchedule', function (e) {
        e.preventDefault();
        resetScheduleForm();
    });

    // Reset form saat modal ditutup
    $('#kelolaOperatorModal').on('hidden.bs.modal', function () {
        resetScheduleForm();
    });

    // Fungsi simpan / update jadwal operator
    function submitOperatorSchedule() {
        var $btn = $('#btnSaveSchedule');
        var operatorId = $('#schOperatorId').val();
        var bagian = $('#schBagian').val();
        var shift = $('#schShift').val();
        var editId = $('#schEditId').val();

        if (!operatorId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Operator Belum Dipilih',
                    text: 'Silakan pilih Operator / Inspector terlebih dahulu.'
                });
            } else {
                alert('Silakan pilih Operator / Inspector terlebih dahulu.');
            }
            $('#schOperatorId').focus();
            return;
        }

        var dates = [];
        $('.sch-date-input').each(function () {
            var val = $(this).val();
            if (val && !dates.includes(val)) {
                dates.push(val);
            }
        });

        if (dates.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Plan Belum Diisi',
                    text: 'Silakan pilih minimal satu Tanggal Plan terlebih dahulu.'
                });
            } else {
                alert('Silakan pilih minimal satu Tanggal Plan terlebih dahulu.');
            }
            $('.sch-date-input').first().focus();
            return;
        }

        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        // Jika dalam mode Edit, lakukan request PUT
        if (editId) {
            var updateData = {
                operator_id: operatorId,
                bagian: bagian,
                shift: shift,
                schedule_date: dates[0],
                plant: config.plant || new URLSearchParams(window.location.search).get('plant') || 'karawang',
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT'
            };

            var updateUrl = (config.scheduleBaseUrl || '/checksheet/kepatuhan-operator/schedule') + '/' + editId;

            $.ajax({
                url: updateUrl,
                type: "POST",
                data: updateData,
                success: function (res) {
                    if (res.success) {
                        try {
                            sessionStorage.setItem('reopenScheduleModal', 'true');
                            sessionStorage.setItem('reopenScheduleOperatorId', operatorId);
                        } catch (e) { }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message || 'Jadwal Operator berhasil diperbarui.',
                                timer: 1200,
                                showConfirmButton: false
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            location.reload();
                        }
                    } else {
                        $btn.prop('disabled', false).html(origHtml);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal memperbarui jadwal.' });
                        } else {
                            alert(res.message);
                        }
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(origHtml);
                    var errMsg = 'Gagal memperbarui jadwal.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: errMsg });
                    } else {
                        alert(errMsg);
                    }
                }
            });
            return;
        }

        // Mode Tambah Baru (Multi-Tanggal)
        var data = {
            operator_id: operatorId,
            bagian: bagian,
            shift: shift,
            schedule_dates: dates,
            plant: config.plant || new URLSearchParams(window.location.search).get('plant') || 'karawang',
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: config.scheduleStoreUrl || '/checksheet/kepatuhan-operator/schedule',
            type: "POST",
            data: data,
            success: function (res) {
                if (res.success) {
                    try {
                        sessionStorage.setItem('reopenScheduleModal', 'true');
                        sessionStorage.setItem('reopenScheduleOperatorId', operatorId);
                    } catch (e) { }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Jadwal Operator berhasil disimpan.',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    $btn.prop('disabled', false).html(origHtml);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menyimpan jadwal.' });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html(origHtml);
                var errMsg = 'Gagal menyimpan jadwal.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: errMsg });
                } else {
                    alert(errMsg);
                }
            }
        });
    }

    // Listener tombol simpan jadwal
    $(document).on('click', '#btnSaveSchedule', function (e) {
        e.preventDefault();
        submitOperatorSchedule();
    });

    // Listener form submit (misal ditekan tombol Enter)
    $(document).on('submit', '#formOperatorSchedule', function (e) {
        e.preventDefault();
        submitOperatorSchedule();
    });

    // Hapus Jadwal Operator dengan konfirmasi SweetAlert2
    $(document).on('click', '.btn-delete-schedule', function () {
        var id = $(this).data('id');
        var opId = $(this).data('operator-id') || $(this).closest('tr').data('operator-id') || $('#schOperatorId').val();
        var baseUrl = config.scheduleBaseUrl || '/checksheet/kepatuhan-operator/schedule';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Jadwal?',
                text: 'Yakin ingin menghapus jadwal ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    executeDeleteSchedule(id, baseUrl, opId);
                }
            });
        } else {
            if (confirm('Yakin ingin menghapus jadwal ini?')) {
                executeDeleteSchedule(id, baseUrl, opId);
            }
        }
    });

    function executeDeleteSchedule(id, baseUrl, opId) {
        $.ajax({
            url: baseUrl + '/' + id,
            type: "DELETE",
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                if (res.success) {
                    try {
                        sessionStorage.setItem('reopenScheduleModal', 'true');
                        if (opId) sessionStorage.setItem('reopenScheduleOperatorId', opId);
                    } catch (e) { }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Jadwal berhasil dihapus.',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal menghapus jadwal.' });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghapus jadwal.' });
                } else {
                    alert('Gagal menghapus jadwal.');
                }
            }
        });
    }

    // Inisialisasi pencarian cepat (single search input) pada dropdown filter operator
    if (typeof initItemSearch === 'function') {
        initItemSearch('filterOperator', { placeholder: 'Ketik Operator / Inspector...', maxResults: 50, hideSelect: true });
        initItemSearch('scheduleFilterOperator', { placeholder: 'Ketik Operator / Inspector...', maxResults: 50, hideSelect: true });
    }

    // Hitung & update badge angka masalah saat pertama kali halaman dimuat
    updateProblemCountBadge();
});
