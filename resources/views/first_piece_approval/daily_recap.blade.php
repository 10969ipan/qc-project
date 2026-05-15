@extends('layouts.admin')

@section('title', 'Rekap Harian FPA — Distribusi Jam')

@section('content')
<style>
    /* ─── Base Table Styles ─── */
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
    }
    #hourlyTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }
    #hourlyTable td,
    #hourlyTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    #hourlyTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        color: #000 !important;
        font-size: 0.78rem !important;
        padding: 7px 12px !important;
    }
    #hourlyTable thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        background-color: #f8fafc !important;
        color: #000 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.70rem !important;
        letter-spacing: 0.2px;
        padding: 10px 12px !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .custom-filter-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    /* ─── Summary Cards ─── */
    .summary-card {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07) !important;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 18px rgba(0,0,0,0.12) !important;
    }
    .summary-card .card-body { padding: 1.2rem 1.4rem; }
    .summary-card .icon-circle {
        width: 46px; height: 46px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
    }
    .summary-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.1;
        color: #1e293b;
    }
    .summary-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-top: 2px;
    }
    /* ─── Peak Badge ─── */
    .badge-peak {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: linear-gradient(135deg, #f59e0b, #ef4444);
        color: #fff;
        font-size: 0.65rem;
        padding: 3px 9px;
        border-radius: 30px;
        font-weight: 700;
        letter-spacing: 0.5px;
        white-space: nowrap;
        line-height: 1.4;
    }
    /* ─── Row highlight for peak hours ─── */
    tr.is-peak td {
        background-color: #fffbeb !important;
    }
    /* ─── Chart container ─── */
    .chart-wrapper {
        position: relative;
        height: 300px;
    }

    /* ─── Print Styles ─── */
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        body { font-size: 9px; color: #333; background: #fff !important; }
        .navbar, .topbar, .sidebar, .footer, .btn, .no-print,
        .custom-filter-card, .d-flex.align-items-center.justify-content-between.mb-3 {
            display: none !important;
        }
        #content-wrapper { margin: 0 !important; padding: 0 !important; }
        .container-fluid { padding: 0 !important; width: 100% !important; }
        .card { border: none !important; box-shadow: none !important; margin-bottom: 6px !important; }
        .card-body { padding: 0 !important; }
        .summary-card { box-shadow: none !important; border: 1px solid #ccc !important; }
        /* Hapus max-height saat print agar semua 24 baris muncul */
        .table-responsive {
            max-height: none !important;
            overflow: visible !important;
        }
        #hourlyTable {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        #hourlyTable thead th {
            border: 1px solid #000 !important;
            background-color: #f2f2f2 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 8px !important;
            padding: 4px 6px !important;
            position: static !important;
        }
        #hourlyTable tbody td {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
            font-size: 9px !important;
            color: #000 !important;
        }
        #hourlyTable tfoot td {
            border: 1px solid #000 !important;
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 9px !important;
            font-weight: 700 !important;
        }
        tr.is-peak td { background-color: #fff9e6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-peak { background: #f59e0b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .chart-section { display: none !important; }
        a[href]:after { content: none !important; }
        /* Progress bar — tampilkan sebagai teks saja saat print */
        .progress-bar-wrap { display: none !important; }
        .pct-text-only { display: inline !important; }
    }
</style>

<div class="container-fluid">

    {{-- ── Page Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 no-print">
        <div>
            <h1 class="h4 mb-0 text-gray-800 font-weight-bold">REKAP HARIAN FPA</h1>
            <p class="text-muted small mb-0">DISTRIBUSI FPA SELAMA 24 Jam</p>
        </div>
        <div class="d-flex" style="gap:8px;">
            <button onclick="window.print()" class="btn btn-sm btn-dark shadow-sm rounded-pill px-3">
                <i class="fas fa-print fa-sm mr-1"></i> Print
            </button>
            <a href="{{ route('first_piece_approval.index') }}" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3">
                <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- ── Print Header ── --}}
    <div class="d-none d-print-block mb-3">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:100px; border:1px solid #000; padding:6px; text-align:center;">
                    <img src="{{ asset('master item/ipp.jpg') }}" style="max-width:90px; max-height:55px; object-fit:contain;">
                </td>
                <td style="border:1px solid #000; padding:6px; text-align:center; font-size:14px; font-weight:bold;">
                    REKAP HARIAN FPA — DISTRIBUSI BEBAN JAM
                </td>
            </tr>
        </table>
        <div style="margin-top:6px; font-size:10px;">
            <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            &nbsp;|&nbsp;
            <strong>Plant:</strong> {{ strtoupper($plantName) }}
        </div>
    </div>

    {{-- ── Filter Card ── --}}
    <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden custom-filter-card no-print">
        <div class="card-body py-2">
            <form action="{{ route('first_piece_approval.daily_recap') }}" method="GET" class="row align-items-center">

                @if(request('plant'))
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                @endif

                <div class="col-md-auto mb-2 mb-md-0 d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Tanggal:</label>
                    <input type="date" name="date" class="form-control form-control-sm shadow-sm"
                           value="{{ $date }}" style="width:145px;">
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-4">
                        <i class="fas fa-search fa-sm mr-1"></i> Tampilkan
                    </button>
                    <a href="{{ route('first_piece_approval.daily_recap') }}" class="btn btn-light btn-sm shadow-sm rounded-pill px-3 border ml-1">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                </div>

                <div class="col text-md-right mt-2 mt-md-0">
                    <span class="badge badge-white border px-3 py-2 rounded-pill shadow-sm">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i>
                        <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong>
                    </span>
                    <span class="badge badge-white border px-3 py-2 rounded-pill shadow-sm ml-2">
                        <i class="fas fa-industry mr-1 text-primary"></i>
                        <strong>{{ strtoupper($plantName) }}</strong>
                    </span>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row mb-4 no-print">
        {{-- Total FPA --}}
        <div class="col-md-4 mb-3">
            <div class="card summary-card h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div class="icon-circle" style="background:linear-gradient(135deg,#3b82f6,#6366f1); color:#fff;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="summary-value">{{ number_format($recap['total']) }}</div>
                        <div class="summary-label">Total FPA Hari Ini</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Peak Hour --}}
        <div class="col-md-4 mb-3">
            <div class="card summary-card h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div class="icon-circle" style="background:linear-gradient(135deg,#f59e0b,#ef4444); color:#fff;">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div>
                        <div class="summary-value" style="font-size:1.3rem;">
                            @if($peakLabel)
                                {{ $peakLabel }}
                            @else
                                <span class="text-muted" style="font-size:1rem;">—</span>
                            @endif
                        </div>
                        <div class="summary-label">Peak Hour (Tersibuk)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Avg Cycle Time --}}
        <div class="col-md-4 mb-3">
            <div class="card summary-card h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div class="icon-circle" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff;">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <div>
                        @if($overallAvgCt !== null)
                            <div class="summary-value">{{ gmdate('i:s', $overallAvgCt) }}</div>
                            <div class="summary-label">Rata-rata Cycle Time (mm:ss)</div>
                        @else
                            <div class="summary-value" style="font-size:1rem; color:#94a3b8;">—</div>
                            <div class="summary-label">Rata-rata Cycle Time</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Bar Chart ── --}}
    <div class="card shadow mb-4 border-0 rounded-lg chart-section no-print">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-dark">
                GRAFIK FPA DALAM SATU HARI
            </h6>
            <small class="text-muted">
                <span class="badge-peak mr-1">PEAK</span> = Jam tersibuk
            </small>
        </div>
        <div class="card-body">
            <div class="chart-wrapper">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Hourly Table ── --}}
    <div class="card shadow mb-4 border-0 rounded-lg overflow-hidden">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-dark">
                FPA DALAM SATU HARI
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="hourlyTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">No</th>
                            <th class="text-center" style="width:120px;">Jam</th>
                            <th class="text-center" style="width:90px;">Jumlah FPA</th>
                            <th class="text-center" style="width:160px;">Persentase (%)</th>
                            <th class="text-center" style="width:110px;">Avg Cycle Time</th>
                            <th class="text-center" style="width:180px; max-width:180px;">Inspector</th>
                            <th class="text-center" style="width:80px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recap['distribution'] as $slot)
                            <tr class="{{ $slot['is_peak'] && $slot['count'] > 0 ? 'is-peak' : '' }}">
                                <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                <td class="text-center font-weight-bold">
                                    {{ sprintf('%02d', $slot['hour']) }}:00
                                    &ndash;
                                    {{ sprintf('%02d', ($slot['hour'] + 1) % 24) }}:00
                                </td>
                                <td class="text-center font-weight-bold">
                                    @if($slot['count'] > 0)
                                        {{ $slot['count'] }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($slot['count'] > 0)
                                        {{-- Progress bar (layar) + teks saja (print) --}}
                                        <span class="pct-text-only" style="display:none; font-weight:700;">{{ number_format($slot['percentage'], 1) }}%</span>
                                        <div class="progress-bar-wrap d-flex align-items-center justify-content-center" style="gap:8px;">
                                            <div style="width:80px; background:#e2e8f0; border-radius:20px; height:8px; overflow:hidden;">
                                                <div style="width:{{ $slot['percentage'] }}%; height:100%; background:{{ $slot['is_peak'] ? 'linear-gradient(90deg,#f59e0b,#ef4444)' : 'linear-gradient(90deg,#3b82f6,#6366f1)' }}; border-radius:20px;"></div>
                                            </div>
                                            <span class="font-weight-bold" style="min-width:38px;">{{ number_format($slot['percentage'], 1) }}%</span>
                                        </div>
                                    @else
                                        <span class="text-muted">0%</span>
                                    @endif
                                </td>
                                <td class="text-center small text-muted">
                                    @if($slot['avg_cycle_time_seconds'] !== null && $slot['count'] > 0)
                                        {{ gmdate('i:s', (int)$slot['avg_cycle_time_seconds']) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center small" style="max-width:180px; word-break:break-word; text-transform:uppercase;">
                                    @if($slot['inspectors'])
                                        {{ $slot['inspectors'] }}
                                    @else
                                        <span class="text-muted" style="text-transform:none;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($slot['is_peak'] && $slot['count'] > 0)
                                        <span class="badge-peak">🔥 PEAK</span>
                                    @elseif($slot['count'] > 0)
                                        <span class="badge badge-light text-muted border" style="font-size:0.65rem;">Normal</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc; font-weight:700; border-top:2px solid #e2e8f0;">
                            <td colspan="2" class="text-right small text-muted pr-3">TOTAL</td>
                            <td class="text-center">{{ number_format($recap['total']) }}</td>
                            <td class="text-center">100%</td>
                            <td class="text-center small text-muted">
                                @if($overallAvgCt !== null)
                                    {{ gmdate('i:s', $overallAvgCt) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Data from PHP ──
    const distribution = @json($recap['distribution']);
    const maxCount = {{ $recap['max_count'] }};

    const labels     = distribution.map(s => `${String(s.hour).padStart(2,'0')}:00`);
    const counts     = distribution.map(s => s.count);
    const percentages = distribution.map(s => s.percentage);
    const isPeak     = distribution.map(s => s.is_peak && s.count > 0);

    // Colors: peak = orange-red gradient, normal = blue-indigo gradient
    const barColors = isPeak.map(p =>
        p ? 'rgba(245, 158, 11, 0.85)' : 'rgba(99, 102, 241, 0.75)'
    );
    const borderColors = isPeak.map(p =>
        p ? 'rgba(239, 68, 68, 1)' : 'rgba(59, 130, 246, 1)'
    );

    const ctx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Persentase (%)',
                    data: percentages,
                    backgroundColor: barColors,
                    borderColor: borderColors,
                    borderWidth: 1.5,
                    borderRadius: 5,
                    yAxisID: 'yPercent',
                    order: 1,
                },
                {
                    label: 'Jumlah FPA',
                    data: counts,
                    type: 'line',
                    borderColor: 'rgba(16, 185, 129, 0.9)',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yCount',
                    order: 0,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: { size: 11 }, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        title: function(items) {
                            const h = parseInt(items[0].label);
                            const hNext = (h + 1) % 24;
                            return `${String(h).padStart(2,'0')}:00 – ${String(hNext).padStart(2,'0')}:00`;
                        },
                        afterLabel: function(item) {
                            const s = distribution[item.dataIndex];
                            if (s.is_peak && s.count > 0) return '🔥 PEAK HOUR';
                            return '';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 0 }
                },
                yPercent: {
                    position: 'left',
                    title: { display: true, text: 'Persentase (%)', font: { size: 11 } },
                    min: 0,
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    ticks: {
                        callback: v => v + '%',
                        font: { size: 10 }
                    }
                },
                yCount: {
                    position: 'right',
                    title: { display: true, text: 'Jumlah FPA', font: { size: 11 } },
                    min: 0,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 }
                    }
                }
            }
        }
    });
});
</script>
@endpush
