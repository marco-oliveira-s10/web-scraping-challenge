@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h1 class="mb-0">Our Product Catalog</h1>
        <p class="text-muted">Browse and explore our collection</p>
    </div>
    <div class="col-md-6">
        <form method="get" action="{{ route('products.index') }}" class="d-flex">
            <select name="category" class="form-select me-2" onchange="this.form.submit()">
                <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-sync"></i>
            </a>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($products as $product)
        <div class="col-md-4 col-sm-6">
            <div class="card product-card h-100">
                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
                <div class="card-body">
                    <h5 class="card-title mb-2">{{ $product->name }}</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 text-primary mb-0">
                            R$ {{ number_format($product->price, 2, ',', '.') }}
                        </span>
                        @if($product->category)
                            <span class="badge">{{ $product->category }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary w-100">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-box-open fa-3x mb-3">]
                <h4>No Products Found</h4>
                <p>There are no products available in this category.</p>
            </div>
        </div>
    @endforelse
</div>

@if($products->total() > 0)
    <div class="mt-4">
        {{ $products->appends(['category' => $category])->links() }}
    </div>
@endif
@endsection