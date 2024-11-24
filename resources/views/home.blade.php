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
            const finals2024 = data.finals.map(item => item.votes);

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
                    type: 'column',
                    options3d: {
                        enabled: true,
                        alpha: 15,
                        beta: 15,
                        viewDistance: 25,
                        depth: 40
                    }
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
                    column: {
                        stacking: 'normal',
                        depth: 40
                    }
                },
                series: [
                    {
                        name: 'Finals 2024',
                        data: finals2024,
                        color: 'rgba(30, 50, 236, 0.8)'
                    }
                ]
            });
        })
        .catch(error => console.error('Error fetching the JSON data:', error));
}

// Fetch immediately and then every minute
fetchData(); // Initial fetch
setInterval(fetchData, 60000); // Fetch every 60000ms (1 minute)

</script>
@endsection
