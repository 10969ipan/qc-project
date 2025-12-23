@extends('layouts.admin')

@section('title', 'Report Monthly Sub Assy')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Report Monthly Sub Assy</h1>
    </div>

    <!-- Date Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Tanggal</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('analysis.monthly_ng') }}" class="form-inline">
                <div class="form-group mb-2">
                    <label for="start_date" class="mr-2">Mulai Tanggal:</label>
                    <input type="date" class="form-control mr-4" id="start_date" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="form-group mb-2">
                    <label for="end_date" class="mr-2">Sampai Tanggal:</label>
                    <input type="date" class="form-control mr-4" id="end_date" name="end_date" value="{{ request('end_date') }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Cari</button>
                <a href="{{ route('analysis.monthly_ng') }}" class="btn btn-secondary mb-2 ml-2">Reset</a>
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
                            <i class="fas fa-circle text-info"></i> Presentase NG (%)
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Kecepatan Kerja (%)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="myInspectorItemCycleChart"></canvas>
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

    document.addEventListener("DOMContentLoaded", function() {
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

        // --- Bar Chart (Avg Cycle Time per Item by User) ---
        var ctxInspectorItemCycle = document.getElementById("myInspectorItemCycleChart");
        var inspectorItemLabels = @json($inspectorItemLabels);
        var inspectorItemDatasets = @json($inspectorItemDatasets);
        var itemCycleTimeDataForCalc = @json($itemCycleTimeData);

        // Add datalabels config to each dataset
        inspectorItemDatasets.forEach(dataset => {
            dataset.datalabels = {
                color: '#fff',
                font: { weight: 'bold', size: 10 },
                anchor: 'center',
                align: 'center',
                formatter: function(value, ctx) {
                    if (value > 0) {
                        // ctx.dataIndex corresponds to the item index (y-axis category)
                        // inspectorItemLabels matches itemCycleTimeDataForCalc keys
                        var idx = ctx.dataIndex;
                        var std = itemCycleTimeDataForCalc[idx];
                        var pct = 0;
                        if (std > 0) {
                            pct = (value / std) * 100;
                        }
                        return pct.toFixed(0) + "%";
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
                            maxTicksLimit: 20, // Allow more items
                            padding: 10,
                            autoSkip: false // Show all item names
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
                        display: true, // Show legend for multiple users
                        position: 'top',
                        labels: {
                            boxWidth: 10,
                            padding: 10
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
                            label: function(tooltipItem) {
                                var std = itemCycleTimeDataForCalc[tooltipItem.dataIndex];
                                var pct = 0;
                                if (std > 0) {
                                    pct = (tooltipItem.raw / std) * 100;
                                }
                                return tooltipItem.dataset.label + ': ' + pct.toFixed(1) + '%';
                            }
                        }
                    }
                }
            }
        });

        // --- Bar Chart (Avg Cycle Time per Item) ---
        var ctxItemCycle = document.getElementById("myItemCycleChart");
        var sortedItemTotalPcs = @json($sortedItemTotalPcs);
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
                        formatter: function(value, ctx) {
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
                            maxTicksLimit: 20, // Allow more items
                            padding: 10,
                            autoSkip: false // Show all item names
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
                            label: function(tooltipItem) {
                                var idx = tooltipItem.dataIndex;
                                var pcs = sortedItemTotalPcs[idx];
                                var secs = sortedItemTotalSeconds[idx];
                                return [
                                    tooltipItem.dataset.label + ': ' + tooltipItem.raw + 's',
                                    'Standar: ' + tooltipItem.raw + ' s/pcs', // Display "Standard: X s/pcs"
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
                        formatter: function(value, ctx) {
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
                            callback: function(value, index, values) {
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
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw + '%';
                            }
                        }
                    }
                }
            }
        });

        // --- Cycle Time Chart (Trend) ---
        var ctxCycle = document.getElementById("myCycleTimeChart");
        
        var myCycleTimeChart = new Chart(ctxCycle, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Avg Cycle Time",
                    lineTension: 0.3,
                    backgroundColor: "rgba(246, 194, 62, 0.05)",
                    borderColor: "rgba(246, 194, 62, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(246, 194, 62, 1)",
                    pointBorderColor: "rgba(246, 194, 62, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(246, 194, 62, 1)",
                    pointHoverBorderColor: "rgba(246, 194, 62, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: dataCycleTime,
                    datalabels: {
                        align: 'end',
                        anchor: 'end',
                        color: '#f6c23e',
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
                            padding: 10,
                            callback: function(value, index, values) {
                                return value + 's';
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
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw + 's';
                            }
                        }
                    }
                }
            }
        });

        // --- Bar Chart (Variants) ---
        var ctxBar = document.getElementById("myBarChart");
        var defectLabels = @json($defectLabels);
        var defectData = @json($defectData);

        var myBarChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: defectLabels,
                datasets: [{
                    label: "Jumlah NG",
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
                indexAxis: 'y', // Horizontal bar chart is often better for variant names
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
                        formatter: function(value, ctx) {
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
                indexAxis: 'y', // Horizontal bars
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
                            callback: function(value, index, values) {
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
                            label: function(tooltipItem) {
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
