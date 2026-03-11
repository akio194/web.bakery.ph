<?php
/**
 * Checkout Debug Tool
 * Diagnoses checkout issues step by step
 */

session_start();
require_once 'config/database.php';

echo "<h1>🛒 Checkout Debug Tool</h1>";
echo "<h2>🔍 Step-by-Step Checkout Diagnosis</h2>";

try {
    // Step 1: Check User Login Status
    echo "<h3>Step 1: User Authentication</h3>";
    if (isset($_SESSION['user_id'])) {
        echo "✅ <strong>User Status:</strong> Logged in<br>";
        echo "📧 <strong>User ID:</strong> " . $_SESSION['user_id'] . "<br>";
        echo "👤 <strong>User Name:</strong> " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
        echo "🔑 <strong>User Role:</strong> " . ($_SESSION['user_role'] ?? 'Not set') . "<br>";
        
        // Check if user is admin (shouldn't be checking out)
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            echo "⚠️ <strong>Issue:</strong> Admin users cannot checkout<br>";
            echo "🛠️ <strong>Fix:</strong> Admin accounts are restricted from ordering<br>";
        }
    } else {
        echo "❌ <strong>Issue:</strong> User not logged in<br>";
        echo "🛠️ <strong>Solution:</strong> User must login to checkout<br>";
    }
    
    // Step 2: Check Cart Contents
    echo "<h3>Step 2: Cart Analysis</h3>";
    
    // Check localStorage cart
    echo "<h4>📦 Cart Storage Check</h4>";
    echo "<script>";
    echo "
    console.log('=== CART DEBUG ===');
    
    // Check localStorage
    const cartData = localStorage.getItem('bakeryCart');
    console.log('Raw localStorage data:', cartData);
    
    if (cartData) {
        try {
            const cart = JSON.parse(cartData);
            console.log('Parsed cart:', cart);
            console.log('Cart length:', cart.length);
            
            if (cart.length > 0) {
                console.log('✅ Cart has items');
                document.getElementById('cart-status').innerHTML = '✅ <strong>Cart Status:</strong> Has ' + cart.length + ' items<br>';
                
                let totalItems = 0;
                let totalPrice = 0;
                cart.forEach(item => {
                    totalItems += item.quantity || 1;
                    totalPrice += (item.price || 0) * (item.quantity || 1);
                });
                
                document.getElementById('cart-details').innerHTML = 
                    '📊 <strong>Total Items:</strong> ' + totalItems + '<br>' +
                    '💰 <strong>Total Price:</strong> ₱' + totalPrice.toFixed(2) + '<br>' +
                    '🛒 <strong>Cart Valid:</strong> Yes<br>';
            } else {
                console.log('❌ Cart is empty');
                document.getElementById('cart-status').innerHTML = '❌ <strong>Cart Status:</strong> Empty<br>';
                document.getElementById('cart-details').innerHTML = '🛠️ <strong>Solution:</strong> Add items to cart first<br>';
            }
        } catch (e) {
            console.error('Cart parse error:', e);
            document.getElementById('cart-status').innerHTML = '❌ <strong>Cart Error:</strong> Invalid data format<br>';
        }
    } else {
        console.log('❌ No cart data in localStorage');
        document.getElementById('cart-status').innerHTML = '❌ <strong>Cart Status:</strong> No cart data found<br>';
        document.getElementById('cart-details').innerHTML = '🛠️ <strong>Solution:</strong> Add items to cart first<br>';
    }
    ";
    echo "</script>";
    echo "<div id='cart-status'>🔄 Checking cart...</div>";
    echo "<div id='cart-details'>🔄 Analyzing cart contents...</div>";
    
    // Check session cart (if exists)
    if (isset($_SESSION['cart'])) {
        echo "📋 <strong>Session Cart:</strong> " . count($_SESSION['cart']) . " items<br>";
    }
    
    // Step 3: Check Checkout File
    echo "<h3>Step 3: Checkout File Analysis</h3>";
    
    if (file_exists('checkout.php')) {
        echo "✅ <strong>Checkout File:</strong> Exists<br>";
        
        // Check for admin redirect
        $checkout_content = file_get_contents('checkout.php');
        if (strpos($checkout_content, 'admin') !== false) {
            echo "🔍 <strong>Admin Check:</strong> File contains admin restrictions<br>";
        }
        
        if (strpos($checkout_content, 'requireAdmin') !== false) {
            echo "⚠️ <strong>Potential Issue:</strong> Admin requirement found<br>";
        }
    } else {
        echo "❌ <strong>Issue:</strong> checkout.php file missing<br>";
    }
    
    // Step 4: Check Database Connection
    echo "<h3>Step 4: Database Check</h3>";
    $pdo = getDBConnection();
    echo "✅ <strong>Database:</strong> Connected<br>";
    
    // Check if user exists in database
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ <strong>User in DB:</strong> " . htmlspecialchars($user['name']) . " (" . $user['role'] . ")<br>";
        } else {
            echo "❌ <strong>Issue:</strong> User not found in database<br>";
        }
    }
    
    // Step 5: Check Required Tables
    echo "<h3>Step 5: Required Tables</h3>";
    $required_tables = ['users', 'products', 'orders', 'order_items'];
    foreach ($required_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ <strong>Table '$table':</strong> Exists<br>";
        } else {
            echo "❌ <strong>Table '$table':</strong> Missing<br>";
        }
    }
    
    // Step 6: Test Order Creation
    echo "<h3>Step 6: Order Creation Test</h3>";
    if (isset($_SESSION['user_id']) && !isset($_SESSION['user_role'])) {
        echo "<p>🧪 Testing order creation capability...</p>";
        
        try {
            $test_order = [
                'user_id' => $_SESSION['user_id'],
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'customer_phone' => '123-456-7890',
                'delivery_address' => '123 Test St',
                'total_price' => 100.00,
                'status' => 'pending',
                'payment_method' => 'test',
                'order_notes' => 'Test order'
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, total_price, status, payment_method, order_notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $test_order['user_id'],
                $test_order['customer_name'],
                $test_order['customer_email'],
                $test_order['customer_phone'],
                $test_order['delivery_address'],
                $test_order['total_price'],
                $test_order['status'],
                $test_order['payment_method'],
                $test_order['order_notes']
            ]);
            
            $order_id = $pdo->lastInsertId();
            echo "✅ <strong>Order Creation:</strong> Test successful (Order #$order_id)<br>";
            
            // Clean up test order
            $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
            echo "🧹 <strong>Cleanup:</strong> Test order removed<br>";
            
        } catch (PDOException $e) {
            echo "❌ <strong>Order Creation Error:</strong> " . $e->getMessage() . "<br>";
        }
    }
    
    // Step 7: JavaScript Environment Check
    echo "<h3>Step 7: Frontend Environment</h3>";
    echo "<script>";
    echo "
    console.log('=== FRONTEND DEBUG ===');
    
    // Check if CartService exists
    if (typeof CartService !== 'undefined') {
        console.log('✅ CartService found');
        document.getElementById('frontend-status').innerHTML += '✅ <strong>CartService:</strong> Available<br>';
    } else {
        console.log('❌ CartService not found');
        document.getElementById('frontend-status').innerHTML += '❌ <strong>CartService:</strong> Missing<br>';
    }
    
    // Check if jQuery is loaded
    if (typeof $ !== 'undefined') {
        console.log('✅ jQuery loaded');
        document.getElementById('frontend-status').innerHTML += '✅ <strong>jQuery:</strong> Loaded<br>';
    } else {
        console.log('❌ jQuery not loaded');
        document.getElementById('frontend-status').innerHTML += '❌ <strong>jQuery:</strong> Missing<br>';
    }
    
    // Check for JavaScript errors
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
    });
    
    // Test checkout button functionality
    document.addEventListener('DOMContentLoaded', function() {
        const checkoutBtn = document.querySelector('a[href*=\"checkout.php\"], button[onclick*=\"checkout\"]');
        if (checkoutBtn) {
            console.log('✅ Checkout button found:', checkoutBtn);
            document.getElementById('frontend-status').innerHTML += '✅ <strong>Checkout Button:</strong> Found<br>';
        } else {
            console.log('❌ Checkout button not found');
            document.getElementById('frontend-status').innerHTML += '❌ <strong>Checkout Button:</strong> Not found<br>';
        }
    });
    ";
    echo "</script>";
    echo "<div id='frontend-status'>🔄 Checking frontend...</div>";
    
} catch (Exception $e) {
    echo "❌ <strong>Debug Error:</strong> " . $e->getMessage() . "<br>";
}

