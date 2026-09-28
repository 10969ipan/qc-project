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
        checksheetId:        _cfgEl.dataset.checksheetId      || null,
        month:               _cfgEl.dataset.month             || null,
        year:                _cfgEl.dataset.year              || null,
        toggleUrl:           _cfgEl.dataset.toggleUrl          || '/checksheet/kepatuhan-operator/toggle',
        verifyUrl:           _cfgEl.dataset.verifyUrl          || '/checksheet/kepatuhan-operator/verify',
        problemStoreUrl:     _cfgEl.dataset.problemStoreUrl    || '/checksheet/kepatuhan-operator/problem',
        problemBaseUrl:      _cfgEl.dataset.problemBaseUrl     || '/checksheet/kepatuhan-operator/problem',
        masterItemStoreUrl:  _cfgEl.dataset.masterItemStoreUrl || '/checksheet/kepatuhan-operator/master-item',
        masterItemBaseUrl:   _cfgEl.dataset.masterItemBaseUrl  || '/checksheet/kepatuhan-operator/master-item'
    } : (window.opComplianceConfig || {}); // Cadangan aman

    let pendingNgCell = null;
    let ngProblemSubmitted = false;

    // Fungsi buat balikin status sel ke kosong kalau modal masalah dibatalin atau ditutup tanpa disimpan
    function revertPendingNgCell() {
        if (!pendingNgCell || ngProblemSubmitted) return;

        var cell = pendingNgCell;
        pendingNgCell = null; // Langsung kosongkan biar nggak dieksekusi 2x

        var checksheetId = cell.data('checksheet-id') || config.checksheetId;
        var itemId       = cell.data('item-id');
        var day          = cell.data('day');

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
            error: function () {
                alert('Gagal memperbarui data matriks. Silakan refresh.');
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

    // Event handler klik sel ceklis harian (Siklus toggle: Kosong -> OK -> NG -> Kosong)
    $('.day-cell').on('click', function () {
        var cell = $(this);
        var checksheetId = cell.data('checksheet-id') || config.checksheetId;
        var itemId       = cell.data('item-id');
        var day          = cell.data('day');
        var currentSt    = cell.data('status');

        // Putaran perubahan status: Kosong -> OK -> NG -> Kosong
        var nextSt = null;
        if (!currentSt) {
            nextSt = 'OK';
        } else if (currentSt === 'OK') {
            nextSt = 'NG';
        } else if (currentSt === 'NG') {
            nextSt = null; // Kembali dikosongkan
        } else if (currentSt === 'NA') {
            nextSt = null;
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
                            '<div class="text-success font-weight-bold" style="font-size:0.50rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="' + res.user_name + '">' + res.user_name + '</div>' +
                            '<div class="text-muted" style="font-size:0.48rem; line-height:1.1; margin-top:1px;">' + formatVerifDate(res.date) + '</div>'
                        );
                    } else {
                        detailDiv.html('');
                    }
                }
            },
            error: function () {
                alert('Gagal memperbarui verifikasi harian leader.');
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
                        span.removeClass('text-muted').addClass('text-success').text('Minggu-' + week + ' ✓');
                        detail.html('<span class="text-success font-weight-bold">' + res.user_name + '</span> <span class="text-muted ml-1">(' + res.date + ')</span>');
                    } else {
                        span.removeClass('text-success').addClass('text-muted').text('Minggu-' + week);
                        detail.html('');
                    }
                }
            },
            error: function () {
                alert('Gagal memperbarui verifikasi mingguan SPV.');
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
                    var textSpan   = $('#mgrStatusText');
                    var detailSpan = $('#mgrDetailText');

                    if (res.checked) {
                        textSpan.removeClass('text-muted').addClass('text-success').text('✓ Verified (Asst Mgr)');
                        detailSpan.html('<span class="text-success font-weight-bold">' + res.user_name + '</span> <span class="text-muted ml-1">(' + res.date + ')</span>');
                    } else {
                        textSpan.removeClass('text-success').addClass('text-muted').text('Ceklis Verifikasi Bulanan Asst Mgr');
                        detailSpan.html('');
                    }
                }
            },
            error: function () {
                alert('Gagal memperbarui verifikasi bulanan.');
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

    // Inisialisasi fitur pencarian cepat pada dropdown filter operator jika fungsi tersedia
    if (typeof initItemSearch === 'function') {
        initItemSearch('filterOperator', { placeholder: 'Ketik Operator / Inspector...', maxResults: 50 });
    }

    // Hitung & update badge angka masalah saat pertama kali halaman dimuat
    updateProblemCountBadge();
});
