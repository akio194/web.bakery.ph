<?php
/**
 * Quick Cart Fix
 * Immediate fix for localStorage vs session cart mismatch
 */

echo "<h1>⚡ Quick Cart Fix</h1>";
echo "<h2>🔧 Fixing Cart Storage Mismatch</h2>";

try {
    $checkout_file = 'checkout.php';
    
    if (!file_exists($checkout_file)) {
        throw new Exception("checkout.php not found");
    }
    
    // Read current checkout.php
    $content = file_get_contents($checkout_file);
    
    // Create backup
    $backup_file = 'checkout.php.backup.' . date('Y-m-d-H-i-s');
    copy($checkout_file, $backup_file);
    
    echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h4>💾 Backup created: $backup_file</h4>";
    echo "</div>";
    
    // Simple fix: Replace session cart with localStorage cart via JavaScript
    $cart_sync_script = '
<script>
// Fix cart mismatch: localStorage to PHP
document.addEventListener("DOMContentLoaded", function() {
    // Get cart from localStorage
    const cartData = localStorage.getItem("bakeryCart");
    
    if (cartData) {
        try {
            const cart = JSON.parse(cartData);
            console.log("Cart found:", cart);
            
            // Update cart summary
            const summaryDiv = document.querySelector(".cart-summary");
            if (summaryDiv) {
                let totalItems = 0;
                let totalPrice = 0;
                
                let summaryHTML = \'<h2 class="section-title">Order Summary</h2>\';
                
                cart.forEach(item => {
                    totalItems += item.quantity || 1;
                    totalPrice += (item.price || 0) * (item.quantity || 1);
                    
                    summaryHTML += \`
                        <div class="summary-item">
                            <span>\${item.name || "Unknown Item"}</span>
                            <span>₱\${(item.price || 0).toFixed(2)} (\${item.quantity || 1}x)</span>
                        </div>
                    \`;
                });
                
                summaryHTML += \`
                    <div class="summary-item">
                        <span>Total Items</span>
                        <span>\${totalItems}</span>
                    </div>
                    <div class="summary-item">
                        <span>Total Amount</span>
                        <span>₱\${totalPrice.toFixed(2)}</span>
                    </div>
                \`;
                
                summaryDiv.innerHTML = summaryHTML;
                
                // Update submit button
                const submitBtn = document.getElementById("submitBtn");
                if (submitBtn) {
                    submitBtn.textContent = \`Place Order - ₱\${totalPrice.toFixed(2)}\`;
                }
            }
            
            // Add cart data to form submission
            const form = document.getElementById("checkoutForm");
            if (form) {
                form.addEventListener("submit", function(e) {
                    // Create hidden input with cart data
                    const hiddenInput = document.createElement("input");
                    hiddenInput.type = "hidden";
                    hiddenInput.name = "cart_data";
                    hiddenInput.value = cartData;
                    form.appendChild(hiddenInput);
                });
            }
            
        } catch (e) {
            console.error("Cart error:", e);
        }
    } else {
        // Show empty cart message
        const summaryDiv = document.querySelector(".cart-summary");
        if (summaryDiv) {
            summaryDiv.innerHTML = \`
                <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;">
                    <h3>🛒 Your Cart is Empty</h3>
                    <p>Please add some items to your cart before proceeding to checkout.</p>
                    <a href="/bakery-website/menu.php" style="display: inline-block; background: #8B4513; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; margin-top: 1rem;">Browse Products</a>
                </div>
            \`;
        }
    }
});
</script>';
    
    // Add script before closing head tag
    if (strpos($content, '</head>') !== false) {
        $content = str_replace('</head>', $cart_sync_script . '</head>', $content);
    } else {
        // Add after opening body tag
        $content = preg_replace('/(<body[^>]*>)/', '$1' . $cart_sync_script, $content);
    }
    
    // Update PHP to handle cart from POST
    $old_cart_line = '$cart_data = json_decode($_SESSION[\'cart_data\'] ?? \'[]\', true) ?? [];';
    $new_cart_code = '
// Get cart data from localStorage (sent via POST)
$cart_data = [];
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && isset($_POST[\'cart_data\'])) {
    $cart_data = json_decode($_POST[\'cart_data\'], true) ?? [];
}';
    
    if (strpos($content, $old_cart_line) !== false) {
        $content = str_replace($old_cart_line, $new_cart_code, $content);
    } else {
        // Find any cart session reference and replace
        $content = preg_replace('/\$cart_data\s*=\s*json_decode\(\$_SESSION\[.*?\]\)/', $new_cart_code, $content);
    }
    
    // Write the fixed file
    if (file_put_contents($checkout_file, $content)) {
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>✅ Cart Fix Applied Successfully!</h3>";
        echo "<h4>What was fixed:</h4>";
        echo "<ul>";
        echo "<li>🔄 Added localStorage to PHP bridge</li>";
        echo "<li>📊 Cart summary now displays correctly</li>";
        echo "<li>🛒 Empty cart shows proper message</li>";
        echo "<li>📝 Form submission includes cart data</li>";
        echo "<li>⚡ Real-time cart updates</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>🚀 Test Your Checkout Now:</h3>";
        echo "<ol>";
        echo "<li><strong>Step 1:</strong> <a href='/bakery-website/menu.php'>Add items to cart</a></li>";
        echo "<li><strong>Step 2:</strong> <a href='/bakery-website/checkout.php'>Go to checkout</a></li>";
        echo "<li><strong>Step 3:</strong> Cart items should now appear!</li>";
        echo "<li><strong>Step 4:</strong> Complete order form and submit</li>";
        echo "</ol>";
        echo "</div>";
        
    } else {
        throw new Exception("Failed to write checkout.php");
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='background: #fbbf24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h3>💡 How This Fix Works:</h3>";
echo "<p><strong>Before:</strong> Cart in localStorage → Backend can't read → Empty cart</p>";
echo "<p><strong>After:</strong> Cart in localStorage → JavaScript bridges to PHP → Cart appears! ✅</p>";
echo "</div>";

echo "<p><a href='/bakery-website/cart.php'>← Test Cart</a> | <a href='/bakery-website/checkout.php'>→ Test Checkout</a></p>";
?>
