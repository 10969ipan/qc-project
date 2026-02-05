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
        /* Modern Dashboard CSS remains for other elements */
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
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modern-card-title {
            font-weight: 800;
            font-size: 0.9rem;
            color: #2d3748;
            margin: 0;
            letter-spacing: -0.025em;
        }

        /* Status Animations from User Template */
        @keyframes pulse-red-border {
            0% {
                border-color: rgba(239, 68, 68, 0.4);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }

            50% {
                border-color: rgba(239, 68, 68, 1);
                box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
            }

            100% {
                border-color: rgba(239, 68, 68, 0.4);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .border-pulse-red {
            animation: pulse-red-border 2s infinite;
        }

        /* Welcome Section Modern */
        .welcome-modern {
            background: var(--gradient-primary);
            border-radius: 12px;
            padding: 1rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.25);
            margin-bottom: 1rem;
        }

        /* Stats Cards Modern */
        .stat-card-modern {
            background: white;
            border-radius: 12px;
            padding: 0.5rem;
            height: 100%;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .stat-card-modern:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
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

    <script src="{{ asset('js/vendor/tailwind.min.js') }}"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#607AFB",
                        "background-light": "#f5f6f8",
                        "background-dark": "#0f1323",
                        "card-light": "#ffffff",
                        "card-dark": "#1e293b",
                        "accent-green": "#10b981",
                        "accent-blue": "#3b82f6",
                        "accent-red": "#ef4444"
                    }
                }
            }
        };
    </script>
    <link href="{{ asset('fonts/material-icons.css') }}" rel="stylesheet">


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


    @php
        $role = auth()->user()->role;
        $isDualView = in_array($role, ['admin', 'manager', 'asst_manager', 'manager_qc', 'asst_manager_qc']);
    @endphp

    {{-- Customer Claim Achievement Charts --}}
    <div class="row mb-5">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div></div>
                <form action="{{ route('dashboard') }}" method="GET" class="form-inline">
                    <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="combined" {{ ($claimData['year'] ?? '') == 'combined' ? 'selected' : '' }}>All</option>
                        <option value="all" {{ ($claimData['year'] ?? '') == 'all' ? 'selected' : '' }}>Tren Tahunan (Summary)
                        </option>
                        @php $currentY = date('Y'); @endphp
                        @for($y = $currentY; $y >= 2022; $y--)
                            <option value="{{ $y }}" {{ ($claimData['year'] ?? $currentY) == $y && !in_array($claimData['year'], ['all', 'combined']) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        {{-- Jakarta Card --}}
        @if($isDualView || (Auth::user()->plant->code ?? '') !== 'karawang')
            <div class="{{ $isDualView ? 'col-lg-4' : 'col-lg-6' }} mb-4">
                <div class="modern-card">
                    <div class="modern-card-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-danger text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h6 class="modern-card-title">CLAIM CUSTOMER JAKARTA</h6>
                                <div class="small text-muted">Statistik Bulanan PPM | Total Claim Jakarta-Karawang:
                                    {{ array_sum($claimData['combined_total'] ?? []) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chartClaimJakarta" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Karawang Card --}}
        @if($isDualView || (Auth::user()->plant->code ?? '') === 'karawang')
            <div class="{{ $isDualView ? 'col-lg-4' : 'col-lg-6' }} mb-4">
                <div class="modern-card">
                    <div class="modern-card-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-danger text-white mr-3"
                                style="width: 32px; height: 32px; font-size: 0.85rem;">
                                <i class="fas fa-industry"></i>
                            </div>
                            <div>
                                <h6 class="modern-card-title">CLAIM CUSTOMER KARAWANG</h6>
                                <div class="small text-muted">Statistik Bulanan PPM | Total Claim Jakarta-Karawang:
                                    {{ array_sum($claimData['combined_total'] ?? []) }}

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chartClaimKarawang" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Frequency Card --}}
        {{-- Frequency Card --}}
        <div class="{{ $isDualView ? 'col-lg-4' : 'col-lg-6' }} mb-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-danger text-white mr-3"
                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h6 class="modern-card-title">FREKUENSI (JAKARTA & KARAWANG)</h6>
                            <div class="small text-muted">Statistik Frekuensi Per Bulan</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chartClaimFrequency" style="height: 320px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($combinedStats))
        <div class="row">
            @php
                /* isDualView already defined above */
            @endphp
            @if($isDualView && isset($statsJakarta) && isset($statsKarawang))
                {{-- Jakarta Stats Row --}}
                <div class="col-12 mb-5">
                    <div class="row">
                        {{-- Jakarta Pie Chart --}}
                        <div class="col-xl-8 col-lg-7 mb-4 mb-xl-0">
                            <div class="modern-card h-100">
                                <div class="modern-card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary text-white mr-3"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div>
                                            <h6 class="modern-card-title">STATUS APPROVAL - JAKARTA</h6>
                                            <div class="small text-muted">Statistik Jakarta</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-light" style="background: #fdfdfe;">
                                    <div id="chartJakarta" style="height: 300px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Jakarta Daily Gauge --}}
                        <div class="col-xl-4 col-lg-5">
                            <div class="modern-card h-100">
                                <div class="modern-card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary text-white mr-3"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <h6 class="modern-card-title">JAKARTA - DAILY APPROVAL</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gauge-jakarta" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Karawang Stats Row --}}
                <div class="col-12 mb-5">
                    <div class="row">
                        {{-- Karawang Pie Chart --}}
                        <div class="col-xl-8 col-lg-7 mb-4 mb-xl-0">
                            <div class="modern-card h-100">
                                <div class="modern-card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-success text-white mr-3"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <i class="fas fa-industry"></i>
                                        </div>
                                        <div>
                                            <h6 class="modern-card-title">STATUS APPROVAL - KARAWANG</h6>
                                            <div class="small text-muted">Statistik Karawang</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-light" style="background: #fdfdfe;">
                                    <div id="chartKarawang" style="height: 300px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Karawang Daily Gauge --}}
                        <div class="col-xl-4 col-lg-5">
                            <div class="modern-card h-100">
                                <div class="modern-card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-success text-white mr-3"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <h6 class="modern-card-title">KARAWANG - DAILY APPROVAL</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gauge-karawang" style="height: 300px;"></div>
                                </div>
                            </div>
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
                {{-- Combined Row --}}
                <div class="col-12 mb-5">
                    <div class="row">
                        {{-- Combined Pie Chart --}}
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
                                            <h6 class="modern-card-title">{{ strtoupper($currentPlantName) }} APPROVAL</h6>
                                            <div class="small text-muted">Statistik {{ $currentPlantName }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-light" style="background: #fdfdfe;">
                                    <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Combined Daily Gauge --}}
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
                    </div>
                </div>

                {{-- NG Rate Single --}}
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
            <script src="{{ asset('js/vendor/canvasjs.min.js') }}"></script>
            <script>

                function explodePie(e) {
                    if (typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
                        e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
                    } else {
                        e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
                    }
                    e.chart.render();
                }

                // Initialize FusionCharts immediately when ready
                if (window.FusionCharts) {
                    FusionCharts.ready(function () {
                        renderGauges();
                    });
                }

                window.addEventListener('load', function () {
                    // Status Approval Charts
                    @if($isDualView && isset($statsJakarta) && isset($statsKarawang))
                        var statsJakarta = @json($statsJakarta);
                        var statsKarawang = @json($statsKarawang);
                        if (statsJakarta && document.getElementById("chartJakarta")) {
                            renderChart("chartJakarta", "STATUS APPROVAL - JAKARTA", statsJakarta);
                        }
                        if (statsKarawang && document.getElementById("chartKarawang")) {
                            renderChart("chartKarawang", "STATUS APPROVAL - KARAWANG", statsKarawang);
                        }
                    @else
                                                                                                                                                                                                                                                        var combinedStats = @json($combinedStats ?? null);
                        if (combinedStats && document.getElementById("chartContainer")) {
                            renderChart("chartContainer", "Status Approval", combinedStats);
                        }
                    @endif

                    // Customer Claim Chart
                    renderClaimChart();

                    // NG Rate Charts
                    renderNgRateCharts();
                });

                function renderGauges() {
                    const dailyJkt = @json($dailyStatsJakarta ?? null);
                    const dailyKrw = @json($dailyStatsKarawang ?? null);
                    const dailyTotal = @json($dailyCombinedStats ?? null);

                    if (dailyJkt && document.getElementById("gauge-jakarta")) {
                        renderGauge("gauge-jakarta", "Jakarta", calculateRate(dailyJkt));
                    }
                    if (dailyKrw && document.getElementById("gauge-karawang")) {
                        renderGauge("gauge-karawang", "Karawang", calculateRate(dailyKrw));
                    }
                    if (dailyTotal && document.getElementById("gauge-total")) {
                        const plantLabel = "{{ Auth::user()->plant->name ?? 'Combined' }}";
                        renderGauge("gauge-total", plantLabel, calculateRate(dailyTotal));
                    }
                }

                function calculateRate(stats) {
                    const total = (stats.approved || 0) + (stats.pending || 0) + (stats.rejected || 0);
                    if (total === 0) return 0;
                    return Math.round((stats.approved / total) * 100);
                }

                function renderGauge(container, label, value) {
                    if (!window.FusionCharts) return;

                    // Dispose existing chart to prevent conflict
                    if (FusionCharts.items && FusionCharts.items[container + "-gauge"]) {
                        FusionCharts.items[container + "-gauge"].dispose();
                    }

                    const dataSource = {
                        chart: {
                            caption: label + " Approval Rate",
                            lowerLimit: "0",
                            upperLimit: "100",
                            showValue: "1",
                            numberSuffix: "%",
                            theme: "gammel",
                            baseFontSize: "11",
                            captionFontSize: "14",
                            subcaptionFontSize: "10",
                            gaugeFillMix: "{light-10},{light-20},{light-30}",
                            gaugeFillRatio: "40,20,40"
                        },
                        colorRange: {
                            color: [
                                { minValue: "0", maxValue: "50", code: "#ef4444" },
                                { minValue: "50", maxValue: "75", code: "#f59e0b" },
                                { minValue: "75", maxValue: "100", code: "#10b981" }
                            ]
                        },
                        dials: {
                            dial: [{
                                value: value.toString(),
                                tooltext: "<b>" + value + "%</b> approved today"
                            }]
                        },
                        trendpoints: {
                            point: [{
                                startvalue: "100",
                                displayvalue: " ",
                                thickness: "2",
                                color: "#E15A26",
                                hideValue: "1",
                                usemarker: "1",
                                markerbordercolor: "#E15A26",
                                markertooltext: "Target Approval: 100%"
                            }]
                        }
                    };

                    new FusionCharts({
                        id: container + "-gauge",
                        type: "angulargauge",
                        renderAt: container,
                        width: "100%",
                        height: "100%",
                        dataFormat: "json",
                        dataSource
                    }).render();
                }

                function renderClaimChart() {
                    var claimData = @json($claimData ?? null);
                    if (!claimData) return;

                    // Common configuration for PPM charts
                    const commonOptions = {
                        animationEnabled: true,
                        theme: "light2",
                        axisX: {
                            interval: 1,
                            labelFontFamily: "Nunito",
                            labelFontSize: 10
                        },
                        axisY: {
                            title: "PPM",
                            titleFontFamily: "Nunito",
                            labelFontFamily: "Nunito",
                            includeZero: true,
                            minimum: 0
                        },
                        axisY2: {
                            title: "Total Claim Jakarta-Karawang",
                            titleFontFamily: "Nunito",
                            labelFontFamily: "Nunito",
                            includeZero: true,
                            minimum: 0
                        },
                        toolTip: {
                            shared: true,
                            fontFamily: "Nunito"
                        },
                        legend: {
                            cursor: "pointer",
                            itemclick: toggleDataSeries,
                            fontFamily: "Nunito",
                            verticalAlign: "bottom",
                            horizontalAlign: "center",
                            fontSize: 10
                        }
                    };

                    // 1. Jakarta Chart
                    if (document.getElementById("chartClaimJakarta") && claimData.jakarta) {
                        var dataJkt = claimData.jakarta.map(function (val, index) {
                            var count = claimData.combined_total[index];
                            var dp = { label: claimData.labels[index], y: val, claim_count: count };
                            if (val > 0) {
                                dp.indexLabel = val.toString();
                                dp.indexLabelFontColor = "#2e59d9";
                                dp.indexLabelFontWeight = "bold";
                                dp.indexLabelFontSize = 10;
                            }
                            return dp;
                        });

                        var jktTarget = claimData.target.map((v, i) => {
                            let dp = { label: claimData.labels[i], y: v };
                            if (i === 0 || i === claimData.target.length - 1) {
                                dp.indexLabel = v.toString();
                                dp.indexLabelFontColor = "#c0392b";
                                dp.indexLabelFontSize = 9;
                                dp.indexLabelFontWeight = "bold";
                            }
                            return dp;
                        });

                        var jktTotalClaims = claimData.combined_total.map((v, i) => ({ label: claimData.labels[i], y: v }));

                        var chartJkt = new CanvasJS.Chart("chartClaimJakarta", {
                            ...commonOptions,
                            toolTip: {
                                ...commonOptions.toolTip,
                                content: "{label}<br/><span style='color:{color}'>{name}</span>: {y}"
                            },
                            data: [
                                {
                                    type: "splineArea",
                                    name: "Jakarta PPM",
                                    showInLegend: true,
                                    color: "#4e73df", // Solid blue for better contrast
                                    markerSize: 5,
                                    dataPoints: dataJkt
                                },
                                {
                                    type: "line",
                                    name: "Total Claim Jakarta-Karawang",
                                    axisYType: "secondary",
                                    showInLegend: true,
                                    color: "#f6c23e", // Brighter yellow
                                    markerSize: 5,
                                    dataPoints: jktTotalClaims
                                },
                                {
                                    type: "line",
                                    name: "Target",
                                    showInLegend: true,
                                    color: "#e74a3b", // Bolder red
                                    lineDashType: "dash",
                                    markerSize: 0,
                                    dataPoints: jktTarget
                                }
                            ]
                        });
                        chartJkt.render();
                    }

                    // 2. Karawang Chart
                    if (document.getElementById("chartClaimKarawang") && claimData.karawang) {
                        var dataKrw = claimData.karawang.map(function (val, index) {
                            var count = claimData.combined_total[index];
                            var dp = { label: claimData.labels[index], y: val, claim_count: count };
                            if (val > 0) {
                                dp.indexLabel = val.toString();
                                dp.indexLabelFontColor = "#17a673";
                                dp.indexLabelFontWeight = "bold";
                                dp.indexLabelFontSize = 10;
                            }
                            return dp;
                        });

                        var targetData = claimData.target.map((v, i) => {
                            let dp = { label: claimData.labels[i], y: v };
                            if (i === 0 || i === claimData.target.length - 1) {
                                dp.indexLabel = v.toString();
                                dp.indexLabelFontColor = "#c0392b";
                                dp.indexLabelFontSize = 9;
                                dp.indexLabelFontWeight = "bold";
                            }
                            return dp;
                        });

                        var krwTotalClaims = claimData.combined_total.map((v, i) => ({ label: claimData.labels[i], y: v }));

                        var chartKrw = new CanvasJS.Chart("chartClaimKarawang", {
                            ...commonOptions,
                            toolTip: {
                                ...commonOptions.toolTip,
                                content: "{label}<br/><span style='color:{color}'>{name}</span>: {y}"
                            },
                            data: [
                                {
                                    type: "splineArea",
                                    name: "Karawang PPM",
                                    showInLegend: true,
                                    color: "#1cc88a", // Vivid green
                                    markerSize: 5,
                                    dataPoints: dataKrw
                                },
                                {
                                    type: "line",
                                    name: "Total Claim Jakarta-Karawang",
                                    axisYType: "secondary",
                                    showInLegend: true,
                                    color: "#f6c23e", // Brighter yellow
                                    markerSize: 5,
                                    dataPoints: krwTotalClaims
                                },
                                {
                                    type: "line",
                                    name: "Target",
                                    showInLegend: true,
                                    color: "#e74a3b", // Bolder red
                                    lineDashType: "dash",
                                    markerSize: 0,
                                    dataPoints: targetData
                                }
                            ]
                        });
                        chartKrw.render();
                    }

                    // 3. Frequency Chart (Horizontal Bar)
                    if (document.getElementById("chartClaimFrequency") && claimData.jakarta && claimData.karawang) {
                        var jktFreqPpm = claimData.jakarta.map((v, i) => ({ label: claimData.labels[i], y: v }));
                        var krwFreqPpm = claimData.karawang.map((v, i) => ({ label: claimData.labels[i], y: v }));

                        var chartFreq = new CanvasJS.Chart("chartClaimFrequency", {
                            animationEnabled: true,
                            theme: "light2",
                            axisX: {
                                interval: 1,
                                labelFontFamily: "Nunito",
                                labelFontSize: 10,
                                reversed: true
                            },
                            axisY: {
                                title: "PPM Value",
                                titleFontFamily: "Nunito",
                                labelFontFamily: "Nunito",
                                includeZero: true
                            },
                            toolTip: {
                                shared: true,
                                fontFamily: "Nunito"
                            },
                            legend: {
                                cursor: "pointer",
                                fontFamily: "Nunito",
                                verticalAlign: "bottom",
                                horizontalAlign: "center",
                                fontSize: 10
                            },
                            data: [
                                {
                                    type: "bar",
                                    name: "Jakarta",
                                    showInLegend: true,
                                    color: "#2e59d9",
                                    dataPoints: jktFreqPpm
                                },
                                {
                                    type: "bar",
                                    name: "Karawang",
                                    showInLegend: true,
                                    color: "#17a673",
                                    dataPoints: krwFreqPpm
                                }
                            ]
                        });
                        chartFreq.render();
                    }
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
                    if (!document.getElementById(containerId)) return;

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
                            itemclick: explodePie,
                            verticalAlign: "bottom",
                            horizontalAlign: "center"
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
                        if (document.getElementById("chartNgJakarta")) {
                            renderSingleNgChart("chartNgJakarta", "Jakarta", ngData.jakarta, ngData.labels);
                        }
                        if (document.getElementById("chartNgKarawang")) {
                            renderSingleNgChart("chartNgKarawang", "Karawang", ngData.karawang, ngData.labels);
                        }
                    } else {
                        if (document.getElementById("chartNgSingle")) {
                            const plantData = currentPlant === 'jakarta' ? ngData.jakarta : ngData.karawang;
                            const plantTitle = currentPlant === 'jakarta' ? 'JAKARTA' : 'KARAWANG';
                            renderSingleNgChart("chartNgSingle", plantTitle, plantData, ngData.labels);
                        }
                    }
                }

                function renderSingleNgChart(containerId, plantName, plantData, labels) {
                    if (!document.getElementById(containerId) || !plantData) return;

                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    const series = [];

                    const formatLabel = (l) => {
                        const parts = l.split('-');
                        if (parts.length < 3) return l;
                        const day = parts[2];
                        const month = monthNames[parseInt(parts[1]) - 1];
                        return day + ' ' + month;
                    };

                    if (plantData.sub_assy) {
                        series.push({
                            type: "spline",
                            name: "Sub Assy",
                            color: "#0d6efd",
                            showInLegend: true,
                            yValueFormatString: "##0.00'%'",
                            dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.sub_assy[i] }))
                        });
                    }

                    if (plantData.in_process) {
                        series.push({
                            type: "spline",
                            name: "In Process",
                            color: "#198754",
                            showInLegend: true,
                            yValueFormatString: "##0.00'%'",
                            dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.in_process[i] }))
                        });
                    }

                    if (plantData.cross_cut) {
                        series.push({
                            type: "spline",
                            name: "Cross Cut",
                            color: "#6f42c1",
                            showInLegend: true,
                            yValueFormatString: "##0.00'%'",
                            dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.cross_cut[i] }))
                        });
                    }

                    if (plantData.sortir) {
                        series.push({
                            type: "spline",
                            name: "Sortir",
                            color: "#d63384",
                            showInLegend: true,
                            yValueFormatString: "##0.00'%'",
                            dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.sortir[i] }))
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
                                <h6 class="modern-card-title">PRODUKSI SUB ASSY - JAKARTA</h6><small
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
                                                            class="text-slate-500 dark:text-slate-500 uppercase tracking-tighter">Status</span>
                                                        <span
                                                            class="{{ $data->judgment === 'OK' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-bold text-[0.6rem]">{{ $data->judgment }}</span>
                                                    </div>
                                                    <div
                                                        class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $data->judgment === 'OK' ? 'from-green-500 to-green-500' : 'from-red-500 to-red-500' }} h-full rounded-full"
                                                            style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center flex flex-col items-center justify-center h-full">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500 mb-0">Meja Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-1 mb-0">Wait
                                                        Setup</p>
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

            {{-- Sub Assy Karawang (Right) --}}
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
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
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter text-sm">Status</span>
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
                                                <div class="text-center flex flex-col items-center justify-center h-full">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Meja Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Setup</p>
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

            {{-- Injection Jakarta (Left) --}}
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
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
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter text-sm">Status</span>
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
                                                <div class="text-center">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Machine Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Setup</p>
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

            {{-- Injection Karawang (Right) --}}
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    <div class="modern-card-header">
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
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter text-sm">Status</span>
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
                                                <div class="text-center">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Machine Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Setup</p>
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

        </div>
    @else

        <div class="row">
            <!-- Sub Assy Lines -->
            <div class="col-xl-6 col-lg-12 mb-5">
                <div class="modern-card h-100">
                    @php
                        $plant = strtolower(optional(auth()->user()->plant)->code ?? request('plant') ?? '');
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
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter text-sm">Status</span>
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
                                                <div class="text-center flex flex-col items-center justify-center h-full">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Meja Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Setup</p>
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
                                                            class="text-slate-500 dark:text-slate-400 uppercase tracking-tighter text-sm">Status</span>
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
                                                <div class="text-center">
                                                    <p class="text-[0.65rem] text-slate-400 dark:text-slate-500">Machine Idle</p>
                                                    <p class="text-[0.6rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5">Wait
                                                        Setup</p>
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

            const unitName = card.querySelector('h4')?.textContent || 'Unknown';
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

            // Build modal content with premium design
            let content = '';

            const unitLabel = unitName.includes('MEJA') ? 'MEJA' : 'MACHINE';

            if (status === 'active') {
                content = `
                                                                                                                                                                                                                                                    <div class="space-y-6 text-slate-800 dark:text-slate-200">
                                                                                                                                                                                                                                                        <!-- Part Info Section -->
                                                                                                                                                                                                                                                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                                                                                                                                                                                                                                                            <div class="flex items-center gap-2 mb-4">
                                                                                                                                                                                                                                                                <span class="material-icons-round text-primary">inventory_2</span>
                                                                                                                                                                                                                                                                <h6 class="font-bold m-0 uppercase tracking-wider text-xs">Informasi Produk</h6>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                                                                                                                                                                                                                <div class="space-y-1">
                                                                                                                                                                                                                                                                    <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Part Number</p>
                                                                                                                                                                                                                                                                    <p class="text-sm font-mono font-bold">${partNumber}</p>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="space-y-1">
                                                                                                                                                                                                                                                                    <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Item Name</p>
                                                                                                                                                                                                                                                                    <p class="text-sm font-bold">${itemName}</p>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="space-y-1">
                                                                                                                                                                                                                                                                    <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Kapasitas (Tonnage)</p>
                                                                                                                                                                                                                                                                    <p class="text-sm font-bold">${tonnage}T</p>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="space-y-1">
                                                                                                                                                                                                                                                                    <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Waktu Update</p>
                                                                                                                                                                                                                                                                    <p class="text-sm font-bold">${time} WIB</p>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>

                                                                                                                                                                                                                                                        <!-- Quality Control Section -->
                                                                                                                                                                                                                                                        <div class="bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-900/30">
                                                                                                                                                                                                                                                            <div class="flex items-center gap-2 mb-4">
                                                                                                                                                                                                                                                                <span class="material-icons-round text-indigo-600">verified_user</span>
                                                                                                                                                                                                                                                                <h6 class="font-bold m-0 uppercase tracking-wider text-xs">Quality Control (QC)</h6>
                                                                                                                                                                                                                                                            </div>

                                                                                                                                                                                                                                                            <div class="grid grid-cols-2 gap-4 mb-4">
                                                                                                                                                                                                                                                                <div class="bg-white dark:bg-slate-800 p-3 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                                                                                                                                                                                                                                                                    <p class="text-[0.6rem] text-slate-500 uppercase font-bold mb-1">Sampling Rate</p>
                                                                                                                                                                                                                                                                    <div class="flex items-end gap-1">
                                                                                                                                                                                                                                                                        <span class="text-xl font-bold">${samplingQty}</span>
                                                                                                                                                                                                                                                                        <span class="text-[0.65rem] text-slate-400 mb-1">/ ${totalQty} pcs</span>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="bg-white dark:bg-slate-800 p-3 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                                                                                                                                                                                                                                                                    <p class="text-[0.6rem] text-slate-500 uppercase font-bold mb-1">Status Judgment</p>
                                                                                                                                                                                                                                                                    <div class="flex items-center gap-1.5">
                                                                                                                                                                                                                                                                        <span class="w-2 h-2 rounded-full ${judgment === 'OK' ? 'bg-green-500' : 'bg-red-500'}"></span>
                                                                                                                                                                                                                                                                        <span class="text-sm font-extrabold ${judgment === 'OK' ? 'text-green-600' : 'text-red-600'}">${judgment}</span>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>

                                                                                                                                                                                                                                                            <div class="grid grid-cols-2 gap-4">
                                                                                                                                                                                                                                                                <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 p-2.5 rounded-xl border border-green-100 dark:border-green-900/30">
                                                                                                                                                                                                                                                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">OK</div>
                                                                                                                                                                                                                                                                    <div>
                                                                                                                                                                                                                                                                        <p class="text-[0.6rem] text-green-700 dark:text-green-400 font-bold uppercase">Total OK</p>
                                                                                                                                                                                                                                                                        <p class="text-sm font-bold">${okCount}</p>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 p-2.5 rounded-xl border border-red-100 dark:border-red-900/30">
                                                                                                                                                                                                                                                                    <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">NG</div>
                                                                                                                                                                                                                                                                    <div>
                                                                                                                                                                                                                                                                        <p class="text-[0.6rem] text-red-700 dark:text-red-400 font-bold uppercase">Total NG</p>
                                                                                                                                                                                                                                                                        <p class="text-sm font-bold">${ngCount}</p>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>

                                                                                                                                                                                                                                                        <!-- Operator Info -->
                                                                                                                                                                                                                                                        <div class="flex items-center justify-between px-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                                                                                                                                                                                                                                            <div class="flex items-center gap-2">
                                                                                                                                                                                                                                                                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400">
                                                                                                                                                                                                                                                                    <span class="material-icons-round text-lg">person</span>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div>
                                                                                                                                                                                                                                                                    <p class="text-[0.6rem] text-slate-500 uppercase font-bold leading-none mb-1">Operator QC</p>
                                                                                                                                                                                                                                                                    <p class="text-xs font-bold leading-none">${operator}</p>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                            <div class="text-right">
                                                                                                                                                                                                                                                                <p class="text-[0.6rem] text-slate-500 uppercase font-bold leading-none mb-1">Shift / Tanggal</p>
                                                                                                                                                                                                                                                                <p class="text-xs font-medium leading-none">Shift ${shift} | ${date}</p>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                    </div>`;
            } else if (['maintenance', 'stopped', 'trouble'].includes(status)) {
                let badge = status === 'maintenance' ? 'GANTI MOLD/SETTING' : (status === 'stopped' ? 'STAND BY' : 'TROUBLE');
                let color = status === 'maintenance' ? 'yellow' : (status === 'stopped' ? 'gray' : 'red');
                let icon = status === 'maintenance' ? 'engineering' : (status === 'stopped' ? 'pause_circle_outline' : 'warning');

                content = `
                                                                                                                                                                                                                                                    <div class="text-center py-6 text-slate-800 dark:text-slate-200">
                                                                                                                                                                                                                                                        <div class="w-20 h-20 bg-${color}-50 dark:bg-${color}-900/20 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-${color}-100 dark:border-${color}-900/30">
                                                                                                                                                                                                                                                            <span class="material-icons-round text-4xl text-${color}-600 dark:text-${color}-400">${icon}</span>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                        <h4 class="text-xl font-black mb-2 uppercase italic">${unitLabel} IN ${badge}</h4>
                                                                                                                                                                                                                                                        <div class="max-w-xs mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 mt-6">
                                                                                                                                                                                                                                                            <p class="text-[0.65rem] text-slate-500 uppercase font-bold mb-2">Keterangan / Masalah</p>
                                                                                                                                                                                                                                                            <p class="text-sm font-medium italic">"${manualDescription || 'Tidak ada keterangan tambahan'}"</p>
                                                                                                                                                                                                                                                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-[0.6rem]">
                                                                                                                                                                                                                                                                <span class="text-slate-400 uppercase font-bold">Dibuat Oleh: ${manualBy}</span>
                                                                                                                                                                                                                                                                <span class="text-slate-400 font-medium">${manualUpdated}</span>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                    </div>`;
            } else {
                content = `
                                                                                                                                                                                                                                                    <div class="text-center py-12 text-slate-800 dark:text-slate-200">
                                                                                                                                                                                                                                                        <div class="relative w-24 h-24 mx-auto mb-6">
                                                                                                                                                                                                                                                            <div class="absolute inset-0 bg-slate-100 dark:bg-slate-800 rounded-full animate-ping opacity-25"></div>
                                                                                                                                                                                                                                                            <div class="relative w-full h-full bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center border-4 border-white dark:border-slate-900 shadow-inner">
                                                                                                                                                                                                                                                                <span class="material-icons-round text-4xl text-slate-400">hourglass_empty</span>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                        <h4 class="text-lg font-bold mb-2 tracking-tight">Status: ${unitLabel} IDLE</h4>
                                                                                                                                                                                                                                                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-[240px] mx-auto">Menunggu pengecekan dari tim Quality Control.</p>
                                                                                                                                                                                                                                                        <button class="mt-8 px-6 py-2 bg-slate-800 dark:bg-white text-white dark:text-slate-900 rounded-full text-xs font-bold uppercase tracking-widest shadow-lg shadow-slate-200 dark:shadow-none hover:scale-105 transition-transform" data-dismiss="modal">Tutup Detail</button>
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