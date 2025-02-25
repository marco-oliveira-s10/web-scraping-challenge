@extends('admin.layouts.app')

@section('title', 'System Status')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">System Status</h1>
    <form action="{{ route('admin.clear-cache') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="d-none d-sm-inline-block btn btn-danger shadow-sm">
            <i class="fas fa-broom fa-sm text-white-50 mr-1"></i> Clear Cache
        </button>
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

<!-- Service Status -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Service Status</h6>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($services as $service => $status)
                <div class="col-md-4 mb-4">
                    <div class="card {{ $status ? 'border-left-success' : 'border-left-danger' }} shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold {{ $status ? 'text-success' : 'text-danger' }} text-uppercase mb-1">
                                        {{ $service }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        @if($status)
                                            <span class="text-success">Online</span>
                                        @else
                                            <span class="text-danger">Offline</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if($status)
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle fa-2x text-danger"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Configuration Settings -->
<div class="row">
    <!-- Database Config -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Database Configuration</h6>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Connection</th>
                            <td>{{ $dbConnection }}</td>
                        </tr>
                        <tr>
                            <th>Queue Table</th>
                            <td>{{ config('queue.connections.database.table', 'jobs') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cache Config -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cache Configuration</h6>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Driver</th>
                            <td>{{ $cacheDriver }}</td>
                        </tr>
                        <tr>
                            <th>Prefix</th>
                            <td>{{ config('cache.prefix', 'laravel_cache') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($systemInfo as $key => $value)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Queue Configuration -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Queue Configuration</h6>
    </div>
    <div class="card-body">
        <table class="table">
            <tbody>
                <tr>
                    <th>Driver</th>
                    <td>{{ $queueConnection }}</td>
                </tr>
                <tr>
                    <th>Default Queue</th>
                    <td>{{ config('queue.connections.'.$queueConnection.'.queue', 'default') }}</td>
                </tr>
                <tr>
                    <th>Retry After</th>
                    <td>{{ config('queue.connections.'.$queueConnection.'.retry_after', 90) }} seconds</td>
                </tr>
                <tr>
                    <th>After Commit</th>
                    <td>{{ config('queue.connections.'.$queueConnection.'.after_commit', false) ? 'Yes' : 'No' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Scheduler -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Scheduler Status</h6>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-0">
                    <strong>Status:</strong>
                    @if($isSchedulerRunning)
                        <span class="badge badge-success">Running</span>
                    @else
                        <span class="badge badge-danger">Not Running</span>
                    @endif
                </p>
                @if(!$isSchedulerRunning)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        The scheduler is not running. Please make sure you have set up a cron job to run <code>php artisan schedule:run</code> every minute.
                    </div>
                @endif
            </div>
            <div class="col-md-4 text-center">
                @if($isSchedulerRunning)
                    <i class="fas fa-calendar-check fa-4x text-success"></i>
                @else
                    <i class="fas fa-calendar-times fa-4x text-danger"></i>
                @endif
            </div>
        </div>
        <div class="mt-4">
            <h6 class="font-weight-bold">Scheduled Commands</h6>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><code>ScrapeProductsJob</code></span>
                    <span>Daily at 00:00</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><code>scraper:categories</code></span>
                    <span>Daily at 02:00</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><code>scraper:cleanup</code></span>
                    <span>Weekly</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection