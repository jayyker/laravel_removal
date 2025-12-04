<?php
// Test API response
$url = 'http://localhost:8000/api/products';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>API Debug</h1>";
echo "<h3>HTTP Status: $httpCode</h3>";
echo "<h3>Raw Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Parse JSON
$data = json_decode($response, true);
echo "<h3>Parsed Data:</h3>";
echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";

// Check database directly
echo "<h3>Database Check:</h3>";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=blanco_db', 'root', '');
    $stmt = $pdo->query("SELECT id, name FROM products ORDER BY id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Products in database:<br>";
    foreach ($products as $product) {
        echo "ID: {$product['id']} - Name: {$product['name']}<br>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>
