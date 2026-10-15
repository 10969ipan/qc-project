/**
 * Bulk Approve Module - Admin Auto-Sampling Quota & Multi-User Approver Assignment
 * Standards: Separation of Concerns, SweetAlert2, 7 Pilar Blueprint
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        $(document).off('click', '#btnBulkApprove').on('click', '#btnBulkApprove', function (e) {
            e.preventDefault();
            var btn = $(this);
            var cfg = window.bulkApproveConfig || {};
            var defaultItemDate = btn.data('item-date') || cfg.defaultItemDate || '';

            var reqStart = cfg.startDate || '';
            var reqEnd = cfg.endDate || '';
            var domStart = $('#start_date').length ? $('#start_date').val() : '';
            var domEnd = $('#end_date').length ? $('#end_date').val() : '';

            var rawStart = reqStart || domStart || '';
            var rawEnd = reqEnd || domEnd || '';

            var startDate = rawStart || rawEnd || '';
            var endDate = rawEnd || rawStart || '';

            var shift = cfg.shift || '';
            if (shift === '' && $('#filterShift').length) {
                shift = $('#filterShift').val() || '';
            }

            var operatorInitials = cfg.operatorInitials || '';
            if (operatorInitials === '' && $('#filterInisial').length) {
                operatorInitials = $('#filterInisial').val() || '';
            }

            var tujuan = cfg.tujuan || '';
            if (tujuan === '' && $('#filterTujuan').length) {
                tujuan = $('#filterTujuan').val() || '';
            }

            var codeMachine = cfg.codeMachine || '';
            if (codeMachine === '' && $('#filterMachine').length) {
                codeMachine = $('#filterMachine').val() || '';
            }

            var approvalMethod = cfg.approvalMethod || '';
            if (approvalMethod === '' && $('#filterApprovalMethod').length) {
                approvalMethod = $('#filterApprovalMethod').val() || '';
            }

            var startTglDatang = cfg.startTglDatang || ($('#start_tgl_datang').length ? $('#start_tgl_datang').val() : '');
            var endTglDatang = cfg.endTglDatang || ($('#end_tgl_datang').length ? $('#end_tgl_datang').val() : '');
            var plant = cfg.plant || ($('input[name="plant"]').length ? $('input[name="plant"]').val() : ($('#filterPlant').length ? $('#filterPlant').val() : ''));
            var resultJudgment = cfg.resultJudgment || ($('#filterJudgment').length ? $('#filterJudgment').val() : '');
            var search = cfg.search || ($('#filterSearch').length ? $('#filterSearch').val() : '');
            var customerName = cfg.customerName || ($('#filterCustomerName').length ? $('#filterCustomerName').val() : ($('#filterCustomer').length ? $('#filterCustomer').val() : ''));
            var supplier = cfg.supplier || ($('#filterSupplier').length ? $('#filterSupplier').val() : '');
            var approvalStatus = cfg.approvalStatus || ($('#filterApprovalStatus').length ? $('#filterApprovalStatus').val() : '');
            var category = cfg.category || ($('#filterCategory').length ? $('#filterCategory').val() : '');
            var itemId = cfg.itemId || ($('#filterItem').length ? $('#filterItem').val() : '');
            var viewMode = cfg.viewMode || ($('input[name="view_mode"]').length ? $('input[name="view_mode"]').val() : '');
            var userRole = cfg.userRole || 'admin';
            var bulkApproveRoute = cfg.bulkApproveRoute || '';
            var usersByRoleRoute = cfg.usersByRoleRoute || '/approvals/users-by-role';
            var testType = cfg.testType || 'thickness';
            var isTrial = cfg.isTrial ? 1 : 0;

            if (!startDate && defaultItemDate) {
                startDate = defaultItemDate;
                endDate = defaultItemDate;
            }

            var filterContext = {
                startDate: startDate,
                endDate: endDate,
                startTglDatang: startTglDatang,
                endTglDatang: endTglDatang,
                plant: plant,
                resultJudgment: resultJudgment,
                search: search,
                customerName: customerName,
                supplier: supplier,
                approvalStatus: approvalStatus,
                category: category,
                shift: shift,
                operatorInitials: operatorInitials,
                itemId: itemId,
                defaultItemDate: defaultItemDate,
                tujuan: tujuan,
                codeMachine: codeMachine,
                approvalMethod: approvalMethod,
                viewMode: viewMode,
                testType: testType,
                isTrial: isTrial,
                userRole: userRole,
                bulkApproveRoute: bulkApproveRoute,
                csrfToken: cfg.csrfToken || $('meta[name="csrf-token"]').attr('content')
            };

            if (userRole === 'admin') {
                // Step 1: Admin selects level approval
                var levelOptions = {
                    'karu_qc': 'Karu QC / Kashift QC',
                    'kashift': 'Kashift / Kepala Regu',
                    'kashift_plating': 'Kashift Plating',
                    'supervisor': 'Supervisor Quality',
                    'supervisor_plating': 'Supervisor Plating',
                    'asst_manager': 'Asst Manager QC',
                    'asst_manager_plating': 'Asst Manager Plating',
                    'manager': 'Manager QC',
                    'manager_plating': 'Manager Plating'
                };

                Swal.fire({
                    title: 'Pilih Level Approval',
                    input: 'select',
                    inputOptions: levelOptions,
                    inputPlaceholder: 'Pilih level...',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjut <i class="fas fa-arrow-right ml-1"></i>',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#1cc88a',
                    cancelButtonColor: '#858796',
                    inputValidator: function (value) {
                        if (!value) return 'Anda harus memilih level approval!';
                    }
                }).then(function (result) {
                    if (!result.isConfirmed || !result.value) return;

                    var selectedLevel = result.value;
                    var selectedLevelLabel = levelOptions[selectedLevel] || selectedLevel;

                    // Loading indicator while checking users for this role
                    Swal.fire({
                        title: 'Memeriksa Pengguna...',
                        html: '<div class="small text-muted mb-2">Mengambil data approver aktif untuk ' + selectedLevelLabel + '...</div>',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });

                    // Fetch users for this role
                    $.ajax({
                        url: usersByRoleRoute,
                        type: 'GET',
                        data: { role: selectedLevel, plant: plant },
                        success: function (res) {
                            var users = (res && res.users) ? res.users : [];
                            if (users.length > 0) {
                                // Step 2: Show user picker modal if users exist
                                showUserSelectionModal(selectedLevel, selectedLevelLabel, users, filterContext);
                            } else {
                                // No specific user found: Fallback to Administrator
                                showConfirmationModal(selectedLevel, selectedLevelLabel, null, 'Administrator', null, filterContext);
                            }
                        },
                        error: function () {
                            // Fallback to Administrator if lookup fails
                            showConfirmationModal(selectedLevel, selectedLevelLabel, null, 'Administrator', null, filterContext);
                        }
                    });
                });
            } else {
                // Non-Admin: Standard Confirmation
                showConfirmationModal(userRole, userRole, null, null, null, filterContext);
            }
        });

        /**
         * Step 2: Modal to select user / shift assignment for roles with users
         */
        function showUserSelectionModal(selectedLevel, selectedLevelLabel, users, filterContext) {
            var isKaruOrKashift = ['karu_qc', 'kashift', 'kashift_qc', 'kashift_plating'].indexOf(selectedLevel) !== -1;

            var userOptionsHtml = '';
            users.forEach(function (u, idx) {
                var extra = u.initials ? ' (' + u.initials + ')' : '';
                var selected = (idx === 0) ? ' selected' : '';
                userOptionsHtml += '<option value="' + u.id + '"' + selected + '>' + u.name + extra + '</option>';
            });
            userOptionsHtml += '<option value="">-- Administrator (Akun Saya) --</option>';

            var shiftOptionsHtml1 = '<option value="">-- Pilih Approver Shift 1 --</option>' + userOptionsHtml;
            var shiftOptionsHtml2 = '<option value="">-- Pilih Approver Shift 2 --</option>' + userOptionsHtml;
            var shiftOptionsHtml3 = '<option value="">-- Pilih Approver Shift 3 --</option>' + userOptionsHtml;

            var modalHtml = '<div class="text-left" style="font-size: 0.9rem;">' +
                '<p class="mb-2 text-muted">Role <strong>' + selectedLevelLabel + '</strong> memiliki ' + users.length + ' user aktif. Tentukan approver yang akan dicatat:</p>' +
                '<div class="form-group mb-3">' +
                '<label class="small font-weight-bold text-gray-700">Pilih Approver Utama (Semua Data):</label>' +
                '<select id="swalSelectUser" class="form-control form-control-sm border shadow-sm">' +
                userOptionsHtml +
                '</select>' +
                '</div>';

            if (isKaruOrKashift) {
                modalHtml += '<div class="card border-0 bg-light p-2 mb-2 rounded">' +
                    '<div class="custom-control custom-checkbox">' +
                    '<input type="checkbox" class="custom-control-input" id="swalToggleShiftMode">' +
                    '<label class="custom-control-label small font-weight-bold text-primary" for="swalToggleShiftMode">' +
                    '<i class="fas fa-layer-group mr-1"></i> Bedakan Approver per Shift (Shift 1 / 2 / 3)' +
                    '</label>' +
                    '</div>' +
                    '<div id="swalShiftSelectors" class="mt-2" style="display: none;">' +
                    '<div class="form-group mb-2">' +
                    '<label class="small text-muted mb-1 font-weight-bold">Shift 1:</label>' +
                    '<select id="swalShift1" class="form-control form-control-sm border shadow-sm">' + shiftOptionsHtml1 + '</select>' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                    '<label class="small text-muted mb-1 font-weight-bold">Shift 2:</label>' +
                    '<select id="swalShift2" class="form-control form-control-sm border shadow-sm">' + shiftOptionsHtml2 + '</select>' +
                    '</div>' +
                    '<div class="form-group mb-1">' +
                    '<label class="small text-muted mb-1 font-weight-bold">Shift 3:</label>' +
                    '<select id="swalShift3" class="form-control form-control-sm border shadow-sm">' + shiftOptionsHtml3 + '</select>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            }

            modalHtml += '</div>';

            Swal.fire({
                title: 'Pilih Approver',
                html: modalHtml,
                showCancelButton: true,
                confirmButtonText: 'Lanjut ke Konfirmasi <i class="fas fa-arrow-right ml-1"></i>',
                cancelButtonText: 'Kembali',
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#858796',
                didOpen: function () {
                    $('#swalToggleShiftMode').on('change', function () {
                        if ($(this).is(':checked')) {
                            $('#swalShiftSelectors').slideDown(150);
                            $('#swalSelectUser').prop('disabled', true);
                        } else {
                            $('#swalShiftSelectors').slideUp(150);
                            $('#swalSelectUser').prop('disabled', false);
                        }
                    });
                }
            }).then(function (res) {
                if (!res.isConfirmed) return;

                var isShiftMode = $('#swalToggleShiftMode').is(':checked');
                var chosenUserId = null;
                var chosenUserName = 'Administrator';
                var userByShift = null;

                if (isShiftMode) {
                    var s1 = $('#swalShift1').val();
                    var s2 = $('#swalShift2').val();
                    var s3 = $('#swalShift3').val();
                    userByShift = {};
                    if (s1) userByShift['1'] = s1;
                    if (s2) userByShift['2'] = s2;
                    if (s3) userByShift['3'] = s3;
                    chosenUserName = 'Sesuai Shift (Shift 1/2/3)';
                } else {
                    chosenUserId = $('#swalSelectUser').val() || null;
                    if (chosenUserId) {
                        chosenUserName = $('#swalSelectUser option:selected').text();
                    }
                }

                showConfirmationModal(selectedLevel, selectedLevelLabel, chosenUserId, chosenUserName, userByShift, filterContext);
            });
        }

        /**
         * Step 3: Final confirmation modal with simulation preview
         */
        function showConfirmationModal(approvalType, approvalTypeLabel, selectedUserId, selectedUserName, userByShift, filterContext) {
            var isAdmin = filterContext.userRole === 'admin';
            var quotaRuleText = '2 data per shift';
            if (['supervisor', 'supervisor_qc', 'supervisor_plating'].indexOf(approvalType) !== -1) {
                quotaRuleText = '1 data per shift';
            } else if (['asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating'].indexOf(approvalType) !== -1) {
                quotaRuleText = '1 data per hari';
            }

            var approverRowHtml = (isAdmin && selectedUserName)
                ? '<li><i class="fas fa-signature text-success mr-2"></i><strong>Approver Terpilih:</strong> <span class="badge badge-light border text-dark font-weight-bold">' + selectedUserName + '</span></li>'
                : '';

            var infoAlertHtml = '';
            if (isAdmin) {
                infoAlertHtml = '<div class="alert alert-info py-2 px-3 small mb-2" style="border-radius: 8px;">' +
                    '<i class="fas fa-magic mr-1 text-primary"></i> <strong>Sistem Sampling Otomatis:</strong><br>' +
                    'Sistem secara acak memilih data checksheet untuk memenuhi kuota sampling (' + quotaRuleText + ') dan menandai seluruh sisa data sebagai <strong>bulk</strong>.' +
                    '</div>';
            } else {
                infoAlertHtml = '<div class="alert alert-info py-2 px-3 small mb-2" style="border-radius: 8px;">' +
                    '<i class="fas fa-info-circle mr-1"></i> Hanya data yang sesuai dengan filter di atas yang akan diproses.' +
                    '</div>';
            }

            var html = '<div class="text-left" style="font-size: 0.88rem;">' +
                '<p class="mb-2">Anda akan melakukan bulk approval dengan ketentuan filter aktif berikut:</p>' +
                '<ul class="mb-3 list-unstyled border rounded p-2 bg-white shadow-sm" style="font-size: 0.85rem; line-height: 1.6;">' +
                '<li><i class="fas fa-user-tag text-primary mr-2"></i><strong>Level Approval:</strong> ' + (approvalTypeLabel || approvalType) + '</li>' +
                approverRowHtml +
                (filterContext.startDate ? '<li><i class="far fa-calendar-alt text-info mr-2"></i><strong>Rentang Tanggal:</strong> ' + filterContext.startDate + (filterContext.endDate && filterContext.endDate !== filterContext.startDate ? ' s/d ' + filterContext.endDate : '') + '</li>' : '') +
                (filterContext.shift ? '<li><i class="fas fa-clock text-warning mr-2"></i><strong>Shift:</strong> Shift ' + filterContext.shift + '</li>' : '<li><i class="fas fa-clock text-muted mr-2"></i><strong>Shift:</strong> Semua Shift</li>') +
                (filterContext.tujuan ? '<li><i class="fas fa-tags text-secondary mr-2"></i><strong>WIP / FG:</strong> ' + filterContext.tujuan + '</li>' : '') +
                (filterContext.codeMachine ? '<li><i class="fas fa-cogs text-secondary mr-2"></i><strong>Mesin:</strong> Mesin ' + filterContext.codeMachine + '</li>' : '') +
                (filterContext.operatorInitials ? '<li><i class="fas fa-user mr-2"></i><strong>Inisial Operator:</strong> ' + filterContext.operatorInitials + '</li>' : '') +
                (filterContext.customerName ? '<li><i class="fas fa-building mr-2"></i><strong>Customer:</strong> ' + filterContext.customerName + '</li>' : '') +
                (filterContext.supplier ? '<li><i class="fas fa-truck mr-2"></i><strong>Supplier:</strong> ' + filterContext.supplier + '</li>' : '') +
                (filterContext.resultJudgment ? '<li><i class="fas fa-check-circle mr-2"></i><strong>Result:</strong> ' + filterContext.resultJudgment + '</li>' : '') +
                (filterContext.search ? '<li><i class="fas fa-search mr-2"></i><strong>Search:</strong> ' + filterContext.search + '</li>' : '') +
                (filterContext.plant ? '<li><i class="fas fa-map-marker-alt mr-2"></i><strong>Plant:</strong> ' + filterContext.plant + '</li>' : '') +
                '</ul>' +
                infoAlertHtml +
                '<p class="text-danger small mb-0 font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Aksi ini tidak dapat dibatalkan!</p>' +
                '</div>';

            Swal.fire({
                title: 'Konfirmasi Bulk Approve',
                html: html,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check-double mr-1"></i> Ya, Jalankan Approval',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#858796',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    doBulkApprove(approvalType, selectedUserId, userByShift, selectedUserName, filterContext);
                }
            });
        }

        /**
         * Execute bulk approval: Fetch partitioned IDs (sampling vs bulk) and process in chunks
         */
        function doBulkApprove(approvalType, selectedUserId, userByShift, selectedUserName, filterContext) {
            var isAdmin = filterContext.userRole === 'admin';
            var preparingHtml = isAdmin
                ? '<div class="mb-2" style="font-size:0.9rem; color:#555;">Mengalokasikan kuota sampling acak dan daftar bulk approval...</div>'
                : '<div class="mb-2" style="font-size:0.9rem; color:#555;">Mengambil daftar data yang perlu di-approve...</div>';

            Swal.fire({
                title: 'Menyiapkan Data...',
                html: preparingHtml,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            var baseData = {
                _token: filterContext.csrfToken,
                start_date: filterContext.startDate,
                end_date: filterContext.endDate,
                item_date: filterContext.defaultItemDate || '',
                start_tgl_datang: filterContext.startTglDatang,
                end_tgl_datang: filterContext.endTglDatang,
                plant: filterContext.plant,
                approval_type: approvalType,
                result_judgment: filterContext.resultJudgment || '',
                judgment: filterContext.resultJudgment || '',
                search: filterContext.search || '',
                customer_name: filterContext.customerName || '',
                customer: filterContext.customerName || '',
                supplier: filterContext.supplier || '',
                approval_status: filterContext.approvalStatus || '',
                category: filterContext.category || '',
                shift: filterContext.shift || '',
                operator_initials: filterContext.operatorInitials || '',
                item_id: filterContext.itemId || '',
                tujuan: filterContext.tujuan || '',
                code_machine: filterContext.codeMachine || '',
                view_mode: filterContext.viewMode || '',
                approval_method: filterContext.approvalMethod || '',
                test_type: filterContext.testType || 'thickness',
                is_trial: filterContext.isTrial ? 1 : 0,
                selected_user_id: selectedUserId || '',
                user_by_shift: userByShift || null
            };

            // Step 1: Fetch IDs & sampling allocation
            $.ajax({
                url: filterContext.bulkApproveRoute,
                type: 'POST',
                data: $.extend({}, baseData, { get_ids: 1 }),
                success: function (res) {
                    if (!res.success || !res.ids || res.ids.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Informasi',
                            text: res.message || 'Tidak ada data yang perlu di-approve pada filter ini.',
                            confirmButtonColor: '#1cc88a'
                        });
                        return;
                    }

                    var allIds = res.ids;
                    var samplingIds = res.sampling_ids || [];
                    var bulkIds = res.bulk_ids || [];
                    var totalCount = allIds.length;
                    var samplingCount = res.sampling_count || samplingIds.length;
                    var bulkCount = res.bulk_count || bulkIds.length;
                    var batchSize = 25;
                    var batches = [];

                    for (var i = 0; i < allIds.length; i += batchSize) {
                        batches.push(allIds.slice(i, i + batchSize));
                    }

                    // Convert sampling IDs to a Set/Lookup for fast lookup
                    var samplingLookup = {};
                    for (var s = 0; s < samplingIds.length; s++) {
                        samplingLookup[samplingIds[s]] = true;
                    }

                    var initialProgressText = 'Memproses 0 / ' + totalCount + ' data (0%)' +
                        (isAdmin ? ' - [' + samplingCount + ' Sampling, ' + bulkCount + ' Bulk]' : '');

                    // Step 2: Show real-time progress bar modal
                    Swal.fire({
                        title: 'Memproses Batch Approval...',
                        html: '<div id="bulk-approve-status-label" class="mb-2" style="font-size:0.88rem; color:#555; font-weight:600;">' +
                            initialProgressText +
                            '</div>' +
                            '<div class="progress" style="height:22px; border-radius:11px; background:#e9ecef;">' +
                            '<div id="bulk-approve-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%; background: linear-gradient(90deg,#1cc88a,#17a673); border-radius:11px; transition:width 0.2s ease; font-size:0.8rem; font-weight:700;">' +
                            '0%' +
                            '</div>' +
                            '</div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false
                    });

                    var currentBatchIndex = 0;
                    var processedCount = 0;

                    function processNextBatch() {
                        if (currentBatchIndex >= batches.length) {
                            var bar = document.getElementById('bulk-approve-progress-bar');
                            var label = document.getElementById('bulk-approve-status-label');
                            if (bar) { bar.style.width = '100%'; bar.textContent = '100%'; }
                            if (label) { label.textContent = 'Selesai: ' + totalCount + ' / ' + totalCount + ' data (100%)'; }

                            setTimeout(function () {
                                var msg = 'Berhasil meng-approve ' + totalCount + ' data laporan';
                                if (isAdmin) {
                                    msg += ' (' + samplingCount + ' Sampling, ' + bulkCount + ' Bulk)';
                                    if (selectedUserName) {
                                        msg += ' atas nama ' + selectedUserName;
                                    }
                                }
                                msg += '.';

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: msg,
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#1cc88a'
                                }).then(function () {
                                    location.reload();
                                });
                            }, 300);
                            return;
                        }

                        var batch = batches[currentBatchIndex];
                        var batchSampling = [];
                        var batchBulk = [];

                        for (var b = 0; b < batch.length; b++) {
                            var id = batch[b];
                            if (samplingLookup[id]) {
                                batchSampling.push(id);
                            } else {
                                batchBulk.push(id);
                            }
                        }

                        var postData = $.extend({}, baseData, {
                            ids: batch,
                            sampling_ids: batchSampling,
                            bulk_ids: batchBulk
                        });

                        $.ajax({
                            url: filterContext.bulkApproveRoute,
                            type: 'POST',
                            data: postData,
                            success: function (batchRes) {
                                if (batchRes.success) {
                                    processedCount += batch.length;
                                    var pct = Math.min(100, Math.round((processedCount / totalCount) * 100));

                                    var bar = document.getElementById('bulk-approve-progress-bar');
                                    var label = document.getElementById('bulk-approve-status-label');
                                    if (bar) {
                                        bar.style.width = pct + '%';
                                        bar.textContent = pct + '%';
                                    }
                                    if (label) {
                                        var batchLabel = 'Memproses ' + processedCount + ' / ' + totalCount + ' data (' + pct + '%)';
                                        if (isAdmin) {
                                            batchLabel += ' - [' + samplingCount + ' Sampling, ' + bulkCount + ' Bulk]';
                                        }
                                        label.textContent = batchLabel;
                                    }

                                    currentBatchIndex++;
                                    processNextBatch();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: batchRes.message || 'Terjadi kesalahan saat memproses batch ke-' + (currentBatchIndex + 1),
                                        confirmButtonColor: '#e74a3b'
                                    });
                                }
                            },
                            error: function (xhr) {
                                var errText = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Koneksi terputus saat memproses batch approval.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errText,
                                    confirmButtonColor: '#e74a3b'
                                });
                            }
                        });
                    }

                    processNextBatch();
                },
                error: function (xhr) {
                    var errText = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi error saat mengambil data approval.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errText,
                        confirmButtonColor: '#e74a3b'
                    });
                }
            });
        }
    });
})(jQuery);
