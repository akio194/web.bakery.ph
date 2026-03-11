<?php
/**
 * Debug Checkout Process
 * Step-by-step debugging of checkout with items in cart
 */

echo "<h1>🔍 Checkout Process Debug</h1>";
echo "<h2>📋 Testing Complete Checkout Flow</h2>";

echo "<h3>Step 1: Cart Status Check</h3>";
echo "<script>";
echo "
console.log('=== CHECKOUT DEBUG START ===');

// Check cart data
const cartData = localStorage.getItem('bakeryCart');
console.log('Raw cart data:', cartData);

if (cartData) {
    try {
        const cart = JSON.parse(cartData);
        console.log('Parsed cart:', cart);
        console.log('Cart length:', cart.length);
        
        if (cart.length > 0) {
            console.log('✅ Cart has items');
            
            // Calculate totals
            let totalItems = 0;
            let totalPrice = 0;
            
            cart.forEach((item, index) => {
                totalItems += (item.quantity || 1);
                totalPrice += (item.price || 0) * (item.quantity || 1);
                console.log(\`Item \${index + 1}: \${item.name} - ₱\${item.price} x \${item.quantity}\`);
            });
            
            console.log('Total items:', totalItems);
            console.log('Total price:', totalPrice);
            
            // Update debug display
            document.getElementById('cart-debug-info').innerHTML = 
                '<div style=\"background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
                '<h4>✅ Cart Status: Has Items</h4>' +
                '<p><strong>Items:</strong> ' + cart.length + '</p>' +
                '<p><strong>Total Items:</strong> ' + totalItems + '</p>' +
                '<p><strong>Total Price:</strong> ₱' + totalPrice.toFixed(2) + '</p>' +
                '</div>';
                
        } else {
            console.log('❌ Cart is empty');
            document.getElementById('cart-debug-info').innerHTML = 
                '<div style=\"background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
                '<h4>❌ Cart Status: Empty</h4>' +
                '<p>Add items to cart before checkout</p>' +
                '</div>';
        }
        
    } catch (e) {
        console.error('Cart parse error:', e);
        document.getElementById('cart-debug-info').innerHTML = 
            '<div style=\"background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
                '<h4>❌ Cart Error:</h4>' +
                '<p>Cart data is corrupted: ' + e.message + '</p>' +
                '</div>';
    }
} else {
    console.log('❌ No cart data found');
    document.getElementById('cart-debug-info').innerHTML = 
        '<div style=\"background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
        '<h4>❌ Cart Status: No Data</h4>' +
        '<p>No cart data in localStorage</p>' +
        '</div>';
}

console.log('=== CHECKOUT DEBUG END ===');
";
echo "</script>";

echo "<div id='cart-debug-info'>🔄 Checking cart...</div>";

echo "<h3>Step 2: Form Submission Test</h3>";
echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🧪 Test Order Creation:</h4>";
echo "<p>Click the button below to test if orders can be created:</p>";
echo "<button onclick='testCheckout()' style='background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer;'>🧪 Test Checkout Process</button>";
echo "</div>";

echo "<h3>Step 3: Common Issues Check</h3>";
echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0;'>";

// Check 1: JavaScript errors
echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db;'>";
echo "<h4>⚠️ JavaScript Errors</h4>";
echo "<p>Press F12 and check Console for red errors</p>";
echo "<p>Common issues:</p>";
echo "<ul>";
echo "<li>CartService not defined</li>";
echo "<li>jQuery not loaded</li>";
echo "<li>Form submission blocked</li>";
echo "<li>CORS errors</li>";
echo "</ul>";
echo "</div>";

// Check 2: Form validation
echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db;'>";
echo "<h4>📝 Form Validation</h4>";
echo "<p>Required fields must be filled:</p>";
echo "<ul>";
echo "<li>Full Name</li>";
echo "<li>Email Address</li>";
echo "<li>Phone Number</li>";
echo "<li>Delivery Address</li>";
echo "<li>Payment Method</li>";
echo "</ul>";
echo "</div>";

// Check 3: Network issues
echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db;'>";
echo "<h4>🌐 Network Issues</h4>";
echo "<p>Check Network tab in F12:</p>";
echo "<ul>";
echo "<li>Failed POST requests</li>";
echo "<li>500 server errors</li>";
echo "<li>CORS blocked requests</li>";
echo "<li>Timeout errors</li>";
echo "</ul>";
echo "</div>";

echo "</div>";

// JavaScript for testing
echo "<script>";
echo "
function testCheckout() {
    console.log('🧪 Testing checkout process...');
    
    // Get cart data
    const cartData = localStorage.getItem('bakeryCart');
    if (!cartData) {
        alert('❌ No cart data found');
        return;
    }
    
    try {
        const cart = JSON.parse(cartData);
        if (cart.length === 0) {
            alert('❌ Cart is empty');
            return;
        }
        
        console.log('Creating test order with cart:', cart);
        
        // Create test order data
        const orderData = {
            cart_data: cartData,
            name: 'Test User',
            email: 'test@example.com',
            phone: '123-456-7890',
            address: '123 Test Street',
            payment_method: 'cash',
            notes: 'Test order'
        };
        
        // Send test request
        fetch('/bakery-website/api/test-checkout-process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Test result:', data);
            
            if (data.success) {
                alert('✅ Checkout process works! Issue is likely in frontend form.');
                document.getElementById('test-result').innerHTML = 
                    '<div style=\"background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
                    '<h4>✅ Backend Works!</h4>' +
                    '<p>Order creation successful. Issue is in frontend form.</p>' +
                    '<p><strong>Order ID:</strong> ' + data.order_id + '</p>' +
                    '</div>';
            } else {
                alert('❌ Checkout process failed: ' + data.message);
                document.getElementById('test-result').innerHTML = 
                    '<div style=\"background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
                    '<h4>❌ Backend Failed!</h4>' +
                    '<p>Error: ' + data.message + '</p>' +
                    '</div>';
            }
        })
        .catch(error => {
            console.error('Test error:', error);
            alert('❌ Network error: ' + error.message);
            document.getElementById('test-result').innerHTML = 
                '<div style=\"background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;\">' +
                '<h4>❌ Network Error!</h4>' +
                '<p>' + error.message + '</p>' +
                '</div>';
        });
        
    } catch (e) {
        console.error('Cart parse error:', e);
        alert('❌ Cart data error: ' + e.message);
    }
}

// Auto-test on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        console.log('Auto-running checkout test...');
        testCheckout();
    }, 2000);
});
";
echo "</script>";

echo "<h3>Step 4: Test Results</h3>";
echo "<div id='test-result'>🔄 Waiting for test results...</div>";

echo "<h2>🚀 Quick Actions</h2>";
echo "<div style='background: #fbbf24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h3>🎯 Debugging Steps:</h3>";
echo "<ol>";
echo "<li><strong>Step 1:</strong> Check cart status above</li>";
echo "<li><strong>Step 2:</strong> Look for JavaScript errors (F12)</li>";
echo "<li><strong>Step 3:</strong> Click 'Test Checkout Process' button</li>";
echo "<li><strong>Step 4:</strong> Review test results</li>";
echo "<li><strong>Step 5:</strong> Apply suggested fixes</li>";
echo "</ol>";
echo "</div>";

echo "<ul>";
echo "<li><a href='/bakery-website/menu.php'>🥐 Add Items to Cart</a></li>";
echo "<li><a href='/bakery-website/cart.php'>🛒 View Cart</a></li>";
echo "<li><a href='/bakery-website/checkout.php'>🛒 Test Checkout</a></li>";
echo "<li><a href='/bakery-website/fix-checkout.php'>🔧 Fix Checkout Issues</a></li>";
echo "</ul>";
?>
