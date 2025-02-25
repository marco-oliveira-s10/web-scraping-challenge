<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Http\Request;

class ScrapeProdutos extends Command
{
    protected $signature = 'product:fetch';
    protected $description = 'Fetch products via scraping';

    public function handle()
    {
        $request = new Request();
        $request->merge(['task' => 'product:fetch']);
        
        $productController = app()->make(ProductController::class);
        $productController->scrape($request);
        
        $this->info('Produtos buscados com sucesso');
        return 0;
    }
}