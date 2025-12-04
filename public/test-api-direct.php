<?php
// Test direct API call
$url = 'http://localhost:8000/api/products';

$data = [
    'name' => 'Test Product',
    'sku' => 'TEST001',
    'price' => 99.99,
    'quantity' => 10,
    'description' => 'Test description'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>API Test Results</h1>";
echo "<h3>HTTP Status: $httpCode</h3>";
echo "<h3>Request Data:</h3>";
echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Check database
echo "<h3>Database Check:</h3>";
try {
    $pdo = new PDO('sqlite:blanco_db');
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    echo "Products in database: $count";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 1");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>" . htmlspecialchars(print_r($product, true)) . "</pre>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>
