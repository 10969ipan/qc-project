{{-- Bulk Approve JavaScript - Include in @push('scripts') --}}
{{-- Requires: $bulkApproveRoute variable to be set before including --}}
@php
    $hasFilter = request('start_date') || request('end_date') || request('result_judgment') || request('search') || request('customer_name') || request('customer') || request('category') || request('shift') || request('operator_initials') || request('item_id');
@endphp
@if(\App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all') && $hasFilter)
    <script>
        $(document).ready(function () {
            $(document).off('click', '#btnBulkApprove').on('click', '#btnBulkApprove', function (e) {
                e.preventDefault();
                var startDate = '{{ request("start_date") }}';
                var endDate = '{{ request("end_date", request("start_date")) }}';
                var plant = '{{ request("plant", "") }}';
                var resultJudgment = '{{ request("result_judgment", "") }}';
                var search = '{{ request("search", "") }}';
                var customerName = '{{ request("customer_name", request("customer", "")) }}';
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
                            'karu_qc': 'Karu QC',
                            'kashift': 'Kashift',
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
                            doBulkApprove(startDate, endDate, plant, result.value, resultJudgment, search, customerName, category, shift, operatorInitials, itemId);
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Konfirmasi Bulk Approve',
                        html: '<div class="text-left">' +
                            '<p>Anda akan meng-approve <strong>semua</strong> data checksheet yang memenuhi filter berikut:</p>' +
                            '<ul>' +
                            (startDate ? '<li><strong>Dari Tanggal:</strong> ' + startDate + '</li>' : '') +
                            (endDate ? '<li><strong>Sampai Tanggal:</strong> ' + endDate + '</li>' : '') +
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
                            doBulkApprove(startDate, endDate, plant, approvalType, resultJudgment, search, customerName, category, shift, operatorInitials, itemId);
                        }
                    });
                }
            });

            function doBulkApprove(startDate, endDate, plant, approvalType, resultJudgment, search, customerName, category, shift, operatorInitials, itemId) {
                // Show animated progress bar
                let progress = 0;
                Swal.fire({
                    title: 'Memproses...',
                    html: `
                        <div class="mb-2" style="font-size:0.9rem; color:#555;">Sedang meng-approve data checksheet...</div>
                        <div class="progress" style="height:20px; border-radius:10px; background:#e9ecef;">
                            <div id="bulk-approve-progress-bar"
                                 class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar"
                                 style="width:0%; background: linear-gradient(90deg,#1cc88a,#17a673); border-radius:10px; transition:width 0.3s ease; font-size:0.8rem; font-weight:600;">
                                0%
                            </div>
                        </div>`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });

                // Animate progress bar while waiting for AJAX
                const progressInterval = setInterval(function () {
                    if (progress < 85) {
                        progress += Math.random() * 8;
                        if (progress > 85) progress = 85;
                        const bar = document.getElementById('bulk-approve-progress-bar');
                        if (bar) {
                            bar.style.width = progress.toFixed(0) + '%';
                            bar.textContent = progress.toFixed(0) + '%';
                        }
                    }
                }, 300);

                $.ajax({
                    url: '{{ $bulkApproveRoute }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        start_date: startDate,
                        end_date: endDate,
                        plant: plant,
                        approval_type: approvalType,
                        result_judgment: resultJudgment || '',
                        search: search || '',
                        customer_name: customerName || '',
                        customer: customerName || '',
                        category: category || '',
                        shift: shift || '',
                        operator_initials: operatorInitials || '',
                        item_id: itemId || '',
                        test_type: '{{ $testType ?? "thickness" }}',
                        is_trial: '{{ isset($isTrial) && $isTrial ? 1 : 0 }}'
                    },
                    success: function (response) {
                        clearInterval(progressInterval);

                        // Complete the bar to 100% then show success
                        const bar = document.getElementById('bulk-approve-progress-bar');
                        if (bar) {
                            bar.style.width = '100%';
                            bar.textContent = '100%';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#1cc88a'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        clearInterval(progressInterval);
                        var msg = 'Terjadi kesalahan.';
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

