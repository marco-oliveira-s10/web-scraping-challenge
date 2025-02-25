<!-- resources/views/products/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => $product->category]) }}">{{ $product->category }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="card mb-4 shadow">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="bg-light rounded p-3 text-center">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" class="img-fluid" alt="{{ $product->name }}" style="max-height: 400px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 py-5">
                                <span class="text-muted">No Image Available</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-7">
                    <h1 class="mb-3">{{ $product->name }}</h1>
                    
                    @if($product->category)
                        <span class="badge bg-secondary mb-3">{{ $product->category }}</span>
                    @endif
                    
                    <div class="mb-3">
                        <h2 class="text-primary">${{ number_format($product->price, 2) }}</h2>
                    </div>
                    
                    @if($product->description)
                        <div class="mb-4">
                            <h5>Description</h5>
                            <p>{{ $product->description }}</p>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <p class="text-muted mb-1"><small>Product ID: {{ $product->product_id }}</small></p>
                        <p class="text-muted mb-0"><small>Last updated: {{ $product->updated_at->format('M d, Y H:i') }}</small></p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection