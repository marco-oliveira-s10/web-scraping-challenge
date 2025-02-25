@extends('admin.layouts.app')

@section('title', 'Scraping Categories')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Available Categories for Scraping</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($availableCategories as $category)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $category }}</h5>
                            <form action="{{ route('admin.products.scrape') }}" method="POST">
                                @csrf
                                <input type="hidden" name="category" value="{{ $category }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync"></i> Scrape {{ $category }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">No categories available for scraping.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection