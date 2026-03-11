<?php
/**
 * Analytics Dashboard Page
 * Comprehensive business analytics and reporting
 */

require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();

// Get date range parameters
$date_range = $_GET['date_range'] ?? '30days';
$report_type = $_GET['report'] ?? 'overview';

// Calculate date ranges
$date_ranges = [
    '7days' => date('Y-m-d', strtotime('-7 days')),
    '30days' => date('Y-m-d', strtotime('-30 days')),
    '90days' => date('Y-m-d', strtotime('-90 days')),
    '1year' => date('Y-m-d', strtotime('-1 year'))
];

$start_date = $date_ranges[$date_range];

// Get analytics data based on report type
$data = [];
switch ($report_type) {
    case 'sales':
        // Sales analytics
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as sale_date,
                COUNT(*) as order_count,
                SUM(total_price) as total_revenue,
                AVG(total_price) as avg_order_value
            FROM orders 
            WHERE created_at >= ? AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY sale_date DESC
        ");
        $stmt->execute([$start_date]);
        $data['sales_daily'] = $stmt->fetchAll();
        
        // Top selling products
        $stmt = $pdo->prepare("
            SELECT 
                p.name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.price) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= ? AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_revenue DESC
            LIMIT 10
        ");
        $stmt->execute([$start_date]);
        $data['top_products'] = $stmt->fetchAll();
        break;
        
    case 'customers':
        // Customer analytics
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as reg_date,
                COUNT(*) as new_customers
            FROM users 
            WHERE created_at >= ? AND role = 'customer'
            GROUP BY DATE(created_at)
            ORDER BY reg_date DESC
        ");
        $stmt->execute([$start_date]);
        $data['customer_registrations'] = $stmt->fetchAll();
        
        // Customer segmentation
        $stmt = $pdo->query("
            SELECT 
                customer_segment,
                COUNT(*) as customer_count,
                AVG(total_orders) as avg_orders,
                AVG(total_spent) as avg_spent
            FROM (
                SELECT 
                    u.id,
                    u.name,
                    CASE 
                        WHEN COUNT(DISTINCT o.id) >= 10 THEN 'VIP'
                        WHEN COUNT(DISTINCT o.id) >= 5 THEN 'Regular'
                        WHEN COUNT(DISTINCT o.id) >= 2 THEN 'Occasional'
                        ELSE 'New'
                    END as customer_segment,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(CASE WHEN o.status != 'cancelled' THEN o.total_price ELSE 0 END) as total_spent
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE u.role = 'customer'
                GROUP BY u.id
            ) customer_data
            GROUP BY customer_segment
            ORDER BY customer_count DESC
        ");
        $data['customer_segments'] = $stmt->fetchAll();
        break;
        
    case 'products':
        // Product analytics
        $stmt = $pdo->query("
            SELECT 
                p.category,
                COUNT(*) as product_count,
                SUM(p.stock) as total_stock,
                SUM(p.stock * p.price) as total_value,
                AVG(p.price) as avg_price
            FROM products p
            GROUP BY p.category
            ORDER BY total_value DESC
        ");
        $data['category_performance'] = $stmt->fetchAll();
        
        // Low stock analysis
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as low_stock_count,
                SUM(stock * price) as low_stock_value
            FROM products
            WHERE stock <= reorder_level
        ");
        $data['low_stock_analysis'] = $stmt->fetch();
        break;
        
    case 'financial':
        // Financial analytics
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                SUM(total_price) as daily_revenue,
                COUNT(*) as order_count,
                AVG(total_price) as avg_order_value
            FROM orders 
            WHERE created_at >= ? AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        $stmt->execute([$start_date]);
        $data['daily_revenue'] = $stmt->fetchAll();
        
        // Payment method analysis
        $stmt = $pdo->prepare("
            SELECT 
                payment_method,
                COUNT(*) as order_count,
                SUM(total_price) as total_revenue,
                AVG(total_price) as avg_order_value
            FROM orders 
            WHERE created_at >= ? AND status != 'cancelled'
            GROUP BY payment_method
            ORDER BY total_revenue DESC
        ");
        $stmt->execute([$start_date]);
        $data['payment_methods'] = $stmt->fetchAll();
        break;
        
    default: // overview
        // Get overview statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_price) as total_revenue,
                AVG(total_price) as avg_order_value,
                COUNT(DISTINCT user_id) as unique_customers
            FROM orders 
            WHERE created_at >= ? AND status != 'cancelled'
        ");
        $stmt->execute([$start_date]);
        $data['overview'] = $stmt->fetch();
        
        // Get growth metrics
        $previous_start = date('Y-m-d', strtotime($start_date . ' - ' . str_replace('days', '', $date_range) . ' days'));
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as previous_orders,
                SUM(total_price) as previous_revenue
            FROM orders 
            WHERE created_at BETWEEN ? AND ? AND status != 'cancelled'
        ");
        $stmt->execute([$previous_start, $start_date]);
        $previous_data = $stmt->fetch();
        
        $data['growth'] = [
            'order_growth' => $previous_data['previous_orders'] > 0 ? 
                (($data['overview']['total_orders'] - $previous_data['previous_orders']) / $previous_data['previous_orders']) * 100 : 0,
            'revenue_growth' => $previous_data['previous_revenue'] > 0 ? 
                (($data['overview']['total_revenue'] - $previous_data['previous_revenue']) / $previous_data['previous_revenue']) * 100 : 0
        ];
        
        // Top metrics
        $stmt = $pdo->prepare("
            SELECT 
                p.name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.price) as revenue
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= ? AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY revenue DESC
            LIMIT 5
        ");
        $stmt->execute([$start_date]);
        $data['top_products'] = $stmt->fetchAll();
        break;
}

include '../includes/header.php';
?>

<style>
.analytics-container { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.metric-card { background: linear-gradient(135deg, #8B4513, #D2691E); color: white; padding: 1.5rem; border-radius: 0.75rem; position: relative; overflow: hidden; }
.metric-card::before { content: ''; position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); }
.metric-value { font-size: 2.5rem; font-weight: 700; position: relative; z-index: 1; }
.metric-label { opacity: 0.9; font-size: 0.875rem; position: relative; z-index: 1; }
.metric-change { font-size: 0.875rem; margin-top: 0.5rem; position: relative; z-index: 1; }
.change-positive { color: #10b981; }
.change-negative { color: #f59e0b; }
.chart-container { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.chart-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
.chart-controls { display: flex; gap: 1rem; }
.chart-select { padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; }
.bar-chart { height: 300px; display: flex; align-items: flex-end; gap: 0.5rem; }
.bar { background: linear-gradient(to top, #8B4513, #D2691E); border-radius: 0.25rem 0.25rem 0 0; flex: 1; min-height: 20px; position: relative; }
.bar:hover { opacity: 0.8; }
.bar-label { position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #666; white-space: nowrap; }
.bar-value { position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #666; white-space: nowrap; }
.table-container { overflow-x-auto; }
.analytics-table { width: 100%; border-collapse: collapse; }
.analytics-table th { background: #f3f4f6; color: #1f2937; padding: 1rem; text-align: left; font-weight: 600; }
.analytics-table td { padding: 1rem; border-bottom: 1px solid #e5e7eb; }
.analytics-table tr:hover { background: #f9fafb; }
.segment-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
.segment-vip { background: #fef3c7; color: #92400e; }
.segment-regular { background: #dbeafe; color: #1e40af; }
.segment-occasional { background: #e0e7ff; color: #3730a3; }
.segment-new { background: #d1fae5; color: #065f46; }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Analytics Dashboard</h1>
        <div class="flex items-center space-x-4">
            <form method="GET" class="flex items-center space-x-4">
                <select name="report" onchange="this.form.submit()" class="bg-white border border-gray-300 rounded-lg px-4 py-2">
                    <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>Overview</option>
                    <option value="sales" <?php echo $report_type === 'sales' ? 'selected' : ''; ?>>Sales Analytics</option>
                    <option value="customers" <?php echo $report_type === 'customers' ? 'selected' : ''; ?>>Customer Analytics</option>
                    <option value="products" <?php echo $report_type === 'products' ? 'selected' : ''; ?>>Product Analytics</option>
                    <option value="financial" <?php echo $report_type === 'financial' ? 'selected' : ''; ?>>Financial Analytics</option>
                </select>
                
                <select name="date_range" onchange="this.form.submit()" class="bg-white border border-gray-300 rounded-lg px-4 py-2">
                    <option value="7days" <?php echo $date_range === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="30days" <?php echo $date_range === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="90days" <?php echo $date_range === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                    <option value="1year" <?php echo $date_range === '1year' ? 'selected' : ''; ?>>Last Year</option>
                </select>
            </form>
            
            <a href="/bakery-website/admin/dashboard.php" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if ($report_type === 'overview'): ?>
        <!-- Overview Analytics -->
        <div class="analytics-container">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Business Overview - Last <?php echo str_replace(['days', 'year'], ['Days', 'Year'], $date_range); ?></h2>
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-value"><?php echo $data['overview']['total_orders']; ?></div>
                    <div class="metric-label">Total Orders</div>
                    <div class="metric-change <?php echo $data['growth']['order_growth'] >= 0 ? 'change-positive' : 'change-negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $data['growth']['order_growth'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs(round($data['growth']['order_growth'], 1)); ?>% from previous period
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">₱<?php echo number_format($data['overview']['total_revenue'], 0); ?></div>
                    <div class="metric-label">Total Revenue</div>
                    <div class="metric-change <?php echo $data['growth']['revenue_growth'] >= 0 ? 'change-positive' : 'change-negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $data['growth']['revenue_growth'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs(round($data['growth']['revenue_growth'], 1)); ?>% from previous period
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">₱<?php echo number_format($data['overview']['avg_order_value'], 0); ?></div>
                    <div class="metric-label">Average Order Value</div>
                    <div class="metric-change">
                        <i class="fas fa-chart-line"></i>
                        Per order average
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value"><?php echo $data['overview']['unique_customers']; ?></div>
                    <div class="metric-label">Unique Customers</div>
                    <div class="metric-change">
                        <i class="fas fa-users"></i>
                        Active customers
                    </div>
                </div>
            </div>
            
            <!-- Top Products -->
            <div class="chart-container">
                <h3 class="chart-title">Top Performing Products</h3>
                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['top_products'] as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                    <td>₱<?php echo number_format($product['revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'sales'): ?>
        <!-- Sales Analytics -->
        <div class="analytics-container">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Sales Analytics</h2>
            
            <!-- Daily Sales Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Daily Sales Performance</h3>
                <div class="bar-chart">
                    <?php 
                    $maxRevenue = max(array_column($data['sales_daily'], 'total_revenue'));
                    foreach ($data['sales_daily'] as $sale): 
                        $height = $maxRevenue > 0 ? ($sale['total_revenue'] / $maxRevenue) * 100 : 0;
                    ?>
                        <div class="bar" style="height: <?php echo $height; ?>%;" 
                             title="<?php echo date('M d', strtotime($sale['sale_date'])); ?>: ₱<?php echo number_format($sale['total_revenue'], 2); ?>">
                            <div class="bar-value">₱<?php echo number_format($sale['total_revenue'], 0); ?></div>
                            <div class="bar-label"><?php echo date('M d', strtotime($sale['sale_date'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Top Selling Products -->
            <div class="chart-container">
                <h3 class="chart-title">Top Selling Products</h3>
                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['top_products'] as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                    <td>₱<?php echo number_format($product['total_revenue'], 2); ?></td>
                                    <td><?php echo $product['order_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'customers'): ?>
        <!-- Customer Analytics -->
        <div class="analytics-container">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Customer Analytics</h2>
            
            <!-- Customer Segments -->
            <div class="chart-container">
                <h3 class="chart-title">Customer Segmentation</h3>
                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Segment</th>
                                <th>Customers</th>
                                <th>Avg Orders</th>
                                <th>Avg Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['customer_segments'] as $segment): ?>
                                <tr>
                                    <td>
                                        <span class="segment-badge segment-<?php echo strtolower($segment['customer_segment']); ?>">
                                            <?php echo ucfirst($segment['customer_segment']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $segment['customer_count']; ?></td>
                                    <td><?php echo number_format($segment['avg_orders'], 1); ?></td>
                                    <td>₱<?php echo number_format($segment['avg_spent'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Customer Registrations -->
            <div class="chart-container">
                <h3 class="chart-title">New Customer Registrations</h3>
                <div class="bar-chart">
                    <?php 
                    $maxCustomers = max(array_column($data['customer_registrations'], 'new_customers'));
                    foreach ($data['customer_registrations'] as $reg): 
                        $height = $maxCustomers > 0 ? ($reg['new_customers'] / $maxCustomers) * 100 : 0;
                    ?>
                        <div class="bar" style="height: <?php echo $height; ?>%;" 
                             title="<?php echo date('M d', strtotime($reg['reg_date'])); ?>: <?php echo $reg['new_customers']; ?> new customers">
                            <div class="bar-value"><?php echo $reg['new_customers']; ?></div>
                            <div class="bar-label"><?php echo date('M d', strtotime($reg['reg_date'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'products'): ?>
        <!-- Product Analytics -->
        <div class="analytics-container">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Product Analytics</h2>
            
            <!-- Category Performance -->
            <div class="chart-container">
                <h3 class="chart-title">Category Performance</h3>
                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Products</th>
                                <th>Total Stock</th>
                                <th>Total Value</th>
                                <th>Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['category_performance'] as $category): ?>
                                <tr>
                                    <td><?php echo ucfirst(htmlspecialchars($category['category'])); ?></td>
                                    <td><?php echo $category['product_count']; ?></td>
                                    <td><?php echo $category['total_stock']; ?></td>
                                    <td>₱<?php echo number_format($category['total_value'], 2); ?></td>
                                    <td>₱<?php echo number_format($category['avg_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Low Stock Analysis -->
            <div class="chart-container">
                <h3 class="chart-title">Inventory Health</h3>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value"><?php echo $data['low_stock_analysis']['low_stock_count']; ?></div>
                        <div class="metric-label">Low Stock Items</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">₱<?php echo number_format($data['low_stock_analysis']['low_stock_value'], 2); ?></div>
                        <div class="metric-label">Low Stock Value</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'financial'): ?>
        <!-- Financial Analytics -->
        <div class="analytics-container">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Financial Analytics</h2>
            
            <!-- Revenue Trends -->
            <div class="chart-container">
                <h3 class="chart-title">Revenue Trends</h3>
                <div class="bar-chart">
                    <?php 
                    $maxRevenue = max(array_column($data['daily_revenue'], 'daily_revenue'));
                    foreach ($data['daily_revenue'] as $revenue): 
                        $height = $maxRevenue > 0 ? ($revenue['daily_revenue'] / $maxRevenue) * 100 : 0;
                    ?>
                        <div class="bar" style="height: <?php echo $height; ?>%;" 
                             title="<?php echo date('M d', strtotime($revenue['date'])); ?>: ₱<?php echo number_format($revenue['daily_revenue'], 2); ?>">
                            <div class="bar-value">₱<?php echo number_format($revenue['daily_revenue'], 0); ?></div>
                            <div class="bar-label"><?php echo date('M d', strtotime($revenue['date'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="chart-container">
                <h3 class="chart-title">Payment Method Analysis</h3>
                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th>Avg Order Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['payment_methods'] as $method): ?>
                                <tr>
                                    <td><?php echo ucfirst(htmlspecialchars($method['payment_method'])); ?></td>
                                    <td><?php echo $method['order_count']; ?></td>
                                    <td>₱<?php echo number_format($method['total_revenue'], 2); ?></td>
                                    <td>₱<?php echo number_format($method['avg_order_value'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
