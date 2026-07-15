// Global error logger to capture any JS errors immediately
        window.onerror = function(msg, url, line, col, error) {
            alert("JS Error: " + msg + "\nURL: " + url + "\nLine: " + line);
        };

        // ============================================================
        // GLOBAL FUNCTIONS - defined outside document.ready
        // so they are always accessible from onclick attributes
        // ============================================================

        function kakotoraDeleteRow(url, token) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data kakotora ini akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = token;
                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Old function kept for safety
        function deleteKakotora(id, token, actionUrl) {
            kakotoraDeleteRow(actionUrl, token);
        }

        function addNewProblem(selectId) {
            Swal.fire({
                title: 'Tambah Problem Baru',
                input: 'text',
                customClass: {
                    input: 'no-autoupper'
                },
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Masukkan problem baru...',
                    style: 'text-transform: none;'
                },
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: (name) => {
                    if(!name) {
                        Swal.showValidationMessage('Nama problem tidak boleh kosong!');
                        return false;
                    }
                    return $.ajax({
                        url: window.kakotoraConfig.addProblemUrl,
                        type: 'POST',
                        data: {
                            _token: window.kakotoraConfig.csrfToken,
                            plant: window.kakotoraConfig.plant,
                            name: name
                        }
                    }).then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Gagal menyimpan data');
                        }
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message || error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    var newProblem = result.value.problem;
                    $('#add_problem_select').append(new Option(newProblem, newProblem, false, false));
                    $('#edit_problem_select').append(new Option(newProblem, newProblem, false, false));
                    
                    $('#' + selectId).val(newProblem);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Problem baru telah ditambahkan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        function deleteProblem(selectId) {
            var select = document.getElementById(selectId);
            var name = select.value;
            if (!name) {
                Swal.fire('Peringatan', 'Silakan pilih problem yang akan dihapus terlebih dahulu.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Hapus Problem?',
                text: 'Problem "' + name + '" akan dihapus permanen dari daftar master opsi. (Data kakotora yang sudah terlanjur menggunakan problem ini tidak akan terhapus, namun opsinya hilang dari dropdown)',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: window.kakotoraConfig.deleteProblemUrl,
                        type: 'POST',
                        data: {
                            _token: window.kakotoraConfig.csrfToken,
                            plant: window.kakotoraConfig.plant,
                            name: name
                        }
                    }).then(response => {
                        if (!response.success) {
                            throw new Error('Gagal menghapus data');
                        }
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message || error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove from both selects
                    $("#add_problem_select option[value='" + name + "']").remove();
                    $("#edit_problem_select option[value='" + name + "']").remove();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Problem telah dihapus dari daftar.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        $(document).ready(function () {
            // Prevent Bootstrap modal from stealing focus from SweetAlert
            $.fn.modal.Constructor.prototype._enforceFocus = function() {};

            window.updateHiddenSimilarPart = function(prefix) {
                var container = document.getElementById(prefix + '_similar_part_container');
                var hidden = document.getElementById(prefix + '_similar_part_hidden');
                var inputs = container.querySelectorAll('input[type="text"]');
                var vals = [];
                var count = inputs.length;
                inputs.forEach(function(inp, index) {
                    var val = inp.value.trim();
                    // Bersihkan nomor (1., 2.) jika sudah ada dari load data lama
                    val = val.replace(/^\d+\.\s*/, '');
                    
                    if (count > 1) {
                        vals.push((index + 1) + '. ' + val);
                    } else {
                        vals.push(val);
                    }
                });
                hidden.value = vals.join('\n');
            };

            window.removeSimilarPart = function(btn, prefix) {
                btn.parentElement.remove();
                window.updateHiddenSimilarPart(prefix);
            };

            window.addSimilarPartElement = function(prefix, val) {
                var container = document.getElementById(prefix + '_similar_part_container');
                var div = document.createElement('div');
                div.className = 'd-flex align-items-center mb-1';
                div.innerHTML = '<input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light font-weight-bold" value="' + val + '" readonly>' +
                                '<button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" onclick="window.removeSimilarPart(this, \'' + prefix + '\')" title="Hapus Part"><i class="fas fa-times"></i></button>';
                container.appendChild(div);
                window.updateHiddenSimilarPart(prefix);
            };

            window.appendSimilarPart = function(inputId, prefix) {
                var input = document.getElementById(inputId);
                var val = input.value.trim();
                
                if (val !== '') {
                    // Cek apakah value ada di datalist
                    var listId = input.getAttribute('list');
                    var datalist = document.getElementById(listId);
                    var exists = false;
                    
                    if (datalist) {
                        var options = datalist.options;
                        for (var i = 0; i < options.length; i++) {
                            if (options[i].value === val) {
                                exists = true;
                                break;
                            }
                        }
                    }
                    
                    if (!exists) {
                        Swal.fire('Peringatan', 'Part tidak terdaftar! Harap pilih part dari daftar yang tersedia.', 'warning');
                        return;
                    }

                    window.addSimilarPartElement(prefix, val);
                    input.value = '';
                    input.focus();
                }
            };
            window.updateHiddenCause = function(prefix) {
                var m = document.getElementById(prefix + '_cause_4m').value;
                var txt = document.getElementById(prefix + '_cause_text').value.trim();
                var hidden = document.getElementById(prefix + '_cause_hidden');
                if (m && txt) {
                    hidden.value = '[' + m + '] ' + txt;
                } else if (txt) {
                    hidden.value = txt;
                } else if (m) {
                    hidden.value = '[' + m + '] ';
                } else {
                    hidden.value = '';
                }
            };

            window.addCmElement = function(prefix, m, corr, prev) {
                var container = document.getElementById(prefix + '_cm_container');
                var div = document.createElement('div');
                div.className = 'd-flex align-items-start mb-2 bg-light p-2 rounded shadow-sm position-relative';
                
                var compiledText = '[' + m + '] Corrective: ' + corr + ' | Preventive: ' + prev;
                
                div.innerHTML = '<div class="flex-grow-1 small">' +
                                '<strong>[' + m + ']</strong><br>' +
                                '<span class="text-dark font-weight-bold">Corrective:</span> ' + corr + '<br>' +
                                '<span class="text-dark font-weight-bold">Preventive:</span> ' + prev + 
                                '</div>' +
                                '<input type="hidden" class="cm-raw-value" value="' + compiledText.replace(/"/g, '&quot;') + '">' +
                                '<button type="button" class="btn btn-sm btn-danger ml-2" onclick="window.removeCm(this, \'' + prefix + '\')" title="Hapus"><i class="fas fa-times"></i></button>';
                container.appendChild(div);
                window.updateHiddenCm(prefix);
            };

            window.removeCm = function(btn, prefix) {
                btn.parentElement.remove();
                window.updateHiddenCm(prefix);
            };

            window.updateHiddenCm = function(prefix) {
                var container = document.getElementById(prefix + '_cm_container');
                var hidden = document.getElementById(prefix + '_cm_hidden');
                var raws = container.querySelectorAll('.cm-raw-value');
                var vals = [];
                var count = raws.length;
                raws.forEach(function(inp, index) {
                    var val = inp.value;
                    val = val.replace(/^\d+\.\s*/, '');
                    if (count > 1) {
                        vals.push((index + 1) + '. ' + val);
                    } else {
                        vals.push(val);
                    }
                });
                hidden.value = vals.join('\n');
            };

            window.appendCountermeasure = function(prefix) {
                var m = document.getElementById(prefix + '_cm_4m');
                var corr = document.getElementById(prefix + '_cm_corrective');
                var prev = document.getElementById(prefix + '_cm_preventive');
                
                if (!m.value) { Swal.fire('Peringatan', 'Pilih 4M terlebih dahulu!', 'warning'); return; }
                if (!corr.value.trim() && !prev.value.trim()) { Swal.fire('Peringatan', 'Isi Corrective atau Preventive minimal satu!', 'warning'); return; }
                
                window.addCmElement(prefix, m.value, corr.value.trim(), prev.value.trim());
                
                m.value = '';
                corr.value = '';
                prev.value = '';
                m.focus();
            };
            var isAdmin = window.kakotoraConfig.isAdmin;
            var colOffset = isAdmin ? 1 : 0;

            var formatChildRow = function (d) {
                var cmRaw = d[19 + colOffset] || '-';
                var cmFormatted = cmRaw;
                
                if (cmRaw !== '-') {
                    var lines = cmRaw.split('\n');
                    var fLines = [];
                    lines.forEach(function(line) {
                        var mMatch = line.match(/^(?:(\d+\.)\s*)?(\[(?:Man|Material|Method|Machine)\])\s*Corrective:\s*(.*?)\s*\|\s*Preventive:\s*(.*)$/si);
                        if (mMatch) {
                            var num = mMatch[1] ? mMatch[1] + ' ' : '';
                            var f = '<div class="mb-2 text-dark">' + num + '<strong>' + mMatch[2] + '</strong><br>' +
                                    '<div style="padding-left: 1.5rem;"><span class="font-weight-bold">&bull; Corrective:</span> ' + mMatch[3] + '<br>' +
                                    '<span class="font-weight-bold">&bull; Preventive:</span> ' + mMatch[4] + '</div></div>';
                            fLines.push(f);
                        } else {
                            fLines.push('<div>' + line + '</div>');
                        }
                    });
                    cmFormatted = fLines.join('');
                }

                return '<div class="p-3" style="background-color: #f8f9fc;">' +
                    '<table class="table table-sm table-borderless mb-0">' +
                    '<tr>' +
                    '<td style="width: 15%; font-weight: bold; padding: 0.5rem;">Similar Part</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[14 + colOffset] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Problem</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[16 + colOffset] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Cause</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[18 + colOffset] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Countermeasure</td>' +
                    '<td style="padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + cmFormatted + '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>';
            };

            var table = $('#dataTableKakotora').DataTable({
                dom: "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                     "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                "order": [],
                "autoWidth": false,
                "columnDefs": [
                    { "orderable": false, "targets": isAdmin ? [0, 1 + colOffset] : [1] },
                    { "visible": false, "targets": [14 + colOffset, 16 + colOffset, 18 + colOffset, 19 + colOffset] }
                ],
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total records)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                initComplete: function(settings, json) {
                    // ponytail: Prevent FOUC (Flash of Unstyled Content) by showing table only after fully initialized
                    $('#tableLoader').hide();
                    $('#tableContainer').fadeIn('fast', function() {
                        table.columns.adjust(); // fix squished headers
                    });
                },
                drawCallback: function(settings) {
                    // ponytail: Highlight search keywords safely using TreeWalker (supports multiple words & overlapping)
                    var api = this.api();
                    var tbody = api.table().body();
                    
                    // 1. Unmark previous highlights and merge split text nodes
                    $(tbody).find('mark.hlt').each(function() {
                        $(this).replaceWith(this.childNodes);
                    });
                    tbody.normalize();

                    var searchStr = api.search();
                    if (!searchStr) return;

                    var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
                    if (keywords.length === 0) return;

                    // Escape regex chars and sort by length descending to match longer words first
                    keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
                    var regex = new RegExp("(" + keywords.join('|') + ")", "gi");

                    api.rows({ page: 'current' }).nodes().each(function(row) {
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
                }
            });

            // Prevent form submit on Enter key press on search input
            $('input[name="search"]').on('keypress', function (e) {
                if (e.which == 13) {
                    e.preventDefault();
                }
            });

            // Prevent normal form submission unless printing
            $('#filterFormKakotora').on('submit', function (e) {
                if (document.activeElement && document.activeElement.hasAttribute('formaction')) {
                    return;
                }
                e.preventDefault();
            });

            // Instant smart search
            $('input[name="search"]').on('keyup input', function () {
                // ponytail: Smart NLP Search - Remove Indonesian stop words so conversational queries like 
                // "tolong keluarkan problem bintik di proses plating" become "bintik plating".
                let input = $(this).val().toLowerCase();
                let stops = ['tolong', 'keluarkan', 'semua', 'di', 'pada', 'proses', 'nah', 'langsung', 'nya', 'tampilkan', 'cari', 'carikan', 'yang', 'ada', 'dan', 'atau', 'buatkan', 'buat', 'data', 'problem', 'masalah', 'part', 'kakotora', 'database', 'dari', 'ke', 'untuk'];
                let keywords = input.split(/[\s,.]+/).filter(w => w && !stops.includes(w));
                table.search(keywords.length ? keywords.join(' ') : input).draw();
            });

            // Instant claim filter (Column index 8)
            $('select[name="category_claim"]').on('change', function () {
                var val = $(this).val();
                table.column(8 + colOffset).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
            });

            // Instant status filter (Column index 23)
            $('select[name="status"]').on('change', function () {
                var val = $(this).val();
                table.column(23 + colOffset).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
            });

            // Reset filters client-side
            $('#btnResetFilter').on('click', function () {
                $('input[name="search"]').val('');
                $('select[name="category_claim"]').val('');
                $('select[name="status"]').val('');
                
                table.search('').columns().search('').draw();
            });

            // Run initial filters if preset
            var initialSearch = $('input[name="search"]').val();
            if (initialSearch) {
                table.search(initialSearch);
            }
            var initialClaim = $('select[name="category_claim"]').val();
            if (initialClaim) {
                table.column(8 + colOffset).search('^' + $.fn.dataTable.util.escapeRegex(initialClaim) + '$', true, false);
            }
            var initialStatus = $('select[name="status"]').val();
            if (initialStatus) {
                table.column(23 + colOffset).search('^' + $.fn.dataTable.util.escapeRegex(initialStatus) + '$', true, false);
            }
            if (initialSearch || initialClaim || initialStatus) {
                table.draw();
            }

            // Add event listener for opening and closing details
            $('#dataTableKakotora tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var icon = $(this).find('i');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    icon.removeClass('fa-caret-down').addClass('fa-caret-right');
                }
                else {
                    row.child(formatChildRow(row.data())).show();
                    tr.addClass('shown');
                    icon.removeClass('fa-caret-right').addClass('fa-caret-down');
                    
                    // Highlight details if search is active
                    var searchStr = table.search();
                    if (searchStr) {
                        var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
                        if (keywords.length > 0) {
                            keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
                            var regex = new RegExp("(" + keywords.join('|') + ")", "gi");
                            var walker = document.createTreeWalker(row.child()[0], NodeFilter.SHOW_TEXT, null, false);
                            var nodes = [];
                            while (walker.nextNode()) nodes.push(walker.currentNode);
                            nodes.forEach(function(node) {
                                var text = node.nodeValue;
                                if (text.trim() && regex.test(text)) {
                                    var span = document.createElement('span');
                                    span.innerHTML = text.replace(regex, "<mark class='hlt' style='background-color: #fffa90; color: #000000; padding: 0 2px; border-radius: 2px;'>$1</mark>");
                                    var frag = document.createDocumentFragment();
                                    while (span.firstChild) frag.appendChild(span.firstChild);
                                    node.parentNode.replaceChild(frag, node);
                                }
                            });
                        }
                    }
                }
            });

            $('#dataTableKakotora tbody').on('click', '.btn-edit-kakotora', function () {
                var id = $(this).data('id');
                var date = $(this).data('date');
                var no_reg = $(this).data('no_reg');
                var issue_date = $(this).data('issue_date');
                var rev_model = $(this).data('rev_model');
                var family = $(this).data('family');
                var category_nm_mp = $(this).data('category_nm_mp');
                var category_claim = $(this).data('category_claim');
                var model = $(this).data('model');
                var part_number = $(this).data('part_number');
                var part_name = $(this).data('part_name');
                var mould = $(this).data('mould');
                var owner_mould = $(this).data('owner_mould');
                var similar_part = $(this).data('similar_part');
                var section = $(this).data('section');
                var process = $(this).data('process');
                var problem = $(this).data('problem');
                var cause = $(this).data('cause');
                var countermeasure = $(this).data('countermeasure');
                var pic = $(this).data('pic');
                var supplier = $(this).data('supplier');
                var defect_category = $(this).data('defect_category');
                var status = $(this).data('status');
                var remarks = $(this).data('remarks');
                var file_url = $(this).data('file_url');

                // Set values to Edit Modal
                $('#edit_date').val(date);
                $('#edit_no_reg').val(no_reg);
                $('#edit_issue_date').val(issue_date);
                $('#edit_rev_model').val(rev_model);
                $('#edit_family').val(family);
                $('#edit_category_nm_mp').val(category_nm_mp);
                $('#edit_category_claim').val(category_claim);
                $('#edit_model').val(model);
                $('#edit_part_number').val(part_number);
                $('#edit_part_name').val(part_name);
                $('#edit_mould').val(mould);
                $('#edit_owner_mould').val(owner_mould);
                
                var container = $('#edit_similar_part_container');
                container.empty();
                if(similar_part) {
                    var parts = similar_part.split('\n');
                    parts.forEach(function(p) {
                        if(p.trim() !== '') {
                            window.addSimilarPartElement('edit', p.trim());
                        }
                    });
                }
                window.updateHiddenSimilarPart('edit');

                $('#edit_section').val(section);
                $('#edit_process').val(process);
                
                // Set problem field properly
                var editProbSel = $('#edit_problem_select');
                var exists = false;
                editProbSel.find('option').each(function(){
                    if($(this).val() == problem && problem != '') {
                        exists = true;
                    }
                });
                if(!exists && problem) {
                    editProbSel.append(new Option(problem, problem, false, false));
                }
                editProbSel.val(problem);

                // Parse Cause
                var mMatchCause = (cause || '').match(/^\[(Man|Material|Method|Machine)\]\s*(.*)$/si);
                if (mMatchCause) {
                    $('#edit_cause_4m').val(mMatchCause[1]);
                    $('#edit_cause_text').val(mMatchCause[2]);
                } else {
                    $('#edit_cause_4m').val('');
                    $('#edit_cause_text').val(cause || '');
                }
                $('#edit_cause_hidden').val(cause || '');

                // Parse Countermeasure
                var cmContainer = $('#edit_cm_container');
                cmContainer.empty();
                if(countermeasure) {
                    var cmParts = countermeasure.split('\n');
                    cmParts.forEach(function(p) {
                        var cleanP = p.replace(/^\d+\.\s*/, '').trim();
                        if(cleanP !== '') {
                            var mMatchCm = cleanP.match(/^\[(Man|Material|Method|Machine)\]\s*Corrective:\s*(.*?)\s*\|\s*Preventive:\s*(.*)$/si);
                            if (mMatchCm) {
                                window.addCmElement('edit', mMatchCm[1], mMatchCm[2], mMatchCm[3]);
                            } else {
                                // Legacy data
                                window.addCmElement('edit', 'Method', cleanP, '-');
                            }
                        }
                    });
                }
                window.updateHiddenCm('edit');
                $('#edit_pic').val(pic);
                $('#edit_supplier').val(supplier);
                $('#edit_defect_category').val(defect_category);
                $('#edit_status').val(status);
                $('#edit_remarks').val(remarks);

                if (file_url) {
                    let fileName = file_url.split('/').pop();
                    $('#edit_file_preview').html(`
                        <label class="small font-weight-bold mb-1 d-block text-muted">File tersimpan:</label>
                        <div id="form-analysis-file-row" class="d-flex align-items-center mb-1 p-1 border rounded bg-light" style="overflow:hidden; font-size: 0.75rem;">
                            <i class="fas fa-file-pdf text-danger mr-1 flex-shrink-0" style="font-size: 1.1rem;"></i>
                            <span class="text-truncate mr-2 flex-grow-1" style="min-width:0;" title="${fileName}">${fileName}</span>
                            <button type="button" class="btn btn-info btn-sm mr-1 flex-shrink-0 view-pdf-btn-kakotora" data-src="${file_url}" style="font-size:0.65rem; padding:2px 6px;">View</button>
                            <button type="button" class="btn btn-danger btn-sm flex-shrink-0 btn-delete-pdf-ajax" data-id="${id}" style="font-size:0.65rem; padding:2px 6px;">Hapus</button>
                        </div>
                    `);
                } else {
                    $('#edit_file_preview').html('');
                }

                // Set Action URL
                $('#formEditKakotora').attr('action', window.kakotoraConfig.updateUrlBase + '/' + id);

                // Show Modal
                $('#modalEditKakotora').modal('show');
            });

            // Bulk Delete Logic
            const checkAllBtn = $('#checkAllRows');
            const bulkMenu = $('#bulkActionMenu');
            const bulkSelectedCount = $('#bulkSelectedCount');
            const btnBulkDelete = $('#btnBulkDelete');
            const countDisplay = $('#checkedCountDisplay');

            function updateCount() {
                const checkedCount = $('.row-checkbox:checked').length;
                const totalCheckboxes = $('.row-checkbox').length;
                countDisplay.text(checkedCount);
                if (bulkSelectedCount.length > 0) {
                    bulkSelectedCount.text(checkedCount);
                }
                
                if(totalCheckboxes > 0) {
                    checkAllBtn.prop('checked', checkedCount === totalCheckboxes);
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

            checkAllBtn.on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.row-checkbox').prop('checked', isChecked);
                updateCount();
            });

            $('#dataTableKakotora tbody').on('change', '.row-checkbox', function(e) {
                e.stopPropagation();
                updateCount();
            });

            table.on('draw', function() {
                updateCount();
            });

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
                                url: window.kakotoraConfig.bulkDestroyUrl,
                                type: 'POST',
                                data: {
                                    _token: window.kakotoraConfig.csrfToken,
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
                                    Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                                }
                            });
                        }
                    });
                });
            }
            // Single Delete Logic - handled by global deleteKakotora() function

            // Delete Form Analysis logic via AJAX
            $(document).on('click', '.btn-delete-pdf-ajax', function() {
                var btn = $(this);
                var id = btn.data('id');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'File PDF akan dihapus secara langsung dan permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Hapus File!',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus File...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: window.kakotoraConfig.deletePdfUrl + '/' + id,
                            type: 'POST',
                            data: {
                                _token: window.kakotoraConfig.csrfToken
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#edit_file_preview').empty();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Update local DOM so the PDF button disappears from the table too
                                        var editBtn = $('.btn-edit-kakotora[data-id="'+id+'"]');
                                        editBtn.attr('data-file_url', '');
                                        editBtn.data('file_url', '');
                                        editBtn.closest('div').find('.view-pdf-btn-kakotora').remove();
                                    });
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Gagal menghapus file.', 'error');
                            }
                        });
                    }
                });
            });

            // View PDF logic
            $(document).on('click', '.view-pdf-btn-kakotora', function(e) {
                e.preventDefault();
                const url = $(this).data('src');
                $('#kakotoraPdfIframe').attr('src', url);
                $('#modalViewPdfKakotora').modal('show');
            });

            // Clear iframe on hide
            $('#modalViewPdfKakotora').on('hidden.bs.modal', function () {
                $('#kakotoraPdfIframe').attr('src', '');
            });

            $('#modalTambahKakotora').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset();
                $('#add_similar_part_container').empty();
                $('#add_similar_part_hidden').val('');
                $('#add_cause_hidden').val('');
                $('#add_cm_container').empty();
                $('#add_cm_hidden').val('');
            });

            // Handle clear input file
            $('#edit_form_analysis').on('change', function() {
                if ($(this)[0].files.length > 0) {
                    $('#clear_edit_file').removeClass('d-none');
                } else {
                    $('#clear_edit_file').addClass('d-none');
                }
            });

            $('#clear_edit_file').on('click', function() {
                $('#edit_form_analysis').val('');
                $(this).addClass('d-none');
            });

        });
    function validateKakotoraForm(e, prefix) {
        // Cek Similar Part
        const similarPartHidden = document.getElementById(prefix + '_similar_part_hidden');
        if (!similarPartHidden || !similarPartHidden.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Similar Part tidak boleh kosong. Silakan isi dan tambahkan (+) part yang mirip.',
                icon: 'warning'
            });
            return false;
        }

        // Cek Problem
        const problemSelect = document.getElementById(prefix + '_problem_select');
        if (!problemSelect || !problemSelect.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Problem tidak boleh kosong. Silakan pilih atau tambahkan problem.',
                icon: 'warning'
            });
            return false;
        }

        // Cek Cause
        const causeHidden = document.getElementById(prefix + '_cause_hidden');
        if (!causeHidden || !causeHidden.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Cause tidak boleh kosong. Silakan isi 4M dan deskripsinya.',
                icon: 'warning'
            });
            return false;
        }

        // Cek Countermeasure
        const cmHidden = document.getElementById(prefix + '_cm_hidden');
        if (!cmHidden || !cmHidden.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Countermeasure tidak boleh kosong. Silakan isi setidaknya satu countermeasure dan klik (+).',
                icon: 'warning'
            });
            return false;
        }

        // Cek Jika ada ketikan di Countermeasure tapi belum di klik +
        const cm4m = document.getElementById(prefix + '_cm_4m');
        const cmCorrective = document.getElementById(prefix + '_cm_corrective');
        const cmPreventive = document.getElementById(prefix + '_cm_preventive');
        
        if (cmCorrective && cmPreventive && cm4m) {
            if (cmCorrective.value.trim() !== '' || cmPreventive.value.trim() !== '') {
                e.preventDefault();
                Swal.fire({
                    title: 'Belum Ditambahkan!',
                    text: 'Anda sudah mengetik Countermeasure, tetapi belum klik tombol (+). Silakan klik tanda (+) terlebih dahulu agar data masuk ke daftar.',
                    icon: 'warning'
                });
                return false;
            }
        }

        return true;
    }

    document.getElementById('formAddKakotora').addEventListener('submit', function(e) {
        validateKakotoraForm(e, 'add');
    });

    document.getElementById('formEditKakotora').addEventListener('submit', function(e) {
        validateKakotoraForm(e, 'edit');
    });