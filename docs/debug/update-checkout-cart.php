<?php
/**
 * Update Checkout Cart
 * Modifies checkout.php to read cart from localStorage
 */

echo "<h1>🔧 Updating Checkout Cart System</h1>";

try {
    // Read current checkout.php
    $checkout_file = 'checkout.php';
    if (!file_exists($checkout_file)) {
        throw new Exception("checkout.php file not found");
    }
    
    $content = file_get_contents($checkout_file);
    echo "<h3>📄 Analyzing checkout.php...</h3>";
    
    // Create backup
    $backup_file = 'checkout.php.backup.' . date('Y-m-d-H-i-s');
    if (copy($checkout_file, $backup_file)) {
        echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>💾 Backup created: $backup_file</h4>";
        echo "</div>";
    }
    
    // Find and replace cart data access
    $old_cart_check = "if (empty(\$cart_data)) {";
    $new_cart_check = "// Get cart data from localStorage via JavaScript
    \$cart_data = []; // Will be populated by JavaScript";
    
    if (strpos($content, $old_cart_check) !== false) {
        $content = str_replace($old_cart_check, $new_cart_check, $content);
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>✅ Cart data access updated</h4>";
        echo "</div>";
    }
    
    // Add JavaScript cart sync at the beginning
    $js_sync = "
<script>
// Sync cart from localStorage to PHP
document.addEventListener('DOMContentLoaded', function() {
    const cartData = localStorage.getItem('bakeryCart');
    if (cartData) {
        try {
            const cart = JSON.parse(cartData);
            if (cart.length > 0) {
                // Update cart display
                console.log('Cart synced from localStorage:', cart);
                
                // Update hidden input with cart data
                const cartInput = document.getElementById('cart_data_hidden');
                if (cartInput) {
                    cartInput.value = JSON.stringify(cart);
                }
                
                // Show cart summary
                updateCartSummary(cart);
            } else {
                // Show empty cart message
                showEmptyCart();
            }
        } catch (e) {
            console.error('Cart sync error:', e);
            showEmptyCart();
        }
    } else {
        showEmptyCart();
    }
});

function updateCartSummary(cart) {
    let totalItems = 0;
    let totalPrice = 0;
    
    cart.forEach(item => {
        totalItems += item.quantity || 1;
        totalPrice += (item.price || 0) * (item.quantity || 1);
    });
    
    // Update cart summary section
    const summaryDiv = document.querySelector('.cart-summary');
    if (summaryDiv) {
        summaryDiv.innerHTML = \`
            <h2 class=\"section-title\">Order Summary</h2>
            \${cart.map(item => \`
                <div class=\"summary-item\">
                    <span>\${item.name || 'Unknown Item'}</span>
                    <span>₱\${(item.price || 0).toFixed(2)} (\${item.quantity || 1}x)</span>
                </div>
            \`).join('')}
            <div class=\"summary-item\">
                <span>Total Items</span>
                <span>\${totalItems}</span>
            </div>
            <div class=\"summary-item\">
                <span>Total Amount</span>
                <span>₱\${totalPrice.toFixed(2)}</span>
            </div>
        \`;
    }
    
    // Update submit button
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.textContent = \`Place Order - ₱\${totalPrice.toFixed(2)}\`;
    }
}

function showEmptyCart() {
    const summaryDiv = document.querySelector('.cart-summary');
    if (summaryDiv) {
        summaryDiv.innerHTML = \`
            <div style=\"background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;\">
                <h3>🛒 Your Cart is Empty</h3>
                <p>Please add some items to your cart before proceeding to checkout.</p>
                <a href=\"/bakery-website/menu.php\" class=\"inline-block bg-[#8B4513] text-white px-6 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300 mt-4\">Browse Products</a>
            </div>
        \`;
    }
}

// Handle form submission with cart data
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const cartData = localStorage.getItem('bakeryCart');
            if (cartData) {
                // Add hidden input with cart data
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'cart_data';
                hiddenInput.value = cartData;
                form.appendChild(hiddenInput);
            }
        });
    }
});
</script>";

    // Add hidden input for cart data
    $hidden_input = '<input type="hidden" id="cart_data_hidden" name="cart_data" value="">';
    
    // Insert JavaScript before closing head or at beginning of body
    if (strpos($content, '</head>') !== false) {
        $content = str_replace('</head>', $js_sync . '</head>', $content);
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>✅ JavaScript sync added to head</h4>";
        echo "</div>";
    } else {
        // Add after opening body tag
        $content = preg_replace('/(<body[^>]*>)/', '$1' . $js_sync, $content);
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>✅ JavaScript sync added to body</h4>";
        echo "</div>";
    }
    
    // Add hidden input to form
    if (strpos($content, '<form method="POST"') !== false) {
        $content = str_replace('<form method="POST"', '<form method="POST"' . $hidden_input, $content);
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>✅ Hidden cart input added to form</h4>";
        echo "</div>";
    }
    
    // Update PHP cart data processing
    $old_php_cart = '$cart_data = json_decode($_SESSION[\'cart_data\'] ?? \'[]\', true) ?? [];';
    $new_php_cart = '$cart_data = [];
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && isset($_POST[\'cart_data\'])) {
    $cart_data = json_decode($_POST[\'cart_data\'], true) ?? [];
}';
    
    if (strpos($content, $old_php_cart) !== false) {
        $content = str_replace($old_php_cart, $new_php_cart, $content);
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>✅ PHP cart processing updated</h4>";
        echo "</div>";
    }
    
    // Write updated file
    if (file_put_contents($checkout_file, $content)) {
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>✅ Checkout.php Updated Successfully!</h3>";
        echo "<h4>Changes Made:</h4>";
        echo "<ul>";
        echo "<li>✅ Added JavaScript localStorage sync</li>";
        echo "<li>✅ Added cart summary display</li>";
        echo "<li>✅ Added empty cart handling</li>";
        echo "<li>✅ Added hidden form input for cart data</li>";
        echo "<li>✅ Updated PHP cart data processing</li>";
        echo "</ul>";
        echo "<h4>Next Steps:</h4>";
        echo "<ol>";
        echo "<li>Go to <a href='/bakery-website/menu.php'>menu.php</a></li>";
        echo "<li>Add items to cart</li>";
        echo "<li>Proceed to <a href='/bakery-website/checkout.php'>checkout.php</a></li>";
        echo "<li>Cart items should now appear correctly</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        throw new Exception("Failed to write updated checkout.php");
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h3>🎯 What This Fix Does:</h3>";
echo "<ul>";
echo "<li>🔄 <strong>Syncs localStorage to PHP:</strong> Cart data from frontend is passed to backend</li>";
echo "<li>📊 <strong>Displays cart summary:</strong> Shows items and totals on checkout page</li>";
echo "<li>🛒 <strong>Handles empty cart:</strong> Shows message when cart is empty</li>";
echo "<li>📝 <strong>Preserves data:</strong> No cart items lost during checkout</li>";
echo "<li>⚡ <strong>Real-time updates:</strong> Cart summary updates automatically</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='/bakery-website/cart.php'>← Test Cart</a> | <a href='/bakery-website/checkout.php'>→ Test Checkout</a></p>";
?>
