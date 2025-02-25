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

            @if ($logs->lastPage() > 1)
            <nav aria-label="Navegação de logs">
                <ul class="pagination justify-content-center">
                    {{-- Botão Anterior --}}
                    @if ($logs->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">&laquo;</span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $logs->previousPageUrl() }}">&laquo;</a>
                    </li>
                    @endif

                    {{-- Números das páginas --}}
                    @php
                    $start = max(1, $logs->currentPage() - 2);
                    $end = min($logs->lastPage(), $logs->currentPage() + 2);
                    @endphp

                    @if($start > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $logs->url(1) }}">1</a>
                    </li>
                    @if($start > 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        @if ($logs->currentPage() == $page)
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                        @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $logs->url($page) }}">{{ $page }}</a>
                        </li>
                        @endif
                        @endfor

                        @if($end < $logs->lastPage())
                            @if($end < $logs->lastPage() - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $logs->url($logs->lastPage()) }}">{{ $logs->lastPage() }}</a>
                                </li>
                                @endif

                                {{-- Botão Próximo --}}
                                @if ($logs->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $logs->nextPageUrl() }}">&raquo;</a>
                                </li>
                                @else
                                <li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>
                                @endif
                </ul>
            </nav>
            @endif
        </div>
    </div>
</div>
@endsection