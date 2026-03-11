<?php
/**
 * Test script to verify cart localStorage key consistency
 */

echo "<h2>Cart Key Consistency Test</h2>";

// List all the files that should use 'cart' key
$files_to_check = [
    'cart.php',
    'checkout.php', 
    'assets/js/cart.js',
    'assets/js/service.js',
    'index.php',
    'menu.php'
];

echo "<h3>Checking localStorage key usage:</h3>";

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for old 'bakeryCart' usage
        if (strpos($content, 'bakeryCart') !== false) {
            echo "<p style='color: red;'>❌ $file still uses 'bakeryCart' key</p>";
        } else {
            echo "<p style='color: green;'>✅ $file uses correct 'cart' key</p>";
        }
        
        // Check for correct 'cart' usage
        if (strpos($content, "localStorage.getItem('cart')") !== false || 
            strpos($content, "localStorage.setItem('cart'") !== false) {
            echo "<p style='color: green;'>✅ $file has correct localStorage usage</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ $file not found</p>";
    }
    echo "<hr>";
}

echo "<h3>Test Instructions:</h3>";
echo "<ol>";
echo "<li>Clear your browser's localStorage</li>";
echo "<li>Add items to cart from menu page</li>";
echo "<li>Go to cart page - items should appear</li>";
echo "<li>Click 'Proceed to Checkout' - should work without 'cart empty' errors</li>";
echo "</ol>";

echo "<h3>Manual Test in Browser Console:</h3>";
echo "<pre>";
echo "// Clear cart
localStorage.removeItem('cart');
localStorage.removeItem('bakeryCart');

// Add test item
const testCart = [{id: 1, name: 'Test Item', price: 10, quantity: 1}];
localStorage.setItem('cart', JSON.stringify(testCart));

// Verify it works
console.log('Cart contents:', JSON.parse(localStorage.getItem('cart')));

// Test checkout process
// Should be able to proceed to checkout without errors
</pre>";
?>
