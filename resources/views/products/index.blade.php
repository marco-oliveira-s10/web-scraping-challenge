<!-- resources/views/products/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col">
            <h1>Lista de Produtos</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('products.scrape') }}" class="btn btn-primary">
                Coletar Produtos
            </a>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="alert alert-info">
            Nenhum produto encontrado. Clique em "Coletar Produtos" para iniciar o scraping.
        </div>
    @else
        <div class="row">
            @foreach($products as $product)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">
                                <strong>Preço:</strong> R$ {{ number_format($product->price, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection