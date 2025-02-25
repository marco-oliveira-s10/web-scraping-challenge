@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div>
            <button type="button" class="d-sm-inline-block btn btn-primary shadow-sm" data-toggle="modal" data-target="#scrapeModal">
                <i class="fas fa-spider fa-sm text-white-50 mr-1"></i> Run Scraper
            </button>
            <a href="{{ route('admin.logs') }}" class="d-sm-inline-block btn btn-secondary shadow-sm ml-2">
                <i class="fas fa-clipboard-list fa-sm text-white-50 mr-1"></i> View Logs
            </a>
        </div>
    </div>

    <!-- Scraper Status Alert -->
    @if($isScraperRunning)
        <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
            <div>
                <i class="fas fa-sync fa-spin mr-2"></i> 
                <strong>Scraper is currently running.</strong> The system is collecting product data.
            </div>
            <div class="text-right">
                <span class="badge badge-primary">Started: {{ $lastScrapeTime ? $lastScrapeTime->diffForHumans() : 'Unknown' }}</span>
            </div>
        </div>
    @elseif($lastScrapeTime)
        <div class="alert alert-success d-flex justify-content-between align-items-center mb-4">
            <div>
                <i class="fas fa-check-circle mr-2"></i> 
                <strong>Scraper is idle.</strong> Last run completed successfully.
            </div>
            <div class="text-right">
                <span class="badge badge-success">Last run: {{ $lastScrapeTime->diffForHumans() }}</span>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Content Row - Stats Cards -->
    <div class="row">
        <!-- Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($productCount) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
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

        <!-- Pending Jobs Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pending Jobs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingJobs }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
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

    <!-- Content Row - Charts -->
    <div class="row">
        <!-- Products by Category Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Products by Category</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="{{ route('admin.products.index') }}">View All Products</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#scrapeModal">Run Scraper</a>
                        </div>
                    </div>
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
                    <h6 class="m-0 font-weight-bold text-primary">Log Statistics</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="{{ route('admin.logs') }}">View All Logs</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('admin.logs', ['filter' => 'error']) }}">View Error Logs</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="logsPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Success
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Info
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Warning
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Error
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row - Activity and Logs -->
    <div class="row">
        <!-- Recent Logs -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
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
                                        @if($log->type === 'success')
                                            <span class="badge badge-success">Success</span>
                                        @elseif($log->type === 'error')
                                            <span class="badge badge-danger">Error</span>
                                        @elseif($log->type === 'warning')
                                            <span class="badge badge-warning">Warning</span>
                                        @else
                                            <span class="badge badge-info">Info</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->category ?? 'N/A' }}</td>
                                    <td class="text-truncate" style="max-width: 200px;">{{ Str::limit($log->message, 50) }}</td>
                                    <td>{{ $log->formatted_occurred_at }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No log entries found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-primary">View All Logs</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Section -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                </div>
                <div class="card-body">
                    <!-- Queue Status -->
                    <h5 class="font-weight-bold">Queue Status</h5>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Pending Jobs:</span>
                            <span class="badge {{ $pendingJobs > 0 ? 'badge-info' : 'badge-success' }}">{{ $pendingJobs }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Failed Jobs:</span>
                            <span class="badge {{ $failedJobs > 0 ? 'badge-danger' : 'badge-success' }}">{{ $failedJobs }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Queue Driver:</span>
                            <span class="badge badge-secondary">{{ config('queue.default') }}</span>
                        </div>
                    </div>

                    <!-- Scraper Status -->
                    <h5 class="font-weight-bold">Scraper Status</h5>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Status:</span>
                            <span class="badge {{ $isScraperRunning ? 'badge-info' : 'badge-success' }}">
                                {{ $isScraperRunning ? 'Running' : 'Idle' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Last Run:</span>
                            <span>{{ $lastScrapeTime ? $lastScrapeTime->format('M d, Y H:i:s') : 'Never' }}</span>
                        </div>
                    </div>

                    <!-- Products Info -->
                    <h5 class="font-weight-bold">Product Information</h5>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Products:</span>
                            <span>{{ number_format($productCount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Categories:</span>
                            <span>{{ count($categoryCounts) }}</span>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('admin.status') }}" class="btn btn-sm btn-primary">Full System Status</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scrape Modal -->
<div class="modal fade" id="scrapeModal" tabindex="-1" role="dialog" aria-labelledby="scrapeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scrapeModalLabel">Run Scraper</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.run-scrape') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="category">Select Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="all">All Categories</option>
                            @foreach($categoryCounts as $category => $count)
                                <option value="{{ $category }}">{{ $category }} ({{ $count }} products)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> This will start a background job to scrape products from the selected category or all categories.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-spider mr-1"></i> Start Scraping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
// Set new default font family and font color to match Bootstrap's defaults
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Products by Category
const categoryLabels = @json(array_keys($categoryCounts));
const categoryData = @json(array_values($categoryCounts));

const ctx = document.getElementById("productsBarChart");
const productsBarChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: categoryLabels,
        datasets: [{
            label: "Products",
            backgroundColor: "#4e73df",
            hoverBackgroundColor: "#2e59d9",
            borderColor: "#4e73df",
            data: categoryData,
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
                time: {
                    unit: 'category'
                },
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

// Logs by Type
const logTypes = @json(array_keys($logTypeCounts));
const logCounts = @json(array_values($logTypeCounts));

// Map log types to colors
const logColors = logTypes.map(type => {
    switch(type) {
        case 'success':
            return '#1cc88a';
        case 'error':
            return '#e74a3b';
        case 'warning':
            return '#f6c23e';
        case 'info':
        default:
            return '#36b9cc';
    }
});

const ctxPie = document.getElementById("logsPieChart");
const logsPieChart = new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: logTypes,
        datasets: [{
            data: logCounts,
            backgroundColor: logColors,
            hoverBackgroundColor: logColors.map(color => color + 'dd'),
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
            display: false
        },
        cutoutPercentage: 80,
    },
});
</script>
@endsection