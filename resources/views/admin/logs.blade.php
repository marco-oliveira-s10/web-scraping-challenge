@extends('admin.layouts.app')

@section('title', 'System Logs')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">System Logs</h1>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.logs') }}" method="GET" class="form-inline">
            <div class="form-group mb-2 mr-3">
                <label for="filter" class="sr-only">Log Type</label>
                <select class="form-control" id="filter" name="filter">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="info" {{ $filter === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="success" {{ $filter === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="warning" {{ $filter === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="error" {{ $filter === 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <div class="form-group mb-2 mr-3">
                <label for="category" class="sr-only">Category</label>
                <select class="form-control" id="category" name="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-2">Filter</button>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Log Entries</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Message</th>
                        <th>Context</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $log->type === 'success' ? 'success' : ($log->type === 'error' ? 'danger' : ($log->type === 'warning' ? 'warning' : 'info')) }}">
                                    {{ ucfirst($log->type) }}
                                </span>
                            </td>
                            <td>{{ $log->category ?? 'All' }}</td>
                            <td>{{ $log->message }}</td>
                            <td>
                                @if($log->context)
                                    <button class="btn btn-sm btn-outline-info" type="button" data-toggle="collapse" data-target="#context-{{ $log->id }}">
                                        View Details
                                    </button>
                                    <div class="collapse mt-2" id="context-{{ $log->id }}">
                                        <div class="card card-body">
                                            <pre>{{ json_encode(json_decode($log->context), JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td>{{ $log->formatted_occurred_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No logs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $logs->appends(['filter' => $filter, 'category' => $category])->links() }}
        </div>
    </div>
</div>
@endsection