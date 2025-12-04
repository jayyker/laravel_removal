<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>Products in Database</h1>";

try {
    $products = DB::table('products')->orderBy('id')->get();
    
    if ($products->count() == 0) {
        echo "<p>No products found in database.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Name</th>";
        echo "<th style='padding: 8px;'>SKU</th>";
        echo "<th style='padding: 8px;'>Price</th>";
        echo "<th style='padding: 8px;'>Quantity</th>";
        echo "<th style='padding: 8px;'>Description</th>";
        echo "</tr>";
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td style='padding: 8px; font-weight: bold;'>{$product->id}</td>";
            echo "<td style='padding: 8px;'>{$product->name}</td>";
            echo "<td style='padding: 8px;'>{$product->sku}</td>";
            echo "<td style='padding: 8px;'>${$product->price}</td>";
            echo "<td style='padding: 8px;'>{$product->quantity}</td>";
            echo "<td style='padding: 8px;'>{$product->description}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p><strong>Total products: {$products->count()}</strong></p>";
        echo "<p><strong>Next product ID will be: " . ($products->count() + 1) . "</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
