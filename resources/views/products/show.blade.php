@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            @if($product->category)
            <li class="breadcrumb-item">
                <a href="{{ route('products.index', ['category' => $product->category]) }}">
                    {{ $product->category }}
                </a>
            </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <div class="product-detail-image mb-4">
                @if($product->image_url)
                <img src="{{ $product->image_url }}"
                    class="img-fluid rounded shadow-sm"
                    alt="{{ $product->name }}"
                    style="max-height: 400px; width: 100%; object-fit: cover;">
                @else
                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                    <span class="text-muted">No Image Available</span>
                </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="product-details">
                @if($product->category)
                <span class="badge bg-primary mb-2">{{ $product->category }}</span>
                @endif

                <h1 class="mb-3">{{ $product->name }}</h1>

                <div class="price-section mb-3">
                    <h2 class="text-primary">
                        R$ {{ number_format($product->price, 2, ',', '.') }}
                    </h2>
                </div>

                @if($product->description)
                <div class="description mb-4">
                    <h5>Description</h5>
                    <p class="text-muted">{{ $product->description }}</p>
                </div>
                @endif

                <div class="product-meta text-muted">
                    @if($product->product_id)
                    <p class="mb-1">
                        <small>Product ID: {{ $product->product_id }}</small>
                    </p>
                    @endif
                    <p>
                        <small>Last updated: {{ $product->updated_at->format('M d, Y H:i') }}</small>
                    </p>
                </div>

                <div class="actions mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                    @if($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category]) }}"
                        class="btn btn-primary">
                        <i class="fas fa-tag me-1"></i> More in {{ $product->category }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection