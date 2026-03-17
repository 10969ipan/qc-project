@extends('layouts.admin')

@section('title', 'Pengaturan Sistem Utama')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold"></i>Pengaturan Sistem</h1>
    </div>
</div>

<div class="row">
    <!-- Sidebar / Nav Pills -->
    <div class="col-xl-3 col-lg-4 mb-4">
        <div class="card shadow-none border-0 bg-transparent">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills custom-nav-pills-minimal" id="settings-tabs" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="users-tab" data-toggle="pill" href="#users" role="tab" aria-controls="users" aria-selected="true">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users-cog mr-3 text-muted"></i>
                            <span class="font-weight-bold">Manajemen Pengguna</span>
                        </div>
                    </a>
                    <a class="nav-link" id="menus-tab" data-toggle="pill" href="#menus" role="tab" aria-controls="menus" aria-selected="false">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-layer-group mr-3 text-muted"></i>
                            <span class="font-weight-bold">Struktur & Menu</span>
                        </div>
                    </a>
                    <a class="nav-link" id="permissions-tab" data-toggle="pill" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt mr-3 text-muted"></i>
                            <span class="font-weight-bold">Hak Akses Modul</span>
                        </div>
                    </a>
                    <a class="nav-link" id="activity-logs-tab" data-toggle="pill" href="#activity-logs" role="tab" aria-controls="activity-logs" aria-selected="false">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-history mr-3 text-muted"></i>
                            <span class="font-weight-bold">Log Aktivitas</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-xl-9 col-lg-8">
        <div class="tab-content" id="settings-tabContent">
            
            <!-- Tab 1: Manajemen Akun -->
            <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                            <h6 class="m-0 font-weight-bold text-dark letter-spacing-1 font-size-sm mb-3 mb-md-0">Daftar Pengguna</h6>
                            
                            <div class="d-flex flex-column flex-sm-row gap-2 w-100 justify-content-end align-items-center" style="max-width: 500px;">
                                <div class="input-group input-group-sm rounded-pill overflow-hidden border bg-white" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-transparent border-0 text-muted px-3" id="basic-addon2">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="liveSearchUser" class="form-control bg-transparent border-0 font-size-sm shadow-none no-autoupper pl-0" placeholder="Cari pengguna..." aria-label="Search" aria-describedby="basic-addon2" style="height: 38px;">
                                </div>
                                
                                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0 ml-sm-2 flex-wrap flex-sm-nowrap">
                                    <button class="btn btn-outline-dark rounded-pill px-3 shadow-sm btn-sm-modern d-inline-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalImportUser" style="height: 38px; white-space: nowrap;">
                                        <i class="fas fa-upload fa-sm text-muted mr-2"></i> <span>Upload</span>
                                    </button>
                                    <a href="{{ route('admin.settings.users.export') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm-modern no-loader d-inline-flex align-items-center justify-content-center" style="height: 38px; white-space: nowrap;">
                                        <i class="fas fa-download fa-sm text-muted mr-2"></i> <span>Ekspor</span>
                                    </a>
                                    <button class="btn btn-dark rounded-pill px-4 shadow-sm btn-sm-modern text-nowrap ml-sm-2 d-inline-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalAddUser" style="height: 38px;">
                                        <i class="fas fa-user-plus fa-sm text-white-50 mr-2"></i> <span>Tambah Pengguna</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle custom-table table-minimalist mb-0 w-100">
                                <thead class="bg-white">
                                    <tr>
                                        <th width="30%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">User Profile</th>
                                        <th width="18%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">Role</th>
                                        <th width="17%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">Area / Plant</th>
                                        <th width="15%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">Status</th>
                                        <th width="20%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr class="table-row-hover">
                                        <td class="pt-3 pb-3 {{ !$loop->first ? 'border-top' : '' }}">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-modern shadow-sm mr-3">
                                                    {{ substr($user->initials, 0, 2) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.95rem; letter-spacing: -0.2px;">{{ $user->name }}</h6>
                                                    <small class="text-muted" style="font-size: 0.8rem;">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pt-3 pb-3 text-center {{ !$loop->first ? 'border-top' : '' }}">
                                            @php
                                                $roleColor = match(strtolower($user->role)) {
                                                    'admin' => '#4e73df',
                                                    'supervisor' => '#36b9cc',
                                                    'inspector' => '#1cc88a',
                                                    'manager', 'asst_manager' => '#f6c23e',
                                                    default => '#858796'
                                                };
                                            @endphp
                                            <span class="font-weight-bold d-block" style="font-size: 0.75rem; letter-spacing: 0.5px; color: {{ $roleColor }}; text-transform: uppercase;">
                                                {{ $user->role ?? 'No Role' }}
                                            </span>
                                        </td>
                                        <td class="pt-3 pb-3 text-center {{ !$loop->first ? 'border-top' : '' }}">
                                            <div class="small d-flex align-items-center justify-content-center" style="font-size: 0.75rem; color: #6e707e;">
                                                <i class="fas fa-map-marker-alt mr-1 text-danger" style="font-size: 0.65rem;"></i>
                                                <span class="font-weight-500">{{ ($user->plant && strtoupper($user->plant->name) !== 'TOTAL') ? str_replace(' / Head Office', '', $user->plant->name) : 'Global' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center pt-3 pb-3 {{ !$loop->first ? 'border-top' : '' }}">
                                            <div class="custom-control custom-switch custom-switch-success custom-switch-md d-inline-block">
                                                <input type="checkbox" class="custom-control-input toggle-user-status" id="status{{ $user->id }}" data-id="{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="status{{ $user->id }}"></label>
                                            </div>
                                        </td>
                                        </td>
                                        <td class="text-center pt-3 pb-3 pr-2 {{ !$loop->first ? 'border-top' : '' }}">
                                            <button class="btn btn-sm btn-action rounded-circle mr-1 edit-user" 
                                                data-id="{{ $user->id }}" 
                                                data-name="{{ $user->name }}" 
                                                data-email="{{ $user->email }}" 
                                                data-role="{{ $user->role }}" 
                                                data-plant="{{ $user->plant_id }}" 
                                                data-initials="{{ $user->initials }}"
                                                data-toggle="tooltip" title="Edit Data">
                                                <i class="fas fa-pen text-primary" style="font-size: 0.8rem;"></i>
                                            </button>
                                            <button class="btn btn-sm btn-action rounded-circle reset-password" 
                                                data-id="{{ $user->id }}" 
                                                data-name="{{ $user->name }}" 
                                                data-toggle="tooltip" title="Reset Password">
                                                <i class="fas fa-key text-warning" style="font-size: 0.8rem;"></i>
                                            </button>
                                            <button class="btn btn-sm btn-action rounded-circle delete-user" 
                                                data-id="{{ $user->id }}" 
                                                data-name="{{ $user->name }}" 
                                                data-toggle="tooltip" title="Hapus User">
                                                <i class="fas fa-trash text-danger" style="font-size: 0.8rem;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Manajemen Menu & Visibilitas -->
            <div class="tab-pane fade" id="menus" role="tabpanel" aria-labelledby="menus-tab">
                <div class="row slide-in">
                    <div class="col-lg-7 mb-4">
                        <div class="card shadow border-0 rounded-lg h-100">
                            <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between border-bottom-0">
                                <h6 class="m-0 font-weight-bold text-dark">Susunan Menu Sidebar</h6>
                                <button type="button" id="saveMenuOrder" class="btn btn-sm btn-outline-dark rounded-pill px-4 shadow-sm btn-sm-modern"><i class="fas fa-save mr-1"></i> Simpan Posisi</button>
                            </div>
                            <div class="card-body pt-0 px-2 px-md-3">
                                <p class="small text-muted mb-3 px-2">Geser <i>(drag-and-drop)</i> untuk merubah urutan menu di sidebar.</p>
                                
                                <div class="list-group list-group-flush sortable-menu" data-parent-id="">
                                    @foreach($menus as $menu)
                                        <div class="nested-group-item mb-2" data-id="{{ $menu->id }}">
                                            <div class="list-group-item d-flex justify-content-between align-items-center menu-item rounded shadow-sm border" data-id="{{ $menu->id }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="drag-handle mr-3 text-muted" style="cursor: move;"><i class="fas fa-grip-vertical"></i></div>
                                                    <div class="icon-square bg-soft-primary text-primary mr-3"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></div>
                                                    <span class="font-weight-bold text-dark">{{ $menu->name }}</span>
                                                </div>
                                                <span class="badge {{ $menu->is_active ? 'badge-soft-success' : 'badge-soft-danger' }} pill-badge">{{ $menu->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                                            </div>
                                            
                                            <div class="list-group list-group-flush sortable-menu ml-4 mt-2" data-parent-id="{{ $menu->id }}" style="border-left: 2px dashed #e3e6f0; min-height: 5px;">
                                                @foreach($menu->children as $child)
                                                    <div class="nested-group-item mb-1" data-id="{{ $child->id }}">
                                                        <div class="list-group-item d-flex justify-content-between align-items-center menu-item rounded border-0 bg-light" data-id="{{ $child->id }}">
                                                            <div class="d-flex align-items-center">
                                                                <div class="drag-handle mr-3 text-muted" style="cursor: move;"><i class="fas fa-grip-vertical"></i></div>
                                                                <div class="icon-square-sm bg-soft-info text-info mr-3"><i class="{{ $child->icon ?? 'fas fa-chevron-right' }}"></i></div>
                                                                <span class="text-dark font-weight-600">{{ $child->name }}</span>
                                                            </div>
                                                            <span class="badge {{ $child->is_active ? 'badge-soft-success' : 'badge-soft-danger' }} pill-badge">{{ $child->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                                                        </div>

                                                        <div class="list-group list-group-flush sortable-menu ml-4 mt-1" data-parent-id="{{ $child->id }}" style="border-left: 2px dotted #e3e6f0; min-height: 2px;">
                                                            @foreach($child->children as $subChild)
                                                                <div class="nested-group-item" data-id="{{ $subChild->id }}">
                                                                    <div class="list-group-item d-flex justify-content-between align-items-center menu-item py-1 border-0 bg-transparent" data-id="{{ $subChild->id }}">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="drag-handle mr-3 text-muted" style="cursor: move; font-size: 0.8rem;"><i class="fas fa-grip-vertical"></i></div>
                                                                            <span class="text-dark small"><i class="{{ $subChild->icon ?? 'fas fa-minus' }} mr-2 text-muted"></i>{{ $subChild->name }}</span>
                                                                        </div>
                                                                        <span class="badge {{ $subChild->is_active ? 'badge-soft-success' : 'badge-soft-danger' }} pill-badge" style="font-size: 0.6rem;">{{ $subChild->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-5 mb-4">
                        <div class="card shadow border-0 rounded-lg config-card sticky-top" style="top: 80px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="m-0 font-weight-bold text-dark d-flex align-items-center">
                                    <i class="fas fa-sliders-h mr-2 text-primary"></i> Pengaturan Detail
                                </h6>
                            </div>
                            <div class="card-body pt-0 bg-light rounded-bottom-lg px-4 pb-4 pt-3">
                                <form id="formMenuDetail">
                                    <input type="hidden" id="menuDetailId">
                                    <div class="detail-input-group">
                                        <label class="premium-label">Nama Menu Display</label>
                                        <input type="text" id="menuDisplayName" name="name" class="form-control premium-input" placeholder="Masukkan nama display...">
                                    </div>

                                    <div class="mb-4">
                                        <label class="premium-label mb-2">Status Visibilitas</label>
                                        <div class="status-choice-group">
                                            <label class="status-choice-item">
                                                <input type="radio" name="menuStatus" value="active" id="statusActive">
                                                <div class="status-card choice-active">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Aktif</span>
                                                </div>
                                            </label>
                                            <label class="status-choice-item">
                                                <input type="radio" name="menuStatus" value="maintenance" id="statusMaint">
                                                <div class="status-card choice-maint">
                                                    <i class="fas fa-tools"></i>
                                                    <span>Maint</span>
                                                </div>
                                            </label>
                                            <label class="status-choice-item">
                                                <input type="radio" name="menuStatus" value="hidden" id="statusHidden">
                                                <div class="status-card choice-hidden">
                                                    <i class="fas fa-eye-slash"></i>
                                                    <span>Hidden</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-input-group">
                                        <label class="premium-label">Pesan Maintenance</label>
                                        <textarea id="menuMaintMessage" name="maintenanceMessage" class="form-control premium-textarea" rows="2" placeholder="Tulis pesan pemeliharaan..."></textarea>
                                    </div>
                                    
                                    <div class="detail-input-group mb-4">
                                        <label class="premium-label">Akses Plant</label>
                                        <div class="d-flex mt-2">
                                            <div class="custom-control custom-switch custom-switch-success custom-switch-md mr-4">
                                                <input type="checkbox" class="custom-control-input" id="plantJktCheckbox" name="plant_jkt" value="1">
                                                <label class="custom-control-label text-dark font-weight-bold" for="plantJktCheckbox" style="font-size: 0.85rem;">Jakarta</label>
                                            </div>
                                            <div class="custom-control custom-switch custom-switch-success custom-switch-md">
                                                <input type="checkbox" class="custom-control-input" id="plantKrwCheckbox" name="plant_krw" value="1">
                                                <label class="custom-control-label text-dark font-weight-bold" for="plantKrwCheckbox" style="font-size: 0.85rem;">Karawang</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="button" id="btnUpdateMenu" class="btn btn-warning btn-block shadow-sm rounded-pill font-weight-bold py-3 mt-2" style="letter-spacing: 1px; font-size: 0.85rem;">
                                        <i class="fas fa-save mr-2"></i> TERAPKAN PERUBAHAN
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Hak Akses Modul -->
            <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-4 d-flex flex-wrap justify-content-between align-items-center border-bottom-0 px-4">
                        <div>
                            <h6 class="m-0 font-weight-bold text-dark mb-1" id="permissionTitle" style="font-size: 1.1rem; letter-spacing: -0.3px;">Izin Modul</h6>
                            <p class="text-muted small mb-0" id="permissionSubtitle">Konfigurasi hak akses spesifik untuk setiap peran pengguna</p>
                        </div>
                        <div class="d-flex align-items-center mt-3 mt-md-0">
                            <!-- Mode Switcher -->
                            <div class="premium-input-group mr-2 mb-2 mb-md-0">
                                <span class="input-icon"><i class="fas fa-sliders-h"></i></span>
                                <select id="permissionMode" class="premium-input" style="min-width: 170px;">
                                    <option value="role">Berdasarkan Role</option>
                                    <option value="user">Berdasarkan User</option>
                                </select>
                            </div>

                            <!-- Role Selector -->
                            <div id="roleSelectorContainer" class="premium-input-group mr-2 mb-2 mb-md-0">
                                <span class="input-icon"><i class="fas fa-user-tag"></i></span>
                                <select id="roleSelector" class="premium-input" style="min-width: 150px;">
                                    @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ $role == $selectedRole ? 'selected' : '' }}>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- User Selector -->
                            <div id="userSelectorContainer" class="premium-input-group mr-2 d-none mb-2 mb-md-0">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <select id="userSelector" class="premium-input" style="min-width: 220px;">
                                    <option value="">Pilih User...</option>
                                    @foreach($users->sortBy('name') as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button" id="savePermissions" class="btn btn-dark rounded-pill px-4 shadow-sm btn-sm-modern h-100 py-2">
                                <i class="fas fa-save mr-2"></i> Simpan
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="permissions-split-container">
                            <!-- Left: Module Selector -->
                            <div class="module-sidebar">
                                <span class="module-sidebar-label">Pilih Modul</span>
                                <div class="module-nav-list">
                                    @php
                                        $parentMenus = \App\Models\AppMenu::whereNull('parent_id')->orderBy('order')->get();
                                    @endphp
                                    @foreach($parentMenus as $parent)
                                        <div class="module-nav-item {{ $loop->first ? 'active' : '' }}" data-target="module-{{ $parent->id }}">
                                            <i class="{{ $parent->icon ?? 'fas fa-th-large' }}"></i>
                                            <span>{{ $parent->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Right: Permission Details -->
                            <div class="permission-detail-panel">
                                @foreach($parentMenus as $parent)
                                <div id="module-{{ $parent->id }}" class="module-content-section {{ $loop->first ? '' : 'd-none' }}">
                                    <div class="panel-header">
                                        <h5 class="font-weight-bold text-dark mb-1">{{ $parent->name }}</h5>
                                        <p class="text-muted small mb-0">Konfigurasi izin untuk modul utama dan sub-modul di dalamnya.</p>
                                    </div>
                                    
                                    <div class="permission-grid-header">
                                        <div>Modul / Sub-Modul</div>
                                        <div>View</div>
                                        <div>Input</div>
                                        <div>Edit/Del</div>
                                        <div>Approve</div>
                                        <div>Export</div>
                                    </div>

                                    <!-- Parent Entry in Detail View -->
                                    <div class="permission-row-item">
                                        <div class="permission-name text-primary">
                                            <i class="fas fa-star mr-2 small"></i> Main Modul
                                        </div>
                                        @foreach(['view', 'input', 'edit', 'approve', 'export'] as $type)
                                        <div class="custom-control custom-switch custom-switch-success custom-switch-md d-inline-block">
                                            <input type="checkbox" class="custom-control-input parent-check" id="{{ $type }}_{{ $parent->id }}" {{ ($permissions[$parent->id]->{"can_$type"} ?? false) ? 'checked' : '' }} data-menu-id="{{ $parent->id }}" data-type="{{ $type }}">
                                            <label class="custom-control-label" for="{{ $type }}_{{ $parent->id }}"></label>
                                        </div>
                                        @endforeach
                                    </div>

                                    <!-- Children List -->
                                    @if($parent->children->isNotEmpty())
                                        @foreach($parent->children as $child)
                                        <div class="permission-row-item">
                                            <div class="permission-name pl-4">
                                                <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-3 small opacity-50"></i> {{ $child->name }}
                                            </div>
                                            @foreach(['view', 'input', 'edit', 'approve', 'export'] as $type)
                                            <div class="custom-control custom-switch custom-switch-success custom-switch-md d-inline-block">
                                                <input type="checkbox" class="custom-control-input child-check-{{ $parent->id }}" id="{{ $type }}_{{ $child->id }}" {{ ($permissions[$child->id]->{"can_$type"} ?? false) ? 'checked' : '' }} data-menu-id="{{ $child->id }}" data-type="{{ $type }}">
                                                <label class="custom-control-label" for="{{ $type }}_{{ $child->id }}"></label>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-5 text-muted small">
                                            <i class="fas fa-info-circle mr-1"></i> Tidak ada sub-modul untuk menu ini.
                                        </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Log Aktivitas -->
            <div class="tab-pane fade" id="activity-logs" role="tabpanel" aria-labelledby="activity-logs-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                        <h6 class="m-0 font-weight-bold text-dark">System Activity Logs</h6>
                        <button type="button" id="refreshLogs" class="btn btn-sm btn-outline-dark rounded-pill px-3 shadow-sm btn-sm-modern">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle custom-table table-minimalist mb-0 w-100" id="activityLogsTable">
                                <thead class="bg-white">
                                    <tr>
                                        <th width="30%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">User</th>
                                        <th width="15%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">Actions</th>
                                        <th width="35%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">Activity</th>
                                        <th width="20%" class="text-dark font-weight-bold pb-3 text-center border-bottom-0">Time</th>
                                    </tr>
                                </thead>
                                <tbody id="activityLogsBody">
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                                            Memuat data log...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="logsPagination" class="mt-4 d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Add User -->
    <div class="modal fade" id="modalAddUser" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Tambah Pengguna Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAddUser">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required placeholder="Contoh: Irfan Arfian">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Alamat Email</label>
                            <input type="email" name="email" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required placeholder="email@perusahaan.com">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Password</label>
                            <div class="password-field-wrapper">
                                <input type="password" name="password" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" placeholder="Opsional (Default: indoplat2526)">
                                <div class="password-toggle-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-dark">Role / Jabatan</label>
                                    <select name="role" class="form-control rounded-pill border-0 bg-light px-3" required>
                                        @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-dark">Lokasi Plant</label>
                                    <select name="plant_id" class="form-control rounded-pill border-0 bg-light px-3">
                                        <option value="">Global</option>
                                        @foreach($plants as $plant)
                                            @if(strtoupper($plant->name) !== 'TOTAL')
                                                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Inisial (Opsional)</label>
                            <input type="text" name="initials" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" placeholder="Contoh: IA" maxlength="5">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="modalEditUser" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-edit mr-2"></i>Edit Pengguna</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditUser">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_user_id">
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Nama Lengkap</label>
                            <input type="text" name="name" id="edit_name" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Alamat Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Password Baru</label>
                            <div class="password-field-wrapper">
                                <input type="password" name="password" id="edit_password" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" placeholder="Biarkan kosong jika tidak ingin mengubah password">
                                <div class="password-toggle-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-dark">Role / Jabatan</label>
                                    <select name="role" id="edit_role" class="form-control rounded-pill border-0 bg-light px-3" required>
                                        @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-dark">Lokasi Plant</label>
                                    <select name="plant_id" id="edit_plant_id" class="form-control rounded-pill border-0 bg-light px-3">
                                        <option value="">Global</option>
                                        @foreach($plants as $plant)
                                            @if(strtoupper($plant->name) !== 'TOTAL')
                                                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Inisial (Opsional)</label>
                            <input type="text" name="initials" id="edit_initials" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" maxlength="5">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">Perbarui Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div class="modal fade" id="modalResetPassword" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-body p-4 text-center">
                    <div class="icon-circle bg-soft-warning text-warning mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="fas fa-key"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">Reset Password?</h5>
                    <p class="small text-muted mb-4">Password user <strong id="reset_user_name"></strong> akan direset menjadi default: <b>password123</b></p>
                    
                    <button type="button" id="confirmResetPassword" class="btn btn-dark btn-block rounded-pill shadow-sm font-weight-bold mb-2">Ya, Reset Sekarang</button>
                    <button type="button" class="btn btn-outline-light btn-block rounded-pill text-muted small" data-dismiss="modal">Batalkan</button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<style>
    /* Modern UI Customization */
    body {
        background-color: #f4f6f9;
    }
    
    .rounded-lg { border-radius: 12px !important; }
    .rounded-top-lg { border-top-left-radius: 12px; border-top-right-radius: 12px; }
    .rounded-bottom-lg { border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
    
    .btn-sm-modern {
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.4rem 1rem;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .btn-sm-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.15) !important;}
    
    .btn-icon {
        width: 32px; height: 32px;
        display: inline-flex;
        align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .btn-icon:hover { transform: scale(1.1); }

    /* Choice Cards Styling */
    .status-choice-group {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .status-choice-item {
        position: relative;
        cursor: pointer;
    }
    
    .status-choice-item input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0; width: 0;
    }
    
    .status-card {
        padding: 12px 8px;
        border-radius: 10px;
        border: 2px solid #eaecf0;
        background: #fff;
        text-align: center;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #667085;
    }
    
    .status-choice-item:hover .status-card {
        border-color: #d0d5dd;
        background: #f9fafb;
    }
    
    .status-choice-item input:checked + .status-card.choice-active {
        border-color: #1cc88a;
        background: #f0fff4;
        color: #1cc88a;
        box-shadow: 0 4px 10px rgba(28, 200, 138, 0.1);
    }
    
    .status-choice-item input:checked + .status-card.choice-maint {
        border-color: #f6c23e;
        background: #fffdf0;
        color: #f6c23e;
        box-shadow: 0 4px 10px rgba(246, 194, 62, 0.1);
    }
    
    .status-choice-item input:checked + .status-card.choice-hidden {
        border-color: #e74a3b;
        background: #fff5f5;
        color: #e74a3b;
        box-shadow: 0 4px 10px rgba(231, 74, 59, 0.1);
    }

    .status-card i {
        font-size: 1.2rem;
        margin-bottom: 6px;
    }
    
    .status-card span {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Refined Detail Card */
    .detail-input-group {
        background: #fff;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid #eaecf0;
        margin-bottom: 1.25rem;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    }
    
    .detail-input-group:focus-within {
        border-color: #4e73df;
        box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
    }
    
    .premium-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: block;
    }

    .premium-input {
        border: none !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        font-weight: 600;
        color: #1d2939;
        font-size: 0.95rem;
        height: auto !important;
    }

    .premium-textarea {
        border: none !important;
        resize: none;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        font-weight: 500;
        color: #344054;
        font-size: 0.85rem;
    }

    /* Premium Input Group for Role Selector */
    .premium-input-group {
        position: relative;
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        height: 44px;
    }

    .premium-input-group:hover {
        border-color: #d0d0d0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

    .premium-input-group:focus-within {
        border-color: #2d3436;
        box-shadow: 0 0 0 4px rgba(45, 52, 54, 0.05);
    }

    .premium-input-group .input-icon {
        position: absolute;
        left: 16px;
        color: #4a4a4a;
        font-size: 0.95rem;
        pointer-events: none;
        z-index: 2;
        opacity: 0.8;
    }

    .premium-input-group .premium-input {
        padding-left: 44px !important;
        padding-right: 32px !important;
        width: 100%;
        cursor: pointer;
        background: transparent !important;
        border: none !important;
        font-weight: 600;
        color: #2d3436;
        font-size: 0.9rem;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }
    
    .premium-input-group::after {
        content: '\f078';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 14px;
        font-size: 0.65rem;
        color: #a0a0a0;
        pointer-events: none;
        transition: transform 0.2s;
    }
    
    .premium-input-group:focus-within::after {
        transform: rotate(180deg);
        color: #2d3436;
    }

    .bg-light-faint {
        background-color: rgba(248, 249, 252, 0.6);
    }

    .font-weight-medium {
        font-weight: 500;
    }

    /* Animations */
    @keyframes fadeInCustom {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-quick {
        animation: fadeInCustom 0.3s ease-out forwards;
    }
    
    /* Sidebar Minimalist Nav */
    .custom-nav-pills-minimal .nav-link {
        border-radius: 10px;
        padding: 0.85rem 1.25rem;
        margin-bottom: 0.25rem;
        color: #6e707e;
        transition: all 0.2s ease;
        background-color: transparent;
        border: none;
    }
    .custom-nav-pills-minimal .nav-link:hover {
        background-color: rgba(78, 115, 223, 0.05);
        color: #4e73df;
    }
    .custom-nav-pills-minimal .nav-link.active {
        background-color: #fff;
        color: #4e73df;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .custom-nav-pills-minimal .nav-link i {
        font-size: 1.1rem;
        width: 20px;
        transition: color 0.2s ease;
    }
    .custom-nav-pills-minimal .nav-link.active i {
        color: #4e73df !important;
    }
    
    .font-weight-medium {
        font-weight: 500;
    }

    /* Split-View Matrix Design */
    .permissions-split-container {
        display: flex;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #eaecf0;
        min-height: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }

    .module-sidebar {
        width: 250px;
        background: #fcfcfd;
        border-right: 1px solid #eaecf0;
        padding: 1.5rem 0.75rem;
    }

    .module-sidebar-label {
        font-size: 0.65rem;
        font-weight: 700;
        color: #98a2b3;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0 1rem 0.75rem;
        display: block;
    }

    .module-nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        color: #475467;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
    }

    .module-nav-item:hover {
        background: #f9fafb;
        color: #1d2939;
    }

    .module-nav-item.active {
        background: #fff;
        color: #4e73df;
        border-color: #eaecf0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .module-nav-item i {
        width: 20px;
        font-size: 1rem;
        margin-right: 12px;
        transition: color 0.2s;
    }

    .module-nav-item.active i {
        color: #4e73df;
    }

    .permission-detail-panel {
        flex: 1;
        padding: 2rem;
        background: #fff;
    }

    .panel-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px dashed #eaecf0;
    }

    .permission-grid-header {
        display: grid;
        grid-template-columns: 1fr repeat(5, 80px);
        gap: 15px;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 10px;
        margin-bottom: 1rem;
    }

    .permission-grid-header > div {
        font-size: 0.7rem;
        font-weight: 700;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
    }

    .permission-grid-header > div:first-child {
        text-align: left;
    }

    .permission-row-item {
        display: grid;
        grid-template-columns: 1fr repeat(5, 80px);
        gap: 15px;
        padding: 1.25rem 1rem;
        border-bottom: 1px solid #f2f4f7;
        align-items: center;
        transition: background 0.2s;
    }

    .permission-row-item:hover {
        background: #fafafa;
    }

    .permission-name {
        font-weight: 600;
        color: #344054;
        font-size: 0.9rem;
    }

    .permission-row-item .custom-control {
        display: inline-block;
        justify-self: center;
    }

    .empty-state-permissions {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #98a2b3;
        text-align: center;
        padding: 4rem;
    }

    .empty-state-permissions i {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    /* Animations */
    @keyframes panelSlideIn {
        from { opacity: 0; transform: translateX(10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .panel-content-animate {
        animation: panelSlideIn 0.3s ease-out forwards;
    }
    
    /* Icon utilities */
    .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }
    .icon-circle {
        width: 40px; height: 40px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .icon-square {
        width: 36px; height: 36px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .avatar-modern {
        width: 44px; height: 44px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; font-weight: 800;
        background: #ffffff;
        color: #495057;
        border: 2px solid #f8f9fa;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        flex-shrink: 0;
        text-transform: uppercase;
        transition: all 0.2s ease;
    }
    .avatar-modern:hover {
        transform: scale(1.05);
        border-color: #e9ecef;
        background-color: #f8f9fa;
    }
    
    /* Software Badge Colors */
    .bg-soft-primary { background-color: rgba(78, 115, 223, 0.15); }
    .bg-soft-success { background-color: rgba(28, 200, 138, 0.15); }
    .bg-soft-info { background-color: rgba(54, 185, 204, 0.15); }
    .bg-soft-warning { background-color: rgba(246, 194, 62, 0.15); }
    
    .badge-soft-primary { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .badge-soft-success { background-color: rgba(28, 200, 138, 0.1); color: #1cc88a; }
    .badge-soft-info { background-color: rgba(54, 185, 204, 0.1); color: #36b9cc; }
    .badge-soft-warning { background-color: rgba(246, 194, 62, 0.1); color: #f6c23e; }
    .pill-badge { font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 1rem; font-weight: 700;}
    
    /* Alert Soft */
    .alert-soft-success {
        background-color: rgba(28, 200, 138, 0.08);
        border-left: 4px solid #1cc88a !important;
    }
    
    /* Custom Tables */
    .custom-table th { font-size: 0.8rem; letter-spacing: 0.5px; background-color: transparent !important; color: #858796 !important; border-top: none; }
    .custom-table td { font-size: 0.85rem; padding: 1rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
    .permission-table td { padding: 0.75rem 0.5rem; }
    
    /* Custom Switch Colors */
    .custom-switch-primary .custom-control-input:checked ~ .custom-control-label::before { background-color: #4e73df; border-color: #4e73df; }
    .custom-switch-success .custom-control-input:checked ~ .custom-control-label::before { background-color: #1cc88a; border-color: #1cc88a; }
    .custom-switch-info .custom-control-input:checked ~ .custom-control-label::before { background-color: #36b9cc; border-color: #36b9cc; }
    .custom-switch-warning .custom-control-input:checked ~ .custom-control-label::before { background-color: #f6c23e; border-color: #f6c23e; }
    .custom-switch-secondary .custom-control-input:checked ~ .custom-control-label::before { background-color: #858796; border-color: #858796; }
    
    /* Animations */
    .slide-in {
        animation: slideInUp 0.4s ease forwards;
        opacity: 0;
        transform: translateY(15px);
    }
    @keyframes slideInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Sortable Menu */
    .sortable-menu { padding: 0.5rem; }
    .menu-item { border: none !important; margin-bottom: 0.5rem; border-radius: 8px !important; transition: all 0.2s; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .menu-item.selected { border-left: 4px solid #4e73df !important; background-color: #f8f9fc; }
    .menu-item:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: grab; }
    .drag-handle { cursor: grab; display: inline-flex; padding: 0.5rem; margin: -0.5rem; }
    
    /* Config Card Focus */
    .border-left-warning { border-left: 4px solid #f6c23e !important; }
    
    /* Minimalist Professional Table Override Global */
    .table.table-minimalist {
        border-collapse: collapse;
        border: none !important;
    }
    /* Ultra-Specific Override for White Header - Refined */
    .table-minimalist thead th,
    .table-minimalist thead td,
    .table-minimalist thead tr,
    table.table-minimalist thead th,
    table.table-minimalist thead tr,
    body .table.table-minimalist thead th,
    body .table.table-minimalist thead tr,
    #users table.table-minimalist thead th,
    #users table.table-minimalist thead tr,
    #activity-logs table.table-minimalist thead th,
    #activity-logs table.table-minimalist thead tr {
        background-color: #ffffff !important;
        background: #ffffff !important;
        color: #2e3b4e !important;
        border: none !important;
        border-bottom: 2px solid #f8f9fa !important;
        padding: 1.25rem 0.75rem !important;
        font-weight: 700 !important;
        font-size: 0.72rem;
        letter-spacing: 0.05rem;
        text-transform: uppercase;
    }
    
    .table.table-minimalist tbody td {
        background-color: #ffffff !important;
        border: none !important;
        border-top: 1px solid #f8f9fa !important;
        vertical-align: middle;
        color: #4a4a4a;
        padding: 1.1rem 0.75rem !important;
    }
    .table-row-hover {
        transition: background-color 0.2s ease;
    }
    .table-row-hover:hover {
        background-color: #fdfdfe !important;
    }
    .table-row-hover:hover td {
        background-color: transparent !important;
    }
    
    /* Buttons Grayscale */
    .btn-dark {
        background-color: #2e3b4e;
        border-color: #2e3b4e;
        color: #fff;
    }
    .btn-dark:hover {
        background-color: #1a2533;
        border-color: #1a2533;
        transform: translateY(-2px);
    }
    
    /* Modern "Flat & Smooth" Toggle Switch */
    .custom-switch-md {
        padding-left: 2.8rem;
    }
    .custom-switch-md .custom-control-label::before {
        height: 1.4rem;
        width: 2.6rem;
        border-radius: 100px;
        background-color: #eaedf2;
        border: none !important;
        top: 0;
        left: -2.8rem;
        transition: background-color 0.25s ease;
    }
    .custom-switch-md .custom-control-label::after {
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background-color: #fff;
        top: 0.2rem;
        left: calc(-2.8rem + 0.2rem);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.25s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.2rem) !important;
    }
    .custom-switch-success .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #2ec4b6 !important; /* Modern Teal-Green */
    }
    
    /* Remove any lingering blue text/icons */
    .text-primary { color: #2d3436 !important; }
    .btn-primary { background-color: #2d3436; border-color: #2d3436; }
    .btn-primary:hover { background-color: #000; border-color: #000; }
    .icon-square.bg-soft-primary { background-color: #f1f3f7; color: #636e72; }
    .fa-circle-notch.text-primary { color: #b2bec3 !important; }
    
    .btn-dark {
        background-color: #2d3436;
        border-color: #2d3436;
    }

    /* Password Toggle Styling */
    .password-field-wrapper {
        position: relative;
    }
    .password-toggle-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #a0a0a0;
        transition: all 0.2s ease;
        z-index: 10;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .password-toggle-icon:hover {
        color: #2d3436;
        background-color: rgba(0,0,0,0.05);
    }

    /* Global Override to prevent forced uppercase */
    .custom-nav-pills-minimal .nav-link,
    .modal-title,
    .form-group label,
    .btn,
    .table th,
    input, 
    select,
    textarea {
        text-transform: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
            $.get(`{{ url('admin/settings/menus') }}/${id}`, function(menu) {
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
                _token: '{{ csrf_token() }}',
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
                url: `{{ url('admin/settings/menus') }}/${menuId}`,
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

            $.post('{{ route("admin.settings.menus.order") }}', {
                _token: '{{ csrf_token() }}',
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
        
        // Handle module switching in Split-View
        $('.module-nav-item').on('click', function() {
            const targetId = $(this).data('target');
            
            // UI Update: Sidebar
            $('.module-nav-item').removeClass('active');
            $(this).addClass('active');
            
            // UI Update: Content Panel
            $('.module-content-section').addClass('d-none').removeClass('panel-content-animate');
            $(`#${targetId}`).removeClass('d-none').addClass('panel-content-animate');
        });

        // Sync parent checkbox with children (Modified for Split-View)
        $('.parent-check').on('change', function() {
            const parentId = $(this).data('menu-id');
            const type = $(this).data('type');
            const isChecked = $(this).is(':checked');
            
            $(`.child-check-${parentId}[data-type="${type}"]`).prop('checked', isChecked);
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
            $.get('{{ route("admin.settings.permissions") }}', params, function(data) {
                resetCheckboxes();
                // Check based on data
                Object.keys(data).forEach(menuId => {
                    const perm = data[menuId];
                    $(`#view_${menuId}`).prop('checked', !!perm.can_view);
                    $(`#input_${menuId}`).prop('checked', !!perm.can_input);
                    $(`#edit_${menuId}`).prop('checked', !!perm.can_edit);
                    $(`#approve_${menuId}`).prop('checked', !!perm.can_approve);
                    $(`#export_${menuId}`).prop('checked', !!perm.can_export);
                });
            });
        }

        function resetCheckboxes() {
            $('input[type="checkbox"].custom-control-input[id^="view_"], \
               input[type="checkbox"].custom-control-input[id^="input_"], \
               input[type="checkbox"].custom-control-input[id^="edit_"], \
               input[type="checkbox"].custom-control-input[id^="approve_"], \
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
                    export: $(`#export_${id}`).is(':checked')
                };
            });
            
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: '{{ route("admin.settings.permissions.save") }}',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    _token: '{{ csrf_token() }}',
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

        // --- USER MANAGEMENT AJAX ---

        // Edit User Button Click
        $('.edit-user').on('click', function() {
            const btn = $(this);
            $('#edit_user_id').val(btn.data('id'));
            $('#edit_name').val(btn.data('name'));
            $('#edit_email').val(btn.data('email'));
            $('#edit_role').val(btn.data('role'));
            $('#edit_plant_id').val(btn.data('plant'));
            $('#edit_initials').val(btn.data('initials'));
            $('#modalEditUser').modal('show');
        });

        // Reset Password Button Click
        let resetUserId = null;
        $('.reset-password').on('click', function() {
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
                url: `{{ url('admin/settings/users') }}/${resetUserId}/reset-password`,
                type: 'PATCH',
                data: { _token: '{{ csrf_token() }}' },
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
                url: '{{ route("admin.settings.users.store") }}',
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
                url: `{{ url('admin/settings/users') }}/${userId}`,
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

        $('.delete-user').on('click', function() {
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
                        url: `{{ url('admin/settings/users') }}/${userId}`,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
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

        // Toggle User Status AJAX
        $('.toggle-user-status').on('change', function() {
            const checkbox = $(this);
            const userId = checkbox.data('id');
            const isActive = checkbox.is(':checked') ? 1 : 0;
            
            // Disable temporarily to prevent multiple clicks
            checkbox.prop('disabled', true);

            $.ajax({
                url: `{{ url('admin/settings/users') }}/${userId}/status`,
                type: 'PATCH',
                data: { 
                    _token: '{{ csrf_token() }}',
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
</script>
    <!-- Modal Import User -->
    <div class="modal fade" id="modalImportUser" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered" role="document" style="z-index: 1061; pointer-events: auto;">
            <div class="card shadow border-0 rounded-lg w-100 slide-up border-left-primary" style="z-index: 1062; pointer-events: auto;">
                <div class="card-header bg-white py-3 d-flex align-items-center border-bottom-0">
                    <div class="bg-light p-2 rounded-circle mr-3">
                        <i class="fas fa-file-import text-primary"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold text-dark">Upload Konfigurasi User</h6>
                    <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.settings.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 pt-0" style="pointer-events: auto; position: relative; z-index: 1063;">
                        <div class="mb-4 text-center">
                            <i class="fas fa-file-csv fa-4x text-light mb-3"></i>
                            <p class="small text-muted mb-0 font-weight-500">Pilih file CSV hasil ekspor untuk mengupdate data user secara otomatis.</p>
                        </div>
                        <div class="form-group mb-0" style="pointer-events: auto; position: relative; z-index: 1064;">
                            <label class="font-weight-bold text-dark">File CSV</label>
                            <input type="file" name="file" id="importFile" accept=".csv,text/csv" class="form-control-file border p-2 rounded bg-light" required style="cursor: pointer; position: relative; z-index: 1065; pointer-events: auto !important;">
                        </div>
                        <div class="alert alert-info border-0 rounded-lg small mb-0 mt-3 shadow-sm bg-light">
                            <i class="fas fa-info-circle mr-2 text-info"></i>
                            <span class="text-dark"><strong>Tips:</strong> Gunakan file hasil **Ekspor Konfigurasi** sebagai template agar format data tetap konsisten.</span>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0" style="pointer-events: auto;">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">Upload & Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script to handle custom file input -->
    <script>
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
    </script>

    <!-- Moment.js for Log Formatting (Local for Offline Use) -->
    <script src="{{ asset('js/vendor/moment.min.js') }}"></script>
    <!-- SortableJS for Menu Reordering -->
    <script src="{{ asset('js/vendor/Sortable.min.js') }}"></script>
    
    <script src="{{ asset('js/vendor/moment-id.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Set Indonesian locale for Moment.js
            moment.locale('id');
            
            // Activity Logs Logic
        function fetchActivityLogs(page = 1) {
            $('#activityLogsBody').html(`
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                        Memuat data log...
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ route('admin.settings.activity_logs') }}?page=" + page,
                type: 'GET',
                success: function(response) {
                    renderLogs(response.data);
                    renderPagination(response);
                },
                error: function() {
                    $('#activityLogsBody').html('<tr><td colspan="4" class="text-center py-5 text-danger">Gagal memuat data log.</td></tr>');
                }
            });
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
                                ${log.model_type ? `<small class="text-muted mt-1 d-block" style="font-size: 0.65rem;">Model: ${log.model_type.split('\\').pop()} #${log.model_id}</small>` : ''}
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

        $(document).on('click', '#logsPagination .page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) fetchActivityLogs(page);
        });
    });
</script>
@endpush
