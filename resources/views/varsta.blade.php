<!DOCTYPE html>
<html>
<head>
    <title>Voturi pe varsta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <canvas id="myChart"></canvas>
    <x-app-layout>
        @push('scripts')
            <script>
                const data = {
                    labels: @json($results2024->map(fn ($data) => $data['age'])),
                    datasets: [
                        {
                            label: 'Registered votes 2020',
                            backgroundColor: 'rgba(255, 99, 132, 0.8)',
                            borderColor: 'rgb(255, 99, 132)',
                            data: @json($results2020->map(fn ($data) => $data['votes'])),
                        },
                        {
                            label: 'Registered votes 2024',
                            backgroundColor: 'rgba(99, 99, 132, 0.8)',
                            borderColor: 'rgb(255, 99, 132)',
                            data: @json($results2024->map(fn ($data) => $data['votes'])),
                        }
                    ],
                    responsive: true,
                };
                const config = {
                    type: 'bar',
                    data: data
                };
                const myChart = new Chart(
                    document.getElementById('myChart'),
                    config
                );
            </script>
        @endpush
    </x-app-layout>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
</body>
</html>
