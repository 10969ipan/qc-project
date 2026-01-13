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
        .unit-number {
            font-size: 1rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .part-number {
            font-size: 0.65rem;
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
            font-size: 0.55rem;
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
            font-size: 0.85rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
        }

        .status-badge-manual {
            font-size: 0.55rem;
            font-weight: 700;
            padding: 2px 8px;
            letter-spacing: 0.02em;
        }

        .status-idle .unit-number { opacity: 0.4; font-size: 1.2rem; }
        .status-idle .item-name { font-size: 0.6rem; }

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

        /* Responsive Design for Small Screens */
        @media (max-width: 768px) {
            .welcome-modern {
                padding: 1rem !important;
                margin-bottom: 1rem !important;
                border-radius: 16px !important;
            }

            .welcome-modern h4 {
                font-size: 1.1rem !important;
            }

            .welcome-modern p {
                font-size: 0.8rem !important;
            }

            #current-date {
                font-size: 0.9rem !important;
            }

            .stat-card-modern {
                padding: 0.75rem !important;
                border-radius: 16px !important;
                margin-bottom: 0.75rem !important;
            }

            .stat-card-modern .h3 {
                font-size: 1.5rem !important;
            }

            .stat-card-modern .text-xs {
                font-size: 0.7rem !important;
            }

            .modern-card {
                border-radius: 16px !important;
            }

            .modern-card-header {
                padding: 1rem !important;
            }

            .modern-card-title {
                font-size: 0.95rem !important;
            }

            .status-item {
                min-height: 90px !important;
                padding: 0.5rem !important;
            }

            .unit-number {
                font-size: 0.85rem !important;
            }

            .part-number {
                font-size: 0.65rem !important;
            }

            .item-name {
                font-size: 0.6rem !important;
            }

            .status-badge {
                font-size: 0.55rem !important;
                padding: 2px 8px !important;
            }
        }

        @media (max-width: 576px) {
            .welcome-modern {
                padding: 0.75rem !important;
            }

            .welcome-modern h4 {
                font-size: 1rem !important;
            }

            .stat-card-modern {
                padding: 0.5rem !important;
            }

            .stat-card-modern .h3 {
                font-size: 1.25rem !important;
            }

            .status-item {
                min-height: 80px !important;
            }

            .unit-number {
                font-size: 0.75rem !important;
            }
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
                        <p class="mb-0" style="opacity: 0.9; font-size: 0.9rem;">Quality Department</p>
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

    @if(isset($activeReport))
    <!-- Monthly Report Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-gradient-primary text-white py-2 px-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-pdf mr-2" style="font-size: 1.1rem;"></i>
                        <div>
                            <h6 class="mb-0 font-weight-bold d-none d-md-block">{{ $activeReport->title }}</h6>
                            <h6 class="mb-0 font-weight-bold d-md-none">Laporan Bulanan</h6>
                            <small class="d-block" style="opacity: 0.85; font-size: 0.75rem;">{{ $activeReport->period }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="pdf-container" style="position: relative; width: 100%; height: 600px; background: #f8f9fc;">
                        <iframe 
                            src="{{ route('monthly_reports.pdf', $activeReport->id) }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH" 
                            style="width: 100%; height: 100%; border: none;"
                            title="Laporan Bulanan PDF"
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Responsive PDF Viewer */
        @media (max-width: 768px) {
            .pdf-container {
                height: 450px !important;
            }
        }
        
        @media (max-width: 576px) {
            .pdf-container {
                height: 350px !important;
            }
        }
    </style>
    @endif

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
                            <small class="text-muted">Monitoring Produksi Sub Assy Hari Ini</small>
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
                                    $statusClass = 'status-stopped'; // Use stopped style for consistency
                                    $isActive = false;
                                } elseif ($isActive) {
                                    $statusClass = $isNg ? 'status-active-danger' : 'status-active-success';
                                }
                            @endphp
                            <div class="col-6 col-md-4 col-lg-3 mb-4 px-2">
                                <div class="status-item {{ $statusClass }}" 
                                     onclick="showDetailModal(this)"
                                     style="cursor: pointer;"
                                     data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                     @if($isActive)
                                         data-part-number="{{ $data->item->part_number ?? '-' }}"
                                         data-item-name="{{ $data->item->name ?? '-' }}"
                                         data-judgment="{{ $data->judgment }}"
                                         data-total-qty="{{ $data->total_qty ?? '-' }}"
                                         data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                         data-ok-count="{{ $data->total_ok ?? '-' }}"
                                         data-ng-count="{{ $data->total_ng ?? '-' }}"
                                         data-operator="{{ $data->operator_initials ?? '-' }}"
                                         data-date="{{ $data->date ?? '-' }}"
                                         data-shift="{{ $data->shift ?? '-' }}"
                                         data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"

                                     @endif
                                     @if($manualStatus && $manualStatus->status !== 'normal')
                                         data-manual-description="{{ $manualStatus->description }}"
                                         data-manual-by="{{ $manualStatus->created_by }}"
                                         data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}"
                                     @endif
                                     title="Click untuk detail">
                                    
                                    <div class="unit-number">MEJA-{{ $i }}</div>
                                    
                                    @if($manualStatus && $manualStatus->status !== 'normal')
                                        <div class="status-badge status-badge-manual" style="background: rgba(255,255,255,0.3); margin-top: 5px;">
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
                            <small class="text-muted">Monitoring Produksi Injection Hari Ini</small>
                        </div>
                    </div>
                    <span class="badge badge-info px-3 py-2 rounded-pill shadow-sm text-white">
                        Running: {{ $runningMachinesCount }}
                    </span>
                </div>
                <div class="card-body bg-light" style="background: #fdfdfe;">
                    <div class="row px-2">
                        @for ($i = 1; $i <= 19; $i++)
                            @if($i == 11 || $i == 13)
                                @continue
                            @endif
                            @php
                                $data = $activeMachines->get($i);
                                $manualStatus = $machineStatuses->get($i);

                                // Machine Tonnage Mapping
                                $machineTonnage = [
                                    1 => '850', 2 => '650', 3 => '650', 4 => '650',
                                    5 => '550', 6 => '450', 7 => '360', 8 => '210',
                                    9 => '210', 10 => '160', 12 => '80', 14 => '120',
                                    15 => '160', 16 => '180', 17 => '180', 18 => '120', 19 => '160',
                                ];
                                $tonnage = $machineTonnage[$i] ?? '-';

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
                                <div class="status-item {{ $statusClass }}" 
                                     onclick="showDetailModal(this)"
                                     style="cursor: pointer;"
                                     data-status="{{ $manualStatus && $manualStatus->status !== 'normal' ? $manualStatus->status : ($isActive ? 'active' : 'idle') }}"
                                     @if($isActive)
                                         data-part-number="{{ $data->item->part_number ?? '-' }}"
                                         data-item-name="{{ $data->item->name ?? '-' }}"
                                         data-judgment="{{ $data->judgment }}"
                                         data-total-qty="{{ $data->total_qty ?? '-' }}"
                                         data-sampling-qty="{{ $data->sampling_qty ?? '-' }}"
                                         data-ok-count="{{ $data->total_ok ?? '-' }}"
                                         data-ng-count="{{ $data->total_ng ?? '-' }}"
                                         data-operator="{{ $data->operator_initials ?? '-' }}"
                                         data-date="{{ $data->date ?? '-' }}"
                                         data-shift="{{ $data->shift ?? '-' }}"
                                         data-time="{{ $data->created_at ? $data->created_at->format('H:i') : '-' }}"

                                     data-tonnage="{{ $tonnage }}"

                                     @endif
                                     @if($manualStatus && $manualStatus->status !== 'normal')
                                         data-manual-description="{{ $manualStatus->description }}"
                                         data-manual-by="{{ $manualStatus->created_by }}"
                                         data-manual-updated="{{ $manualStatus->updated_at->format('Y-m-d H:i') }}"
                                     @endif
                                     title="Click untuk detail">
                                    
                                    
                                    <div class="unit-number">MESIN-{{ $i }}</div>

                                    @if($manualStatus && $manualStatus->status !== 'normal')
                                        <div class="status-badge status-badge-manual" style="background: rgba(255,255,255,0.3); margin-top: 5px;">
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

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
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
                // Active production data
                content = `
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-box mr-2"></i>Part Information
                        </h6>
                        <div class="pl-4">
                            <p class="mb-1"><strong>Part Number:</strong> ${partNumber}</p>
                            <p class="mb-1"><strong>Item Name:</strong> ${itemName}</p>
                            <p class="mb-0"><strong>Machine Tonnage:</strong> ${tonnage}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-clipboard-check mr-2"></i>Quality Check
                        </h6>
                        <div class="pl-4">
                            <p class="mb-1"><strong>Total Qty:</strong> ${totalQty}</p>
                            <p class="mb-2"><strong>Sampling Qty:</strong> ${samplingQty}</p>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <span class="badge badge-success">Sampling OK: ${okCount}</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge badge-danger">Sampling NG: ${ngCount}</span>
                                </div>
                            </div>
                            <p class="mb-1">
                                <strong>Judgment:</strong> 
                                <span class="badge badge-${judgment === 'OK' ? 'success' : 'danger'}">${judgment}</span>
                            </p>
                            <p class="mb-1"><strong>QC:</strong> ${operator}</p>
                            <p class="mb-0"><strong>Date:</strong> ${date} | <strong>Time:</strong> ${time} | <strong>Shift:</strong> ${shift}</p>
                        </div>
                    </div>
                `;
            } else if (status === 'maintenance' || status === 'stopped' || status === 'trouble') {
                // Manual status
                let statusBadge = '';
                let statusIcon = '';
                if (status === 'maintenance') {
                    statusBadge = '<span class="badge badge-warning">GANTI MOLD/SETTING</span>';
                    statusIcon = '<i class="fas fa-tools mr-2"></i>';
                } else if (status === 'stopped') {
                    statusBadge = '<span class="badge badge-dark">STAND BY</span>';
                    statusIcon = '<i class="fas fa-pause-circle mr-2"></i>';
                } else if (status === 'trouble') {
                    statusBadge = '<span class="badge badge-danger">TROUBLE</span>';
                    statusIcon = '<i class="fas fa-exclamation-triangle mr-2"></i>';
                }

                content = `
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-warning mb-2">
                            ${statusIcon}Manual Status
                        </h6>
                        <div class="pl-4">
                            <p class="mb-2"><strong>Status:</strong> ${statusBadge}</p>
                            <p class="mb-1"><strong>Description:</strong> ${manualDescription || 'No description'}</p>
                            <p class="mb-1"><strong>Set by:</strong> ${manualBy}</p>
                            <p class="mb-0"><strong>Updated:</strong> ${manualUpdated}</p>
                        </div>
                    </div>
                `;
            } else {
                // Idle status
                content = `
                    <div class="text-center py-4">
                        <i class="fas fa-moon fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Status: IDLE</h6>
                        <p class="text-muted mb-0">No production data available</p>
                    </div>
                `;
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