@extends('layouts.admin')

@section('title', 'Schedule Kepatuhan Operator')

@section('content')
    @php
        $plantCode = strtolower($plantCode ?: 'karawang');
        $isJkt = in_array($plantCode, ['jakarta', 'jkt']);
        $baseHeader = \App\Models\GeneralSetting::getDocHeader('kepatuhan_operator', $plantCode, [
            'no_dokumen' => $isJkt ? 'QC-JKT-F-051' : 'PI-KRW-F-051',
            'tgl_terbit' => '31/03/2022',
            'revisi'     => '08/09/2023',
            'halaman'    => '1 / 1'
        ]);
        $docHeader = \App\Models\GeneralSetting::getDocHeader('schedule_kepatuhan_operator', $plantCode, $baseHeader);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $weeks = [
            1 => ['start' => 1, 'end' => 7],
            2 => ['start' => 8, 'end' => 14],
            3 => ['start' => 15, 'end' => 21],
            4 => ['start' => 22, 'end' => 28],
            5 => ['start' => 29, 'end' => 31],
        ];
    @endphp

    <style>
        /* ===== UI STYLES (TANPA STICKY BAR) ===== */
        #content-wrapper .op-schedule-grid-wrapper {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }

        body #content-wrapper .op-schedule-grid {
            table-layout: fixed;
            border-collapse: collapse;
            width: 100%;
            min-width: 1200px;
            background-color: #fff;
            border: 1px solid #e2e8f0;
            margin-bottom: 0;
        }

        body #content-wrapper .op-schedule-grid thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.62rem;
            letter-spacing: 0.3px;
            padding: 6px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            text-align: center;
        }

        body #content-wrapper .op-schedule-grid thead th.week-header {
            height: 28px;
            background-color: #f1f5f9;
            color: #334155;
        }

        body #content-wrapper .op-schedule-grid thead th.day-header {
            height: 24px;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.60rem;
        }

        body #content-wrapper .op-schedule-grid thead th.op-col, 
        body #content-wrapper .op-schedule-grid thead th.bag-col, 
        body #content-wrapper .op-schedule-grid thead th.status-col {
            background-color: #f1f5f9;
        }

        body #content-wrapper .op-schedule-grid .op-col { width: 17%; min-width: 180px; }
        body #content-wrapper .op-schedule-grid .bag-col { width: 10.5%; min-width: 120px; }
        body #content-wrapper .op-schedule-grid .status-col { width: 4.5%; min-width: 55px; border-right: 2px solid #cbd5e1; }

        body #content-wrapper .op-schedule-grid tbody td.op-col,
        body #content-wrapper .op-schedule-grid tbody td.bag-col {
            background-color: #fff;
            color: #334155;
            font-size: 0.68rem;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
        }

        body #content-wrapper .op-schedule-grid tbody td.status-col {
            background-color: #f8fafc;
            font-weight: 800;
            font-size: 0.65rem;
            border: 1px solid #e2e8f0;
            border-right: 2px solid #cbd5e1;
            text-align: center;
        }

        body #content-wrapper .op-schedule-grid td {
            border: 1px solid #e2e8f0;
            height: 28px;
            padding: 2px;
            vertical-align: middle;
            background-color: #fff;
            font-size: 0.68rem;
        }

        .badge-legend {
            display: inline-block;
            padding: 2px 6px;
            font-size: 0.62rem;
            border-radius: 4px;
            font-weight: 700;
            line-height: 1.2;
        }

        .marker-p { background-color: #10b981 !important; color: white !important; }
        .marker-a { background-color: #06b6d4 !important; color: white !important; }
        .day-sunday { background-color: #fef2f2 !important; color: #dc2626 !important; }

        /* Metadata Info Tabel persis Standar Checksheet (Hanya tampil saat Cetak) */
        .schedule-meta-table {
            display: none;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
            font-size: 0.72rem;
            background: #fff;
        }
        .schedule-meta-table td {
            border: 1px solid #dee2e6;
            padding: 4px 8px;
            vertical-align: middle;
        }
        .schedule-meta-table .lbl {
            font-weight: 700;
            width: 85px;
            background: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.65rem;
        }
        .schedule-meta-table .val {
            color: #1e293b;
        }

        /* Custom Filter Wrapper & Select Styling persis Checksheet */
        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
        .custom-filter-wrapper .ips-input, .filter-select-custom {
            padding: 2px 8px !important;
            font-size: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            background: #fff !important;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
            height: 31px !important;
            border-radius: 4px !important;
            font-weight: 600 !important;
            color: #212529 !important;
        }
        .custom-filter-wrapper .ips-input { padding-right: 28px !important; }
        .custom-filter-wrapper .ips-input:focus, .filter-select-custom:focus { border-color: #4a6cf7 !important; box-shadow: 0 0 0 2px rgba(74,108,247,.18) !important; outline: none !important; }
        .custom-filter-wrapper .ips-clear { right: 8px !important; font-size: 18px !important; font-weight: 700 !important; color: #64748b !important; line-height: 1 !important; }
        .custom-filter-wrapper .ips-clear:hover { color: #ef4444 !important; }
        .custom-filter-wrapper { position: relative; top: 0px; }

        /* ===== PRINT VIEW STYLES (STANDAR ISO CHECKSHEET SEPERTI INPROCESS) ===== */
        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm 8mm;
            }

            * {
                box-sizing: border-box;
            }

            html, body {
                background: #fff !important;
                font-family: Arial, sans-serif !important;
                color: #000 !important;
                font-size: 6.5pt !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Sembunyikan navigasi, filter, scroll-top, dan tombol */
            .navbar, .sidebar, #accordionSidebar, .topbar, footer, .no-print, 
            #scheduleFilterForm, .btn, .alert, .scroll-to-top {
                display: none !important;
            }

            #wrapper, #content-wrapper, #content, .container-fluid, .main-content-container, .card, .card-body {
                margin: 0 !important;
                padding: 0 !important;
                padding-top: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: #fff !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            #content > .container-fluid {
                padding: 0 !important;
                padding-top: 0 !important;
            }

            .card-body {
                padding: 0 !important;
                zoom: 82%; /* Skala proporsional agar 31 hari muat presisi di 1 lembar A4 landscape */
            }

            .header-iso-table {
                width: 100% !important;
                margin-top: 0 !important;
                margin-bottom: 3px !important;
                border-collapse: collapse !important;
            }

            .header-iso-table, .header-iso-table td, .header-iso-table th {
                border: 1px solid #000 !important;
                color: #000 !important;
            }

            /* Metadata Table Cetak (Hanya Muncul Saat Print) */
            .schedule-meta-table {
                display: table !important;
                width: 100% !important;
                border-collapse: collapse !important;
                margin-bottom: 3px !important;
                font-size: 6.5pt !important;
            }

            .schedule-meta-table td {
                border: 1px solid #000 !important;
                padding: 1.5px 4px !important;
                color: #000 !important;
            }

            .schedule-meta-table .lbl {
                background: #f1f5f9 !important;
                font-weight: 700 !important;
                color: #000 !important;
                width: 70px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Grid Matriks Jadwal Cetak */
            .op-schedule-grid-wrapper {
                max-height: none !important;
                overflow: visible !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                margin-top: 0 !important;
            }

            body #content-wrapper .op-schedule-grid {
                width: 100% !important;
                min-width: 0 !important;
                border-collapse: collapse !important;
                table-layout: fixed !important;
                border: 1px solid #000 !important;
            }

            body #content-wrapper .op-schedule-grid thead th {
                border: 1px solid #000 !important;
                color: #000 !important;
                background-color: #f1f5f9 !important;
                padding: 2px 1px !important;
                font-size: 6pt !important;
                vertical-align: middle !important;
                text-align: center !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body #content-wrapper .op-schedule-grid thead th.week-header {
                height: 18px !important;
                background-color: #e2e8f0 !important;
                font-size: 6.2pt !important;
                padding: 1px !important;
            }

            body #content-wrapper .op-schedule-grid thead th.day-header {
                height: 16px !important;
                font-size: 5.8pt !important;
                padding: 1px 0 !important;
            }

            body #content-wrapper .op-schedule-grid thead th.op-col,
            body #content-wrapper .op-schedule-grid tbody td.op-col {
                width: 17% !important;
                min-width: 0 !important;
                font-size: 6.5pt !important;
                padding: 1.5px 4px !important;
                border: 1px solid #000 !important;
                text-align: left !important;
                font-weight: 700 !important;
                word-break: break-word !important;
            }

            body #content-wrapper .op-schedule-grid thead th.bag-col,
            body #content-wrapper .op-schedule-grid tbody td.bag-col {
                width: 10.5% !important;
                min-width: 0 !important;
                font-size: 6pt !important;
                padding: 1.5px 2px !important;
                border: 1px solid #000 !important;
                text-align: center !important;
                word-break: break-word !important;
            }

            body #content-wrapper .op-schedule-grid thead th.status-col,
            body #content-wrapper .op-schedule-grid tbody td.status-col {
                width: 4.5% !important;
                min-width: 0 !important;
                font-size: 6pt !important;
                padding: 1px !important;
                border: 1px solid #000 !important;
                text-align: center !important;
                font-weight: 800 !important;
            }

            body #content-wrapper .op-schedule-grid thead th.day-header {
                min-width: 0 !important;
                padding: 1px 0 !important;
                font-size: 5.8pt !important;
                border: 1px solid #000 !important;
            }

            body #content-wrapper .op-schedule-grid tbody td {
                border: 1px solid #000 !important;
                color: #000 !important;
                height: 18px !important;
                padding: 1px 0 !important;
                font-size: 5.8pt !important;
                vertical-align: middle !important;
                text-align: center !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .badge-legend {
                padding: 0.5px 3px !important;
                font-size: 5.5pt !important;
                font-weight: 800 !important;
                border-radius: 2px !important;
                line-height: 1.1 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .marker-p {
                background-color: #10b981 !important;
                color: #fff !important;
            }

            .marker-a {
                background-color: #06b6d4 !important;
                color: #fff !important;
            }

            .day-sunday {
                background-color: #fef2f2 !important;
                color: #dc2626 !important;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            tbody tr.sch-op-row {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    </style>

        <!-- Unified Single Card Container (Satu Card Utuh persis Checksheet Kepatuhan Operator) -->
        <div class="card shadow mb-2">
            <div class="card-body p-2">

                <!-- 1. Header Dokumen ISO persis Checksheet Kepatuhan Operator -->
                <div class="mb-2">
                    <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;" class="header-iso-table">
                        <tr>
                            <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                                <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                            </td>
                            <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                                <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                    SCHEDULE KEPATUHAN OPERATOR
                                </h1>
                            </td>
                            <td style="width:1px; border:1px solid #dee2e6; padding:0 !important; vertical-align:top; white-space:nowrap;">

                                {{-- Wrapper baris — menyamakan tinggi kedua tabel anak --}}
                                <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                    <tr style="height:100%;">

                                        {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                                        <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                                            <table style="border-collapse:collapse; border:1px solid #dee2e6; font-size:0.65rem; background:#fff; height:100%; width:100%;">
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:700; color:#212529; white-space:nowrap;">
                                                        {{ $docHeader['no_dokumen'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                                                </tr>
                                            </table>
                                        </td>

                                        {{-- ===== [3B] Tabel Signatures ===== --}}
                                        <td style="border:none; padding:4px 4px 4px 0; vertical-align:top; height:100%;">
                                            <table style="border-collapse:collapse; border:1px solid #dee2e6; text-align:center; font-size:0.65rem; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:388px;">
                                                <thead>
                                                    <tr>
                                                        <th style="border:1px solid #dee2e6; padding:3px 2px; font-weight:600; color:#495057; background:#fff; width:28px;">Tgl.</th>
                                                        <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Dibuat</th>
                                                        <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diperiksa</th>
                                                        <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diketahui</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- BARIS 1: Gambar tanda tangan --}}
                                                    <tr>
                                                        <td rowspan="3" style="border:1px solid #dee2e6; padding:2px; vertical-align:middle; text-align:center; width:28px;">
                                                            <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:0.58rem; font-weight:400; margin:0 auto; color:#6c757d;">
                                                                {{ date('d-M-y') }}
                                                            </div>
                                                        </td>

                                                        {{-- Tanda tangan Dibuat --}}
                                                        <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                            @if(in_array(strtolower($plantCode ?? 'karawang'), ['jakarta', 'jkt']))
                                                                <img src="{{ asset('signatures/suli.png') }}" alt="Masuli"
                                                                     style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            @else
                                                                <img src="{{ asset('signatures/arif.png') }}" alt="Arief H"
                                                                     style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            @endif
                                                        </td>

                                                        {{-- Tanda tangan Diperiksa --}}
                                                        <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                            <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S"
                                                                 style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        </td>

                                                        {{-- Tanda tangan Diketahui --}}
                                                        <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                            <img src="{{ asset('signatures/desti.png') }}" alt="Desti K"
                                                                 style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        </td>
                                                    </tr>

                                                    {{-- BARIS 2: Nama --}}
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">{{ in_array(strtolower($plantCode ?? 'karawang'), ['jakarta', 'jkt']) ? 'Masuli' : 'Arief H' }}</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Iwan S</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Desti K</td>
                                                    </tr>

                                                    {{-- BARIS 3: Jabatan --}}
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Spv. QC</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>

                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </table>
                </div>

                <!-- 2. Metadata Info Schedule (Standar Dokumen Checksheet - Hanya Tampil Saat Cetak) -->
                <div class="mb-2 d-none d-print-block">
                    <table class="schedule-meta-table">
                        <tr>
                            <td class="lbl">Plant</td>
                            <td class="val">: <strong>{{ strtoupper($plantCode) }}</strong></td>
                            <td class="lbl">Periode</td>
                            <td class="val">: <strong>{{ $monthNames[$month] }} {{ $year }}</strong></td>
                            <td class="lbl">Shift</td>
                            <td class="val">: <strong>{{ $shiftFilter ?: 'Semua Shift' }}</strong></td>
                            <td class="lbl">Keterangan</td>
                            <td class="val">
                                : <span class="badge-legend marker-p">P</span> <strong>Plan</strong> (Jadwal)
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <span class="badge-legend marker-a">A</span> <strong>Actual</strong> (Realisasi Checksheet)
                            </td>
                        </tr>
                    </table>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- 3. Bar Filter Data Schedule (Dropdown Operator, Bulan, & Tahun persis Checksheet) -->
                <form id="scheduleFilterForm" action="{{ route('checksheet.operator_compliance.schedule') }}" method="GET" 
                    class="d-flex flex-wrap align-items-end bg-white p-0 rounded mb-3 no-print" 
                    style="gap: 14px;">
                    
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <!-- Dropdown Pilihan Operator / Inspector -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700">Operator / Inspector</label>
                        <div style="width: 230px;" class="custom-filter-wrapper">
                            <select name="operator_id" id="scheduleFilterOperator" class="form-control form-control-sm filter-select-custom" onchange="this.form.submit()">
                                <option value="">Semua Operator / Inspector</option>
                                @foreach(($inspectors ?? []) as $insp)
                                    <option value="{{ $insp->id }}" data-name="{{ $insp->name }}" {{ ($selectedOperatorId ?? request('operator_id')) == $insp->id ? 'selected' : '' }}>
                                        {{ $insp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Shift -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700">Shift</label>
                        <div style="width: 105px;">
                            <select name="shift" class="form-control form-control-sm filter-select-custom" onchange="this.form.submit()">
                                <option value="" {{ empty($shiftFilter) ? 'selected' : '' }}>Semua</option>
                                <option value="Non Shift" {{ $shiftFilter === 'Non Shift' ? 'selected' : '' }}>Non Shift</option>
                                <option value="Shift 1" {{ $shiftFilter === 'Shift 1' ? 'selected' : '' }}>Shift 1</option>
                                <option value="Shift 2" {{ $shiftFilter === 'Shift 2' ? 'selected' : '' }}>Shift 2</option>
                                <option value="Shift 3" {{ $shiftFilter === 'Shift 3' ? 'selected' : '' }}>Shift 3</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Bulan -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700">Bulan</label>
                        <div style="width: 130px;">
                            <select name="month" class="form-control form-control-sm filter-select-custom" onchange="this.form.submit()">
                                @foreach($monthNames as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Tahun -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700">Tahun</label>
                        <div style="width: 95px;">
                            <select name="year" class="form-control form-control-sm filter-select-custom" onchange="this.form.submit()">
                                @php $cy = date('Y'); @endphp
                                @for($y = $cy - 2; $y <= $cy + 2; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Aksi di sebelah kanan (Cetak, Kembali ke Form Checksheet, & Legenda) -->
                    <div class="ml-auto d-flex align-items-center flex-wrap mt-auto" style="gap: 8px;">
                        <button type="button" id="btnCetakSchedule" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;" title="Cetak Schedule Kepatuhan">
                            <i class="fas fa-print fa-sm mr-1"></i> Cetak
                        </button>

                        @if(auth()->user()->role === 'admin')
                        <button type="button" id="btnSyncHistoricalSchedule" data-url="{{ route('checksheet.operator_compliance.schedule.sync_historical') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;" title="Sinkronkan entri riwayat checksheet ke Plan Schedule">
                            <i class="fas fa-sync-alt fa-sm mr-1"></i> Sinkron Riwayat
                        </button>
                        @endif

                        <a href="{{ route('checksheet.operator_compliance.index', ['plant' => $plantCode, 'month' => $month, 'year' => $year]) }}" 
                           class="btn btn-sm btn-secondary rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Form Checksheet
                        </a>
                        
                        <div class="d-flex align-items-center" style="gap: 8px; border-left: 1px solid #cbd5e1; padding-left: 10px;">
                            <div class="d-flex align-items-center" style="gap: 3px;">
                                <span class="badge-legend marker-p">P</span>
                                <span class="small text-muted font-weight-bold" style="font-size: 0.6rem;">PLAN</span>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 3px;">
                                <span class="badge-legend marker-a">A</span>
                                <span class="small text-muted font-weight-bold" style="font-size: 0.6rem;">ACTUAL</span>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- 4. Matriks Schedule Bulanan (Tanggal 1 - $daysInMonth) -->
                @php
                    $dayColPct = 68.0 / $daysInMonth;
                @endphp
                <div class="op-schedule-grid-wrapper">
                    <table class="table table-bordered op-schedule-grid mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle op-col text-center" style="width: 17%;">NAMA OPERATOR</th>
                                <th rowspan="2" class="align-middle bag-col text-center" style="width: 10.5%;">BAGIAN</th>
                                <th rowspan="2" class="align-middle status-col text-center" style="width: 4.5%;">PLAN /<br>ACTUAL</th>
                                @foreach($weeks as $wNum => $wRange)
                                    @if($wRange['start'] <= $daysInMonth)
                                        @php
                                            $wEnd = min($wRange['end'], $daysInMonth);
                                            $wSpan = ($wEnd - $wRange['start']) + 1;
                                        @endphp
                                        <th colspan="{{ $wSpan }}" class="week-header text-center">Minggu-{{ $wNum }}</th>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday(); @endphp
                                    <th class="day-header text-center {{ $isSunday ? 'day-sunday' : '' }}" style="width: {{ number_format($dayColPct, 4, '.', '') }}%;">
                                        {{ $d }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operatorRows as $opId => $data)
                                <!-- Baris Plan (P) -->
                                <tr class="sch-op-row" data-op-name="{{ strtolower($data['operator']->name ?? '') }}" data-bagian="{{ strtolower($data['bagian'] ?? '') }}">
                                    <td rowspan="2" class="op-col align-middle text-left font-weight-bold text-dark">
                                        {{ $data['operator']->name ?? 'Unknown' }}
                                    </td>
                                    <td rowspan="2" class="bag-col align-middle text-center text-muted">
                                        {{ $data['bagian'] }}
                                    </td>
                                    <td class="status-col text-center align-middle">
                                        <span class="badge-legend marker-p">P</span>
                                    </td>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday(); @endphp
                                        <td class="text-center align-middle {{ $isSunday ? 'day-sunday' : '' }}">
                                            @if(!empty($data['plans'][$d]))
                                                <span class="badge-legend marker-p" title="Plan: {{ $d }} {{ $monthNames[$month] }}">P</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                                <!-- Baris Actual (A) -->
                                <tr class="sch-op-row-sub">
                                    <td class="status-col text-center align-middle">
                                        <span class="badge-legend marker-a">A</span>
                                    </td>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday(); @endphp
                                        <td class="text-center align-middle {{ $isSunday ? 'day-sunday' : '' }}">
                                            @if(!empty($data['actuals'][$d]))
                                                <span class="badge-legend marker-a" title="Actual: {{ $d }} {{ $monthNames[$month] }}">A</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 3 + $daysInMonth }}" class="text-center py-4 text-muted font-italic">
                                        Belum ada jadwal Kepatuhan Operator yang diatur untuk periode {{ $monthNames[$month] }} {{ $year }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
<script src="{{ asset('js/checksheet/operator-compliance.js') }}?v={{ time() }}"></script>
@endpush
