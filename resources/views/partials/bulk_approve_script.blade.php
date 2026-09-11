{{-- Bulk Approve JavaScript - Include in @push('scripts') --}}
{{-- Requires: $bulkApproveRoute variable to be set before including --}}
@php
    $userRole = auth()->user()->role ?? '';
    $approvalRoles = ['admin', 'kashift', 'kashift_qc', 'karu_qc', 'kashift_plating', 'supervisor', 'supervisor_qc', 'supervisor_plating', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_plating'];
    $canBulkApproveScript = in_array($userRole, $approvalRoles) || \App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all');
@endphp
@if($canBulkApproveScript)
    <script>
        $(document).ready(function () {
            $(document).off('click', '#btnBulkApprove').on('click', '#btnBulkApprove', function (e) {
                e.preventDefault();
                var startDate = '{{ request("start_date") }}';
                var endDate = '{{ request("end_date", request("start_date")) }}';
                var startTglDatang = '{{ request("start_tgl_datang", "") }}';
                var endTglDatang = '{{ request("end_tgl_datang", "") }}';
                var plant = '{{ request("plant", "") }}';
                var resultJudgment = '{{ request("result_judgment", request("judgment", "")) }}';
                var search = '{{ request("search", "") }}';
                var customerName = '{{ request("customer_name", request("customer", "")) }}';
                var supplier = '{{ request("supplier", "") }}';
                var approvalStatus = '{{ request("approval_status", "") }}';
                var category = '{{ request("category", "") }}';
                var shift = '{{ request("shift", "") }}';
                var operatorInitials = '{{ request("operator_initials", "") }}';
                var itemId = '{{ request("item_id", "") }}';
                var userRole = '{{ auth()->user()->role }}';

                var approvalType = userRole;
                // Admin needs to pick a type
                if (userRole === 'admin') {
                    Swal.fire({
                        title: 'Pilih Level Approval',
                        input: 'select',
                        inputOptions: {
                            'karu_qc': 'Karu QC / Kashift QC',
                            'kashift': 'Kashift / Kepala Regu',
                            'kashift_plating': 'Kashift Plating',
                            'supervisor': 'Supervisor Quality',
                            'supervisor_plating': 'Supervisor Plating',
                            'asst_manager': 'Asst Manager QC',
                            'asst_manager_plating': 'Asst Manager Plating',
                            'manager': 'Manager QC',
                            'manager_plating': 'Manager Plating'
                        },
                        inputPlaceholder: 'Pilih level...',
                        showCancelButton: true,
                        confirmButtonText: 'Lanjut',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#1cc88a',
                        inputValidator: (value) => {
                            if (!value) return 'Anda harus memilih level approval!';
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doBulkApprove(startDate, endDate, startTglDatang, endTglDatang, plant, result.value, resultJudgment, search, customerName, supplier, approvalStatus, category, shift, operatorInitials, itemId);
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Konfirmasi Bulk Approve',
                        html: '<div class="text-left">' +
                            '<p>Anda akan meng-approve <strong>semua</strong> data yang memenuhi filter berikut:</p>' +
                            '<ul>' +
                            (startDate ? '<li><strong>Dari Tanggal:</strong> ' + startDate + '</li>' : '') +
                            (endDate ? '<li><strong>Sampai Tanggal:</strong> ' + endDate + '</li>' : '') +
                            (startTglDatang ? '<li><strong>Tgl Datang Dari:</strong> ' + startTglDatang + '</li>' : '') +
                            (endTglDatang ? '<li><strong>Tgl Datang Sampai:</strong> ' + endTglDatang + '</li>' : '') +
                            (supplier ? '<li><strong>Supplier:</strong> ' + supplier + '</li>' : '') +
                            (shift ? '<li><strong>Shift:</strong> Shift ' + shift + '</li>' : '') +
                            (operatorInitials ? '<li><strong>Inisial:</strong> ' + operatorInitials + '</li>' : '') +
                            (customerName ? '<li><strong>Customer:</strong> ' + customerName + '</li>' : '') +
                            (resultJudgment ? '<li><strong>Result:</strong> ' + resultJudgment + '</li>' : '') +
                            (search ? '<li><strong>Search:</strong> ' + search + '</li>' : '') +
                            (plant ? '<li><strong>Plant:</strong> ' + plant + '</li>' : '') +
                            '</ul>' +
                            '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Aksi ini tidak dapat dibatalkan!</p>' +
                            '</div>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-check-double"></i> Ya, Approve Semua',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#1cc88a',
                        cancelButtonColor: '#858796',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doBulkApprove(startDate, endDate, startTglDatang, endTglDatang, plant, approvalType, resultJudgment, search, customerName, supplier, approvalStatus, category, shift, operatorInitials, itemId);
                        }
                    });
                }
            });

            function doBulkApprove(startDate, endDate, startTglDatang, endTglDatang, plant, approvalType, resultJudgment, search, customerName, supplier, approvalStatus, category, shift, operatorInitials, itemId) {
                // Show modal loading while fetching IDs
                Swal.fire({
                    title: 'Menyiapkan Data...',
                    html: '<div class="mb-2" style="font-size:0.9rem; color:#555;">Mengambil daftar data yang perlu di-approve...</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                var baseData = {
                    _token: '{{ csrf_token() }}',
                    start_date: startDate,
                    end_date: endDate,
                    start_tgl_datang: startTglDatang,
                    end_tgl_datang: endTglDatang,
                    plant: plant,
                    approval_type: approvalType,
                    result_judgment: resultJudgment || '',
                    judgment: resultJudgment || '',
                    search: search || '',
                    customer_name: customerName || '',
                    customer: customerName || '',
                    supplier: supplier || '',
                    approval_status: approvalStatus || '',
                    category: category || '',
                    shift: shift || '',
                    operator_initials: operatorInitials || '',
                    item_id: itemId || '',
                    test_type: '{{ $testType ?? "thickness" }}',
                    is_trial: '{{ isset($isTrial) && $isTrial ? 1 : 0 }}'
                };

                // Step 1: Fetch pending IDs
                $.ajax({
                    url: '{{ $bulkApproveRoute }}',
                    type: 'POST',
                    data: $.extend({}, baseData, { get_ids: 1 }),
                    success: function (res) {
                        if (!res.success || !res.ids || res.ids.length === 0) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Informasi',
                                text: 'Tidak ada data yang perlu di-approve.',
                                confirmButtonColor: '#1cc88a'
                            });
                            return;
                        }

                        var allIds = res.ids;
                        var totalCount = allIds.length;
                        var batchSize = 25;
                        var batches = [];

                        for (var i = 0; i < allIds.length; i += batchSize) {
                            batches.push(allIds.slice(i, i + batchSize));
                        }

                        // Show Real-time Progress Bar Modal
                        Swal.fire({
                            title: 'Memproses Batch Approval...',
                            html: `
                                <div id="bulk-approve-status-label" class="mb-2" style="font-size:0.88rem; color:#555; font-weight:600;">
                                    Memproses 0 / ${totalCount} data (0%)
                                </div>
                                <div class="progress" style="height:22px; border-radius:11px; background:#e9ecef;">
                                    <div id="bulk-approve-progress-bar"
                                         class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         style="width:0%; background: linear-gradient(90deg,#1cc88a,#17a673); border-radius:11px; transition:width 0.2s ease; font-size:0.8rem; font-weight:700;">
                                        0%
                                    </div>
                                </div>`,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false
                        });

                        var currentBatchIndex = 0;
                        var processedCount = 0;

                        function processNextBatch() {
                            if (currentBatchIndex >= batches.length) {
                                // All batches completed
                                const bar = document.getElementById('bulk-approve-progress-bar');
                                const label = document.getElementById('bulk-approve-status-label');
                                if (bar) { bar.style.width = '100%'; bar.textContent = '100%'; }
                                if (label) { label.textContent = `Memproses ${totalCount} / ${totalCount} data (100%)`; }

                                setTimeout(function () {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: `Berhasil meng-approve ${totalCount} data laporan.`,
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#1cc88a'
                                    }).then(() => {
                                        location.reload();
                                    });
                                }, 300);
                                return;
                            }

                            var batchIds = batches[currentBatchIndex];

                            $.ajax({
                                url: '{{ $bulkApproveRoute }}',
                                type: 'POST',
                                data: $.extend({}, baseData, { ids: batchIds }),
                                success: function (batchRes) {
                                    currentBatchIndex++;
                                    processedCount += batchIds.length;
                                    var percent = Math.min(100, Math.round((processedCount / totalCount) * 100));

                                    const bar = document.getElementById('bulk-approve-progress-bar');
                                    const label = document.getElementById('bulk-approve-status-label');
                                    if (bar) {
                                        bar.style.width = percent + '%';
                                        bar.textContent = percent + '%';
                                    }
                                    if (label) {
                                        label.textContent = `Memproses ${processedCount} / ${totalCount} data (${percent}%)`;
                                    }

                                    // Process next batch
                                    processNextBatch();
                                },
                                error: function (xhr) {
                                    var msg = 'Terjadi kesalahan saat memproses batch data.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        msg = xhr.responseJSON.message;
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: `${msg} (${processedCount} dari ${totalCount} data berhasil di-approve).`,
                                        confirmButtonColor: '#e74a3b'
                                    }).then(() => {
                                        if (processedCount > 0) {
                                            location.reload();
                                        }
                                    });
                                }
                            });
                        }

                        // Start batch loop
                        processNextBatch();
                    },
                    error: function (xhr) {
                        var msg = 'Gagal mengambil data untuk approval.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: msg,
                            confirmButtonColor: '#e74a3b'
                        });
                    }
                });
            }
        });
    </script>
@endif

