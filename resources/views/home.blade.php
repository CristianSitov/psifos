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
                            <div id="container" style="min-height: 600px"></div>
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
            const categories = data.map(item => item.the_key);
            const presence2019 = data.map(item => item.the_presence_2019);
            const presence2024 = data.map(item => item.the_presence_2024);

            // Create the Highcharts area plot
            Highcharts.chart('container', {
                chart: {
                    type: 'area'
                },
                title: {
                    text: 'Presence Comparison Over Years'
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
                        color: 'rgba(67, 67, 72, 0.8)' // Optional: custom color
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
