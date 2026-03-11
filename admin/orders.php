<?php
/**
 * Advanced Orders Management Page
 * Complete order management with filters, search, and bulk actions
 */

require_once '../config/database.php';
requireAdmin();
require_once '../config/validation.php';

$pdo = getDBConnection();

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && isset($_POST['order_ids'])) {
    $orderIds = $_POST['order_ids'];
    $action = $_POST['bulk_action'];
    
    foreach ($orderIds as $orderId) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$action, $orderId]);
    }
    $message = count($orderIds) . ' orders updated successfully';
}

// Update single order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    $message = 'Order status updated successfully';
}

// Get filter parameters
$status_filter = sanitizeInput($_GET['status'] ?? '');
$date_filter = sanitizeInput($_GET['date'] ?? '');
$search = sanitizeInput($_GET['search'] ?? '');

// Build base query
$query = "
    SELECT o.*, u.name as user_name, u.email as user_email,
           COUNT(oi.id) as item_count
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
";

$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    $where_conditions[] = "DATE(o.created_at) = ?";
    $params[] = $date_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.id LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

$orders = $pdo->prepare($query);
$orders->execute($params);
$orders = $orders->fetchAll();

// Get order statistics
$orderStats = $pdo->query("
    SELECT status, COUNT(*) as count
    FROM orders
    GROUP BY status
")->fetchAll();

include '../includes/header.php';
?>

<!-- Order Management Styles -->
<style>
.order-filters { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.order-table { background: white; border-radius: 0.75rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.bulk-actions { background: #f8f9fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; }
.order-row:hover { background-color: #f8f9fa; }
.action-buttons { display: flex; gap: 0.5rem; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Order Management</h1>
        <div class="flex items-center space-x-4">
            <a href="/bakery-website/admin/dashboard.php" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Order Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <?php foreach ($orderStats as $stat): 
            $totalOrders = array_sum(array_column($orderStats, 'count'));
            $percentage = $totalOrders > 0 ? ($stat['count'] / $totalOrders) * 100 : 0;
        ?>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="status-badge status-<?php echo $stat['status']; ?> mb-2">
                    <?php echo ucfirst($stat['status']); ?>
                </div>
                <p class="text-2xl font-bold text-gray-800"><?php echo $stat['count']; ?></p>
                <p class="text-sm text-gray-500"><?php echo round($percentage); ?>% of total</p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="order-filters">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Filter Orders</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Order ID, customer name, email..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <?php if (isset($message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="text-left py-3 px-4">Order ID</th>
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-left py-3 px-4">Email</th>
                        <th class="text-left py-3 px-4">Total</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Date</th>
                        <th class="text-left py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">#<?php echo $order['id']; ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($order['user_email']); ?></td>
                        <td class="py-3 px-4 font-bold text-[#D2691E]">$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-sm 
                                <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                          ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                          ($order['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                          'bg-red-100 text-red-800')); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td class="py-3 px-4">
                            <form method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="text-sm border rounded px-2 py-1">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="bg-[#8B4513] text-white px-3 py-1 rounded text-sm hover:bg-[#D2691E] transition duration-300">
                                    Update
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>