$(document).ready(function() {
        var table = $('#dataTable').DataTable({
            deferRender: true,
            processing: true,
            initComplete: function(settings, json) {
                $('#tableLoader').hide();
                $('#tableContainer').fadeIn('fast', function() {
                    table.columns.adjust();
                });
            }
        });

        // DataTables draw event for Auto-Highlight
        table.on('draw', function() {
            var tbody = table.table().body();
            
            // Unmark previous
            $(tbody).find('mark.hlt').each(function() {
                $(this).replaceWith(this.childNodes);
            });
            tbody.normalize();

            var searchStr = table.search();
            if (!searchStr) return;

            var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
            if (keywords.length === 0) return;

            keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
            var regex = new RegExp("(" + keywords.join('|') + ")", "gi");

            table.rows({ page: 'current' }).nodes().each(function(row) {
                $(row).find('td:not(:last-child)').each(function() {
                    var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT, null, false);
                    var nodes = [];
                    while (walker.nextNode()) {
                        nodes.push(walker.currentNode);
                    }
                    nodes.forEach(function(node) {
                        var text = node.nodeValue;
                        if (text.trim() && regex.test(text)) {
                            var span = document.createElement('span');
                            span.innerHTML = text.replace(regex, "<mark class='hlt' style='background-color: #fffa90; color: #000000; padding: 0 2px; border-radius: 2px;'>$1</mark>");
                            var frag = document.createDocumentFragment();
                            while (span.firstChild) {
                                frag.appendChild(span.firstChild);
                            }
                            node.parentNode.replaceChild(frag, node);
                        }
                    });
                });
            });
        });

        // Instant smart search on keyup
        $('#search_master').on('keypress', function (e) {
            if (e.which == 13) e.preventDefault();
        });

        $('#search_master').on('keyup input', function () {
            table.search($(this).val()).draw();
        });

        // Search Button Fallback
        $('#btnFilterSearchMaster').on('click', function () {
            table.search($('#search_master').val()).draw();
        });

        // Client-side DataTables Reset
        $('#btnResetFilterMaster').on('click', function () {
            $('#search_master').val('');
            table.search('').draw();
        });
        // Thickness Modal
        $('#dataTable').on('click', '.btn-thickness', function() {
            var item = $(this).data('item');
            var cr = (item && item.thickness_cr !== null && item.thickness_cr !== undefined && item.thickness_cr !== '') ? item.thickness_cr : ($(this).attr('data-cr') || $(this).data('cr') || '-');
            var ni = (item && item.thickness_ni !== null && item.thickness_ni !== undefined && item.thickness_ni !== '') ? item.thickness_ni : ($(this).attr('data-ni') || $(this).data('ni') || '-');
            var cu = (item && item.thickness_cu !== null && item.thickness_cu !== undefined && item.thickness_cu !== '') ? item.thickness_cu : ($(this).attr('data-cu') || $(this).data('cu') || '-');

            var name = (item && item.part_name) ? item.part_name : ($(this).data('name') || '');
            var id = (item && item.id) ? item.id : $(this).data('id');

            $('#thickness_test_id').val(id);
            $('#thickness_part_name').val(name);

            $('#thickness_std_cr_display, #thickness_std_cr_display_2, #thickness_std_cr_display_single').text(cr);
            $('#thickness_std_ni_display, #thickness_std_ni_display_2, #thickness_std_ni_display_single').text(ni);
            $('#thickness_std_cu_display, #thickness_std_cu_display_2, #thickness_std_cu_display_single').text(cu);

            $('#modalThickness').modal('show');
        });

        $('#dataTable').on('click', '.btn-edit', function() {
            var item = $(this).data('item');
            var url = window.durabilityConfig.updateUrl;
            url = url.replace(':id', item.id);
            $('#formEdit').attr('action', url);
            
            $('#edit_part_name').val(item.part_name);
            $('#edit_customer_name').val(item.customer_name);
            $('#edit_customer_standard').val(item.customer_standard);
            
            $('#edit_thickness_cu').val(item.thickness_cu);
            $('#edit_thickness_ni').val(item.thickness_ni);
            $('#edit_thickness_cr').val(item.thickness_cr);
            $('#edit_thickness_freq').val(item.thickness_freq);
            
            $('#edit_corrodkote_time').val(item.corrodkote_time);
            $('#edit_corrodkote_std_max_corrosion').val(item.corrodkote_std_max_corrosion);
            $('#edit_corrodkote_freq').val(item.corrodkote_freq);
            
            $('#edit_cass_time').val(item.cass_time);
            $('#edit_cass_std_min_rn').val(item.cass_std_min_rn);
            $('#edit_cass_freq').val(item.cass_freq);
            
            $('#edit_salt_spray_time').val(item.salt_spray_time);
            $('#edit_salt_spray_std_rusting').val(item.salt_spray_std_rusting);
            $('#edit_salt_spray_freq').val(item.salt_spray_freq);
            
            $('#edit_porecount_std_min').val(item.porecount_std_min);
            $('#edit_porecount_freq').val(item.porecount_freq);

            $('#modalEdit').modal('show');
        });

        $('#dataTable').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
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