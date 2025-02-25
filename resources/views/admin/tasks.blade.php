@extends('admin.layouts.app')

@section('title', 'Task Management')

@section('content')
<div class="container-fluid">
    <!-- Status Overview -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pending Jobs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($pendingJobs) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Failed Jobs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($failedJobs) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Batches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($jobBatches) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Next Scheduled Run
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ \Carbon\Carbon::parse(Cache::get('next_scheduled_run'))->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduled Tasks -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Scheduled Tasks</h6>
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#runTaskModal">
                <i class="fas fa-play fa-sm"></i> Run Task Now
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Schedule</th>
                            <th>Last Run</th>
                            <th>Next Run</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduledTasks as $task)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $task['name'] }}</div>
                                <small class="text-muted">{{ $task['description'] }}</small>
                            </td>
                            <td>{{ $task['schedule'] }}</td>
                            <td>{{ $task['last_run'] ?? 'Never' }}</td>
                            <td>{{ $task['next_run'] }}</td>
                            <td>
                                <span class="badge badge-{{ $task['status'] === 'running' ? 'info' : ($task['status'] === 'completed' ? 'success' : 'warning') }}">
                                    {{ ucfirst($task['status']) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="runTask('{{ $task['id'] }}')">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="toggleTask('{{ $task['id'] }}')">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Jobs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pending Jobs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="pendingJobsTable">
                    <thead>
                        <tr>
                            <th>Queue</th>
                            <th>Job</th>
                            <th>Attempts</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingJobs as $job)
                        <tr>
                            <td>{{ $job->queue }}</td>
                            <td>
                                <div>{{ json_decode($job->payload)->displayName }}</div>
                                <small class="text-muted">{{ Str::limit(json_decode($job->payload)->data->command, 100) }}</small>
                            </td>
                            <td>{{ $job->attempts }}</td>
                            <td>{{ Carbon\Carbon::parse($job->created_at)->diffForHumans() }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteJob('{{ $job->id }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No pending jobs</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Failed Jobs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-danger">Failed Jobs</h6>
            <div>
                <button class="btn btn-sm btn-warning" onclick="retryAllFailed()">
                    <i class="fas fa-redo"></i> Retry All
                </button>
                <button class="btn btn-sm btn-danger" onclick="clearAllFailed()">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="failedJobsTable">
                    <thead>
                        <tr>
                            <th>UUID</th>
                            <th>Connection</th>
                            <th>Queue</th>
                            <th>Failed At</th>
                            <th>Exception</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failedJobs as $job)
                        <tr>
                            <td>{{ $job->uuid }}</td>
                            <td>{{ $job->connection }}</td>
                            <td>{{ $job->queue }}</td>
                            <td>{{ Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="showException('{{ $job->uuid }}')">
                                    View Error
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="retryJob('{{ $job->uuid }}')">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteFailedJob('{{ $job->uuid }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No failed jobs</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Run Task Modal -->
<div class="modal fade" id="runTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Run Task</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="runTaskForm" action="{{ route('admin.tasks.run') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Task</label>
                        <select class="form-control" name="task">
                            @foreach($scheduledTasks as $task)
                                <option value="{{ $task['id'] }}">{{ $task['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Run Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Inicialização das DataTables
    $(document).ready(function() {
        $('#pendingJobsTable').DataTable({
            order: [
                [3, 'desc']
            ],
            pageLength: 10,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        $('#failedJobsTable').DataTable({
            order: [
                [3, 'desc']
            ],
            pageLength: 10
        });

        // Atualização automática a cada 30 segundos
        setInterval(function() {
            updateTaskStatus();
        }, 30000);
    });

    // Funções para gerenciamento de tasks
    function runTask(taskId) {
        if (!confirm('Are you sure you want to run this task now?')) {
            return;
        }

        $.ajax({
            url: '/admin/tasks/' + taskId + '/run',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('success', 'Task started successfully');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                showToast('error', 'Failed to start task: ' + xhr.responseJSON.message);
            }
        });
    }

    function toggleTask(taskId) {
        $.ajax({
            url: '/admin/tasks/' + taskId + '/toggle',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('success', response.message);
                updateTaskStatus();
            },
            error: function(xhr) {
                showToast('error', 'Failed to toggle task: ' + xhr.responseJSON.message);
            }
        });
    }

    function retryJob(uuid) {
        if (!confirm('Are you sure you want to retry this failed job?')) {
            return;
        }

        $.ajax({
            url: '{{ route("admin.retry-job") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                uuid: uuid
            },
            success: function(response) {
                showToast('success', 'Job queued for retry');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                showToast('error', 'Failed to retry job: ' + xhr.responseJSON.message);
            }
        });
    }

    function deleteJob(id) {
        if (!confirm('Are you sure you want to delete this job? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: '/admin/jobs/' + id,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('success', 'Job deleted successfully');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                showToast('error', 'Failed to delete job: ' + xhr.responseJSON.message);
            }
        });
    }

    function retryAllFailed() {
        if (!confirm('Are you sure you want to retry all failed jobs?')) {
            return;
        }

        $.ajax({
            url: '{{ route("admin.retry-all-failed") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('success', 'All failed jobs queued for retry');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                showToast('error', 'Failed to retry jobs: ' + xhr.responseJSON.message);
            }
        });
    }

    function clearAllFailed() {
        if (!confirm('Are you sure you want to clear all failed jobs? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: '{{ route("admin.clear-failed-jobs") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('success', 'All failed jobs have been cleared');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                showToast('error', 'Failed to clear jobs: ' + xhr.responseJSON.message);
            }
        });
    }

    function showException(uuid) {
        $.ajax({
            url: '/admin/jobs/' + uuid + '/exception',
            type: 'GET',
            success: function(response) {
                $('#exceptionModal .modal-body pre').text(response.exception);
                $('#exceptionModal').modal('show');
            },
            error: function(xhr) {
                showToast('error', 'Failed to fetch exception details');
            }
        });
    }

    // Função para atualizar o status das tasks
    function updateTaskStatus() {
        $.ajax({
            url: '/admin/tasks/status',
            type: 'GET',
            success: function(response) {
                response.tasks.forEach(function(task) {
                    updateTaskRow(task);
                });
            }
        });
    }

    // Função para atualizar uma linha da tabela de tasks
    function updateTaskRow(task) {
        const row = $(`tr[data-task-id="${task.id}"]`);
        row.find('.task-status').html(`
        <span class="badge badge-${getStatusBadgeClass(task.status)}">
            ${task.status}
        </span>
    `);
        row.find('.task-last-run').text(task.last_run);
        row.find('.task-next-run').text(task.next_run);
    }

    // Função auxiliar para determinar a classe do badge de status
    function getStatusBadgeClass(status) {
        switch (status) {
            case 'running':
                return 'info';
            case 'completed':
                return 'success';
            case 'failed':
                return 'danger';
            default:
                return 'warning';
        }
    }

    // Função para mostrar toasts de notificação
    function showToast(type, message) {
        const toast = $('<div>').addClass('toast').attr('role', 'alert')
            .html(`
            <div class="toast-header">
                <strong class="mr-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `);

        $('.toast-container').append(toast);
        toast.toast({
            delay: 3000,
            autohide: true
        }).toast('show');
    }
</script>
@endsection