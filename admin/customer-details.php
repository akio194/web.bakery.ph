<?php
/**
 * Customer Details Page
 * View detailed information about a specific customer
 */

require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();

// Get customer ID from URL
$customer_id = $_GET['id'] ?? 0;

if (!$customer_id) {
    header('Location: /bakery-website/admin/customers.php');
    exit();
}

// Get customer details
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.phone,
        u.address,
        u.created_at as registration_date,
        COUNT(DISTINCT o.id) as total_orders,
        COUNT(DISTINCT CASE WHEN o.status != 'cancelled' THEN o.id END) as completed_orders,
        SUM(CASE WHEN o.status != 'cancelled' THEN o.total_price ELSE 0 END) as total_spent,
        AVG(CASE WHEN o.status != 'cancelled' THEN o.total_price ELSE NULL END) as avg_order_value,
        MAX(o.created_at) as last_order_date,
        MIN(o.created_at) as first_order_date,
        CASE 
            WHEN MAX(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'active'
            WHEN MAX(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 'regular'
            ELSE 'inactive'
        END as customer_status
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.id = ? AND u.role = 'customer'
    GROUP BY u.id
");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: /bakery-website/admin/customers.php');
    exit();
}

// Get customer orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT 
        o.id,
        o.total_price,
        o.status,
        o.created_at,
        o.payment_method,
        COUNT(oi.id) as item_count,
        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as order_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$customer_id, $limit, $offset]);
$orders = $stmt->fetchAll();

// Get total orders count for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$stmt->execute([$customer_id]);
$total_orders = $stmt->fetch()['total'];
$total_pages = ceil($total_orders / $limit);

// Get customer preferences (favorite products)
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        SUM(oi.quantity) as total_quantity,
        COUNT(DISTINCT oi.order_id) as order_frequency,
        SUM(oi.quantity * oi.price) as total_spent_on_product
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = ? AND o.status != 'cancelled'
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 5
");
$stmt->execute([$customer_id]);
$favorite_products = $stmt->fetchAll();

// Get order statistics by month
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as order_count,
        SUM(total_price) as monthly_spent
    FROM orders 
    WHERE user_id = ? AND status != 'cancelled'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$stmt->execute([$customer_id]);
$monthly_stats = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>
.customer-details-container { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.customer-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem; }
.customer-info { flex: 1; }
.customer-name { font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; }
.customer-contact { color: #6b7280; margin-bottom: 0.25rem; }
.customer-actions { display: flex; gap: 1rem; }
.btn-action { padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; text-decoration: none; }
.btn-primary { background: #8B4513; color: white; }
.btn-primary:hover { background: #6B3410; }
.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: linear-gradient(135deg, #8B4513, #D2691E); color: white; padding: 1.5rem; border-radius: 0.75rem; text-align: center; }
.stat-value { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
.stat-label { opacity: 0.9; font-size: 0.875rem; }
.section-card { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.section-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; margin-bottom: 1.5rem; }
.orders-table { width: 100%; border-collapse: collapse; }
.orders-table th { background: #f3f4f6; color: #1f2937; padding: 1rem; text-align: left; font-weight: 600; }
.orders-table td { padding: 1rem; border-bottom: 1px solid #e5e7eb; }
.orders-table tr:hover { background: #f9fafb; }
.order-status { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.customer-status { padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; display: inline-block; }
.status-active { background: #d1fae5; color: #065f46; }
.status-regular { background: #fef3c7; color: #92400e; }
.status-inactive { background: #fee2e2; color: #991b1b; }
.favorite-products { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.product-card { background: #f9fafb; padding: 1rem; border-radius: 0.5rem; }
.product-name { font-weight: 600; color: #1f2937; margin-bottom: 0.5rem; }
.product-stats { font-size: 0.875rem; color: #6b7280; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 1.5rem; }
.page-link { padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; text-decoration: none; color: #374151; }
.page-link:hover { background: #f3f4f6; }
.page-link.active { background: #8B4513; color: white; border-color: #8B4513; }
.chart-container { height: 200px; display: flex; align-items: flex-end; gap: 0.5rem; margin-top: 1rem; }
.chart-bar { background: linear-gradient(to top, #8B4513, #D2691E); border-radius: 0.25rem 0.25rem 0 0; flex: 1; min-height: 20px; position: relative; }
.chart-bar:hover { opacity: 0.8; }
.chart-label { position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #666; white-space: nowrap; }
.chart-value { position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #666; white-space: nowrap; }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Customer Details</h1>
        <a href="/bakery-website/admin/customers.php" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Customers
        </a>
    </div>

    <!-- Customer Header -->
    <div class="customer-details-container">
        <div class="customer-header">
            <div class="customer-info">
                <div class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></div>
                <div class="customer-contact">
                    <i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($customer['email']); ?>
                </div>
                <?php if ($customer['phone']): ?>
                    <div class="customer-contact">
                        <i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($customer['phone']); ?>
                    </div>
                <?php endif; ?>
                <?php if ($customer['address']): ?>
                    <div class="customer-contact">
                        <i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($customer['address']); ?>
                    </div>
                <?php endif; ?>
                <div class="customer-contact">
                    <i class="fas fa-calendar mr-2"></i>Member since <?php echo date('M d, Y', strtotime($customer['registration_date'])); ?>
                </div>
            </div>
            <div class="customer-actions">
                <span class="customer-status status-<?php echo $customer['customer_status']; ?>">
                    <?php echo ucfirst($customer['customer_status']); ?> Customer
                </span>
            </div>
        </div>

        <!-- Customer Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $customer['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₱<?php echo number_format($customer['total_spent'], 0); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₱<?php echo number_format($customer['avg_order_value'], 0); ?></div>
                <div class="stat-label">Avg Order Value</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $customer['completed_orders']; ?></div>
                <div class="stat-label">Completed Orders</div>
            </div>
        </div>
    </div>

    <!-- Favorite Products -->
    <?php if (!empty($favorite_products)): ?>
        <div class="section-card">
            <h3 class="section-title">Favorite Products</h3>
            <div class="favorite-products">
                <?php foreach ($favorite_products as $product): ?>
                    <div class="product-card">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-stats">
                            <div><?php echo $product['total_quantity']; ?> items purchased</div>
                            <div><?php echo $product['order_frequency']; ?> orders</div>
                            <div>₱<?php echo number_format($product['total_spent_on_product'], 2); ?> spent</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Monthly Spending Chart -->
    <?php if (!empty($monthly_stats)): ?>
        <div class="section-card">
            <h3 class="section-title">Monthly Spending Trends</h3>
            <div class="chart-container">
                <?php 
                $maxSpent = max(array_column($monthly_stats, 'monthly_spent'));
                foreach ($monthly_stats as $stat): 
                    $height = $maxSpent > 0 ? ($stat['monthly_spent'] / $maxSpent) * 100 : 0;
                ?>
                    <div class="chart-bar" style="height: <?php echo $height; ?>%;" 
                         title="<?php echo date('M Y', strtotime($stat['month'] . '-01')); ?>: ₱<?php echo number_format($stat['monthly_spent'], 2); ?>">
                        <div class="chart-value">₱<?php echo number_format($stat['monthly_spent'], 0); ?></div>
                        <div class="chart-label"><?php echo date('M', strtotime($stat['month'] . '-01')); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Order History -->
    <div class="section-card">
        <div class="flex justify-between items-center mb-4">
            <h3 class="section-title">Order History</h3>
            <span class="text-gray-600">Showing <?php echo count($orders); ?> of <?php echo $total_orders; ?> orders</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <span class="font-medium">#<?php echo $order['id']; ?></span>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900">
                                    <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo date('h:i A', strtotime($order['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <?php echo $order['item_count']; ?> items
                                </div>
                                <div class="text-xs text-gray-500" title="<?php echo htmlspecialchars($order['order_items']); ?>">
                                    <?php echo strlen($order['order_items']) > 50 ? substr($order['order_items'], 0, 50) . '...' : $order['order_items']; ?>
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-green-600">
                                    ₱<?php echo number_format($order['total_price'], 2); ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900">
                                    <?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?>
                                </div>
                            </td>
                            <td>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/bakery-website/admin/order-details.php?id=<?php echo $order['id']; ?>" 
                                   class="text-[#8B4513] hover:text-[#D2691E] text-sm font-medium">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="text-center py-8">
                <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No orders found for this customer.</p>
            </div>
        <?php endif; ?>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?id=<?php echo $customer_id; ?>&page=<?php echo $page - 1; ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php elseif ($i <= 3 || $i >= $total_pages - 2 || ($i >= $page - 1 && $i <= $page + 1)): ?>
                        <a href="?id=<?php echo $customer_id; ?>&page=<?php echo $i; ?>" 
                           class="page-link"><?php echo $i; ?></a>
                    <?php elseif ($i == 4 || $i == $total_pages - 3): ?>
                        <span class="page-link">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?id=<?php echo $customer_id; ?>&page=<?php echo $page + 1; ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
