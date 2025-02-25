<!-- resources/views/products/categories.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Available Categories</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col">
            <h1>Available Categories</h1>
            <p class="text-muted">These categories are available on the e-commerce website for scraping.</p>
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

    @if(empty($availableCategories))
        <div class="alert alert-info">
            <h4 class="alert-heading">No categories found!</h4>
            <p>We couldn't find any categories on the e-commerce website. This might be a temporary issue. Please try again later.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($availableCategories as $category)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">{{ $category }}</h5>
                            <p class="card-text text-muted">Click the button below to scrape products in this category.</p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-grid">
                                <a href="{{ route('products.scrape', ['category' => $category]) }}" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Scrape {{ $category }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    
    <div class="mt-4">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
</div>
@endsection