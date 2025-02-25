@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <form action="{{ route('admin.run-scrape') }}" method="POST" class="d-inline">
        @csrf
        <div class="input-group">
            <select name="category" class="form-control mr-2">
                <option value="all">All Categories</option>
                @foreach($categoryCounts as $category => $count)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
            <div class="input-group-append">
                <button type="submit" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
                    <i class="fas fa-sync fa-sm text-white-50 mr-1"></i> Run Scraper
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Status Alert -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- System Status Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <h5>Scraper Status</h5>
                    <p>
                        @if($isScraperRunning)
                            <span class="scraper-status scraper-status-running"></span> 
                            <span class="text-info">Running</span>
                        @else
                            <span class="scraper-status scraper-status-idle"></span>
                            <span class="text-success">Idle</span>
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <h5>Last Scrape</h5>
                    <p>
                        @if($lastScrapeTime)
                            {{ $lastScrapeTime->diffForHumans() }}
                            <small class="text-muted">({{ $lastScrapeTime->format('M d, Y H:i:s') }})</small>
                        @else
                            Never
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <h5>Queue Status</h5>
                    <p>
                        <span class="badge badge-primary">{{ $pendingJobs }} Pending</span>
                        <span class="badge badge-danger">{{ $failedJobs }} Failed</span>
                    </p>
                </div>
                <div class="mb-3">
                    <h5>Quick Actions</h5>
                    <div class="btn-group">
                        <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-list-alt mr-1"></i> View Logs
                        </a>
                        <a href="{{ route('admin.tasks') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-tasks mr-1"></i> Manage Tasks
                        </a>
                        <a href="{{ route('admin.status') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-server mr-1"></i> System Status
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Total Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-basket fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Categories</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($categoryCounts) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-folder fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Rate Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Success Rate
                        </div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                @php
                                    $totalLogs = array_sum($logTypeCounts);
                                    $successRate = $totalLogs > 0 ? round(($logTypeCounts['success'] ?? 0) / $totalLogs * 100) : 0;
                                @endphp
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $successRate }}%</div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $successRate }}%"
                                        aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Failed Jobs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Failed Jobs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $failedJobs }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Products by Category Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Products by Category</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="productsBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs by Type Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Logs by Type</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie">
                    <canvas id="logsDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Logs Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recent Logs</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Message</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $log->type === 'success' ? 'success' : ($log->type === 'error' ? 'danger' : ($log->type === 'warning' ? 'warning' : 'info')) }}">
                                    {{ ucfirst($log->type) }}
                                </span>
                            </td>
                            <td>{{ $log->category ?? 'All' }}</td>
                            <td>{{ Str::limit($log->message, 80) }}</td>
                            <td>{{ $log->formatted_occurred_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No logs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('admin.logs') }}" class="btn btn-primary">View All Logs</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Products by Category Chart
var ctx = document.getElementById("productsBarChart");
var categoriesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($categoryCounts)) !!},
        datasets: [{
            label: "Products",
            backgroundColor: "#4e73df",
            hoverBackgroundColor: "#2e59d9",
            borderColor: "#4e73df",
            data: {!! json_encode(array_values($categoryCounts)) !!},
        }],
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    maxTicksLimit: 6
                },
                maxBarThickness: 25,
            }],
            yAxes: [{
                ticks: {
                    min: 0,
                    max: Math.max(...Object.values({!! json_encode($categoryCounts) !!})) + 5,
                    maxTicksLimit: 5,
                    padding: 10,
                },
                gridLines: {
                    color: "rgb(234, 236, 244)",
                    zeroLineColor: "rgb(234, 236, 244)",
                    drawBorder: false,
                    borderDash: [2],
                    zeroLineBorderDash: [2]
                }
            }],
        },
        legend: {
            display: false
        },
        tooltips: {
            titleMarginBottom: 10,
            titleFontColor: '#6e707e',
            titleFontSize: 14,
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
        },
    }
});

// Logs by Type Chart
var logsCtx = document.getElementById("logsDonutChart");
var logsChart = new Chart(logsCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_map('ucfirst', array_keys($logTypeCounts))) !!},
        datasets: [{
            data: {!! json_encode(array_values($logTypeCounts)) !!},
            backgroundColor: ['#1cc88a', '#4e73df', '#f6c23e', '#e74a3b'],
            hoverBackgroundColor: ['#17a673', '#2e59d9', '#dda20a', '#be2617'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
        },
        legend: {
            display: true,
            position: 'bottom'
        },
        cutoutPercentage: 70,
    },
});
</script>
@endsection