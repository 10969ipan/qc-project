<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QC TV </title>
    
    <!-- Premium Fonts & Icons -->
    <link href="{{ asset('fonts/inter.css') }}" rel="stylesheet">
    <link href="{{ asset('fonts/material-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    
    <!-- Scripts (Local Assets) -->
    <script src="{{ asset('js/vendor/tailwind.min.js') }}"></script>
    <script src="{{ asset('js/vendor/canvasjs.min.js') }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.js') }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.widgets.js') }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.theme.fusion.js') }}"></script>
    <script src="{{ asset('js/vendor/fusioncharts.theme.gammel.js') }}"></script>

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
            font-family: 'Inter', sans-serif;
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

        <div class="dots-container absolute left-1/2 -translate-x-1/2 flex gap-3">
            <div class="indicator-dot active"></div>
            <div class="indicator-dot"></div>
            <div class="indicator-dot"></div>
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
            $karawangMeja = range(1, 15);
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
                    </div>
                    <span class="bg-green-100 text-green-700 text-xs font-black px-3 py-1 rounded-full border border-green-200 uppercase">
                        {{ $activeLines->count() }} Meja Aktif
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
                                <div class="station-card flex flex-col p-2.5 bg-white border-2 border-slate-100 rounded-2xl shadow-sm h-full min-h-0 overflow-hidden">
                                    <div class="flex justify-between items-center mb-1 border-b border-slate-50 pb-1">
                                        <h3 class="text-xl font-extrabold text-slate-800 tracking-tighter capitalize">MEJA-{{ $i }}</h3>
                                        @if($isTrouble)
                                            <span class="text-[9px] bg-red-100 text-red-700 px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1 animate-pulse">
                                                <i class="material-icons-round text-xs">warning</i> TROUBLE
                                            </span>
                                        @elseif($isActive)
                                            <span class="text-[9px] bg-green-100 text-green-700 px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1 uppercase tracking-wider">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> RUNNING
                                            </span>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <span class="text-[9px] bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-bold flex items-center gap-1 uppercase tracking-wider">
                                                <i class="fas fa-hourglass-half text-[10px]"></i> STAND BY
                                            </span>
                                        @else
                                            <span class="text-[9px] bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full font-bold flex items-center gap-1 uppercase tracking-wider">
                                                <i class="fas fa-pause text-[8px]"></i> IDLE
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex-1 flex flex-col pt-1.5 min-h-0">
                                        @if($isActive)
                                            <div class="flex-1 flex flex-col justify-center space-y-2">
                                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-800 truncate ml-2 text-right text-xs leading-tight">{{ $data->item->name }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-600 truncate ml-2 text-right">{{ $data->item->part_number }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center border-t border-slate-50 pt-1 mt-1"><span class="text-slate-400 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-600">{{ $data->created_at->format('H:i') }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">QC</span><span class="font-bold text-slate-600 uppercase">{{ $qcName }}</span></div>
                                            </div>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-70 w-full text-center">
                                                <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter">STAND BY</h3>
                                                <p class="text-[12px] font-bold text-slate-500 uppercase tracking-widest mt-1 italic">{{ $manualStatus->description ?: 'Waiting MP' }}</p>
                                            </div>
                                        @else
                                            <div class="flex-1 flex flex-col items-center justify-center opacity-40 w-full text-center">
                                                <h3 class="text-2xl font-black text-slate-200 uppercase tracking-tighter">Meja Idle</h3>
                                                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mt-1">Wait Setup</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- SLIDE 2: IN-PROCESS MONITORING (FULL SCREEN) -->
        <section class="slide" id="slide-inprocess" data-label="In-Process Injection">
             <div class="modern-card">
                <div class="p-3 bg-slate-50/50 border-b flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">In-Process Injection</h2>
                    </div>
                    <span class="bg-blue-100 text-blue-700 text-xs font-black px-3 py-0.5 rounded-full border border-blue-200 uppercase">
                        {{ $activeMachines->count() }} Mesin Aktif
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
                                    
                                    // Machine is ONLY active if running and NOT in manual state
                                    $isActive = ($data && !$isTrouble && !$isMaintenance && !$isStopped);
                                    
                                    $qcName = ($isActive && isset($operatorMap[$data->operator_initials])) ? $operatorMap[$data->operator_initials] : ($data->operator_initials ?? '-');
                                    
                                    $statusText = 'IDLE';
                                    if ($isTrouble) $statusText = 'TROUBLE';
                                    elseif ($isMaintenance) $statusText = 'MAINTENANCE';
                                    elseif ($isStopped) $statusText = 'OFF';
                                    elseif ($isActive) $statusText = 'RUNNING';
                                @endphp
                                <div class="station-card flex flex-col p-1.5 bg-white border-2 border-slate-100 rounded-xl shadow-sm h-full min-h-0 overflow-hidden">
                                    <div class="flex justify-between items-center mb-1 border-b border-slate-50 pb-1">
                                        <div>
                                            <h3 class="text-base font-extrabold text-slate-800 tracking-tighter leading-none">MESIN-{{ $i }}</h3>
                                            <p class="text-[9px] font-bold text-slate-400 tracking-tighter mt-0.5">({{ $tonnage }})</p>
                                        </div>
                                        @if($isTrouble)
                                            <span class="text-[8px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full font-bold flex items-center gap-1 animate-pulse">
                                                <i class="material-icons-round text-[9px]">warning</i> ALERT
                                            </span>
                                        @elseif($isActive)
                                            <span class="text-[8px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold flex items-center gap-1 uppercase tracking-wider">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> RUNNING
                                            </span>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <div class="flex items-center gap-1 px-1.5 py-0.5 border border-slate-200 rounded-full bg-slate-50">
                                                <span class="text-[9px] text-slate-600 font-bold uppercase tracking-widest flex items-center gap-1">
                                                    <i class="fas fa-hourglass-half text-[8px]"></i> STAND BY
                                                </span>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1 px-1.5 py-0.5 border border-slate-200 rounded-full bg-slate-50">
                                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1">
                                                    <i class="fas fa-pause text-[8px]"></i> IDLE
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 flex flex-col pt-1">
                                        @if($isActive)
                                            <div class="flex-1 flex flex-col justify-center space-y-1.5">
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-700 truncate ml-2 text-right">{{ $data->item->name }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-500 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-700 truncate ml-2 text-right">{{ $data->item->part_number }}</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-500 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-700 text-right">{{ $data->created_at->format('H:i') }} WIB</span></div>
                                                <div class="flex justify-between text-[11px] items-center"><span class="text-slate-500 font-medium tracking-tight">QC</span><span class="font-bold text-slate-700 truncate ml-2 text-right">{{ $qcName }}</span></div>
                                            </div>
                                            <div class="mt-2">
                                                <div class="flex justify-between items-end pt-1">
                                                    <span class="text-[7px] text-slate-400 uppercase font-black tracking-widest leading-none">STATUS</span>
                                                    <span class="text-2xl font-black leading-none {{ $data->judgment === 'OK' ? 'text-green-600' : 'text-red-600' }}">{{ $data->judgment }}</span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-1 mt-0.5 overflow-hidden">
                                                    <div class="{{ $data->judgment === 'OK' ? 'bg-green-500' : 'bg-red-500' }} h-full" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                            <div class="flex flex-col items-center justify-center opacity-70 h-full mt-1 w-full text-center">
                                                <div class="text-2xl font-black text-slate-800 tracking-tighter">STAND BY</div>
                                                <p class="text-[12px] font-bold text-slate-500 uppercase mt-0.5 tracking-widest italic leading-tight">{{ $manualStatus->description ?: 'Waiting MP' }}</p>
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center opacity-30 h-full mt-1">
                                                <div class="text-lg font-black text-slate-300 tracking-tighter">MESIN IDLE</div>
                                                <p class="text-[8px] font-bold text-slate-400 uppercase mt-0.5 tracking-widest">Wait Setup</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- SLIDE 3: APPROVAL STATISTICS (CATEGORIZED GAUGES) -->
        <section class="slide" id="slide-stats" data-label="Approval Statistics">
             <div class="grid grid-cols-2 gap-10 h-full">
                <!-- Sub-Assy Stats Card -->
                <div class="modern-card">
                    <div class="p-6 border-b flex flex-col">
                        <div>
                            <h2 class="text-2xl font-black text-slate-800 uppercase">Sub-Assy Approval</h2>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">QC Rate Hari Ini</p>
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col items-center justify-center p-8">
                        <div id="gauge-subassy" class="w-full h-[400px]"></div>
                    </div>
                </div>

                <!-- In-Process Stats Card -->
                <div class="modern-card">
                    <div class="p-6 border-b flex flex-col">
                        <div>
                            <h2 class="text-2xl font-black text-slate-800 uppercase">In-Process Approval</h2>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">QC Rate Hari Ini</p>
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col items-center justify-center p-8">
                        <div id="gauge-inprocess" class="w-full h-[400px]"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CLONE SLIDE 1 FOR INFINITE LOOP -->
        <section class="slide" id="slide-subassy-clone" data-label="Outgoing Sub-Assy">
             <div class="modern-card">
                <div class="p-3.5 bg-slate-50/50 border-b flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Outgoing Sub-Assy</h2>
                    </div>
                    <span class="bg-green-100 text-green-700 text-xs font-black px-3 py-1 rounded-full border border-green-200 uppercase">
                        {{ $activeLines->count() }} Meja Aktif
                    </span>
                </div>
                <div class="flex-1 p-4 grid grid-cols-5 grid-rows-3 gap-3 overflow-hidden">
                    @foreach($karawangMeja as $i)
                        @php
                            $data = $activeLines->get($i);
                            $manualStatus = $lineStatuses->get($i);
                            $isTrouble = ($manualStatus && $manualStatus->status === 'trouble');
                            $isMaintenance = ($manualStatus && $manualStatus->status === 'maintenance');
                            $isActive = ($data && !$isTrouble && !$isMaintenance);
                            $statusLabel = $isActive ? 'OK' : ($isTrouble ? 'NG' : ($isMaintenance ? 'MAIN' : 'IDLE'));
                        @endphp
                        <div class="station-container relative h-full min-h-0">
                            <div class="station-card flex flex-col p-1.5 bg-white border-2 border-slate-100 rounded-xl shadow-sm h-full min-h-0 overflow-hidden">
                                <div class="flex justify-between items-center mb-1 border-b border-slate-50 pb-1">
                                    <div>
                                        <h3 class="text-base font-extrabold text-slate-800 tracking-tighter leading-none">MEJA-{{ $i }}</h3>
                                    </div>
                                    @if($isTrouble)
                                        <span class="flex items-center gap-1 bg-red-100 text-red-600 text-[10px] font-black px-2 py-0.5 rounded-full uppercase"><i class="fas fa-exclamation-triangle"></i> NG</span>
                                    @elseif($isMaintenance)
                                        <span class="bg-blue-100 text-blue-600 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">MAIN</span>
                                    @elseif($isActive)
                                        <span class="bg-green-100 text-green-600 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">OK</span>
                                    @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                        <span class="flex items-center gap-1 bg-slate-100 text-slate-600 text-[10px] font-black px-2 py-0.5 rounded-full uppercase"><i class="fas fa-hourglass-half"></i> STAND BY</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-400 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">IDLE</span>
                                    @endif
                                </div>
                                <div class="flex-1 flex flex-col pt-1.5">
                                    @if($isActive)
                                        <div class="flex-1 flex flex-col justify-center space-y-2">
                                            <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-800 truncate ml-2 text-right">{{ $data->item->name }}</span></div>
                                            <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-600 truncate ml-2 text-right">{{ $data->item->part_number }}</span></div>
                                            <div class="flex justify-between text-[11px] items-center border-t border-slate-50 pt-1 mt-1"><span class="text-slate-400 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-600 tracking-tighter">{{ $data->created_at->format('H:i') }}</span></div>
                                            <div class="flex justify-between text-[11px] items-center"><span class="text-slate-400 font-medium tracking-tight">QC</span><span class="font-bold text-slate-600 uppercase">{{ $qcName }}</span></div>
                                        </div>
                                    @elseif($manualStatus && ($manualStatus->status === 'standby' || $manualStatus->status === 'stopped'))
                                        <div class="flex-1 flex flex-col items-center justify-center opacity-70 w-full text-center">
                                            <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter">STAND BY</h3>
                                            <p class="text-[12px] font-bold text-slate-500 uppercase tracking-widest mt-1 italic">{{ $manualStatus->description ?: 'Waiting MP' }}</p>
                                        </div>
                                    @else
                                        <div class="flex-1 flex flex-col items-center justify-center opacity-40 w-full text-center">
                                            <h3 class="text-2xl font-black text-slate-200 uppercase tracking-tighter">Meja Idle</h3>
                                            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mt-1">Wait Setup</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        </div>
    </main>

    <script id="dashboard-stats" type="application/json">
        {
            "statsSubAssy": @json($dailyStatsSubAssy),
            "statsInProcess": @json($dailyStatsInProcess)
        }
    </script>

    <script>
        const SLIDE_TIME = 10000; // 10 seconds per slide
        const POLL_TIME = 30000;  // 30 seconds sync
        const slidesWrapper = document.getElementById('slides-wrapper');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.indicator-dot');
        const timerBar = document.getElementById('timer-bar');
        const slideLabel = document.getElementById('current-slide-label');
        let activeIdx = 0;
        const JSON_STATS = JSON.parse(document.getElementById('dashboard-stats').textContent);

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
            // Calculate virtual index for dots (looping back to 0 when on clone)
            const dotIdx = activeIdx % dots.length;
            dots[dotIdx].classList.remove('active');

            // Move to next slide
            activeIdx++;

            // Animate wrapper
            if (slidesWrapper) {
                slidesWrapper.style.transition = 'transform 1.2s cubic-bezier(0.645, 0.045, 0.355, 1)';
                slidesWrapper.style.transform = `translateX(-${activeIdx * 100}%)`;
            }

            // Update dots based on new index
            const nextDotIdx = activeIdx % dots.length;
            dots[nextDotIdx].classList.add('active');

            // Update header text based on data-label of current slide
            // Use modulo to correctly pick label for the clone
            const labelIdx = activeIdx % slides.length;
            if (slideLabel && slides[labelIdx]) {
                slideLabel.textContent = slides[labelIdx].getAttribute('data-label');
            }

            // If we are on Slide 3 (Stats) or its clone, handle gauges
            if(labelIdx === 2) {
                setTimeout(renderAllGauges, 300);
            }

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
                // If we've reached the clone (Slide 4, index 3)
                if (activeIdx >= slides.length - 1) {
                    slidesWrapper.style.transition = 'none';
                    activeIdx = 0;
                    slidesWrapper.style.transform = `translateX(0)`;
                }
            });
        }

        function calculateRate(stats) {
            if (!stats) return 0;
            const total = (stats.approved || 0) + (stats.pending || 0) + (stats.rejected || 0);
            if (total === 0) return 0;
            return Math.round(((stats.approved || 0) / total) * 100);
        }

        function renderGauge(container, label, value) {
            if (!window.FusionCharts || !document.getElementById(container)) return;
            new FusionCharts({
                type: "angulargauge",
                renderAt: container,
                width: "100%",
                height: "100%",
                dataFormat: "json",
                dataSource: {
                    chart: {
                        caption: label + " Approval Rate",
                        lowerLimit: "0",
                        upperLimit: "100",
                        showValue: "1",
                        numberSuffix: "%",
                        theme: "gammel",
                        baseFontSize: "14",
                        captionFontSize: "20",
                        subcaptionFontSize: "12",
                        gaugeFillMix: "{light-10},{light-20},{light-30}",
                        gaugeFillRatio: "40,20,40"
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
                            tooltext: "<b>" + value + "%</b> disetujui hari ini",
                            borderAlpha: "0",
                            baseWidth: "15",
                            topWidth: "1",
                            radius: "150"
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
                }
            }).render();
        }

        function renderAllGauges() {
            if (!window.FusionCharts) return;
            
            FusionCharts.ready(function() {
                renderGauge("gauge-subassy", "Sub-Assy", calculateRate(JSON_STATS.statsSubAssy));
                renderGauge("gauge-inprocess", "In-Process", calculateRate(JSON_STATS.statsInProcess));
            });
        }

        async function syncData() {
            try {
                // Fetch the entire page as HTML to extract updated monitoring cards
                const res = await fetch('/dashboard/tv?plant=karawang', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await res.text();
                
                // Use DOMParser to extract the new data
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // 1. Update the Slides Content (Meja, Machine, Stats, and Clone)
                const newWrapper = doc.getElementById('slides-wrapper');
                const currentWrapper = document.getElementById('slides-wrapper');
                if (newWrapper && currentWrapper) {
                    // Update innerHTML but preserve the current transform state
                    currentWrapper.innerHTML = newWrapper.innerHTML;
                }

                // 2. Update JSON stats for gauges
                const newStatsScript = doc.getElementById('dashboard-stats');
                if (newStatsScript) {
                    const newData = JSON.parse(newStatsScript.textContent);
                    JSON_STATS.statsSubAssy = newData.statsSubAssy;
                    JSON_STATS.statsInProcess = newData.statsInProcess;
                }
                
                // 3. Refresh gauges if we are on the stats slide
                if (activeIdx % slides.length === 2) {
                    renderAllGauges();
                }

                console.log("Dashboard Sync: Data updated successfully.");

            } catch (e) {
                console.warn("Polling Sync failed", e);
            }
        }

        updateClock();
        setInterval(updateClock, 1000);
        setInterval(switchSlide, SLIDE_TIME);
        setInterval(syncData, POLL_TIME);
        
        FusionCharts.ready(() => {
            if(activeIdx === 2) renderAllGauges();
        });

        timerBar.style.transition = `width ${SLIDE_TIME}ms linear`;
        timerBar.style.width = '100%';
    </script>
</body>
</html>
