@extends('admin.layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">System Logs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Message</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <span class="badge badge-{{ 
                                        $log->type === 'success' ? 'success' : 
                                        ($log->type === 'error' ? 'danger' : 
                                        ($log->type === 'warning' ? 'warning' : 'info')) 
                                    }}">
                                        {{ ucfirst($log->type) }}
                                    </span>
                                </td>
                                <td>{{ $log->category ?? 'All' }}</td>
                                <td>{{ $log->message }}</td>
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
            
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection