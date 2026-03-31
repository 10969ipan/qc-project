(function () {
    'use strict';

    const DASH = window.__DASHBOARD__ || {};

    if (window.FusionCharts) {
        FusionCharts.ready(function () {
            renderGauges();
        });
    }

    window.addEventListener('load', function () {
        if (DASH.isDualView && DASH.statsJakarta && DASH.statsKarawang) {
            if (document.getElementById("chartJakarta")) {
                renderChart("chartJakarta", "STATUS APPROVAL - JAKARTA", DASH.statsJakarta);
            }
            if (document.getElementById("chartKarawang")) {
                renderChart("chartKarawang", "STATUS APPROVAL - KARAWANG", DASH.statsKarawang);
            }
        } else if (DASH.combinedStats && document.getElementById("chartContainer")) {
            renderChart("chartContainer", "Status Approval", DASH.combinedStats);
        }

        renderClaimChart();
        renderNgRateCharts();
    });

    function explodePie(e) {
        const dp = e.dataSeries.dataPoints[e.dataPointIndex];
        dp.exploded = (typeof dp.exploded === "undefined" || !dp.exploded);
        e.chart.render();
    }

    function toggleDataSeries(e) {
        if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        } else {
            e.dataSeries.visible = true;
        }
        e.chart.render();
    }

    function renderChart(containerId, title, stats) {
        if (!document.getElementById(containerId)) return;

        var chart = new CanvasJS.Chart(containerId, {
            exportEnabled: true,
            animationEnabled: true,
            title: {
                text: title,
                fontSize: 18,
                fontFamily: "Nunito"
            },
            legend: {
                cursor: "pointer",
                itemclick: explodePie,
                verticalAlign: "bottom",
                horizontalAlign: "center"
            },
            data: [{
                type: "pie",
                showInLegend: true,
                toolTipContent: "{name}: <strong>{y}</strong>",
                indexLabel: "{name} - {y}",
                dataPoints: [
                    { y: stats.pending,  name: "Pending",  color: "#f6c23e", exploded: true },
                    { y: stats.approved, name: "Approved", color: "#1cc88a" },
                    { y: stats.rejected, name: "Rejected", color: "#e74a3b" }
                ]
            }]
        });
        chart.render();
    }

    function calculateRate(stats) {
        if (!stats) return 0;
        
        // Handled = Approved + Rejected
        const handled = (stats.approved || 0) + (stats.rejected || 0);
        
        // Late Pending = Items created > 24h ago that are still pending
        const latePending = (stats.pending_late || 0);
        
        // Total "Due" = Handled + Late Pending
        const totalDue = handled + latePending;
        
        if (totalDue === 0) return 100; // No overdue items = 100% compliance
        
        // Rate = Handled / Total Due
        return Math.min(100, Math.round((handled / totalDue) * 100));
    }

    function renderGauges() {
        const dailyJkt   = DASH.dailyStatsJakarta  || null;
        const dailyKrw   = DASH.dailyStatsKarawang  || null;
        const dailyTotal = DASH.dailyCombinedStats   || null;

        if (dailyJkt && document.getElementById("gauge-jakarta")) {
            renderGauge("gauge-jakarta", "Jakarta", calculateRate(dailyJkt));
        }
        if (dailyKrw && document.getElementById("gauge-karawang")) {
            renderGauge("gauge-karawang", "Karawang", calculateRate(dailyKrw));
        }
        if (dailyTotal && document.getElementById("gauge-total")) {
            const namaPabrik = DASH.plantName || "Gabungan";
            renderGauge("gauge-total", namaPabrik, calculateRate(dailyTotal));
        }
    }

    function renderGauge(container, label, value) {
        if (!window.FusionCharts) return;

        if (FusionCharts.items && FusionCharts.items[container + "-gauge"]) {
            FusionCharts.items[container + "-gauge"].dispose();
        }

        const dataSource = {
            chart: {
                caption: label + " Approval Rate",
                lowerLimit: "0",
                upperLimit: "100",
                showValue: "1",
                numberSuffix: "%",
                theme: "gammel",
                baseFontSize: "11",
                captionFontSize: "14",
                subcaptionFontSize: "10",
                gaugeFillMix: "{light-10},{light-20},{light-30}",
                gaugeFillRatio: "40,20,40"
            },
            colorRange: {
                color: [
                    { minValue: "0",  maxValue: "50",  code: "#ef4444" },
                    { minValue: "50", maxValue: "75",  code: "#f59e0b" },
                    { minValue: "75", maxValue: "100", code: "#10b981" }
                ]
            },
            dials: {
                dial: [{
                    value: value.toString(),
                    tooltext: "<b>" + value + "%</b> disetujui hari ini"
                }]
            },
            trendpoints: {
                point: [{
                    startvalue: "100",
                    displayvalue: " ",
                    thickness: "2",
                    color: "#E15A26",
                    hideValue: "1",
                    usemarker: "1",
                    markerbordercolor: "#E15A26",
                    markertooltext: "Target Approval: 100%"
                }]
            }
        };

        new FusionCharts({
            id: container + "-gauge",
            type: "angulargauge",
            renderAt: container,
            width: "100%",
            height: "100%",
            dataFormat: "json",
            dataSource
        }).render();
    }

    function renderClaimChart() {
        var claimData = DASH.claimData;
        if (!claimData) return;

        const commonOptions = {
            animationEnabled: true,
            theme: "light2",
            axisX: {
                interval: 1,
                labelFontFamily: "Nunito",
                labelFontSize: 10
            },
            axisY: {
                title: "PPM",
                titleFontFamily: "Nunito",
                labelFontFamily: "Nunito",
                includeZero: true,
                minimum: 0
            },
            axisY2: {
                title: "Total Claim Jakarta-Karawang",
                titleFontFamily: "Nunito",
                labelFontFamily: "Nunito",
                includeZero: true,
                minimum: 0
            },
            toolTip: {
                shared: true,
                fontFamily: "Nunito"
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries,
                fontFamily: "Nunito",
                verticalAlign: "bottom",
                horizontalAlign: "center",
                fontSize: 10
            }
        };

        if (document.getElementById("chartClaimJakarta") && claimData.jakarta) {
            var dataJkt = claimData.jakarta.map(function (val, index) {
                var dp = { label: claimData.labels[index], y: val, claim_count: claimData.combined_total[index] };
                if (val > 0) {
                    dp.indexLabel = val.toString();
                    dp.indexLabelFontColor = "#2e59d9";
                    dp.indexLabelFontWeight = "bold";
                    dp.indexLabelFontSize = 10;
                }
                return dp;
            });

            var jktTarget = claimData.target.map((v, i) => {
                let dp = { label: claimData.labels[i], y: v };
                if (i === 0 || i === claimData.target.length - 1) {
                    dp.indexLabel = v.toString();
                    dp.indexLabelFontColor = "#c0392b";
                    dp.indexLabelFontSize = 9;
                    dp.indexLabelFontWeight = "bold";
                }
                return dp;
            });

            var jktTotalClaims = claimData.combined_total.map((v, i) => ({ label: claimData.labels[i], y: v }));

            var chartJkt = new CanvasJS.Chart("chartClaimJakarta", {
                ...commonOptions,
                toolTip: { ...commonOptions.toolTip, content: "{label}<br/><span style='color:{color}'>{name}</span>: {y}" },
                data: [
                    { type: "splineArea", name: "Jakarta PPM",                showInLegend: true, color: "#4e73df", markerSize: 5, dataPoints: dataJkt        },
                    { type: "line",       name: "Total Claim Jakarta-Karawang", axisYType: "secondary", showInLegend: true, color: "#f6c23e", markerSize: 5, dataPoints: jktTotalClaims },
                    { type: "line",       name: "Target",                     showInLegend: true, color: "#e74a3b", lineDashType: "dash", markerSize: 0, dataPoints: jktTarget     }
                ]
            });
            chartJkt.render();
        }

        if (document.getElementById("chartClaimKarawang") && claimData.karawang) {
            var dataKrw = claimData.karawang.map(function (val, index) {
                var dp = { label: claimData.labels[index], y: val, claim_count: claimData.combined_total[index] };
                if (val > 0) {
                    dp.indexLabel = val.toString();
                    dp.indexLabelFontColor = "#17a673";
                    dp.indexLabelFontWeight = "bold";
                    dp.indexLabelFontSize = 10;
                }
                return dp;
            });

            var targetData = claimData.target.map((v, i) => {
                let dp = { label: claimData.labels[i], y: v };
                if (i === 0 || i === claimData.target.length - 1) {
                    dp.indexLabel = v.toString();
                    dp.indexLabelFontColor = "#c0392b";
                    dp.indexLabelFontSize = 9;
                    dp.indexLabelFontWeight = "bold";
                }
                return dp;
            });

            var krwTotalClaims = claimData.combined_total.map((v, i) => ({ label: claimData.labels[i], y: v }));

            var chartKrw = new CanvasJS.Chart("chartClaimKarawang", {
                ...commonOptions,
                toolTip: { ...commonOptions.toolTip, content: "{label}<br/><span style='color:{color}'>{name}</span>: {y}" },
                data: [
                    { type: "splineArea", name: "Karawang PPM",               showInLegend: true, color: "#1cc88a", markerSize: 5, dataPoints: dataKrw       },
                    { type: "line",       name: "Total Claim Jakarta-Karawang", axisYType: "secondary", showInLegend: true, color: "#f6c23e", markerSize: 5, dataPoints: krwTotalClaims },
                    { type: "line",       name: "Target",                     showInLegend: true, color: "#e74a3b", lineDashType: "dash", markerSize: 0, dataPoints: targetData    }
                ]
            });
            chartKrw.render();
        }

        var claimFrequency = DASH.claimFrequency;
        if (document.getElementById("chartClaimFrequency") && claimFrequency) {
            var jktFreqData = claimFrequency.jakarta.map((v, i) => {
                var dp = { label: claimFrequency.labels[i], y: v };
                if (v > 0) { dp.indexLabel = v.toString(); dp.indexLabelFontSize = 10; dp.indexLabelFontWeight = "bold"; }
                return dp;
            });
            var krwFreqData = claimFrequency.karawang.map((v, i) => {
                var dp = { label: claimFrequency.labels[i], y: v };
                if (v > 0) { dp.indexLabel = v.toString(); dp.indexLabelFontSize = 10; dp.indexLabelFontWeight = "bold"; }
                return dp;
            });

            var chartFreq = new CanvasJS.Chart("chartClaimFrequency", {
                animationEnabled: true,
                theme: "light2",
                axisX: { interval: 1, labelFontFamily: "Nunito", labelFontSize: 10, reversed: true },
                axisY: { title: "Frekuensi", titleFontFamily: "Nunito", labelFontFamily: "Nunito", includeZero: true, minimum: 0 },
                toolTip: {
                    shared: true,
                    fontFamily: "Nunito",
                    content: "{label}<br/><span style='color:{color}'>{name}</span>: <strong>{y}</strong> claim"
                },
                legend: { cursor: "pointer", fontFamily: "Nunito", verticalAlign: "bottom", horizontalAlign: "center", fontSize: 10 },
                data: [
                    { type: "bar", name: "Jakarta",  showInLegend: true, color: "#2e59d9", dataPoints: jktFreqData },
                    { type: "bar", name: "Karawang", showInLegend: true, color: "#17a673", dataPoints: krwFreqData }
                ]
            });
            chartFreq.render();
        }
    }

    function renderNgRateCharts() {
        const ngData = DASH.ngRateData;
        if (!ngData) return;

        const isDualView   = DASH.isDualView;
        const currentPlant = DASH.currentPlant || '';

        if (isDualView) {
            if (document.getElementById("chartNgJakarta")) {
                renderSingleNgChart("chartNgJakarta", "Jakarta", ngData.jakarta, ngData.labels);
            }
            if (document.getElementById("chartNgKarawang")) {
                renderSingleNgChart("chartNgKarawang", "Karawang", ngData.karawang, ngData.labels);
            }
        } else {
            if (document.getElementById("chartNgSingle")) {
                const plantData  = currentPlant === 'jakarta' ? ngData.jakarta : ngData.karawang;
                const plantTitle = currentPlant === 'jakarta' ? 'JAKARTA' : 'KARAWANG';
                renderSingleNgChart("chartNgSingle", plantTitle, plantData, ngData.labels);
            }
        }
    }

    function renderSingleNgChart(containerId, plantName, plantData, labels) {
        if (!document.getElementById(containerId) || !plantData) return;

        const namaBulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
        const series = [];

        const formatLabel = (l) => {
            const parts = l.split('-');
            if (parts.length < 3) return l;
            return parts[2] + ' ' + namaBulan[parseInt(parts[1]) - 1];
        };

        if (plantData.sub_assy) {
            series.push({
                type: "spline", name: "Sub Assy",  color: "#0d6efd",
                showInLegend: true, yValueFormatString: "##0.00'%'",
                dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.sub_assy[i] }))
            });
        }
        if (plantData.in_process) {
            series.push({
                type: "spline", name: "In Process", color: "#198754",
                showInLegend: true, yValueFormatString: "##0.00'%'",
                dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.in_process[i] }))
            });
        }
        if (plantData.cross_cut) {
            series.push({
                type: "spline", name: "Cross Cut", color: "#6f42c1",
                showInLegend: true, yValueFormatString: "##0.00'%'",
                dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.cross_cut[i] }))
            });
        }
        if (plantData.sortir) {
            series.push({
                type: "spline", name: "Sortir", color: "#d63384",
                showInLegend: true, yValueFormatString: "##0.00'%'",
                dataPoints: labels.map((l, i) => ({ label: formatLabel(l), y: plantData.sortir[i] }))
            });
        }

        const chart = new CanvasJS.Chart(containerId, {
            animationEnabled: true,
            theme: "light2",
            title:  { text: "", fontFamily: "Nunito" },
            toolTip: { shared: true, fontFamily: "Nunito" },
            legend:  { cursor: "pointer", itemclick: toggleDataSeries, fontFamily: "Nunito" },
            axisX:  { labelFontFamily: "Nunito", labelFontSize: 10 },
            axisY:  { title: "NG Rate (%)", suffix: "%", titleFontFamily: "Nunito", labelFontFamily: "Nunito" },
            data: series
        });
        chart.render();
    }

})();
