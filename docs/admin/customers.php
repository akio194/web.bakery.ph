<?php
/**
 * Customers Management Page
 * View and manage customer accounts
 */

require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter !== 'all') {
    if ($status_filter === 'active') {
        $where_conditions[] = "o.status != 'cancelled'";
    } elseif ($status_filter === 'inactive') {
        $where_conditions[] = "o.status = 'cancelled' OR o.status IS NULL";
    }
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get customers with order statistics
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.phone,
        u.address,
        u.created_at as registration_date,
        COUNT(DISTINCT o.id) as total_orders,
        SUM(CASE WHEN o.status != 'cancelled' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN o.status != 'cancelled' THEN o.total_price ELSE 0 END) as total_spent,
        MAX(o.created_at) as last_order_date,
        CASE 
            WHEN MAX(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'active'
            WHEN MAX(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 'regular'
            ELSE 'inactive'
        END as customer_status
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    $where_clause
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
");

$all_params = array_merge($params, [$limit, $offset]);
$stmt->execute($all_params);
$customers = $stmt->fetchAll();

// Get total count for pagination
$count_stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT u.id) as total
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    $where_clause
");

$count_params = $params;
$count_stmt->execute($count_params);
$total_customers = $count_stmt->fetch()['total'];
$total_pages = ceil($total_customers / $limit);

// Get customer statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_customers,
        COUNT(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as new_customers_month,
        COUNT(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as new_customers_week,
        COUNT(CASE WHEN created_at >= CURDATE() THEN 1 END) as new_customers_today
    FROM users
    WHERE role = 'customer'
");
$stats = $stats_stmt->fetch();

include '../includes/header.php';
?>

<style>
.customers-container { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: linear-gradient(135deg, #8B4513, #D2691E); color: white; padding: 1.5rem; border-radius: 0.75rem; }
.stat-value { font-size: 2rem; font-weight: 700; }
.stat-label { opacity: 0.9; font-size: 0.875rem; }
.filter-section { background: #f9fafb; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; }
.filter-form { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
.filter-input { padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; min-width: 200px; }
.filter-select { padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; min-width: 150px; }
.btn-filter { background: #8B4513; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer; }
.btn-filter:hover { background: #6B3410; }
.customers-table { width: 100%; border-collapse: collapse; }
.customers-table th { background: #f3f4f6; color: #1f2937; padding: 1rem; text-align: left; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
.customers-table td { padding: 1rem; border-bottom: 1px solid #e5e7eb; }
.customers-table tr:hover { background: #f9fafb; }
.customer-status { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
.status-active { background: #d1fae5; color: #065f46; }
.status-regular { background: #fef3c7; color: #92400e; }
.status-inactive { background: #fee2e2; color: #991b1b; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 2rem; }
.page-link { padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; text-decoration: none; color: #374151; }
.page-link:hover { background: #f3f4f6; }
.page-link.active { background: #8B4513; color: white; border-color: #8B4513; }
.customer-actions { display: flex; gap: 0.5rem; }
.btn-action { padding: 0.25rem 0.75rem; border: none; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; text-decoration: none; }
.btn-view { background: #3b82f6; color: white; }
.btn-view:hover { background: #2563eb; }
.btn-orders { background: #10b981; color: white; }
.btn-orders:hover { background: #059669; }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Customers Management</h1>
        <a href="/bakery-website/admin/dashboard.php" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>Dashboard
        </a>
    </div>

    <!-- Customer Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total_customers']; ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['new_customers_month']; ?></div>
            <div class="stat-label">New This Month</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['new_customers_week']; ?></div>
            <div class="stat-label">New This Week</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['new_customers_today']; ?></div>
            <div class="stat-label">New Today</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search customers..." 
                   value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
            
            <select name="status" class="filter-select">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="regular" <?php echo $status_filter === 'regular' ? 'selected' : ''; ?>>Regular</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
            
            <button type="submit" class="btn-filter">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            
            <?php if (!empty($search) || $status_filter !== 'all'): ?>
                <a href="/bakery-website/admin/customers.php" class="btn-filter" style="background: #6b7280;">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="customers-container">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Customer List</h2>
            <span class="text-gray-600">Showing <?php echo count($customers); ?> of <?php echo $total_customers; ?> customers</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="customers-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Registration Date</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                        <th>Last Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($customer['name']); ?></div>
                                    <div class="text-sm text-gray-500">ID: #<?php echo $customer['id']; ?></div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($customer['email']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900">
                                    <?php echo date('M d, Y', strtotime($customer['registration_date'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <div class="font-medium"><?php echo $customer['total_orders']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $customer['completed_orders']; ?> completed</div>
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-green-600">
                                    ₱<?php echo number_format($customer['total_spent'], 2); ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-gray-900">
                                    <?php 
                                    if ($customer['last_order_date']) {
                                        echo date('M d, Y', strtotime($customer['last_order_date']));
                                    } else {
                                        echo 'No orders';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <span class="customer-status status-<?php echo $customer['customer_status']; ?>">
                                    <?php echo ucfirst($customer['customer_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="customer-actions">
                                    <a href="/bakery-website/admin/customer-details.php?id=<?php echo $customer['id']; ?>" 
                                       class="btn-action btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="/bakery-website/admin/orders.php?customer=<?php echo $customer['id']; ?>" 
                                       class="btn-action btn-orders">
                                        <i class="fas fa-shopping-bag"></i> Orders
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($customers)): ?>
            <div class="text-center py-8">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No customers found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" 
                   class="page-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="page-link active"><?php echo $i; ?></span>
                <?php elseif ($i <= 3 || $i >= $total_pages - 2 || ($i >= $page - 1 && $i <= $page + 1)): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" 
                       class="page-link"><?php echo $i; ?></a>
                <?php elseif ($i == 4 || $i == $total_pages - 3): ?>
                    <span class="page-link">...</span>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" 
                   class="page-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
