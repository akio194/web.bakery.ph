<?php
session_start();

/**
 * Checkout Page (checkout.php)
 * Handles order placement and processing
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to checkout';
    header('Location: /bakery-website/login.php');
    exit();
}

// Redirect admin users - they cannot place orders
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /bakery-website/admin/dashboard.php');
    exit();
}

$pdo = getDBConnection();

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Security token expired. Please try again.']);
        exit();
    }
    
    // Validate form data
    $errors = [];
    
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Validate name
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (!isValidName($name)) {
        $errors[] = 'Please enter a valid name';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Validate phone
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!isValidPhone($phone)) {
        $errors[] = 'Please enter a valid phone number';
    }
    
    // Validate address
    if (empty($address)) {
        $errors[] = 'Address is required';
    } elseif (!isValidAddress($address)) {
        $errors[] = 'Please enter a complete address';
    }
    
    if (empty($errors)) {
        // Get cart from POST data (sent from JavaScript)
        $cart = json_decode($_POST['cart_data'], true);
        
        if (empty($cart)) {
            $errors[] = 'Cart is empty';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Calculate total
                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
                
                // Insert order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_price, status, customer_name, customer_email, customer_phone, delivery_address, order_notes) 
                    VALUES (?, ?, 'pending', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $total,
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['address'],
                    $_POST['notes'] ?? ''
                ]);
                
                $orderId = $pdo->lastInsertId();
                
                // Insert order items
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($cart as $item) {
                    $stmt->execute([
                        $orderId,
                        $item['id'],
                        $item['quantity'],
                        $item['price']
                    ]);
                }
                
                $pdo->commit();
                
                // Clear cart from database session
                $_SESSION['cart'] = [];
                
                // Return success response with order ID
                echo json_encode([
                    'success' => true,
                    'order_id' => $orderId,
                    'message' => 'Order placed successfully!'
                ]);
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => 'Error processing order: ' . $e->getMessage()
                ]);
                exit();
            }
        }
    }
    
    // If there are errors, return them
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);
    exit();
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-['Playfair_Display']">Checkout</h1>
        
        <!-- Loading Spinner (hidden by default) -->
        <div id="loading-spinner" class="hidden fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white p-8 rounded-lg shadow-xl text-center">
                <div class="spinner mx-auto mb-4"></div>
                <p class="text-gray-700">Processing your order...</p>
            </div>
        </div>
        
        <!-- Order Summary Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4 text-[#8B4513]">Order Summary</h2>
            <div id="order-summary-items" class="space-y-3 mb-4">
                <!-- Cart items will be loaded here via JavaScript -->
            </div>
            <div class="border-t pt-4">
                <div class="flex justify-between items-center text-xl font-bold">
                    <span>Total:</span>
                    <span id="order-total" class="text-[#D2691E]">₱0.00</span>
                </div>
            </div>
        </div>
        
        <!-- Checkout Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-4 text-[#8B4513]">Delivery Information</h2>
            
            <div id="error-messages" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"></div>
            
            <form id="checkout-form" class="space-y-4">
                <input type="hidden" name="cart_data" id="cart_data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Full Name *</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Email *</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Phone Number *</label>
                    <input type="tel" name="phone" id="phone" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Delivery Address *</label>
                    <textarea name="address" id="address" rows="3" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]"></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Order Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]"
                              placeholder="Any special instructions? (e.g., allergy information, delivery instructions)"></textarea>
                </div>
                
                <button type="submit" id="place-order-btn" class="w-full bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300 font-bold text-lg">
                    Place Order
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Order Confirmation Modal (hidden by default) -->
<div id="confirmation-modal" class="hidden fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl max-w-md text-center">
        <div class="text-green-500 text-6xl mb-4">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 class="text-2xl font-bold text-[#8B4513] mb-4">Order Placed Successfully!</h2>
        <p class="text-gray-600 mb-2">Thank you for your order. We'll start preparing it right away!</p>
        <p class="text-gray-600 mb-6">Order #: <span id="order-number" class="font-bold text-[#D2691E]"></span></p>
        <div class="flex space-x-4 justify-center">
            <a href="/bakery-website/menu.php" class="bg-[#8B4513] text-white px-6 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                Continue Shopping
            </a>
            <a href="/bakery-website/orders.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition duration-300">
                View Orders
            </a>
        </div>
    </div>
</div>

<script>
// Load cart items and display order summary
document.addEventListener('DOMContentLoaded', function() {
    loadOrderSummary();
    setupFormSubmission();
});

function loadOrderSummary() {
    // Use CartService to get cart data
    const cart = window.CartService ? CartService.getCart() : JSON.parse(localStorage.getItem('cart')) || [];
    const summaryContainer = document.getElementById('order-summary-items');
    const totalElement = document.getElementById('order-total');
    const cartDataInput = document.getElementById('cart_data');
    
    if (cart.length === 0) {
        // Redirect to menu if cart is empty
        window.location.href = '/bakery-website/menu.php';
        return;
    }
    
    // Set cart data for form submission
    cartDataInput.value = JSON.stringify(cart);
    
    let summaryHtml = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        summaryHtml += `
            <div class="flex justify-between items-center py-2 border-b">
                <div class="flex items-center space-x-3">
                    <span class="font-medium text-[#8B4513]">${item.quantity}x</span>
                    <span>${item.name}</span>
                </div>
                <span class="font-bold">₱${itemTotal.toFixed(2)}</span>
            </div>
        `;
    });
    
    summaryContainer.innerHTML = summaryHtml;
    totalElement.textContent = `₱${subtotal.toFixed(2)}`;
}

function setupFormSubmission() {
    const form = document.getElementById('checkout-form');
    const spinner = document.getElementById('loading-spinner');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const errorContainer = document.getElementById('error-messages');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Show loading spinner
        spinner.classList.remove('hidden');
        placeOrderBtn.disabled = true;
        placeOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
        
        // Hide any previous errors
        errorContainer.classList.add('hidden');
        
        // Get form data
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/bakery-website/checkout.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Clear cart using CartService
                if (window.CartService) {
                    CartService.clearCart();
                } else {
                    localStorage.removeItem('cart');
                }
                
                // Update cart count in navigation
                updateCartCount();
                
                // Redirect to order confirmation page
                window.location.href = '/bakery-website/order-confirmation.php?order_id=' + result.order_id;
                
                // Hide spinner
                spinner.classList.add('hidden');
                
                // Show success message
                showToast('Order placed successfully!', 'success');
            } else {
                // Show errors
                if (result.errors) {
                    errorContainer.innerHTML = '<ul class="list-disc list-inside">' + 
                        result.errors.map(error => `<li>${error}</li>`).join('') + 
                        '</ul>';
                    errorContainer.classList.remove('hidden');
                } else {
                    showToast(result.message || 'Error placing order', 'error');
                }
                
                // Hide spinner and re-enable button
                spinner.classList.add('hidden');
                placeOrderBtn.disabled = false;
                placeOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showToast('Network error. Please try again.', 'error');
            
            // Hide spinner and re-enable button
            spinner.classList.add('hidden');
            placeOrderBtn.disabled = false;
            placeOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });
}

function validateForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    let isValid = true;
    let errorMessages = [];
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
    
    // Validate name
    if (!name) {
        showFieldError('name', 'Name is required');
        isValid = false;
    }
    
    // Validate email
    if (!email) {
        showFieldError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showFieldError('email', 'Please enter a valid email');
        isValid = false;
    }
    
    // Validate phone
    if (!phone) {
        showFieldError('phone', 'Phone number is required');
        isValid = false;
    } else if (!isValidPhone(phone)) {
        showFieldError('phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    // Validate address
    if (!address) {
        showFieldError('address', 'Address is required');
        isValid = false;
    }
    
    if (!isValid) {
        showToast('Please fill in all required fields correctly', 'error');
    }
    
    return isValid;
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('border-red-500');
    
    const errorDiv = document.createElement('p');
    errorDiv.className = 'error-message text-red-500 text-sm mt-1';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^[\d\s\-\(\)\+]{10,}$/;
    return re.test(phone);
}

function updateCartCount() {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(el => {
        el.textContent = '0';
        el.classList.add('hidden');
    });
}

function showToast(message, type) {
    // This function should be available from service.js
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}

// Close modal when clicking outside
document.getElementById('confirmation-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});
</script>

<?php include 'includes/footer.php'; ?>