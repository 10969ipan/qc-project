/**
 * Logika Pembuatan Checksheet Incoming
 * Digunakan untuk Parts, Sub-Parts, Chemicals, Materials, dan Exports
 */

const AQL_TABLE = {
    getSampleSize: function (lotSize) {
        if (lotSize >= 500001) return 1250;
        if (lotSize >= 150001) return 800;
        if (lotSize >= 35001) return 500;
        if (lotSize >= 10001) return 315;
        if (lotSize >= 3201) return 200;
        if (lotSize >= 1201) return 125;
        if (lotSize >= 501) return 80;
        if (lotSize >= 281) return 50;
        if (lotSize >= 151) return 32;
        if (lotSize >= 20) return 20;
        return lotSize; // 100% Check for lots < 20
    },
    getAqlLimits: function (sampleSize) {
        if (sampleSize >= 1250) return { acc: 14, rej: 15 };
        if (sampleSize >= 800) return { acc: 10, rej: 11 };
        if (sampleSize >= 500) return { acc: 7, rej: 8 };
        if (sampleSize >= 315) return { acc: 5, rej: 6 };
        if (sampleSize >= 200) return { acc: 3, rej: 4 };
        if (sampleSize >= 125) return { acc: 2, rej: 3 };
        if (sampleSize >= 80) return { acc: 1, rej: 2 };
        if (sampleSize >= 50) return { acc: 1, rej: 2 };
        if (sampleSize >= 32) return { acc: 0, rej: 1 };
        if (sampleSize >= 20) return { acc: 0, rej: 1 };
        return { acc: 0, rej: 1 }; // Default for smaller samples
    }
};

function normalizePartNumber(pn) {
    if (!pn) return '';
    return pn.toString()
        .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
        .replace(/\s+/g, '')
        .toUpperCase();
}

document.addEventListener('DOMContentLoaded', function () {
    // Tangani Pemilihan Item untuk memperbarui Dropdown Defect
    $('#itemSelect').on('change', function () {
        const selected = $(this).find(':selected');
        const defects = selected.data('defects');
        updateDefectOptions(defects);
    });

    function updateDefectOptions(defects) {
        const defectSelects = $('.defect-select');
        defectSelects.each(function () {
            const currentVal = $(this).val();
            // Coba cocokkan "-- Pilih Defect --" atau "-- Defect --"
            const firstOption = $(this).find('option').first().text() || '-- Pilih Defect --';
            $(this).empty().append(`<option value="">${firstOption}</option>`);

            if (defects) {
                const defectList = Array.isArray(defects) ? defects : JSON.parse(defects || '[]');
                defectList.forEach(defect => {
                    const val = typeof defect === 'string' ? defect : (defect.name || defect.type);
                    $(this).append(`<option value="${val}">${val}</option>`);
                });
            }
            $(this).val(currentVal);
        });
    }

    // Tambah Baris Defect
    $('#addDefectBtn').on('click', function () {
        const container = $('#defectContainer');
        const firstRow = container.find('.defect-row').first().clone();
        firstRow.find('input').val('');
        firstRow.find('select').val('');
        
        if (firstRow.find('.remove-defect-btn').length === 0) {
            firstRow.append('<div class="input-group-append"><button type="button" class="btn btn-outline-danger btn-sm remove-defect-btn"><i class="fas fa-trash"></i></button></div>');
        }
        
        container.append(firstRow);
    });

    $(document).on('click', '.remove-defect-btn', function() {
        if ($('.defect-row').length > 1) {
            $(this).closest('.defect-row').remove();
            calculateAndJudge();
        }
    });

    // Hitung Ukuran Sampel dari Kuantitas Lot
    $('#lotQtyInput').on('input', function() {
        const lotSize = parseInt($(this).val()) || 0;
        const sampleSize = AQL_TABLE.getSampleSize(lotSize);
        $('#totalCheckInput').val(sampleSize).trigger('input');
    });

    // Hitung Total NG dan Atur Judgment Otomatis
    $(document).on('input', '.defect-qty, .defect-select, #totalCheckInput', function () {
        calculateAndJudge();
    });

    function calculateAndJudge() {
        let totalNg = 0;
        $('.defect-row').each(function () {
            const qtyInput = $(this).find('.defect-qty');
            const select = $(this).find('.defect-select');
            const qty = parseInt(qtyInput.val()) || 0;
            if (select.val()) {
                totalNg += qty;
            }
        });
        
        const totalCheck = parseInt($('#totalCheckInput').val()) || 0;
        const aql = AQL_TABLE.getAqlLimits(totalCheck);
        
        $('#totalNgInput').val(totalNg);
        
        const judgment = (totalNg >= aql.rej) ? 'NG' : 'OK';
        $('#judgmentSelect').val(judgment);
        
        // Tampilkan Info AQL
        if (!$('#aqlInfoBox').length) {
            $('#judgmentSelect').after('<div id="aqlInfoBox" class="mt-2 small text-muted text-center" style="font-size: 0.7rem;"></div>');
        }
        
        if (totalCheck > 0) {
            $('#aqlInfoBox').html(`Standard: AQL 0.65<br>Acc: ${aql.acc} | Rej: ${aql.rej}`);
        } else {
            $('#aqlInfoBox').empty();
        }
    }

    // Pengiriman Formulir AJAX
    $('#checksheetForm').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const saveBtn = form.find('button[type="submit"]');
        const originalHtml = saveBtn.html();

        // Bersihkan defect yang dipilih tapi tidak ada qty atau qty = 0
        $('.defect-row').each(function() {
            const typeInput = $(this).find('.defect-select');
            const qtyInput = $(this).find('.defect-qty');
            const type = typeInput.val();
            const qty = parseInt(qtyInput.val()) || 0;
            
            if (type && qty === 0) {
                typeInput.val('');
                qtyInput.val('');
            }
        });

        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                if (typeof $('#global-loader').hide === 'function') {
                    $('#global-loader').hide();
                }

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Data Berhasil Disimpan',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Lihat Data',
                        cancelButtonText: 'Tutup',
                        reverseButtons: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = response.index_url;
                        } else {
                            resetState(form);
                        }
                    });
                }
            },
            error: function (xhr) {
                if (typeof $('#global-loader').hide === 'function') {
                    $('#global-loader').hide();
                }

                let errorMsg = 'Gagal menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
                saveBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    function resetState(form) {
        form[0].reset();
        $('#itemSelect').val('').trigger('change');
        $('#defectContainer').find('.defect-row').not(':first').remove();
        $('#totalNgInput').val(0);
        $('#judgmentSelect').val('OK');
        $('#aqlInfoBox').empty();
        
        // Khusus untuk Select2 jika digunakan
        if ($.fn.select2) {
            form.find('.select2').trigger('change');
        }
    }
});
