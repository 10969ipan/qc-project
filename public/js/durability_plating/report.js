$(document).ready(function() {
    if (typeof initItemSearch === 'function') {
        initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...' });
    }

    const config = window.__DURABILITY_PLATING_REPORT__ || {};

    $('.btn-edit-thickness').click(function() {
        let item = $(this).data('item');
        let partName = $(this).data('part');
        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        
        $('#formEditThickness').attr('action', url);
        
        $('#edit_thickness_part_name').val(partName);
        $('#edit_production_date').val(item.production_date);
        $('#edit_shift').val(item.shift);
        $('#edit_lot_no').val(item.lot_no);
        $('#edit_actual_cu').val(item.actual_cu);
        $('#edit_actual_ni').val(item.actual_ni);
        $('#edit_actual_cr').val(item.actual_cr);
        
        $('#edit_actual_corrodkote_waktu').val(item.actual_corrodkote_waktu);
        $('#edit_actual_corrodkote').val(item.actual_corrodkote);
        
        $('#edit_actual_cass_waktu').val(item.actual_cass_waktu);
        $('#edit_actual_cass').val(item.actual_cass);
        
        $('#edit_actual_salt_spray_waktu').val(item.actual_salt_spray_waktu);
        $('#edit_actual_salt_spray').val(item.actual_salt_spray);
        
        $('#edit_actual_porecount').val(item.actual_porecount);
        
        $('#edit_result_judgment').val(item.result_judgment ?? '-');
        $('#edit_description').val(item.description);
        
        $('#modalEditThickness').modal('show');
    });

    $('.btn-input-corrodkote').click(function() {
        let item = $(this).data('item');
        
        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputCorrodkote').attr('action', url);
        
        $('#corrodkote_report_id').val(item.id);
        $('#corrodkote_part_name').val($(this).data('part'));
        $('#corrodkote_customer').val($(this).data('customer'));
        $('#corrodkote_std').val($(this).data('std'));
        $('#corrodkote_standard_time').val($(this).data('time'));
        $('#corrodkote_produksi').val(item.production_date);
        $('#corrodkote_shift').val(item.shift);
        $('#corrodkote_lot').val(item.lot_no);
        $('#modalInputCorrodkote').modal('show');
    });

    $('.btn-input-cass').click(function() {
        let item = $(this).data('item');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputCass').attr('action', url);

        $('#cass_report_id').val(item.id);
        $('#cass_part_name').val($(this).data('part'));
        $('#cass_customer').val($(this).data('customer'));
        $('#cass_std').val($(this).data('std'));
        $('#cass_standard_time').val($(this).data('time'));
        $('#cass_produksi').val(item.production_date);
        $('#cass_shift').val(item.shift);
        $('#cass_lot').val(item.lot_no);
        $('#modalInputCass').modal('show');
    });

    $('.btn-input-salt-spray').click(function() {
        let item = $(this).data('item');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputSaltSpray').attr('action', url);

        $('#salt_report_id').val(item.id);
        $('#salt_part_name').val($(this).data('part'));
        $('#salt_customer').val($(this).data('customer'));
        $('#salt_std').val($(this).data('std'));
        $('#salt_standard_time').val($(this).data('time'));
        $('#salt_produksi').val(item.production_date);
        $('#salt_shift').val(item.shift);
        $('#salt_lot').val(item.lot_no);
        $('#modalInputSaltSpray').modal('show');
    });

    $('.btn-input-porecount').click(function() {
        let item = $(this).data('item');

        let url = config.updateUrl;
        url = url.replace(':id', item.id);
        $('#formInputPorecount').attr('action', url);

        $('#porecount_report_id').val(item.id);
        $('#porecount_part_name').val($(this).data('part'));
        $('#porecount_customer').val($(this).data('customer'));
        $('#porecount_std').val($(this).data('std'));
        $('#porecount_standard_min').val($(this).data('stdmin'));
        $('#porecount_produksi').val(item.production_date);
        $('#porecount_shift').val(item.shift);
        $('#porecount_lot').val(item.lot_no);
        $('#modalInputPorecount').modal('show');
    });

    // Delete SweetAlert
    $('.delete-form').submit(function(e) {
        e.preventDefault();
        let form = this;
        Swal.fire({
            title: 'Hapus Laporan?',
            text: "Laporan yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Dynamically set sticky top for thead rows 2 and 3
    function fixStickyHeaderTops() {
        var $rows = $('#dataTable > thead > tr');
        if ($rows.length < 2) return;
        var row1H = $rows.eq(0).outerHeight();
        var row2H = $rows.length > 2 ? $rows.eq(1).outerHeight() : 0;
        $rows.eq(1).find('th').css('top', row1H + 'px');
        if ($rows.length > 2) {
            $rows.eq(2).find('th').css('top', (row1H + row2H) + 'px');
        }
    }
    
    // Hide loader and show container, then calculate sticky headers
    $('#tableLoader').hide();
    $('#tableContainer').fadeIn('fast', function() {
        fixStickyHeaderTops();
    });
    
    $(window).on('resize', fixStickyHeaderTops);
});
