<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::redirect('/', '/products');

// Product routes for React frontend
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// React app route
Route::get('/react', function () {
    return view('react');
});

// Database test route
Route::get('/test-db', function () {
    try {
        \DB::connection()->getPdo();
        echo "Database connected successfully!<br>";
        
        $count = \DB::table('products')->count();
        echo "Products in database: " . $count . "<br>";
        
        if ($count == 0) {
            echo "Database is empty. Adding sample data...<br>";
            
            \DB::table('products')->insert([
                ['name' => 'Test Product', 'sku' => 'TEST001', 'price' => 100, 'quantity' => 10, 'description' => 'Test description', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Another Product', 'sku' => 'TEST002', 'price' => 200, 'quantity' => 5, 'description' => 'Another test', 'created_at' => now(), 'updated_at' => now()],
            ]);
            
            echo "Sample data added!";
        }
        
    } catch (\Exception $e) {
        echo "Database connection failed: " . $e->getMessage();
    }
});
