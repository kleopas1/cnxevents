@extends('cnxevents::layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="container">
    <h1>Analytics</h1>
    <div class="row">
        <div class="col-md-6">
            <h3>Events by Status</h3>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="col-md-6">
            <h3>Events by Venue</h3>
            <canvas id="venueChart"></canvas>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <h3>Monthly Events</h3>
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    fetch('{{ route("cnxevents.analytics.status") }}')
        .then(response => response.json())
        .then(data => {
            new Chart(document.getElementById('statusChart'), {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                    }]
                }
            });
        });

    // Venue Chart
    fetch('{{ route("cnxevents.analytics.venue") }}')
        .then(response => response.json())
        .then(data => {
            new Chart(document.getElementById('venueChart'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Events',
                        data: data.values,
                        backgroundColor: '#36A2EB'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

    // Monthly Chart
    fetch('{{ route("cnxevents.analytics.monthly") }}')
        .then(response => response.json())
        .then(data => {
            new Chart(document.getElementById('monthlyChart'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Events',
                        data: data.values,
                        borderColor: '#FF6384',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
});
</script>
@endsection