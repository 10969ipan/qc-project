<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

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

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="/">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    @php
        $canViewAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'oshef']);

        $canInputAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc', 'oshef']);
    @endphp

    @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'inspector', 'karu_qc', 'oshef'])))
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseQC" aria-expanded="true"
                aria-controls="collapseQC">
                <i class="fas fa-fw fa-clipboard-check"></i>
                <span>Quality Control</span>
            </a>
            <div id="collapseQC" class="collapse" aria-labelledby="headingQC" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">

                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                        <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                            data-target="#qcPlantJKT">Plant Jakarta</a>
                        <div id="qcPlantJKT" class="collapse pl-2">
                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcMasterJKT">Master data</a>
                                <div id="qcMasterJKT" class="collapse pl-2">
                                    <a class="collapse-item" href="{{ route('admin.items.index', ['plant' => 'jakarta']) }}">Data
                                        Item</a>
                                    <a class="collapse-item"
                                        href="{{ route('admin.categories.index', ['plant' => 'jakarta']) }}">Kategori</a>
                                    <a class="collapse-item"
                                        href="{{ route('admin.monthly-reports.index', ['plant' => 'jakarta']) }}">Lap Bulanan</a>
                                </div>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcReportJKT">Report</a>
                                <div id="qcReportJKT" class="collapse pl-2">
                                    <a class="collapse-item" href="{{ route('analysis.monthly_ng', ['plant' => 'jakarta']) }}">Sub
                                        Assy
                                        Anls</a>
                                    <a class="collapse-item"
                                        href="{{ route('analysis.monthly_ng_in_process', ['plant' => 'jakarta']) }}">Inprocess
                                        Anls</a>
                                </div>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'inspector', 'supervisor', 'kashift', 'asst_manager', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcCheckJKT">Checksheet</a>
                                <div id="qcCheckJKT" class="collapse pl-2">
                                    <a class="collapse-item" href="{{ route('checksheet.sub_assy', ['plant' => 'jakarta']) }}">Sub
                                        Assy</a>
                                    <a class="collapse-item"
                                        href="{{ route('in_process.create', ['plant' => 'jakarta']) }}">Inprocess</a>
                                    <a class="collapse-item"
                                        href="{{ route('first_piece_approval.create', ['plant' => 'jakarta']) }}">FPA</a>
                                    <a class="collapse-item" href="{{ route('sortir.create', ['plant' => 'jakarta']) }}">Sortir</a>
                                </div>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcLaporanJKT">Laporan</a>
                                <div id="qcLaporanJKT" class="collapse pl-2">
                                    <a class="collapse-item"
                                        href="{{ route('admin.checksheets.index', ['plant' => 'jakarta']) }}">Sub
                                        Assy</a>
                                    <a class="collapse-item"
                                        href="{{ route('in_process.index', ['plant' => 'jakarta']) }}">Inprocess</a>
                                    <a class="collapse-item"
                                        href="{{ route('first_piece_approval.index', ['plant' => 'jakarta']) }}">FPA</a>
                                    <a class="collapse-item" href="{{ route('sortir.index', ['plant' => 'jakarta']) }}">Sortir</a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="dropdown-divider"></div>

                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                        <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                            data-target="#qcPlantKRW">Plant Karawang</a>
                        <div id="qcPlantKRW" class="collapse pl-2">
                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcMasterKRW">Master data</a>
                                <div id="qcMasterKRW" class="collapse pl-2">
                                    <a class="collapse-item" href="{{ route('admin.items.index', ['plant' => 'karawang']) }}">Data
                                        Item</a>
                                    <a class="collapse-item"
                                        href="{{ route('admin.categories.index', ['plant' => 'karawang']) }}">Kategori</a>
                                    <a class="collapse-item"
                                        href="{{ route('admin.monthly-reports.index', ['plant' => 'karawang']) }}">Lap Bulanan</a>
                                </div>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcReportKRW">Report</a>
                                <div id="qcReportKRW" class="collapse pl-2">
                                    <a class="collapse-item" href="{{ route('analysis.monthly_ng', ['plant' => 'karawang']) }}">Sub
                                        Assy
                                        Anls</a>
                                    <a class="collapse-item"
                                        href="{{ route('analysis.monthly_ng_in_process', ['plant' => 'karawang']) }}">Inprocess
                                        Anls</a>
                                    <a class="collapse-item"
                                        href="{{ route('analysis.monthly_ng_cross_cut', ['plant' => 'karawang']) }}">Cross Cut
                                        Anls</a>
                                </div>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'inspector', 'supervisor', 'kashift', 'asst_manager', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcCheckKRW">Checksheet</a>
                                <div id="qcCheckKRW" class="collapse pl-2">
                                    <a class="collapse-item" href="{{ route('checksheet.sub_assy', ['plant' => 'karawang']) }}">Sub
                                        Assy</a>
                                    <a class="collapse-item"
                                        href="{{ route('in_process.create', ['plant' => 'karawang']) }}">Inprocess</a>
                                    <a class="collapse-item"
                                        href="{{ route('first_piece_approval.create', ['plant' => 'karawang']) }}">FPA</a>
                                    <a class="collapse-item" href="{{ route('cross_cut.create', ['plant' => 'karawang']) }}">Cross
                                        Cut Plating</a>
                                    <a class="collapse-item"
                                        href="{{ route('cross_cut_painting.create', ['plant' => 'karawang']) }}">Cross Cut
                                        Painting</a>
                                    <a class="collapse-item" href="{{ route('plating.create') }}">Plating</a>
                                    <a class="collapse-item" href="{{ route('double_tape.create') }}">Double Tape</a>
                                    <a class="collapse-item" href="{{ route('sortir.create', ['plant' => 'karawang']) }}">Sortir</a>
                                </div>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc', 'oshef']))
                                <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                                    data-target="#qcLaporanKRW">Laporan</a>
                                <div id="qcLaporanKRW" class="collapse pl-2">
                                    <a class="collapse-item"
                                        href="{{ route('admin.checksheets.index', ['plant' => 'karawang']) }}">Sub
                                        Assy</a>
                                    <a class="collapse-item"
                                        href="{{ route('in_process.index', ['plant' => 'karawang']) }}">Inprocess</a>
                                    <a class="collapse-item"
                                        href="{{ route('first_piece_approval.index', ['plant' => 'karawang']) }}">FPA</a>
                                    <a class="collapse-item" href="{{ route('cross_cut.index', ['plant' => 'karawang']) }}">Cross
                                        Cut Plating</a>
                                    <a class="collapse-item"
                                        href="{{ route('cross_cut_painting.index', ['plant' => 'karawang']) }}">Cross Cut
                                        Painting</a>
                                    <a class="collapse-item" href="{{ route('plating.index') }}">Plating</a>
                                    <a class="collapse-item" href="{{ route('double_tape.index') }}">Double Tape</a>
                                    <a class="collapse-item" href="{{ route('sortir.index', ['plant' => 'karawang']) }}">Sortir</a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager'])))
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseQA" aria-expanded="true"
                aria-controls="collapseQA">
                <i class="fas fa-fw fa-award"></i>
                <span>Quality Assurance</span>
            </a>
            <div id="collapseQA" class="collapse" aria-labelledby="headingQA" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">

                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                        <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                            data-target="#qaPlantJKT">Plant Jakarta</a>
                        <div id="qaPlantJKT" class="collapse pl-2">
                            <a class="collapse-item"
                                href="{{ route('admin.customer-claims.index', ['plant' => 'jakarta']) }}">Claim
                                Customer</a>
                            <a class="collapse-item"
                                href="{{ route('admin.customer-claim-records.index', ['plant' => 'jakarta']) }}">List Claim</a>
                        </div>
                    @endif

                    <div class="dropdown-divider"></div>

                    @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                        <a class="collapse-item font-weight-bold py-1" href="#" data-toggle="collapse"
                            data-target="#qaPlantKRW">Plant Karawang</a>
                        <div id="qaPlantKRW" class="collapse pl-2">
                            <a class="collapse-item"
                                href="{{ route('admin.customer-claims.index', ['plant' => 'karawang']) }}">Claim Customer</a>
                            <a class="collapse-item"
                                href="{{ route('admin.customer-claim-records.index', ['plant' => 'karawang']) }}">List Claim</a>
                        </div>
                    @endif

                </div>
            </div>
        </li>
    @endif

    @if(auth()->check() && (auth()->user()->plant && auth()->user()->plant->code !== 'jakarta') && (auth()->user()->role === 'karu_qc' || auth()->user()->role === 'kashift_plating' || auth()->user()->role === 'supervisor_plating' || auth()->user()->role === 'manager_plating'))
        <li class="nav-item">
            <a class="nav-link" href="{{ route('cross_cut.index') }}">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Hasil Input Cross Cut</span>
            </a>
        </li>
    @endif

    <li class="nav-item">
        <a class="nav-link btn-logout" href="#">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span></a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>