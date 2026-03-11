<?php
/**
 * Quick Fix - Add Images to Products
 * Directly updates products without images
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get products without proper images
    $stmt = $pdo->query("SELECT id, name, category, image FROM products WHERE image IS NULL OR image = '' OR image = 'default-product.jpg' OR image = 'default-product.svg'");
    $products = $stmt->fetchAll();
    
    echo "<h2>🖼️ Adding Images to Products</h2>";
    
    // Default images available
    $default_images = [
        'chocolate-cake.jpg',
        'birthday-cake.jpg', 
        'croissant.jpg',
        'cupcakes.jpg',
        'sourdough.jpg',
        'bagels.jpg',
        'macarons.jpg',
        'apple-pie.jpg'
    ];
    
    $updated = 0;
    
    foreach ($products as $product) {
        // Choose image based on category
        $image = 'chocolate-cake.jpg'; // default
        
        if (strpos(strtolower($product['category']), 'cake') !== false) {
            $image = 'birthday-cake.jpg';
        } elseif (strpos(strtolower($product['category']), 'pastry') !== false || strpos(strtolower($product['name']), 'croissant') !== false) {
            $image = 'croissant.jpg';
        } elseif (strpos(strtolower($product['category']), 'bread') !== false) {
            $image = 'sourdough.jpg';
        } elseif (strpos(strtolower($product['category']), 'pie') !== false) {
            $image = 'apple-pie.jpg';
        }
        
        // Update product
        $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        if ($stmt->execute([$image, $product['id']])) {
            echo "<p>✅ Updated: {$product['name']} → {$image}</p>";
            $updated++;
        }
    }
    
    echo "<h3>✅ Updated {$updated} products</h3>";
    echo "<p><a href='menu.php'>View Menu</a> | <a href='index.php'>Home</a></p>";
    
} catch (Exception $e) {
    echo "<p>Error: {$e->getMessage()}</p>";
}
?>
