$(document).ready(function () {
    const config = window.__CUSTOMER_CLAIM_RECORDS__ || {};

    function updateEvaluasiDate(inputSelector, targetSelector) {
        const dateVal = $(inputSelector).val();
        if (dateVal) {
            const date = new Date(dateVal);
            date.setMonth(date.getMonth() + 6);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            $(targetSelector).val(`${day}-${month}-${year}`);
        }
    }

    $('#tambah_tanggal_claim').on('change', function () {
        updateEvaluasiDate('#tambah_tanggal_claim', '#tambah_evaluasi');
    });

    $('#edit_tanggal_claim').on('change', function () {
        updateEvaluasiDate('#edit_tanggal_claim', '#edit_evaluasi');
    });

    $('.customer-select').on('change', function () {
        const select = $(this);
        const manual = select.siblings('.customer-manual');
        if (select.val() === 'OTHER') {
            manual.removeClass('d-none').val('').focus().attr('required', true);
        } else {
            manual.addClass('d-none').val(select.val()).attr('required', false);
        }
    });

    $(document).on('click', '.btn-preview-file', function () {
        const url = $(this).data('url');
        const filename = $(this).data('name');
        const extension = filename.split('.').pop().toLowerCase();
        const previewBody = $('#file_preview_body');

        $('#previewModalLabel').text('Preview: ' + filename);
        $('#btn-download-full').attr('href', url);
        previewBody.html('<div class="text-center p-5 text-white"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');

        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            previewBody.html(`<img src="${url}" class="img-fluid d-block mx-auto shadow">`);
        } else if (extension === 'pdf') {
            previewBody.html(`<iframe src="${url}" width="100%" height="700px" style="border: none;"></iframe>`);
        } else {
            previewBody.html(`
                <div class="text-center p-5 text-white">
                    <i class="fas fa-file-alt fa-4x mb-3 text-light"></i>
                    <h5>Preview tidak tersedia</h5>
                    <p>Format file <strong>.${extension}</strong> tidak dapat dipratinjau secara langsung.</p>
                    <a href="${url}" download class="btn btn-primary btn-sm mt-2 px-4">
                        <i class="fas fa-download mr-1"></i> Download untuk melihat
                    </a>
                </div>
            `);
        }
        $('#previewModal').modal('show');
    });

    $('.btn-edit-record').click(function () {
        const data = $(this).data('json');
        const id = $(this).data('id');
        const form = $('#formEditRecord');
        
        if (config.baseUrl) {
            form.attr('action', config.baseUrl + '/' + id);
        }

        form.find('[name="tanggal_claim"]').val(data.tanggal_claim ? data.tanggal_claim.split('T')[0] : '');

        const customerVal = data.customer || '';
        const selectElement = $('#edit_customer_select');
        const manualElement = $('#edit_customer_manual');
        
        if (selectElement.find('option[value="' + customerVal + '"]').length > 0) {
            selectElement.val(customerVal).trigger('change');
        } else {
            selectElement.val('OTHER').trigger('change');
            manualElement.val(customerVal);
        }
        
        form.find('[name="plant_up_customer"]').val(data.plant_up_customer);
        form.find('[name="claim_type"]').val(data.claim_type);
        form.find('[name="no_report"]').val(data.no_report);
        form.find('[name="source_type"]').val(data.source_type);
        form.find('[name="project"]').val(data.project);
        form.find('[name="plant_id"]').val(data.plant_id);
        form.find('[name="nama_part"]').val(data.nama_part);
        form.find('[name="problem"]').val(data.problem);
        form.find('[name="kategori_defect"]').val(data.kategori_defect);
        form.find('[name="kategori_penyimpangan"]').val(data.kategori_penyimpangan);
        form.find('[name="qty"]').val(data.qty);
        form.find('[name="initial_operator"]').val(data.initial_operator);
        form.find('[name="initial_inspektor"]').val(data.initial_inspektor);
        form.find('[name="action_taken"]').val(data.action_taken);
        form.find('[name="total_akomodasi"]').val(data.total_akomodasi);
        form.find('[name="total_overtime"]').val(data.total_overtime);
        form.find('[name="feedback"]').val(data.feedback);
        form.find('[name="status_feedback"]').val(data.status_feedback);
        form.find('[name="status_cm"]').val(data.status_cm);
        form.find('[name="monitoring"]').val(data.monitoring);
        form.find('[name="evaluasi"]').val(data.evaluasi);
        form.find('[name="monitoring_status"]').val(data.monitoring_status);

        const attachmentList = $('#edit_attachments_list');
        attachmentList.empty();

        if (data.attachments && Array.isArray(data.attachments) && data.attachments.length > 0) {
            data.attachments.forEach((path, index) => {
                const fileName = path.split('/').pop().replace(/^\d+_/, '');
                attachmentList.append(`
                    <div class="d-flex align-items-center justify-content-between p-2 mb-1 bg-white border rounded small">
                        <span class="text-truncate mr-2" title="${fileName}">${fileName}</span>
                        <div class="btn-group">
                            <a href="/storage/${path}" target="_blank" class="btn btn-info btn-xs" title="View">
                                <i class="fas fa-eye fa-xs"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-xs btn-delete-attachment" 
                                    data-id="${id}" data-index="${index}" title="Delete">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </div>
                    </div>
                `);
            });
        } else {
            attachmentList.append('<div class="text-muted small italic">No files uploaded</div>');
        }
    });

    $(document).on('click', '.btn-delete-attachment', function (e) {
        e.preventDefault();
        const btn = $(this);
        const id = btn.data('id');
        const index = btn.data('index');

        Swal.fire({
            title: 'Hapus file?',
            text: "File akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                if (config.deleteAttachmentUrl) {
                    let url = config.deleteAttachmentUrl.replace(':id', id).replace(':index', index);
                    
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            plant: config.plant
                        },
                        success: function (response) {
                            if (response.success) {
                                btn.closest('.d-flex').fadeOut(300, function () {
                                    $(this).remove();
                                    if ($('#edit_attachments_list').children().length === 0) {
                                        $('#edit_attachments_list').append('<div class="text-muted small italic">No files uploaded</div>');
                                    }
                                });
                                Swal.fire('Terhapus!', 'File telah dihapus.', 'success');
                            }
                        },
                        error: function (xhr) {
                            let message = 'Terjadi kesalahan saat menghapus file.';
                            if (xhr.status === 404) message = 'File atau record tidak ditemukan.';
                            else if (xhr.status === 403) message = 'Anda tidak memiliki akses untuk menghapus file ini.';
                            Swal.fire('Gagal!', message, 'error');
                        }
                    });
                }
            }
        });
    });

    $('.btn-delete-record').click(function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        Swal.fire({
            title: 'Hapus data claim?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
