<?php
/**
 * Order Confirmation Page
 * Displays order details after successful checkout
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Check if user is logged in
requireLogin();

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: /bakery-website/orders.php');
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Get order details with validation that it belongs to current user
    $stmt = $pdo->prepare("
        SELECT o.*, oi.*, p.name as product_name, p.image as product_image
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?
        ORDER BY oi.id
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order_items = $stmt->fetchAll();
    
    if (empty($order_items)) {
        showToast('Order not found', 'error');
        header('Location: /bakery-website/orders.php');
        exit();
    }
    
    // Get order info from first item
    $order = [
        'id' => $order_items[0]['id'],
        'total_amount' => $order_items[0]['total_price'], // Fixed: total_price not total_amount
        'status' => $order_items[0]['status'],
        'created_at' => $order_items[0]['created_at'],
        'shipping_address' => $order_items[0]['delivery_address'], // Fixed: delivery_address not shipping_address
        'phone' => $order_items[0]['customer_phone'] // Fixed: customer_phone not phone
    ];
    
} catch (Exception $e) {
    error_log('Error loading order confirmation: ' . $e->getMessage());
    showToast('Error loading order details', 'error');
    header('Location: /bakery-website/orders.php');
    exit();
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Success Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <i class="fas fa-check text-4xl text-green-600"></i>
        </div>
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display'] mb-2">Order Confirmed!</h1>
        <p class="text-gray-600 text-lg">Thank you for your order. We'll start preparing it right away.</p>
    </div>
    
    <!-- Order Summary Card -->
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-[#8B4513] mb-2">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                <p class="text-gray-600">Placed on <?php echo date('F j, Y, g:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-semibold">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="mb-8">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Order Items</h3>
            <div class="space-y-4">
                <?php foreach ($order_items as $item): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <img src="/bakery-website/assets/images/<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'default-product.svg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="w-16 h-16 object-cover rounded-lg"
                                 onerror="this.src='/bakery-website/assets/images/default-product.svg'">
                            <div>
                                <h4 class="font-semibold text-[#8B4513]"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <p class="text-gray-600">Quantity: <?php echo $item['quantity']; ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg text-[#D2691E]">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            <p class="text-gray-500 text-sm">₱<?php echo number_format($item['price'], 2); ?> each</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Delivery Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="text-xl font-bold mb-4 text-gray-800">Delivery Information</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="mb-2"><strong>Address:</strong></p>
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <?php if (!empty($order['phone'])): ?>
                        <p class="mt-3"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <h3 class="text-xl font-bold mb-4 text-gray-800">Order Summary</h3>
                <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Delivery Fee:</span>
                        <span>₱0.00</span>
                    </div>
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total:</span>
                            <span class="text-[#D2691E]">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <button onclick="window.print()" class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition duration-300">
                <i class="fas fa-print mr-2"></i>Print Receipt
            </button>
            <a href="/bakery-website/orders.php" class="flex-1 bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300 text-center">
                <i class="fas fa-list mr-2"></i>View All Orders
            </a>
            <a href="/bakery-website/menu.php" class="flex-1 bg-[#D2691E] text-white px-6 py-3 rounded-lg hover:bg-[#8B4513] transition duration-300 text-center">
                <i class="fas fa-shopping-bag mr-2"></i>Continue Shopping
            </a>
        </div>
    </div>
    
    <!-- Estimated Delivery -->
    <div class="max-w-4xl mx-auto bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
        <i class="fas fa-truck text-3xl text-blue-600 mb-3"></i>
        <h3 class="text-xl font-bold text-blue-800 mb-2">Estimated Delivery</h3>
        <p class="text-blue-700">Your order will be delivered within <strong>30-45 minutes</strong></p>
        <p class="text-blue-600 text-sm mt-2">You'll receive a notification when your order is on the way</p>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .container > * {
        visibility: visible;
    }
    .container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    button, a {
        display: none !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>