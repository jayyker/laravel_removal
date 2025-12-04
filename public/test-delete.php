<?php
// Test the delete API directly
$id = 1; // Change this to an existing product ID

$url = "http://localhost:8000/api/products/$id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Delete API Test</h1>";
echo "<h3>HTTP Status: $httpCode</h3>";
echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Check products after delete
echo "<h3>Products After Delete:</h3>";
try {
    $pdo = new PDO('sqlite:blanco_db');
    $stmt = $pdo->query("SELECT id, name FROM products ORDER BY id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        echo "ID: {$product['id']} - Name: {$product['name']}<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
