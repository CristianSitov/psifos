@extends('tablar::page')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <!-- Page pre-title -->
                    <div class="page-pretitle">
                        Overview
                    </div>
                    <h2 class="page-title">
                        Dashboard
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Elections</h3>
                        </div>

                        <div class="card-body">
                            <div id="graph-controls" style="margin-bottom: 1em;"></div>
                            <div id="container-comparison-gross" style="min-height: 600px"></div>
                            <div id="container-comparison-share" style="min-height: 600px"></div>
                            <div id="container-comparison-final" style="min-height: 600px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const jsonUrl = '{{ url('/') }}/status.json';
        const AUTO_REFRESH_MS = 60000;
        const seriesNames = [
            'Presence 2019 - 1',
            'Presence 2019 - 2',
            'Presence 2024 - 1',
            'Presence 2025 - 1',
            'Presence 2025 - 2'
        ];

        let grossChart = null;
        let shareChart = null;
        let finalsChart = null;

        // === URL Param Utilities ===
        function getUrlParams() {
            const params = new URLSearchParams(window.location.search);
            const xMin = parseFloat(params.get('xMin'));
            const xMax = parseFloat(params.get('xMax'));
            const visible = params.get('visible')
                ?.split(',')
                .map(v => parseInt(v, 10))
                .filter(v => !isNaN(v));
            return {
                xMin: !isNaN(xMin) ? xMin : null,
                xMax: !isNaN(xMax) ? xMax : null,
                visible,
            };
        }
        function updateUrlParams(updates) {
            const params = new URLSearchParams(window.location.search);
            for (const [key, value] of Object.entries(updates)) {
                if (value === null || value === undefined || value === '') {
                    params.delete(key);
                } else {
                    params.set(key, Array.isArray(value) ? value.join(',') : value);
                }
            }
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            history.replaceState(null, '', newUrl);
        }

        // === UI ===
        function humanSize(value) {
            if (value == null || isNaN(value)) return '';
            return Highcharts.numberFormat(value / 1_000_000, 2, '.', '') + ' M';
        }

        function renderCheckboxes(visibleSet) {
            const container = document.getElementById('graph-controls');
            container.innerHTML = '';
            seriesNames.forEach((name, i) => {
                const label = document.createElement('label');
                label.style.marginRight = '1em';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = visibleSet.has(i);
                checkbox.addEventListener('change', () => {
                    const newVisible = Array.from(document.querySelectorAll('#graph-controls input[type="checkbox"]'))
                        .map((cb, idx) => cb.checked ? idx : null)
                        .filter(i => i !== null);
                    updateUrlParams({ visible: newVisible });
                    fetchData();
                });
                label.appendChild(checkbox);
                label.append(' ' + name);
                container.appendChild(label);
            });
        }

        // === Sync ===
        function syncVisibility(index, visible) {
            [grossChart, shareChart].forEach(chart => {
                if (chart && chart.series[index]) {
                    chart.series[index].setVisible(visible, false);
                }
            });
            if (grossChart) grossChart.redraw();
            if (shareChart) shareChart.redraw();
        }

        function handleLegendClick(index) {
            const currentVisible = grossChart.series[index].visible;
            const newVisibleSet = new Set(grossChart.series
                .map((s, i) => (i === index ? !currentVisible : s.visible ? i : null))
                .filter(i => i !== null));
            updateUrlParams({ visible: [...newVisibleSet].sort((a, b) => a - b) });
            fetchData();
        }

        function syncZoom(min, max) {
            [grossChart, shareChart].forEach(chart => {
                if (chart && chart.xAxis[0]) {
                    chart.xAxis[0].setExtremes(min, max, true, false);
                }
            });
        }

        // === Main ===
        function fetchData() {
            const { xMin, xMax, visible } = getUrlParams();
            const visibleSet = new Set(visible ?? [0, 1, 2, 3, 4]);
            renderCheckboxes(visibleSet);

            fetch(jsonUrl)
                .then(res => res.json())
                .then(data => {
                    const categories = data.presence.map(i => i.day_hour_key);
                    const candidates = data.finals.map(item => item.candidate);
                    const finals2025 = data.finals.map(item => ({
                        y: item.votes,
                        d: item.difference,
                        p: ((item.votes / total) * 100).toFixed(2) // Calculate percentage and format to 2 decimals
                    }));

                    const grossColors = [
                        'rgba(150, 0, 0, 0.8)',
                        'rgba(100, 0, 0, 0.8)',
                        'rgba(160, 160, 0, 0.8)',
                        'rgba(50, 100, 255, 0.8)',
                        'rgba(0, 50, 255, 0.8)'
                    ];

                    const grossData = [
                        data.presence.map(i => i.the_presence_2019_1),
                        data.presence.map(i => i.the_presence_2019_2),
                        data.presence.map(i => i.the_presence_2024_1),
                        data.presence.map(i => i.the_presence_2025_1),
                        data.presence.map(i => i.the_presence_2025_2),
                    ];

                    const shareData = [
                        data.presence.map(i => i.the_presence_2019_1_percent),
                        data.presence.map(i => i.the_presence_2019_2_percent),
                        data.presence.map(i => i.the_presence_2024_1_percent),
                        data.presence.map(i => i.the_presence_2025_1_percent),
                        data.presence.map(i => i.the_presence_2025_2_percent),
                    ];

                    if (grossChart) grossChart.destroy();
                    if (shareChart) shareChart.destroy();

                    const createSeries = (dataArr) =>
                        seriesNames.map((name, i) => ({
                            name,
                            data: dataArr[i],
                            color: grossColors[i],
                            visible: visibleSet.has(i),
                            events: {
                                legendItemClick: function () {
                                    handleLegendClick(i);
                                    return false;
                                }
                            }
                        }));

                    grossChart = Highcharts.chart('container-comparison-gross', {
                        chart: {
                            zooming: { type: 'x' },
                            events: {
                                load() {
                                    const xAxis = this.xAxis[0];
                                    if (xMin !== null && xMax !== null) {
                                        xAxis.setExtremes(xMin, xMax, true, false);
                                    } else {
                                        const dataMin = xAxis.dataMin, dataMax = xAxis.dataMax;
                                        xAxis.setExtremes(dataMin + (dataMax - dataMin) * 2 / 3, dataMax);
                                    }
                                },
                                selection(event) {
                                    if (event.xAxis) {
                                        const min = event.xAxis[0].min;
                                        const max = event.xAxis[0].max;
                                        updateUrlParams({ xMin: min.toFixed(2), xMax: max.toFixed(2) });
                                        syncZoom(min, max);
                                    } else {
                                        updateUrlParams({ xMin: null, xMax: null });
                                        syncZoom(null, null);
                                    }
                                    return false;
                                }
                            }
                        },
                        title: { text: 'Presence Comparison Over Years - Gross' },
                        xAxis: { categories, title: { text: 'Time' } },
                        yAxis: { title: { text: 'Presence (Millions)' } },
                        tooltip: {
                            shared: true,
                            useHTML: true,
                            formatter: function () {
                                return `<table>${
                                    this.points.map(p => `
                                <tr>
                                    <td style="color:${p.color}; padding-right:10px; white-space:nowrap;">
                                        ● ${p.series.name}
                                    </td>
                                    <td style="text-align:right; min-width:70px;">
                                        <b>${humanSize(p.y)}</b>
                                    </td>
                                </tr>
                            `).join('')
                                }</table>`;
                            }
                        },
                        series: createSeries(grossData)
                    });

                    shareChart = Highcharts.chart('container-comparison-share', {
                        chart: { zooming: { type: 'x' } },
                        title: { text: 'Presence Comparison Over Years - Share' },
                        xAxis: { categories, title: { text: 'Time' } },
                        yAxis: { title: { text: 'Share (%)' } },
                        tooltip: {
                            shared: true,
                            formatter: function () {
                                return this.points.map(p =>
                                    `<span style="color:${p.color}">●</span> ${p.series.name}: <b>${p.y.toFixed(2)}%</b><br/>`
                                ).join('');
                            }
                        },
                        series: createSeries(shareData)
                    });

                    if (xMin !== null && xMax !== null) {
                        syncZoom(xMin, xMax);
                    }

                    finalsChart = Highcharts.chart('container-comparison-final', {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: 'Finals 2025'
                        },
                        xAxis: {
                            categories: candidates,
                            title: {
                                text: 'Candidates',
                                style: {
                                    fontSize: '20px'
                                }
                            },
                            labels: {
                                skew3d: true,
                                style: {
                                    fontSize: '10px',
                                    textOverflow: 'ellipsis',
                                    width: '100px',
                                }
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Distribution'
                            }
                        },
                        plotOptions: {
                            series: {
                                dataLabels: {
                                    enabled: true,
                                    formatter: function () {
                                        return `D: ${humanSize(this.point.d)} //  T: ${humanSize(this.point.y)} // P: ${this.point.p}%`; // Display percentage as data label
                                    }
                                },
                                pointWidth: 30,
                            },
                        },
                        series: [
                            {
                                name: 'Finals 2024',
                                data: finals2025,
                                colorByPoint: true,
                                colors: [
                                    "#F15854", // Muted Red
                                    "#5DA5DA", // Soft Blue
                                ],
                            }
                        ]
                    });
                })
                .catch(err => console.error("Fetch error:", err));
        }

        // === Init ===
        fetchData();
        setInterval(fetchData, AUTO_REFRESH_MS);
    </script>
@endsection