// Step 8: Provide Solutions
echo "<h2>🛠️ Automated Solutions</h2>";
echo "<div style='background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h3>🔧 Common Fixes:</h3>";

// Solution 1: Fix Admin Checkout Issue
echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0;'>";
echo "<h4>🚫 Fix Admin Checkout Restriction:</h4>";
echo "<p><strong>Issue:</strong> Admin users might be blocked from checkout</p>";
echo "<p><strong>Solution:</strong></p>";
echo "<ol>";
echo "<li>Temporarily remove admin restrictions from checkout.php</li>";
echo "<li>Or create a regular customer account for testing</li>";
echo "</ol>";
echo "<button onclick='fixAdminCheckout()' style='background: #3b82f6; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.25rem; cursor: pointer;'>🔧 Fix Admin Checkout</button>";
echo "</div>";

// Solution 2: Clear and Reset Cart
echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0;'>";
echo "<h4>🛒 Reset Cart:</h4>";
echo "<p><strong>Issue:</strong> Cart data might be corrupted</p>";
echo "<p><strong>Solution:</strong> Clear cart and add fresh items</p>";
echo "<button onclick='resetCart()' style='background: #10b981; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.25rem; cursor: pointer;'>🔄 Reset Cart</button>";
echo "</div>";

// Solution 3: Create Test Order
echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0;'>";
echo "<h4>🧪 Test Order Creation:</h4>";
echo "<p><strong>Issue:</strong> Backend order creation might be failing</p>";
echo "<p><strong>Solution:</strong> Test order creation directly</p>";
echo "<button onclick='testOrderCreation()' style='background: #059669; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.25rem; cursor: pointer;'>🧪 Test Order</button>";
echo "</div>";

