@extends('admin.layouts.app')

@section('title', 'Task Management')

@section('content')
<div class="container-fluid">
    <!-- Status Overview -->
    <div class="row">
       
    </div>

    <!-- Scheduled Tasks -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Scheduled Tasks</h6>
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
        url: '{{ route("admin.tasks.run") }}',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            task: taskId
        },
        success: function(response) {
            showToast('success', 'Task started successfully');
            setTimeout(function() {
                location.reload();
            }, 2000);
        },
        error: function(xhr) {
            let errorMsg = 'Failed to start task';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg += ': ' + xhr.responseJSON.error;
            }
            showToast('error', errorMsg);
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