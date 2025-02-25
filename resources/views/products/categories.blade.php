@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1>Product Categories</h1>
        <p class="text-muted">Explore our product range by category</p>
    </div>
</div>

@if(empty($categories))
    <div class="alert alert-info text-center">
        <i class="fas fa-tags fa-3x mb-3"></i>
        <h4>No Categories Available</h4>
        <p>We couldn't find any product categories at the moment.</p>
    </div>
@else
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @foreach($categories as $category)
            <div class="col">
                <div class="card h-100 product-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-tag me-2"></i>{{ $category }}
                        </h5>
                        <p class="card-text text-muted">
                            Browse products in the {{ $category }} category
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="{{ route('products.index', ['category' => $category]) }}" class="btn btn-primary w-100">
                            View {{ $category }} Products
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection