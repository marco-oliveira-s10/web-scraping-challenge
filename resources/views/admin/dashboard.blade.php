@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <form action="{{ route('admin.run-scrape') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Run Scraper
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Status Overview -->
<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card h-100 dashboard-card">
            <div class="card-body">
                <h5 class="card-title text-primary">Product Count</h5>
                <p class="card-text display-4">{{ $productCount }}</p>
                <p class="card-text text-muted">
                    <i class="fas fa-boxes"></i> Total products in database
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100 dashboard-card">
            <div class="card-body">
                <h5 class="card-title text-info">Queue Status</h5>
                <p class="card-text display-4">{{ $pendingJobs }}</p>
                <p class="card-text text-muted">
                    <i class="fas fa-tasks"></i> Pending jobs
                </p>
                @if($failedJobs > 0)
                    <span class="badge bg-danger">{{ $failedJobs }} Failed</span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100 dashboard-card">
            <div class="card-body">
                <h5 class="card-title text-success">Scraper Status</h5>
                @if($isScraperRunning)
                    <div class="d-flex align-items-center">
                        <div class="spinner-border text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="card-text mb-0">Running</p>
                    </div>
                @else
                    <p class="card-text">Idle</p>
                @endif
                <p class="card-text text-muted mt-2">
                    <i class="fas fa-clock"></i> 
                    Last run: {{ $lastScrapeTime ? $lastScrapeTime->diffForHumans() : 'Never' }}
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100 dashboard-card">
            <div class="card-body">
                <h5 class="card-title text-warning">Log Activity</h5>
                <div class="d-flex justify-content-around">
                    <div class="text-center">
                        <span class="d-block h4">{{ $logTypeCounts['success'] ?? 0 }}</span>
                        <span class="badge bg-success">Success</span>
                    </div>
                    <div class="text-center">
                        <span class="d-block h4">{{ $logTypeCounts['error'] ?? 0 }}</span>
                        <span class="badge bg-danger">Errors</span>
                    </div>
                    <div class="text-center">
                        <span class="d-block h4">{{ $logTypeCounts['warning'] ?? 0 }}</span>
                        <span class="badge bg-warning">Warnings</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Category Distribution Chart -->
    <div class="col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Products by Category</h5>
                <div style="height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Logs -->
    <div class="col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Recent Activity</h5>
                <div class="table-responsive" style="height: 300px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogs as $log)
                                <tr>
                                    <td>
                                        <span class="badge {{ $log->status_badge_class }}">{{ ucfirst($log->type) }}</span>
                                    </td>
                                    <td>{{ Str::limit($log->message, 60) }}</td>
                                    <td>{{ $log->occurred_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No recent logs</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-outline-secondary">View All Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card dashboard-card">
            <div class="card-header">
                Quick Actions
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <form action="{{ route('admin.run-scrape') }}" method="POST">
                            @csrf
                            <div class="d-grid">
                                <button type="