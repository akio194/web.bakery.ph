<?php
/**
 * Check Product Images
 * Shows which products have images and which ones need them
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get all products
    $stmt = $pdo->query('SELECT id, name, image FROM products ORDER BY name');
    $products = $stmt->fetchAll();
    
    echo "<h2>📦 Product Image Status</h2>";
    
    $images_dir = __DIR__ . '/assets/images/';
    $missing_images = [];
    $existing_images = [];
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Product Name</th>";
    echo "<th>Image Path</th>";
    echo "<th>Status</th>";
    echo "<th>Action</th>";
    echo "</tr>";
    
    foreach ($products as $product) {
        $image_path = $images_dir . $product['image'];
        $has_image = file_exists($image_path) && !empty($product['image']);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td><code>" . htmlspecialchars($product['image']) . "</code></td>";
        
        if ($has_image) {
            echo "<td style='color: green;'>✅ Exists</td>";
            echo "<td>-</td>";
            $existing_images[] = $product;
        } else {
            echo "<td style='color: red;'>❌ Missing</td>";
            echo "<td><button onclick='addImage(" . $product['id'] . ", \"" . htmlspecialchars($product['name']) . "\")' style='background: #8B4513; color: white; padding: 4px 8px; border: none; border-radius: 4px; cursor: pointer;'>Add Image</button></td>";
            $missing_images[] = $product;
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>📊 Summary</h3>";
    echo "<ul>";
    echo "<li>✅ Products with images: " . count($existing_images) . "</li>";
    echo "<li>❌ Products missing images: " . count($missing_images) . "</li>";
    echo "<li>📦 Total products: " . count($products) . "</li>";
    echo "</ul>";
    
    if (!empty($missing_images)) {
        echo "<h3>🔧 Products needing images:</h3>";
        echo "<ul>";
        foreach ($missing_images as $product) {
            echo "<li><strong>" . htmlspecialchars($product['name']) . "</strong> (ID: " . $product['id'] . ")</li>";
        }
        echo "</ul>";
        
        echo "<h3>💡 Suggested images to add:</h3>";
        echo "<p>Based on the product names, here are some image suggestions:</p>";
        echo "<ul>";
        foreach ($missing_images as $product) {
            $suggested_image = strtolower(str_replace([' ', '-'], '-', $product['name'])) . '.jpg';
            echo "<li><strong>" . htmlspecialchars($product['name']) . "</strong> → <code>" . $suggested_image . "</code></li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>

<script>
function addImage(productId, productName) {
    const suggestedImage = productName.toLowerCase().replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, '-') + '.jpg';
    const newImage = prompt(`Enter image filename for "${productName}":`, suggestedImage);
    
    if (newImage) {
        fetch('update-product-image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                image: newImage
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Image updated successfully!');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Network error: ' + error.message);
        });
    }
}
</script>

<style>
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
button:hover { background: #D2691E !important; }
</style>
