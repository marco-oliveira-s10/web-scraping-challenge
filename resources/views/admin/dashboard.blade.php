@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <form action="{{ route('admin.products.scrape') }}" method="POST" class="d-inline">
        @csrf
        <div class="input-group">

            <div class="input-group">
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
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">                   
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