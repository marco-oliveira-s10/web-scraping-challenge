@extends('admin.layouts.app')

@section('title', 'Product Details')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Product Details</h1>
    <div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-arrow-left"></i>
            </span>
            <span class="text">Back to Products</span>
        </a>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary btn-icon-split ml-2">
            <span class="icon text-white-50">
                <i class="fas fa-edit"></i>
            </span>
            <span class="text">Edit Product</span>
        </a>
    </div>
</div>

<!-- Product Details -->
<div class="row">
    <!-- Product Image -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid mb-3" style="max-height: 300px;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center p-5 mb-3" style="height: 300px;">
                        <i class="fas fa-image fa-5x text-secondary"></i>
                    </div>
                @endif
                <span class="badge badge-pill badge-primary">{{ $product->category }}</span>
            </div>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
            </div>
            <div class="card-body">
                <h2 class="card-title text-primary mb-3">{{ $product->name }}</h2>
                <h3 class="card-subtitle mb-4 text-success font-weight-bold">${{ number_format($product->price, 2) }}</h3>
                
                <div class="mb-4">
                    <h5 class="font-weight-bold">Description</h5>
                    <p>{{ $product->description ?? 'No description available' }}</p>
                </div>
                
                <div class="mb-4">
                    <h5 class="font-weight-bold">Details</h5>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th style="width: 150px;">Product ID</th>
                                <td>{{ $product->product_id }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>{{ $product->category }}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $product->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $product->updated_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Product Modal Trigger -->
<div class="text-right mb-4">
    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteProductModal">
        <i class="fas fa-trash"></i> Delete Product
    </button>
</div>

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" role="dialog" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product? This action cannot be undone.
                <p class="font-weight-bold mt-3">{{ $product->name }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection