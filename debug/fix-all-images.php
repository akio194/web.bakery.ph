<?php
/**
 * Complete Image Fix
 * 1. Add images to products without them
 * 2. Fix default image issues
 * 3. Ensure all images display properly
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>🖼️ Complete Image Fix</h2>";
    
    // Step 1: Check current products
    $stmt = $pdo->query("SELECT id, name, category, image FROM products ORDER BY name");
    $products = $stmt->fetchAll();
    
    echo "<h3>📊 Current Product Status</h3>";
    
    $needs_update = [];
    foreach ($products as $product) {
        $has_image = !empty($product['image']) && 
                    $product['image'] !== 'default-product.jpg' && 
                    $product['image'] !== 'default-product.svg';
        
        if (!$has_image) {
            $needs_update[] = $product;
        }
    }
    
    echo "<p>📦 Total products: " . count($products) . "</p>";
    echo "<p>❌ Need images: " . count($needs_update) . "</p>";
    
    // Step 2: Update products without proper images
    if (!empty($needs_update)) {
        echo "<h3>🔧 Adding Images...</h3>";
        
        $image_map = [
            // Cakes
            'cake' => 'chocolate-cake.jpg',
            'birthday' => 'birthday-cake.jpg',
            'chocolate' => 'chocolate-cake.jpg',
            'red velvet' => 'red-velvet.jpg',
            'cheesecake' => 'cheesecake.jpg',
            'cupcake' => 'cupcakes.jpg',
            
            // Pastries  
            'croissant' => 'croissant.jpg',
            'macaron' => 'macarons.jpg',
            'eclair' => 'eclair.jpg',
            'cinnamon' => 'cinnamon-roll.jpg',
            'tiramisu' => 'tiramisu.jpg',
            
            // Bread
            'bread' => 'sourdough.jpg',
            'sourdough' => 'sourdough.jpg',
            'baguette' => 'baguette.jpg',
            'bagel' => 'bagels.jpg',
            
            // Pies
            'pie' => 'apple-pie.jpg',
            'apple' => 'apple-pie.jpg',
            'pumpkin' => 'pumpkin-pie.jpg',
            'tart' => 'fruit-tart.jpg',
            
            // Cookies/Muffins
            'cookie' => 'chocolate-cookies.jpg',
            'muffin' => 'blueberry-muffin.jpg',
            'brownie' => 'brownie.jpg'
        ];
        
        $updated = 0;
        
        foreach ($needs_update as $product) {
            $name_lower = strtolower($product['name']);
            $assigned_image = 'chocolate-cake.jpg'; // default
            
            // Find matching image
            foreach ($image_map as $keyword => $image) {
                if (strpos($name_lower, $keyword) !== false) {
                    $assigned_image = $image;
                    break;
                }
            }
            
            // Update product
            $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
            if ($stmt->execute([$assigned_image, $product['id']])) {
                echo "<p style='color: green;'>✅ {$product['name']} → {$assigned_image}</p>";
                $updated++;
            }
        }
        
        echo "<h3>✅ Updated {$updated} products</h3>";
    }
    
    // Step 3: Verify images exist
    echo "<h3>🔍 Verifying Image Files</h3>";
    $images_dir = __DIR__ . '/assets/images/';
    $stmt = $pdo->query("SELECT name, image FROM products");
    $all_products = $stmt->fetchAll();
    
    $missing_files = [];
    foreach ($all_products as $product) {
        $image_path = $images_dir . $product['image'];
        if (!file_exists($image_path)) {
            $missing_files[] = $product;
        }
    }
    
    if (!empty($missing_files)) {
        echo "<p style='color: orange;'>⚠️ " . count($missing_files) . " products reference missing image files:</p>";
        foreach ($missing_files as $product) {
            echo "<p style='color: orange;'>- {$product['name']} → {$product['image']}</p>";
        }
        
        // Fix missing files by assigning existing images
        echo "<h3>🔧 Fixing Missing Files...</h3>";
        $existing_images = array_diff(scandir($images_dir), ['.', '..']);
        $valid_images = array_filter($existing_images, function($img) {
            return strpos($img, '.jpg') !== false || strpos($img, '.png') !== false || strpos($img, '.svg') !== false;
        });
        
        $fixed = 0;
        foreach ($missing_files as $product) {
            // Use first available image
            $fallback_image = reset($valid_images);
            if ($fallback_image) {
                $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
                if ($stmt->execute([$fallback_image, $product['id']])) {
                    echo "<p style='color: blue;'>🔧 Fixed: {$product['name']} → {$fallback_image}</p>";
                    $fixed++;
                }
            }
        }
        echo "<p>🔧 Fixed {$fixed} products with missing files</p>";
    } else {
        echo "<p style='color: green;'>✅ All image files exist!</p>";
    }
    
    echo "<h3>🎉 Done!</h3>";
    echo "<p><a href='menu.php' style='background: #8B4513; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🛒 View Menu</a></p>";
    echo "<p><a href='index.php' style='background: #D2691E; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Home</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: {$e->getMessage()}</p>";
}
?>
