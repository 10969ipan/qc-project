$(document).ready(function () {
    // 1. Initialize Modules & Search
    if (typeof window.initInProcessIndex === 'function') {
        window.initInProcessIndex({
            qrScannerModalId: '#qrScannerModal',
            btnScanId: '#btnScanQRIndex',
            inputQrId: '#filterQrRaw'
        });
    }

    if (typeof initItemSearch === 'function') {
        initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
        initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
        initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
        initItemSearch('filterMethod', { placeholder: 'Ketik Tipe...', maxResults: 5 });
    }

    // 2. Filter Form & Export Links Sync
    var form = document.getElementById('filterFormInProcess');
    if (form) {
        function syncExportLinks() {
            var params = new URLSearchParams();
            var formData = new FormData(form);
            for (var pair of formData.entries()) {
                if (pair[1]) params.append(pair[0], pair[1]);
            }
            
            var queryString = params.toString();
            
            var printBtn = form.querySelector('a[title="Print"]');
            var pdfBtn = form.querySelector('a[title="Export to PDF"]');
            var measurementsBtn = form.querySelector('a[title="Export Data Dimensi (XLSX)"]');
            var recapBtn = document.getElementById('btnDailyRecap');
            
            if (printBtn) printBtn.href = window.inProcessConfig.routePrint + '?' + queryString;
            if (pdfBtn) pdfBtn.href = window.inProcessConfig.routeExportPdf + '?' + queryString;
            if (measurementsBtn) measurementsBtn.href = window.inProcessConfig.routeExportMeasurements + '?' + queryString;
            if (recapBtn) recapBtn.href = window.inProcessConfig.routeDailyRecap + '?' + queryString;
        }

        $(form).find('input, select').on('change', syncExportLinks);
        syncExportLinks(); // initial load

        $(form).on('submit', function(e) {
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;

            if (startDate && endDate && startDate > endDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Rentang Tanggal Tidak Valid',
                    text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                    confirmButtonColor: '#4e73df'
                });
            }
        });
    }

    // 3. Direct Print
    $(document).on('click', '.btn-print-direct', function(e) {
        e.preventDefault();
        var printUrl = $(this).attr('href');
        if (!printUrl || printUrl === '#') return;

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

    // 4. Import Measurements Modal
    $('#importFile').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    $('#importMeasurementsForm').on('submit', function() {
        $('#importMeasurementsModal').modal('hide');
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Mengunggah & Memproses Data...',
            showConfirmButton: false,
            timerProgressBar: true
        });
    });

    // 5. Bulk Check & Delete Logic
    var savedScroll = sessionStorage.getItem('inProcessScrollPos');
    if (savedScroll) {
        $('.table-responsive').scrollTop(savedScroll);
        sessionStorage.removeItem('inProcessScrollPos');
    }

    $(window).on('beforeunload', function() {
        sessionStorage.setItem('inProcessScrollPos', $('.table-responsive').scrollTop());
    });

    const checkAllBtn = $('#checkAllRows');
    const rowCheckboxes = $('.row-checkbox');
    const countDisplay = $('#checkedCountDisplay');
    const bulkMenu = $('#bulkActionMenu');
    const bulkSelectedCount = $('#bulkSelectedCount');
    const btnBulkDelete = $('#btnBulkDelete');

    function updateCount() {
        const checkedCount = $('.row-checkbox:checked').length;
        countDisplay.text(checkedCount);
        if (bulkSelectedCount.length > 0) {
            bulkSelectedCount.text(checkedCount);
        }
        
        if(rowCheckboxes.length > 0) {
            checkAllBtn.prop('checked', checkedCount === rowCheckboxes.length);
        }

        if (checkedCount > 0) {
            bulkMenu.fadeIn(200);
        } else {
            bulkMenu.fadeOut(200);
        }

        $('.row-checkbox').each(function() {
            const row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                row.css('background-color', 'rgba(78, 115, 223, 0.05)');
            } else {
                row.css('background-color', '');
            }
        });
    }

    if (checkAllBtn.length > 0) {
        checkAllBtn.on('change', function() {
            const isChecked = $(this).prop('checked');
            rowCheckboxes.prop('checked', isChecked);
            updateCount();
        });
    }

    if (rowCheckboxes.length > 0) {
        rowCheckboxes.on('change', function() {
            updateCount();
        });
    }

    if (btnBulkDelete.length > 0) {
        btnBulkDelete.on('click', function() {
            const selectedIds = $('.row-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Apakah Anda yakin ingin menghapus " + selectedIds.length + " data yang dipilih? Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus Data...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: window.inProcessConfig.routeBulkDestroy + window.location.search,
                        type: 'POST',
                        data: {
                            _token: window.inProcessConfig.csrfToken,
                            ids: selectedIds
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    if (response.redirect) {
                                        window.location.href = response.redirect;
                                    } else {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            var errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.statusText ? ('Terjadi kesalahan sistem (' + xhr.status + ': ' + xhr.statusText + ')') : 'Terjadi kesalahan sistem.');
                            Swal.fire('Error!', errMsg, 'error');
                        }
                    });
                }
            });
        });
    }

    // 6. Hidden Items Logic (Admin)
    if ($('#modalHiddenItems').length > 0) {
        function sortHiddenTableRows() {
            var tbody = $('#tableHiddenItems tbody');
            var rows = tbody.find('tr.hidden-item-row').get();
            rows.sort(function(a, b) {
                var aChecked = $(a).find('.chk-hidden-item').is(':checked') ? 1 : 0;
                var bChecked = $(b).find('.chk-hidden-item').is(':checked') ? 1 : 0;
                if (aChecked !== bChecked) {
                    return bChecked - aChecked; // Checked (1) comes before Unchecked (0)
                }
                var aName = $(a).find('td:nth-child(3)').text().trim().toLowerCase();
                var bName = $(b).find('td:nth-child(3)').text().trim().toLowerCase();
                return aName.localeCompare(bName);
            });
            $.each(rows, function(index, row) {
                tbody.append(row);
            });
        }

        function filterHiddenModalTable() {
            var query = $('#searchHiddenModalItem').val().toLowerCase().trim();
            var dimFilter = $('#filterDimensionModalItem').val();

            $('#tableHiddenItems tbody tr.hidden-item-row').each(function() {
                var searchData = $(this).data('search') || '';
                var hasDim = $(this).attr('data-has-dimension');
                var hasNg = $(this).attr('data-has-ng');

                var matchSearch = searchData.indexOf(query) !== -1;
                var matchDim = true;
                if (dimFilter === "1" || dimFilter === "0") {
                    matchDim = (dimFilter === hasDim);
                } else if (dimFilter === "ng") {
                    matchDim = (hasNg === "1");
                }

                if (matchSearch && matchDim) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        $('#searchHiddenModalItem').on('input', filterHiddenModalTable);
        $('#filterDimensionModalItem').on('change', filterHiddenModalTable);

        function updateHiddenCount() {
            var cnt = $('.chk-hidden-item:checked').length;
            $('#countSelectedHidden strong').text(cnt);
        }

        var hiddenItemsLoaded = false;
        $('#modalHiddenItems, #modalManageHiddenItems').on('show.bs.modal', function () {
            if (!hiddenItemsLoaded) {
                $('#tbodyHiddenItems').html('<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2 text-primary"></i> Memuat data item...</td></tr>');
                fetch(window.inProcessConfig.routeHiddenItemsData)
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.success) {
                            $('#tbodyHiddenItems').html(data.html);
                            hiddenItemsLoaded = true;
                            updateHiddenCount();
                            sortHiddenTableRows();
                        }
                    })
                    .catch(function(err) {
                        console.error('Error loading hidden items data:', err);
                        $('#tbodyHiddenItems').html('<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data item. Silakan coba lagi.</td></tr>');
                    });
            }
        });

        $(document).on('change', '.chk-hidden-item', function() {
            updateHiddenCount();
            sortHiddenTableRows();
        });

        $('#btnSelectAllHidden').on('click', function() {
            $('#tableHiddenItems tbody tr.hidden-item-row:visible .chk-hidden-item').prop('checked', true);
            updateHiddenCount();
            sortHiddenTableRows();
        });

        $('#btnUnselectAllHidden').on('click', function() {
            $('#tableHiddenItems tbody tr.hidden-item-row:visible .chk-hidden-item').prop('checked', false);
            updateHiddenCount();
            sortHiddenTableRows();
        });

        $('#btnSelectNoDimensionHidden').on('click', function() {
            $('#switchHideNoDimensionRows').prop('checked', true);
            var countChecked = 0;
            $('#tableHiddenItems tbody tr.hidden-item-row[data-has-dimension="0"]').each(function() {
                var chk = $(this).find('.chk-hidden-item');
                if (!chk.is(':checked')) {
                    chk.prop('checked', true);
                    countChecked++;
                }
            });
            updateHiddenCount();
            sortHiddenTableRows();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: countChecked > 0 ? (countChecked + ' item Visual Only (Tanpa Dimensi) & opsi sembunyikan baris Visual Only diaktifkan') : 'Semua item Visual Only sudah dicentang & opsi sembunyikan baris Visual Only diaktifkan',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });

        $('#btnSelectNgHidden').on('click', function() {
            $('#switchHideNgRows').prop('checked', true);
            var countChecked = 0;
            $('#tableHiddenItems tbody tr.hidden-item-row[data-has-ng="1"]').each(function() {
                var chk = $(this).find('.chk-hidden-item');
                if (!chk.is(':checked')) {
                    chk.prop('checked', true);
                    countChecked++;
                }
            });
            updateHiddenCount();
            sortHiddenTableRows();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: countChecked > 0 ? (countChecked + ' item NG Dimensi & opsi sembunyikan baris NG Dimensi diaktifkan') : 'Semua item NG Dimensi sudah dicentang & opsi sembunyikan baris NG Dimensi diaktifkan',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });

        $('#modalHiddenItems').on('shown.bs.modal', function() {
            sortHiddenTableRows();
        });
    }

    // 8. Modal Detail Alat Ukur Per Point (Single Reusable Dynamic Modal)
    $(document).on('click', '.btn-detail-alat-ukur', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var tools = $btn.data('tools');
        var itemName = $btn.data('item') || '-';
        var partNumber = $btn.data('part-number') || '-';

        if (typeof tools === 'string') {
            try {
                tools = JSON.parse(tools);
            } catch (err) {
                tools = {};
            }
        }

        var subtitleText = itemName + ' (' + partNumber + ')';
        $('#alatUkurModalSubtitle').text(subtitleText);

        var $tbody = $('#alatUkurModalTableBody');
        $tbody.empty();

        if (!tools || typeof tools !== 'object' || Object.keys(tools).length === 0) {
            $tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data alat ukur spesifik per point.</td></tr>');
        } else {
            var sortedPoints = Object.keys(tools).sort(function (a, b) {
                return parseInt(a, 10) - parseInt(b, 10);
            });

            var rowsHtml = '';
            sortedPoints.forEach(function (pt) {
                var toolName = tools[pt];
                if (toolName && String(toolName).trim() !== '') {
                    rowsHtml += '<tr>' +
                        '<td class="align-middle text-center font-weight-bold" style="width: 35%; color: #334155; font-size: 0.68rem; padding: 5px 8px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9;">' +
                            'Ø' + pt +
                        '</td>' +
                        '<td class="align-middle text-left font-weight-bold" style="width: 65%; color: #1e293b; font-size: 0.68rem; padding: 5px 8px; border-bottom: 1px solid #f1f5f9;">' +
                            toolName +
                        '</td>' +
                    '</tr>';
                }
            });

            if (rowsHtml === '') {
                rowsHtml = '<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data alat ukur yang valid.</td></tr>';
            }
            $tbody.html(rowsHtml);
        }

        $('#modalDetailAlatUkur').modal('show');
    });
});

