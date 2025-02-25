<!-- resources/views/products/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Product Catalog</h1>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('products.scrape') }}" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Scrape All Products
            </a>
            <a href="{{ route('products.categories') }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-list"></i> Available Categories
            </a>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <!-- Category Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('products.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="category" class="col-form-label fw-bold">Filter by Category:</label>
                </div>
                <div class="col-md-4">
                    <select name="category" id="category" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $selectedCategory == 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $selectedCategory == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-secondary">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="alert alert-info">
            <h4 class="alert-heading">No products found!</h4>
            <p>There are no products in the database matching your criteria. Click on "Scrape All Products" to fetch data from the e-commerce website.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($products as $product)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: contain;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span class="text-muted">No Image</span>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            
                            @if($product->category)
                                <span class="badge bg-secondary mb-2">{{ $product->category }}</span>
                            @endif
                            
                            @if($product->description)
                                <p class="card-text text-muted small">{{ Str::limit($product->description, 100) }}</p>
                            @endif
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary m-0">${{ number_format($product->price, 2) }}</span>
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="d-flex justify-content-center mt-4">
            {{ $products->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection