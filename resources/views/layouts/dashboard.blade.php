@extends('layouts.admin')

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
            // 13 skipped
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
            // 11 removed as requested
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
        /* Modern Dashboard CSS */
        :root {
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: 1px solid rgba(255, 255, 255, 0.2);
            --shadow-soft: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 20px 40px -5px rgba(0, 0, 0, 0.12);
            --gradient-primary: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            --gradient-success: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            --gradient-danger: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
            --gradient-info: linear-gradient(135deg, #36b9cc 0%, #2c9faf 100%);
            --gradient-warning: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            --gradient-dark: linear-gradient(135deg, #5a5c69 0%, #373840 100%);
            --gradient-idle: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
        }

        .dashboard-container {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Modern Card */
        .modern-card {
            background: #fff;
            border-radius: 15px;
            /* Reduced radius */
            border: none;
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            height: 100%;
        }

        .modern-card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            padding: 0.5rem;
            /* Reduced to 0.5rem */
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modern-card-title {
            font-weight: 800;
            font-size: 0.9rem;
            /* Reduced to 0.9rem */
            color: #2d3748;
            margin: 0;
            letter-spacing: -0.025em;
        }

        /* Status Grid Item */
        .status-item {
            position: relative;
            border-radius: 10px;
            /* Reduced to 10px */
            padding: 0;
            min-height: 65px;
            /* Reduced to 65px */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            cursor: default;
        }

        .status-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }

        /* Variants */
        .status-active-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 8px 15px rgba(28, 200, 138, 0.3);
        }

        .status-active-danger {
            background: var(--gradient-danger);
            color: white;
            box-shadow: 0 8px 15px rgba(231, 74, 59, 0.3);
            animation: pulse-red 2s infinite;
        }

        .status-maintenance {
            background: var(--gradient-warning);
            color: white;
            box-shadow: 0 8px 15px rgba(246, 194, 62, 0.3);
        }

        .status-stopped {
            background: var(--gradient-dark);
            color: white;
            box-shadow: 0 8px 15px rgba(90, 92, 105, 0.3);
        }

        .status-trouble {
            background: var(--gradient-danger);
            color: white;
            box-shadow: 0 8px 15px rgba(231, 74, 59, 0.4);
            animation: pulse-red 2s infinite;
        }

        .status-idle {
            background: var(--gradient-idle);
            color: #858796;
            border: 1px solid #e3e6f0;
        }

        .status-idle:hover {
            border-color: #b7b9cc;
            background: white;
        }

        /* Typography inside cards */
        /* Typography inside cards */
        .unit-number {
            font-size: 0.8rem;
            /* Reduced to 0.8rem */
            font-weight: 800;
            line-height: 1;
            margin-bottom: 2px;
        }

        .part-number {
            font-size: 0.55rem;
            /* Reduced to 0.55rem */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.9;
            margin-bottom: 1px;
            max-width: 90%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-name {
            font-size: 0.45rem;
            /* Reduced to 0.45rem */
            font-weight: 400;
            opacity: 0.8;
            line-height: 1.1;
            max-width: 85%;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .status-badge {
            margin-top: 2px;
            /* Reduced to 2px */
            font-size: 0.65rem;
            /* Reduced to 0.65rem */
            font-weight: 800;
            padding: 1px 6px;
            /* Reduced to 1px 6px */
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
        }

        .status-badge-manual {
            font-size: 0.45rem;
            /* Reduced to 0.45rem */
            font-weight: 700;
            padding: 1px 4px;
            /* Reduced to 1px 4px */
            letter-spacing: 0.02em;
        }

        .status-idle .unit-number {
            opacity: 0.4;
            font-size: 0.9rem;
            /* Reduced to 0.9rem */
        }

        .status-idle .item-name {
            font-size: 0.5rem;
            /* Reduced to 0.5rem */
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(231, 74, 59, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0);
            }
        }

        /* Welcome Section Modern */
        .welcome-modern {
            background: var(--gradient-primary);
            border-radius: 12px;
            /* Reduced to 12px */
            padding: 1rem;
            /* Reduced to 1rem */
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.25);
            margin-bottom: 1rem;
            /* Reduced to 1rem */
        }

        /* Stats Cards Modern */
        .stat-card-modern {
            background: white;
            border-radius: 12px;
            /* Reduced to 12px */
            padding: 0.5rem;
            /* Reduced to 0.5rem */
            height: 100%;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            /* Reduced to 3px */
        }

        .stat-card-modern:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        /* Responsive Design for Small Screens */
        @media (max-width: 768px) {
            .welcome-modern {
                padding: 0.75rem !important;
                /* Reduced */
                margin-bottom: 0.75rem !important;
                border-radius: 12px !important;
            }

            .welcome-modern h4 {
                font-size: 1rem !important;
                /* Reduced */
            }

            .welcome-modern p {
                font-size: 0.75rem !important;
                /* Reduced */
            }

            #current-date {
                font-size: 0.8rem !important;
                /* Reduced */
            }

            .stat-card-modern {
                padding: 0.6rem !important;
                border-radius: 12px !important;
                margin-bottom: 0.6rem !important;
            }

            .stat-card-modern .h3 {
                font-size: 1.2rem !important;
                /* Reduced */
            }

            .stat-card-modern .text-xs {
                font-size: 0.6rem !important;
                /* Reduced */
            }

            .modern-card {
                border-radius: 12px !important;
            }

            .modern-card-header {
                padding: 0.75rem !important;
            }

            .modern-card-title {
                font-size: 0.9rem !important;
            }

            .status-item {
                min-height: 80px !important;
                /* Reduced */
                padding: 0.25rem !important;
            }

            .unit-number {
                font-size: 0.75rem !important;
            }

            .part-number {
                font-size: 0.55rem !important;
            }

            .item-name {
                font-size: 0.5rem !important;
            }

            .status-badge {
                font-size: 0.5rem !important;
                padding: 2px 6px !important;
            }
        }

        @media (max-width: 576px) {
            .welcome-modern {
                padding: 0.5rem !important;
            }

            .welcome-modern h4 {
                font-size: 0.9rem !important;
            }

            .stat-card-modern {
                padding: 0.4rem !important;
            }

            .stat-card-modern .h3 {
                font-size: 1rem !important;
            }

            .status-item {
                min-height: 70px !important;
            }

            .unit-number {
                font-size: 0.7rem !important;
            }
        }

        /* Pie Chart Styling */
        .chart-pie {
            position: relative;
            height: 15rem;
        }

        .chart-pie canvas {
            height: 100% !important;
            width: 100% !important;
        }
    </style>

    <!-- Welcome Section -->
    <div class="row">
        <div class="col-12">
            <div class="welcome-modern shadow">
                <!-- SVG Background Decoration -->
                <div
                    style="position: absolute; top: 0; right: 0; width: 100%; height: 100%; opacity: 0.1; background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
                </div>

                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-lg-8">
                        <h4 class="font-weight-bold mb-1">Selamat Datang, {{ Auth::user()->name }}! </h4>
                        <p class="mb-0" style="opacity: 0.9; font-size: 0.9rem;">Quality Department</p>
                        <div class="mt-3">
                            <span class="badge badge-light text-primary px-3 py-2 rounded-pill shadow-sm"
                                style="font-size: 0.85rem;">
                                <i class="fas fa-user-tag mr-1"></i> {{ getRoleDisplayName(Auth::user()->role) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4 text-right d-none d-lg-block">
                        <div class="h3 mb-0 font-weight-bold" id="current-date">Loading...</div>
                        <small style="opacity: 0.8; font-size: 0.9rem;"><i class="fas fa-clock mr-1"></i> <span
                                id="current-time"></span> WIB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Customer Claim Achievement Chart --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-danger text-white mr-3"
                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h6 class="modern-card-title">
                                @if($claimData['year'] === 'combined')
                                    CLAIM CUSTOMER QUALITY
                                @elseif($claimData['year'] === 'all')
                                    TREN TAHUNAN
                                @else
                                    PENCAPAIAN CLAIM CUSTOMER {{ $claimData['year'] }}
                                @endif
                            </h6>
                            <div class="small text-muted">
                                @if($claimData['year'] === 'combined')
                                    Data Claim Customer Jakarta-Karawang
                                @elseif($claimData['year'] === 'all')
                                    Rata-rata PPM per Tahun
                                @else
                                    Statistik Bulanan PPM
                                @endif
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('dashboard') }}" method="GET" class="form-inline">
                        <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="combined" {{ ($claimData['year'] ?? '') == 'combined' ? 'selected' : '' }}>All
                            </option>
                            <option value="all" {{ ($claimData['year'] ?? '') == 'all' ? 'selected' : '' }}>Tren Tahunan
                                (Summary)</option>
                            @php $currentY = date('Y'); @endphp
                            @for($y = $currentY; $y >= 2022; $y--)
                                <option value="{{ $y }}" {{ ($claimData['year'] ?? $currentY) == $y && !in_array($claimData['year'], ['all', 'combined']) ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <div id="chartCustomerClaim" style="height: 400px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($combinedStats))
        <div class="row">
            @php
                $role = auth()->user()->role;
                $isDualView = in_array($role, ['admin', 'manager', 'asst_manager', 'manager_qc', 'asst_manager_qc']);
            @endphp
            @if($isDualView && isset($statsJakarta) && isset($statsKarawang))
                {{-- Jakarta Chart (Left) --}}
                <div class="col-xl-6 col-lg-12 mb-5">
                    <div class="modern-card h-100">
                        <div class="modern-card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary text-white mr-3"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <h6 class="modern-card-title">Status Approval - Jakarta</h6>
                                    <div class="small text-muted">Statistik Jakarta</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-light" style="background: #fdfdfe;">
                            <div id="chartJakarta" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
                {{-- Karawang Chart (Right) --}}
                <div class="col-xl-6 col-lg-12 mb-5">
                    <div class="modern-card h-100">
                        <div class="modern-card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-success text-white mr-3"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-industry"></i>
                                </div>
                                <div>
                                    <h6 class="modern-card-title">Status Approval - Karawang</h6>
                                    <div class="small text-muted">Statistik Karawang</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-light" style="background: #fdfdfe;">
                            <div id="chartKarawang" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- NG Rate Jakarta --}}
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

                {{-- NG Rate Karawang --}}
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
            @else
                {{-- Combined/Single Chart --}}
                <div class="col-xl-6 col-lg-12 mb-5">
                    <div class="modern-card h-100">
                        <div class="modern-card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-info text-white mr-3"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div>
                                    <h6 class="modern-card-title">Total Approval</h6>
                                    <div class="small text-muted">Statistik Global</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-light" style="background: #fdfdfe;">
                            <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- NG Rate Single --}}
                <div class="col-xl-6 col-lg-12 mb-5">
                    <div class="modern-card h-100">
                        <div class="modern-card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary text-white mr-3"
                                    style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    @php
                                        $currentPlantDisplay = strtolower(Auth::user()->plant->code ?? request('plant') ?? 'jakarta');
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
        </div>

        @push('scripts')
            <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
            <script>
                function explodePie(e) {
                    if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
                        e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
                    } else {
                        e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
                    }
                    e.chart.render();
                }

                window.onload = function () {
                    @if($isDualView && isset($statsJakarta) && isset($statsKarawang))
                        var statsJakarta = @json($statsJakarta);
                        var statsKarawang = @json($statsKarawang);

                        renderChart("chartJakarta", "Status Approval - Jakarta", statsJakarta);
                        renderChart("chartKarawang", "Status Approval - Karawang", statsKarawang);
                    @else
                                                                                                                                                                                                                                                                                                                                                                                                                    var combinedStats = @json($combinedStats);
                        renderChart("chartContainer", "Status Approval", combinedStats);
                    @endif

                    // Customer Claim Chart
                    renderClaimChart();

                    // NG Rate Charts
                    renderNgRateCharts();
                }

                function renderClaimChart() {
                    var claimData = @json($claimData);
                    var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                    var dataJkt = claimData.jakarta.map(function (val, index) {
                        var dp = { label: claimData.labels[index], y: val };
                        if (val > 0) {
                            dp.indexLabel = val.toString();
                            dp.indexLabelPlacement = "outside";
                            dp.indexLabelFontColor = "#000000";
                            dp.indexLabelFontWeight = "bold";
                            dp.indexLabelFontSize = 10;
                            dp.indexLabelFontFamily = "Nunito";
                        }
                        return dp;
                    });

                    var dataKrw = claimData.karawang.map(function (val, index) {
                        var dp = { label: claimData.labels[index], y: val };
                        if (val > 0) {
                            dp.indexLabel = val.toString();
                            dp.indexLabelPlacement = "outside";
                            dp.indexLabelFontColor = "#000000";
                            dp.indexLabelFontWeight = "bold";
                            dp.indexLabelFontSize = 10;
                            dp.indexLabelFontFamily = "Nunito";
                        }
                        return dp;
                    });

                    var dataTarget = claimData.target.map(function (val, index) {
                        var dp = { label: claimData.labels[index], y: val };
                        // Add indexLabel to first and last points of target
                        if (index === 0 || index === claimData.target.length - 1) {
                            dp.indexLabel = "{y}";
                            dp.indexLabelFontWeight = "bold";
                            dp.indexLabelFontSize = 12; // Slightly smaller
                            dp.indexLabelFontColor = "#e74a3b";
                        }
                        return dp;
                    });

                    var chart = new CanvasJS.Chart("chartCustomerClaim", {
                        animationEnabled: true,
                        theme: "light2",
                        title: {
                            text: "", // Title is in the header
                            fontFamily: "Nunito"
                        },
                        axisX: {
                            interval: 1,
                            labelFontFamily: "Nunito"
                        },
                        axisY: {
                            title: "Ppm",
                            titleFontFamily: "Nunito",
                            labelFontFamily: "Nunito",
                            includeZero: true,
                            minimum: 0,
                            // Add extra headroom for labels
                            maximum: Math.max(...claimData.jakarta, ...claimData.karawang, ...claimData.target) * 1.3
                        },
                        toolTip: {
                            shared: true,
                            fontFamily: "Nunito"
                        },
                        legend: {
                            cursor: "pointer",
                            itemclick: toggleDataSeries,
                            fontFamily: "Nunito"
                            ,
                            verticalAlign: "center",
                            horizontalAlign: "right"
                        },
                        data: [
                            {
                                type: "column",
                                name: "Jakarta",
                                showInLegend: true,
                                color: "#4e73df",
                                dataPoints: dataJkt
                            },
                            {
                                type: "column",
                                name: "Karawang",
                                showInLegend: true,
                                color: "#1cc88a",
                                dataPoints: dataKrw
                            },
                            {
                                type: "line",
                                name: "Target",
                                showInLegend: true,
                                color: "#e74a3b",
                                markerSize: 6, // Slightly smaller marker
                                lineThickness: 2, // Thinner line
                                yValueFormatString: "##0.00",
                                dataPoints: dataTarget
                            }
                        ]
                    });
                    chart.render();
                }

                function toggleDataSeries(e) {
                    if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                        e.dataSeries.visible = false;
                    } else {
                        e.dataSeries.visible = true;
                    }
                    e.chart.render();
                }

                function renderChart(containerId, title, stats) {
                    var chart = new CanvasJS.Chart(containerId, {
                        exportEnabled: true,
                        animationEnabled: true,
                        title: {
                            text: title,
                            fontSize: 18,
                            fontFamily: "Nunito"
                        },
                        legend: {
                            cursor: "pointer",
                            itemclick: explodePie
                        },
                        data: [{
                            type: "pie",
                            showInLegend: true,
                            toolTipContent: "{name}: <strong>{y}</strong>",
                            indexLabel: "{name} - {y}",
                            dataPoints: [
                                { y: stats.pending, name: "Pending", color: "#f6c23e", exploded: true },
                                { y: stats.approved, name: "Approved", color: "#1cc88a" },
                                { y: stats.rejected, name: "Rejected", color: "#e74a3b" }
                            ]
                        }]
                    });
                    chart.render();
                }

                function renderNgRateCharts() {
                    const ngData = @json($ngRateData ?? null);
                    if (!ngData) return;

                    const isDualView = {{ $isDualView ? 'true' : 'false' }};
                    const currentPlant = "{{ strtolower($currentPlant ?? '') }}";

                    if (isDualView) {
                        renderSingleNgChart("chartNgJakarta", "Jakarta", ngData.jakarta, ngData.labels);
                        renderSingleNgChart("chartNgKarawang", "Karawang", ngData.karawang, ngData.labels);
                    } else {
                        const plantData = currentPlant === 'jakarta' ? ngData.jakarta : ngData.karawang;
                        renderSingleNgChart("chartNgSingle", currentPlant.toUpperCase(), plantData, ngData.labels);
                    }
                }

                function renderSingleNgChart(containerId, plantName, plantData, labels) {
                    const series = [];

                    if (plantData.sub_assy) {
                        series.push({
                            name: "Sub Assy",
                            type: "spline",
                            showInLegend: true,
                            yValueFormatString: "##0.00'%'",
                            dataPoints: labels.map((l, i) => ({ label: l.split('-').slice(1).reverse().join('/'), y: plantData.sub_assy[i] }))
                        });
                    }

                    if (plantData.in_process) {
                        series.push({
                            name: "In Process",
                            type: "spline",
                            showInLegend: true,
                            yValueFormatString: "##0.00'%'",
                            dataPoints: labels.map((l, i) => ({ label: l.split('-').slice(1).reverse().join('/'), y: plantData.in_process[i] }))
                        });
                    }

                    if (plantData.cross_cut) {
                        series.push({
                            name: "Cross Cut",
                            type: "spline",
                            showInLegend: true,
                            yValueFormatString: "##0.00'%'",
                            dataPoints: labels.map((l, i) => ({ label: l.split('-').slice(1).reverse().join('/'), y: plantData.cross_cut[i] }))
                        });
                    }

                    const chart = new CanvasJS.Chart(containerId, {
                        animationEnabled: true,
                        theme: "light2",
                        title: {
                            text: "",
                            fontFamily: "Nunito"
                        },
                        toolTip: {
                            shared: true,
                            fontFamily: "Nunito"
                        },
                        legend: {
                            cursor: "pointer",
                            itemclick: toggleDataSeries,
                            fontFamily: "Nunito"
                        },
                        axisX: {
                            labelFontFamily: "Nunito",
                            labelFontSize: 10
                        },
                        axisY: {
                            title: "NG Rate (%)",
                            suffix: "%",
                            titleFontFamily: "Nunito",
                            labelFontFamily: "Nunito"
                        },
                        data: series
                    });
                    chart.render();
                }
            </script>
        @endpush
    @endif


    <!-- Production Status Section -->

    @if(isset($isDualView) && $isDualView && isset($productionJakarta) && isset($productionKarawang))
        {{-- DUAL VIEW MODE --}}
        <div class="row">
            {{-- Sub Assy Jakarta (Left) --}}
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-success text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-industry"></i></div>
                            <div>
                                <h6 class="modern-card-title">Produksi Sub Assy - Jakarta</h6><small
                                    class="text-muted">Monitoring Jakarta</small>
                            </div>
                        </div>
                        <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">Running:
                            {{ $productionJakarta['activeLines']->count() }}</span>
                    </div>
                    <div class="card-body bg-light" style="background: #fdfdfe;">
                        <div class="row px-2">
                            @foreach ([1, 2, 4, 5, 6, 7, 8, 9, 10, 11] as $i)
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
                                <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                    <div class="status-item {{ $statusClass }}" onclick="showDetailModal(this)"
                                        style="cursor: pointer;"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $data->operator_initials ?? '-' }}" data-date="{{ $data->date ?? '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                        data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}" @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">
                                        <div class="unit-number">MEJA-{{ $i }}</div>
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            <div class="status-badge status-badge-manual"
                                                style="background: rgba(255,255,255,0.3); margin-top: 5px;">
                                                {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : strtoupper($manualStatus->status)) }}
                                            </div>
                                            <small class="text-white small mt-1"
                                                style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
                                        @elseif($isActive)
                                            <div class="part-number">{{ $data->item->part_number ?? 'NO PART' }}</div>
                                            <div class="item-name text-center px-2">{{ $data->item->name ?? '-' }}</div>
                                            <div class="status-badge">{{ $data->judgment }}</div>
                                        @else <div class="item-name mt-2 font-weight-bold">IDLE</div> @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sub Assy Karawang (Right) --}}
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-industry"></i></div>
                            <div>
                                <h6 class="modern-card-title">Produksi Sub Assy - Karawang</h6><small
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
                                <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                    <div class="status-item {{ $statusClass }}" onclick="showDetailModal(this)"
                                        style="cursor: pointer;"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $data->operator_initials ?? '-' }}" data-date="{{ $data->date ?? '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                        data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}" @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">
                                        <div class="unit-number">MEJA-{{ $i }}</div>
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            <div class="status-badge status-badge-manual"
                                                style="background: rgba(255,255,255,0.3); margin-top: 5px;">
                                                {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : strtoupper($manualStatus->status)) }}
                                            </div>
                                            <small class="text-white small mt-1"
                                                style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
                                        @elseif($isActive)
                                            <div class="part-number">{{ $data->item->part_number ?? 'NO PART' }}</div>
                                            <div class="item-name text-center px-2">{{ $data->item->name ?? '-' }}</div>
                                            <div class="status-badge">{{ $data->judgment }}</div>
                                        @else <div class="item-name mt-2 font-weight-bold">IDLE</div> @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Injection Jakarta (Left) --}}
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-info text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-cogs"></i></div>
                            <div>
                                <h6 class="modern-card-title">Produksi Injection - Jakarta</h6><small
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
                                <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                    <div class="status-item {{ $statusClass }}" onclick="showDetailModal(this)"
                                        style="cursor: pointer;"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $data->operator_initials ?? '-' }}" data-date="{{ $data->date ?? '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                            data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"
                                        data-tonnage="{{ $machineInfo['tonnage'] ?? '-' }}" @endif @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">
                                        <div class="unit-number">MESIN-{{ $i }}</div>
                                        @if($machineInfo)
                                            <div style="font-size: 0.5rem; opacity: 0.7; font-weight: 700; line-height: 1;">
                                                ({{ $machineInfo['tonnage'] }}T)
                                            </div>
                                        @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            <div class="status-badge status-badge-manual"
                                                style="background: rgba(255,255,255,0.3); margin-top: 5px;">
                                                {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : strtoupper($manualStatus->status)) }}
                                            </div>
                                            <small class="text-white small mt-1"
                                                style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
                                        @elseif($isActive)
                                            <div class="part-number">{{ $data->item->part_number ?? 'NO PART' }}</div>
                                            <div class="item-name text-center px-2">{{ $data->item->name ?? '-' }}</div>
                                            <div class="status-badge">{{ $data->judgment }}</div>
                                        @else <div class="item-name mt-2 font-weight-bold">IDLE</div> @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Injection Karawang (Right) --}}
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-warning text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;"><i class="fas fa-cogs"></i></div>
                            <div>
                                <h6 class="modern-card-title">Produksi Injection - Karawang</h6><small
                                    class="text-muted">Monitoring Karawang</small>
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
                                <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                    <div class="status-item {{ $statusClass }}" onclick="showDetailModal(this)"
                                        style="cursor: pointer;"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $data->operator_initials ?? '-' }}" data-date="{{ $data->date ?? '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                            data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"
                                        data-tonnage="{{ $machineInfo['tonnage'] ?? '-' }}" @endif @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">
                                        <div class="unit-number">MESIN-{{ $i }}</div>
                                        @if($machineInfo)
                                            <div style="font-size: 0.5rem; opacity: 0.7; font-weight: 700; line-height: 1;">
                                                ({{ $machineInfo['tonnage'] }}T)
                                            </div>
                                        @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            <div class="status-badge status-badge-manual"
                                                style="background: rgba(255,255,255,0.3); margin-top: 5px;">
                                                {{ $manualStatus->status === 'maintenance' ? 'GANTI MOLD/SETTING' : ($manualStatus->status === 'stopped' ? 'STAND BY' : strtoupper($manualStatus->status)) }}
                                            </div>
                                            <small class="text-white small mt-1"
                                                style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
                                        @elseif($isActive)
                                            <div class="part-number">{{ $data->item->part_number ?? 'NO PART' }}</div>
                                            <div class="item-name text-center px-2">{{ $data->item->name ?? '-' }}</div>
                                            <div class="status-badge">{{ $data->judgment }}</div>
                                        @else <div class="item-name mt-2 font-weight-bold">IDLE</div> @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @else

        <div class="row">
            <!-- Sub Assy Lines -->
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    @php
                        $plant = strtolower(auth()->user()->plant ?? request('plant') ?? '');
                        $tableOptions = range(1, 15);
                        if ($plant === 'jakarta') {
                            $tableOptions = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
                        }
                    @endphp
                    <div class="modern-card-header">
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

                                    // Default State
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';

                                    // Override Logic
                                    if ($manualStatus && $manualStatus->status === 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $isActive = false; // Hide production data
                                    } elseif ($manualStatus && $manualStatus->status === 'stopped') {
                                        $statusClass = 'status-stopped'; // Use stopped style for consistency
                                        $isActive = false;
                                    } elseif ($manualStatus && $manualStatus->status === 'trouble') {
                                        $statusClass = 'status-trouble';
                                        $isActive = false;
                                    } elseif ($isActive) {
                                        $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                    }
                                @endphp
                                <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                    <div class="status-item {{ $statusClass }}" onclick="showDetailModal(this)"
                                        style="cursor: pointer;"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $data->operator_initials ?? '-' }}" data-date="{{ $data->date ?? '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                        data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}" @endif
                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">

                                        <div class="unit-number">MEJA-{{ $i }}</div>

                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            <div class="status-badge status-badge-manual"
                                                style="background: rgba(255,255,255,0.3); margin-top: 5px;">
                                                @if($manualStatus->status === 'maintenance')
                                                    GANTI MOLD/SETTING
                                                @elseif($manualStatus->status === 'stopped')
                                                    STAND BY
                                                @else
                                                    {{ strtoupper($manualStatus->status) }}
                                                @endif
                                            </div>
                                            <small class="text-white small mt-1"
                                                style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
                                        @elseif($isActive)
                                            <div class="part-number">{{ $data->item->part_number ?? 'NO PART' }}</div>
                                            <div class="item-name text-center px-2">
                                                {{ $data->item->name ?? '-' }}
                                            </div>
                                            <div class="status-badge">
                                                {{ $data->judgment }}
                                            </div>
                                        @else
                                            <div class="item-name mt-2 font-weight-bold">IDLE</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Process Machines -->
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
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

                                    // Default State
                                    $isActive = $data ? true : false;
                                    $isNg = $isActive && $data->judgment === 'NG';
                                    $statusClass = 'status-idle';

                                    // Override Logic
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
                                <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                    <div class="status-item {{ $statusClass }}" onclick="showDetailModal(this)"
                                        style="cursor: pointer;"
                                        data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                        @if($isActive) data-part-number="{{ $data->item->part_number ?? '-' }}"
                                            data-item-name="{{ $data->item->name ?? '-' }}" data-judgment="{{ $data->judgment }}"
                                            data-total-qty="{{ $data->total_qty ?? '-' }}"
                                            data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                            data-ok-count="{{ $data->total_ok ?? '-' }}" data-ng-count="{{ $data->total_ng ?? '-' }}"
                                            data-operator="{{ $data->operator_initials ?? '-' }}" data-date="{{ $data->date ?? '-' }}"
                                            data-shift="{{ $data->shift ?? '-' }}"
                                            data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"
                                        data-tonnage="{{ $tonnage }}" @endif @if($manualStatus && $manualStatus->status !== 'normal')
                                            data-manual-description="{{ $manualStatus->description }}"
                                            data-manual-by="{{ $manualStatus->created_by }}"
                                        data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}" @endif
                                        title="Click untuk detail">


                                        <div class="unit-number">MESIN-{{ $i }}</div>
                                        @if($machineInfo)
                                            <div style="font-size: 0.5rem; opacity: 0.7; font-weight: 700; line-height: 1;">
                                                ({{ $machineInfo['tonnage'] }}T)
                                            </div>
                                        @endif

                                        @if($manualStatus && $manualStatus->status !== 'normal')
                                            <div class="status-badge status-badge-manual"
                                                style="background: rgba(255,255,255,0.3); margin-top: 5px;">
                                                @if($manualStatus->status === 'maintenance')
                                                    GANTI MOLD/SETTING
                                                @elseif($manualStatus->status === 'stopped')
                                                    STAND BY
                                                @elseif($manualStatus->status === 'trouble')
                                                    TROUBLE
                                                @else
                                                    {{ strtoupper($manualStatus->status) }}
                                                @endif
                                            </div>
                                            <small class="text-white small mt-1"
                                                style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
                                        @elseif($isActive)
                                            <div class="part-number">{{ $data->item->part_number ?? 'NO PART' }}</div>
                                            <div class="item-name text-center px-2">
                                                {{ $data->item->name ?? '-' }}
                                            </div>
                                            <div class="status-badge">
                                                {{ $data->judgment }}
                                            </div>
                                        @else
                                            <div class="item-name mt-2 font-weight-bold">IDLE</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Detail Modal -->
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
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to show detail modal
        function showDetailModal(element) {
            const card = element.closest('.status-item');
            if (!card) return;

            const unitName = card.querySelector('.unit-number')?.textContent || 'Unknown';
            const partNumber = card.dataset.partNumber || '-';
            const itemName = card.dataset.itemName || '-';
            const judgment = card.dataset.judgment || '-';
            const totalQty = card.dataset.totalQty || '-';
            const samplingQty = card.dataset.samplingQty || '-';
            const okCount = card.dataset.okCount || '-';
            const ngCount = card.dataset.ngCount || '-';
            const operator = card.dataset.operator || '-';
            const date = card.dataset.date || '-';
            const shift = card.dataset.shift || '-';
            const time = card.dataset.time || '-';
            const tonnage = card.dataset.tonnage || '-';
            const status = card.dataset.status || 'idle';
            const manualDescription = card.dataset.manualDescription || '';
            const manualBy = card.dataset.manualBy || '';
            const manualUpdated = card.dataset.manualUpdated || '';

            // Set modal title
            document.getElementById('modalUnitName').textContent = unitName;

            // Build modal content
            let content = '';

            if (status === 'active') {
                content = `
                                                                                                                                                                                                        <div class="mb-3">
                                                                                                                                                                                                            <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-box mr-2"></i>Part Info</h6>
                                                                                                                                                                                                            <div class="pl-4">
                                                                                                                                                                                                                <p class="mb-1"><strong>Part Number:</strong> ${partNumber}</p>
                                                                                                                                                                                                                <p class="mb-1"><strong>Item Name:</strong> ${itemName}</p>
                                                                                                                                                                                                                <p class="mb-0"><strong>Tonnage:</strong> ${tonnage}</p>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                        <div class="mb-3">
                                                                                                                                                                                                            <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-clipboard-check mr-2"></i>QC Check</h6>
                                                                                                                                                                                                            <div class="pl-4">
                                                                                                                                                                                                                <p class="mb-1"><strong>Sampling:</strong> ${samplingQty} / ${totalQty}</p>
                                                                                                                                                                                                                <div class="row mb-2">
                                                                                                                                                                                                                    <div class="col-6"><span class="badge badge-success w-100">OK: ${okCount}</span></div>
                                                                                                                                                                                                                    <div class="col-6"><span class="badge badge-danger w-100">NG: ${ngCount}</span></div>
                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                <p class="mb-1"><strong>Judgment:</strong> <span class="badge badge-${judgment === 'OK' ? 'success' : 'danger'}">${judgment}</span></p>
                                                                                                                                                                                                                <p class="mb-1"><strong>QC:</strong> ${operator}</p>
                                                                                                                                                                                                                <p class="mb-0"><strong>Time:</strong> ${date} | ${time} | Shift ${shift}</p>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                        </div>`;
            } else if (['maintenance', 'stopped', 'trouble'].includes(status)) {
                let badge = status === 'maintenance' ? 'GANTI MOLD/SETTING' : (status === 'stopped' ? 'STAND BY' : 'TROUBLE');
                let color = status === 'maintenance' ? 'warning' : (status === 'stopped' ? 'dark' : 'danger');
                content = `
                                                                                                                                                                                                        <div class="mb-3">
                                                                                                                                                                                                            <h6 class="font-weight-bold text-${color} mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Manual Status</h6>
                                                                                                                                                                                                            <div class="pl-4">
                                                                                                                                                                                                                <p class="mb-2"><strong>Status:</strong> <span class="badge badge-${color}">${badge}</span></p>
                                                                                                                                                                                                                <p class="mb-1"><strong>Desc:</strong> ${manualDescription || '-'}</p>
                                                                                                                                                                                                                <p class="mb-1"><strong>By:</strong> ${manualBy}</p>
                                                                                                                                                                                                                <p class="mb-0"><strong>Updated:</strong> ${manualUpdated}</p>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                        </div>`;
            } else {
                content = `
                                                                                                                                                                                                        <div class="text-center py-4">
                                                                                                                                                                                                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                                                                                                                                                                                            <h6 class="text-muted">Status: IDLE</h6>
                                                                                                                                                                                                            <p class="text-muted mb-0">Menunggu Pengecekan Quality</p>
                                                                                                                                                                                                        </div>`;
            }

            // Set modal content
            document.getElementById('modalBody').innerHTML = content;

            // Show modal
            $('#detailModal').modal('show');
        }

        function updateDateTime() {
            const now = new Date();

            // Format date
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const date = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();

            const dateString = `${dayName}, ${date} ${monthName} ${year}`;

            // Format time
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;

            // Update DOM
            const dateElement = document.getElementById('current-date');
            if (dateElement) {
                dateElement.textContent = dateString;
            }

            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Update immediately
        updateDateTime();

        // Update every second
        setInterval(updateDateTime, 1000);

    </script>
@endsection