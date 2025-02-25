@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Products</h1>
    <form action="{{ route('admin.run-scrape') }}" method="POST" class="d-inline">
        @csrf
        <div class="input-group">
            <select name="category" class="form-control mr-2">
                <option value="all">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
            <div class="input-group-append">
                <button type="submit" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
                    <i class="fas fa-sync fa-sm text-white-50 mr-1"></i> Update Products
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Status Alert -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
            <div class="form-group mb-2 mr-3">
                <label for="category" class="sr-only">Category</label>
                <select class="form-control" id="category" name="category">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-2">Filter</button>
        </form>
    </div>
</div>

<!-- Products Grid -->
<div class="row">
    @forelse($products as $product)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow">
                <div class="position-relative">
                    @if($product->image_url)
                        <img class="card-img-top" src="{{ $product->image_url }}" alt="{{ $product->name }}" style="height: 200px; object-fit: contain; padding: 10px;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image fa-4x text-secondary"></i>
                        </div>
                    @endif
                    <div class="position-absolute" style="top: 10px; right: 10px;">
                        <span class="badge badge-pill badge-primary">{{ $product->category }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $product->name }}</h5>
                    <h6 class="card-subtitle mb-2 text-success font-weight-bold">${{ number_format($product->price, 2) }}</h6>
                    <p class="card-text text-muted small">
                        @if($product->description)
                            {{ Str::limit($product->description, 100) }}
                        @else
                            <em>No description available</em>
                        @endif
                    </p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Last updated: {{ $product->updated_at->diffForHumans() }}</small>
                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">
                No products found. Click "Update Products" to start scraping or change your filter criteria.
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $products->appends(['category' => $selectedCategory])->links() }}
</div>
@endsection