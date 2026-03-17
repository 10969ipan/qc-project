@extends('layouts.admin')

@section('title', 'Report Sub Assy')

@section('content')
    <div class="container-fluid">

        <x-plant-header title="Report Sub Assy" :plant="$plant" />

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter Tanggal</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('analysis.monthly_ng') }}" class="form-inline">
                    <div class="form-group mb-2">
                        <label for="start_date" class="mr-2">Mulai Tanggal:</label>
                        <input type="date" class="form-control mr-4" id="start_date" name="start_date"
                            value="{{ request('start_date') }}">
                    </div>
                    <div class="form-group mb-2">
                        <label for="end_date" class="mr-2">Sampai Tanggal:</label>
                        <input type="date" class="form-control mr-4" id="end_date" name="end_date"
                            value="{{ request('end_date') }}">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Cari</button>
                    <a href="{{ route('analysis.monthly_ng', ['plant' => $plant]) }}"
                        class="btn btn-secondary mb-2 ml-2">Reset</a>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Total NG (%)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myPercentageChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Total NG (pcs)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>



            </div>

            <div class="col-xl-6 col-lg-6">

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Jenis NG (%)</h6>
                    </div>
                    <div class="card-body">
                        <div id="ngPercentageChartContainer" style="height: 370px; width: 100%;"></div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Jenis NG (pcs)</h6>
                    </div>
                    <div class="card-body">
                        <div id="ngCountChartContainer" style="height: 370px; width: 100%;"></div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Standard Cycle Time per Item (s)</h6>
                    </div>
                    <div class="card-body">
                        <div id="cycleTimeChartContainer" style="height: 370px; width: 100%;"></div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Kecepatan Kerja (%)</h6>
                    </div>
                    <div class="card-body">
                        <div id="workSpeedChartContainer" style="height: 450px; width: 100%;"></div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/chartjs-plugin-datalabels.min.js') }}"></script>
    <script src="{{ asset('js/vendor/canvasjs.min.js') }}"></script>

    <script>
        window.__ANALYSIS__ = {
            labels: @json($labels),
            dataValues: @json($data),
            dataPercentage: @json($dataPercentage),
            dataCycleTime: @json($dataCycleTime),
            itemLabels: @json($itemLabels),
            itemCycleTimeData: @json($itemCycleTimeData),
            inspectorItemLabels: @json($inspectorItemLabels),
            inspectorItemDatasets: @json($inspectorItemDatasets),
            sortedItemTotalPcs: @json($sortedItemTotalPcs),
            sortedItemTotalSeconds: @json($sortedItemTotalSeconds),
            defectLabels: @json($defectLabels),
            defectData: @json($defectData),
            maxCycleTimeY: 30
        };
    </script>
    <script src="{{ asset('js/analysis/analysis-charts.js') }}"></script>
@endsection
