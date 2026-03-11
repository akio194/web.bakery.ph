<?php
/**
 * Advanced Admin Dashboard
 * Comprehensive bakery management system
 */

require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();

// Today's Statistics
$today = date('Y-m-d');
$todayOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
$todayOrders->execute([$today]);
$todayOrdersCount = $todayOrders->fetchColumn();

$todayRevenue = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE DATE(created_at) = ? AND status != 'cancelled'");
$todayRevenue->execute([$today]);
$todayRevenueAmount = $todayRevenue->fetchColumn() ?? 0;

// Weekly/Monthly Sales
$weekStart = date('Y-m-d', strtotime('-7 days'));
$monthStart = date('Y-m-d', strtotime('-30 days'));

$weeklyRevenue = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE created_at >= ? AND status != 'cancelled'");
$weeklyRevenue->execute([$weekStart]);
$weeklyRevenueAmount = $weeklyRevenue->fetchColumn() ?? 0;

$monthlyRevenue = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE created_at >= ? AND status != 'cancelled'");
$monthlyRevenue->execute([$monthStart]);
$monthlyRevenueAmount = $monthlyRevenue->fetchColumn() ?? 0;

// Top Selling Products
$topProducts = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.price * oi.quantity) as revenue
    FROM products p
    JOIN order_items oi ON p.id = oi.product_id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'cancelled'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();

// Recent Orders with status
$recentOrders = $pdo->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
")->fetchAll();

// Customer Registration Stats
$todayCustomers = $pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?");
$todayCustomers->execute([$today]);
$todayCustomersCount = $todayCustomers->fetchColumn();

$totalCustomers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Low Stock Alerts (if you add stock column to products)
$lowStockAlerts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn(); // Placeholder

