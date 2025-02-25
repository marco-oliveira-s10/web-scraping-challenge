@extends('admin.layouts.app')

@section('title', 'System Status')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Service Status</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($services as $service => $status)
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-{{ $status ? 'success' : 'danger' }}">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-{{ $status ? 'success' : 'danger' }} text-uppercase mb-1">
                                            {{ $service }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $status ? 'Online' : 'Offline' }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-{{ $status ? 'check' : 'times' }}-circle fa-2x text-{{ $status ? 'success' : 'danger' }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
        </div>
        <div class="card-body">
            <table class="table">
                @foreach($systemInfo as $key => $value)
                    <tr>
                        <td><strong>{{ $key }}</strong></td>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection