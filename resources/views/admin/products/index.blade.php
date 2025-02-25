@extends('admin.layouts.app')

@section('title', 'Products Management')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Products Management</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Product List</h6>
            <div>

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
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category }}</td>
                            <td>R$ {{ number_format($product->price, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection