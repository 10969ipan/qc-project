<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QC TV </title>
    
    <!-- Premium Fonts & Icons -->
    <link href="{{ asset('fonts/ibm-plex-sans.css') }}" rel="stylesheet">
    <link href="{{ asset('fonts/material-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    
    <!-- Scripts (Local Assets) -->
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
    <script src="{{ asset('js/vendor/canvasjs.min.js') }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.js')) }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.widgets.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.widgets.js')) }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.theme.fusion.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.theme.fusion.js')) }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.theme.gammel.js') }}?v={{ filemtime(public_path('js/vendor/fusioncharts.theme.gammel.js')) }}"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        indigo: { 50: '#f5f7ff', 100: '#ebf0ff', 200: '#d6e0ff', 300: '#adc2ff', 400: '#85a3ff', 500: '#5c85ff', 600: '#3366ff', 700: '#2952cc', 800: '#1f3d99', 900: '#142966' },
                        slate: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a' }
                    }
                }
            }
        }
    </script>
    
    <style>
        :root {
            --shadow-premium: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -5px rgba(0, 0, 0, 0.1);
            --gradient-blue: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: 'IBM Plex Sans', sans-serif;
            margin: 0;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        .slide-container {
            height: calc(100vh - 80px);
            overflow: hidden;
            position: relative;
            background: #f8fafc;
        }

        .slides-wrapper {
            display: flex;
            height: 100%;
            width: 100%;
            transition: transform 1.2s cubic-bezier(0.645, 0.045, 0.355, 1);
            will-change: transform;
        }

        .slide {
            width: 100%;
            height: 100%;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
        }

        .modern-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: var(--shadow-premium);
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .status-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.6rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .status-active-danger {
            border: 2px solid #ef4444;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0%, 100% { border-color: #ef4444; box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
            50% { border-color: #fca5a5; box-shadow: 0 0 10px 4px rgba(239, 68, 68, 0.1); }
        }

        .tv-header {
            background: var(--gradient-blue);
            height: 80px;
            padding: 0 2.5rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .dots-container {
            display: flex;
            align-items: center;
        }

        .progress-timer {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: white;
            z-index: 100;
            opacity: 0.6;
        }

        .indicator-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .indicator-dot.active {
            background: white;
            transform: scale(1.3);
        }

        /* Fullscreen Toggle Button Style */
        .fullscreen-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(8px);
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.5;
        }

        .fullscreen-btn:hover {
            opacity: 1;
            background: rgba(30, 41, 59, 0.8);
            transform: scale(1.1);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        .fullscreen-btn i {
            font-size: 24px;
        }

        /* Suppress FusionCharts Trial Branding */
        div[id^="gauge-"] svg > g[class$="-caption-group"] + g > g[style*="cursor:pointer"],
        div[id^="gauge-"] svg > g[class$="-caption-group"] + g > g[style*="cursor: pointer"],
        div[id^="gauge-"] svg > g rect[fill="#ffffff"][fill-opacity="0"],
        div[id^="gauge-"] svg > g text[style*="10px"] {
            display: none !important;
        }
    </style>
</head>
<body>
    @php
        $plantCode = 'karawang';
    @endphp

    <header class="tv-header">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tight">Quality Monitoring</h1>
                <div class="flex items-center gap-2">
                    <span class="bg-white text-blue-700 text-[8px] font-black px-1.5 py-0.5 rounded-full uppercase">KARAWANG PLANT</span>
                    <span class="text-white/70 text-[8px] font-bold tracking-widest uppercase" id="current-slide-label">Outgoing Sub-Assy</span>
                </div>
            </div>
        </div>  

        <!-- Floating Fullscreen Button -->
        <div id="fullscreen-toggler" class="fullscreen-btn" title="Toggle Fullscreen">
            <i class="material-icons-round">fullscreen</i>
        </div>

        <div class="tv-container" id="dashboard-main">
            <div class="dots-container absolute left-1/2 -translate-x-1/2 flex gap-3">
                <div class="indicator-dot active"></div>
                <div class="indicator-dot"></div>
                <div class="indicator-dot"></div>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="text-right">
                <div id="digital-clock" class="text-3xl font-black tracking-tighter leading-none">09:00:00</div>
                <div id="date-display" class="text-[10px] font-bold text-blue-200 uppercase tracking-widest mt-1">SABTU, 1 JANUARI 2026</div>
            </div>
        </div>
    </header>

    <div class="progress-timer" id="timer-bar"></div>

    <main class="slide-container">
        <div class="slides-wrapper" id="slides-wrapper">
        @php
            // AKTUAL KARAWANG CONFIGURATION
            $karawangMeja = range(1, 14);
            $karawangMachines = [
                1 => '850T', 2 => '650T', 3 => '650T', 4 => '650T',
                5 => '550T', 6 => '450T', 7 => '360T', 8 => '210T',
                9 => '210T', 11 => '160T', 12 => '80T',  14 => '120T',
                15 => '160T', 16 => '180T', 17 => '180T', 18 => '120T',
                19 => '160T'
            ];
        @endphp
        <!-- SLIDE 1: SUB-ASSY MONITORING (FULL SCREEN) -->
        <section class="slide active" id="slide-subassy" data-label="Outgoing Sub-Assy">
            <div class="modern-card">
                <div class="p-3.5 bg-slate-50/50 border-b flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Outgoing Sub-Assy</h2>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-emerald-200 uppercase flex items-center gap-1.5 shadow-sm">
                           <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span> LIVE
                        </span>
                    </div>
                    <span class="bg-green-100 text-green-700 text-xs font-black px-3 py-1 rounded-full border border-green-200 uppercase header-status-badge">
                       RUNNING <span class="active-count">{{ $activeLines->count() }}</span>
                    </span>
                </div>
                <!-- Grid optimized for 15 Meja -->
                <div class="flex-1 p-4 grid grid-cols-5 grid-rows-3 gap-3 overflow-hidden">
                    @foreach($karawangMeja as $i)
                                @php
                                    $data = $activeLines->get($i);
                                    $manualStatus = $lineStatuses->get($i);
                                    $isTrouble = ($manualStatus && $manualStatus->status === 'trouble');
                                    $isMaintenance = ($manualStatus && $manualStatus->status === 'maintenance');
                                    $isStopped = ($manualStatus && $manualStatus->status === 'stopped');
                                    
                                    // Meja is ONLY active if running and NOT in alarm state
                                    $isActive = ($data && !$isTrouble && !$isMaintenance && !$isStopped);
                                    
                                    $qcName = ($isActive && isset($operatorMap[$data->operator_initials])) ? $operatorMap[$data->operator_initials] : ($data->operator_initials ?? '-');
                                    
                                    $statusText = 'IDLE';
                                    if ($isTrouble) $statusText = 'TROUBLE';
                                    elseif ($isMaintenance) $statusText = 'MAINTENANCE';
                                    elseif ($isStopped) $statusText = 'OFF';
                                    elseif ($isActive) $statusText = 'RUNNING';
                                @endphp
                                <div class="station-card flex flex-col p-3.5 bg-white border-2 border-slate-100 rounded-2xl shadow-sm h-full min-h-0 overflow-hidden" data-station-type="meja" data-station-id="{{ $i }}">
                                    <div class="flex justify-between items-center mb-1.5 border-b border-slate-50 pb-1.5">
                                        <h3 class="text-xl font-extrabold text-slate-800 tracking-tighter capitalize">MEJA-{{ $i }}</h3>
                                        <div class="status-badge-container">
                                        @if($isTrouble)
                                            <span class="text-[14px] bg-red-100 text-red-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-red-200 animate-pulse">
                                                <i class="material-icons-round text-base">warning</i> TROUBLE
                                            </span>
                                        @elseif($isActive)
                                            <span class="text-[14px] bg-green-100 text-green-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-green-200">
                                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span> RUNNING
                                            </span>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <span class="text-[14px] bg-slate-100 text-slate-600 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-200">
                                                <i class="fas fa-hourglass-half text-xs"></i> STAND BY
                                            </span>
                                        @else
                                            <span class="text-[14px] bg-slate-50 text-slate-400 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-100">
                                                <i class="fas fa-pause text-xs"></i> IDLE
                                            </span>
                                        @endif
                                        </div>
                                    </div>

                                    <div class="flex-1 flex flex-col pt-1 min-h-0 card-content-area">
                                        @if($isActive)
                                            <div class="flex-1 flex flex-col justify-center space-y-1">
                                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-800 truncate ml-2 text-right text-[12px] leading-tight">{{ $data->item->name }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-600 truncate ml-2 text-right text-[10px]">{{ $data->item->part_number }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center border-t border-slate-50 pt-0.5 mt-0.5"><span class="text-slate-400 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-600">{{ $data->created_at->format('H:i') }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">QC</span><span class="font-bold text-slate-600 uppercase truncate ml-2 max-w-[100px] text-right">{{ $qcName }}</span></div>
                                            </div>
                                            <div class="mt-1.5 border-t border-slate-50 pt-1">
                                                <div class="flex justify-between items-end">
                                                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest leading-none">STATUS</span>
                                                    <span class="text-xl font-black leading-none {{ $data->judgment === 'OK' ? 'text-green-600' : 'text-red-600' }}">{{ $data->judgment }}</span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-1 mt-0.5 overflow-hidden">
                                                    <div class="{{ $data->judgment === 'OK' ? 'bg-green-500' : 'bg-red-500' }} h-full" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-70 w-full text-center py-1">
                                                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">STAND BY</h3>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 italic line-clamp-1 px-2">{{ $manualStatus->description ?: 'Waiting MP' }}</p>
                                            </div>
                                        @else
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-40 w-full text-center">
                                                <h3 class="text-xl font-black text-slate-300 uppercase tracking-tighter">Meja Idle</h3>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                    @endforeach

                    <!-- SLOT 15: INTEGRATED ACTIVITY GAUGE -->
                    <div class="station-card flex flex-col p-5 bg-white border-2 border-slate-100 rounded-2xl shadow-sm h-full min-h-0 overflow-hidden bg-slate-50/30">
                        <div id="gauge-subassy-activity" class="w-full h-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 2: IN-PROCESS MONITORING (FULL SCREEN) -->
        <section class="slide" id="slide-inprocess" data-label="In-Process Injection">
             <div class="modern-card">
                <div class="p-3 bg-slate-50/50 border-b flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">In-Process Injection</h2>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-emerald-200 uppercase flex items-center gap-1.5 shadow-sm">
                           <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span> LIVE
                        </span>
                    </div>
                    <span class="bg-blue-100 text-blue-700 text-xs font-black px-3 py-0.5 rounded-full border border-blue-200 uppercase header-status-badge">
                       RUNNING <span class="active-count">{{ $activeMachines->count() }}</span>
                    </span>
                </div>
                <!-- Grid optimized for Karawang Machines -->
                <div class="flex-1 p-3 grid grid-cols-6 gap-2.5 overflow-hidden" style="grid-auto-rows: 1fr;">
                    @foreach($karawangMachines as $i => $tonnage)
                                @php
                                    $data = $activeMachines->get($i);
                                    $manualStatus = $machineStatuses->get($i);
                                    $isTrouble = ($manualStatus && $manualStatus->status === 'trouble');
                                    $isMaintenance = ($manualStatus && $manualStatus->status === 'maintenance');
                                    $isStopped = ($manualStatus && $manualStatus->status === 'stopped');
                                    $isActive = ($data && !$isTrouble && !$isMaintenance && !$isStopped);
                                    $qcName = ($isActive && isset($operatorMap[$data->operator_initials])) ? $operatorMap[$data->operator_initials] : ($data->operator_initials ?? '-');
                                @endphp
                                <div class="station-card flex flex-col p-3 bg-white border-2 border-slate-100 rounded-2xl shadow-sm h-full min-h-0 overflow-hidden" data-station-type="mesin" data-station-id="{{ $i }}">
                                    <div class="flex justify-between items-center mb-1 border-b border-slate-50 pb-1">
                                        <div>
                                            <h3 class="text-sm font-black text-slate-800 tracking-tighter leading-none uppercase">MESIN-{{ $i }}</h3>
                                            <p class="text-[8px] font-bold text-slate-400 tracking-tighter mt-0.5">({{ $tonnage }})</p>
                                        </div>
                                        <div class="status-badge-container">
                                        @if($isTrouble)
                                            <span class="text-[14px] bg-red-100 text-red-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-red-200 animate-pulse">
                                                <i class="material-icons-round text-base">warning</i> TROUBLE
                                            </span>
                                        @elseif($isActive)
                                            <span class="text-[14px] bg-green-100 text-green-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-green-200">
                                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span> RUNNING
                                            </span>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <span class="text-[14px] bg-slate-100 text-slate-600 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-200">
                                                <i class="fas fa-hourglass-half text-xs"></i> STAND BY
                                            </span>
                                        @else
                                            <span class="text-[14px] bg-slate-50 text-slate-400 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-100">
                                                <i class="fas fa-pause text-xs"></i> IDLE
                                            </span>
                                        @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 flex flex-col pt-1 min-h-0 card-content-area">
                                        @if($isActive)
                                            <div class="flex-1 flex flex-col justify-center space-y-1">
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-700 truncate ml-2 text-right text-[10px] leading-tight">{{ $data->item->name }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-500 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-700 truncate ml-2 text-right text-[10px] leading-tight">{{ $data->item->part_number }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center border-t border-slate-50 pt-1 mt-0.5"><span class="text-slate-500 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-700 text-right text-[10px]">{{ $data->created_at->format('H:i') }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-500 font-medium tracking-tight">QC</span><span class="font-bold text-slate-700 truncate ml-2 max-w-[80px] text-right text-[10px]">{{ $qcName }}</span></div>
                                            </div>
                                            <div class="mt-1 border-t border-slate-50 pt-1">
                                                <div class="flex justify-between items-end">
                                                    <span class="text-[9px] text-slate-400 uppercase font-bold tracking-widest leading-none">STATUS</span>
                                                    <span class="text-xl font-black leading-none {{ $data->judgment === 'OK' ? 'text-green-600' : 'text-red-600' }}">{{ $data->judgment }}</span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-0.5 mt-0.5 overflow-hidden">
                                                    <div class="{{ $data->judgment === 'OK' ? 'bg-green-500' : 'bg-red-500' }} h-full" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-70 w-full text-center py-0.5">
                                                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">STAND BY</h3>
                                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 italic line-clamp-1 px-1">{{ $manualStatus->description ?: 'Waiting MP' }}</p>
                                            </div>
                                        @else
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-40 w-full text-center">
                                                <h3 class="text-lg font-black text-slate-200 uppercase tracking-tighter">IDLE</h3>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                    @endforeach

                    <!-- SLOT 18: INTEGRATED ACTIVITY GAUGE -->
                    <div class="station-card flex flex-col p-6 bg-white border-2 border-slate-100 rounded-2xl shadow-sm h-full min-h-0 overflow-hidden bg-slate-50/30">
                        <div id="gauge-inprocess-activity" class="w-full h-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 3: MONITORING RATE NG - KARAWANG -->
        <section class="slide" id="slide-ng-rate" data-label="Monitoring Rate NG - Karawang">
             <div class="modern-card">
                <div class="p-3.5 bg-slate-50/50 border-b flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Monitoring Rate NG - Karawang</h2>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-emerald-200 uppercase flex items-center gap-1.5 shadow-sm">
                           <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span> LIVE
                        </span>
                    </div>
                    <span class="bg-red-100 text-red-700 text-xs font-black px-3 py-1 rounded-full border border-red-200 uppercase">
                        30 Day Trend
                    </span>
                </div>
                <!-- Full Width Line Chart -->
                <div class="flex-1 p-6 min-h-0" style="position:relative;">
                    <canvas id="chart-ng-trend" style="width:100%;height:100%;"></canvas>
                </div>
            </div>
        </section>

        <!-- CLONE SLIDE 1 FOR INFINITE LOOP -->
        <section class="slide" id="slide-subassy-clone" data-label="Outgoing Sub-Assy">
             <div class="modern-card">
                <div class="p-3.5 bg-slate-50/50 border-b flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Outgoing Sub-Assy</h2>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-emerald-200 uppercase flex items-center gap-1.5 shadow-sm">
                           <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span> LIVE
                        </span>
                    </div>
                    <span class="bg-green-100 text-green-700 text-xs font-black px-3 py-1 rounded-full border border-green-200 uppercase">
                        {{ $activeLines->count() }} Meja Aktif
                    </span>
                </div>
                <!-- Grid optimized for 15 Meja (14 Data + 1 Gauge) -->
                <div class="flex-1 p-4 grid grid-cols-5 grid-rows-3 gap-3 overflow-hidden">
                    @foreach($karawangMeja as $i)
                                @php
                                    $data = $activeLines->get($i);
                                    $manualStatus = $lineStatuses->get($i);
                                    $isTrouble = ($manualStatus && $manualStatus->status === 'trouble');
                                    $isMaintenance = ($manualStatus && $manualStatus->status === 'maintenance');
                                    $isStopped = ($manualStatus && $manualStatus->status === 'stopped');
                                    $isActive = ($data && !$isTrouble && !$isMaintenance && !$isStopped);
                                    $qcName = ($isActive && isset($operatorMap[$data->operator_initials])) ? $operatorMap[$data->operator_initials] : ($data->operator_initials ?? '-');
                                @endphp
                                <div class="station-card flex flex-col p-3.5 bg-white border-2 border-slate-100 rounded-2xl shadow-sm h-full min-h-0 overflow-hidden" data-station-type="meja" data-station-id="{{ $i }}">
                                    <div class="flex justify-between items-center mb-1.5 border-b border-slate-50 pb-1.5">
                                        <h3 class="text-xl font-extrabold text-slate-800 tracking-tighter capitalize">MEJA-{{ $i }}</h3>
                                        <div class="status-badge-container">
                                        @if($isTrouble)
                                            <span class="text-[14px] bg-red-100 text-red-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-red-200 animate-pulse">
                                                <i class="material-icons-round text-base">warning</i> TROUBLE
                                            </span>
                                        @elseif($isActive)
                                            <span class="text-[14px] bg-green-100 text-green-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-green-200">
                                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span> RUNNING
                                            </span>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <span class="text-[14px] bg-slate-100 text-slate-600 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-200">
                                                <i class="fas fa-hourglass-half text-xs"></i> STAND BY
                                            </span>
                                        @else
                                            <span class="text-[14px] bg-slate-50 text-slate-400 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-100">
                                                <i class="fas fa-pause text-xs"></i> IDLE
                                            </span>
                                        @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 flex flex-col pt-1 min-h-0 card-content-area">
                                        @if($isActive)
                                            <div class="flex-1 flex flex-col justify-center space-y-1">
                                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-800 truncate ml-2 text-right text-[12px] leading-tight">{{ $data->item->name }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-600 truncate ml-2 text-right text-[10px]">{{ $data->item->part_number }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center border-t border-slate-50 pt-0.5 mt-0.5"><span class="text-slate-400 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-600">{{ $data->created_at->format('H:i') }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">QC</span><span class="font-bold text-slate-600 uppercase truncate ml-2 max-w-[100px] text-right">{{ $qcName }}</span></div>
                                            </div>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-70 w-full text-center py-1">
                                                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">STAND BY</h3>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 italic line-clamp-1 px-2">{{ $manualStatus->description ?: 'Waiting MP' }}</p>
                                            </div>
                                        @else
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-40 w-full text-center">
                                                <h3 class="text-xl font-black text-slate-300 uppercase tracking-tighter">Meja Idle</h3>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                    @endforeach

                    <!-- SLOT 15: INTEGRATED ACTIVITY GAUGE (CLONE) -->
                    <div class="station-card flex flex-col p-5 bg-white border-2 border-slate-100 rounded-2xl shadow-sm h-full min-h-0 overflow-hidden bg-slate-50/30">
                        <div id="gauge-subassy-activity-clone" class="w-full h-full"></div>
                    </div>
                </div>
            </div>
        </section>
        </div>
    </main>


    <script>
        const SLIDE_TIME = 10000; // 10 seconds per slide
        const POLL_TIME  = 10000; // 10 seconds live data refresh
        const slidesWrapper = document.getElementById('slides-wrapper');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.indicator-dot');
        const timerBar = document.getElementById('timer-bar');
        const slideLabel = document.getElementById('current-slide-label');
        let activeIdx = 0;
        
        const chartInstances = {};
        let ngRateDataRaw = @json($ngRateData['karawang'] ?? (object)[]);
        let ngLabelsRaw = @json($ngRateData['labels'] ?? []);
        let activityStats = {
            subAssy: { active: 0, total: 14 },
            inProcess: { active: 0, total: 17 }
        };

        function calculateActivity() {
            // Count ONLY station cards with status RUNNING (not STAND BY / TROUBLE / IDLE)
            function countRunning(slideId) {
                const slide = document.getElementById(slideId);
                if (!slide) return 0;
                let count = 0;
                // Select all status badge spans and check if their text contains 'RUNNING'
                slide.querySelectorAll('.station-card span.bg-green-100').forEach(el => {
                    if (el.textContent.toUpperCase().includes('RUNNING')) count++;
                });
                return count;
            }
            activityStats.subAssy.active   = countRunning('slide-subassy');
            activityStats.inProcess.active = countRunning('slide-inprocess');
        }

        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('digital-clock');
            const dateEl = document.getElementById('date-display');
            if (clockEl) clockEl.textContent = now.toLocaleTimeString('id-id', { hour12: false });
            if (dateEl) dateEl.textContent = now.toLocaleDateString('id-id', { 
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' 
            }).toUpperCase();
        }

        function switchSlide() {
            if (!slidesWrapper || !dots.length) return;
            const dotsCount = dots.length;
            
            // Clean current dot
            dots.forEach(d => d.classList.remove('active'));
            
            activeIdx++;
            
            // Premium smooth transition
            slidesWrapper.style.transition = 'transform 1.2s cubic-bezier(0.4, 0, 0.2, 1)';
            slidesWrapper.style.transform = `translateX(-${activeIdx * 100}%)`;

            const nextDotIdx = activeIdx % dotsCount;
            dots[nextDotIdx].classList.add('active');

            // Update label
            // Slide 0: Sub-Assy, Slide 1: In-Process, Slide 2: NG Rate
            const labelIdx = activeIdx % dotsCount; 
            const labels = ["Outgoing Sub-Assy", "In-Process Monitoring", "Monitoring Rate NG - Karawang"];
            if (slideLabel) {
                slideLabel.textContent = labels[labelIdx] || "Monitoring";
            }

            // NOTE: Charts are now persistent, no need to re-render here.
            // They just slide into view smoothly.

            timerBar.style.transition = 'none';
            timerBar.style.width = '0%';
            setTimeout(() => {
                timerBar.style.transition = `width ${SLIDE_TIME}ms linear`;
                timerBar.style.width = '100%';
            }, 50);
        }

        // Handle the seamless reset at the end of the transition
        if (slidesWrapper) {
            slidesWrapper.addEventListener('transitionend', () => {
                // If we've reached the clone (Slide index 3)
                const realSlidesCount = 3; 
                if (activeIdx >= realSlidesCount) {
                    slidesWrapper.style.transition = 'none';
                    activeIdx = 0;
                    slidesWrapper.style.transform = `translateX(0)`;
                }
            });
        }

        function calculateRate(type) {
            const stats = activityStats[type];
            if (!stats || stats.total === 0) return 0;
            return Math.round((stats.active / stats.total) * 100);
        }

        function renderGauge(container, label, value) {
            if (!window.FusionCharts || !document.getElementById(container)) return;
            
            const dataSource = {
                chart: {
                    caption: label,
                    lowerLimit: "0",
                    upperLimit: "100",
                    showValue: "1",
                    numberSuffix: "%",
                    theme: "gammel",
                    baseFontSize: "14",
                    captionFontSize: "24",
                    subcaptionFontSize: "12",
                    gaugeFillMix: "{light-10},{light-20},{light-30}",
                    gaugeFillRatio: "40,20,40",
                    valueBelowPivot: "1",
                    valuePadding: "20",
                    manageResize: "1",
                    autoScale: "1",
                    animation: "0"
                },
                colorRange: {
                    color: [
                        { minValue: "0",  maxValue: "50",  code: "#ef4444" },
                        { minValue: "50", maxValue: "75",  code: "#f59e0b" },
                        { minValue: "75", maxValue: "100", code: "#10b981" }
                    ]
                },
                dials: {
                    dial: [{
                        value: value.toString(),
                        tooltext: "<b>" + value + "%</b> unit aktif saat ini",
                        borderAlpha: "0",
                        baseWidth: "6",
                        topWidth: "1",
                        radius: "70%"
                    }]
                },
                trendpoints: {
                    point: [{
                        startvalue: "100",
                        displayvalue: " ",
                        thickness: "3",
                        color: "#E15A26",
                        hideValue: "1",
                        usemarker: "1",
                        markerbordercolor: "#E15A26",
                        markertooltext: "Target Approval: 100%"
                    }]
                }
            };

            // Optimize: Update if exists
            if (chartInstances[container]) {
                chartInstances[container].setJSONData(dataSource);
                return;
            }

            chartInstances[container] = new FusionCharts({
                id: container + "-gauge",
                type: "angulargauge",
                renderAt: container,
                width: "100%",
                height: "100%",
                dataFormat: "json",
                dataSource: dataSource
            }).render();
        }

        function renderNGRateChart() {
            const canvas = document.getElementById('chart-ng-trend');
            if (!canvas) return;

            const subData = ngRateDataRaw.sub_assy || [];
            const inpData = ngRateDataRaw.in_process || [];
            const paintingData = ngRateDataRaw.painting || [];

            const ctx = canvas.getContext('2d');
            const W = canvas.parentElement.clientWidth - 32;
            const H = canvas.parentElement.clientHeight - 32;
            canvas.width = W;
            canvas.height = H;
            ctx.clearRect(0, 0, W, H);

            const padL = 60, padR = 60, padT = 40, padB = 60;
            const chartW = W - padL - padR;
            const chartH = H - padT - padB;
            const n = Math.max(ngLabelsRaw.length, 2);

            const allVals = [...subData, ...inpData, ...paintingData].filter(v => v > 0);
            const maxVal = allVals.length ? Math.max(...allVals) * 1.3 : 5;

            function xPos(i) { return padL + (i / (n - 1)) * chartW; }
            function yPos(v) { return padT + chartH - (v / maxVal) * chartH; }

            // --- Grid lines
            ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1;
            for (let g = 0; g <= 5; g++) {
                const y = padT + (g / 5) * chartH;
                ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(padL + chartW, y); ctx.stroke();
                const val = maxVal * (1 - g / 5);
                ctx.fillStyle = '#64748b'; ctx.font = '11px Inter, sans-serif'; ctx.textAlign = 'right';
                ctx.fillText(val.toFixed(1) + '%', padL - 8, y + 4);
            }

            // --- X-axis labels in Indonesian "DD-Mon" format
            const bulanID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            ctx.fillStyle = '#64748b'; ctx.textAlign = 'center'; ctx.font = '10px Inter, sans-serif';
            ngLabelsRaw.forEach((rawDate, i) => {
                if (i % 3 === 0) {
                    const parts = rawDate.split('-');
                    const day = parseInt(parts[2]);
                    const mon = bulanID[parseInt(parts[1]) - 1] || '';
                    ctx.fillText(`${day}-${mon}`, xPos(i), padT + chartH + 18);
                }
            });

            // --- Draw lines
            function drawLine(data, color) {
                ctx.beginPath(); ctx.strokeStyle = color; ctx.lineWidth = 4;
                ctx.lineJoin = 'round'; ctx.lineCap = 'round';
                let started = false;
                data.forEach((v, i) => {
                    const x = xPos(i), y = yPos(v);
                    if (!started) { ctx.moveTo(x, y); started = true; } else ctx.lineTo(x, y);
                });
                ctx.stroke();
                data.forEach((v, i) => {
                    if (v > 0) {
                        ctx.beginPath(); ctx.arc(xPos(i), yPos(v), 4, 0, Math.PI * 2);
                        ctx.fillStyle = color; ctx.fill();
                    }
                });
            }
            drawLine(subData, '#3b82f6');
            drawLine(inpData, '#f59e0b');
            drawLine(paintingData, '#e83e8c');

            // --- Last data point labels
            function drawLastLabel(data, color, offset) {
                if (!data || data.length === 0) return;
                const val = data[data.length - 1];
                const x = xPos(data.length - 1);
                const y = yPos(val);

                ctx.font = 'bold 16px Inter, sans-serif';
                ctx.textAlign = 'left';
                
                // Shadow / Glow for clarity on background
                ctx.shadowBlur = 4;
                ctx.shadowColor = 'white';
                ctx.fillStyle = color;
                ctx.fillText(`${val.toFixed(2)}%`, x + 10, y + offset);
                
                // Reset shadow
                ctx.shadowBlur = 0;
            }
            drawLastLabel(subData, '#3b82f6', -5);
            drawLastLabel(inpData, '#f59e0b', 15);
            drawLastLabel(paintingData, '#e83e8c', -25);

            // --- Data point labels (only for > 0)
            function drawValueLabels(data, color, yOffset) {
                if (!data || data.length === 0) return;
                
                data.forEach((v, i) => {
                    if (v > 0) {
                        const x = xPos(i);
                        const y = yPos(v);

                        ctx.font = 'bold 12px Inter, sans-serif';
                        ctx.textAlign = 'center';
                        
                        // Shadow / Glow for clarity on background
                        ctx.shadowBlur = 4;
                        ctx.shadowColor = 'white';
                        ctx.fillStyle = color;
                        ctx.fillText(`${v.toFixed(1)}%`, x, y + yOffset);
                        
                        // Reset shadow
                        ctx.shadowBlur = 0;
                    }
                });
            }
            drawValueLabels(subData, '#3b82f6', -15);
            drawValueLabels(inpData, '#f59e0b', -15);
            drawValueLabels(paintingData, '#e83e8c', -15);

            // --- Legend bottom
            const legendY = padT + chartH + 42;
            const centerX = (padL + padL + chartW) / 2;
            ctx.font = 'bold 12px Inter, sans-serif'; ctx.textAlign = 'left';
            ctx.fillStyle = '#3b82f6'; ctx.fillRect(centerX - 160, legendY - 10, 26, 4);
            ctx.fillStyle = '#334155'; ctx.fillText('Sub-Assy', centerX - 130, legendY);
            ctx.fillStyle = '#f59e0b'; ctx.fillRect(centerX - 40, legendY - 10, 26, 4);
            ctx.fillStyle = '#334155'; ctx.fillText('In-Process', centerX - 10, legendY);
            ctx.fillStyle = '#e83e8c'; ctx.fillRect(centerX + 80, legendY - 10, 26, 4);
            ctx.fillStyle = '#334155'; ctx.fillText('Painting', centerX + 110, legendY);

        }

        function killWatermark() {
            const cleaner = () => {
                const watermarks = document.querySelectorAll('svg g[style*="cursor:pointer"], svg g[style*="cursor: pointer"], text');
                watermarks.forEach(el => {
                    if (el.textContent.toLowerCase().includes('fusioncharts')) {
                        el.style.setProperty('display', 'none', 'important');
                        // If it's a group containing the text, hide the whole group
                        if(el.tagName === 'text' && el.parentNode) {
                            el.parentNode.style.setProperty('display', 'none', 'important');
                        }
                    }
                });
            };
            
            // Run immediately and then multiple times to catch late renders
            cleaner();
            setTimeout(cleaner, 100);
            setTimeout(cleaner, 500);
            setTimeout(cleaner, 1000);
            setTimeout(cleaner, 3000);
        }
        
        // Continuous observer to keep it hidden during transitions/refreshes
        const observer = new MutationObserver(() => killWatermark());
        observer.observe(document.body, { childList: true, subtree: true });

        function renderAllGauges() {
            // Canvas chart (instant, no library needed)
            calculateActivity();
            renderNGRateChart();

            // FusionCharts gauges (rendered when library is ready)
            if (!window.FusionCharts) return;
            const subAssyRate = calculateRate('subAssy');
            const inProcessRate = calculateRate('inProcess');

            FusionCharts.ready(function() {
                renderGauge("gauge-subassy-activity", "Utility", subAssyRate);
                renderGauge("gauge-inprocess-activity", "Utility", inProcessRate);
                const cloneGauge = document.getElementById('gauge-subassy-activity-clone');
                if(cloneGauge) {
                    renderGauge("gauge-subassy-activity-clone", "Utility", subAssyRate);
                }
                killWatermark();
            });
        }

        async function syncLive() {
            try {
                const ts = new Date().getTime();
                const res = await fetch(`/dashboard/tv/live?t=${ts}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                
                if (!data) return;

                const { activeLines, activeMachines, lineStatuses, machineStatuses, operatorMap } = data;

                // Helper to render Status Badge HTML
                const getStatusBadge = (status, isActive) => {
                    if (status === 'trouble') return `<span class="text-[14px] bg-red-100 text-red-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-red-200 animate-pulse"><i class="material-icons-round text-base">warning</i> TROUBLE</span>`;
                    if (isActive) return `<span class="text-[14px] bg-green-100 text-green-700 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-green-200"><span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span> RUNNING</span>`;
                    if (status === 'standby' || status === 'stopped') return `<span class="text-[14px] bg-slate-100 text-slate-600 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-200"><i class="fas fa-hourglass-half text-xs"></i> STAND BY</span>`;
                    return `<span class="text-[14px] bg-slate-50 text-slate-400 px-4 py-1 rounded-full font-black flex items-center gap-2 uppercase tracking-tighter shadow-sm border border-slate-100"><i class="fas fa-pause text-xs"></i> IDLE</span>`;
                };

                // Helper to render Content HTML
                const getContentHtml = (type, id, item, manualStatus) => {
                    const status = manualStatus ? manualStatus.status : null;
                    const isActive = (item && status !== 'trouble' && status !== 'maintenance' && status !== 'stopped');
                    
                    if (isActive) {
                        const qcName = operatorMap[item.operator_initials?.toUpperCase()] || item.operator_initials || '-';
                        const judgmentColor = item.judgment === 'OK' ? 'text-green-600' : 'text-red-600';
                        const progressColor = item.judgment === 'OK' ? 'bg-green-500' : 'bg-red-500';
                        
                        return `
                            <div class="flex-1 flex flex-col justify-center space-y-1">
                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-800 truncate ml-2 text-right text-[12px] leading-tight">${item.item_name}</span></div>
                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-600 truncate ml-2 text-right text-[10px]">${item.part_number}</span></div>
                                <div class="flex justify-between text-[11px] items-center border-t border-slate-50 pt-0.5 mt-0.5"><span class="text-slate-400 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-600">${item.created_at}</span></div>
                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">QC</span><span class="font-bold text-slate-600 uppercase truncate ml-2 max-w-[100px] text-right">${qcName}</span></div>
                            </div>
                            <div class="mt-1.5 border-t border-slate-50 pt-1">
                                <div class="flex justify-between items-end">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest leading-none">STATUS</span>
                                    <span class="text-xl font-black leading-none ${judgmentColor}">${item.judgment}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1 mt-0.5 overflow-hidden">
                                    <div class="${progressColor} h-full" style="width: 100%"></div>
                                </div>
                            </div>`;
                    } else if (status === 'standby' || status === 'stopped') {
                        return `
                            <div class="flex-1 flex flex-col items-center justify-center opacity-70 w-full text-center py-1">
                                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">STAND BY</h3>
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 italic line-clamp-1 px-2">${manualStatus.description || 'Waiting MP'}</p>
                            </div>`;
                    } else {
                        return `
                            <div class="flex-1 flex flex-col items-center justify-center opacity-40 w-full text-center">
                                <h3 class="text-xl font-black text-slate-300 uppercase tracking-tighter">${type === 'meja' ? 'Meja Idle' : 'IDLE'}</h3>
                            </div>`;
                    }
                };

                // Update all cards
                document.querySelectorAll('.station-card[data-station-type]').forEach(card => {
                    const type = card.dataset.stationType;
                    const id = card.dataset.stationId;
                    
                    let item, manualStatus;
                    if (type === 'meja') {
                        item = activeLines[id];
                        manualStatus = lineStatuses[id];
                    } else {
                        item = activeMachines[id];
                        manualStatus = machineStatuses[id];
                    }

                    const status = manualStatus ? manualStatus.status : null;
                    const isActive = (item && status !== 'trouble' && status !== 'maintenance' && status !== 'stopped');

                    // Update Badge
                    const badgeContainer = card.querySelector('.status-badge-container');
                    if (badgeContainer) badgeContainer.innerHTML = getStatusBadge(status, isActive);

                    // Update Content
                    const contentArea = card.querySelector('.card-content-area');
                    if (contentArea) contentArea.innerHTML = getContentHtml(type, id, item, manualStatus);
                });

                // Update Header Counts
                const runningLines = Object.values(activeLines).filter((l, i) => {
                   const s = lineStatuses[Object.keys(activeLines)[i]];
                   return s ? (s.status !== 'trouble' && s.status !== 'maintenance' && s.status !== 'stopped') : true;
                }).length;

                const runningMachines = Object.values(activeMachines).filter((m, i) => {
                   const s = machineStatuses[Object.keys(activeMachines)[i]];
                   return s ? (s.status !== 'trouble' && s.status !== 'maintenance' && s.status !== 'stopped') : true;
                }).length;

                const counts = document.querySelectorAll('.header-status-badge .active-count');
                if (counts[0]) counts[0].textContent = runningLines;
                if (counts[1]) counts[1].textContent = runningMachines;

                // Sync Activity Stats for Gauges
                activityStats.subAssy.active = runningLines;
                activityStats.inProcess.active = runningMachines;

                // Render Gauges
                renderAllGauges();
                
                console.log('Real-time sync completed');

            } catch (e) {
                console.warn("Real-time sync failed", e);
            }
        }

        updateClock();
        setInterval(updateClock, 1000);
        setInterval(switchSlide, SLIDE_TIME);
        setInterval(syncLive, POLL_TIME);

        // Initial First Render
        FusionCharts.ready(() => {
            renderAllGauges();
        });

        timerBar.style.transition = `width ${SLIDE_TIME}ms linear`;
        timerBar.style.width = '100%';

        // Fullscreen Logic
        const fsBtn = document.getElementById('fullscreen-toggler');
        const fsIcon = fsBtn.querySelector('i');

        fsBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.warn(`Error attempting to enable fullscreen: ${err.message}`);
                });
                fsIcon.textContent = 'fullscreen_exit';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    fsIcon.textContent = 'fullscreen';
                }
            }
        });

        // Sync icon if manually exited via ESC key
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                fsIcon.textContent = 'fullscreen';
            } else {
                fsIcon.textContent = 'fullscreen_exit';
            }
        });
    </script>
</body>
</html>
