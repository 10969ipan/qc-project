(function () {
    'use strict';

    function initAnalysisCharts(data) {
        // Set defaults for Chart.js
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = 'Nunito, -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.color = '#858796';
            if (typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
            }
        }

        // 1. Area Chart (Total NG pcs) - Chart.js
        const ctxArea = document.getElementById("myAreaChart");
        if (ctxArea) {
            new Chart(ctxArea, {
                type: 'line',
                data: {
                    labels: data.labels,
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
                        data: data.dataValues,
                        datalabels: {
                            align: 'end',
                            anchor: 'end',
                            color: '#4e73df',
                            font: { weight: 'bold' }
                        }
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 12 } },
                        y: { ticks: { maxTicksLimit: 5, padding: 10 }, grid: { color: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2] } }
                    },
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: "rgb(255,255,255)", bodyColor: "#858796", borderColor: '#dddfeb', borderWidth: 1 } }
                }
            });
        }

        // 2. Percentage Chart (Total NG %) - Chart.js
        const ctxPerc = document.getElementById("myPercentageChart");
        if (ctxPerc) {
            new Chart(ctxPerc, {
                type: 'line',
                data: {
                    labels: data.labels,
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
                        data: data.dataPercentage,
                        datalabels: {
                            align: 'end',
                            anchor: 'end',
                            color: '#1cc88a',
                            font: { weight: 'bold' },
                            formatter: (val) => val + "%"
                        }
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 12 } },
                        y: { ticks: { maxTicksLimit: 5, padding: 10, callback: (v) => v + '%' }, grid: { color: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2] } }
                    },
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: "rgb(255,255,255)", bodyColor: "#858796", borderColor: '#dddfeb', borderWidth: 1, callbacks: { label: (i) => i.dataset.label + ': ' + i.raw + '%' } } }
                }
            });
        }

        // 3. CanvasJS Work Speed Chart
        const workSpeedContainer = document.getElementById("workSpeedChartContainer");
        if (workSpeedContainer && typeof CanvasJS !== 'undefined') {
            const canvasJSDataPoints = [];
            data.inspectorItemLabels.forEach((itemName, itemIndex) => {
                const itemData = { label: itemName, operators: [] };
                data.inspectorItemDatasets.forEach(dataset => {
                    const actualCycleTime = dataset.data[itemIndex];
                    const standardCycleTime = data.itemCycleTimeData[itemIndex];
                    let percentage = 0;
                    if (standardCycleTime > 0 && actualCycleTime > 0) {
                        percentage = (actualCycleTime / standardCycleTime) * 100;
                    }
                    if (percentage > 0) {
                        itemData.operators.push({
                            name: dataset.label,
                            percentage: percentage,
                            color: dataset.backgroundColor
                        });
                    }
                });
                if (itemData.operators.length > 0) canvasJSDataPoints.push(itemData);
            });

            const workSpeedChart = new CanvasJS.Chart("workSpeedChartContainer", {
                theme: "light1",
                animationEnabled: true,
                exportEnabled: true,
                title: { text: "Kecepatan Kerja per Item (%)", fontSize: 16, fontFamily: "Nunito, sans-serif" },
                axisX: { title: "Item", titleFontSize: 14, labelFontSize: 10, interval: 1, gridThickness: 0 },
                axisY: { title: "Persentase (%)", titleFontSize: 14, labelFontSize: 12, suffix: "%", maximum: 200, gridThickness: 1, gridColor: "#f0f0f0" },
                legend: { cursor: "pointer", fontSize: 12, verticalAlign: "top", horizontalAlign: "center" },
                toolTip: { 
                    shared: true,
                    content: function(e) {
                        let content = "<strong>" + e.entries[0].dataPoint.label + "</strong><br/>";
                        e.entries.forEach(entry => {
                            content += "<span style='color:" + entry.dataSeries.color + "'>" + entry.dataSeries.name + "</span>: " + entry.dataPoint.y.toFixed(1) + "%<br/>";
                        });
                        return content;
                    }
                },
                data: []
            });

            const operatorMap = {};
            canvasJSDataPoints.forEach(item => {
                item.operators.forEach(op => {
                    if (!operatorMap[op.name]) {
                        operatorMap[op.name] = { type: "bar", name: op.name, showInLegend: true, color: op.color, dataPoints: [] };
                    }
                    operatorMap[op.name].dataPoints.push({ label: item.label, y: parseFloat(op.percentage.toFixed(1)), indexLabel: "{y}%", indexLabelFontSize: 10 });
                });
            });
            Object.values(operatorMap).forEach(series => workSpeedChart.options.data.push(series));
            workSpeedChart.render();
        }

        // 4. CanvasJS Standard Cycle Time Chart
        const cycleTimeContainer = document.getElementById("cycleTimeChartContainer");
        if (cycleTimeContainer && typeof CanvasJS !== 'undefined') {
            const chart = new CanvasJS.Chart("cycleTimeChartContainer", {
                theme: "light1",
                animationEnabled: true,
                exportEnabled: true,
                title: { text: "Standard Cycle Time per Item", fontSize: 16, fontFamily: "Nunito, sans-serif" },
                axisX: { title: "Item", titleFontSize: 14, labelFontSize: 10, interval: 1, gridThickness: 0 },
                axisY: { title: "Waktu (detik)", titleFontSize: 14, labelFontSize: 12, suffix: "s", maximum: data.maxCycleTimeY || 30, gridThickness: 1, gridColor: "#f0f0f0" },
                toolTip: {
                    content: function(e) {
                        const idx = e.dataPoint.index;
                        return "<strong>" + e.dataPoint.label + "</strong><br/>Standar: " + e.dataPoint.y + " s/pcs<br/>Total Pcs: " + data.sortedItemTotalPcs[idx] + "<br/>Total Detik: " + data.sortedItemTotalSeconds[idx];
                    }
                },
                data: [{
                    type: "bar", color: "#6f42c1",
                    dataPoints: data.itemLabels.map((label, idx) => ({
                        label: label, y: data.itemCycleTimeData[idx], index: idx, indexLabel: "{y} s", indexLabelFontSize: 10
                    }))
                }]
            });
            chart.render();
        }

        // 5. CanvasJS Jenis NG (pcs & %)
        const ngCountContainer = document.getElementById("ngCountChartContainer");
        if (ngCountContainer && typeof CanvasJS !== 'undefined') {
            const ngCountChart = new CanvasJS.Chart("ngCountChartContainer", {
                theme: "light1",
                animationEnabled: true,
                exportEnabled: true,
                title: { text: "Jenis NG (pcs)", fontSize: 16, fontFamily: "Nunito, sans-serif" },
                axisX: { title: "Jenis NG", titleFontSize: 14, labelFontSize: 10, interval: 1, gridThickness: 0 },
                axisY: { title: "Jumlah (pcs)", titleFontSize: 14, labelFontSize: 12, gridThickness: 1, gridColor: "#f0f0f0" },
                data: [{
                    type: "bar", color: "#e74a3b",
                    dataPoints: data.defectLabels.map((label, idx) => ({
                        label: label, y: data.defectData[idx], indexLabel: "{y} pcs", indexLabelFontSize: 10
                    }))
                }]
            });
            ngCountChart.render();
        }

        const ngPercContainer = document.getElementById("ngPercentageChartContainer");
        if (ngPercContainer && typeof CanvasJS !== 'undefined') {
            const sum = data.defectData.reduce((a, b) => a + b, 0);
            const ngPercChart = new CanvasJS.Chart("ngPercentageChartContainer", {
                theme: "light1",
                animationEnabled: true,
                exportEnabled: true,
                title: { text: "Jenis NG (%)", fontSize: 16, fontFamily: "Nunito, sans-serif" },
                axisX: { title: "Jenis NG", titleFontSize: 14, labelFontSize: 10, interval: 1, gridThickness: 0 },
                axisY: { title: "Persentase (%)", titleFontSize: 14, labelFontSize: 12, suffix: "%", gridThickness: 1, gridColor: "#f0f0f0" },
                toolTip: { 
                    content: (e) => `<strong>${e.dataPoint.label}</strong><br/>Persentase: ${(e.dataPoint.y * 100 / sum).toFixed(1)}%<br/>Jumlah: ${e.dataPoint.y} pcs`
                },
                data: [{
                    type: "bar", color: "#36b9cc",
                    dataPoints: data.defectLabels.map((label, idx) => ({
                        label: label, y: data.defectData[idx], indexLabel: (data.defectData[idx] * 100 / sum).toFixed(1) + "%", indexLabelFontSize: 10
                    }))
                }]
            });
            ngPercChart.render();
        }
    }

    if (window.__ANALYSIS__) {
        initAnalysisCharts(window.__ANALYSIS__);
    }

    window.initAnalysisCharts = initAnalysisCharts;
})();