echo "</div>";

// JavaScript for solutions
echo "<script>";
echo "
function fixAdminCheckout() {
    // This would be implemented to remove admin restrictions
    alert('To fix admin checkout: Edit checkout.php and remove admin restrictions');
    window.location.href = '/bakery-website/checkout.php';
}

function resetCart() {
    localStorage.removeItem('bakeryCart');
    console.log('Cart reset');
    alert('Cart has been reset. Please add items again.');
    window.location.href = '/bakery-website/menu.php';
}

function testOrderCreation() {
    // Test if order creation works
    fetch('/bakery-website/api/test-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Order creation works! Issue is likely in frontend.');
        } else {
            alert('❌ Order creation failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Network error: ' + error.message);
    });
}
";
echo "</script>";

// Quick links
echo "<h2>🚀 Quick Test Links</h2>";
echo "<ul>";
echo "<li><a href='/bakery-website/menu.php'>🥐 Browse Products</a></li>";
echo "<li><a href='/bakery-website/cart.php'>🛒 View Cart</a></li>";
echo "<li><a href='/bakery-website/checkout.php'>🛒 Test Checkout</a></li>";
echo "<li><a href='/bakery-website/login.php'>🔐 Login/Register</a></li>";
echo "<li><a href='/bakery-website/add-test-data.php'>🧁 Add Test Data</a></li>";
echo "</ul>";

echo "<div style='background: #fbbf24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h3>📋 Debugging Checklist:</h3>";
echo "<ul>";
echo "<li>□ User is logged in (not admin)</li>";
echo "<li>□ Cart has valid items</li>";
echo "<li>□ Checkout button exists and works</li>";
echo "<li>□ No JavaScript errors in console</li>";
echo "<li>□ Database tables exist</li>";
echo "<li>□ Order creation works</li>";
echo "</ul>";
echo "</div>";
?>
