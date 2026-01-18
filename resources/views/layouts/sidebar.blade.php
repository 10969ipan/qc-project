<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">QC Apps
            @if(auth()->check() && auth()->user()->plant)
                <br><small class="text-white-50"
                    style="font-size: 0.7rem;">{{ strtoupper(auth()->user()->plant->name) }}</small>
            @endif
        </div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="/">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Menu
    </div>

    @php
        // Roles that can VIEW all plants (for reports/laporan) - EXCLUDES inspector and plating roles logic if specific strictness needed, but request asked for specific roles
        $canViewAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift']);

        // Roles that can INPUT in all plants - EXCLUDES inspector (they can only input in their own plant)
        $canInputAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc']);
    @endphp

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager' || auth()->user()->role === 'manager'))
        <!-- Nav Item - Master Data -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMaster" aria-expanded="true"
                aria-controls="collapseMaster">
                <i class="fas fa-fw fa-database"></i>
                <span>Master Data</span>
            </a>
            <div id="collapseMaster" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#masterJakarta" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Jakarta
                        </a>
                        <div id="masterJakarta" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('admin.items.index', ['plant' => 'jakarta']) }}">Data
                                Item</a>
                            <a class="collapse-item"
                                href="{{ route('admin.categories.index', ['plant' => 'jakarta']) }}">Kategori Item</a>
                            <a class="collapse-item"
                                href="{{ route('admin.monthly-reports.index', ['plant' => 'jakarta']) }}">Laporan Bulanan</a>
                        </div>
                    @endif
                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#masterKarawang" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Karawang
                        </a>
                        <div id="masterKarawang" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('admin.items.index', ['plant' => 'karawang']) }}">Data
                                Item</a>
                            <a class="collapse-item"
                                href="{{ route('admin.categories.index', ['plant' => 'karawang']) }}">Kategori Item</a>
                            <a class="collapse-item"
                                href="{{ route('admin.monthly-reports.index', ['plant' => 'karawang']) }}">Laporan Bulanan</a>
                        </div>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager' || auth()->user()->role === 'manager'))
        <!-- Nav Item - Report -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport" aria-expanded="true"
                aria-controls="collapseReport">
                <i class="fas fa-fw fa-chart-line"></i>
                <span>Report</span>
            </a>
            <div id="collapseReport" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#reportJakarta" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Jakarta
                        </a>
                        <div id="reportJakarta" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('analysis.monthly_ng', ['plant' => 'jakarta']) }}">Sub
                                Assy</a>
                            <a class="collapse-item"
                                href="{{ route('analysis.monthly_ng_in_process', ['plant' => 'jakarta']) }}">Inprocess</a>
                        </div>
                    @endif
                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#reportKarawang" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Karawang
                        </a>
                        <div id="reportKarawang" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('analysis.monthly_ng', ['plant' => 'karawang']) }}">Sub
                                Assy</a>
                            <a class="collapse-item"
                                href="{{ route('analysis.monthly_ng_in_process', ['plant' => 'karawang']) }}">Inprocess</a>
                            <a class="collapse-item"
                                href="{{ route('analysis.monthly_ng_cross_cut', ['plant' => 'karawang']) }}">Cross Cut</a>
                        </div>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'inspector' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager'))
        <!-- Nav Item - Checksheet (Input) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseChecksheet"
                aria-expanded="true" aria-controls="collapseChecksheet">
                <i class="fas fa-fw fa-edit"></i>
                <span>Checksheet</span>
            </a>
            <div id="collapseChecksheet" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    {{-- Inspector can only input in their own plant, management can input in all plants --}}
                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#checksheetJakarta" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Jakarta
                        </a>
                        <div id="checksheetJakarta" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('checksheet.sub_assy', ['plant' => 'jakarta']) }}">Sub
                                Assy</a>
                            <a class="collapse-item"
                                href="{{ route('in_process.create', ['plant' => 'jakarta']) }}">Inprocess</a>
                            <a class="collapse-item" href="{{ route('sortir.create', ['plant' => 'jakarta']) }}">Sortir</a>
                        </div>
                    @endif
                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#checksheetKarawang" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Karawang
                        </a>
                        <div id="checksheetKarawang" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('checksheet.sub_assy', ['plant' => 'karawang']) }}">Sub
                                Assy</a>
                            <a class="collapse-item"
                                href="{{ route('in_process.create', ['plant' => 'karawang']) }}">Inprocess</a>
                            <a class="collapse-item" href="{{ route('cross_cut.create', ['plant' => 'karawang']) }}">Cross
                                Cut</a>
                            <a class="collapse-item" href="{{ route('sortir.create', ['plant' => 'karawang']) }}">Sortir</a>
                        </div>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'inspector' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager' || auth()->user()->role === 'manager' || auth()->user()->role === 'karu_qc'))
        <!-- Nav Item - Laporan (Viewing) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLaporan"
                aria-expanded="true" aria-controls="collapseLaporan">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    {{-- Inspector can view all plants' reports --}}
                    @if($canViewAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#laporanJakarta" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Jakarta
                        </a>
                        <div id="laporanJakarta" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('admin.checksheets.index', ['plant' => 'jakarta']) }}">Sub
                                Assy</a>
                            <a class="collapse-item"
                                href="{{ route('in_process.index', ['plant' => 'jakarta']) }}">Inprocess</a>
                            <a class="collapse-item" href="{{ route('sortir.index', ['plant' => 'jakarta']) }}">Sortir</a>
                        </div>
                    @endif
                    @if($canViewAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                        <a class="collapse-item collapsed d-flex align-items-center" href="#" data-toggle="collapse"
                            data-target="#laporanKarawang" aria-expanded="false">
                            <i class="fas fa-building mr-2"></i> Plant Karawang
                        </a>
                        <div id="laporanKarawang" class="collapse" style="padding-left: 20px;">
                            <a class="collapse-item" href="{{ route('admin.checksheets.index', ['plant' => 'karawang']) }}">Sub
                                Assy</a>
                            <a class="collapse-item"
                                href="{{ route('in_process.index', ['plant' => 'karawang']) }}">Inprocess</a>
                            <a class="collapse-item" href="{{ route('cross_cut.index', ['plant' => 'karawang']) }}">Cross
                                Cut</a>
                            <a class="collapse-item" href="{{ route('sortir.index', ['plant' => 'karawang']) }}">Sortir</a>
                        </div>
                    @endif
                </div>
            </div>
        </li>
    @endif





    @if(auth()->check() && (auth()->user()->plant && auth()->user()->plant->code !== 'jakarta') && (auth()->user()->role === 'karu_qc' || auth()->user()->role === 'kashift_plating' || auth()->user()->role === 'supervisor_plating' || auth()->user()->role === 'manager_plating'))
        <!-- Nav Item - Cross Cut Only (For Plating Roles) -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('cross_cut.index') }}">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Hasil Input Cross Cut</span>
            </a>
        </li>
    @endif

    <!-- Nav Item - Logout -->
    <li class="nav-item">
        <a class="nav-link btn-logout" href="#">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>