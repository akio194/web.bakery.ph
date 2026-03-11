<?php
/**
 * Cart Page (cart.php)
 * Displays shopping cart with JavaScript functionality
 */

require_once 'config/database.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to view your cart';
    header('Location: /bakery-website/login.php');
    exit();
}

// Redirect admin users - they cannot place orders
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /bakery-website/admin/dashboard.php');
    exit();
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-['Playfair_Display']">Your Shopping Cart</h1>
    
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Cart Items -->
        <div class="lg:w-2/3">
            <div id="cart-items" class="space-y-4">
                <!-- Cart items will be loaded here via JavaScript -->
            </div>
            
            <div id="empty-cart" class="text-center py-12 hidden">
                <i class="fas fa-shopping-cart text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-600">Your cart is empty</h3>
                <p class="text-gray-500 mt-2">Browse our menu and add some delicious items!</p>
                <a href="/bakery-website/menu.php" class="inline-block mt-4 bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300">
                    View Menu
                </a>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="lg:w-1/3">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                <h2 class="text-2xl font-bold mb-4 text-[#8B4513]">Order Summary</h2>
                
                <div id="cart-summary" class="space-y-3 mb-4">
                    <!-- Summary items will be loaded here -->
                </div>
                
                <div class="border-t pt-4 mb-6">
                    <div class="flex justify-between items-center text-xl font-bold">
                        <span>Total:</span>
                        <span id="cart-total" class="text-[#D2691E]">₱0.00</span>
                    </div>
                </div>
                
                <button id="checkout-btn" onclick="proceedToCheckout()" 
                        class="w-full bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load cart items on page load - let service.js handle it
document.addEventListener('DOMContentLoaded', function() {
    // Trigger service.js cart display
    if (window.CartService) {
        CartService.updateCartDisplay();
    }
    
    // Override proceedToCheckout to use service.js cart
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.onclick = function() {
            proceedToCheckout();
        };
    }
});

function proceedToCheckout() {
    // Use service.js cart
    const cart = window.CartService ? CartService.getCart() : [];
    if (cart.length === 0) {
        showToast('Your cart is empty', 'error');
        return;
    }
    
    // Store cart in session for checkout
    fetch('/bakery-website/api/save-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart: cart })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/bakery-website/checkout.php';
        } else {
            showToast('Error saving cart', 'error');
        }
    });
}

// Helper function to update cart count (for compatibility)
function updateCartCount() {
    if (window.CartService) {
        CartService.updateCartCount();
    }
}
</script>

<?php include 'includes/footer.php'; ?>