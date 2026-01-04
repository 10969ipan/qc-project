<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">QC Apps</div>
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

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager' || auth()->user()->role === 'manager'))
        <!-- Nav Item - Master Data (Shared Admin Access) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMaster" aria-expanded="true"
                aria-controls="collapseMaster">
                <i class="fas fa-fw fa-database"></i>
                <span>Master Data</span>
            </a>
            <div id="collapseMaster" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('admin.items.index') }}">Data Item</a>
                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager' || auth()->user()->role === 'manager'))
        <!-- Nav Item - Analis (Shared) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAnalysis"
                aria-expanded="true" aria-controls="collapseAnalysis">
                <i class="fas fa-fw fa-chart-line"></i>
                <span>Report</span>
            </a>
            <div id="collapseAnalysis" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('analysis.monthly_ng') }}">Sub Assy</a>
                    <a class="collapse-item" href="{{ route('analysis.monthly_ng_in_process') }}">Inprocess</a>
                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'inspector' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager'))
        <!-- Nav Item - Checksheet Menu (Shared) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseChecksheet"
                aria-expanded="true" aria-controls="collapseChecksheet">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Checksheet</span>
            </a>
            <div id="collapseChecksheet" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('checksheet.sub_assy') }}">Sub Assy</a>
                    <a class="collapse-item" href="{{ route('in_process.create') }}">Inprocess</a>
                    <a class="collapse-item" href="{{ route('cross_cut.create') }}">Cross Cut Plating</a>
                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor' || auth()->user()->role === 'inspector' || auth()->user()->role === 'kashift' || auth()->user()->role === 'asst_manager' || auth()->user()->role === 'manager'))
        <!-- Nav Item - Laporan (Shared) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport" aria-expanded="true"
                aria-controls="collapseReport">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseReport" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('admin.checksheets.index') }}">Sub Assy</a>
                    <a class="collapse-item" href="{{ route('in_process.index') }}">Inprocess</a>
                    <a class="collapse-item" href="{{ route('cross_cut.index') }}">Cross Cut Plating</a>
                </div>
            </div>
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