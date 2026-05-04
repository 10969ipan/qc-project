$(document).ready(function() {
    const config = window.__CUSTOMER_CLAIMS__ || {};

    function toggleFields(plantId, modalPrefix) {
        $(`.${modalPrefix}-ppm-fields`).show();
        $(`.${modalPrefix}-total-fields`).hide();
    }

    function calculatePPM(pcs, delivery) {
        let p = isNaN(pcs) ? 0 : pcs;
        let d = isNaN(delivery) ? 0 : delivery;

        if (d > 0) {
            return ((p / d) * 1000000).toFixed(2);
        }
        return '0.00';
    }

    $(document).on('input', '.calc-input-summary, [class*="calc-input-"], .calc-input-edit', function() {
        let month = $(this).data('month');
        let pcsInput, deliveryInput, ppmInput;

        if ($(this).hasClass('calc-input-edit')) {
            pcsInput = $('#edit_total_claim_pcs');
            deliveryInput = $('#edit_total_delivery');
            ppmInput = $('#edit_ppm_value');
        } else if (month === 'summary') {
            pcsInput = $('input[name="total_claim_pcs"]');
            deliveryInput = $('input[name="total_delivery"]');
            ppmInput = $('#ppm_value_summary');
        } else {
            pcsInput = $(`input[name="data[${month}][total_claim_pcs]"]`);
            deliveryInput = $(`input[name="data[${month}][total_delivery]"]`);
            ppmInput = $(`#ppm_value_${month}`);
        }

        let pcsVal = pcsInput.val();
        let deliveryVal = deliveryInput.val();
        
        let pcs = pcsVal === '' ? NaN : parseFloat(pcsVal);
        let delivery = deliveryVal === '' ? NaN : parseFloat(deliveryVal);

        ppmInput.val(calculatePPM(pcs, delivery));
    });

    function updateAllPPM(container) {
        $(container).find('.calc-input-summary, [class*="calc-input-"], .calc-input-edit').trigger('input');
    }

    $('#modalTambahData, #modalEditData').on('shown.bs.modal', function() {
        updateAllPPM(this);
    });

    $('#modal_plant_id').on('change', function() {
        toggleFields($(this).val(), 'modal');
    });

    if ($('#modal_plant_id').val()) {
        toggleFields($('#modal_plant_id').val(), 'modal');
    }

    $('.btn-edit-claim').on('click', function() {
        const id = $(this).data('id');
        const plantId = $(this).data('plant');
        const year = $(this).data('year');
        const month = $(this).data('month');
        const ppm = $(this).data('ppm');
        const target = $(this).data('target-val');
        const total = $(this).data('total');
        const pcs = $(this).data('total-claim-pcs');
        const delivery = $(this).data('total-delivery');

        $('#edit_plant_id').val(plantId);
        $('#edit_year').val(year);
        $('#edit_month').val(month);
        $('#edit_ppm_value').val(ppm);
        $('#edit_target_value').val(target);
        $('#edit_total_claims').val(total);
        $('#edit_total_claim_pcs').val(pcs);
        $('#edit_total_delivery').val(delivery);

        toggleFields(plantId, 'edit');

        if (config.routes && config.routes.update) {
            let url = config.routes.update.replace(':id', id);
            $('#formEditClaim').attr('action', url);
        }
    });

    $('#edit_plant_id').on('change', function() {
        toggleFields($(this).val(), 'edit');
    });
});
