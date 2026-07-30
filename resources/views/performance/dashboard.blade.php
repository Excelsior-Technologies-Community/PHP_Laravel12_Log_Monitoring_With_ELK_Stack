<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Performance Monitoring Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {

            background: #f4f6f9;

        }

        .dashboard-title {

            font-weight: bold;

        }

        .stat-card {

            border: none;

            border-radius: 12px;

            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);

            transition: .3s;

        }

        .stat-card:hover {

            transform: translateY(-4px);

        }

        .stat-icon {

            font-size: 34px;

        }

        .chart-card {

            border: none;

            border-radius: 12px;

            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);

        }

        .table-card {

            border: none;

            border-radius: 12px;

            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);

        }
    </style>

</head>

<body>

    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="dashboard-title">

                    <i class="bi bi-speedometer2"></i>

                    Performance Monitoring Dashboard

                </h2>

                <p class="text-muted mb-0">

                    Laravel Performance Analytics

                </p>

            </div>

            <a href="/"

                class="btn btn-primary">

                <i class="bi bi-house-fill"></i>

                Home

            </a>

        </div>


        <!-- Statistics -->

        <div class="row g-4 mb-4">

            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Total Requests</h6>

                            <h2>

                                {{ $totalRequests }}

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-bar-chart stat-icon text-primary"></i>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Average Response</h6>

                            <h2>

                                {{ round($averageResponse,2) }}

                                ms

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-clock-history stat-icon text-success"></i>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Maximum Response</h6>

                            <h2>

                                {{ $maxResponse }}

                                ms

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-arrow-up-circle stat-icon text-danger"></i>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Minimum Response</h6>

                            <h2>

                                {{ $minResponse }}

                                ms

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-arrow-down-circle stat-icon text-info"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Second Row -->

        <div class="row g-4 mb-4">

            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Slow Requests</h6>

                            <h2 class="text-danger">

                                {{ $slowRequests }}

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-exclamation-triangle-fill stat-icon text-danger"></i>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Fast Requests</h6>

                            <h2 class="text-success">

                                {{ $fastRequests }}

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-lightning-charge-fill stat-icon text-success"></i>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Failed Requests</h6>

                            <h2 class="text-danger">

                                {{ $failedRequests }}

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-x-circle-fill stat-icon text-danger"></i>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="card stat-card p-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>Success Rate</h6>

                            <h2 class="text-primary">

                                {{ $successRate }}%

                            </h2>

                        </div>

                        <div>

                            <i class="bi bi-check-circle-fill stat-icon text-primary"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Response Time Chart -->

        <div class="row mb-4">

            <div class="col-lg-8">

                <div class="card chart-card">

                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="bi bi-graph-up"></i>

                            Response Time Trend

                        </h5>

                    </div>

                    <div class="card-body">

                        <canvas id="responseChart"

                            height="120">

                        </canvas>

                    </div>

                </div>

            </div>

            <!-- Right Side Cards -->

            <div class="col-lg-4">

                <!-- Average Memory Usage -->

                <div class="card stat-card mb-4">

                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="bi bi-memory"></i>

                            Average Memory Usage

                        </h5>

                    </div>

                    <div class="card-body text-center">

                        <h2 class="text-primary">

                            {{ round($averageMemory / 1024, 2) }} KB

                        </h2>

                        <p class="text-muted mb-0">

                            Average memory consumed per request

                        </p>

                    </div>

                </div>

                <!-- Top Requested URLs -->

                <div class="card table-card">

                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="bi bi-link-45deg"></i>

                            Top Requested URLs

                        </h5>

                    </div>

                    <div class="card-body p-0">

                        <table class="table table-striped table-hover mb-0">

                            <thead class="table-dark">

                                <tr>

                                    <th>URL</th>

                                    <th class="text-center">

                                        Hits

                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($topUrls as $url)

                                <tr>

                                    <td>

                                        {{ $url->url }}

                                    </td>

                                    <td class="text-center">

                                        <span class="badge bg-primary">

                                            {{ $url->total }}

                                        </span>

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td colspan="2"
                                        class="text-center text-muted">

                                        No URL data available

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        <!-- Dashboard Summary -->

        <div class="row mb-4">

            <div class="col-12">

                <div class="card table-card">

                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="bi bi-info-circle-fill"></i>

                            Performance Summary

                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row text-center">

                            <div class="col-md-3">

                                <h4 class="text-success">

                                    {{ $successfulRequests }}

                                </h4>

                                <p class="mb-0">

                                    Successful Requests

                                </p>

                            </div>

                            <div class="col-md-3">

                                <h4 class="text-danger">

                                    {{ $failedRequests }}

                                </h4>

                                <p class="mb-0">

                                    Failed Requests

                                </p>

                            </div>

                            <div class="col-md-3">

                                <h4 class="text-warning">

                                    {{ $slowRequests }}

                                </h4>

                                <p class="mb-0">

                                    Slow Requests

                                </p>

                            </div>

                            <div class="col-md-3">

                                <h4 class="text-primary">

                                    {{ $successRate }}%

                                </h4>

                                <p class="mb-0">

                                    Success Rate

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Recent Requests -->

        <div class="row">

            <div class="col-12">

                <div class="card table-card">

                    <div class="card-header d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">

                            <i class="bi bi-clock-history"></i>

                            Recent Requests

                        </h5>

                        <span class="badge bg-primary">

                            {{ count($recentLogs) }} Records

                        </span>

                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover mb-0 align-middle">

                                <thead class="table-dark">

                                    <tr>

                                        <th>#</th>

                                        <th>Method</th>

                                        <th>URL</th>

                                        <th>Status</th>

                                        <th>Response Time</th>

                                        <th>Memory</th>

                                        <th>Request Type</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($recentLogs as $index => $log)

                                    <tr>

                                        <td>

                                            {{ $index + 1 }}

                                        </td>

                                        <td>

                                            @switch($log->method)

                                            @case('GET')

                                            <span class="badge bg-success">

                                                GET

                                            </span>

                                            @break

                                            @case('POST')

                                            <span class="badge bg-primary">

                                                POST

                                            </span>

                                            @break

                                            @case('PUT')

                                            <span class="badge bg-warning text-dark">

                                                PUT

                                            </span>

                                            @break

                                            @case('DELETE')

                                            <span class="badge bg-danger">

                                                DELETE

                                            </span>

                                            @break

                                            @default

                                            <span class="badge bg-secondary">

                                                {{ $log->method }}

                                            </span>

                                            @endswitch

                                        </td>

                                        <td>

                                            <small>

                                                {{ $log->url }}

                                            </small>

                                        </td>

                                        <td>

                                            @if($log->status_code >= 500)

                                            <span class="badge bg-danger">

                                                {{ $log->status_code }}

                                            </span>

                                            @elseif($log->status_code >= 400)

                                            <span class="badge bg-warning text-dark">

                                                {{ $log->status_code }}

                                            </span>

                                            @elseif($log->status_code >= 300)

                                            <span class="badge bg-info">

                                                {{ $log->status_code }}

                                            </span>

                                            @else

                                            <span class="badge bg-success">

                                                {{ $log->status_code }}

                                            </span>

                                            @endif

                                        </td>

                                        <td>

                                            @if($log->is_slow)

                                            <span class="badge bg-danger">

                                                {{ $log->response_time }} ms

                                            </span>

                                            @else

                                            <span class="badge bg-success">

                                                {{ $log->response_time }} ms

                                            </span>

                                            @endif

                                        </td>

                                        <td>

                                            {{ round($log->memory_usage / 1024, 2) }}

                                            KB

                                        </td>

                                        <td>

                                            @if($log->is_slow)

                                            <span class="badge bg-danger">

                                                Slow Request

                                            </span>

                                            @else

                                            <span class="badge bg-success">

                                                Normal

                                            </span>

                                            @endif

                                        </td>

                                    </tr>

                                    @empty

                                    <tr>

                                        <td colspan="7" class="text-center text-muted py-4">

                                            No performance logs available.

                                        </td>

                                    </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Chart.js -->

        <script>
            const labels = [

                @foreach($chartData as $log)

                "{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}",

                @endforeach

            ];



            const responseTimes = [

                @foreach($chartData as $log)

                {
                    {
                        $log - > response_time
                    }
                },

                @endforeach

            ];



            const ctx = document
                .getElementById('responseChart')
                .getContext('2d');



            new Chart(ctx, {

                type: 'line',

                data: {

                    labels: labels,

                    datasets: [

                        {

                            label: 'Response Time (ms)',

                            data: responseTimes,

                            borderColor: '#0d6efd',

                            backgroundColor: 'rgba(13,110,253,.15)',

                            borderWidth: 3,

                            fill: true,

                            tension: .4,

                            pointRadius: 4,

                            pointHoverRadius: 6

                        }

                    ]

                },

                options: {

                    responsive: true,

                    plugins: {

                        legend: {

                            display: true

                        }

                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            title: {

                                display: true,

                                text: 'Milliseconds'

                            }

                        },

                        x: {

                            title: {

                                display: true,

                                text: 'Request Time'

                            }

                        }

                    }

                }

            });
        </script>



        <script>
            setInterval(function() {

                location.reload();

            }, 30000);
        </script>



</body>

</html>