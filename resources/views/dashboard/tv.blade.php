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
            position: relative;
            height: calc(100vh - 80px); /* Tighter header */
            width: 100%;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(0.98);
            padding: 0.75rem 1.25rem;
        }

        .slide.active {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
            z-index: 10;
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
                <div class="flex-1 p-4 grid grid-cols-5 grid-rows-3 gap-3 overflow-hidden min-h-0">
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
                                        @else
                                            <span class="text-[9px] bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full font-bold flex items-center gap-1 uppercase tracking-wider">
                                                <i class="fas fa-pause text-[8px]"></i> IDLE
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex-1 flex flex-col pt-1.5">
                                        @if($isActive)
                                            <div class="flex-1 flex flex-col justify-center space-y-2">
                                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Item</span><span class="font-bold text-slate-800 truncate ml-2 text-right">{{ $data->item->name }}</span></div>
                                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Part No.</span><span class="font-bold text-slate-800 text-right">{{ $data->item->part_number }}</span></div>
                                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">Jam</span><span class="font-bold text-slate-800 text-right">{{ $data->created_at->format('H:i') }} WIB</span></div>
                                                <div class="flex justify-between text-[13px] items-center"><span class="text-slate-500 font-medium tracking-tight">QC</span><span class="font-bold text-slate-800 truncate ml-2 text-right">{{ $qcName }}</span></div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="flex justify-between items-end pt-1">
                                                    <span class="text-[9px] text-slate-400 uppercase font-black tracking-widest leading-none">STATUS</span>
                                                    <span class="text-3xl font-black leading-none {{ $data->judgment === 'OK' ? 'text-green-600' : 'text-red-600' }}">{{ $data->judgment }}</span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-1 mt-0.5 overflow-hidden">
                                                    <div class="{{ $data->judgment === 'OK' ? 'bg-green-500' : 'bg-red-500' }} h-full" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center opacity-30 h-full">
                                                <div class="text-2xl font-black text-slate-300 tracking-tighter">Meja Idle</div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5 tracking-widest">Wait Setup</p>
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
                                        @else
                                            <div class="flex flex-col items-center justify-center opacity-30 h-full mt-1">
                                                <div class="text-lg font-black text-slate-300 tracking-tighter">Machine Idle</div>
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
    </main>

    <script id="dashboard-stats" type="application/json">
        {
            "statsSubAssy": @json($dailyStatsSubAssy),
            "statsInProcess": @json($dailyStatsInProcess)
        }
    </script>

    <script>
        const SLIDE_TIME = 5000; // 15 seconds for more data density
        const POLL_TIME = 60000;
        let activeIdx = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.indicator-dot');
        const timerBar = document.getElementById('timer-bar');
        const slideLabel = document.getElementById('current-slide-label');
        let JSON_STATS = JSON.parse(document.getElementById('dashboard-stats').textContent);

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
            slides[activeIdx].classList.remove('active');
            dots[activeIdx].classList.remove('active');

            activeIdx = (activeIdx + 1) % slides.length;

            slides[activeIdx].classList.add('active');
            dots[activeIdx].classList.add('active');
            slideLabel.textContent = slides[activeIdx].getAttribute('data-label');

            if(activeIdx === 2) renderAllGauges();

            timerBar.style.transition = 'none';
            timerBar.style.width = '0%';
            setTimeout(() => {
                timerBar.style.transition = `width ${SLIDE_TIME}ms linear`;
                timerBar.style.width = '100%';
            }, 50);
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
                // Ensure plant=karawang is always appended for consistency with TV requirements
                const res = await fetch('/dashboard/tv?plant=karawang', { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const data = await res.json();
                
                // Update total counts in headers
                const subassyHeaderCount = document.querySelector('#slide-subassy .bg-green-100');
                const inprocessHeaderCount = document.querySelector('#slide-inprocess .bg-blue-100');
                if(subassyHeaderCount) subassyHeaderCount.textContent = `${Object.keys(data.activeLines).length} Meja Aktif`;
                if(inprocessHeaderCount) inprocessHeaderCount.textContent = `${Object.keys(data.activeMachines).length} Mesin Aktif`;

                // Update internal JSON stats object
                if(data.dailyStatsSubAssy) {
                    JSON_STATS.statsSubAssy = data.dailyStatsSubAssy;
                }
                if(data.dailyStatsInProcess) {
                    JSON_STATS.statsInProcess = data.dailyStatsInProcess;
                }
                
                // If we are currently on the stat slide, refresh gauges
                if(activeIdx === 2) renderAllGauges();

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
