<?php
/**
 * Fix Cart Mismatch
 * Fixes localStorage vs session cart storage issue
 */

echo "<h1>🛒 Cart Mismatch Fix</h1>";
echo "<h2>🔍 Diagnosing Cart Storage Issue</h2>";

echo "<h3>📋 Problem Analysis:</h3>";
echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🚨 Issue Identified:</h4>";
echo "<p><strong>Frontend:</strong> Cart stored in localStorage as 'bakeryCart'</p>";
echo "<p><strong>Backend:</strong> Looking for cart in \$_SESSION['cart_data']</p>";
echo "<p><strong>Result:</strong> Cart appears empty on checkout page</p>";
echo "</div>";

echo "<h3>🛠️ Solution Options:</h3>";

// Option 1: Update checkout to read from localStorage
echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🔧 Option 1: Update Checkout (Recommended)</h4>";
echo "<p>Modify checkout.php to read cart from localStorage instead of session</p>";
echo "<button onclick='updateCheckout()' style='background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer; width: 100%;'>🔧 Update Checkout.php</button>";
echo "</div>";

// Option 2: Update cart to use session
echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🔄 Option 2: Update Cart System</h4>";
echo "<p>Modify cart to use session storage instead of localStorage</p>";
echo "<button onclick='updateCartSystem()' style='background: #10b981; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer; width: 100%;'>🔄 Update Cart System</button>";
echo "</div>";

// Option 3: Create bridge between localStorage and session
echo "<div style='background: #fbbf24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🌉 Option 3: Create Bridge (Quick Fix)</h4>";
echo "<p>Create a bridge that syncs localStorage to session on checkout</p>";
echo "<button onclick='createBridge()' style='background: #f59e0b; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer; width: 100%;'>🌉 Create Cart Bridge</button>";
echo "</div>";

// JavaScript for solutions
echo "<script>";
echo "
function updateCheckout() {
    if (confirm('This will update checkout.php to read cart from localStorage. Continue?')) {
        window.location.href = '/bakery-website/update-checkout-cart.php';
    }
}

function updateCartSystem() {
    if (confirm('This will update the entire cart system to use sessions. Continue?')) {
        window.location.href = '/bakery-website/update-cart-system.php';
    }
}

function createBridge() {
    if (confirm('This will create a quick bridge to sync cart data. Continue?')) {
        window.location.href = '/bakery-website/create-cart-bridge.php';
    }
}

// Show current cart status
console.log('=== CART DEBUG ===');
const cartData = localStorage.getItem('bakeryCart');
if (cartData) {
    try {
        const cart = JSON.parse(cartData);
        console.log('Cart items:', cart.length);
        console.log('Cart data:', cart);
        document.getElementById('cart-status').innerHTML = 
            '<div style=\"background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
            '<h4>✅ Current Cart Status:</h4>' +
            '<p><strong>Items in cart:</strong> ' + cart.length + '</p>' +
            '<p><strong>Total value:</strong> ₱' + cart.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2) + '</p>' +
            '<p><strong>Storage:</strong> localStorage (bakeryCart)</p>' +
            '</div>';
    } catch (e) {
        console.error('Cart parse error:', e);
        document.getElementById('cart-status').innerHTML = 
            '<div style=\"background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
            '<h4>❌ Cart Data Corrupt</h4>' +
            '<p>Cart data in localStorage is invalid</p>' +
            '</div>';
    }
} else {
    console.log('No cart data found');
    document.getElementById('cart-status').innerHTML = 
        '<div style=\"background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
        '<h4>❌ Cart Empty</h4>' +
        '<p>No cart data found in localStorage</p>' +
        '</div>';
}
";
echo "</script>";

echo "<h3>📊 Current Cart Status:</h3>";
echo "<div id='cart-status'>🔄 Checking cart...</div>";

echo "<h2>🎯 Recommended Solution:</h2>";
echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🔧 Use Option 1 (Update Checkout)</h4>";
echo "<p><strong>Why:</strong> Keeps current cart system, just fixes checkout</p>";
echo "<p><strong>Benefits:</strong></p>";
echo "<ul>";
echo "<li>✅ Minimal changes required</li>";
echo "<li>✅ Preserves existing cart functionality</li>";
echo "<li>✅ Quick fix</li>";
echo "<li>✅ No data loss</li>";
echo "</ul>";
echo "<p><strong>Steps:</strong></p>";
echo "<ol>";
echo "<li>Click '🔧 Update Checkout.php' button</li>";
echo "<li>Test checkout process</li>";
echo "<li>Verify cart items appear correctly</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🚀 Quick Test Links:</h2>";
echo "<ul>";
echo "<li><a href='/bakery-website/menu.php'>🥐 Browse Products</a></li>";
echo "<li><a href='/bakery-website/cart.php'>🛒 View Cart</a></li>";
echo "<li><a href='/bakery-website/checkout.php'>🛒 Test Checkout</a></li>";
echo "<li><a href='/bakery-website/login.php'>🔐 Login</a></li>";
echo "</ul>";
?>
