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
        <div class="card shadow-sm border-0 bg-white settings-sidebar-container">
            <div class="card-body p-0">
                <div class="nav flex-column settings-sidebar-nav" id="settings-tabs" role="tablist" aria-orientation="vertical">
                    
                    <!-- Group: Sistem -->
                    <div class="settings-sidebar-header">
                        <i class="fas fa-laptop-code mr-2 text-secondary"></i> Sistem
                    </div>
                    <a class="nav-link active settings-sidebar-item" id="general-tab" data-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">
                        <span>Umum</span>
                    </a>
                    <a class="nav-link settings-sidebar-item" id="activity-logs-tab" data-toggle="pill" href="#activity-logs" role="tab" aria-controls="activity-logs" aria-selected="false">
                        <span>Log Aktivitas</span>
                    </a>
                    <a class="nav-link settings-sidebar-item" id="dashboard-layout-tab" data-toggle="pill" href="#dashboard-layout" role="tab" aria-controls="dashboard-layout" aria-selected="false">
                        <span>Layout Dashboard</span>
                    </a>
                    <a class="nav-link settings-sidebar-item" id="header-dokumen-tab" data-toggle="pill" href="#header-dokumen" role="tab" aria-controls="header-dokumen" aria-selected="false">
                        <span>Header Dokumen</span>
                    </a>

                    <!-- Group: Pengguna & Akses -->
                    <div class="settings-sidebar-header">
                        <i class="fas fa-users-cog mr-2 text-secondary"></i> Pengguna & Akses
                    </div>
                    <a class="nav-link settings-sidebar-item" id="users-tab" data-toggle="pill" href="#users" role="tab" aria-controls="users" aria-selected="false">
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a class="nav-link settings-sidebar-item" id="permissions-tab" data-toggle="pill" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">
                        <span>Hak Akses Modul</span>
                    </a>

                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-xl-9 col-lg-8">
        <div class="tab-content" id="settings-tabContent">
            
            <!-- Tab 0: Pengaturan Umum -->
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center border-bottom-0 px-4">
                        <div>
                            <h6 class="m-0 font-weight-bold text-dark mb-1" style="font-size: 1.1rem; letter-spacing: -0.3px;">Konfigurasi Umum</h6>
                            <p class="text-muted small mb-0">Kostumisasi fitur dan parameter global sistem</p>
                        </div>
                        <button type="button" id="saveGeneralSettings" class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm-modern h-100 py-2">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                    <div class="card-body px-4 pt-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <h6 class="font-weight-bold text-dark mb-3">
                                    Keamanan & Kontrol Akses
                                </h6>
                                
                                <div class="premium-setting-item d-flex align-items-center justify-content-between p-3 rounded-xl mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box-modern bg-soft-info text-info mr-3">
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">Pengamanan Daily Approval</h6>
                                            <small class="text-muted">Kunci input data jika rate approval < 90% setelah jam 12:00 siang</small>
                                        </div>
                                    </div>
                                    <div class="custom-control custom-switch custom-switch-success custom-switch-md">
                                        <input type="checkbox" class="custom-control-input" id="dailyApprovalGate" 
                                            {{ ($generalSettings['daily_approval_gate_enabled']->value ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="dailyApprovalGate"></label>
                                    </div>
                                </div>

                                <div class="premium-setting-item p-3 rounded-xl mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-tags mr-2 text-primary"></i>Kategori First Piece Approval (FPA)</h6>
                                            <small class="text-muted">Daftar opsi kategori yang tampil pada form input First Piece Approval (pisahkan tiap kategori dengan baris baru)</small>
                                        </div>
                                    </div>
                                    @php
                                        $categoriesSetting = isset($generalSettings['fpa_categories']) ? $generalSettings['fpa_categories']->value : null;
                                        if ($categoriesSetting !== null && $categoriesSetting !== '') {
                                            $decoded = json_decode($categoriesSetting, true);
                                            if (is_array($decoded)) {
                                                $categoriesText = implode("\n", $decoded);
                                            } else {
                                                $categoriesText = $categoriesSetting;
                                            }
                                        } else {
                                            $categoriesText = implode("\n", \App\Models\GeneralSetting::getFpaCategories());
                                        }
                                    @endphp
                                    <textarea id="fpaCategoriesInput" class="form-control mt-2" rows="6" style="font-size: 0.85rem; background: #fff;" placeholder="awal produksi&#10;operator istirahat&#10;...">{!! e($categoriesText) !!}</textarea>
                                </div>

                                <hr class="my-4" style="border-top: 1px dashed #e2e8f0;">

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h6 class="font-weight-bold text-dark mb-1">Manajemen Next Process</h6>
                                        <p class="text-muted small mb-0">Kelola opsi proses selanjutnya berdasarkan plant</p>
                                    </div>
                                    <button type="button" class="btn btn-dark rounded-pill px-4 shadow-sm btn-sm-modern py-2" data-toggle="modal" data-target="#modalAddNextProcess">
                                        <i class="fas fa-plus mr-2"></i> Tambah Opsi
                                    </button>
                                </div>

                                <div class="next-process-accordion" id="nextProcessAccordion">
                                    @foreach($qcModules as $moduleKey => $moduleLabel)
                                    <div class="card border-0 mb-3 shadow-sm rounded-lg overflow-hidden slide-in">
                                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" 
                                             id="heading{{ $moduleKey }}" 
                                             data-toggle="collapse" 
                                             data-target="#collapse{{ $moduleKey }}" 
                                             aria-expanded="false"
                                             style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-light text-dark mr-3" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                    <i class="fas fa-layer-group" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $moduleLabel }}</h6>
                                                    <p class="text-muted small mb-0">{{ $nextProcesses->where('module', $moduleKey)->count() }} Opsi Proses</p>
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-down text-muted transition-arrow"></i>
                                        </div>
                                        <div id="collapse{{ $moduleKey }}" class="collapse" aria-labelledby="heading{{ $moduleKey }}" data-parent="#nextProcessAccordion">
                                            <div class="card-body p-0 border-top">
                                                <div class="table-responsive">
                                                    <table class="table table-borderless align-middle custom-table table-minimalist mb-0 w-100">
                                                        <thead class="bg-light text-muted">
                                                            <tr>
                                                                <th width="10%" class="font-weight-bold py-2 text-center small" style="text-transform: uppercase;">Order</th>
                                                                <th width="35%" class="font-weight-bold py-2 text-left small" style="text-transform: uppercase;">Nama Proses</th>
                                                                <th width="20%" class="font-weight-bold py-2 text-left small" style="text-transform: uppercase;">Plant</th>
                                                                <th width="15%" class="font-weight-bold py-2 text-center small" style="text-transform: uppercase;">Status</th>
                                                                <th width="20%" class="font-weight-bold py-2 text-center small" style="text-transform: uppercase;">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($nextProcesses->where('module', $moduleKey) as $process)
                                                            <tr class="table-row-hover" style="border-bottom: 1px solid #f8f9fa;">
                                                                <td class="text-center">
                                                                    <span class="badge badge-light rounded-pill px-2">{{ $process->order }}</span>
                                                                </td>
                                                                <td>
                                                                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $process->name }}</h6>
                                                                </td>
                                                                <td>
                                                                    @if($process->plant && strtoupper($process->plant->name) !== 'TOTAL')
                                                                        <span class="badge badge-pill px-2 py-1 {{ strtolower($process->plant->code ?? '') === 'jakarta' ? 'badge-info' : 'badge-primary' }}" style="font-size: 0.65rem;">
                                                                            {{ $process->plant->name }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-pill px-2 py-1 badge-secondary" style="font-size: 0.65rem;">
                                                                            Global
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="custom-control custom-switch custom-switch-success custom-switch-md d-inline-block">
                                                                        <input type="checkbox" class="custom-control-input toggle-process-status" id="processStatus{{ $process->id }}" data-id="{{ $process->id }}" data-plant="{{ $process->plant_id }}" data-module="{{ $process->module }}" {{ $process->is_active ? 'checked' : '' }}>
                                                                        <label class="custom-control-label" for="processStatus{{ $process->id }}"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm edit-process" 
                                                                            data-id="{{ $process->id }}" 
                                                                            data-name="{{ $process->name }}" 
                                                                            data-order="{{ $process->order }}"
                                                                            data-plant="{{ $process->plant_id }}"
                                                                            data-module="{{ $process->module }}"
                                                                            data-toggle="tooltip" title="Edit Opsi">
                                                                            <i class="fas fa-pen text-primary" style="font-size: 0.7rem;"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm delete-process ml-1" 
                                                                            data-id="{{ $process->id }}" 
                                                                            data-name="{{ $process->name }}" 
                                                                            data-toggle="tooltip" title="Hapus Opsi">
                                                                            <i class="fas fa-trash text-danger" style="font-size: 0.7rem;"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4 text-muted small">Belum ada opsi proses untuk modul ini.</td>
                                                            </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Dashboard Layout -->
            <div class="tab-pane fade" id="dashboard-layout" role="tabpanel" aria-labelledby="dashboard-layout-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center border-bottom-0 px-4">
                        <div>
                            <h6 class="m-0 font-weight-bold text-dark mb-1" style="font-size: 1.1rem; letter-spacing: -0.3px;">Layout Dashboard</h6>
                            <p class="text-muted small mb-0">Konfigurasi visibilitas grafik dashboard berdasarkan Role</p>
                        </div>
                        <div class="d-flex align-items-center mt-3 mt-md-0">
                            <!-- Role Selector -->
                            <div class="premium-input-group mr-3 mb-2 mb-md-0">
                                <span class="input-icon"><i class="fas fa-user-tag"></i></span>
                                <select id="dashboardRoleSelector" class="premium-input" style="min-width: 150px;">
                                    @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ $role == $selectedRole ? 'selected' : '' }}>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" id="saveDashboardLayout" class="btn btn-dark rounded-pill px-4 shadow-sm btn-sm-modern h-100 py-2">
                                <i class="fas fa-save mr-2"></i> Simpan
                            </button>
                        </div>
                    </div>
                    <div class="card-body px-4 pt-0">
                        <div class="alert alert-info border-0 shadow-sm" style="border-radius: 10px;">
                            <i class="fas fa-info-circle mr-2"></i> Secara default, jika tidak ada konfigurasi, semua grafik akan ditampilkan.
                        </div>
                        
                        <div class="row" id="dashboardLayoutCheckboxes">
                            @php
                                $dashboardWidgets = [
                                    'chartClaimJakarta' => 'Claim Jakarta',
                                    'chartClaimKarawang' => 'Claim Karawang',
                                    'chartClaimFrequency' => 'Frekuensi Claim (Total)',
                                    'chartJakarta' => 'Approval Jakarta (Bar/Line Chart)',
                                    'gauge-jakarta' => 'Approval Daily Jakarta (Gauge)',
                                    'chartKarawang' => 'Approval Karawang (Bar/Line Chart)',
                                    'gauge-karawang' => 'Approval Daily Karawang (Gauge)',
                                    'chartNgJakarta' => 'Monitoring Rate NG Jakarta',
                                    'chartNgKarawang' => 'Monitoring Rate NG Karawang',
                                    'productionJakarta' => 'Produksi Sub Assy Jakarta',
                                    'productionKarawang' => 'Produksi Sub Assy Karawang',
                                    'injectionJakarta' => 'Produksi Injection Jakarta',
                                    'injectionKarawang' => 'Produksi Injection Karawang',
                                    'chartContainer' => 'Approval [Plant] (Single View)',
                                    'gauge-total' => 'Approval Daily [Plant] (Single View)',
                                    'chartNgSingle' => 'Monitoring Rate NG [Plant] (Single View)',
                                    'productionSingle' => 'Produksi Sub Assy [Plant] (Single View)',
                                    'injectionSingle' => 'Produksi Injection [Plant] (Single View)',
                                    'monitoringPlating' => 'Produksi Plating',
                                    'monitoringPainting' => 'Produksi Painting',
                                    'monitoringCrossCutPlating' => 'Cross Cut Plating',
                                    'monitoringCrossCutPainting' => 'Cross Cut Painting',
                                    'monitoringDoubleTape' => 'Double Tape',
                                ];
                            @endphp

                            @foreach($dashboardWidgets as $widgetId => $widgetLabel)
                                <div class="col-md-6 mb-3">
                                    <div class="premium-setting-item d-flex align-items-center justify-content-between p-3 rounded-xl h-100" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $widgetLabel }}</h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">ID: {{ $widgetId }}</small>
                                            </div>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-success custom-switch-md">
                                            <input type="checkbox" class="custom-control-input dashboard-layout-toggle" id="layout_{{ $widgetId }}" data-widget-id="{{ $widgetId }}" checked>
                                            <label class="custom-control-label" for="layout_{{ $widgetId }}"></label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 1: Manajemen Akun -->
            <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                            <h6 class="m-0 font-weight-bold text-dark letter-spacing-1 font-size-sm mb-3 mb-md-0">Daftar Pengguna</h6>
                            
                            <div class="d-flex flex-column flex-sm-row gap-2 w-100 justify-content-end align-items-center" style="max-width: 550px;">
                                <div class="input-group rounded-pill overflow-hidden bg-white" style="border: 1px solid #e2e8f0; height: 38px;">
                                    <div class="input-group-prepend h-100">
                                        <span class="input-group-text bg-transparent border-0 text-muted px-3 h-100 d-flex align-items-center justify-content-center" id="basic-addon2">
                                            <i class="fas fa-search" style="color: #94a3b8;"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="liveSearchUser" class="form-control bg-transparent border-0 font-size-sm shadow-none no-autoupper pl-0 h-100 d-flex align-items-center" placeholder="Cari pengguna..." aria-label="Search" aria-describedby="basic-addon2" style="color: #475569; padding-top: 0; padding-bottom: 0;">
                                </div>
                                
                                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0 ml-sm-2 flex-wrap flex-sm-nowrap">
                                    <button class="btn bg-white rounded-pill px-3 btn-sm-modern d-inline-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalImportUser" style="height: 38px; white-space: nowrap; border: 1px solid #cbd5e1; color: #64748b; font-weight: 500;">
                                        <i class="fas fa-upload fa-sm mr-2" style="color: #94a3b8;"></i> <span>Upload</span>
                                    </button>
                                    <a href="{{ route('admin.settings.users.export') }}" class="btn bg-white rounded-pill px-3 btn-sm-modern no-loader d-inline-flex align-items-center justify-content-center" style="height: 38px; white-space: nowrap; border: 1px solid #cbd5e1; color: #64748b; font-weight: 500;">
                                        <i class="fas fa-download fa-sm mr-2" style="color: #94a3b8;"></i> <span>Ekspor</span>
                                    </a>
                                    <button class="btn rounded-pill px-4 btn-sm-modern text-nowrap ml-sm-2 d-inline-flex align-items-center justify-content-center text-white" data-toggle="modal" data-target="#modalAddUser" style="height: 38px; background-color: #2d3748; border: none; font-weight: 500;">
                                        <i class="fas fa-user-plus fa-sm text-white-50 mr-2"></i> <span>Tambah Pengguna</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive px-1">
                            <table class="table table-borderless align-middle custom-table table-minimalist mb-0 w-100" id="usersTable">
                                <thead class="bg-light rounded text-muted">
                                    <tr>
                                        <th width="35%" class="font-weight-bold pb-2 pt-3 text-left pl-4" style="border-radius: 10px 0 0 10px; font-size: 0.8rem; text-transform: uppercase;">Profil Pengguna</th>
                                        <th width="30%" class="font-weight-bold pb-2 pt-3 text-left" style="font-size: 0.8rem; text-transform: uppercase;">Otorisasi & Area</th>
                                        <th width="15%" class="font-weight-bold pb-2 pt-3 text-center" style="font-size: 0.8rem; text-transform: uppercase;">Status</th>
                                        <th width="20%" class="font-weight-bold pb-2 pt-3 text-center" style="border-radius: 0 10px 10px 0; font-size: 0.8rem; text-transform: uppercase;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr class="table-row-hover" style="border-bottom: 1px solid #f8f9fa;">
                                        <td class="pt-3 pb-3 pl-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-modern shadow-sm mr-3">
                                                    {{ strtoupper(substr(!empty($user->initials) ? $user->initials : $user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $user->name }}</h6>
                                                    <small class="text-muted" style="font-size: 0.8rem;">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pt-3 pb-3">
                                            @php
                                                $roleColor = match(strtolower($user->role)) {
                                                    'admin' => '#4e73df',
                                                    'supervisor' => '#36b9cc',
                                                    'inspector' => '#1cc88a',
                                                    'manager', 'asst_manager' => '#f6c23e',
                                                    default => '#858796'
                                                };
                                            @endphp
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold mb-1" style="font-size: 0.75rem; color: {{ $roleColor }}; text-transform: uppercase;">
                                                    <i class="fas fa-user-shield mr-1"></i> {{ $user->role ?? 'No Role' }}
                                                </span>
                                                <div class="small text-muted" style="font-size: 0.75rem;">
                                                    <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
                                                    {{ ($user->plant && strtoupper($user->plant->name) !== 'TOTAL') ? str_replace(' / Head Office', '', $user->plant->name) : 'Semua Area (Global)' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center pt-3 pb-3">
                                            <div class="custom-control custom-switch custom-switch-success custom-switch-md d-inline-block">
                                                <input type="checkbox" class="custom-control-input toggle-user-status" id="status{{ $user->id }}" data-id="{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="status{{ $user->id }}"></label>
                                            </div>
                                        </td>
                                        <td class="text-center pt-3 pb-3">
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <button class="btn btn-sm btn-light rounded-circle shadow-sm edit-user mr-1" 
                                                    data-id="{{ $user->id }}" 
                                                    data-name="{{ $user->name }}" 
                                                    data-email="{{ $user->email }}" 
                                                    data-role="{{ $user->role }}" 
                                                    data-plant="{{ $user->plant_id }}" 
                                                    data-initials="{{ $user->initials }}"
                                                    data-toggle="tooltip" title="Edit Profil">
                                                    <i class="fas fa-pen text-primary" style="font-size: 0.75rem;"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light rounded-circle shadow-sm reset-password mr-1" 
                                                    data-id="{{ $user->id }}" 
                                                    data-name="{{ $user->name }}" 
                                                    data-toggle="tooltip" title="Reset Sandi">
                                                    <i class="fas fa-key text-warning" style="font-size: 0.75rem;"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light rounded-circle shadow-sm delete-user" 
                                                    data-id="{{ $user->id }}" 
                                                    data-name="{{ $user->name }}" 
                                                    data-toggle="tooltip" title="Hapus Akun">
                                                    <i class="fas fa-trash text-danger" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
                    <div class="card-body p-3" id="permissionAccordion">
                        @php
                            $parentMenus = \App\Models\AppMenu::whereNull('parent_id')->orderBy('order')->get();
                        @endphp

                        @foreach($parentMenus as $parent)
                        <div class="perm-module-card mb-3" data-module-id="{{ $parent->id }}">

                            {{-- Card Header --}}
                            <div class="perm-module-card-header"
                                 data-toggle="collapse"
                                 data-target="#perm-collapse-{{ $parent->id }}"
                                 aria-expanded="false"
                                 aria-controls="perm-collapse-{{ $parent->id }}">

                                <div class="d-flex align-items-center" style="min-width:0;">
                                    {{-- Power Toggle Button --}}
                                    <button type="button"
                                            class="module-master-toggle mr-3"
                                            data-menu-id="{{ $parent->id }}"
                                            title="Nonaktifkan seluruh modul">
                                        <i class="fas fa-power-off"></i>
                                    </button>

                                    {{-- Module Icon --}}
                                    <div class="perm-module-icon mr-3">
                                        <i class="{{ $parent->icon ?? 'fas fa-th-large' }}"></i>
                                    </div>

                                    {{-- Title --}}
                                    <div style="min-width:0;">
                                        <div class="perm-module-title">{{ $parent->name }}</div>
                                        @if($parent->children->isNotEmpty())
                                            <div class="perm-module-meta">
                                                <i class="fas fa-sitemap mr-1" style="font-size:0.6rem;"></i>
                                                {{ $parent->children->count() }} sub-modul
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <span class="perm-module-status-badge mr-3" id="status-badge-{{ $parent->id }}">Aktif</span>
                                    <i class="fas fa-chevron-down perm-collapse-arrow collapsed-arrow"></i>
                                </div>
                            </div>

                            {{-- Card Body (Collapsible) --}}
                            <div id="perm-collapse-{{ $parent->id }}"
                                 class="collapse">
                                <div class="perm-module-card-body">

                                    {{-- Grid Header --}}
                                    <div class="permission-grid-header">
                                        <div>Modul / Sub-Modul</div>
                                        <div>View</div>
                                        <div>Input</div>
                                        <div>Edit/Hapus</div>
                                        <div>Approve</div>
                                        <div style="line-height:1.2;">Approve<br><small>Semua</small></div>
                                        <div>Export</div>
                                    </div>

                                    {{-- Permission rows (parent + children recursively) --}}
                                    @include('settings.partials.permission_row', [
                                        'menu'        => $parent,
                                        'level'       => 0,
                                        'ancestorIds' => [],
                                    ])
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>


            <!-- Tab: Header Dokumen -->
            <div class="tab-pane fade" id="header-dokumen" role="tabpanel" aria-labelledby="header-dokumen-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                        <div>
                            <h6 class="m-0 font-weight-bold text-dark letter-spacing-1 font-size-sm mb-1">Header Dokumen</h6>
                            <p class="text-muted small mb-0">Kustomisasi dinamis untuk data dokumen di berbagai halaman laporan</p>
                        </div>
                        <button type="button" class="btn btn-dark rounded-pill px-4 shadow-sm btn-sm-modern py-2" data-toggle="modal" data-target="#modalAddDocumentHeader">
                            <i class="fas fa-plus mr-2"></i> Tambah Kustomisasi
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle custom-table table-minimalist mb-0 w-100" id="documentHeadersTable">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="font-weight-bold py-2 text-left small" style="text-transform: uppercase;">Modul / Area</th>
                                        <th class="font-weight-bold py-2 text-left small" style="text-transform: uppercase;">No. Dokumen</th>
                                        <th class="font-weight-bold py-2 text-left small" style="text-transform: uppercase;">Tgl. Terbit</th>
                                        <th class="font-weight-bold py-2 text-left small" style="text-transform: uppercase;">Revisi / Tgl</th>
                                        <th class="font-weight-bold py-2 text-left small" style="text-transform: uppercase;">Halaman</th>
                                        <th class="font-weight-bold py-2 text-center small" style="text-transform: uppercase;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Log Aktivitas -->
            <div class="tab-pane fade" id="activity-logs" role="tabpanel" aria-labelledby="activity-logs-tab">
                <div class="card shadow border-0 rounded-lg mb-4 slide-in">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                        <h6 class="m-0 font-weight-bold text-dark">System Activity Logs</h6>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchLogs" class="form-control form-control-sm border-0 shadow-sm mr-2 no-autoupper" placeholder="Cari log..." style="width: 220px; font-size: 0.75rem;">
                            <button type="button" id="refreshLogs" class="btn btn-sm btn-outline-dark rounded-pill px-3 shadow-sm btn-sm-modern">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                            <button type="button" id="resetLogs" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm-modern ml-2">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                        </div>
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
                                <input type="password" name="password" autocomplete="new-password" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" placeholder="Opsional (Default: indoplat2526)">
                                <div class="password-toggle-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="small font-weight-bold text-dark mb-0">Role / Jabatan</label>
                                        <button type="button" class="btn btn-sm btn-link text-primary p-0 toggle-new-role" style="font-size: 0.7rem; font-weight: 600; text-decoration: none;" data-target="add"><i class="fas fa-plus mr-1"></i>Role Baru</button>
                                    </div>
                                    <div class="position-relative" id="role_container_add">
                                        <select name="role" id="add_role" class="form-control rounded-pill border-0 bg-light px-3" required>
                                            @foreach($roles as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group d-none" id="role_input_group_add">
                                            <input type="text" id="role_input_add" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" placeholder="Ketik nama role baru..." style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;">
                                            <div class="input-group-append">
                                                <button class="btn btn-danger cancel-new-role" type="button" data-target="add" style="border-top-right-radius: 50rem; border-bottom-right-radius: 50rem; padding-left: 1rem; padding-right: 1rem;" title="Batal Tambah Role Baru"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
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
                                <input type="password" name="password" id="edit_password" autocomplete="new-password" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" placeholder="Biarkan kosong jika tidak ingin mengubah password">
                                <div class="password-toggle-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="small font-weight-bold text-dark mb-0">Role / Jabatan</label>
                                        <button type="button" class="btn btn-sm btn-link text-primary p-0 toggle-new-role" style="font-size: 0.7rem; font-weight: 600; text-decoration: none;" data-target="edit"><i class="fas fa-plus mr-1"></i>Role Baru</button>
                                    </div>
                                    <div class="position-relative" id="role_container_edit">
                                        <select name="role" id="edit_role" class="form-control rounded-pill border-0 bg-light px-3" required>
                                            @foreach($roles as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group d-none" id="role_input_group_edit">
                                            <input type="text" id="role_input_edit" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" placeholder="Ketik nama role baru..." style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;">
                                            <div class="input-group-append">
                                                <button class="btn btn-danger cancel-new-role" type="button" data-target="edit" style="border-top-right-radius: 50rem; border-bottom-right-radius: 50rem; padding-left: 1rem; padding-right: 1rem;" title="Batal Tambah Role Baru"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
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

    <!-- Modal Add Next Process -->
    <div class="modal fade" id="modalAddNextProcess" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus mr-2"></i>Tambah Next Process</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAddNextProcess">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Nama Proses</label>
                            <input type="text" name="name" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required placeholder="Contoh: CRUSHING">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Plant</label>
                            <select name="plant_id" class="form-control rounded-pill border-0 bg-light px-3" required>
                                @foreach($plants as $plant)
                                    @if(strtoupper($plant->name) !== 'TOTAL')
                                        <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Modul</label>
                            <select name="module" class="form-control rounded-pill border-0 bg-light px-3" required>
                                <option value="">-- Pilih Modul --</option>
                                @foreach($qcModules as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Urutan (Order)</label>
                            <input type="number" name="order" class="form-control rounded-pill border-0 bg-light px-3" value="0">
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

    <!-- Modal Edit Next Process -->
    <div class="modal fade" id="modalEditNextProcess" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-pen mr-2"></i>Edit Next Process</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditNextProcess">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_process_id">
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Nama Proses</label>
                            <input type="text" name="name" id="edit_process_name" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Plant</label>
                            <select name="plant_id" id="edit_process_plant_id" class="form-control rounded-pill border-0 bg-light px-3" required>
                                @foreach($plants as $plant)
                                    @if(strtoupper($plant->name) !== 'TOTAL')
                                        <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Modul</label>
                            <select name="module" id="edit_process_module" class="form-control rounded-pill border-0 bg-light px-3" required>
                                @foreach($qcModules as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Urutan (Order)</label>
                            <input type="number" name="order" id="edit_process_order" class="form-control rounded-pill border-0 bg-light px-3">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Document Header -->
    <div class="modal fade" id="modalAddDocumentHeader" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title font-weight-bold" id="documentHeaderModalTitle"><i class="fas fa-file-alt mr-2"></i>Kustomisasi Header Dokumen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formDocumentHeader">
                    @csrf
                    <input type="hidden" name="id" id="doc_header_id">
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Pilih Modul / Laporan</label>
                            <select name="key" id="doc_header_key" class="form-control rounded-pill border-0 bg-light px-3" required>
                                <option value="">-- Pilih Modul --</option>
                                <option value="master_alat_ukur">Master Alat Ukur</option>
                                <option value="hasil_verifikasi_alat_ukur">Hasil Verifikasi Alat Ukur</option>
                                @foreach($qcModules as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Pilih Plant / Area</label>
                            <select name="plant_code" id="doc_header_plant_code" class="form-control rounded-pill border-0 bg-light px-3" required>
                                <option value="">-- Pilih Plant --</option>
                                <option value="jakarta">Jakarta</option>
                                <option value="karawang">Karawang</option>
                            </select>
                        </div>
                        
                        <hr class="my-4" style="border-top: 1px dashed #e2e8f0;">
                        
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">No. Dokumen</label>
                            <input type="text" name="no_dokumen" id="doc_header_no_dokumen" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required placeholder="Contoh: QC-KRW-F-0213">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-dark">Tgl. Terbit</label>
                                    <input type="text" name="tgl_terbit" id="doc_header_tgl_terbit" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required placeholder="Contoh: 25/03/2015">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-dark">Revisi / Tgl</label>
                                    <input type="text" name="revisi" id="doc_header_revisi" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required placeholder="Contoh: 3 / 22/12/2025">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Halaman</label>
                            <input type="text" name="halaman" id="doc_header_halaman" class="form-control rounded-pill border-0 bg-light px-3 no-autoupper" required placeholder="Contoh: 1 / 1" value="1 / 1">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">Simpan Kustomisasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @include('settings.styles')
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

<script>
    window.settingsConfig = {
        var_0: "{{ url('admin/settings/menus') }}",
        var_1: '{{ csrf_token() }}',
        var_2: "{{ route("admin.settings.menus.order") }}",
        var_3: "{{ route("admin.settings.permissions") }}",
        var_4: "{{ route("admin.settings.permissions.save") }}",
        var_5: "{{ url('admin/settings/users') }}",
        var_6: "{{ route("admin.settings.users.store") }}",
        var_7: "{{ route('admin.settings.activity_logs') }}",
        var_8: "{{ route('admin.settings.general.update') }}",
        var_9: "{{ route('admin.settings.next-processes.store') }}",
        var_10: "{{ url('admin/settings/next-processes') }}",
        var_11: "{{ route('admin.settings.dashboard-layouts') }}",
        var_12: "{{ route('admin.settings.dashboard-layouts.save') }}",
        var_13: "{{ route('admin.settings.document-headers') }}",
        var_14: "{{ route('admin.settings.document-headers.store') }}",
        var_15: "{{ url('admin/settings/document-headers') }}",
    };
</script>

    <script src="{{ asset('js/vendor/moment.min.js') }}"></script>
    <script src="{{ asset('js/vendor/Sortable.min.js') }}"></script>
    <script src="{{ asset('js/vendor/moment-id.min.js') }}"></script>
    <script src="{{ asset('js/settings/index.js') }}"></script>
@endpush
