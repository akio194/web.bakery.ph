<?php
/**
 * Setup Sample Products
 * Creates sample products for testing
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Clear existing products
    $pdo->exec("DELETE FROM order_items");
    $pdo->exec("DELETE FROM orders");
    $pdo->exec("DELETE FROM products");
    
    // Sample products
    $products = [
        [
            'name' => 'Chocolate Cake',
            'description' => 'Rich chocolate cake with smooth frosting',
            'price' => 450.00,
            'category' => 'cakes',
            'image' => 'chocolate-cake.jpg'
        ],
        [
            'name' => 'Birthday Cake',
            'description' => 'Celebration cake with colorful decorations',
            'price' => 650.00,
            'category' => 'cakes',
            'image' => 'birthday-cake.jpg'
        ],
        [
            'name' => 'Fresh Croissant',
            'description' => 'Buttery French croissant, flaky and delicious',
            'price' => 85.00,
            'category' => 'pastries',
            'image' => 'croissant.jpg'
        ],
        [
            'name' => 'Cupcakes',
            'description' => 'Assorted flavored cupcakes with frosting',
            'price' => 75.00,
            'category' => 'cakes',
            'image' => 'cupcakes.jpg'
        ],
        [
            'name' => 'Sourdough Bread',
            'description' => 'Artisan sourdough bread with crispy crust',
            'price' => 120.00,
            'category' => 'bread',
            'image' => 'sourdough.jpg'
        ],
        [
            'name' => 'Fresh Bagels',
            'description' => 'Traditional bagels perfect for breakfast',
            'price' => 95.00,
            'category' => 'bread',
            'image' => 'bagels.jpg'
        ],
        [
            'name' => 'French Macarons',
            'description' => 'Delicate French macarons in assorted colors',
            'price' => 55.00,
            'category' => 'pastries',
            'image' => 'macarons.jpg'
        ],
        [
            'name' => 'Apple Pie',
            'description' => 'Classic apple pie with flaky crust',
            'price' => 380.00,
            'category' => 'pies',
            'image' => 'apple-pie.jpg'
        ],
        [
            'name' => 'Chocolate Croissant',
            'description' => 'Chocolate-filled croissant with powdered sugar',
            'price' => 95.00,
            'category' => 'pastries',
            'image' => 'croissant.jpg'
        ]
    ];
    
    // Insert products
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    
    foreach ($products as $product) {
        $stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $product['category'],
            $product['image']
        ]);
    }
    
    echo "<h2>✅ Successfully created " . count($products) . " sample products!</h2>";
    echo "<p><a href='menu.php'>View Menu</a> | <a href='index.php'>Go Home</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