// Order Status Distribution
$orderStats = $pdo->query("
    SELECT status, COUNT(*) as count
    FROM orders
    GROUP BY status
")->fetchAll();

// Daily Sales Chart Data (Last 7 days)
$dailySales = [];
for($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sales = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE DATE(created_at) = ? AND status != 'cancelled'");
    $sales->execute([$date]);
    $dailySales[] = [
        'date' => date('M d', strtotime($date)),
        'amount' => $sales->fetchColumn() ?? 0
    ];
}

include '../includes/header.php';
?>

<!-- Admin Dashboard Styles -->
<style>
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
.stat-card { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
.stat-card:hover { transform: translateY(-2px); }
.stat-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.chart-container { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.quick-action { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.2s; }
.quick-action:hover { transform: translateY(-2px); box-shadow: 0 8px 12px rgba(0,0,0,0.15); }
.sales-chart { height: 300px; display: flex; align-items: flex-end; justify-content: space-between; gap: 0.5rem; }
.sales-bar { background: linear-gradient(to top, #8B4513, #D2691E); border-radius: 0.25rem 0.25rem 0 0; flex: 1; min-height: 20px; position: relative; }
.sales-bar:hover { opacity: 0.8; }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Dashboard Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Admin Dashboard</h1>
        <div class="flex items-center space-x-4">
            <span class="text-gray-600">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
            <span class="text-sm text-gray-500"><?php echo date('F d, Y'); ?></span>
        </div>
    </div>

    <!-- Today's Metrics -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Today's Performance</h2>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Today's Orders</p>
                        <p class="text-3xl font-bold text-[#8B4513]"><?php echo $todayOrdersCount; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Total orders today</p>
                    </div>
                    <div class="stat-icon bg-[#FDF8F5]">
                        <i class="fas fa-shopping-bag text-2xl text-[#8B4513]"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Today's Revenue</p>
                        <p class="text-3xl font-bold text-green-600">₱<?php echo number_format($todayRevenueAmount, 2); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Sales today</p>
                    </div>
                    <div class="stat-icon bg-green-50">
                        <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">New Customers</p>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $todayCustomersCount; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Registered today</p>
                    </div>
                    <div class="stat-icon bg-blue-50">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Low Stock Alerts</p>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $lowStockAlerts; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Items need attention</p>
                    </div>
                    <div class="stat-icon bg-orange-50">
                        <i class="fas fa-exclamation-triangle text-2xl text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="chart-container">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Sales Overview</h3>
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-500">Weekly</p>
                    <p class="text-2xl font-bold text-[#8B4513]">₱<?php echo number_format($weeklyRevenueAmount, 2); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Monthly</p>
                    <p class="text-2xl font-bold text-[#8B4513]">₱<?php echo number_format($monthlyRevenueAmount, 2); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Customers</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $totalCustomers; ?></p>
                </div>
            </div>
            
            <!-- Simple Bar Chart -->
            <div class="sales-chart">
                <?php 
                $maxAmount = max(array_column($dailySales, 'amount'));
                foreach ($dailySales as $sale): 
                    $height = $maxAmount > 0 ? ($sale['amount'] / $maxAmount) * 100 : 0;
                ?>
                    <div class="sales-bar" style="height: <?php echo $height; ?>%;" title="<?php echo $sale['date']; ?>: ₱<?php echo number_format($sale['amount'], 2); ?>">
                        <div style="position: absolute; top: -20px; left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #666;">
                            <?php echo $sale['date']; ?>
                        </div>
                        <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #666;">
                            ₱<?php echo number_format($sale['amount'], 0); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="chart-container">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Order Status</h3>
            <div class="space-y-3">
                <?php foreach ($orderStats as $stat): 
                    $totalOrders = array_sum(array_column($orderStats, 'count'));
                    $percentage = $totalOrders > 0 ? ($stat['count'] / $totalOrders) * 100 : 0;
                ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="status-badge status-<?php echo $stat['status']; ?>">
                                <?php echo ucfirst($stat['status']); ?>
                            </span>
                            <span class="text-sm text-gray-600"><?php echo $stat['count']; ?> orders</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-[#8B4513] h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="text-sm text-gray-600"><?php echo round($percentage); ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Top Selling Products & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Top Selling Products -->
        <div class="chart-container">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Top Selling Products</h3>
            <div class="space-y-3">
                <?php foreach ($topProducts as $index => $product): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <span class="text-lg font-bold text-[#8B4513]">#<?php echo $index + 1; ?></span>
                            <div>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo $product['total_sold']; ?> sold</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">₱<?php echo number_format($product['revenue'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <h3 class="text-xl font-bold mb-4 text-gray-800">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="/bakery-website/admin/orders.php" class="quick-action">
                    <div class="flex items-center space-x-3">
                        <div class="stat-icon bg-blue-50">
                            <i class="fas fa-clipboard-list text-xl text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Manage Orders</h4>
                            <p class="text-sm text-gray-500">View and update orders</p>
                        </div>
                    </div>
                </a>
                
                <a href="/bakery-website/admin/products.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-box mr-2"></i>Manage Products
                </a>
                
                <a href="/bakery-website/admin/inventory.php" class="quick-action">
                    <div class="flex items-center space-x-3">
                        <div class="stat-icon bg-orange-50">
                            <i class="fas fa-boxes text-xl text-orange-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Inventory Management</h4>
                            <p class="text-sm text-gray-500">Track stock, expiration, and batch management</p>
                        </div>
                    </div>
                </a>
                
                <a href="/bakery-website/admin/customers.php" class="quick-action">
                    <div class="flex items-center space-x-3">
                        <div class="stat-icon bg-purple-50">
                            <i class="fas fa-users text-xl text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Customers</h4>
                            <p class="text-sm text-gray-500">View customers</p>
                        </div>
                    </div>
                </a>
                
                <a href="/bakery-website/admin/analytics.php" class="quick-action">
                    <div class="flex items-center space-x-3">
                        <div class="stat-icon bg-orange-50">
                            <i class="fas fa-chart-line text-xl text-orange-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Analytics</h4>
                            <p class="text-sm text-gray-500">View reports</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="chart-container">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Recent Orders</h3>
            <a href="/bakery-website/admin/orders.php" class="text-[#8B4513] hover:text-[#D2691E] text-sm font-medium">View All Orders →</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-gray-600">Order ID</th>
                        <th class="text-left py-3 text-gray-600">Customer</th>
                        <th class="text-left py-3 text-gray-600">Items</th>
                        <th class="text-left py-3 text-gray-600">Total</th>
                        <th class="text-left py-3 text-gray-600">Status</th>
                        <th class="text-left py-3 text-gray-600">Date</th>
                        <th class="text-left py-3 text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): 
                        // Get order items count
                        $itemCount = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                        $itemCount->execute([$order['id']]);
                        $itemsCount = $itemCount->fetchColumn();
                    ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3">
                                <span class="font-medium text-gray-800">#<?php echo $order['id']; ?></span>
                            </td>
                            <td class="py-3">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['user_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="text-gray-600"><?php echo $itemsCount; ?> items</span>
                            </td>
                            <td class="py-3">
                                <span class="font-bold text-green-600">₱<?php echo number_format($order['total_price'], 2); ?></span>
                            </td>
                            <td class="py-3">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="text-gray-600"><?php echo date('M d, H:i', strtotime($order['created_at'])); ?></span>
                            </td>
                            <td class="py-3">
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
    </div>
</div>

<?php include '../includes/footer.php'; ?>