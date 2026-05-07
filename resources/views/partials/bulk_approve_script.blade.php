{{-- Bulk Approve JavaScript - Include in @push('scripts') --}}
{{-- Requires: $bulkApproveRoute variable to be set before including --}}
@if(in_array(auth()->user()->role, ['supervisor', 'supervisor_plating', 'asst_manager', 'manager', 'manager_qc', 'manager_plating', 'admin']) && request('start_date'))
    <script>
        $(document).ready(function () {
            $('#btnBulkApprove').on('click', function () {
                var startDate = '{{ request("start_date") }}';
                var endDate = '{{ request("end_date", request("start_date")) }}';
                var plant = '{{ request("plant", "") }}';
                var userRole = '{{ auth()->user()->role }}';

                var approvalType = userRole;
                // Admin needs to pick a type
                if (userRole === 'admin') {
                    Swal.fire({
                        title: 'Pilih Level Approval',
                        input: 'select',
                        inputOptions: {
                            'kashift': 'Kashift',
                            'supervisor': 'Supervisor',
                            'supervisor_plating': 'Supervisor Plating',
                            'asst_manager': 'Asst Manager',
                            'manager': 'Manager',
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
                            doBulkApprove(startDate, endDate, plant, result.value);
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Konfirmasi Bulk Approve',
                        html: '<div class="text-left">' +
                            '<p>Anda akan meng-approve <strong>semua</strong> data checksheet yang memenuhi filter berikut:</p>' +
                            '<ul>' +
                            '<li><strong>Dari Tanggal:</strong> ' + startDate + '</li>' +
                            '<li><strong>Sampai Tanggal:</strong> ' + endDate + '</li>' +
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
                            doBulkApprove(startDate, endDate, plant, approvalType);
                        }
                    });
                }
            });

            function doBulkApprove(startDate, endDate, plant, approvalType) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    html: 'Sedang meng-approve data checksheet...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ $bulkApproveRoute }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        start_date: startDate,
                        end_date: endDate,
                        plant: plant,
                        approval_type: approvalType
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: response.message,
                            confirmButtonColor: '#1cc88a'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
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
