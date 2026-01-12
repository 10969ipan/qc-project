@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <style>
        /* Modern Dashboard CSS */
        :root {
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: 1px solid rgba(255, 255, 255, 0.2);
            --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.08);
            --shadow-hover: 0 20px 40px -5px rgba(0,0,0,0.12);
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
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            height: 100%;
        }

        .modern-card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.03);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modern-card-title {
            font-weight: 800;
            font-size: 1.1rem;
            color: #2d3748;
            margin: 0;
            letter-spacing: -0.025em;
        }

        /* Status Grid Item */
        .status-item {
            position: relative;
            border-radius: 16px;
            padding: 0;
            min-height: 110px;
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
            box-shadow: 0 12px 20px rgba(0,0,0,0.1);
        }

        /* Variants */
        .status-active-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 8px 15px rgba(28, 200, 138, 0.3);
        }

        .status-active-info {
            background: var(--gradient-info);
            color: white;
            box-shadow: 0 8px 15px rgba(54, 185, 204, 0.3);
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
        .unit-number {
            font-size: 1rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .part-number {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.9;
            margin-bottom: 2px;
            max-width: 90%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-name {
            font-size: 0.7rem;
            font-weight: 400;
            opacity: 0.8;
            line-height: 1.2;
            max-width: 85%;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .status-badge {
            margin-top: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
        }

        .status-idle .unit-number { opacity: 0.4; font-size: 1.2rem; }
        .status-idle .item-name { font-size: 0.7rem; }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(231, 74, 59, 0); }
            100% { box-shadow: 0 0 0 0 rgba(231, 74, 59, 0); }
        }

        /* Welcome Section Modern */
        .welcome-modern {
            background: var(--gradient-primary);
            border-radius: 20px;
            padding: 1.5rem; /* Reduced from 2.5rem */
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.25);
            margin-bottom: 1.5rem; /* Reduced from 2rem */
        }
        
        /* Stats Cards Modern */
        .stat-card-modern {
            background: white;
            border-radius: 20px;
            padding: 1rem; /* Reduced from 1.5rem */
            height: 100%;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
            border-left: 5px solid transparent;
        }
        
        .stat-card-modern:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
    </style>

    <!-- Welcome Section -->
    <div class="row">
        <div class="col-12">
            <div class="welcome-modern shadow">
                <!-- SVG Background Decoration -->
                <div style="position: absolute; top: 0; right: 0; width: 100%; height: 100%; opacity: 0.1; background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-lg-8">
                        <h4 class="font-weight-bold mb-1">Selamat Datang, {{ Auth::user()->name }}! </h4>
                        <p class="mb-0" style="opacity: 0.9; font-size: 0.9rem;">Quality Control Department</p>
                        <div class="mt-3">
                            <span class="badge badge-light text-primary px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-user-tag mr-1"></i> {{ ucfirst(Auth::user()->role) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4 text-right d-none d-lg-block">
                         <div class="h3 mb-0 font-weight-bold" id="current-date">Loading...</div>
                         <small style="opacity: 0.8; font-size: 0.9rem;"><i class="fas fa-clock mr-1"></i> <span id="current-time"></span> WIB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($combinedStats))
    <!-- Stats Cards Section -->
    <div class="row mb-5">
        <div class="col-12 mb-3">
            <h5 class="font-weight-bold text-gray-800 ml-1" style="border-left: 4px solid #4e73df; padding-left: 12px; letter-spacing: 0.5px;">OVERVIEW APPROVAL QC</h5>
        </div>


        <!-- Pending -->
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="stat-card-modern" style="border-left-color: #f6c23e;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $combinedStats['pending'] }}</div>
                    </div>
                    <div class="icon-circle bg-warning text-white" style="width: 40px; height: 40px; font-size: 1rem;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="stat-card-modern" style="border-left-color: #1cc88a;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $combinedStats['approved'] }}</div>
                    </div>
                    <div class="icon-circle bg-success text-white" style="width: 40px; height: 40px; font-size: 1rem;">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="stat-card-modern" style="border-left-color: #e74a3b;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $combinedStats['rejected'] }}</div>
                    </div>
                    <div class="icon-circle bg-danger text-white" style="width: 40px; height: 40px; font-size: 1rem;">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Status Section -->
    <div class="row">
        <!-- Sub Assy Lines -->
        <div class="col-xl-6 col-lg-12 mb-5">
            <div class="modern-card h-100">
                <div class="modern-card-header">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-primary text-white mr-3" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-industry"></i>
                        </div>
                        <div>
                            <h6 class="modern-card-title">Line Sub Assy</h6>
                            <small class="text-muted">Monitoring Produksi Hari Ini</small>
                        </div>
                    </div>
                    <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">
                        Running: {{ $activeLines->count() }}
                    </span>
                </div>
                <div class="card-body bg-light" style="background: #fdfdfe;">
                    <div class="row px-2">
                        @for ($i = 1; $i <= 15; $i++)
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
                                    $statusClass = 'status-idle'; // Use Idle style for consistency
                                    $isActive = false;
                                } elseif ($isActive) {
                                    $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                }
                            @endphp
                            <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                <div class="status-item {{ $statusClass }}" 
                                     @if($isActive) title="Part: {{ $data->item->part_number }}&#010;Item: {{ $data->item->name }}&#010;Status: {{ $data->judgment }}" @endif
                                     @if($manualStatus && !$isActive) title="{{ ucfirst($manualStatus->status) }}: {{ $manualStatus->description }}" @endif>
                                    
                                    <div class="unit-number">LINE-{{ $i }}</div>
                                    
                                    @if($manualStatus && $manualStatus->status !== 'normal')
                                        <div class="status-badge" style="background: rgba(255,255,255,0.3); margin-top: 5px; {{ $manualStatus->status === 'stopped' ? 'color: #858796; background: #eaecf4;' : '' }}">
                                            {{ $manualStatus->status === 'stopped' ? 'IDLE' : strtoupper($manualStatus->status) }}
                                        </div>
                                        <small class="{{ $manualStatus->status === 'stopped' ? 'text-muted' : 'text-white' }} small mt-1" style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
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
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- In Process Machines -->
        <div class="col-xl-6 col-lg-12 mb-5">
            <div class="modern-card h-100">
                <div class="modern-card-header">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-info text-white mr-3" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <h6 class="modern-card-title">Mesin In Process</h6>
                            <small class="text-muted">Monitoring Mesin Hari Ini</small>
                        </div>
                    </div>
                    <span class="badge badge-info px-3 py-2 rounded-pill shadow-sm text-white">
                        Running: {{ $runningMachinesCount }}
                    </span>
                </div>
                <div class="card-body bg-light" style="background: #fdfdfe;">
                    <div class="row px-2">
                        @for ($i = 1; $i <= 18; $i++)
                            @php
                                $data = $activeMachines->get($i);
                                $manualStatus = $machineStatuses->get($i);

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
                                } elseif ($isActive) {
                                    $statusClass = $isNg ? 'status-active-danger' : 'status-active-info';
                                }
                            @endphp
                            <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                <div class="status-item {{ $statusClass }}" 
                                     @if($isActive) title="Part: {{ $data->item->part_number }}&#010;Item: {{ $data->item->name }}&#010;Status: {{ $data->judgment }}" @endif
                                     @if($manualStatus && !$isActive) title="{{ ucfirst($manualStatus->status) }}: {{ $manualStatus->description }}" @endif>
                                    
                                    <div class="unit-number">MESIN-{{ $i }}</div>
                                    
                                    @if($manualStatus && $manualStatus->status !== 'normal')
                                        <div class="status-badge" style="background: rgba(255,255,255,0.3); margin-top: 5px;">
                                            @if($manualStatus->status === 'maintenance')
                                                GANTI MOLD/SETTING
                                            @elseif($manualStatus->status === 'stopped')
                                                STAND BY
                                            @else
                                                {{ strtoupper($manualStatus->status) }}
                                            @endif
                                        </div>
                                        <small class="text-white small mt-1" style="font-size: 0.6rem; opacity: 0.9;">{{ Str::limit($manualStatus->description, 15) }}</small>
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
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
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