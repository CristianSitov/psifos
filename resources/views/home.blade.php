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
                            <div id="container-comparison-count"></div>
                            <div id="container-comparison-final" style="min-height: 600px"></div>
                            <div id="container-comparison-gross"></div>
                            <div id="container-comparison-share"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
// URL of the JSON file
const jsonUrl = '{{ url('/') }}/status.json';

// Fetch the JSON data
function fetchData() {
    fetch(jsonUrl)
        .then(response => response.json())
        .then(data => {
            const categories = data.presence.map(item => item.the_key);
            const presence2019 = data.presence.map(item => item.the_presence_2019);
            const presence2024 = data.presence.map(item => item.the_presence_2024);
            const presence2019Share = data.presence.map(item => item.the_presence_2019_percent);
            const presence2024Share = data.presence.map(item => item.the_presence_2024_percent);
            const candidates = data.finals.map(item => item.candidate);
            const total = data.finals.reduce((sum, item) => sum + item.votes, 0);
            const finals2024 = data.finals.map(item => ({
                y: item.votes,
                d: item.difference,
                p: ((item.votes / total) * 100).toFixed(2) // Calculate percentage and format to 2 decimals
            }));
            const totalVotes = data.totals.total;
            const finalVotes = data.totals.final;

            Highcharts.chart('container-comparison-gross', {
                chart: {
                    type: 'column'
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
                    title: {
                        text: 'Presence'
                    }
                },
                series: [
                    {
                        name: 'Presence 2019',
                        data: presence2019,
                        color: 'rgba(124, 181, 236, 0.8)' // Optional: custom color
                    },
                    {
                        name: 'Presence 2024',
                        data: presence2024,
                        color: 'rgba(30, 50, 236, 0.8)' // Optional: custom color
                    }
                ]
            });

            Highcharts.chart('container-comparison-share', {
                chart: {
                    type: 'column'
                },
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
                        name: 'Presence 2019',
                        data: presence2019Share,
                        color: 'rgba(124, 181, 236, 0.8)' // Optional: custom color
                    },
                    {
                        name: 'Presence 2024',
                        data: presence2024Share,
                        color: 'rgba(30, 50, 236, 0.8)' // Optional: custom color
                    }
                ]
            });

            Highcharts.chart('container-comparison-final', {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: 'Finals 2024'
                },
                xAxis: {
                    categories: candidates,
                    title: {
                        text: 'Categories'
                    },
                    labels: {
                        skew3d: true,
                        style: {
                            fontSize: '16px'
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
                        }
                    }
                },
                series: [
                    {
                        name: 'Finals 2024',
                        data: finals2024,
                        colorByPoint: true,
                        colors: [
                            "rgb(123, 45, 67)",
                            "rgb(255, 0, 100)",
                            "rgb(34, 123, 230)",
                            "rgb(210, 178, 99)",
                            "rgb(75, 200, 180)",
                            "rgb(10, 90, 220)",
                            "rgb(255, 123, 50)",
                            "rgb(140, 70, 90)",
                            "rgb(180, 255, 60)",
                            "rgb(90, 40, 250)",
                            "rgb(200, 80, 120)",
                            "rgb(30, 200, 80)",
                            "rgb(190, 255, 220)",
                            "rgb(100, 10, 190)"
                        ]
                    }
                ]
            });

            Highcharts.chart('container-comparison-count', {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: 'Votes Count Progress'
                },
                xAxis: {
                    categories: ['Votes'], // Single category for the single bar
                    title: {
                        text: null
                    }
                },
                yAxis: {
                    min: 0,
                    max: totalVotes,
                    title: {
                        text: 'Number of Votes'
                    }
                },
                tooltip: {
                    formatter: function () {
                        return `<b>${this.series.name}</b>: ${this.y}<br>
                            Percentage: ${(this.y / totalVotes * 100).toFixed(2)}%`;
                    }
                },
                plotOptions: {
                    series: {
                        stacking: 'normal', // Stacked bar to show progress
                        dataLabels: {
                            enabled: true,
                            formatter: function () {
                                return `${((this.y / totalVotes) * 100).toFixed(2)}%`; // Show percentage
                            }
                        }
                    }
                },
                series: [{
                    name: 'Counted Votes',
                    data: [finalVotes],
                    color: '#7cb5ec' // Custom color for counted votes
                }, {
                    name: 'Remaining Votes',
                    data: [totalVotes - finalVotes],
                    color: '#90ed7d' // Custom color for remaining votes
                }]
            });
        })
        .catch(error => console.error('Error fetching the JSON data:', error));
}

// Fetch immediately and then every minute
fetchData(); // Initial fetch
setInterval(fetchData, 60000); // Fetch every 60000ms (1 minute)

function humanSize(size) {
    let base = 1000
    let units = ['', 'K', 'M', 'G', 'T']
    let i = Math.log(size) / Math.log(base) | 0
    return `${(size / Math.pow(base, i)).toFixed(3) * 1} ${units[i]}`
}

</script>
@endsection
