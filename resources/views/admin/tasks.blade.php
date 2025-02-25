@extends('admin.layouts.app')

@section('title', 'Queue Tasks')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pending Jobs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Queue</th>
                            <th>Payload</th>
                            <th>Attempts</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingJobs as $job)
                            <tr>
                                <td>{{ $job->queue }}</td>
                                <td>{{ json_decode($job->payload)->job }}</td>
                                <td>{{ $job->attempts }}</td>
                                <td>{{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No pending jobs</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">Failed Jobs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>UUID</th>
                            <th>Connection</th>
                            <th>Queue</th>
                            <th>Exception</th>
                            <th>Failed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failedJobs as $job)
                            <tr>
                                <td>{{ $job->uuid }}</td>
                                <td>{{ $job->connection }}</td>
                                <td>{{ $job->queue }}</td>
                                <td>{{ Str::limit($job->exception, 100) }}</td>
                                <td>{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No failed jobs</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection