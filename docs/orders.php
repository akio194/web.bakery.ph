<?php
/**
 * Orders Page (orders.php)
 * Displays user's order history
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to view your orders';
    header('Location: /bakery-website/login.php');
    exit();
}

// Redirect admin users - they should only access admin pages
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /bakery-website/admin/dashboard.php');
    exit();
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user statistics
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_price), 0) as total_spent FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    // Get orders with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $per_page, $offset]);
    $orders = $stmt->fetchAll();

    // Get total count for pagination
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $count_stmt->execute([$user_id]);
    $total_orders_count = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_orders_count / $per_page);

} catch (Exception $e) {
    error_log('Error loading orders: ' . $e->getMessage());
    $orders = [];
    $stats = ['total_orders' => 0, 'total_spent' => 0];
    $total_pages = 1;
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-['Playfair_Display']">Your Orders</h1>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-[#D2691E]"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-[#D2691E]">₱<?php echo number_format($stats['total_spent'], 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-[#D2691E]">₱<?php echo $stats['total_orders'] > 0 ? number_format($stats['total_spent'] / $stats['total_orders'], 2) : '0.00'; ?></div>
                <div class="stat-label">Average Order Value</div>
            </div>
        </div>
        
        <!-- Orders List -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-2xl font-bold text-[#8B4513]">Order History</h2>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-shopping-bag text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-600">No orders yet</h3>
                    <p class="text-gray-500 mt-2">Start shopping to see your orders here!</p>
                    <a href="/bakery-website/menu.php" class="inline-block mt-4 bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300">
                        Browse Menu
                    </a>
                </div>
            <?php else: ?>
                <div class="divide-y">
                    <?php foreach ($orders as $order): ?>
                        <div class="p-6 hover:bg-gray-50 transition duration-200">
                            <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-[#8B4513]">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo date('F j, Y, g:i A', strtotime($order['created_at'])); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $order['item_count']; ?> items</p>
                                </div>
                                <div class="mt-3 md:mt-0 flex items-center space-x-4">
                                    <span class="bg-[#FDF8F5] text-[#8B4513] px-3 py-1 rounded-full text-sm font-semibold">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <a href="/bakery-website/order-details.php?id=<?php echo $order['id']; ?>" 
                                       class="text-[#8B4513] hover:text-[#D2691E] transition duration-300">
                                        View Details <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Total Amount</p>
                                <p class="text-2xl font-bold text-[#D2691E]">₱<?php echo number_format($order['total_price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="p-6 border-t">
                        <div class="flex justify-center">
                            <nav class="flex items-center space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" 
                                       class="px-3 py-2 text-[#8B4513] bg-white border border-gray-300 rounded-lg hover:bg-[#FDF8F5] transition duration-300">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="px-3 py-2 bg-[#8B4513] text-white rounded-lg">
                                            <?php echo $i; ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?>" 
                                           class="px-3 py-2 text-[#8B4513] bg-white border border-gray-300 rounded-lg hover:bg-[#FDF8F5] transition duration-300">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" 
                                       class="px-3 py-2 text-[#8B4513] bg-white border border-gray-300 rounded-lg hover:bg-[#FDF8F5] transition duration-300">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-label {
    @apply text-gray-600 text-sm mt-2;
}
</style>

<?php include 'includes/footer.php'; ?>