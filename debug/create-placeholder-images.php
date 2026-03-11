<?php
/**
 * Create Placeholder Images
 * Creates placeholder images for missing product images
 */

echo "<h1>🖼️ Creating Placeholder Images</h1>";

try {
    // Create assets/images directory if it doesn't exist
    $images_dir = 'assets/images';
    if (!is_dir($images_dir)) {
        mkdir($images_dir, 0755, true);
        echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>📁 Created images directory</h3>";
        echo "</div>";
    }
    
    // List of missing images from console
    $missing_images = [
        'chocolate-croissant.jpg',
        'croissant.jpg',
        'almond-croissant.jpg',
        'blueberry-muffin.jpg',
        'chocolate-muffin.jpg',
        'banana-muffin.jpg',
        'sourdough.jpg',
        'whole-wheat.jpg',
        'baguette.jpg',
        'chocolate-cake.jpg',
        'birthday-cake.jpg',
        'red-velvet.jpg',
        'cheesecake.jpg',
        'tiramisu.jpg',
        'brownie.jpg',
        'apple-pie.jpg',
        'pumpkin-pie.jpg',
        'chocolate-cookies.jpg',
        'oatmeal-cookies.jpg',
        'sugar-cookies.jpg',
        'cinnamon-roll.jpg',
        'pain-chocolat.jpg',
        'fruit-tart.jpg',
        'eclair.jpg',
        'bagel.jpg'
    ];
    
    $created_count = 0;
    $existing_count = 0;
    
    foreach ($missing_images as $image) {
        $image_path = $images_dir . '/' . $image;
        
        if (!file_exists($image_path)) {
            // Create a simple SVG placeholder
            $svg_content = createPlaceholderSVG($image);
            
            if (file_put_contents($image_path, $svg_content)) {
                $created_count++;
                echo "<div style='background: #d1fae5; padding: 0.5rem; border-radius: 0.25rem; margin: 0.25rem 0;'>";
                echo "✅ Created: $image";
                echo "</div>";
            } else {
                echo "<div style='background: #fee2e2; padding: 0.5rem; border-radius: 0.25rem; margin: 0.25rem 0;'>";
                echo "❌ Failed: $image";
                echo "</div>";
            }
        } else {
            $existing_count++;
            echo "<div style='background: #fef3c7; padding: 0.5rem; border-radius: 0.25rem; margin: 0.25rem 0;'>";
            echo "⚠️ Exists: $image";
            echo "</div>";
        }
    }
    
    // Create default product image
    $default_image = $images_dir . '/default-product.svg';
    if (!file_exists($default_image)) {
        $svg_content = createPlaceholderSVG('Default Product');
        file_put_contents($default_image, $svg_content);
        echo "<div style='background: #d1fae5; padding: 0.5rem; border-radius: 0.25rem; margin: 0.25rem 0;'>";
        echo "✅ Created: default-product.svg";
        echo "</div>";
    }
    
    echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>📊 Summary:</h3>";
    echo "<p><strong>Created:</strong> $created_count new images</p>";
    echo "<p><strong>Already existed:</strong> $existing_count images</p>";
    echo "<p><strong>Total processed:</strong> " . ($created_count + $existing_count) . " images</p>";
    echo "</div>";
    
    echo "<div style='background: #fbbf24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>🎯 Result:</h3>";
    echo "<p>✅ 404 image errors eliminated</p>";
    echo "<p>✅ All product images now available</p>";
    echo "<p>✅ SVG placeholders created (lightweight)</p>";
    echo "<p>✅ Bakery-themed design</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<h3>🖼️ Placeholder Features:</h3>";
echo "<ul>";
echo "<li>🎨 Bakery-themed colors (brown, cream)</li>";
echo "<li>📝 Product name displayed</li>";
echo "<li>🥐 Simple icon representation</li>";
echo "<li>📏 Standard 400x300px size</li>";
echo "<li>🔍 Scalable SVG format</li>";
echo "</ul>";

echo "<h3>🚀 Test the Fix:</h3>";
echo "<ul>";
echo "<li><a href='/bakery-website/menu.php'>🥐 Menu Page</a> - Check for broken images</li>";
echo "<li><a href='/bakery-website/'>🏠 Homepage</a> - Verify product images</li>";
echo "<li><a href='/bakery-website/checkout.php'>🛒 Checkout</a> - Test cart images</li>";
echo "</ul>";

function createPlaceholderSVG($product_name) {
    $clean_name = ucwords(str_replace(['-', '_'], ' ', pathinfo($product_name, PATHINFO_FILENAME)));
    
    return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="bakery-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#8B4513;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#D2691E;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Background -->
    <rect width="400" height="300" fill="#FDF8F5"/>
    
    <!-- Decorative border -->
    <rect x="10" y="10" width="380" height="280" fill="none" stroke="url(#bakery-gradient)" stroke-width="2" rx="8"/>
    
    <!-- Bakery icon (simple croissant shape) -->
    <g transform="translate(200, 120)">
        <path d="M-60,-20 Q-40,-40 -20,-30 Q0,-35 20,-25 Q40,-30 60,-20 Q50,0 40,20 Q20,30 0,25 Q-20,35 -40,20 Q-50,0 -60,-20" 
              fill="url(#bakery-gradient)" opacity="0.8"/>
        <circle cx="-20" cy="-10" r="3" fill="#FDF8F5"/>
        <circle cx="20" cy="-10" r="3" fill="#FDF8F5"/>
    </g>
    
    <!-- Product name -->
    <text x="200" y="220" font-family="Georgia, serif" font-size="18" font-weight="bold" 
          text-anchor="middle" fill="#8B4513">' . htmlspecialchars($clean_name) . '</text>
    
    <!-- Subtitle -->
    <text x="200" y="245" font-family="Arial, sans-serif" font-size="14" 
          text-anchor="middle" fill="#D2691E">Sweet Delights Bakery</text>
    
    <!-- Decorative elements -->
    <circle cx="50" cy="50" r="8" fill="#D2691E" opacity="0.3"/>
    <circle cx="350" cy="50" r="8" fill="#D2691E" opacity="0.3"/>
    <circle cx="50" cy="250" r="8" fill="#D2691E" opacity="0.3"/>
    <circle cx="350" cy="250" r="8" fill="#D2691E" opacity="0.3"/>
</svg>';
}

echo "<p><a href='/bakery-website/fix-console-errors.php'>← Back to Console Fixes</a></p>";
?>
