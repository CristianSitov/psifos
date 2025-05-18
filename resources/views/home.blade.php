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
                            <div id="container-comparison-gross" style="min-height: 600px"></div>
                            <div id="container-comparison-share" style="min-height: 600px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
// URL of the JSON file
const jsonUrl = '{{ url('/') }}/status.json';

const STORAGE_KEY = "highcharts-zoom";

function saveZoomToStorage(xMin, xMax) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ xMin, xMax }));
}

function getZoomFromStorage() {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    try {
        const { xMin, xMax } = JSON.parse(raw);
        if (!isNaN(xMin) && !isNaN(xMax)) {
            return { xMin, xMax };
        }
    } catch (e) {}
    return null;
}

function clearZoomFromStorage() {
    localStorage.removeItem(STORAGE_KEY);
}

// Fetch the JSON data
function fetchData() {
    const zoomState = getZoomFromStorage();

    fetch(jsonUrl)
        .then(response => response.json())
        .then(data => {
            const categories = data.presence.map(item => item.day_hour_key);
            const presence2019_1 = data.presence.map(item => item.the_presence_2019_1);
            const presence2019_2 = data.presence.map(item => item.the_presence_2019_2);
            const presence2024_1 = data.presence.map(item => item.the_presence_2024_1);
            const presence2025_1 = data.presence.map(item => item.the_presence_2025_1);
            const presence2025_2 = data.presence.map(item => item.the_presence_2025_2);
            const presence20191Share = data.presence.map(item => item.the_presence_2019_1_percent);
            const presence20192Share = data.presence.map(item => item.the_presence_2019_2_percent);
            const presence20241Share = data.presence.map(item => item.the_presence_2024_1_percent);
            const presence20251Share = data.presence.map(item => item.the_presence_2025_1_percent);
            const presence20252Share = data.presence.map(item => item.the_presence_2025_2_percent);
            // const candidates = data.finals.map(item => item.candidate);
            // const total = data.finals.reduce((sum, item) => sum + item.votes, 0);
            // const finals2025 = data.finals.map(item => ({
            //     y: item.votes,
            //     d: item.difference,
            //     p: ((item.votes / total) * 100).toFixed(2) // Calculate percentage and format to 2 decimals
            // }));

            const comparison_gross = Highcharts.chart('container-comparison-gross', {
                chart: {
                //     type: 'column'
                    zooming: {
                        type: 'x'
                    },
                    events: {
                        selection: function (event) {
                            if (event.xAxis) {
                                const xMin = event.xAxis[0].min;
                                const xMax = event.xAxis[0].max;
                                saveZoomToStorage(xMin, xMax);
                            } else {
                                clearZoomFromStorage(); // reset
                            }
                        },
                        redraw: function () {
                            if (!this.resetZoomButton && getZoomFromStorage()) {
                                this.showResetZoom();
                            }
                        }
                    }
                },
                title: {
                    text: 'Presence Comparison Over Years - Gross'
                },
                xAxis: {
                    categories: categories,
                    title: {
                        text: 'Categories'
                    }
                },
                yAxis: {
                    // type: 'logarithmic',
                    title: {
                        text: 'Presence'
                    }
                },
                series: [
                    {
                        name: 'Presence 2019 - 1',
                        data: presence2019_1,
                        color: 'rgba(150, 0, 0, 0.8)'
                    },
                    {
                        name: 'Presence 2019 - 2',
                        data: presence2019_2,
                        color: 'rgba(100, 0, 0, 0.8)'
                    },
                    {
                        name: 'Presence 2024 - 1',
                        data: presence2024_1,
                        color: 'rgba(160, 160, 0, 0.8)'
                    },
                    {
                        name: 'Presence 2025 - 1',
                        data: presence2025_1,
                        color: 'rgba(50, 100, 255, 0.8)'
                    },
                    {
                        name: 'Presence 2025 - 2',
                        data: presence2025_2,
                        color: 'rgba(0, 50, 255, 0.8)'
                    }
                ],
                tooltip: {
                    shared: true,
                    useHTML: true,
                    formatter: function () {
                        return `<table>
                                    ${this.points.map(point => `
                                        <tr>
                                            <td style="color:${point.color}; padding-right: 10px; white-space: nowrap; font-family: 'Courier New'" font-size: 0.75em>
                                                <b>● ${point.series.name}</b>
                                            </td>
                                            <td style="text-align: right; min-width: 70px; white-space: nowrap; font-family: 'Courier New'; font-size: 0.75em">
                                                <b>${humanSize(point.y)}</b>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </table>`;
                    }
                }
            });

            if (zoomState) {
                const axis = comparison_gross.xAxis[0];
                axis.setExtremes(zoomState.xMin, zoomState.xMax, true, false);

                // Manually show Reset Zoom button
                comparison_gross.showResetZoom();

                // Optional: store the zoom flag so reset works properly
                comparison_gross.resetZoomButton = comparison_gross.renderer.button(
                    "Reset Zoom",
                    comparison_gross.plotLeft + 10,
                    comparison_gross.plotTop + 10,
                    function () {
                        axis.setExtremes(null, null);
                        clearZoomFromStorage();
                        this.destroy();
                    }
                ).attr({
                    zIndex: 20
                }).add();
            }

            Highcharts.chart('container-comparison-share', {
                // chart: {
                //     type: 'column'
                // },
                title: {
                    text: 'Presence Comparison Over Years - Share'
                },
                xAxis: {
                    categories: categories,
                    title: {
                        text: 'Categories'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Presence'
                    }
                },
                series: [
                    {
                        name: 'Presence 2019 - 1',
                        data: presence20191Share,
                        color: 'rgba(124, 0, 0, 0.8)'
                    },
                    {
                        name: 'Presence 2019 - 2',
                        data: presence20192Share,
                        color: 'rgba(104, 0, 0, 0.8)'
                    },
                    {
                        name: 'Presence 2024 - 1',
                        data: presence20241Share,
                        color: 'rgba(80, 80, 0, 0.8)'
                    },
                    {
                        name: 'Presence 2025 - 1',
                        data: presence20251Share,
                        color: 'rgba(50, 100, 255, 0.8)'
                    },
                    {
                        name: 'Presence 2025 - 2',
                        data: presence20252Share,
                        color: 'rgba(0, 50, 255, 0.8)'
                    }
                ],
                tooltip: {
                    shared: true,
                    formatter: function () {
                        return this.points.map(point =>
                            `<span style="color:${point.color}">●</span> ${point.series.name}: <b>${point.y.toFixed(2)}%</b><br/>`
                        ).join('');
                    }
                }
            });

            // Highcharts.chart('container-comparison-final', {
            //     chart: {
            //         type: 'bar'
            //     },
            //     title: {
            //         text: 'Finals 2025'
            //     },
            //     xAxis: {
            //         categories: candidates,
            //         title: {
            //             text: 'Candidates',
            //             style: {
            //                 fontSize: '20px'
            //             }
            //         },
            //         labels: {
            //             skew3d: true,
            //             style: {
            //                 fontSize: '10px',
            //                 textOverflow: 'ellipsis',
            //                 width: '100px',
            //             }
            //         }
            //     },
            //     yAxis: {
            //         title: {
            //             text: 'Distribution'
            //         }
            //     },
            //     plotOptions: {
            //         series: {
            //             dataLabels: {
            //                 enabled: true,
            //                 formatter: function () {
            //                     return `D: ${humanSize(this.point.d)} //  T: ${humanSize(this.point.y)} // P: ${this.point.p}%`; // Display percentage as data label
            //                 }
            //             },
            //             pointWidth: 30,
            //         },
            //     },
            //     series: [
            //         {
            //             name: 'Finals 2024',
            //             data: finals2025,
            //             colorByPoint: true,
            //             colors: [
            //                 "#F15854", // Muted Red
            //                 "#5DA5DA", // Soft Blue
            //             ],
            //         }
            //     ]
            // });

        })
        .catch(error => console.error('Error fetching the JSON data:', error));
}

// Fetch immediately and then every minute
fetchData(); // Initial fetch
setInterval(fetchData, 60000); // Fetch every 60000ms (1 minute)

function humanSize(value) {
    if (value == null || isNaN(value)) return '';

    const millions = value / 1_000_000;

    // Example: 1.234 M
    return Highcharts.numberFormat(millions, 2, '.', '') + ' M';
}

</script>
@endsection
