@extends('layouts.admin')

@section('title', 'Report Cross Cut')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Report Cross Cut</h1>

        <!-- Date Filter -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter Tanggal</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('analysis.monthly_ng_cross_cut') }}" class="form-inline">
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
                    <a href="{{ route('analysis.monthly_ng_cross_cut') }}" class="btn btn-secondary mb-2 ml-2">Reset</a>
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
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="myPieChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="mr-2">
                                <i class="fas fa-circle text-info"></i> Persentase Status (%)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detail Variants Chart (Swapped Position: Bottom) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Jenis NG (pcs)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar">
                            <canvas id="myBarChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detail Cycle Time per Item Chart -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Standard Cycle Time per Item (s)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar">
                            <canvas id="myItemCycleChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detail Cycle Time per User Chart -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Kecepatan Kerja (%)</h6>
                        <div class="small text-muted">
                            <i class="fas fa-info-circle"></i> Persentase terhadap standar cycle time
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 700px; overflow-y: auto; overflow-x: hidden;">
                            <div id="inspectorChartContainer" style="position: relative; min-height: 400px;">
                                <canvas id="myInspectorItemCycleChart"></canvas>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <small class="text-muted">
                                        <span class="badge badge-success">●</span> ≥ 100% (Sesuai/Lebih Cepat)
                                    </small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <small class="text-muted">
                                        <span class="badge badge-warning">●</span> 80-99% (Perlu Perhatian)
                                    </small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <small class="text-muted">
                                        <span class="badge badge-danger">●</span>
                                        < 80% (Di Bawah Standar) </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Page level plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

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
            });

            // --- Improved Bar Chart (Avg Cycle Time per Item by User) ---
            var ctxInspectorItemCycle = document.getElementById("myInspectorItemCycleChart");
            var inspectorItemLabels = @json($inspectorItemLabels);
            var inspectorItemDatasets = @json($inspectorItemDatasets);
            var itemCycleTimeDataForCalc = @json($itemCycleTimeData);

            // Debug logging
            console.log('Inspector Item Labels:', inspectorItemLabels);
            console.log('Inspector Item Datasets:', inspectorItemDatasets);
            console.log('Item Cycle Time Data:', itemCycleTimeDataForCalc);
            console.log('Canvas Element:', ctxInspectorItemCycle);

            // Check if data exists
            if (!inspectorItemLabels || inspectorItemLabels.length === 0) {
                console.warn('No inspector item labels data available');
                document.getElementById('inspectorChartContainer').innerHTML = 
                    '<div class="alert alert-info text-center">Tidak ada data untuk ditampilkan. Silakan pilih range tanggal atau tambahkan data checksheet terlebih dahulu.</div>';
                // Skip chart initialization
            } else {
                console.log('Data count:', inspectorItemLabels.length);
                
                // Calculate dynamic height based on number of data points
                var dataCount = inspectorItemLabels.length;
                var barHeight = 50; // Increased height per bar for better visibility
                var minHeight = 400;
                var calculatedHeight = Math.max(minHeight, dataCount * barHeight);
                
                // Set container height dynamically
                document.getElementById('inspectorChartContainer').style.height = calculatedHeight + 'px';

                // Color-code datasets based on performance
            inspectorItemDatasets.forEach(dataset => {
                // Create color array for each data point
                dataset.backgroundColor = dataset.data.map((value, idx) => {
                    var std = itemCycleTimeDataForCalc[idx];
                    var pct = std > 0 ? (value / std) * 100 : 0;
                    if (pct >= 100) return '#28a745'; // Green
                    if (pct >= 80) return '#ffc107'; // Yellow
                    return '#dc3545'; // Red
                });
                
                // Configure data labels with larger font
                dataset.datalabels = {
                    color: '#fff',
                    font: { 
                        weight: 'bold', 
                        size: 14 // Larger font for better visibility
                    },
                    anchor: 'center',
                    align: 'center',
                    formatter: function (value, ctx) {
                        if (value > 0) {
                            var idx = ctx.dataIndex;
                            var std = itemCycleTimeDataForCalc[idx];
                            var pct = 0;
                            if (std > 0) {
                                pct = (value / std) * 100;
                            }
                            return pct.toFixed(1) + "%";
                        }
                        return "";
                    }
                };
            });

            var myInspectorItemCycleChart = new Chart(ctxInspectorItemCycle, {
                type: 'bar',
                data: {
                    labels: inspectorItemLabels,
                    datasets: inspectorItemDatasets
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Horizontal bars
                    layout: {
                        padding: {
                            left: 10,
                            right: 30,
                            top: 25,
                            bottom: 10
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                maxTicksLimit: 8,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            ticks: {
                                padding: 10,
                                autoSkip: false,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
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
                            displayColors: true,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10,
                            callbacks: {
                                label: function (tooltipItem) {
                                    var std = itemCycleTimeDataForCalc[tooltipItem.dataIndex];
                                    var pct = 0;
                                    if (std > 0) {
                                        pct = (tooltipItem.raw / std) * 100;
                                    }
                                    var status = pct >= 100 ? '✓ Sesuai' : (pct >= 80 ? '⚠ Perlu Perhatian' : '✗ Di Bawah Standar');
                                    return [
                                        tooltipItem.dataset.label + ': ' + pct.toFixed(1) + '%',
                                        status,
                                        'Standar: ' + std + 's'
                                    ];
                                }
                            }
                        }
                    }
                }
            });
            } // End of data check if-else

            // --- Bar Chart (Avg Cycle Time per Item) ---
            var ctxItemCycle = document.getElementById("myItemCycleChart");
            var sortedItemTotalSeconds = @json($sortedItemTotalSeconds);

            var myItemCycleChart = new Chart(ctxItemCycle, {
                type: 'bar',
                data: {
                    labels: itemLabels,
                    datasets: [{
                        label: "Avg Cycle Time (s)",
                        backgroundColor: "#6f42c1", // Purple
                        hoverBackgroundColor: "#59359a",
                        borderColor: "#6f42c1",
                        data: itemCycleTimeData,
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold'
                            },
                            anchor: 'center',
                            align: 'center',
                            formatter: function (value, ctx) {
                                return value + "s";
                            }
                        }
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Horizontal
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
                                maxTicksLimit: 6
                            }
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 20,
                                padding: 10,
                                autoSkip: false
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
                                    var idx = tooltipItem.dataIndex;
                                    var pcs = sortedItemTotalPcs[idx];
                                    var secs = sortedItemTotalSeconds[idx];
                                    return [
                                        tooltipItem.dataset.label + ': ' + tooltipItem.raw + 's',
                                        'Standar: ' + tooltipItem.raw + ' s/pcs',
                                        'Total Pcs: ' + pcs,
                                        'Total Detik: ' + secs
                                    ];
                                }
                            }
                        }
                    }
                }
            });

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

            // --- Bar Chart (Status) ---
            var ctxBar = document.getElementById("myBarChart");
            var defectLabels = @json($defectLabels);
            var defectData = @json($defectData);

            var myBarChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: defectLabels,
                    datasets: [{
                        label: "Jumlah",
                        backgroundColor: "#e74a3b", // Danger red
                        hoverBackgroundColor: "#be2617",
                        borderColor: "#e74a3b",
                        data: defectData,
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold'
                            },
                            anchor: 'center',
                            align: 'center'
                        }
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y',
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
                                maxTicksLimit: 6
                            }
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 10,
                                padding: 10,
                                autoSkip: false
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

            // --- Vertical Bar Chart (Percentage Distribution) ---
            var ctxDist = document.getElementById("myPieChart");

            var myDistChart = new Chart(ctxDist, {
                type: 'bar',
                data: {
                    labels: defectLabels,
                    datasets: [{
                        label: "Persentase NG (%)",
                        data: defectData,
                        backgroundColor: "#36b9cc", // Cyan
                        hoverBackgroundColor: "#2c9faf",
                        borderColor: "#36b9cc",
                        datalabels: {
                            color: '#444',
                            font: {
                                weight: 'bold'
                            },
                            anchor: 'end',
                            align: 'end',
                            formatter: function (value, ctx) {
                                let sum = 0;
                                let dataArr = ctx.chart.data.datasets[0].data;
                                dataArr.map(data => {
                                    sum += data;
                                });
                                let percentage = (value * 100 / sum).toFixed(1) + "%";
                                return percentage;
                            }
                        }
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y',
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
                                maxTicksLimit: 6,
                                callback: function (value, index, values) {
                                    return value;
                                }
                            }
                        },
                        y: {
                            ticks: {
                                padding: 10,
                                autoSkip: false
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
                                    let sum = 0;
                                    let dataArr = tooltipItem.chart.data.datasets[0].data;
                                    dataArr.forEach(data => sum += data);
                                    let percentage = (tooltipItem.raw * 100 / sum).toFixed(1) + "%";
                                    return tooltipItem.dataset.label + ': ' + percentage;
                                }
                            }
                        }
                    }
                },
            });
        });
    </script>
@endsection