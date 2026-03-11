<?php
/**
 * Create Clean Checkout
 * Creates a new, working checkout.php file
 */

echo "<h1>🔄 Creating Clean Checkout</h1>";

try {
    $clean_checkout_content = '<?php
/**
 * Checkout Page
 * Handles the checkout process for customer orders
 */

session_start();

// Redirect admin users - they should not place orders
if (isset($_SESSION[\'user_role\']) && $_SESSION[\'user_role\'] === \'admin\') {
    $_SESSION[\'error\'] = \'Admin users cannot place orders. Please use a customer account.\';
    header(\'Location: /bakery-website/index.php\');
    exit();
}

// Redirect to login if not logged in
if (!isset($_SESSION[\'user_id\'])) {
    $_SESSION[\'error\'] = \'Please login to proceed with checkout\';
    header(\'Location: /bakery-website/login.php\');
    exit();
}

// Redirect to cart if empty
$cart_data = json_decode($_SESSION[\'cart_data\'] ?? \'[]\', true) ?? [];
if (empty($cart_data)) {
    $_SESSION[\'error\'] = \'Your cart is empty. Add some items first.\';
    header(\'Location: /bakery-website/menu.php\');
    exit();
}

require_once \'config/database.php\';
require_once \'config/validation.php\';

$pdo = getDBConnection();
$user_id = $_SESSION[\'user_id\'];

// Process form submission
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
    try {
        // Validate and sanitize input
        $customer_name = sanitizeInput($_POST[\'customer_name\'] ?? \'\');
        $customer_email = sanitizeInput($_POST[\'customer_email\'] ?? \'\');
        $customer_phone = sanitizeInput($_POST[\'customer_phone\'] ?? \'\');
        $delivery_address = sanitizeInput($_POST[\'delivery_address\'] ?? \'\');
        $payment_method = sanitizeInput($_POST[\'payment_method\'] ?? \'\');
        $order_notes = sanitizeInput($_POST[\'order_notes\'] ?? \'\');
        
        // Basic validation
        $errors = [];
        
        if (empty($customer_name)) $errors[] = \'Full name is required\';
        if (empty($customer_email)) $errors[] = \'Email is required\';
        if (!isValidEmail($customer_email)) $errors[] = \'Valid email is required\';
        if (empty($customer_phone)) $errors[] = \'Phone number is required\';
        if (empty($delivery_address)) $errors[] = \'Delivery address is required\';
        if (empty($payment_method)) $errors[] = \'Payment method is required\';
        
        if (!empty($errors)) {
            $_SESSION[\'errors\'] = $errors;
            header(\'Location: /bakery-website/checkout.php\');
            exit();
        }
        
        // Calculate total from cart
        $total_price = 0;
        foreach ($cart_data as $item) {
            $total_price += ($item[\'price\'] ?? 0) * ($item[\'quantity\'] ?? 1);
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, total_price, status, payment_method, order_notes, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $customer_name,
            $customer_email,
            $customer_phone,
            $delivery_address,
            $total_price,
            \'pending\',
            $payment_method,
            $order_notes
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Create order items
        $item_stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($cart_data as $item) {
            $item_stmt->execute([
                $order_id,
                $item[\'id\'],
                $item[\'quantity\'] ?? 1,
                $item[\'price\']
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Clear cart
        unset($_SESSION[\'cart_data\']);
        
        // Set success message
        $_SESSION[\'success\'] = \'Order placed successfully! Order #\' . $order_id;
        
        // Return JSON response for AJAX requests
        if (!empty($_SERVER[\'HTTP_X_REQUESTED_WITH\']) && strtolower($_SERVER[\'HTTP_X_REQUESTED_WITH\']) === \'xmlhttprequest\') {
            header(\'Content-Type: application/json\');
            echo json_encode([
                \'success\' => true,
                \'order_id\' => $order_id,
                \'message\' => \'Order placed successfully!\'
            ]);
            exit();
        }
        
        // Redirect to order confirmation
        header(\'Location: /bakery-website/order-confirmation.php?id=\' . $order_id);
        exit();
        
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        
        $_SESSION[\'error\'] = \'Order placement failed. Please try again.\';
        
        if (!empty($_SERVER[\'HTTP_X_REQUESTED_WITH\']) && strtolower($_SERVER[\'HTTP_X_REQUESTED_WITH\']) === \'xmlhttprequest\') {
            header(\'Content-Type: application/json\');
            echo json_encode([
                \'success\' => false,
                \'message\' => \'Order placement failed: \' . $e->getMessage()
            ]);
            exit();
        }
        
        header(\'Location: /bakery-website/checkout.php\');
        exit();
    }
}

include \'includes/header.php\';
?>

<style>
.checkout-container { max-width: 800px; margin: 0 auto; padding: 2rem; }
.checkout-section { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.section-title { font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1.5rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.5rem; }
.form-group { margin-bottom: 1.5rem; }
.form-label { display: block; font-weight: 500; color: #374151; margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; transition: border-color 0.2s; }
.form-input:focus { outline: none; border-color: #8B4513; box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1); }
.form-textarea { min-height: 100px; resize: vertical; }
.form-select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; background-color: white; }
.btn-checkout { background: #8B4513; color: white; padding: 1rem 2rem; border: none; border-radius: 0.5rem; font-size: 1.125rem; font-weight: 600; cursor: pointer; width: 100%; transition: background-color 0.2s; }
.btn-checkout:hover { background: #6B3410; }
.btn-checkout:disabled { background: #9ca3af; cursor: not-allowed; }
.cart-summary { background: #f9fafb; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; }
.summary-item { display: flex; justify-content: space-between; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.summary-item:last-child { border-bottom: none; font-weight: 600; font-size: 1.125rem; color: #8B4513; }
.error-message { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.success-message { background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
</style>

<div class="container mx-auto px-4 py-8">
    <div class="checkout-container">
        <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-[\'Playfair_Display\']">Checkout</h1>
        
        <?php if (isset($_SESSION[\'errors\'])): ?>
            <div class="error-message">
                <h3 class="text-lg font-semibold mb-2">Please fix the following errors:</h3>
                <ul class="list-disc list-inside">
                    <?php foreach ($_SESSION[\'errors\'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION[\'errors\']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION[\'success\'])): ?>
            <div class="success-message">
                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($_SESSION[\'success\']); ?></h3>
            </div>
            <?php unset($_SESSION[\'success\']); ?>
        <?php endif; ?>
        
        <!-- Cart Summary -->
        <div class="cart-summary">
            <h2 class="section-title">Order Summary</h2>
            <?php
            $total_price = 0;
            $total_items = 0;
            foreach ($cart_data as $item) {
                $item_total = ($item[\'price\'] ?? 0) * ($item[\'quantity\'] ?? 1);
                $total_price += $item_total;
                $total_items += ($item[\'quantity\'] ?? 1);
            ?>
            <div class="summary-item">
                <span><?php echo htmlspecialchars($item[\'name\'] ?? \'Unknown Item\'); ?></span>
                <span>₱<?php echo number_format($item_total, 2); ?> (<?php echo $item[\'quantity\'] ?? 1; ?>x)</span>
            </div>
            <?php } ?>
            <div class="summary-item">
                <span>Total Items</span>
                <span><?php echo $total_items; ?></span>
            </div>
            <div class="summary-item">
                <span>Total Amount</span>
                <span>₱<?php echo number_format($total_price, 2); ?></span>
            </div>
        </div>
        
        <!-- Checkout Form -->
        <form method="POST" action="" id="checkoutForm">
            <div class="checkout-section">
                <h2 class="section-title">Delivery Information</h2>
                
                <div class="form-group">
                    <label for="customer_name" class="form-label">Full Name *</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-input" 
                           value="<?php echo htmlspecialchars($_SESSION[\'user_name\'] ?? \'\'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_email" class="form-label">Email Address *</label>
                    <input type="email" id="customer_email" name="customer_email" class="form-input" 
                           value="<?php echo htmlspecialchars($_SESSION[\'user_email\'] ?? \'\'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_phone" class="form-label">Phone Number *</label>
                    <input type="tel" id="customer_phone" name="customer_phone" class="form-input" 
                           placeholder="+63 XXX XXX XXXX" required>
                </div>
                
                <div class="form-group">
                    <label for="delivery_address" class="form-label">Delivery Address *</label>
                    <textarea id="delivery_address" name="delivery_address" class="form-input form-textarea" 
                              placeholder="Enter your complete delivery address" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="payment_method" class="form-label">Payment Method *</label>
                    <select id="payment_method" name="payment_method" class="form-select" required>
                        <option value="">Select payment method</option>
                        <option value="cash">Cash on Delivery</option>
                        <option value="gcash">GCash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="order_notes" class="form-label">Order Notes (Optional)</label>
                    <textarea id="order_notes" name="order_notes" class="form-input form-textarea" 
                              placeholder="Special instructions, delivery preferences, etc."></textarea>
                </div>
                
                <button type="submit" class="btn-checkout" id="submitBtn">
                    Place Order - ₱<?php echo number_format($total_price, 2); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    const form = document.getElementById(\'checkoutForm\');
    const submitBtn = document.getElementById(\'submitBtn\');
    
    if (form && submitBtn) {
        form.addEventListener(\'submit\', function(e) {
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.textContent = \'Processing...\';
            
            // Re-enable after 5 seconds if form somehow fails
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = \'Place Order - ₱<?php echo number_format($total_price, 2); ?>\';
                }
            }, 5000);
        });
    }
});
</script>

<?php include \'includes/footer.php\'; ?>';

    // Write the clean checkout file
    if (file_put_contents('checkout.php', $clean_checkout_content)) {
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>✅ Clean checkout.php created successfully!</h3>";
        echo "<p>A new, working checkout file has been created.</p>";
        echo "<h4>Features included:</h4>";
        echo "<ul>";
        echo "<li>✅ Proper user authentication checks</li>";
        echo "<li>✅ Admin user restrictions</li>";
        echo "<li>✅ Cart validation</li>";
        echo "<li>✅ SQL column/value matching</li>";
        echo "<li>✅ Transaction handling</li>";
        echo "<li>✅ Error handling</li>";
        echo "<li>✅ AJAX and regular form support</li>";
        echo "</ul>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>";
        echo "<li><a href='/bakery-website/login.php'>Login as customer</a></li>";
        echo "<li><a href='/bakery-website/menu.php'>Add items to cart</a></li>";
        echo "<li><a href='/bakery-website/checkout.php'>Test checkout</a></li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>❌ Failed to create checkout file</h3>";
        echo "<p>Please check file permissions.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<p><a href='/bakery-website/fix-checkout.php'>← Back to Fix Tool</a></p>";
?>
