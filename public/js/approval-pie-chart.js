// Approval Pie Chart with Leader Lines
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById("approvalPieChart");
    if (!ctx) return;

    var approvalPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    parseInt(ctx.dataset.pending || 0),
                    parseInt(ctx.dataset.approved || 0),
                    parseInt(ctx.dataset.rejected || 0)
                ],
                backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
                hoverBackgroundColor: ['#f4b619', '#17a673', '#d52a1a'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
                borderWidth: 2,
                borderColor: '#fff'
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function (context) {
                            var label = context.label || '';
                            var value = context.parsed || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 13
                    },
                    formatter: function (value, context) {
                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                        var percentage = ((value / total) * 100).toFixed(1);
                        return context.chart.data.labels[context.dataIndex] + '\n' + value + '\n(' + percentage + '%)';
                    },
                    anchor: 'end',
                    align: 'end',
                    offset: 8,
                    borderWidth: 2,
                    borderColor: function (context) {
                        return context.dataset.backgroundColor[context.dataIndex];
                    },
                    borderRadius: 4,
                    backgroundColor: function (context) {
                        return context.dataset.backgroundColor[context.dataIndex];
                    },
                    padding: 6,
                    display: function (context) {
                        return context.dataset.data[context.dataIndex] > 0;
                    }
                }
            },
            layout: {
                padding: {
                    top: 30,
                    bottom: 30,
                    left: 30,
                    right: 30
                }
            }
        },
        plugins: [{
            id: 'leaderLines',
            afterDatasetsDraw: function (chart) {
                var ctx = chart.ctx;
                chart.data.datasets.forEach(function (dataset, i) {
                    var meta = chart.getDatasetMeta(i);
                    if (!meta.hidden) {
                        meta.data.forEach(function (element, index) {
                            var value = dataset.data[index];
                            if (value === 0) return;

                            // Calculate positions
                            var startAngle = element.startAngle;
                            var endAngle = element.endAngle;
                            var midAngle = startAngle + (endAngle - startAngle) / 2;

                            var x = element.x;
                            var y = element.y;
                            var outerRadius = element.outerRadius;

                            // Point on the arc
                            var x1 = x + Math.cos(midAngle) * outerRadius;
                            var y1 = y + Math.sin(midAngle) * outerRadius;

                            // Extended point for the line
                            var extraRadius = 15;
                            var x2 = x + Math.cos(midAngle) * (outerRadius + extraRadius);
                            var y2 = y + Math.sin(midAngle) * (outerRadius + extraRadius);

                            // Horizontal line extension
                            var lineLength = 25;
                            var x3 = x2 + (Math.cos(midAngle) > 0 ? lineLength : -lineLength);
                            var y3 = y2;

                            // Draw the leader line
                            ctx.strokeStyle = dataset.backgroundColor[index];
                            ctx.lineWidth = 2;
                            ctx.beginPath();
                            ctx.moveTo(x1, y1);
                            ctx.lineTo(x2, y2);
                            ctx.lineTo(x3, y3);
                            ctx.stroke();
                        });
                    }
                });
            }
        }]
    });
});
