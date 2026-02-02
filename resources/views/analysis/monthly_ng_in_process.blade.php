@extends('layouts.admin')

@section('title', 'Report In-Process')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <x-plant-header title="Report In-Process" :plant="$plant" />

        <!-- Date Filter -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter Tanggal</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('analysis.monthly_ng_in_process') }}" class="form-inline">
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
                    <a href="{{ route('analysis.monthly_ng_in_process', ['plant' => $plant]) }}"
                        class="btn btn-secondary mb-2 ml-2">Reset</a>
                </form>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">

            <!-- Area Chart (Trend) -->
            <div class="col-xl-6 col-lg-6">

                <!-- Trend Percentage Chart -->
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

                <!-- Trend Total NG -->
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

                <!-- Distribution Chart (Swapped Position: Top) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Jenis NG (%)</h6>
                    </div>
                    <div class="card-body">
                        <div id="ngPercentageChartContainer" style="height: 370px; width: 100%;"></div>
                    </div>
                </div>

                <!-- Detail Variants Chart (Swapped Position: Bottom) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Jenis NG (pcs)</h6>
                    </div>
                    <div class="card-body">
                        <div id="ngCountChartContainer" style="height: 370px; width: 100%;"></div>
                    </div>
                </div>

                <!-- Detail Cycle Time per Item Chart -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Standard Cycle Time per Item (s)</h6>
                    </div>
                    <div class="card-body">
                        <div id="cycleTimeChartContainer" style="height: 370px; width: 100%;"></div>
                    </div>
                </div>

                <!-- Detail Cycle Time per User Chart -->
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

    <!-- Page level plugins -->
    <script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/chartjs-plugin-datalabels.min.js') }}"></script>
    <script src="{{ asset('js/vendor/canvasjs.min.js') }}"></script>

    <script>
        // Set new default font family and font color to mimic Bootstrap's default styling
        Chart.defaults.font.family = 'Nunito, -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.color = '#858796';

        // Register DataLabels plugin
        Chart.register(ChartDataLabels);

        document.addEventListener("DOMContentLoaded", function () {
            // --- Area Chart (Trend) ---
            var ctx = document.getElementById("myAreaChart");
            var labels = @json($labels);
            var dataValues = @json($data);
            var dataPercentage = @json($dataPercentage);
            var dataCycleTime = @json($dataCycleTime);
            var itemLabels = @json($itemLabels);
            var itemCycleTimeData = @json($itemCycleTimeData);

            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Total NG",
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: dataValues,
                        datalabels: {
                            align: 'end',
                            anchor: 'end',
                            color: '#4e73df',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 12
                            }
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10
                            },
                            grid: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        },
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleMarginBottom: 10,
                            titleColor: '#6e707e',
                            titleFont: {
                                size: 14,
                            },
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10,
                        }
                    }
                }
            });

            // --- CanvasJS Work Speed Chart (Kecepatan Kerja) ---
            var inspectorItemLabels = @json($inspectorItemLabels);
            var inspectorItemDatasets = @json($inspectorItemDatasets);
            var itemCycleTimeDataForCalc = @json($itemCycleTimeData);

            // Transform data for CanvasJS
            var canvasJSDataPoints = [];

            // Process each item (y-axis labels)
            inspectorItemLabels.forEach(function (itemName, itemIndex) {
                var itemData = {
                    label: itemName,
                    operators: []
                };

                // For each operator/inspector dataset
                inspectorItemDatasets.forEach(function (dataset) {
                    var actualCycleTime = dataset.data[itemIndex];
                    var standardCycleTime = itemCycleTimeDataForCalc[itemIndex];
                    var percentage = 0;

                    if (standardCycleTime > 0 && actualCycleTime > 0) {
                        percentage = (actualCycleTime / standardCycleTime) * 100;
                    }

                    if (percentage > 0) {
                        itemData.operators.push({
                            name: dataset.label,
                            percentage: percentage,
                            actualTime: actualCycleTime,
                            standardTime: standardCycleTime,
                            color: dataset.backgroundColor
                        });
                    }
                });

                if (itemData.operators.length > 0) {
                    canvasJSDataPoints.push(itemData);
                }
            });

            // Create CanvasJS chart
            var workSpeedChart = new CanvasJS.Chart("workSpeedChartContainer", {
                theme: "light1", // White theme
                animationEnabled: true,
                exportEnabled: true,
                title: {
                    text: "Kecepatan Kerja per Item (%)",
                    fontSize: 16,
                    fontFamily: "Nunito, sans-serif"
                },
                axisX: {
                    title: "Item",
                    titleFontSize: 14,
                    labelFontSize: 10,
                    labelMaxWidth: 150,
                    labelWrap: true,
                    interval: 1,
                    gridThickness: 0
                },
                axisY: {
                    title: "Persentase (%)",
                    titleFontSize: 14,
                    labelFontSize: 12,
                    suffix: "%",
                    maximum: 200,
                    gridThickness: 1,
                    gridColor: "#f0f0f0"
                },
                toolTip: {
                    shared: true,
                    content: function (e) {
                        var content = "<strong>" + e.entries[0].dataPoint.label + "</strong><br/>";
                        e.entries.forEach(function (entry) {
                            content += "<span style='color:" + entry.dataSeries.color + "'>" +
                                entry.dataSeries.name + "</span>: " +
                                entry.dataPoint.y.toFixed(1) + "%<br/>";
                        });
                        return content;
                    }
                },
                legend: {
                    cursor: "pointer",
                    fontSize: 12,
                    verticalAlign: "top",
                    horizontalAlign: "center"
                },
                data: []
            });

            // Add data series for each operator
            var operatorMap = {};
            canvasJSDataPoints.forEach(function (item) {
                item.operators.forEach(function (op) {
                    if (!operatorMap[op.name]) {
                        operatorMap[op.name] = {
                            type: "bar",
                            name: op.name,
                            showInLegend: true,
                            color: op.color,
                            dataPoints: []
                        };
                    }

                    operatorMap[op.name].dataPoints.push({
                        label: item.label,
                        y: parseFloat(op.percentage.toFixed(1)),
                        indexLabel: "{y}%",
                        indexLabelFontSize: 10,
                        indexLabelFontColor: "#444"
                    });
                });
            });

            // Add all operator series to chart
            Object.values(operatorMap).forEach(function (series) {
                workSpeedChart.options.data.push(series);
            });

            workSpeedChart.render();

            // --- CanvasJS Standard Cycle Time Chart ---
            var sortedItemTotalPcs = @json($sortedItemTotalPcs);
            var sortedItemTotalSeconds = @json($sortedItemTotalSeconds);

            var cycleTimeChart = new CanvasJS.Chart("cycleTimeChartContainer", {
                theme: "light1",
                animationEnabled: true,
                exportEnabled: true,
                title: {
                    text: "Standard Cycle Time per Item",
                    fontSize: 16,
                    fontFamily: "Nunito, sans-serif"
                },
                axisX: {
                    title: "Item",
                    titleFontSize: 14,
                    labelFontSize: 10,
                    labelMaxWidth: 150,
                    labelWrap: true,
                    interval: 1,
                    gridThickness: 0
                },
                axisY: {
                    title: "Waktu (detik)",
                    titleFontSize: 14,
                    labelFontSize: 12,
                    suffix: "s",
                    maximum: 30,
                    gridThickness: 1,
                    gridColor: "#f0f0f0"
                },
                toolTip: {
                    shared: false,
                    content: function (e) {
                        var idx = e.dataPoint.index;
                        var pcs = sortedItemTotalPcs[idx];
                        var secs = sortedItemTotalSeconds[idx];
                        return "<strong>" + e.dataPoint.label + "</strong><br/>" +
                            "Standar: " + e.dataPoint.y + " s/pcs<br/>" +
                            "Total Pcs: " + pcs + "<br/>" +
                            "Total Detik: " + secs;
                    }
                },
                data: [{
                    type: "bar",
                    color: "#6f42c1",
                    dataPoints: itemLabels.map(function (label, idx) {
                        return {
                            label: label,
                            y: itemCycleTimeData[idx],
                            index: idx,
                            indexLabel: "{y} s",
                            indexLabelFontSize: 10,
                            indexLabelFontColor: "#444"
                        };
                    })
                }]
            });
            cycleTimeChart.render();

            // --- Percentage Chart (Trend) ---
            var ctxPerc = document.getElementById("myPercentageChart");

            var myPercentageChart = new Chart(ctxPerc, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Persentase NG (%)",
                        lineTension: 0.3,
                        backgroundColor: "rgba(28, 200, 138, 0.05)",
                        borderColor: "rgba(28, 200, 138, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(28, 200, 138, 1)",
                        pointBorderColor: "rgba(28, 200, 138, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                        pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: dataPercentage,
                        datalabels: {
                            align: 'end',
                            anchor: 'end',
                            color: '#1cc88a',
                            font: {
                                weight: 'bold'
                            },
                            formatter: function (value, ctx) {
                                return value + "%";
                            }
                        }
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 12
                            }
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                callback: function (value, index, values) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        },
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleMarginBottom: 10,
                            titleColor: '#6e707e',
                            titleFont: {
                                size: 14,
                            },
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10,
                            callbacks: {
                                label: function (tooltipItem) {
                                    return tooltipItem.dataset.label + ': ' + tooltipItem.raw + '%';
                                }
                            }
                        }
                    }
                }
            });

            // --- CanvasJS Jenis NG (pcs) Chart ---
            var defectLabels = @json($defectLabels);
            var defectData = @json($defectData);

            var ngCountChart = new CanvasJS.Chart("ngCountChartContainer", {
                theme: "light1",
                animationEnabled: true,
                exportEnabled: true,
                title: {
                    text: "Jenis NG (pcs)",
                    fontSize: 16,
                    fontFamily: "Nunito, sans-serif"
                },
                axisX: {
                    title: "Jenis NG",
                    titleFontSize: 14,
                    labelFontSize: 10,
                    labelMaxWidth: 150,
                    labelWrap: true,
                    interval: 1,
                    gridThickness: 0
                },
                axisY: {
                    title: "Jumlah (pcs)",
                    titleFontSize: 14,
                    labelFontSize: 12,
                    gridThickness: 1,
                    gridColor: "#f0f0f0"
                },
                toolTip: {
                    shared: false,
                    content: "<strong>{label}</strong><br/>Jumlah: {y} pcs"
                },
                data: [{
                    type: "bar",
                    color: "#e74a3b",
                    dataPoints: defectLabels.map(function (label, idx) {
                        return {
                            label: label,
                            y: defectData[idx],
                            indexLabel: "{y} pcs",
                            indexLabelFontSize: 10,
                            indexLabelFontColor: "#444"
                        };
                    })
                }]
            });
            ngCountChart.render();

            // --- CanvasJS Jenis NG (%) Chart ---
            var ngPercentageChart = new CanvasJS.Chart("ngPercentageChartContainer", {
                theme: "light1",
                animationEnabled: true,
                exportEnabled: true,
                title: {
                    text: "Jenis NG (%)",
                    fontSize: 16,
                    fontFamily: "Nunito, sans-serif"
                },
                axisX: {
                    title: "Jenis NG",
                    titleFontSize: 14,
                    labelFontSize: 10,
                    labelMaxWidth: 150,
                    labelWrap: true,
                    interval: 1,
                    gridThickness: 0
                },
                axisY: {
                    title: "Persentase (%)",
                    titleFontSize: 14,
                    labelFontSize: 12,
                    suffix: "%",
                    gridThickness: 1,
                    gridColor: "#f0f0f0"
                },
                toolTip: {
                    shared: false,
                    content: function (e) {
                        var sum = defectData.reduce(function (a, b) { return a + b; }, 0);
                        var percentage = (e.dataPoint.y * 100 / sum).toFixed(1);
                        return "<strong>" + e.dataPoint.label + "</strong><br/>" +
                            "Persentase: " + percentage + "%<br/>" +
                            "Jumlah: " + e.dataPoint.y + " pcs";
                    }
                },
                data: [{
                    type: "bar",
                    color: "#36b9cc",
                    dataPoints: defectLabels.map(function (label, idx) {
                        var sum = defectData.reduce(function (a, b) { return a + b; }, 0);
                        var percentage = (defectData[idx] * 100 / sum).toFixed(1);
                        return {
                            label: label,
                            y: defectData[idx],
                            indexLabel: percentage + "%",
                            indexLabelFontSize: 10,
                            indexLabelFontColor: "#444"
                        };
                    })
                }]
            });
            ngPercentageChart.render();
        });
    </script>
@endsection