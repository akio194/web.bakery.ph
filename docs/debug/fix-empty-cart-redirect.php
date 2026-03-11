<?php
/**
 * Fix Empty Cart Redirect
 * Fixes the issue where empty cart redirects instead of showing message
 */

echo "<h1>🛒 Fixing Empty Cart Redirect Issue</h1>";

try {
    $checkout_file = 'checkout.php';
    
    if (!file_exists($checkout_file)) {
        throw new Exception("checkout.php not found");
    }
    
    $content = file_get_contents($checkout_file);
    
    echo "<h3>🔍 Current Issue:</h3>";
    echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h4>🚨 Problem Found:</h4>";
    echo "<p><strong>Line 270-274:</strong> Empty cart redirects to menu.php</p>";
    echo "<p><strong>Expected:</strong> Should show empty cart message on checkout page</p>";
    echo "<p><strong>Result:</strong> User can't see checkout page with empty cart</p>";
    echo "</div>";
    
    // Create backup
    $backup_file = 'checkout.php.backup.' . date('Y-m-d-H-i-s');
    copy($checkout_file, $backup_file);
    
    echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h4>💾 Backup created: $backup_file</h4>";
    echo "</div>";
    
    // Fix the redirect issue
    $old_redirect = "
    if (cart.length === 0) {
        // Redirect to menu if cart is empty
        window.location.href = '/bakery-website/menu.php';
        return;
    }";
    
    $new_logic = "
    if (cart.length === 0) {
        // Show empty cart message instead of redirecting
        const summaryContainer = document.getElementById('order-summary-items');
        if (summaryContainer) {
            summaryContainer.innerHTML = \`
                <div style=\"background: #fee2e2; color: #991b1b; padding: 2rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;\">
                    <h3 style=\"font-size: 1.5rem; margin-bottom: 1rem;\">🛒 Your Cart is Empty</h3>
                    <p style=\"font-size: 1.125rem; margin-bottom: 1.5rem;\">Please add some delicious items to your cart before proceeding to checkout.</p>
                    <a href=\"/bakery-website/menu.php\" style=\"display: inline-block; background: #8B4513; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; transition: background-color 0.2s;\" onmouseover=\"this.style.backgroundColor='#D2691E'\" onmouseout=\"this.style.backgroundColor='#8B4513'\">Browse Products</a>
                </div>
            \`;
        }
        
        // Also update other elements
        const totalElement = document.getElementById('order-total');
        if (totalElement) {
            totalElement.textContent = '₱0.00';
        }
        
        // Hide submit button
        const submitBtn = document.querySelector('button[type=\"submit\"]');
        if (submitBtn) {
            submitBtn.style.display = 'none';
        }
        
        return;
    }";
    
    if (strpos($content, $old_redirect) !== false) {
        $content = str_replace($old_redirect, $new_logic, $content);
        
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>✅ Empty cart redirect fixed</h4>";
        echo "<p>Now shows empty cart message instead of redirecting</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h4>⚠️ Redirect logic not found</h4>";
        echo "<p>The redirect code may have been already changed</p>";
        echo "</div>";
    }
    
    // Write the fixed file
    if (file_put_contents($checkout_file, $content)) {
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>✅ Fix Applied Successfully!</h3>";
        echo "<h4>What was changed:</h4>";
        echo "<ul>";
        echo "<li>✅ Removed automatic redirect for empty cart</li>";
        echo "<li>✅ Added user-friendly empty cart message</li>";
        echo "<li>✅ Added browse products button</li>";
        echo "<li>✅ Hidden submit button for empty cart</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>🎯 Test Your Checkout:</h3>";
        echo "<ol>";
        echo "<li><strong>Step 1:</strong> <a href='/bakery-website/menu.php'>Add items to cart</a></li>";
        echo "<li><strong>Step 2:</strong> <a href='/bakery-website/checkout.php'>Go to checkout with items</a></li>";
        echo "<li><strong>Step 3:</strong> Cart should appear correctly ✅</li>";
        echo "<li><strong>Step 4:</strong> Remove all items from cart</li>";
        echo "<li><strong>Step 5:</strong> Go to checkout again → Should show empty cart message ✅</li>";
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

echo "<h2>💡 Why This Fix Works:</h2>";
echo "<div style='background: #fbbf24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🚫 Before Fix:</h4>";
echo "<p><strong>Empty cart:</strong> → Redirect to menu.php → User confused 🤔</p>";
echo "<h4>✅ After Fix:</h4>";
echo "<p><strong>Empty cart:</strong> → Shows helpful message + Browse button → User understands ✅</p>";
echo "</div>";

echo "<p><a href='/bakery-website/checkout.php'>← Test Checkout Now</a></p>";
?>
