<?php
/**
 * Inventory Reports Page - Fixed Version
 * Comprehensive inventory analysis and reporting
 */

require_once '../config/database.php';
requireAdmin();

// Initialize database connection
$pdo = getDBConnection();

// Get report parameters
$report_type = $_GET['report'] ?? 'overview';
$date_range = $_GET['date_range'] ?? '30days';

// Calculate date ranges
$date_ranges = [
    '7days' => date('Y-m-d', strtotime('-7 days')),
    '30days' => date('Y-m-d', strtotime('-30 days')),
    '90days' => date('Y-m-d', strtotime('-90 days')),
    '1year' => date('Y-m-d', strtotime('-1 year'))
];

// Get inventory data based on report type
$data = [];
switch ($report_type) {
    case 'best_selling':
        $stmt = $pdo->prepare("
            SELECT p.*, SUM(oi.quantity) as total_sold, SUM(oi.quantity * p.price) as total_revenue
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE o.status != 'cancelled' AND o.created_at >= ?
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 20
        ");
        $stmt->execute([$date_ranges[$date_range]]);
        $data = $stmt->fetchAll();
        break;
        
    case 'low_stock':
        $stmt = $pdo->query("
            SELECT p.*, p.stock, p.reorder_level
            FROM products p
            WHERE p.stock <= p.reorder_level
            ORDER BY p.stock ASC
        ");
        $data = $stmt->fetchAll();
        break;
        
    case 'expiring':
        $stmt = $pdo->query("
            SELECT p.*, p.expiration_date, DATEDIFF(p.expiration_date, CURDATE()) as days_until_expiration
            FROM products p
            WHERE p.expiration_date IS NOT NULL 
              AND p.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY p.expiration_date ASC
        ");
        $data = $stmt->fetchAll();
        break;
        
    case 'stock_value':
        $stmt = $pdo->query("
            SELECT 
                p.*,
                p.stock,
                p.stock * p.price as total_value,
                CASE 
                    WHEN p.stock = 0 THEN 'out_of_stock'
                    WHEN p.stock <= p.reorder_level THEN 'low_stock'
                    ELSE 'in_stock'
                END as stock_status
            FROM products p
            ORDER BY total_value DESC
        ");
        $data = $stmt->fetchAll();
        break;
        
    case 'category_analysis':
        $stmt = $pdo->query("
            SELECT 
                p.category,
                COUNT(*) as product_count,
                SUM(p.stock) as total_stock,
                SUM(p.stock * p.price) as category_value,
                AVG(p.price) as avg_price
            FROM products p
            GROUP BY p.category
            ORDER BY category_value DESC
        ");
        $data = $stmt->fetchAll();
        break;
        
    default: // overview
        $end_date = $date_ranges[$date_range];
        
        // Get inventory movements
        $stmt = $pdo->query("
            SELECT 
                DATE(created_at) as movement_date,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as sales_count,
                SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) as sales_value
            FROM orders 
            WHERE created_at >= ? AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY movement_date DESC
        ", [$end_date]);
        $movements = $stmt->fetchAll();
        
        // Get current inventory summary
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_products,
                SUM(stock) as total_stock,
                SUM(stock * price) as total_value,
                COUNT(CASE WHEN stock = 0 THEN 1 END) as out_of_stock_count,
                COUNT(CASE WHEN stock <= reorder_level THEN 1 END) as low_stock_count,
                COUNT(CASE WHEN expiration_date IS NOT NULL AND DATEDIFF(expiration_date, CURDATE()) <= 7 THEN 1 END) as expiring_count
            FROM products
        ");
        $summary = $stmt->fetch();
        
        // Get category breakdown
        $stmt = $pdo->query("
            SELECT 
                category,
                COUNT(*) as count,
                SUM(stock) as total_stock,
                SUM(stock * price) as total_value
            FROM products
            GROUP BY category
            ORDER BY total_value DESC
        ");
        $categories = $stmt->fetchAll();
        
        $data = [
            'summary' => $summary,
            'movements' => $movements,
            'categories' => $categories
        ];
        break;
}

include '../includes/header.php';
?>

<style>
.report-container { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
.report-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; }
.report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.report-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
.report-value { font-size: 2rem; font-weight: 700; color: #059669; }
.chart-container { background: white; border-radius: 0.5rem; padding: 1.5rem; margin-top: 1rem; }
.movement-chart { height: 300px; display: flex; align-items: flex-end; gap: 0.5rem; }
.movement-bar { background: linear-gradient(to top, #10b981, #f59e0b); border-radius: 0.25rem 0.25rem 0 0; flex: 1; min-height: 20px; position: relative; }
.movement-bar:hover { opacity: 0.8; }
.category-pie { width: 200px; height: 200px; }
.table-container { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #f3f4f6; color: #1f2937; padding: 0.75rem; text-align: left; font-weight: 600; }
.data-table td { padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
.data-table tr:hover { background: #f9fafb; }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Inventory Reports</h1>
        <div class="flex items-center space-x-4">
            <form method="GET" class="flex items-center space-x-4">
                <select name="report" onchange="this.form.submit()" class="bg-white border border-gray-300 rounded-lg px-4 py-2">
                    <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>Overview</option>
                    <option value="best_selling" <?php echo $report_type === 'best_selling' ? 'selected' : ''; ?>>Best Selling</option>
                    <option value="low_stock" <?php echo $report_type === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="expiring" <?php echo $report_type === 'expiring' ? 'selected' : ''; ?>>Expiring Soon</option>
                    <option value="stock_value" <?php echo $report_type === 'stock_value' ? 'selected' : ''; ?>>Stock Value</option>
                    <option value="category_analysis" <?php echo $report_type === 'category_analysis' ? 'selected' : ''; ?>>Category Analysis</option>
                </select>
                
                <select name="date_range" onchange="this.form.submit()" class="bg-white border border-gray-300 rounded-lg px-4 py-2">
                    <option value="7days" <?php echo $date_range === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="30days" <?php echo $date_range === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="90days" <?php echo $date_range === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                    <option value="1year" <?php echo $date_range === '1year' ? 'selected' : ''; ?>>Last Year</option>
                </select>
            </form>
            
            <a href="/bakery-website/admin/inventory.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
            </a>
        </div>
    </div>

    <?php if ($report_type === 'overview'): ?>
        <!-- Overview Report -->
        <div class="report-container">
            <h2 class="report-title">Inventory Overview - Last <?php echo $date_range === '7days' ? '7' : ($date_range === '30days' ? '30' : ($date_range === '90days' ? '90' : '365')); ?> Days</h2>
            
            <div class="report-grid">
                <div class="report-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Current Inventory</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span>Total Products:</span>
                            <span class="report-value"><?php echo $data['summary']['total_products']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Stock Value:</span>
                            <span class="report-value">₱<?php echo number_format($data['summary']['total_value'], 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Out of Stock:</span>
                            <span class="report-value text-red-600"><?php echo $data['summary']['out_of_stock_count']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Low Stock Items:</span>
                            <span class="report-value text-yellow-600"><?php echo $data['summary']['low_stock_count']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Expiring Soon (<?php echo $date_range === '7days' ? '7' : ($date_range === '30days' ? '30' : ($date_range === '90days' ? '90' : '365')); ?> days):</span>
                            <span class="report-value text-orange-600"><?php echo $data['summary']['expiring_count']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Category Breakdown</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Products</th>
                                    <th>Total Stock</th>
                                    <th>Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['categories'] as $category): ?>
                                    <tr>
                                        <td><?php echo ucfirst(htmlspecialchars($category['category'])); ?></td>
                                        <td><?php echo $category['count']; ?></td>
                                        <td><?php echo $category['total_stock']; ?></td>
                                        <td>₱<?php echo number_format($category['total_value'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'best_selling'): ?>
        <!-- Best Selling Products Report -->
        <div class="report-container">
            <h2 class="report-title">Best Selling Products</h2>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product</th>
                            <th>Total Sold</th>
                            <th>Revenue</th>
                            <th>Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $index => $product): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['total_sold']; ?></td>
                                <td>₱<?php echo number_format($product['total_revenue'], 2); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'low_stock'): ?>
        <!-- Low Stock Report -->
        <div class="report-container">
            <h2 class="report-title">Low Stock Alert</h2>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Shortage</th>
                            <th>Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $product): ?>
                            <?php $shortage = $product['reorder_level'] - $product['stock']; ?>
                            <tr class="<?php echo $shortage > 10 ? 'bg-red-50' : ($shortage > 5 ? 'bg-yellow-50' : 'bg-orange-50'); ?>">
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td><?php echo $product['reorder_level']; ?></td>
                                <td class="font-bold text-<?php echo $shortage > 10 ? 'red' : ($shortage > 5 ? 'yellow' : 'orange'); ?>">
                                    <?php echo $shortage > 0 ? '-' . $shortage : '0'; ?>
                                </td>
                                <td>₱<?php echo number_format($product['stock'] * $product['price'], 2); ?></td>
                                <td>
                                    <a href="/bakery-website/admin/inventory.php?action=update_stock&id=<?php echo $product['id']; ?>" 
                                       class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">
                                        Reorder Now
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'expiring'): ?>
        <!-- Expiring Products Report -->
        <div class="report-container">
            <h2 class="report-title">Products Expiring Soon</h2>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Expiration Date</th>
                            <th>Days Until Expiry</th>
                            <th>Current Stock</th>
                            <th>Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $product): ?>
                            <?php $days = $product['days_until_expiration']; ?>
                            <tr class="<?php echo $days <= 3 ? 'bg-red-50' : ($days <= 7 ? 'bg-yellow-50' : 'bg-orange-50'); ?>">
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($product['expiration_date'])); ?></td>
                                <td class="font-bold text-<?php echo $days <= 3 ? 'red' : ($days <= 7 ? 'yellow' : 'orange'); ?>">
                                    <?php echo $days > 0 ? $days : '0'; ?> days
                                </td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>₱<?php echo number_format($product['stock'] * $product['price'], 2); ?></td>
                                <td>
                                    <a href="/bakery-website/admin/inventory.php?action=update_stock&id=<?php echo $product['id']; ?>" 
                                       class="bg-<?php echo $days <= 3 ? 'red' : ($days <= 7 ? 'yellow' : 'orange'); ?>-600 text-white px-3 py-1 rounded hover:bg-<?php echo $days <= 3 ? 'red' : ($days <= 7 ? 'yellow' : 'orange'); ?>-700 text-sm">
                                        Use Now
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'stock_value'): ?>
        <!-- Stock Value Report -->
        <div class="report-container">
            <h2 class="report-title">Inventory Value Analysis</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="report-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Category Summary</h3>
                    
                    <!-- Simple Pie Chart -->
                    <div class="flex justify-center">
                        <canvas id="categoryChart" width="200" height="200"></canvas>
                    </div>
                    
                    <div class="mt-6 space-y-2">
                        <?php 
                        $total_products = array_sum(array_column($data, 'count'));
                        $total_value = array_sum(array_column($data, 'total_value'));
                        $avg_price = $total_value / $total_products;
                        ?>
                        
                        <div class="flex justify-between">
                            <span>Total Categories:</span>
                            <span class="report-value"><?php echo count($data); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Average Product Value:</span>
                            <span class="report-value">₱<?php echo number_format($avg_price, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Total Inventory Value:</span>
                            <span class="report-value">₱<?php echo number_format($total_value, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Performance Metrics</h3>
                    <div class="space-y-3">
                        <?php 
                        $total_products = array_sum(array_column($data, 'count'));
                        $total_value = array_sum(array_column($data, 'total_value'));
                        $avg_price = $total_value / $total_products;
                        ?>
                        
                        <div class="flex justify-between">
                            <span>Total Categories:</span>
                            <span class="report-value"><?php echo count($data); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Average Product Value:</span>
                            <span class="report-value">₱<?php echo number_format($avg_price, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Total Inventory Value:</span>
                            <span class="report-value">₱<?php echo number_format($total_value, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($report_type === 'category_analysis'): ?>
        <!-- Category Analysis Report -->
        <div class="report-container">
            <h2 class="report-title">Category Performance Analysis</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="report-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Category Summary</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Products</th>
                                    <th>Total Stock</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Stock Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $index => $product): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($product['category'])); ?></td>
                                        <td><?php echo $product['count']; ?></td>
                                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                        <td>₱<?php echo number_format($product['total_value'], 2); ?></td>
                                        <td>
                                            <span class="stock-badge stock-<?php echo $product['stock_status']; ?>">
                                                <?php 
                                                    if ($product['stock_status'] === 'out_of_stock') echo 'Out of Stock';
                                                    elseif ($product['stock_status'] === 'low_stock') echo 'Low Stock';
                                                    else echo 'In Stock';
                                                    ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Performance Metrics</h3>
                    <div class="space-y-3">
                        <?php 
                        $total_products = array_sum(array_column($data, 'count'));
                        $total_value = array_sum(array_column($data, 'total_value'));
                        $avg_price = $total_value / $total_products;
                        ?>
                        
                        <div class="flex justify-between">
                            <span>Total Categories:</span>
                            <span class="report-value"><?php echo count($data); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Average Product Value:</span>
                            <span class="report-value">₱<?php echo number_format($avg_price, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Total Inventory Value:</span>
                            <span class="report-value">₱<?php echo number_format($total_value, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Simple pie chart for category analysis
<?php if ($report_type === 'category_analysis'): ?>
    const ctx = document.getElementById('categoryChart');
    if (ctx) {
        const categories = <?php echo json_encode(array_column($data, 'category')); ?>;
        const values = <?php echo json_encode(array_column($data, 'total_value')); ?>;
        const colors = <?php echo json_encode(array_map(function($cat) {
            return '#' . substr(md5($cat), 0, 6);
        }, array_column($data, 'category'))); ?>;
        
        // Create simple pie chart
        const total = values.reduce((a, b) => a + b, 0);
        let currentAngle = 0;
        
        categories.forEach((category, index) => {
            const sliceAngle = (values[index] / total) * 360;
            const endAngle = currentAngle + sliceAngle;
            
            // Draw pie slice
            ctx.beginPath();
            ctx.moveTo(100, 100);
            ctx.arc(100, 100, 80, currentAngle * Math.PI / 180, endAngle * Math.PI / 180, false);
            ctx.fillStyle = colors[index];
            ctx.fill();
            
            // Draw label
            const labelAngle = currentAngle + sliceAngle / 2;
            const labelX = 100 + Math.cos(labelAngle * Math.PI / 180) * 60;
            const labelY = 100 + Math.sin(labelAngle * Math.PI / 180) * 60;
            
            ctx.fillStyle = '#1f2937';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(categories[index], labelX, labelY);
            
            currentAngle = endAngle;
        });
    }
    <?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
