@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Empty State Handling --}}
    @if($products->isEmpty())
    <div class="row justify-content-center">
        <div class="col-md-8 text-center my-5">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-box-seam text-muted" viewBox="0 0 16 16">
                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 4.93a.5.5 0 0 0-.196.696c.036.06.405.643.879 1.546l.407.777a.5.5 0 0 0 .659.221l.188-.079c.535-.28 1.185-.467 1.839-.467.654 0 1.304.187 1.839.467l.188.079a.5.5 0 0 0 .659-.221l.407-.777c.474-.903.843-1.486.879-1.546a.5.5 0 0 0-.196-.696l-6.368-3.816a.5.5 0 0 0-.372 0L1 5.591c-.133.697.05 1.458.419 2.73.367 1.27.957 2.835 1.536 4.452.18.528.348 1.055.51 1.58a.5.5 0 0 0 .658.314c1.415-.54 2.507-1.532 3.251-2.632.735-1.1 1.146-2.363 1.146-3.677 0-1.194-.346-2.339-1.007-3.363z" />
                    <path d="M7.5 1.435V7.97a2.53 2.53 0 0 1-1.286 2.18l-.735.44a2.53 2.53 0 0 0-1.267 2.207V16h8V8.804a2.53 2.53 0 0 0-1.267-2.207l-.735-.44A2.53 2.53 0 0 1 9.5 4.57V1.435a.5.5 0 0 0-.372-.495 2.53 2.53 0 0 0-1.656 0 .5.5 0 0 0-.372.495z" />
                </svg>
            </div>
            <h2 class="mb-3">No Products Found</h2>
            <p class="text-muted">It seems there are no products available at the moment.</p>
            <div class="mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Refresh Catalog
                </a>
            </div>
        </div>
    </div>
    @else
    {{-- Products Grid --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="display-6 mb-2">Our Product Catalog</h1>
            <p class="lead text-muted">{{ $products->total() }} products available</p>
        </div>
        <div class="col-md-6 text-md-end">
            <form method="get" action="{{ route('products.index') }}" class="d-flex gap-2 align-items-center justify-content-md-end">
                <select name="category" class="form-select flex-grow-1" onchange="this.form.submit()">
                    <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-secondary" title="Refresh">
                    <i class="fas fa-sync"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @foreach($products as $product)
        <div class="col-md-4 col-sm-6">
            <div class="card product-card h-100 border-0 shadow-sm hover-lift">
                <div class="product-card-image-container">
                    <img
                        src="{{ $product->image_url }}"
                        class="card-img-top product-card-image"
                        alt="{{ $product->name }}"
                        onerror="this.src='{{ asset('images/placeholder-product.png') }}'">
                    @if($product->category)
                    <span class="badge bg-primary product-category-badge">
                        {{ $product->category }}
                    </span>
                    @endif
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2 text-truncate">{{ $product->name }}</h5>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="h5 text-primary mb-0">
                            R$ {{ number_format($product->price, 2, ',', '.') }}
                        </span>
                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-primary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->total() > 0)
    <div class="mt-4 d-flex justify-content-center">
        {{ $products->appends(['category' => $category])->links() }}
    </div>
    @endif
    @endif
</div>

@push('styles')
<style>
    .product-card-image-container {
        position: relative;
        overflow: hidden;
        height: 250px;
    }

    .product-card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }

    .product-category-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0.9;
    }

    .product-card-image-container img:hover {
        transform: scale(1.1);
    }
</style>
@endpush
@endsection