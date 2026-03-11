<?php
/**
 * Order Details Page
 * Shows comprehensive order information with print functionality
 */

require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: /bakery-website/admin/orders.php');
    exit();
}

// Get order details with customer and items
$stmt = $pdo->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: /bakery-website/admin/orders.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>
.order-details { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.order-header { border-bottom: 2px solid #f3f4f6; padding-bottom: 1.5rem; margin-bottom: 2rem; }
.order-section { margin-bottom: 2rem; }
.status-badge { padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 500; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.item-row { border-bottom: 1px solid #f3f4f6; padding: 1rem 0; }
.item-row:last-child { border-bottom: none; }
.print-only { display: none; }
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .order-details { box-shadow: none; }
}
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8 no-print">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Order Details</h1>
        <div class="flex items-center space-x-4">
            <a href="/bakery-website/admin/orders.php" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Orders
            </a>
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i>Print Order
            </button>
        </div>
    </div>

    <div class="order-details">
        <!-- Order Header -->
        <div class="order-header">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Order #<?php echo $order['id']; ?></h2>
                    <p class="text-gray-600">Placed on <?php echo date('F d, Y at g:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="text-right">
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                    <p class="text-sm text-gray-500 mt-2">Status</p>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="order-section">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Customer Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer_phone'] ?: 'Not provided'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Account</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['user_name']); ?> (<?php echo htmlspecialchars($order['user_email']); ?>)</p>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="order-section">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Delivery Information</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="font-medium text-gray-800"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                <?php if (!empty($order['order_notes'])): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Order Notes:</p>
                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($order['order_notes'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items -->
        <div class="order-section">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Order Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 text-gray-700">Product</th>
                            <th class="text-left py-3 text-gray-700">Price</th>
                            <th class="text-left py-3 text-gray-700">Quantity</th>
                            <th class="text-right py-3 text-gray-700">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr class="item-row">
                                <td class="py-3">
                                    <div class="flex items-center space-x-3">
                                        <img src="/bakery-website/assets/images/<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'default-product.svg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="w-12 h-12 object-cover rounded"
                                             onerror="this.src='/bakery-website/assets/images/default-product.svg'">
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="text-gray-600">₱<?php echo number_format($item['price'], 2); ?></span>
                                </td>
                                <td class="py-3">
                                    <span class="text-gray-600"><?php echo $item['quantity']; ?></span>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="font-bold text-gray-800">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="order-section">
            <div class="border-t pt-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>₱<?php echo number_format($order['total_price'], 2); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Delivery Fee</span>
                        <span>₱0.00</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tax</span>
                        <span>₱0.00</span>
                    </div>
                    <div class="flex justify-between text-xl font-bold text-gray-800 border-t pt-2">
                        <span>Total</span>
                        <span>₱<?php echo number_format($order['total_price'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Actions -->
        <div class="order-section no-print">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Order Actions</h3>
            <div class="flex flex-wrap gap-4">
                <form method="POST" class="inline">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="ml-2 bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                        Update Status
                    </button>
                </form>
                
                <button onclick="sendEmailNotification(<?php echo $order['id']; ?>)" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-envelope mr-2"></i>Send Email
                </button>
                
                <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-print mr-2"></i>Print Receipt
                </button>
            </div>
        </div>

        <!-- Print Header (only visible when printing) -->
        <div class="print-only text-center mb-6">
            <h1 class="text-2xl font-bold text-[#8B4513]">Sweet Delights Bakery</h1>
            <p class="text-gray-600">Order Receipt</p>
        </div>
    </div>
</div>

<script>
function sendEmailNotification(orderId) {
    // Placeholder for email notification functionality
    alert('Email notification would be sent to customer for order #' + orderId);
}

// Update order status
document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('/bakery-website/admin/update-order-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating order status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
