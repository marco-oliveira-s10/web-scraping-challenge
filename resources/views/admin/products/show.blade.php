@extends('admin.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <img src="{{ $product->image_url }}" class="img-fluid" alt="{{ $product->name }}">
                </div>
                <div class="col-md-8">
                    <h2>{{ $product->name }}</h2>
                    <p><strong>Category:</strong> {{ $product->category }}</p>
                    <p><strong>Price:</strong> R$ {{ number_format($product->price, 2) }}</p>
                    <p><strong>Description:</strong> {{ $product->description ?? 'No description' }}</p>
                    <p><strong>Scraped At:</strong> {{ $product->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection