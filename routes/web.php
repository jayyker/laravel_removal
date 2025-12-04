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

// Renumber products route
Route::get('/renumber-products', function() {
    // Get all products sorted by current ID
    $products = \DB::table('products')->orderBy('id')->get();
    
    if ($products->isEmpty()) {
        return "No products to renumber.";
    }
    
    // Backup data to array
    $backup = [];
    foreach ($products as $product) {
        $backup[] = [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'description' => $product->description,
            'created_at' => $product->created_at,
            'updated_at' => now(),
        ];
    }
    
    // Truncate table
    \DB::statement('SET FOREIGN_KEY_CHECKS=0');
    \DB::table('products')->truncate();
    
    // Reinsert with new sequential IDs
    \DB::table('products')->insert($backup);
    
    // Set auto-increment
    $nextId = count($backup) + 1;
    \DB::statement("ALTER TABLE products AUTO_INCREMENT = $nextId");
    \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    return "Products renumbered! Now have IDs: " . implode(', ', range(1, count($backup)));
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
