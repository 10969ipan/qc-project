document.addEventListener('DOMContentLoaded', function() {
        // Tab Persistence
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('settingsActiveTab', $(e.target).attr('href'));
        });
        
        var activeTab = localStorage.getItem('settingsActiveTab');
        if (activeTab) {
            $('a[href="' + activeTab + '"]').tab('show');
        }

        // Menu Detail Loading
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                
                const menuId = this.getAttribute('data-id');
                loadMenuDetails(menuId);
            });
        });

        function loadMenuDetails(id) {
            $('#formMenuDetail').css('opacity', '0');
            $.get(`${window.settingsConfig.var_0}/${id}`, function(menu) {
                $('#menuDetailId').val(menu.id);
                $('#menuDetailName').text(menu.name);
                $('#menuDisplayName').val(menu.name);
                $('#menuDetailIdText').text(`ID Menu: #${menu.id}`);
                $('#menuDetailIcon').attr('class', menu.icon || 'fas fa-circle');
                $('#menuMaintMessage').val(menu.maintenance_message);
                
                // Visibility Status
                if (!menu.is_active) {
                    $('#statusHidden').prop('checked', true);
                } else if (menu.is_maintenance) {
                    $('#statusMaint').prop('checked', true);
                } else {
                    $('#statusActive').prop('checked', true);
                }
                
                // Plant Access
                const plants = menu.plant_code ? menu.plant_code.split(',') : [];
                $('#plantJktCheckbox').prop('checked', plants.includes('JKT'));
                $('#plantKrwCheckbox').prop('checked', plants.includes('KRW'));

                $('#formMenuDetail').addClass('fade-in-quick').css('opacity', '1');
                setTimeout(() => $('#formMenuDetail').removeClass('fade-in-quick'), 400);
            });
        }

        // Update Menu Details
        $('#btnUpdateMenu').on('click', function() {
            const menuId = $('#menuDetailId').val();
            if (!menuId) {
                Swal.fire('Info', 'Pilih menu terlebih dahulu.', 'info');
                return;
            }

            const status = $('input[name="menuStatus"]:checked').val();
            const data = {
                _token: window.settingsConfig.var_1,
                name: $('#menuDisplayName').val(),
                is_active: status !== 'hidden' ? 1 : 0,
                is_maintenance: status === 'maintenance' ? 1 : 0,
                maintenance_message: $('#menuMaintMessage').val(),
                plant_jkt: $('#plantJktCheckbox').is(':checked') ? 1 : 0,
                plant_krw: $('#plantKrwCheckbox').is(':checked') ? 1 : 0
            };

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: `${window.settingsConfig.var_0}/${menuId}`,
                type: 'PUT',
                data: data,
                success: function(response) {
                    Swal.fire('Berhasil', response.message, 'success').then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat memperbarui menu.', 'error');
                    btn.prop('disabled', false).text('Terapkan Perubahan');
                }
            });
        });

        // Initialize Sortable for all levels
        document.querySelectorAll('.sortable-menu').forEach(el => {
            new Sortable(el, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-soft-primary',
                group: el.getAttribute('data-parent-id') === "" ? 'root' : 'nested'
            });
        });

        // Save Menu Order
        $('#saveMenuOrder').on('click', function() {
            const items = [];
            
            // Recursive function to collect items correctly
            function collectItems(container, parentId) {
                $(container).children('.nested-group-item').each(function(index) {
                    const itemId = $(this).data('id');
                    items.push({
                        id: itemId,
                        order: index + 1,
                        parent_id: parentId || null
                    });
                    
                    // Look for nested containers within this item
                    const childContainer = $(this).children('.sortable-menu');
                    if (childContainer.length > 0) {
                        collectItems(childContainer[0], itemId);
                    }
                });
            }
            
            const rootContainer = document.querySelector('.sortable-menu[data-parent-id=""]');
            if (rootContainer) {
                collectItems(rootContainer, null);
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.post(window.settingsConfig.var_2, {
                _token: window.settingsConfig.var_1,
                items: items
            }, function(response) {
                Swal.fire('Berhasil', response.message, 'success').then(() => location.reload());
            }).fail(function() {
                Swal.fire('Gagal', 'Gagal menyimpan susunan menu.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
            });
        });
        
        // Tooltip init
        $('[data-toggle="tooltip"]').tooltip();

        // --- DYNAMIC ROLE PERMISSIONS (Split-View Matrix) ---
        
        // Bootstrap collapse arrow sync
        $(document).on('show.bs.collapse', '.collapse', function() {
            const headerId = '[data-target="#' + this.id + '"]';
            $(headerId).attr('aria-expanded', 'true');
        });
        $(document).on('hide.bs.collapse', '.collapse', function() {
            const headerId = '[data-target="#' + this.id + '"]';
            $(headerId).attr('aria-expanded', 'false');
        });

        // ===========================================================
        // CASCADE LOGIC: generalized to ALL levels via .perm-check 
        // (See CHECKBOX MANUAL SYNC section below)
        // ===========================================================

        // ===========================================================
        // MASTER TOGGLE: power button Ã¢â€ â€™ disable/enable WHOLE MODULE
        // ===========================================================
        // Helper: update card visual state + status badge
        function setModuleCardState(rootId, isActive) {
            const card = $(`.perm-module-card[data-module-id="${rootId}"]`);
            const badge = $(`#status-badge-${rootId}`);
            const btn = card.find('.module-master-toggle');

            if (isActive) {
                card.removeClass('module-is-off');
                badge.text('Aktif').removeClass('is-off');
                btn.removeClass('is-inactive').addClass('is-active')
                   .attr('title', 'Nonaktifkan seluruh modul ini');
            } else {
                card.addClass('module-is-off');
                badge.text('Non-aktif').addClass('is-off');
                btn.removeClass('is-active').addClass('is-inactive')
                   .attr('title', 'Aktifkan seluruh modul ini');
            }
        }
        // Determine initial state of each card on load
        function refreshMasterToggles() {
            $('.module-master-toggle').each(function() {
                const rootId = $(this).data('menu-id');
                const anyParent = $(`#view_${rootId}, #input_${rootId}, #edit_${rootId}, #approve_${rootId}, #export_${rootId}`)
                    .toArray().some(cb => cb.checked);
                const anyChild = $(`.child-check-${rootId}.perm-check`).toArray().some(cb => cb.checked);
                setModuleCardState(rootId, anyParent || anyChild);
            });
        }

        // Power button click Ã¢â€ â€™ cascade OFF/ON
        // NOTE: Uses direct binding (NOT delegated from document) so stopPropagation
        // correctly prevents Bootstrap collapse on the card header from triggering.
        // The inline onclick was removed from the HTML for the same reason.
        $(document).ready(function() {
            // Re-bind whenever permissions tab is shown (cards may have been re-rendered)
            function bindMasterToggles() {
                // Unbind first to avoid duplicate handlers
                $('.module-master-toggle').off('click.masterToggle').on('click.masterToggle', function(e) {
                    e.stopPropagation(); // Stops event from reaching .perm-module-card-header Ã¢â€ â€™ prevents collapse
                    const rootId        = $(this).data('menu-id');
                    const isCurrentlyOn = $(this).hasClass('is-active');
                    const willBeOn      = !isCurrentlyOn;

                    if (!willBeOn) {
                        // OFF: uncheck ALL checkboxes (parent + all children)
                        $(`#view_${rootId}, #input_${rootId}, #edit_${rootId}, #approve_${rootId}, #export_${rootId}`)
                            .prop('checked', false);
                        $(`.child-check-${rootId}.perm-check`).prop('checked', false);
                    }
                    // ON: just un-dim the card; user decides which checkboxes to enable

                    setModuleCardState(rootId, willBeOn);
                });
            }
            bindMasterToggles();
        });

        // Run initial state on page load
        refreshMasterToggles();
        refreshSubToggles();

        // ===========================================================
        // SUB-MODULE TOGGLE (intermediate nodes with children)
        // ===========================================================

        // Sync appearance of a sub-toggle button
        function setSubToggleState(btn, isActive) {
            if (isActive) {
                btn.removeClass('sub-is-inactive').addClass('sub-is-active')
                   .attr('title', 'Nonaktifkan sub-modul ini beserta isinya');
            } else {
                btn.removeClass('sub-is-active').addClass('sub-is-inactive')
                   .attr('title', 'Aktifkan sub-modul ini');
            }
        }

        // Determine initial state of all sub-toggles (called on load + after fetchPermissions)
        function refreshSubToggles() {
            $('.sub-module-toggle').each(function() {
                const menuId = $(this).data('menu-id');

                // Check own checkboxes
                const ownChecked = $(`#view_${menuId}, #input_${menuId}, #edit_${menuId}, #approve_${menuId}, #export_${menuId}`)
                    .toArray().some(cb => cb.checked);

                // Check descendant checkboxes (empty set for leaf nodes Ã¢â€ â€™ always false)
                const childChecked = $(`.child-check-${menuId}.perm-check`).toArray().some(cb => cb.checked);

                const isActive = ownChecked || childChecked;
                setSubToggleState($(this), isActive);

                // For intermediate nodes: dim/undim descendant rows
                // For leaf nodes: descRows is empty, so no dimming occurs (correct behaviour)
                const descRows = $('.permission-row-item').filter(function() {
                    return $(this).find(`.child-check-${menuId}`).length > 0;
                });
                descRows.toggleClass('sub-group-disabled', !isActive);
            });
        }

        // Sub-module toggle click handler
        $(document).on('click', '.sub-module-toggle', function(e) {
            e.stopPropagation();
            const btn    = $(this);
            const menuId = btn.data('menu-id');
            const isOn   = btn.hasClass('sub-is-active');
            const willBeOn = !isOn;

            if (!willBeOn) {
                // ---- OFF: uncheck own checkboxes + all descendants ----
                $(`#view_${menuId}, #input_${menuId}, #edit_${menuId}, #approve_${menuId}, #export_${menuId}`)
                    .prop('checked', false).trigger('change');
                $(`.child-check-${menuId}.perm-check`).prop('checked', false).trigger('change');

                // Dim descendant rows visually
                const descRows = $('.permission-row-item').filter(function() {
                    return $(this).find(`.child-check-${menuId}`).length > 0;
                });
                descRows.addClass('sub-group-disabled');
            } else {
                // ---- ON: un-dim rows (user picks which to enable) ----
                const descRows = $('.permission-row-item').filter(function() {
                    return $(this).find(`.child-check-${menuId}`).length > 0;
                });
                descRows.removeClass('sub-group-disabled');
                
                // Jika ini adalah level terakhir (leaf node, tidak punya descendants), 
                // otomatis nyalakan kelima centang permission
                if (descRows.length === 0) {
                    $(`#view_${menuId}, #input_${menuId}, #edit_${menuId}, #approve_${menuId}, #export_${menuId}`)
                        .prop('checked', true).trigger('change');
                }
            }

            setSubToggleState(btn, willBeOn);

            // NOTE: We intentionally do NOT call refreshMasterToggles() here.
            // Each level is INDEPENDENT Ã¢â‚¬â€ turning off a sub-module should NEVER
            // automatically turn off its parent card. The parent card state is
            // only updated by: (1) its own power button, or (2) initial page load.
        });

        // After permissions loaded, refresh both
        const _origFetch = fetchPermissions;

        // Listen to individual checkbox changes to keep power buttons in sync
        // AND cascade the permission type to all descendants!
        $(document).on('change', '.perm-check', function() {
            const menuId    = $(this).data('menu-id');
            const type      = $(this).data('type');
            const isChecked = $(this).is(':checked');

            // Cascade to ALL descendants of THIS menu for the same type!
            // Any descendant of this menu has class `child-check-{menuId}`
            $(`.child-check-${menuId}[data-type="${type}"]`).prop('checked', isChecked);

            // Slight delay ensures the DOM state is completely updated before sync
            setTimeout(() => {
                refreshMasterToggles();
                refreshSubToggles();
            }, 10);
        });


        // ===========================================================
        // LIVE SEARCH & MINIMALIST PAGINATION (DATATABLES)
        // ===========================================================
        const userTable = $('#usersTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            info: false,
            ordering: false, // Tetap rapi tanpa panah sorting
            dom: 't<"mt-4 d-flex justify-content-center"p>',
            language: {
                paginate: {
                    previous: "<i class='fas fa-chevron-left fa-sm'></i>",
                    next: "<i class='fas fa-chevron-right fa-sm'></i>"
                }
            }
        });

        $('#liveSearchUser').on('keyup', function() {
            userTable.search($(this).val()).draw();
        });

        // Mode Switching (Role vs User)
        $('#permissionMode').on('change', function() {
            const mode = $(this).val();
            if (mode === 'role') {
                $('#roleSelectorContainer').removeClass('d-none');
                $('#userSelectorContainer').addClass('d-none');
                $('#permissionTitle').text('Matriks Izin Modul (By Role)');
                $('#permissionSubtitle').text('Konfigurasi hak akses standar untuk setiap peran pengguna');
                $('#roleSelector').trigger('change');
            } else {
                $('#roleSelectorContainer').addClass('d-none');
                $('#userSelectorContainer').removeClass('d-none');
                $('#permissionTitle').text('Matriks Izin Modul (By User)');
                $('#permissionSubtitle').text('Konfigurasi hak akses khusus/spesifik untuk individu (Override)');
                $('#userSelector').trigger('change');
            }
        });

        // Role Selector Change
        $('#roleSelector').on('change', function() {
            if ($('#permissionMode').val() !== 'role') return;
            fetchPermissions({ role: $(this).val() });
        });

        // User Selector Change
        $('#userSelector').on('change', function() {
            if ($('#permissionMode').val() !== 'user') return;
            const userId = $(this).val();
            if (!userId) {
                resetCheckboxes();
                return;
            }
            fetchPermissions({ user_id: userId });
        });

        function fetchPermissions(params) {
            $.get(window.settingsConfig.var_3, params, function(data) {
                resetCheckboxes();
                // Check based on data
                Object.keys(data).forEach(menuId => {
                    const perm = data[menuId];
                    $(`#view_${menuId}`).prop('checked', !!perm.can_view);
                    $(`#input_${menuId}`).prop('checked', !!perm.can_input);
                    $(`#edit_${menuId}`).prop('checked', !!perm.can_edit);
                    $(`#approve_${menuId}`).prop('checked', !!perm.can_approve);
                    $(`#approve_all_${menuId}`).prop('checked', !!perm.can_approve_all);
                    $(`#export_${menuId}`).prop('checked', !!perm.can_export);
                });
                // Refresh cascade state after loading permissions
                refreshMasterToggles();
                refreshSubToggles();
            });
        }

        function resetCheckboxes() {
            $('input[type="checkbox"].custom-control-input[id^="view_"], \
               input[type="checkbox"].custom-control-input[id^="input_"], \
               input[type="checkbox"].custom-control-input[id^="edit_"], \
               input[type="checkbox"].custom-control-input[id^="approve_"], \
               input[type="checkbox"].custom-control-input[id^="approve_all_"], \
               input[type="checkbox"].custom-control-input[id^="export_"]').prop('checked', false);
        }

        $('#savePermissions').on('click', function() {
            const mode = $('#permissionMode').val();
            const role = $('#roleSelector').val();
            const userId = $('#userSelector').val();
            
            if (mode === 'user' && !userId) {
                Swal.fire('Peringatan', 'Silakan pilih user terlebih dahulu.', 'warning');
                return;
            }

            const permissions = {};
            
            // Build permissions object from checkboxes
            $('input[type="checkbox"].custom-control-input[id^="view_"]').each(function() {
                const id = this.id.split('_')[1];
                permissions[id] = {
                    view: $(`#view_${id}`).is(':checked'),
                    input: $(`#input_${id}`).is(':checked'),
                    edit: $(`#edit_${id}`).is(':checked'),
                    approve: $(`#approve_${id}`).is(':checked'),
                    approve_all: $(`#approve_all_${id}`).is(':checked'),
                    export: $(`#export_${id}`).is(':checked')
                };
            });
            
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: window.settingsConfig.var_4,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    _token: window.settingsConfig.var_1,
                    role: mode === 'role' ? role : null,
                    user_id: mode === 'user' ? userId : null,
                    permissions: permissions
                }),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Matriks');
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan matriks izin.';
                    Swal.fire('Gagal', errorMsg, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Matriks');
                }
            });
        });

        // --- USER MANAGEMENT AJAX & MODAL LOGIC ---

        $('#modalAddUser').on('show.bs.modal', function() {
            $('#formAddUser')[0].reset();
            $('.cancel-new-role[data-target="add"]').trigger('click');
        });

        // Edit User Button Click (Using delegation for DataTables compatibility)
        $(document).on('click', '.edit-user', function() {
            const btn = $(this);
            $('#edit_user_id').val(btn.data('id'));
            $('#edit_name').val(btn.data('name'));
            $('#edit_email').val(btn.data('email'));
            
            // Revert external logic for roles
            $('.cancel-new-role[data-target="edit"]').trigger('click');
            $('#edit_role').val(btn.data('role'));

            $('#edit_plant_id').val(btn.data('plant'));
            $('#edit_initials').val(btn.data('initials'));
            $('#modalEditUser').modal('show');
        });

        // TOGGLE NEW ROLE INPUT LOGIC
        $('.toggle-new-role').on('click', function() {
            let target = $(this).data('target'); // 'add' or 'edit'
            let selectId = target === 'edit' ? 'edit_role' : 'add_role';
            
            $('#' + selectId).prop('disabled', true).addClass('d-none').removeAttr('name');
            $('#role_input_group_' + target).removeClass('d-none');
            $('#role_input_' + target).prop('disabled', false).attr('name', 'role').attr('required', true).focus();
            $(this).addClass('d-none');
        });

        $('.cancel-new-role').on('click', function() {
            let target = $(this).data('target'); 
            let selectId = target === 'edit' ? 'edit_role' : 'add_role';

            $('#role_input_group_' + target).addClass('d-none');
            $('#role_input_' + target).prop('disabled', true).removeAttr('name').removeAttr('required').val('');
            $('#' + selectId).prop('disabled', false).removeClass('d-none').attr('name', 'role');
            $('.toggle-new-role[data-target="' + target + '"]').removeClass('d-none');
        });

        // Reset Password Button Click (Using delegation)
        let resetUserId = null;
        $(document).on('click', '.reset-password', function() {
            const btn = $(this);
            resetUserId = btn.data('id');
            $('#reset_user_name').text(btn.data('name'));
            $('#modalResetPassword').modal('show');
        });

        // Confirm Reset Password
        $('#confirmResetPassword').on('click', function() {
            if (!resetUserId) return;
            
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: `${window.settingsConfig.var_5}/${resetUserId}/reset-password`,
                type: 'PATCH',
                data: { _token: window.settingsConfig.var_1 },
                success: function(response) {
                    $('#modalResetPassword').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    btn.prop('disabled', false).text('Ya, Reset Sekarang');
                    resetUserId = null;
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal mereset password: ' + (xhr.responseJSON?.message || 'Error tidak dikenal')
                    });
                    btn.prop('disabled', false).text('Ya, Reset Sekarang');
                }
            });
        });

        // Form Add User Submit
        $('#formAddUser').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: window.settingsConfig.var_6,
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#modalAddUser').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menambah user: ' + (xhr.responseJSON?.message || 'Error tidak dikenal')
                    });
                    submitBtn.prop('disabled', false).text('Simpan User');
                }
            });
        });

        // Form Edit User Submit
        $('#formEditUser').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const userId = $('#edit_user_id').val();
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memperbarui...');

            $.ajax({
                url: `${window.settingsConfig.var_5}/${userId}`,
                type: 'PUT',
                data: form.serialize(),
                success: function(response) {
                    $('#modalEditUser').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal memperbarui user: ' + (xhr.responseJSON?.message || 'Error tidak dikenal')
                    });
                    submitBtn.prop('disabled', false).text('Perbarui Data');
                }
            });
        });

        $(document).on('click', '.delete-user', function() {
            const btn = $(this);
            const userId = btn.data('id');
            const userName = btn.data('name');

            Swal.fire({
                title: 'Hapus User?',
                text: `Anda akan menghapus user ${userName}. Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#2d3436',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${window.settingsConfig.var_5}/${userId}`,
                        type: 'DELETE',
                        data: { _token: window.settingsConfig.var_1 },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menghapus user: ' + (xhr.responseJSON?.message || 'Error tidak dikenal')
                            });
                        }
                    });
                }
            });
        });

        // Toggle User Status AJAX (Using delegation)
        $(document).on('change', '.toggle-user-status', function() {
            const checkbox = $(this);
            const userId = checkbox.data('id');
            const isActive = checkbox.is(':checked') ? 1 : 0;
            
            // Disable temporarily to prevent multiple clicks
            checkbox.prop('disabled', true);

            $.ajax({
                url: `${window.settingsConfig.var_5}/${userId}/status`,
                type: 'PATCH',
                data: { 
                    _token: window.settingsConfig.var_1,
                    is_active: isActive
                },
                success: function(response) {
                    checkbox.prop('disabled', false);
                    // Minimal feedback, toast or similar could be added if needed
                },
                error: function(xhr) {
                    checkbox.prop('disabled', false);
                    checkbox.prop('checked', !isActive); // Revert on failure
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal merubah status user: ' + (xhr.responseJSON?.message || 'Error tidak dikenal')
                    });
                }
            });
        });

        // Toggle Password Visibility
        $(document).on('click', '.password-toggle-icon', function() {
            const iconContainer = $(this);
            const icon = iconContainer.find('i');
            const input = iconContainer.siblings('input');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        // Live Search for Users Table
        $('#liveSearchUser').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#users table tbody tr").filter(function() {
                // Ensure we only match text content within td elements to avoid matching html attributes
                var rowText = $(this).find('td').text().toLowerCase();
                $(this).toggle(rowText.indexOf(value) > -1);
            });
            
            // Show "No results" message if all rows are hidden
            var visibleRows = $("#users table tbody tr:visible").length;
            if (visibleRows === 0) {
                if ($('#noResultsRow').length === 0) {
                    $("#users table tbody").append('<tr id="noResultsRow"><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-search fa-2x mb-3 opacity-50"></i><br>Tidak ada data pengguna yang cocok dengan pencarian "<b>'+$(this).val()+'</b>".</td></tr>');
                } else {
                    $('#noResultsRow td').html('<i class="fas fa-search fa-2x mb-3 opacity-50"></i><br>Tidak ada data pengguna yang cocok dengan pencarian "<b>'+$(this).val()+'</b>".');
                    $('#noResultsRow').show();
                }
            } else {
                $('#noResultsRow').hide();
            }
        });

    });


        $(document).ready(function() {
            $('#importFile').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#fileNameDisplay').text(fileName);
                } else {
                    $('#fileNameDisplay').text('Pilih file CSV...');
                }
            });
        });
    

        $(document).ready(function() {
            // Set Indonesian locale for Moment.js
            moment.locale('id');
            
            function highlightSearchTerm(containerId, term) {
                if (!term) return;
                const container = document.getElementById(containerId);
                if (!container) return;

                const stopWords = ['di', 'ke', 'dari', 'yang', 'dan', 'atau', 'untuk', 'dengan', 'pada', 'adalah'];
                const terms = term.toLowerCase().split(' ').filter(t => !stopWords.includes(t) && t.length > 1);
                if (terms.length === 0) terms.push(term.toLowerCase());

                const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, null, false);
                const nodesToHighlight = [];
                let node;
                while ((node = walker.nextNode())) {
                    const nodeText = node.nodeValue.toLowerCase();
                    if (terms.some(t => nodeText.includes(t)) && node.parentNode.nodeName !== 'SCRIPT' && node.parentNode.nodeName !== 'STYLE') {
                        nodesToHighlight.push(node);
                    }
                }

                nodesToHighlight.forEach(node => {
                    let html = node.nodeValue;
                    terms.forEach(t => {
                        if (t.trim() === '') return;
                        const regex = new RegExp(`(${t.replace(/[.*+?^${}()|[\\]\\\\]/g, '\\\\$&')})`, 'gi');
                        html = html.replace(regex, '<mark class="bg-warning text-dark p-0">$1</mark>');
                    });
                    const span = document.createElement('span');
                    span.innerHTML = html;
                    node.parentNode.replaceChild(span, node);
                });
            }

            // Activity Logs Logic
        function fetchActivityLogs(page = 1) {
            const search = $('#searchLogs').val() || '';
            $('#activityLogsBody').html(`
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                        Memuat data log...
                    </td>
                </tr>
            `);

            $.ajax({
                url: window.settingsConfig.var_7 + "?page=" + page + "&search=" + encodeURIComponent(search),
                type: 'GET',
                success: function(response) {
                    renderLogs(response.data);
                    renderPagination(response);
                    
                    if (search) {
                        setTimeout(() => {
                            highlightSearchTerm('activityLogsBody', search);
                        }, 50);
                    }
                },
                error: function() {
                    $('#activityLogsBody').html('<tr><td colspan="4" class="text-center py-5 text-danger">Gagal memuat data log.</td></tr>');
                }
            });
        }

        function formatFieldLabel(field) {
            const customLabels = {
                'date': 'Tanggal',
                'remarks': 'Remarks',
                'cycle_time': 'Cycle Time',
                'total_qty': 'Total Qty',
                'total_ok': 'Total OK',
                'total_ng': 'Total NG',
                'part_number': 'Nomor Part',
                'sap_code': 'Kode SAP',
                'dimension_check': 'Dimension Check',
                'defect_types': 'Defects',
                'kashift_qc': 'Approval Ka.Shift',
                'supervisor_qc': 'Approval Supervisor',
                'asst_manager_qc': 'Approval Asst. Manager',
                'manager_qc': 'Approval Manager'
            };
            if (customLabels[field]) return customLabels[field];
            return field.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        function formatValue(val, field, isNew = false) {
            if (val === null || val === undefined || val === '') {
                return '<em class="text-muted">kosong</em>';
            }

            let parsedVal = val;
            let iter = 0;
            while (typeof parsedVal === 'string' && iter < 3) {
                let trimmed = parsedVal.trim();
                // Check if it's a JSON string or a double-encoded string starting with quote
                if (trimmed.startsWith('{') || trimmed.startsWith('[') || (trimmed.startsWith('"') && trimmed.endsWith('"'))) {
                    try {
                        if (trimmed.startsWith('"') && trimmed.endsWith('"')) {
                            // Strip outer quotes and unescape manually if JSON.parse fails
                            try {
                                parsedVal = JSON.parse(trimmed);
                            } catch(e) {
                                let unescaped = trimmed.substring(1, trimmed.length - 1).replace(/\\"/g, '"');
                                parsedVal = JSON.parse(unescaped);
                            }
                        } else {
                            parsedVal = JSON.parse(trimmed);
                        }
                    } catch (e) {
                        break;
                    }
                } else {
                    break;
                }
                iter++;
            }

            // Formatting khusus untuk Defects
            if (field === 'defects' || field === 'defect_types') {
                if (!parsedVal || (Array.isArray(parsedVal) && parsedVal.length === 0) || (typeof parsedVal === 'object' && Object.keys(parsedVal).length === 0)) {
                    return '<em class="text-muted">Tidak ada NG</em>';
                }
                if (Array.isArray(parsedVal)) {
                    return parsedVal.map(d => {
                        if (typeof d === 'object') {
                            return `${d.type || d.nama || Object.keys(d)[0]}: ${d.qty || d.jumlah || Object.values(d)[0]} pcs`;
                        }
                        return d;
                    }).join(', ');
                }
                if (typeof parsedVal === 'object') {
                    return Object.entries(parsedVal).map(([k, v]) => `${k}: ${v} pcs`).join(', ');
                }
            }

            // Formatting khusus untuk Dimension Check
            if (field === 'dimension_check') {
                if (!parsedVal || (typeof parsedVal === 'object' && Object.keys(parsedVal).length === 0)) {
                    return '<em class="text-muted">kosong</em>';
                }
                if (typeof parsedVal === 'object') {
                    let formatted = [];
                    for (const [cavity, points] of Object.entries(parsedVal)) {
                        let pointStrs = [];
                        for (const [point, value] of Object.entries(points)) {
                            pointStrs.push(`Point ${point}= ${value}`);
                        }
                        formatted.push(`<span class="d-block" style="margin-bottom: 2px;"><span class="font-weight-bold">Cav ${cavity}</span> &rarr; ${pointStrs.join(', ')}</span>`);
                    }
                    return formatted.join('');
                }
            }

            if (typeof parsedVal === 'object') {
                try {
                    return JSON.stringify(parsedVal);
                } catch(e) {
                    return String(parsedVal);
                }
            }

            return String(parsedVal);
        }

        // ponytail: render old->new property changes inline
        function renderChanges(properties) {
            if (!properties || typeof properties !== 'object' || Object.keys(properties).length === 0) return '';
            let rows = '';
            for (const [field, vals] of Object.entries(properties)) {
                if (!vals || typeof vals !== 'object') continue;
                
                const oldVal = formatValue(vals.old, field, false);
                const newVal = formatValue(vals.new, field, true);
                
                if (field === 'dimension_check') {
                    rows += `<div class="mb-2" style="font-size: 0.7rem; line-height: 1.3;">
                        <div class="font-weight-bold text-secondary mb-1">${formatFieldLabel(field)}:</div>
                        <div class="pl-2 border-left border-danger mb-1 text-danger" style="text-decoration: line-through; opacity: 0.7;">
                            ${oldVal}
                        </div>
                        <div class="pl-2 border-left border-success text-success font-weight-bold">
                            ${newVal}
                        </div>
                    </div>`;
                } else {
                    rows += `<div class="d-flex align-items-start mb-1" style="font-size: 0.7rem; line-height: 1.3;">
                        <span class="font-weight-bold text-secondary mr-1 pt-1" style="min-width: 90px;">${formatFieldLabel(field)}:</span>
                        <div class="d-flex flex-wrap flex-fill align-items-start pt-1">
                            <span class="text-danger" style="text-decoration: line-through; opacity: 0.7;">${oldVal}</span>
                            <i class="fas fa-long-arrow-alt-right mx-2 text-primary" style="font-size: 0.6rem; margin-top: 3px;"></i>
                            <span class="text-success font-weight-bold">${newVal}</span>
                        </div>
                    </div>`;
                }
            }
            if (!rows) return '';
            return `<div class="mt-2 pl-2">${rows}</div>`;
        }
        function renderLogs(logs) {
            let html = '';
            if (logs.length === 0) {
                html = '<tr><td colspan="4" class="text-center py-5 text-muted">Belum ada riwayat aktivitas.</td></tr>';
            } else {
                logs.forEach(log => {
                    const actionLabel = getActionLabel(log.action);
                    const userName = log.user ? log.user.name : 'System';
                    const userEmail = log.user ? log.user.email : 'system@indoplat.com';
                    const initials = log.user ? (log.user.initials || userName.substring(0, 2)) : 'SY';
                    const time = moment(log.created_at).calendar(); 

                    html += `
                        <tr class="table-row-hover">
                            <td class="pt-3 pb-3 border-top">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-modern shadow-sm mr-3">
                                        ${initials.substring(0, 2).toUpperCase()}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.95rem; letter-spacing: -0.2px;">${userName}</h6>
                                        <small class="text-muted" style="font-size: 0.8rem;">${userEmail}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="pt-3 pb-3 text-center border-top">
                                ${actionLabel}
                            </td>
                            <td class="pt-3 pb-3 border-top">
                                <span class="d-block text-dark small font-weight-500">${log.description || '-'}</span>
                                ${log.model_type ? `<small class="text-muted mt-1 d-block" style="font-size: 0.65rem;">ID: #${log.model_id}</small>` : ''}
                            
                                ${renderChanges(log.properties)}
                            </td>
                            <td class="text-center pt-3 pb-3 border-top">
                                <div class="small d-flex align-items-center justify-content-center" style="font-size: 0.75rem; color: #6e707e;">
                                    <i class="far fa-clock mr-1 text-muted" style="font-size: 0.65rem;"></i>
                                    <span class="font-weight-500">${time}</span>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#activityLogsBody').html(html);
        }

        function getActionLabel(action) {
            let config = {
                'created': { color: '#4e73df', label: 'CREATED' },
                'updated': { color: '#36b9cc', label: 'UPDATED' },
                'deleted': { color: '#e74a3b', label: 'DELETED' },
                'approved': { color: '#1cc88a', label: 'APPROVED' },
                'rejected': { color: '#f6c23e', label: 'REJECTED' },
                'reset_password': { color: '#f6c23e', label: 'RESET PWD' },
                'status_toggle': { color: '#858796', label: 'STATUS' }
            };

            let item = config[action] || { color: '#858796', label: action.toUpperCase() };
            return `<span class="font-weight-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px; color: ${item.color}; text-transform: uppercase;">${item.label}</span>`;
        }

        function renderPagination(response) {
            let html = '<nav><ul class="pagination pagination-sm mb-0">';
            
            // Previous Button
            html += `<li class="page-item ${response.prev_page_url ? '' : 'disabled'}">
                        <a class="page-link rounded-circle mr-2" href="#" data-page="${response.current_page - 1}"><i class="fas fa-chevron-left"></i></a>
                    </li>`;

            // Simple pagination info
            html += `<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-dark font-weight-bold">Halaman ${response.current_page} dari ${response.last_page}</span></li>`;

            // Next Button
            html += `<li class="page-item ${response.next_page_url ? '' : 'disabled'}">
                        <a class="page-link rounded-circle ml-2" href="#" data-page="${response.current_page + 1}"><i class="fas fa-chevron-right"></i></a>
                    </li>`;

            html += '</ul></nav>';
            $('#logsPagination').html(html);
        }

        // Event listeners
        $('#activity-logs-tab').on('shown.bs.tab', function() {
            fetchActivityLogs();
        });

        $('#refreshLogs').on('click', function() {
            fetchActivityLogs();
        });

        $('#resetLogs').on('click', function() {
            $('#searchLogs').val('');
            fetchActivityLogs();
        });

        $('#searchLogs').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                fetchActivityLogs();
            }
        });

        $(document).on('click', '#logsPagination .page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) fetchActivityLogs(page);
        });

        // Handle General Settings Save
        $('#saveGeneralSettings').on('click', function() {
            var btn = $(this);
            var originalHtml = btn.html();
            
            var settings = {
                'daily_approval_gate_enabled': $('#dailyApprovalGate').is(':checked') ? '1' : '0'
            };

            btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: window.settingsConfig.var_8,
                type: "POST",
                data: {
                    _token: window.settingsConfig.var_1,
                    settings: settings
                },
                success: function(response) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan konfigurasi.';
                    alert('Error: ' + msg);
                },
                complete: function() {
                    btn.html(originalHtml).prop('disabled', false);
                }
            });
        });

        // Handle Add Next Process
        $('#formAddNextProcess').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            
            $.ajax({
                url: window.settingsConfig.var_9,
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500
                    }).then(() => location.reload());
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menambahkan opsi.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        // Handle Edit Next Process Modal
        $(document).on('click', '.edit-process', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var order = $(this).data('order');
            var plant = $(this).data('plant');
            var module = $(this).data('module');
            
            $('#edit_process_id').val(id);
            $('#edit_process_name').val(name);
            $('#edit_process_order').val(order);
            $('#edit_process_plant_id').val(plant);
            $('#edit_process_module').val(module);
            
            $('#modalEditNextProcess').modal('show');
        });

        // Handle Update Next Process
        $('#formEditNextProcess').on('submit', function(e) {
            e.preventDefault();
            var id = $('#edit_process_id').val();
            var form = $(this);
            
            $.ajax({
                url: window.settingsConfig.var_10 + "/" + id,
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500
                    }).then(() => location.reload());
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memperbarui opsi.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        // Handle Toggle Process Status
        $(document).on('change', '.toggle-process-status', function() {
            var id = $(this).data('id');
            var plant = $(this).data('plant');
            var module = $(this).data('module');
            var isActive = $(this).is(':checked') ? 1 : 0;
            
            $.ajax({
                url: window.settingsConfig.var_10 + "/" + id,
                type: "POST",
                data: {
                    _token: window.settingsConfig.var_1,
                    _method: "PUT",
                    is_active: isActive,
                    name: $(this).closest('tr').find('h6').text(),
                    plant_id: plant,
                    module: module
                },
                success: function(response) {
                    // Success notification optional
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal mengubah status.', 'error');
                    $(this).prop('checked', !isActive);
                }
            });
        });

        // Handle Delete Next Process
        $(document).on('click', '.delete-process', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            Swal.fire({
                title: 'Hapus Opsi?',
                text: "Anda akan menghapus opsi proses: " + name,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: window.settingsConfig.var_10 + "/" + id,
                        type: "DELETE",
                        data: { _token: window.settingsConfig.var_1 },
                        success: function(response) {
                            Swal.fire('Terhapus!', response.message, 'success').then(() => location.reload());
                        }
                    });
                }
            });
        });

        // Initial active tab handling from URL hash if exists
        var hash = window.location.hash;
        if (hash) {
            $('.nav-link[href="' + hash + '"]').tab('show');
            if (hash === '#header-dokumen') {
                loadDocumentHeaders();
            }
        }
        
        // --- Dashboard Layout Script ---
        const dashboardRoleSelector = $('#dashboardRoleSelector');
        
        function loadDashboardLayout() {
            var role = dashboardRoleSelector.val();
            if (!role) return;

            $.ajax({
                url: window.settingsConfig.var_11,
                type: 'GET',
                data: { role: role },
                success: function(response) {
                    // response is layout object or empty array
                    $('.dashboard-layout-toggle').each(function() {
                        var widgetId = $(this).data('widget-id');
                        // if empty object/array, default is checked
                        if ($.isEmptyObject(response)) {
                            $(this).prop('checked', true);
                        } else {
                            // if property exists, set to its boolean value, else default true
                            var val = response.hasOwnProperty(widgetId) ? response[widgetId] : true;
                            var isVisible = (val === true || val === 'true' || val === 1 || val === '1');
                            $(this).prop('checked', isVisible);
                        }
                    });
                },
                error: function() {
                    console.error("Gagal memuat layout dashboard.");
                }
            });
        }

        dashboardRoleSelector.on('change', loadDashboardLayout);
        
        // Load initial layout on tab show if first time or just load
        $('a[data-toggle="pill"][href="#dashboard-layout"]').on('shown.bs.tab', function (e) {
            loadDashboardLayout();
        });
        
        $('#saveDashboardLayout').on('click', function() {
            var role = dashboardRoleSelector.val();
            var layout = {};
            
            $('.dashboard-layout-toggle').each(function() {
                var widgetId = $(this).data('widget-id');
                layout[widgetId] = $(this).is(':checked');
            });
            
            var btn = $(this);
            var originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...').prop('disabled', true);
            
            $.ajax({
                url: window.settingsConfig.var_12,
                type: 'POST',
                data: {
                    _token: window.settingsConfig.var_1,
                    role: role,
                    layout: layout
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500
                    });
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan konfigurasi.';
                    Swal.fire('Error', msg, 'error');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });
        // --- End Dashboard Layout Script ---
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') === '#header-dokumen') {
                loadDocumentHeaders();
            }
        });

        // Load Document Headers
        function loadDocumentHeaders() {
            var tbody = $('#documentHeadersTable tbody');
            tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm mr-2 text-primary"></div> Memuat data...</td></tr>');
            
            $.ajax({
                url: window.settingsConfig.var_13,
                type: "GET",
                success: function(response) {
                    tbody.empty();
                    if(response.length === 0) {
                        tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted small">Belum ada kustomisasi header dokumen.</td></tr>');
                        return;
                    }
                    
                    response.forEach(function(item) {
                        let val = {};
                        try {
                            val = JSON.parse(item.value);
                        } catch(e) {}
                        
                        let moduleName = $('#doc_header_key option[value="'+item.key+'"]').text();
                        if(!moduleName || moduleName === '') moduleName = item.key;
                        
                        let html = `
                        <tr class="table-row-hover" style="border-bottom: 1px solid #f8f9fa;">
                            <td>
                                <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.85rem;">${moduleName}</h6>
                                <span class="badge badge-pill badge-${item.plant_code === 'jakarta' ? 'info' : 'primary'}" style="font-size: 0.65rem;">${item.plant_code.toUpperCase()}</span>
                            </td>
                            <td><span class="font-weight-bold text-dark" style="font-size: 0.8rem;">${val.no_dokumen || '-'}</span></td>
                            <td><span style="font-size: 0.8rem;">${val.tgl_terbit || '-'}</span></td>
                            <td><span style="font-size: 0.8rem;">${val.revisi || '-'}</span></td>
                            <td><span style="font-size: 0.8rem;">${val.halaman || '-'}</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm edit-doc-header" 
                                    data-id="${item.id}" 
                                    data-key="${item.key}"
                                    data-plant="${item.plant_code}"
                                    data-json='${JSON.stringify(val)}'
                                    data-toggle="tooltip" title="Edit">
                                    <i class="fas fa-pen text-primary" style="font-size: 0.7rem;"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm delete-doc-header ml-1" 
                                    data-id="${item.id}" 
                                    data-toggle="tooltip" title="Hapus">
                                    <i class="fas fa-trash text-danger" style="font-size: 0.7rem;"></i>
                                </button>
                            </td>
                        </tr>`;
                        tbody.append(html);
                    });
                },
                error: function() {
                    tbody.html('<tr><td colspan="6" class="text-center py-4 text-danger small">Gagal memuat data.</td></tr>');
                }
            });
        }

        // Add/Edit Document Header
        $(document).on('click', '.edit-doc-header', function() {
            var id = $(this).data('id');
            var key = $(this).data('key');
            var plant = $(this).data('plant');
            var val = $(this).data('json');
            
            $('#doc_header_id').val(id);
            $('#doc_header_key').val(key);
            $('#doc_header_plant_code').val(plant);
            $('#doc_header_no_dokumen').val(val.no_dokumen || '');
            $('#doc_header_tgl_terbit').val(val.tgl_terbit || '');
            $('#doc_header_revisi').val(val.revisi || '');
            $('#doc_header_halaman').val(val.halaman || '');
            
            $('#documentHeaderModalTitle').html('<i class="fas fa-pen mr-2"></i>Edit Header Dokumen');
            $('#modalAddDocumentHeader').modal('show');
        });
        
        $('#modalAddDocumentHeader').on('hidden.bs.modal', function () {
            $('#formDocumentHeader')[0].reset();
            $('#doc_header_id').val('');
            $('#documentHeaderModalTitle').html('<i class="fas fa-file-alt mr-2"></i>Kustomisasi Header Dokumen');
        });

        $('#formDocumentHeader').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: window.settingsConfig.var_14,
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    $('#modalAddDocumentHeader').modal('hide');
                    form[0].reset();
                    loadDocumentHeaders();
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan data.', 'error');
                }
            });
        });

        // Delete Document Header
        $(document).on('click', '.delete-doc-header', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Kustomisasi?',
                text: "Header dokumen akan kembali ke nilai bawaan sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: window.settingsConfig.var_15 + "/" + id,
                        type: "DELETE",
                        data: { _token: window.settingsConfig.var_1 },
                        success: function(response) {
                            loadDocumentHeaders();
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        });

        });

