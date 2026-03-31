<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QC TV Dashboard | {{ config('app.name') }}</title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="{{ asset('fonts/material-icons.css') }}" rel="stylesheet">
    
    <!-- External Dependencies -->
    <!-- Note: Assuming these assets exist or are accessible via public path -->
    <script src="{{ asset('js/vendor/tailwind.min.js') }}"></script>
    <script src="{{ asset('js/vendor/canvasjs.min.js') }}"></script>
    <script src="https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
    <script src="https://cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js"></script>

    <style>
        :root {
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --accent-primary: #6366f1;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            background-color: var(--bg-dark);
            color: #f8fafc;
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
        }

        .slide-container {
            position: relative;
            height: calc(100vh - 80px); /* Header offset */
            width: 100%;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            visibility: hidden;
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(0.98) translateY(20px);
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
        }

        .slide.active {
            opacity: 1;
            visibility: visible;
            transform: scale(1) translateY(0);
            z-index: 10;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .header-bar {
            height: 80px;
            background: rgba(15, 23, 42, 0.9);
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 4rem;
            z-index: 100;
        }

        .status-badge {
            padding: 0.5rem 1.75rem;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .badge-running { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .timer-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 6px;
            background: linear-gradient(to right, #6366f1, #818cf8);
            transition: width 0.1s linear;
            z-index: 200;
        }

        .big-number {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1;
            font-family: 'JetBrains Mono', monospace;
        }

        .grid-monitoring {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            overflow: hidden;
        }

        .monitor-item {
            padding: 1.25rem;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .monitor-item.active { border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.05); }
        .monitor-item.trouble { border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.1); animation: pulse-border-red 2s infinite; }

        @keyframes pulse-border-red {
            0%, 100% { border-color: rgba(239, 68, 68, 0.4); }
            50% { border-color: rgba(239, 68, 68, 1); }
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    </style>
</head>
<body>
    <div class="timer-progress" id="timer-bar"></div>

    <header class="header-bar">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-500/40">
                <i class="material-icons text-white text-4xl">live_tv</i>
            </div>
            <div>
                <h1 class="text-3xl font-black tracking-tight uppercase">Dashboard Produksi & QC</h1>
                <p class="text-indigo-400 text-sm font-bold tracking-[0.2em] uppercase">Pabrik {{ strtoupper($plantCode ?? 'Jakarta') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-10 text-right">
            <div>
                <div class="text-4xl font-extrabold font-mono tracking-tighter" id="live-clock">00:00:00</div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-[0.3em]" id="live-date">Loading...</div>
            </div>
            <div class="flex gap-4" id="slide-indicators">
                <div class="w-4 h-4 rounded-full bg-indigo-600 shadow-[0_0_15px_rgba(99,102,241,0.5)] transition-all duration-500 indicator" data-idx="0"></div>
                <div class="w-4 h-4 rounded-full bg-gray-800 transition-all duration-500 indicator" data-idx="1"></div>
            </div>
        </div>
    </header>

    <main class="slide-container">
        <!-- SLIDE 1: PRODUCTION MONITORING -->
        <section class="slide active" id="slide-production">
            <div class="grid grid-cols-2 gap-10 h-full">
                <!-- Sub Assy Section -->
                <div class="glass-card p-10 flex flex-col">
                    <div class="flex justify-between items-center mb-8">
                        <div class="flex items-center gap-4">
                            <i class="material-icons text-emerald-400 text-5xl">settings_input_component</i>
                            <h2 class="text-4xl font-black">SUB-ASSY</h2>
                        </div>
                        <div class="status-badge badge-running" id="subassy-badge">{{ $activeLines->count() }} ACTIVE</div>
                    </div>
                    
                    <div class="grid grid-cols-4 gap-4 overflow-hidden" id="subassy-grid">
                        @foreach(range(1, 16) as $i)
                            @php 
                                $data = $activeLines->get($i); 
                                $status = $lineStatuses->get($i);
                                $isTrouble = ($status && $status->status === 'trouble');
                                $isActive = ($data && !$isTrouble);
                            @endphp
                            <div class="monitor-item {{ $isTrouble ? 'trouble' : ($isActive ? 'active' : '') }}">
                                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Meja</span>
                                <span class="text-3xl font-black {{ $isTrouble ? 'text-red-500' : ($isActive ? 'text-emerald-400' : 'text-slate-600') }}">{{ $i }}</span>
                                <div class="mt-2 text-[9px] uppercase font-bold text-gray-400 truncate w-full text-center">
                                    {{ $isActive ? $data->item->name : ($isTrouble ? 'ALERT' : 'READY') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- In-Process Section -->
                <div class="glass-card p-10 flex flex-col">
                    <div class="flex justify-between items-center mb-8">
                        <div class="flex items-center gap-4">
                            <i class="material-icons text-blue-400 text-5xl">precision_manufacturing</i>
                            <h2 class="text-4xl font-black">IN-PROCESS</h2>
                        </div>
                        <div class="status-badge badge-running" id="inprocess-badge" style="color: #60a5fa; border-color: rgba(96, 165, 250, 0.4); background: rgba(96, 165, 250, 0.1);">{{ $activeMachines->count() }} ACTIVE</div>
                    </div>
                    
                    <div class="grid grid-cols-4 gap-4 overflow-hidden" id="machine-grid">
                        @foreach(range(1, 16) as $i)
                             @php 
                                $data = $activeMachines->get($i); 
                                $status = $machineStatuses->get($i);
                                $isTrouble = ($status && $status->status === 'trouble');
                                $isActive = ($data && !$isTrouble);
                            @endphp
                            <div class="monitor-item {{ $isTrouble ? 'trouble' : ($isActive ? 'active' : '') }}">
                                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Mesin</span>
                                <span class="text-3xl font-black {{ $isTrouble ? 'text-red-500' : ($isActive ? 'text-blue-400' : 'text-slate-600') }}">{{ $i }}</span>
                                <div class="mt-2 text-[9px] uppercase font-bold text-gray-400 truncate w-full text-center">
                                    {{ $isActive ? $data->item->name : ($isTrouble ? 'ALERT' : 'READY') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 2: APPROVAL PERCENTAGES -->
        <section class="slide" id="slide-approval">
            <div class="grid grid-cols-2 gap-10 h-full">
                <!-- Jakarta -->
                @php
                    $jktRate = 0;
                    if(isset($statsJakarta)) {
                        $handled = $statsJakarta['approved'] + $statsJakarta['rejected'];
                        $totalDue = $handled + $statsJakarta['pending_late'];
                        $jktRate = $totalDue == 0 ? 100 : round(($handled / $totalDue) * 100);
                    }
                @endphp
                <div class="glass-card p-12 flex flex-col items-center justify-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-3 bg-indigo-500"></div>
                    <h2 class="text-5xl font-black mb-2">JAKARTA</h2>
                    <p class="text-gray-400 font-bold uppercase tracking-[0.4em] mb-12">QC Approval Compliance</p>
                    
                    <div class="relative flex items-center justify-center">
                        <svg class="w-80 h-80">
                            <circle class="text-gray-800" stroke-width="20" stroke="currentColor" fill="transparent" r="140" cx="160" cy="160"/>
                            <circle class="text-indigo-500 transition-all duration-1000 ease-out" stroke-width="20" stroke-dasharray="{{ 2 * pi() * 140 }}" stroke-dashoffset="{{ 2 * pi() * 140 * (1 - $jktRate / 100) }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="140" cx="160" cy="160" style="transform: rotate(-90deg); transform-origin: 50% 50%;"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-8xl font-black font-mono tracking-tighter" id="jkt-rate">{{ $jktRate }}%</span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Rate H+1</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-16 mt-16 w-full max-w-xl">
                        <div class="text-center">
                            <div class="text-indigo-400 text-5xl font-black" id="jkt-approved">{{ $statsJakarta['approved'] ?? 0 }}</div>
                            <div class="text-[10px] text-gray-500 uppercase font-black tracking-widest mt-3">Approved</div>
                        </div>
                        <div class="text-center">
                            <div class="text-white text-5xl font-black" id="jkt-pending">{{ $statsJakarta['pending'] ?? 0 }}</div>
                            <div class="text-[10px] text-gray-500 uppercase font-black tracking-widest mt-3">Pending</div>
                        </div>
                        <div class="text-center border-l border-gray-800 pl-8">
                            <div class="text-red-500 text-5xl font-black" id="jkt-late">{{ $statsJakarta['pending_late'] ?? 0 }}</div>
                            <div class="text-[10px] text-red-500 uppercase font-black tracking-widest mt-3">Overdue</div>
                        </div>
                    </div>
                </div>

                <!-- Karawang -->
                @php
                    $krwRate = 0;
                    if(isset($statsKarawang)) {
                        $handled = $statsKarawang['approved'] + $statsKarawang['rejected'];
                        $totalDue = $handled + $statsKarawang['pending_late'];
                        $krwRate = $totalDue == 0 ? 100 : round(($handled / $totalDue) * 100);
                    }
                @endphp
                <div class="glass-card p-12 flex flex-col items-center justify-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-3 bg-emerald-500"></div>
                    <h2 class="text-5xl font-black mb-2">KARAWANG</h2>
                    <p class="text-gray-400 font-bold uppercase tracking-[0.4em] mb-12">QC Approval Compliance</p>
                    
                    <div class="relative flex items-center justify-center">
                        <svg class="w-80 h-80">
                            <circle class="text-gray-800" stroke-width="20" stroke="currentColor" fill="transparent" r="140" cx="160" cy="160"/>
                            <circle class="text-emerald-500 transition-all duration-1000 ease-out" stroke-width="20" stroke-dasharray="{{ 2 * pi() * 140 }}" stroke-dashoffset="{{ 2 * pi() * 140 * (1 - $krwRate / 100) }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="140" cx="160" cy="160" style="transform: rotate(-90deg); transform-origin: 50% 50%;"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-8xl font-black font-mono tracking-tighter" id="krw-rate">{{ $krwRate }}%</span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Rate H+1</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-16 mt-16 w-full max-w-xl">
                        <div class="text-center">
                            <div class="text-emerald-400 text-5xl font-black" id="krw-approved">{{ $statsKarawang['approved'] ?? 0 }}</div>
                            <div class="text-[10px] text-gray-500 uppercase font-black tracking-widest mt-3">Approved</div>
                        </div>
                        <div class="text-center">
                            <div class="text-white text-5xl font-black" id="krw-pending">{{ $statsKarawang['pending'] ?? 0 }}</div>
                            <div class="text-[10px] text-gray-500 uppercase font-black tracking-widest mt-3">Pending</div>
                        </div>
                        <div class="text-center border-l border-gray-800 pl-8">
                            <div class="text-red-500 text-5xl font-black" id="krw-late">{{ $statsKarawang['pending_late'] ?? 0 }}</div>
                            <div class="text-[10px] text-red-500 uppercase font-black tracking-widest mt-3">Overdue</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        const CONFIG = {
            slideTime: 10000,
            pollTime: 60000
        };

        let activeIdx = 0;
        const slides = document.querySelectorAll('.slide');
        const indicators = document.querySelectorAll('.indicator');
        const timerBar = document.getElementById('timer-bar');

        function updateClock() {
            const now = new Date();
            document.getElementById('live-clock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('live-date').textContent = now.toLocaleDateString('id-ID', { 
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' 
            }).toUpperCase();
        }

        function switchSlide() {
            slides[activeIdx].classList.remove('active');
            indicators[activeIdx].classList.remove('bg-indigo-600', 'shadow-[0_0_15px_rgba(99,102,241,0.5)]');
            indicators[activeIdx].classList.add('bg-gray-800');

            activeIdx = (activeIdx + 1) % slides.length;

            slides[activeIdx].classList.add('active');
            indicators[activeIdx].classList.remove('bg-gray-800');
            indicators[activeIdx].classList.add('bg-indigo-600', 'shadow-[0_0_15px_rgba(99,102,241,0.5)]');

            // Reset Timer Bar
            timerBar.style.transition = 'none';
            timerBar.style.width = '0%';
            setTimeout(() => {
                timerBar.style.transition = `width ${CONFIG.slideTime}ms linear`;
                timerBar.style.width = '100%';
            }, 50);
        }

        async function pollData() {
            try {
                const res = await fetch('/dashboard/tv', { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const data = await res.json();
                
                // Update Badge Counts
                document.getElementById('subassy-badge').textContent = `${Object.keys(data.activeLines).length} ACTIVE`;
                document.getElementById('inprocess-badge').textContent = `${Object.keys(data.activeMachines).length} ACTIVE`;

                // Quick UI Sync for Stats
                if(data.statsJakarta) {
                    document.getElementById('jkt-approved').textContent = data.statsJakarta.approved;
                    document.getElementById('jkt-pending').textContent = data.statsJakarta.pending;
                    document.getElementById('jkt-late').textContent = data.statsJakarta.pending_late;
                }
                
                // Note: Full grid refresh or SVG animation updates can be added here
            } catch (e) {
                console.warn("Poll failed", e);
            }
        }

        // Init
        setInterval(updateClock, 1000);
        setInterval(switchSlide, CONFIG.slideTime);
        setInterval(pollData, CONFIG.pollTime);
        
        updateClock();
        switchSlide(); // Start first animation
    </script>
</body>
</html>
