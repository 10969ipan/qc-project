@extends('layouts.admin')

@section('title', 'Dashboard')

@section('title', 'Dashboard')

@section('content')
    @php
        $jakartaMachines = [
            1 => ['brand' => 'NISSEI', 'tonnage' => '210'],
            2 => ['brand' => 'NISSEI', 'tonnage' => '210'],
            3 => ['brand' => 'HYBRIC', 'tonnage' => '160'],
            4 => ['brand' => 'NISSEI', 'tonnage' => '160'],
            5 => ['brand' => 'NISSEI', 'tonnage' => '160'],
            6 => ['brand' => 'NISSEI', 'tonnage' => '60'],
            7 => ['brand' => 'NISSEI', 'tonnage' => '120'],
            8 => ['brand' => 'NISSEI', 'tonnage' => '80'],
            9 => ['brand' => 'NISSEI', 'tonnage' => '120'],
            10 => ['brand' => 'YIZUMI', 'tonnage' => '160'],
            11 => ['brand' => 'SOUND', 'tonnage' => '230'],
            12 => ['brand' => 'SOUND', 'tonnage' => '230'],
            
            14 => ['brand' => 'THOSIBA', 'tonnage' => '450'],
            15 => ['brand' => 'YIZUMI', 'tonnage' => '160'],
            16 => ['brand' => 'YIZUMI', 'tonnage' => '120'],
            17 => ['brand' => 'YIZUMI', 'tonnage' => '120'],
            18 => ['brand' => 'YIZUMI', 'tonnage' => '160'],
            19 => ['brand' => 'SOUND', 'tonnage' => '230'],
            20 => ['brand' => 'YIZUMI', 'tonnage' => '120'],
            21 => ['brand' => 'SOUND', 'tonnage' => '160'],
            22 => ['brand' => 'NISSEI', 'tonnage' => '80'],
            23 => ['brand' => 'SOUND', 'tonnage' => '220'],
        ];

        $karawangMachines = [
            1 => ['brand' => '-', 'tonnage' => '850'],
            2 => ['brand' => '-', 'tonnage' => '650'],
            3 => ['brand' => '-', 'tonnage' => '650'],
            4 => ['brand' => '-', 'tonnage' => '650'],
            5 => ['brand' => '-', 'tonnage' => '550'],
            6 => ['brand' => '-', 'tonnage' => '450'],
            7 => ['brand' => '-', 'tonnage' => '360'],
            8 => ['brand' => '-', 'tonnage' => '210'],
            9 => ['brand' => '-', 'tonnage' => '210'],
            11 => ['brand' => '-', 'tonnage' => '160'],
            12 => ['brand' => '-', 'tonnage' => '80'],
            14 => ['brand' => '-', 'tonnage' => '120'],
            15 => ['brand' => '-', 'tonnage' => '160'],
            16 => ['brand' => '-', 'tonnage' => '180'],
            17 => ['brand' => '-', 'tonnage' => '180'],
            18 => ['brand' => '-', 'tonnage' => '120'],
            19 => ['brand' => '-', 'tonnage' => '160'],
        ];
    @endphp

    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.4);
            --shadow-premium: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -5px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
            --gradient-welcome: linear-gradient(135deg, #6366f1 0%, #4338ca 50%, #312e81 100%);
            --accent-glow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .dashboard-container {
            font-family: 'IBM Plex Sans', system-ui, -apple-system, sans-serif;
            letter-spacing: -0.01em;
        }

        .modern-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: var(--shadow-premium);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modern-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .modern-card-header {
            background: rgba(248, 250, 252, 0.5);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            padding: 1.25rem;
            backdrop-filter: blur(10px);
        }

        .modern-card-title {
            font-weight: 800;
            font-size: 0.95rem;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .welcome-modern {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.25);
            margin-bottom: 1.5rem;
        }

        .glass-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-date-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.25rem;
            border-radius: 18px;
            text-align: right;
        }

        @media (max-width: 991.98px) {
            .welcome-modern { padding: 1.5rem; }
            .welcome-date-card { text-align: left; margin-top: 1.5rem; }
        }
    </style>

    <script>
        (function() {
            if (!window.tailwindWarnMuted) {
                const originalWarn = console.warn;
                console.warn = function(...args) {
                    if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
                    originalWarn.apply(console, args);
                };
                window.tailwindWarnMuted = true;
            }
        })();
    </script>
    <script src="{{ asset('js/vendor/tailwind.min.js') }}"></script>
    
    <script src="{{ asset('js/dashboard/tailwind-config.js') }}"></script>
    <link href="{{ asset('fonts/material-icons.css') }}" rel="stylesheet">


    
    <div class="row">
        <div class="col-12">
            <div class="welcome-modern shadow">
                <div style="position: absolute; top: 0; right: 0; width: 100%; height: 100%; opacity: 0.1; background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-lg-8 text-center text-lg-left">
                        <h4 class="font-weight-bold mb-1">Selamat Datang, {{ Auth::user()->name }}! </h4>
                        <p class="mb-0" style="opacity: 0.9; font-size: 0.9rem;">Quality Department</p>
                        <div class="mt-3">
                            <span class="badge badge-light text-primary px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-user-tag mr-1"></i> {{ getRoleDisplayName(Auth::user()->role) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center text-lg-right mt-3 mt-lg-0 d-none d-md-block">
                        <div class="h3 mb-0 font-weight-bold" id="current-date">Loading...</div>
                        <small style="opacity: 0.8; font-size: 0.9rem;"><i class="fas fa-clock mr-1"></i> <span id="current-time"></span> WIB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>



    
    @php
        $showClaimJakarta = ($dashboardLayout['chartClaimJakarta'] ?? true) && ($isDualView || (Auth::user()->plant->code ?? '') !== 'karawang');
        $showClaimKarawang = ($dashboardLayout['chartClaimKarawang'] ?? true) && ($isDualView || (Auth::user()->plant->code ?? '') === 'karawang');
        $showClaimFrequency = $dashboardLayout['chartClaimFrequency'] ?? true;
        $showAnyClaim = $showClaimJakarta || $showClaimKarawang || $showClaimFrequency;
    @endphp

    @if($showAnyClaim)
    <div class="row mb-5 px-md-3 px-lg-4">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div></div>
                <form action="{{ route('dashboard') }}" method="GET" class="form-inline gap-2">
                    @php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $selectedMonth = $selectedMonth ?? date('n');
                        $selectedYear = $selectedYear ?? date('Y');
                    @endphp
                    
                    <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>

                    <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="combined" {{ ($claimData['year'] ?? '') == 'combined' ? 'selected' : '' }}>All Claims</option>
                        @php $currentY = date('Y'); @endphp
                        @for($y = $currentY; $y >= 2022; $y--)
                            <option value="{{ $y }}" {{ ($selectedYear == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        
        @if(($dashboardLayout['chartClaimJakarta'] ?? true) && ($isDualView || (Auth::user()->plant->code ?? '') !== 'karawang'))
            <div class="{{ $isDualView ? 'col-xl-4 col-lg-6' : 'col-lg-6' }} col-md-12 mb-4">
                <div class="modern-card">
                    <div class="modern-card-header d-flex align-items-center">
                        <div class="icon-circle bg-indigo-100 text-indigo-600 mr-3 d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 42px; height: 42px; border-radius: 12px;">
                            <i class="fas fa-building" style="font-size: 1.1rem;"></i>
                        </div>
                        <div>
                            <h6 class="modern-card-title mb-0">Claim Jakarta</h6>
                            <div class="text-xs font-weight-bold text-slate-400 mt-1">Bulan ini: <span class="text-indigo-600">{{ array_sum($claimData['combined_total'] ?? []) }}</span></div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="chartClaimJakarta" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        @endif

        
        @if(($dashboardLayout['chartClaimKarawang'] ?? true) && ($isDualView || (Auth::user()->plant->code ?? '') === 'karawang'))
            <div class="{{ $isDualView ? 'col-xl-4 col-lg-6' : 'col-lg-6' }} col-md-12 mb-4">
                <div class="modern-card">
                    <div class="modern-card-header d-flex align-items-center">
                        <div class="icon-circle bg-emerald-100 text-emerald-600 mr-3 d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 42px; height: 42px; border-radius: 12px;">
                            <i class="fas fa-industry" style="font-size: 1.1rem;"></i>
                        </div>
                        <div>
                            <h6 class="modern-card-title mb-0">Claim Karawang</h6>
                            <div class="text-xs font-weight-bold text-slate-400 mt-1">Bulan ini: <span class="text-emerald-600">{{ array_sum($claimData['combined_total'] ?? []) }}</span></div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="chartClaimKarawang" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        @endif

        
        
        @if($dashboardLayout['chartClaimFrequency'] ?? true)
        <div class="{{ $isDualView ? 'col-xl-4 col-lg-6' : 'col-lg-6' }} col-md-12 mb-4">
            <div class="modern-card">
                <div class="modern-card-header d-flex align-items-center">
                    <div class="icon-circle bg-blue-100 text-blue-600 mr-3 d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 42px; height: 42px; border-radius: 12px;">
                        <i class="fas fa-chart-bar" style="font-size: 1.1rem;"></i>
                    </div>
                    <div>
                        <h6 class="modern-card-title mb-0">Frekuensi</h6>
                        <div class="text-xs font-weight-bold text-slate-400 mt-1">Total Frekuensi Jakarta & Karawang</div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="chartClaimFrequency" style="height: 320px; width: 100%;"></div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    @if(isset($combinedStats))
        <div class="row">
            @php
                $showJakartaApproval = ($dashboardLayout['chartJakarta'] ?? true) || ($dashboardLayout['gauge-jakarta'] ?? true);
                $showKarawangApproval = ($dashboardLayout['chartKarawang'] ?? true) || ($dashboardLayout['gauge-karawang'] ?? true);
                $showSingleApproval = ($dashboardLayout['chartContainer'] ?? true) || ($dashboardLayout['gauge-total'] ?? true);
            @endphp
            @if($isDualView && isset($statsJakarta) && isset($statsKarawang))
                
                @if($showJakartaApproval)
                <div class="col-12 mb-5">
                    <div class="row">
                        
                        @if($dashboardLayout['chartJakarta'] ?? true)
                        <div class="col-xl-8 col-lg-7 mb-4 mb-xl-0">
                            <div class="modern-card h-100">
                                <div class="modern-card-header d-flex align-items-center">
                                    <div class="icon-circle bg-indigo-100 text-indigo-600 mr-3 d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 42px; height: 42px; border-radius: 12px;">
                                        <i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="modern-card-title mb-0">Approval Jakarta - {{ $months[$selectedMonth] }} {{ $selectedYear }}</h6>
                                        <div class="text-xs font-weight-bold text-slate-400 mt-1">Status Verifikasi & Validasi</div>
                                    </div>
                                </div>
                                <div class="card-body bg-light" style="background: #fdfdfe;">
                                    <div id="chartJakarta" style="height: 300px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($dashboardLayout['gauge-jakarta'] ?? true)
                        <div class="col-xl-4 col-lg-5">
                            <div class="modern-card h-100">
                                <div class="modern-card-header d-flex align-items-center">
                                    <div class="icon-circle bg-blue-100 text-blue-600 mr-3 d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 42px; height: 42px; border-radius: 12px;">
                                        <i class="fas fa-tachometer-alt" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="modern-card-title mb-0">Approval Daily Jakarta</h6>
                                        <div class="text-xs font-weight-bold text-slate-400 mt-1">Persentase Approval Harian</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gauge-jakarta" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                
                @if($showKarawangApproval)
                <div class="col-12 mb-5">
                    <div class="row">
                        
                        @if($dashboardLayout['chartKarawang'] ?? true)
                        <div class="col-xl-8 col-lg-7 mb-4 mb-xl-0">
                            <div class="modern-card h-100">
                                <div class="modern-card-header d-flex align-items-center">
                                    <div class="icon-circle bg-emerald-100 text-emerald-600 mr-3 d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 42px; height: 42px; border-radius: 12px;">
                                        <i class="fas fa-check-double" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="modern-card-title mb-0">Approval Karawang - {{ $months[$selectedMonth] }} {{ $selectedYear }}</h6>
                                        <div class="text-xs font-weight-bold text-slate-400 mt-1">Status Verifikasi & Validasi</div>
                                    </div>
                                </div>
                                <div class="card-body bg-light" style="background: #fdfdfe;">
                                    <div id="chartKarawang" style="height: 300px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($dashboardLayout['gauge-karawang'] ?? true)
                        <div class="col-xl-4 col-lg-5">
                            <div class="modern-card h-100">
                                <div class="modern-card-header d-flex align-items-center">
                                    <div class="icon-circle bg-green-100 text-green-600 mr-3 d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 42px; height: 42px; border-radius: 12px;">
                                        <i class="fas fa-tachometer-alt" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="modern-card-title mb-0">Approval Daily Karawang</h6>
                                        <div class="text-xs font-weight-bold text-slate-400 mt-1">Persentase Approval Harian</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gauge-karawang" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                
                @if($dashboardLayout['chartNgJakarta'] ?? true)
                <div class="col-xl-6 col-lg-12 mb-5">
                    <div class="modern-card h-100">
                        <div class="modern-card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary text-white mr-3"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h6 class="modern-card-title">MONITORING RATE NG - JAKARTA</h6>
                                    <div class="small text-muted">Trend Rate NG 1 Bulan Terakhir</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="chartNgJakarta" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($dashboardLayout['chartNgKarawang'] ?? true)
                <div class="col-xl-6 col-lg-12 mb-5">
                    <div class="modern-card h-100">
                        <div class="modern-card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-success text-white mr-3"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h6 class="modern-card-title">MONITORING RATE NG - KARAWANG</h6>
                                    <div class="small text-muted">Trend Rate NG 1 Bulan Terakhir</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="chartNgKarawang" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
                @endif
            @else
                
                @if($showSingleApproval)
                <div class="col-12 mb-5">
                    <div class="row">
                        
                        @if($dashboardLayout['chartContainer'] ?? true)
                        <div class="col-xl-8 col-lg-7 mb-4 mb-xl-0">
                            <div class="modern-card h-100">
                                <div class="modern-card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-info text-white mr-3"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <i class="fas fa-chart-pie"></i>
                                        </div>
                                        <div>
                                            @php
                                                $currentPlantName = Auth::user()->plant->name ?? 'Total';
                                            @endphp
                                            <h6 class="modern-card-title">{{ strtoupper($currentPlantName) }} APPROVAL - {{ strtoupper($months[$selectedMonth]) }} {{ $selectedYear }}</h6>
                                            <div class="small text-muted">Statistik {{ $currentPlantName }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-light" style="background: #fdfdfe;">
                                    <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($dashboardLayout['gauge-total'] ?? true)
                        <div class="col-xl-4 col-lg-5">
                            <div class="modern-card h-100">
                                <div class="modern-card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-info text-white mr-3"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <h6 class="modern-card-title">{{ strtoupper(Auth::user()->plant->code ?? 'COMBINED') }} -
                                            DAILY APPROVAL</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gauge-total" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                
                @if($dashboardLayout['chartNgSingle'] ?? true)
                <div class="col-12 mb-5">
                    <div class="modern-card h-100">
                        <div class="modern-card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary text-white mr-3"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    @php
                                        $currentPlantDisplay = strtolower(Auth::user()->plant?->code ?? request('plant') ?? 'jakarta');
                                    @endphp
                                    <h6 class="modern-card-title">MONITORING RATE NG - {{ strtoupper($currentPlantDisplay) }}</h6>
                                    <div class="small text-muted">Trend Rate NG 1 Bulan Terakhir</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="chartNgSingle" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>

        @push('scripts')
            <script src="{{ asset('js/vendor/fusioncharts.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.js')) }}"></script>
            <script src="{{ asset('js/vendor/fusioncharts.widgets.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.widgets.js')) }}"></script>
            <script src="{{ asset('js/vendor/fusioncharts.theme.fusion.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.theme.fusion.js')) }}"></script>
            <script src="{{ asset('js/vendor/fusioncharts.theme.gammel.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.theme.gammel.js')) }}"></script>
            <script src="{{ asset('js/vendor/canvasjs.min.js') }}?v={{ filemtime(public_path('js/vendor/canvasjs.min.js')) }}"></script>
            @php
                $dashboardStats = [
                    'isDualView'         => (bool)($isDualView ?? false),
                    'currentPlant'       => strtolower($currentPlant ?? ''),
                    'plantName'          => Auth::user()->plant?->name ?? 'Combined',
                    'statsJakarta'       => $statsJakarta ?? null,
                    'statsKarawang'      => $statsKarawang ?? null,
                    'combinedStats'      => $combinedStats ?? null,
                    'dailyStatsJakarta'  => $dailyStatsJakarta ?? null,
                    'dailyStatsKarawang' => $dailyStatsKarawang ?? null,
                    'dailyCombinedStats' => $dailyCombinedStats ?? null,
                    'claimData'          => $claimData ?? null,
                    'claimFrequency'     => $claimFrequency ?? null,
                    'ngRateData'         => $ngRateData ?? null,
                ];
            @endphp
            <script id="dashboard-stats" type="application/json">
                @json($dashboardStats)
            </script>
            <script>
                (function() {
                    const statsEl = document.getElementById('dashboard-stats');
                    if (statsEl) {
                        window.__DASHBOARD__ = JSON.parse(statsEl.textContent);
                    }
                })();
            </script>
            
            <script src="{{ asset('js/dashboard/dashboard-charts.js') }}"></script>
        @endpush
    @endif


    

    @if(isset($isDualView) && $isDualView && isset($productionJakarta) && isset($productionKarawang))
        
        <div class="row">
            
            @if($dashboardLayout['productionJakarta'] ?? true)
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-success text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-industry"></i></div>
                            <div>
                                <h6 class="modern-card-title">PRODUKSI SUB ASSY - JAKARTA</h6><small
                                    class="text-muted">Monitoring Jakarta</small>
                            </div>
                        </div>
                        <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">Running:
                            {{ $productionJakarta['activeLines']->count() }}</span>
                    </div>
                    <div class="card-body bg-light" style="background: #fdfdfe;">
                        <div class="row px-2">
                            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] as $i)
                                @php
                                    $data = $productionJakarta['activeLines']->get($i);
                                    $manualStatus = $productionJakarta['lineStatuses']->get($i);
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';
                                    if ($manualStatus && $manualStatus->status === 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'stopped') {
                                        $statusClass = 'status-stopped';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'trouble') {
                                        $statusClass = 'status-trouble';
                                        $isActive = false;
                                    } elseif ($isActive) {
                                        $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                    }
                                @endphp
                                <div class="col-6 col-md-6 col-lg-4 mb-4 px-2">
                                    <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group cursor-pointer min-h-[180px] {{ $statusClass === 'status-trouble' || $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}"
                                        onclick="showDetailModal(this)"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}"
                                            data-date="{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('d/m/Y') : '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                        data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}" @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">

                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex flex-col">
                                                <h4
                                                    class="text-sm font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">
                                                    MEJA-{{ $i }}</h4>
                                            </div>
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                @php
                                                    $badgeColor = $manualStatus->status === 'maintenance' ? 'yellow' : ($manualStatus->status === 'stopped' ? 'gray' : 'red');
                                                    $badgeBg = $manualStatus->status === 'maintenance' ? 'bg-yellow-50 dark:bg-yellow-900/20' : ($manualStatus->status === 'stopped' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-red-50 dark:bg-red-900/40');
                                                    $badgeText = $manualStatus->status === 'maintenance' ? 'MAINT' : ($manualStatus->status === 'stopped' ? 'IDLE' : 'TROUBLE');
                                                    $icon = $manualStatus->status === 'maintenance' ? 'engineering' : ($manualStatus->status === 'stopped' ? 'pause_circle_outline' : 'warning');
                                                @endphp
                                                <div
                                                    class="flex items-center gap-1 {{ $badgeBg }} text-{{ $badgeColor }}-700 dark:text-{{ $badgeColor }}-200 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-{{ $badgeColor }}-200 dark:border-{{ $badgeColor }}-700 shadow-sm">
                                                    <span
                                                        class="material-icons-round text-[10px] {{ $badgeColor === 'red' ? 'animate-bounce' : '' }}">{{ $icon }}</span>
                                                    {{ $badgeText }}
                                                </div>
                                            @elseif($isActive)
                                                <div
                                                    class="flex items-center gap-1 text-green-700 dark:text-green-300 text-[0.55rem] font-bold">
                                                    <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                    RUNNING
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center gap-1 text-gray-600 dark:text-gray-300 text-[0.55rem] font-bold">
                                                    <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                    IDLE
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-1.5 min-h-[100px]">
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                <div
                                                    class="rounded-lg p-1.5 border border-{{ $badgeColor }}-100 dark:border-{{ $badgeColor }}-800/50">
                                                    <p
                                                        class="text-[0.65rem] text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-700 dark:text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-300 font-semibold uppercase">
                                                        {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : 'TROUBLE') }}
                                                    </p>
                                                    <p
                                                        class="text-[0.6rem] text-slate-500 dark:text-slate-400 italic mt-0.5 leading-tight">
                                                        {{ Str::limit($manualStatus->description, 30) }}
                                                    </p>
                                                </div>
                                            @elseif($isActive)
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->name ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight mt-0.5">
                                                    <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                    <span
                                                        class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->part_number ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200">{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}
                                                        WIB</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                    <span
                                                        class="font-medium text-slate-700 dark:text-slate-300 truncate max-w-[120px]">{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}</span>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between text-[0.5rem] mb-1 font-medium">
                                                        <span
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                        <span
                                                            class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold text-lg">{{ $data->judgment }}</span>
                                                    </div>
                                                    <div
                                                        class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full"
                                                            style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="rounded-lg text-center flex flex-col items-center justify-center h-full">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500 mb-0">Meja Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-1 mb-0">Wait
                                                        Check QC</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
            @endif

            
            @if($dashboardLayout['productionKarawang'] ?? true)
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-industry"></i></div>
                            <div>
                                <h6 class="modern-card-title">PRODUKSI SUB ASSY - KARAWANG</h6><small
                                    class="text-muted">Monitoring Karawang</small>
                            </div>
                        </div>
                        <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">Running:
                            {{ $productionKarawang['activeLines']->count() }}</span>
                    </div>
                    <div class="card-body bg-light" style="background: #fdfdfe;">
                        <div class="row px-2">
                            @foreach (range(1, 15) as $i)
                                @php
                                    $data = $productionKarawang['activeLines']->get($i);
                                    $manualStatus = $productionKarawang['lineStatuses']->get($i);
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';
                                    if ($manualStatus && $manualStatus->status === 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'stopped') {
                                        $statusClass = 'status-stopped';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'trouble') {
                                        $statusClass = 'status-trouble';
                                        $isActive = false;
                                    } elseif ($isActive) {
                                        $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                    }
                                @endphp
                                <div class="col-6 col-md-6 col-lg-4 mb-4 px-2">
                                    <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group cursor-pointer {{ $statusClass === 'status-trouble' || $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}"
                                        onclick="showDetailModal(this)"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}"
                                            data-date="{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('d/m/Y') : '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                        data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}" @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">

                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex flex-col">
                                                <h4
                                                    class="text-sm font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">
                                                    MEJA-{{ $i }}</h4>
                                            </div>
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                @php
                                                    $badgeColor = $manualStatus->status === 'maintenance' ? 'yellow' : ($manualStatus->status === 'stopped' ? 'gray' : 'red');
                                                    $badgeBg = $manualStatus->status === 'maintenance' ? 'bg-yellow-50 dark:bg-yellow-900/20' : ($manualStatus->status === 'stopped' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-red-50 dark:bg-red-900/40');
                                                    $badgeText = $manualStatus->status === 'maintenance' ? 'MAINT' : ($manualStatus->status === 'stopped' ? 'IDLE' : 'TROUBLE');
                                                    $icon = $manualStatus->status === 'maintenance' ? 'engineering' : ($manualStatus->status === 'stopped' ? 'pause_circle_outline' : 'warning');
                                                @endphp
                                                <div
                                                    class="flex items-center gap-1 {{ $badgeBg }} text-{{ $badgeColor }}-700 dark:text-{{ $badgeColor }}-200 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-{{ $badgeColor }}-200 dark:border-{{ $badgeColor }}-700 shadow-sm">
                                                    <span
                                                        class="material-icons-round text-[10px] {{ $badgeColor === 'red' ? 'animate-bounce' : '' }}">{{ $icon }}</span>
                                                    {{ $badgeText }}
                                                </div>
                                            @elseif($isActive)
                                                <div
                                                    class="flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-green-200 dark:border-green-800">
                                                    <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                    RUNNING
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-gray-200 dark:border-gray-700">
                                                    <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                    IDLE
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-1.5 min-h-[100px]">
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                <div
                                                    class="rounded-lg p-1.5 border border-{{ $badgeColor }}-100 dark:border-{{ $badgeColor }}-800/50">
                                                    <p
                                                        class="text-[0.65rem] text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-700 dark:text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-300 font-semibold uppercase">
                                                        {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : 'TROUBLE') }}
                                                    </p>
                                                    <p
                                                        class="text-[0.6rem] text-slate-500 dark:text-slate-400 italic mt-0.5 leading-tight">
                                                        {{ Str::limit($manualStatus->description, 30) }}
                                                    </p>
                                                </div>
                                            @elseif($isActive)
                                                <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->name ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.55rem] leading-tight mt-0.5">
                                                    <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                    <span
                                                        class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->part_number ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200">{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}
                                                        WIB</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                    <div
                                                        class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-medium text-slate-700 dark:text-slate-300">
                                                        <span class="material-icons-round text-[0.45rem]">person</span>
                                                        <span
                                                            class="truncate max-w-[120px]">{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between text-[0.7rem] mb-1 font-medium">
                                                        <span
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                        <span
                                                            class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $data->judgment }}</span>
                                                    </div>
                                                    <div
                                                        class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full"
                                                            style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    class="p-2 rounded-lg text-center flex flex-col items-center justify-center h-full">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Meja Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Check QC</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($dashboardLayout['injectionJakarta'] ?? true)
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-info text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-cogs"></i></div>
                            <div>
                                <h6 class="modern-card-title">PRODUKSI INJECTION - JAKARTA</h6><small
                                    class="text-muted">Monitoring Jakarta</small>
                            </div>
                        </div>
                        <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">Running:
                            {{ $productionJakarta['activeMachines']->count() }}</span>
                    </div>
                    <div class="card-body bg-light" style="background: #fdfdfe;">
                        <div class="row px-2">
                            @foreach (array_keys($jakartaMachines) as $i)
                                @php
                                    $data = $productionJakarta['activeMachines']->get($i);
                                    $manualStatus = $productionJakarta['machineStatuses']->get($i);
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';
                                    if ($manualStatus && $manualStatus->status === 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'stopped') {
                                        $statusClass = 'status-stopped';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'trouble') {
                                        $statusClass = 'status-trouble';
                                        $isActive = false;
                                    } elseif ($isActive) {
                                        $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                    }
                                    $machineInfo = $jakartaMachines[$i] ?? null;
                                @endphp
                                <div class="col-6 col-md-6 col-lg-4 mb-4 px-2">
                                    <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group cursor-pointer {{ $statusClass === 'status-trouble' || $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}"
                                        onclick="showDetailModal(this)"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}"
                                            data-date="{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('d/m/Y') : '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                            data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"
                                        data-tonnage="{{ $machineInfo['tonnage'] ?? '-' }}" @endif @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">

                                        <div class="flex justify-between items-start mb-1.5">
                                            <div class="flex flex-col">
                                                <h4
                                                    class="text-xs font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">
                                                    MESIN-{{ $i }}</h4>
                                                @if($machineInfo)
                                                    <span
                                                        class="text-[0.55rem] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">({{ $machineInfo['tonnage'] }}T)</span>
                                                @endif
                                            </div>
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                @php
                                                    $badgeColor = $manualStatus->status === 'maintenance' ? 'yellow' : ($manualStatus->status === 'stopped' ? 'gray' : 'red');
                                                    $badgeBg = $manualStatus->status === 'maintenance' ? 'bg-yellow-50 dark:bg-yellow-900/20' : ($manualStatus->status === 'stopped' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-red-50 dark:bg-red-900/40');
                                                    $badgeText = $manualStatus->status === 'maintenance' ? 'MAINT' : ($manualStatus->status === 'stopped' ? 'IDLE' : 'TROUBLE');
                                                    $icon = $manualStatus->status === 'maintenance' ? 'engineering' : ($manualStatus->status === 'stopped' ? 'pause_circle_outline' : 'warning');
                                                @endphp
                                                <div
                                                    class="flex items-center gap-1 {{ $badgeBg }} text-{{ $badgeColor }}-700 dark:text-{{ $badgeColor }}-200 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-{{ $badgeColor }}-200 dark:border-{{ $badgeColor }}-700 shadow-sm">
                                                    <span
                                                        class="material-icons-round text-[10px] {{ $badgeColor === 'red' ? 'animate-bounce' : '' }}">{{ $icon }}</span>
                                                    {{ $badgeText }}
                                                </div>
                                            @elseif($isActive)
                                                <div
                                                    class="flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-green-200 dark:border-green-800">
                                                    <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                    RUNNING
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-gray-200 dark:border-gray-700">
                                                    <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                    IDLE
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-1.5 min-h-[100px]">
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                <div
                                                    class="rounded-lg p-1.5 border border-{{ $badgeColor }}-100 dark:border-{{ $badgeColor }}-800/50">
                                                    <p
                                                        class="text-[0.65rem] text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-700 dark:text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-300 font-semibold uppercase">
                                                        {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : 'TROUBLE') }}
                                                    </p>
                                                    <p
                                                        class="text-[0.6rem] text-slate-500 dark:text-slate-400 italic mt-0.5 leading-tight">
                                                        {{ Str::limit($manualStatus->description, 30) }}
                                                    </p>
                                                </div>
                                            @elseif($isActive)
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->name ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight mt-0.5">
                                                    <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                    <span
                                                        class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->part_number ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200">{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}
                                                        WIB</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                    <div
                                                        class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-medium text-slate-700 dark:text-slate-300">
                                                        <span class="material-icons-round text-[0.65rem]">person</span>
                                                        <span
                                                            class="truncate max-w-[120px]">{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between text-[0.5rem] mb-1 font-medium">
                                                        <span
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                        <span
                                                            class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $data->judgment }}</span>
                                                    </div>
                                                    <div
                                                        class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full"
                                                            style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-2 rounded-lg text-center">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Machine Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Check QC</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            
            @if($dashboardLayout['injectionKarawang'] ?? true)
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-warning text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-cogs"></i></div>
                            <div>
                                <h6 class="modern-card-title">PRODUKSI INJECTION - KARAWANG</h6><small
                                    class="text-muted">Monitoring
                                    Karawang</small>
                            </div>
                        </div>
                        <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">Running:
                            {{ $productionKarawang['activeMachines']->count() }}</span>
                    </div>
                    <div class="card-body bg-light" style="background: #fdfdfe;">
                        <div class="row px-2">
                            @foreach (array_keys($karawangMachines) as $i)
                                @php
                                    $data = $productionKarawang['activeMachines']->get($i);
                                    $manualStatus = $productionKarawang['machineStatuses']->get($i);
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';
                                    if ($manualStatus && $manualStatus->status === 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'stopped') {
                                        $statusClass = 'status-stopped';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'trouble') {
                                        $statusClass = 'status-trouble';
                                        $isActive = false;
                                    } elseif ($isActive) {
                                        $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                    }
                                    $machineInfo = $karawangMachines[$i] ?? null;
                                @endphp
                                <div class="col-6 col-md-6 col-lg-4 mb-4 px-2">
                                    <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group cursor-pointer {{ $statusClass === 'status-trouble' || $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}"
                                        onclick="showDetailModal(this)"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}"
                                            data-date="{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('d/m/Y') : '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                            data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"
                                        data-tonnage="{{ $machineInfo['tonnage'] ?? '-' }}" @endif @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">

                                        <div class="flex justify-between items-start mb-1.5">
                                            <div class="flex flex-col">
                                                <h4
                                                    class="text-xs font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">
                                                    MESIN-{{ $i }}</h4>
                                                @if($machineInfo)
                                                    <span
                                                        class="text-[0.55rem] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">({{ $machineInfo['tonnage'] }}T)</span>
                                                @endif
                                            </div>
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                @php
                                                    $badgeColor = $manualStatus->status === 'maintenance' ? 'yellow' : ($manualStatus->status === 'stopped' ? 'gray' : 'red');
                                                    $badgeBg = $manualStatus->status === 'maintenance' ? 'bg-yellow-50 dark:bg-yellow-900/20' : ($manualStatus->status === 'stopped' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-red-50 dark:bg-red-900/40');
                                                    $badgeText = $manualStatus->status === 'maintenance' ? 'MAINT' : ($manualStatus->status === 'stopped' ? 'IDLE' : 'TROUBLE');
                                                    $icon = $manualStatus->status === 'maintenance' ? 'engineering' : ($manualStatus->status === 'stopped' ? 'pause_circle_outline' : 'warning');
                                                @endphp
                                                <div
                                                    class="flex items-center gap-1 {{ $badgeBg }} text-{{ $badgeColor }}-700 dark:text-{{ $badgeColor }}-200 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-{{ $badgeColor }}-200 dark:border-{{ $badgeColor }}-700 shadow-sm">
                                                    <span
                                                        class="material-icons-round text-[10px] {{ $badgeColor === 'red' ? 'animate-bounce' : '' }}">{{ $icon }}</span>
                                                    {{ $badgeText }}
                                                </div>
                                            @elseif($isActive)
                                                <div
                                                    class="flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-green-200 dark:border-green-800">
                                                    <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                    RUNNING
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-gray-200 dark:border-gray-700">
                                                    <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                    IDLE
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-1.5 min-h-[100px]">
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                <div
                                                    class="rounded-lg p-1.5 border border-{{ $badgeColor }}-100 dark:border-{{ $badgeColor }}-800/50">
                                                    <p
                                                        class="text-[0.65rem] text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-700 dark:text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-300 font-semibold uppercase">
                                                        {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : 'TROUBLE') }}
                                                    </p>
                                                    <p
                                                        class="text-[0.6rem] text-slate-500 dark:text-slate-400 italic mt-0.5 leading-tight">
                                                        {{ Str::limit($manualStatus->description, 30) }}
                                                    </p>
                                                </div>
                                            @elseif($isActive)
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->name ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight mt-0.5">
                                                    <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                    <span
                                                        class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->part_number ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200">{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}
                                                        WIB</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                    <div
                                                        class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-medium text-slate-700 dark:text-slate-300">
                                                        <span class="material-icons-round text-[0.65rem]">person</span>
                                                        <span
                                                            class="truncate max-w-[120px]">{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between text-[0.5rem] mb-1 font-medium">
                                                        <span
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                        <span
                                                            class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $data->judgment }}</span>
                                                    </div>
                                                    <div
                                                        class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full"
                                                            style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-2 rounded-lg text-center">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Machine Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Check QC</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    @else

        <div class="row">
            
            @if($dashboardLayout['productionSingle'] ?? true)
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    @php
                        $plant = strtolower(optional(auth()->user()->plant)->code ?? request('plant') ?? '');
                        $tableOptions = range(1, 15);
                        if ($plant === 'jakarta') {
                            $tableOptions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        }
                    @endphp
                    <div class="modern-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;">
                                <i class="fas fa-industry"></i>
                            </div>
                            <div>
                                <h6 class="modern-card-title">Produksi Sub Assy</h6>
                                <small class="text-muted">Monitoring Produksi Sub Assy Hari Ini</small>
                            </div>
                        </div>
                        <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">
                            Running: {{ $runningLinesCount }}
                        </span>
                    </div>
                    <div class="card-body bg-light" style="background: #fdfdfe;">
                        <div class="row px-2">
                            @foreach ($tableOptions as $i)
                                @php
                                    $data = $activeLines->get($i);
                                    $manualStatus = $lineStatuses->get($i);

                                    
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';

                                    
                                    if ($manualStatus && $manualStatus->status === 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $isActive = false; 
                                    } elseif ($manualStatus && $manualStatus->status === 'stopped') {
                                        $statusClass = 'status-stopped'; 
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'trouble') {
                                        $statusClass = 'status-trouble';
                                        $isActive = false;
                                    } elseif ($isActive) {
                                        $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                    }
                                @endphp
                                <div class="col-6 col-md-6 col-lg-4 mb-4 px-2">
                                    <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group cursor-pointer {{ $statusClass === 'status-trouble' || $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}"
                                        onclick="showDetailModal(this)"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}"
                                            data-date="{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('d/m/Y') : '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                        data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}" @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">

                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex flex-col">
                                                <h4
                                                    class="text-sm font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">
                                                    MEJA-{{ $i }}</h4>
                                            </div>
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                @php
                                                    $badgeColor = $manualStatus->status === 'maintenance' ? 'yellow' : ($manualStatus->status === 'stopped' ? 'gray' : 'red');
                                                    $badgeBg = $manualStatus->status === 'maintenance' ? 'bg-yellow-50 dark:bg-yellow-900/20' : ($manualStatus->status === 'stopped' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-red-50 dark:bg-red-900/40');
                                                    $badgeText = $manualStatus->status === 'maintenance' ? 'MAINT' : ($manualStatus->status === 'stopped' ? 'IDLE' : 'TROUBLE');
                                                    $icon = $manualStatus->status === 'maintenance' ? 'engineering' : ($manualStatus->status === 'stopped' ? 'pause_circle_outline' : 'warning');
                                                @endphp
                                                <div
                                                    class="flex items-center gap-1 {{ $badgeBg }} text-{{ $badgeColor }}-700 dark:text-{{ $badgeColor }}-200 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-{{ $badgeColor }}-200 dark:border-{{ $badgeColor }}-700 shadow-sm">
                                                    <span
                                                        class="material-icons-round text-[10px] {{ $badgeColor === 'red' ? 'animate-bounce' : '' }}">{{ $icon }}</span>
                                                    {{ $badgeText }}
                                                </div>
                                            @elseif($isActive)
                                                <div
                                                    class="flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-green-200 dark:border-green-800">
                                                    <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                    RUNNING
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-gray-200 dark:border-gray-700">
                                                    <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                    IDLE
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-1.5 min-h-[100px]">
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                <div
                                                    class="rounded-lg p-1.5 border border-{{ $badgeColor }}-100 dark:border-{{ $badgeColor }}-800/50">
                                                    <p
                                                        class="text-[0.65rem] text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-700 dark:text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-300 font-semibold uppercase">
                                                        {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : 'TROUBLE') }}
                                                    </p>
                                                    <p
                                                        class="text-[0.6rem] text-slate-500 dark:text-slate-400 italic mt-0.5 leading-tight">
                                                        {{ Str::limit($manualStatus->description, 30) }}
                                                    </p>
                                                </div>
                                            @elseif($isActive)
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->name ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight mt-0.5">
                                                    <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                    <span
                                                        class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->part_number ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200">{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}
                                                        WIB</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                    <div
                                                        class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-medium text-slate-700 dark:text-slate-300">
                                                        <span class="material-icons-round text-[0.65rem]">person</span>
                                                        <span
                                                            class="truncate max-w-[120px]">{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between text-[0.5rem] mb-1 font-medium">
                                                        <span
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                        <span
                                                            class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $data->judgment }}</span>
                                                    </div>
                                                    <div
                                                        class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full"
                                                            style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    class="p-2 rounded-lg text-center flex flex-col items-center justify-center h-full">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Meja Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Check QC</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            
            @if($dashboardLayout['injectionSingle'] ?? true)
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-info text-white mr-3"
                                style="width: 40px; height: 40px; font-size: 1rem;">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div>
                                <h6 class="modern-card-title">Produksi Injection</h6>
                                <small class="text-muted">Monitoring Produksi Injection Hari Ini</small>
                            </div>
                        </div>
                        <span class="badge badge-info px-3 py-2 rounded-pill shadow-sm text-white">
                            Running: {{ $runningMachinesCount }}
                        </span>
                    </div>
                    <div class="card-body bg-light" style="background: #fdfdfe;">
                        <div class="row px-2">
                            @php
                                $plant = strtolower(optional(auth()->user()->plant)->code ?? request('plant') ?? '');
                                $machinesToDisplay = ($plant === 'jakarta') ? array_keys($jakartaMachines) : array_keys($karawangMachines);
                            @endphp
                            @foreach ($machinesToDisplay as $i)
                                @php
                                    $data = $activeMachines->get($i);
                                    $manualStatus = $machineStatuses->get($i);

                                    $machineInfo = ($plant === 'jakarta') ? ($jakartaMachines[$i] ?? null) : ($karawangMachines[$i] ?? null);
                                    $tonnage = $machineInfo['tonnage'] ?? '-';

                                    
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';

                                    
                                    if ($manualStatus && $manualStatus->status === 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'stopped') {
                                        $statusClass = 'status-stopped';
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'trouble') {
                                        $statusClass = 'status-trouble';
                                        $isActive = false;
                                    } elseif ($isActive) {
                                        $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                    }
                                @endphp
                                <div class="col-6 col-md-6 col-lg-4 mb-4 px-2">
                                    <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group cursor-pointer {{ $statusClass === 'status-trouble' || $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}"
                                        onclick="showDetailModal(this)"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}"
                                            data-date="{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('d/m/Y') : '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                            data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"
                                        data-tonnage="{{ $tonnage }}" @endif @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">

                                        <div class="flex justify-between items-start mb-1.5">
                                            <div class="flex flex-col">
                                                <h4
                                                    class="text-xs font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">
                                                    MESIN-{{ $i }}</h4>
                                                @if($machineInfo)
                                                    <span
                                                        class="text-[0.55rem] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">({{ $machineInfo['tonnage'] }}T)</span>
                                                @endif
                                            </div>
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                @php
                                                    $badgeColor = $manualStatus->status === 'maintenance' ? 'yellow' : ($manualStatus->status === 'stopped' ? 'gray' : 'red');
                                                    $badgeBg = $manualStatus->status === 'maintenance' ? 'bg-yellow-50 dark:bg-yellow-900/20' : ($manualStatus->status === 'stopped' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-red-50 dark:bg-red-900/40');
                                                    $badgeText = $manualStatus->status === 'maintenance' ? 'MAINT' : ($manualStatus->status === 'stopped' ? 'IDLE' : 'TROUBLE');
                                                    $icon = $manualStatus->status === 'maintenance' ? 'engineering' : ($manualStatus->status === 'stopped' ? 'pause_circle_outline' : 'warning');
                                                @endphp
                                                <div
                                                    class="flex items-center gap-1 {{ $badgeBg }} text-{{ $badgeColor }}-700 dark:text-{{ $badgeColor }}-200 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-{{ $badgeColor }}-200 dark:border-{{ $badgeColor }}-700 shadow-sm">
                                                    <span
                                                        class="material-icons-round text-[10px] {{ $badgeColor === 'red' ? 'animate-bounce' : '' }}">{{ $icon }}</span>
                                                    {{ $badgeText }}
                                                </div>
                                            @elseif($isActive)
                                                <div
                                                    class="flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-green-200 dark:border-green-800">
                                                    <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                    RUNNING
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-gray-200 dark:border-gray-700">
                                                    <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                    IDLE
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-1.5 min-h-[100px]">
                                            @if($manualStatus && $manualStatus->status !== 'normal')
                                                <div
                                                    class="rounded-lg p-1.5 border border-{{ $badgeColor }}-100 dark:border-{{ $badgeColor }}-800/50">
                                                    <p
                                                        class="text-[0.65rem] text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-700 dark:text-{{ $badgeColor === 'gray' ? 'slate' : $badgeColor }}-300 font-semibold uppercase">
                                                        {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : 'TROUBLE') }}
                                                    </p>
                                                    <p
                                                        class="text-[0.6rem] text-slate-500 dark:text-slate-400 italic mt-0.5 leading-tight">
                                                        {{ Str::limit($manualStatus->description, 30) }}
                                                    </p>
                                                </div>
                                            @elseif($isActive)
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->name ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight mt-0.5">
                                                    <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                    <span
                                                        class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->part_number ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                    <span
                                                        class="font-bold text-slate-700 dark:text-slate-200">{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}
                                                        WIB</span>
                                                </div>
                                                <div class="flex items-center justify-between text-[0.65rem] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                    <div
                                                        class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-medium text-slate-700 dark:text-slate-300">
                                                        <span class="material-icons-round text-[0.65rem]">person</span>
                                                        <span
                                                            class="truncate max-w-[120px]">{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between text-[0.5rem] mb-1 font-medium">
                                                        <span
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                        <span
                                                            class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $data->judgment }}</span>
                                                    </div>
                                                    <div
                                                        class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full"
                                                            style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-2 rounded-lg text-center">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Machine Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Check QC</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif

            <div class="col-12 mb-5">
                <div class="row">
                    <!-- Kiri: Plating, Painting, Cross Cut & Double Tape -->
                    <div class="col-12">
                        @if(($dashboardLayout['monitoringPlating'] ?? true) || ($dashboardLayout['monitoringPainting'] ?? true))
                        <div class="mb-4">
                            <div class="modern-card h-100">
                                <div class="modern-card-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-warning text-white mr-3"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-layer-group"></i></div>
                                        <div>
                                            <h6 class="modern-card-title">PRODUKSI PLATING, PAINTING & DOUBLE TAPE</h6><small
                                                class="text-muted">Monitoring {{ ucfirst($currentPlant ?? 'Karawang') }}</small>
                                        </div>
                                    </div>
                                    @php
                                        $activeCount = 0;
                                        foreach (range(1, 12) as $i) {
                                            if ($activePlating->get($i) || $activePainting->get($i)) {
                                                $activeCount++;
                                            }
                                        }
                                    @endphp
                                    <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">Running: {{ $activeCount }}</span>
                                </div>
                                <div class="card-body bg-light" style="background: #fdfdfe;">
                                    <div class="row px-2">
                                        @foreach (range(1, 12) as $i)
                                            @php
                                                $d_plate = $activePlating->get($i) ?? null;
                                                $d_paint = $activePainting->get($i) ?? null;
                                                $d_cc_plate = $activeCrossCutPlating->get($i) ?? null;
                                                $d_cc_paint = $activeCrossCutPainting->get($i) ?? null;
                                                
                                                $latestData = null;
                                                $type = null;
                                                
                                                $candidates = [
                                                    ['Plating', $d_plate],
                                                    ['Painting', $d_paint],
                                                    ['Cross Cut Plating', $d_cc_plate],
                                                    ['Cross Cut Painting', $d_cc_paint],
                                                ];

                                                $maxTime = null;
                                                foreach($candidates as [$cType, $cData]) {
                                                    if ($cData) {
                                                        $cTime = $cData->created_at;
                                                        if (!$maxTime || $cTime > $maxTime) {
                                                            $maxTime = $cTime;
                                                            $latestData = $cData;
                                                            $type = $cType;
                                                        }
                                                    }
                                                }

                                                $isActive = $latestData ? true : false;
                                                $isNg = false;
                                                if ($isActive) {
                                                    $isNg = isset($latestData->judgment) ? $latestData->judgment === 'NG' : (isset($latestData->position_remark_judgment) ? $latestData->position_remark_judgment === 'NG' : false);
                                                }
                                                $statusClass = 'status-idle';
                                                if ($isActive) {
                                                    $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                                }
                                                
                                                $typeColor = 'info';
                                                if ($type === 'Plating') $typeColor = 'warning';
                                                elseif ($type === 'Painting') $typeColor = 'primary';
                                                elseif ($type === 'Cross Cut Plating') $typeColor = 'secondary';
                                                elseif ($type === 'Cross Cut Painting') $typeColor = 'dark';
                                            @endphp
                                            <div class="col-6 col-md-4 col-lg-2 mb-3 px-2">
                                                <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group cursor-pointer {{ $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}"
                                                    data-status="{{ $isActive ? 'active' : 'idle' }}"
                                                    @if($isActive) data-part-number="{{ $latestData->item->part_number ?? '-' }}"
                                                        data-item-name="{{ $latestData->item->name ?? '-' }}" data-judgment="{{ $latestData->judgment ?? $latestData->position_remark_judgment ?? '-' }}"
                                                        data-total-qty="{{ $latestData->total_qty ?? '-' }}"
                                                        data-sampling-qty="{{ $latestData->sampling_qty ?? '-' }}"
                                                        data-ok-count="{{ $latestData->total_ok ?? '-' }}" data-ng-count="{{ $latestData->total_ng ?? '-' }}"
                                                        data-operator="{{ $operatorMap[$latestData->operator_initials] ?? $latestData->operator_initials ?? '-' }}"
                                                        data-date="{{ $latestData->date ?? ($latestData->production_datetime ? \Carbon\Carbon::parse($latestData->production_datetime)->format('Y-m-d') : null) ? \Carbon\Carbon::parse($latestData->date ?? $latestData->production_datetime)->format('d/m/Y') : '-' }}"
                                                        data-shift="{{ $latestData->shift ?? $latestData->production_shift ?? '-' }}"
                                                    data-time="{{ $latestData->created_at ? $latestData->created_at->format('H:i') : '-' }}" @endif
                                                    title="Click untuk detail">
                                                    
                                                    <div class="flex justify-between items-start mb-2">
                                                        <div class="flex flex-col">
                                                            <h4 class="text-sm font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">MEJA-{{ $i }}</h4>
                                                        </div>
                                                        @if($isActive)
                                                            <div class="flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-green-200 dark:border-green-800">
                                                                <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                                RUNNING
                                                            </div>
                                                        @else
                                                            <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-gray-200 dark:border-gray-700">
                                                                <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                                IDLE
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="space-y-1.5 min-h-[100px]">
                                                        @if($isActive)
                                                            <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                                <span class="text-slate-500 dark:text-slate-400">Proses</span>
                                                                <div class="flex items-center gap-1 bg-{{ $typeColor }} px-1.5 py-0.5 rounded text-white font-bold text-[0.5rem] tracking-wider uppercase shadow-sm">
                                                                    {{ $type }}
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center justify-between text-[0.55rem] leading-tight mt-1">
                                                                <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                                <span class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $latestData->item->name ?? '-' }}</span>
                                                            </div>
                                                            <div class="flex items-center justify-between text-[0.55rem] leading-tight mt-0.5">
                                                                <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                                <span class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $latestData->item->part_number ?? '-' }}</span>
                                                            </div>
                                                            <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                                <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $latestData->created_at ? $latestData->created_at->format('H:i') : '-' }} WIB</span>
                                                            </div>
                                                            <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                                <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                                <div class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-medium text-slate-700 dark:text-slate-300">
                                                                    <span class="material-icons-round text-[0.45rem]">person</span>
                                                                    <span class="truncate max-w-[120px]">{{ $operatorMap[$latestData->operator_initials] ?? $latestData->operator_initials ?? '-' }}</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="flex justify-between text-[0.7rem] mb-1 font-medium">
                                                                    <span class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                                    <span class="{{ ($latestData->judgment ?? $latestData->position_remark_judgment ?? '') === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $latestData->judgment ?? $latestData->position_remark_judgment ?? 'NG' }}</span>
                                                                </div>
                                                                <div class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                                    <div class="bg-gradient-to-r {{ ($latestData->judgment ?? $latestData->position_remark_judgment ?? '') === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full" style="width: 100%"></div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="p-2 rounded-lg text-center flex flex-col items-center justify-center h-full">
                                                                <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Meja Idle</p>
                                                                <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait Check QC</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Double Tape as 13th box -->
                                        @if($dashboardLayout['monitoringDoubleTape'] ?? true)
                                        <div class="col-6 col-md-4 col-lg-2 mb-3 px-2">
                                            @php
                                                $data = $latestDoubleTape ?? null;
                                                $isActive = $data ? true : false;
                                                $isNg = $isActive && $data->judgment === 'NG';
                                                $statusClass = 'status-idle';
                                                if ($isActive) {
                                                    $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                                }
                                            @endphp
                                            <div class="status-item bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-3 hover:shadow-lg transition group {{ $statusClass === 'status-active-danger' ? 'border-2 border-red-500 dark:border-red-600 border-pulse-red' : '' }}">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="flex flex-col">
                                                        <h4 class="text-sm font-bold text-slate-800 dark:text-white mt-0.5 whitespace-nowrap">DOUBLE TAPE</h4>
                                                    </div>
                                                    @if($isActive)
                                                        <div class="flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-green-200 dark:border-green-800">
                                                            <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                                                            RUNNING
                                                        </div>
                                                    @else
                                                        <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full text-[0.55rem] font-bold border border-gray-200 dark:border-gray-700">
                                                            <span class="material-icons-round text-[10px]">pause_circle_outline</span>
                                                            IDLE
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="space-y-1.5 min-h-[100px]">
                                                    @if($isActive)
                                                        <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                            <span class="text-slate-500 dark:text-slate-400">Proses</span>
                                                            <div class="flex items-center gap-1 bg-dark px-1.5 py-0.5 rounded text-white font-bold text-[0.5rem] tracking-wider uppercase shadow-sm">
                                                                D. Tape
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center justify-between text-[0.55rem] leading-tight mt-1">
                                                            <span class="text-slate-500 dark:text-slate-400">Item</span>
                                                            <span class="font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->name ?? '-' }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between text-[0.55rem] leading-tight mt-0.5">
                                                            <span class="text-slate-500 dark:text-slate-400">Part No.</span>
                                                            <span class="font-mono font-bold text-slate-700 dark:text-slate-200 truncate ml-2 text-right">{{ $data->item->part_number ?? '-' }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                            <span class="text-slate-500 dark:text-slate-400">Jam</span>
                                                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $data->created_at ? $data->created_at->format('H:i') : '-' }} WIB</span>
                                                        </div>
                                                        <div class="flex items-center justify-between text-[0.55rem] leading-tight">
                                                            <span class="text-slate-500 dark:text-slate-400">QC</span>
                                                            <div class="flex items-center gap-1 bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-medium text-slate-700 dark:text-slate-300">
                                                                <span class="material-icons-round text-[0.45rem]">person</span>
                                                                <span class="truncate max-w-[120px]">{{ $operatorMap[$data->operator_initials] ?? $data->operator_initials ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="flex justify-between text-[0.7rem] mb-1 font-medium">
                                                                <span class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter">Status</span>
                                                                <span class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold">{{ $data->judgment }}</span>
                                                            </div>
                                                            <div class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                                <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-full rounded-full" style="width: 100%"></div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="p-2 rounded-lg text-center flex flex-col items-center justify-center h-full">
                                                            <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Meja Idle</p>
                                                            <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait Check QC</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

    
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="detailModalLabel">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span id="modalUnitName"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/dashboard/dashboard-ui.js') }}"></script>
    @endpush
@endsection



