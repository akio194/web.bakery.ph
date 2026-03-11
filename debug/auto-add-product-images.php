<?php
/**
 * Auto-Add Missing Product Images
 * Automatically assigns appropriate images to products that don't have them
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get all products without images or with default images
    $stmt = $pdo->query("SELECT id, name, category, image FROM products WHERE image IS NULL OR image = '' OR image = 'default-product.jpg' OR image = 'default-product.svg'");
    $products = $stmt->fetchAll();
    
    // Available images in the assets/images directory
    $available_images = [
        'almond-croissant.jpg',
        'apple-pie.jpg',
        'bagel.jpg',
        'bagels.jpg',
        'baguette.jpg',
        'banana-muffin.jpg',
        'birthday-cake.jpg',
        'blueberry-muffin.jpg',
        'brownie.jpg',
        'cheesecake.jpg',
        'chocolate-cake.jpg',
        'chocolate-cookies.jpg',
        'chocolate-croissant.jpg',
        'chocolate-muffin.jpg',
        'cinnamon-roll.jpg',
        'croissant.jpg',
        'cupcakes.jpg',
        'eclair.jpg',
        'fruit-tart.jpg',
        'macarons.jpg',
        'oatmeal-cookies.jpg',
        'pain-chocolat.jpg',
        'pumpkin-pie.jpg',
        'red-velvet.jpg',
        'sourdough.jpg',
        'sugar-cookies.jpg',
        'tiramisu.jpg',
        'whole-wheat.jpg'
    ];
    
    // Mapping of product names/categories to appropriate images
    $image_mapping = [
        // Cakes
        'cake' => ['chocolate-cake.jpg', 'birthday-cake.jpg', 'red-velvet.jpg', 'cheesecake.jpg'],
        'birthday' => ['birthday-cake.jpg'],
        'chocolate cake' => ['chocolate-cake.jpg'],
        'red velvet' => ['red-velvet.jpg'],
        'cheesecake' => ['cheesecake.jpg'],
        
        // Pastries
        'croissant' => ['croissant.jpg', 'chocolate-croissant.jpg', 'almond-croissant.jpg', 'pain-chocolat.jpg'],
        'chocolate croissant' => ['chocolate-croissant.jpg'],
        'almond croissant' => ['almond-croissant.jpg'],
        'macaron' => ['macarons.jpg'],
        'eclair' => ['eclair.jpg'],
        'cinnamon roll' => ['cinnamon-roll.jpg'],
        'tiramisu' => ['tiramisu.jpg'],
        
        // Bread
        'bread' => ['sourdough.jpg', 'baguette.jpg', 'whole-wheat.jpg', 'bagels.jpg', 'bagel.jpg'],
        'sourdough' => ['sourdough.jpg'],
        'baguette' => ['baguette.jpg'],
        'bagel' => ['bagels.jpg', 'bagel.jpg'],
        'whole wheat' => ['whole-wheat.jpg'],
        
        // Pies
        'pie' => ['apple-pie.jpg', 'pumpkin-pie.jpg', 'fruit-tart.jpg'],
        'apple pie' => ['apple-pie.jpg'],
        'pumpkin pie' => ['pumpkin-pie.jpg'],
        'fruit tart' => ['fruit-tart.jpg'],
        
        // Cookies & Muffins
        'cookie' => ['chocolate-cookies.jpg', 'oatmeal-cookies.jpg', 'sugar-cookies.jpg'],
        'muffin' => ['banana-muffin.jpg', 'blueberry-muffin.jpg', 'chocolate-muffin.jpg'],
        'cupcake' => ['cupcakes.jpg'],
        'brownie' => ['brownie.jpg'],
        
        // Default fallback by category
        'cakes' => ['chocolate-cake.jpg', 'birthday-cake.jpg'],
        'pastries' => ['croissant.jpg', 'macarons.jpg'],
        'bread' => ['sourdough.jpg', 'baguette.jpg'],
        'pies' => ['apple-pie.jpg'],
        'cookies' => ['chocolate-cookies.jpg']
    ];
    
    echo "<h2>🖼️ Auto-Adding Missing Product Images</h2>";
    
    $updated_count = 0;
    $failed_count = 0;
    
    foreach ($products as $product) {
        $product_name = strtolower($product['name']);
        $product_category = strtolower($product['category']);
        $assigned_image = null;
        
        // Try to find exact match first
        foreach ($image_mapping as $keyword => $images) {
            if (strpos($product_name, $keyword) !== false) {
                // Find an available image from the mapped options
                foreach ($images as $img) {
                    if (in_array($img, $available_images)) {
                        $assigned_image = $img;
                        break 2; // Break both loops
                    }
                }
            }
        }
        
        // If no exact match, try category-based assignment
        if (!$assigned_image && isset($image_mapping[$product_category])) {
            foreach ($image_mapping[$product_category] as $img) {
                if (in_array($img, $available_images)) {
                    $assigned_image = $img;
                    break;
                }
            }
        }
        
        // Final fallback - pick a random available image
        if (!$assigned_image) {
            $random_keys = array_keys($available_images);
            $random_index = array_rand($random_keys);
            $assigned_image = $available_images[$random_keys[$random_index]];
        }
        
        // Update the product
        $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        $result = $stmt->execute([$assigned_image, $product['id']]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Updated '{$product['name']}' → <code>{$assigned_image}</code></p>";
            $updated_count++;
        } else {
            echo "<p style='color: red;'>❌ Failed to update '{$product['name']}'</p>";
            $failed_count++;
        }
    }
    
    echo "<h3>📊 Summary</h3>";
    echo "<ul>";
    echo "<li>✅ Successfully updated: {$updated_count} products</li>";
    echo "<li>❌ Failed to update: {$failed_count} products</li>";
    echo "<li>📦 Total processed: " . count($products) . " products</li>";
    echo "</ul>";
    
    if ($updated_count > 0) {
        echo "<p><a href='check-product-images.php' style='background: #8B4513; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Verify Images</a></p>";
        echo "<p><a href='menu.php' style='background: #D2691E; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🛒 View Menu</a></p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>
