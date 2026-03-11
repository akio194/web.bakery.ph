<?php
/**
 * Inventory Management System
 * Complete stock tracking, low stock alerts, and inventory reports
 */

require_once '../config/database.php';
requireAdmin();

// Sanitize input function (fallback if validation.php not loaded)
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

$pdo = getDBConnection();

// Add stock column to products table if it doesn't exist
try {
    $pdo->exec("
        ALTER TABLE products 
        ADD COLUMN IF NOT EXISTS stock INT DEFAULT 0,
        ADD COLUMN IF NOT EXISTS reorder_level INT DEFAULT 10,
        ADD COLUMN IF NOT EXISTS batch_number VARCHAR(50),
        ADD COLUMN IF NOT EXISTS expiration_date DATE,
        ADD COLUMN IF NOT EXISTS cost_price DECIMAL(10,2) DEFAULT 0.00
    ");
} catch (PDOException $e) {
    // Column addition failed, continue
}

// Handle CRUD operations
$message = '';
$action = $_GET['action'] ?? 'list';

// Display success messages from session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

switch ($action) {
    case 'update_stock':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = (int)$_POST['product_id'] ?? 0;
            $stock = (int)$_POST['stock'] ?? 0;
            $reorder_level = (int)$_POST['reorder_level'] ?? 10;
            $batch_number = sanitizeInput($_POST['batch_number'] ?? '');
            $expiration_date = $_POST['expiration_date'] ?? null;
            
            $stmt = $pdo->prepare("
                UPDATE products 
                SET stock = ?, reorder_level = ?, batch_number = ?, expiration_date = ? 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$stock, $reorder_level, $batch_number, $expiration_date, $product_id])) {
                $_SESSION['success_message'] = '✅ Stock updated successfully!';
            } else {
                $_SESSION['error_message'] = '❌ Error updating stock.';
            }
            
            header('Location: /bakery-website/admin/inventory.php');
            exit();
        }
        break;
        
    case 'batch_update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_ids = $_POST['product_ids'] ?? [];
            $batch_number = sanitizeInput($_POST['batch_number'] ?? '');
            $expiration_date = $_POST['expiration_date'] ?? null;
            
            foreach ($product_ids as $product_id) {
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET batch_number = ?, expiration_date = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$batch_number, $expiration_date, $product_id]);
            }
            
            $_SESSION['success_message'] = '✅ Batch updated for ' . count($product_ids) . ' products!';
            header('Location: /bakery-website/admin/inventory.php');
            exit();
        }
        break;
}

// Get inventory data
$products = [];
$low_stock_items = [];
$out_of_stock_items = [];
$expiring_soon_items = [];

// Get inventory statistics (always needed for dashboard)
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_products,
        SUM(stock) as total_stock,
        COUNT(CASE WHEN stock = 0 THEN 1 END) as out_of_stock_count,
        COUNT(CASE WHEN stock <= reorder_level THEN 1 END) as low_stock_count,
        SUM(stock * price) as total_value
    FROM products
")->fetch();

if ($action === 'list') {
    // Get all products with stock info
    $stmt = $pdo->query("
        SELECT p.*, 
               CASE 
                   WHEN p.stock = 0 THEN 'out_of_stock'
                   WHEN p.stock <= p.reorder_level THEN 'low_stock'
                   ELSE 'in_stock'
               END as stock_status
        FROM products p
        ORDER BY 
               CASE 
                   WHEN p.stock = 0 THEN 1
                   WHEN p.stock <= p.reorder_level THEN 2
                   ELSE 3
               END,
               p.name
    ");
    $products = $stmt->fetchAll();
    
    // Categorize products
    foreach ($products as $product) {
        if ($product['stock'] == 0) {
            $out_of_stock_items[] = $product;
        } elseif ($product['stock'] <= $product['reorder_level']) {
            $low_stock_items[] = $product;
        }
        
        // Check expiring soon (within 7 days)
        if ($product['expiration_date']) {
            $exp_date = new DateTime($product['expiration_date']);
            $today = new DateTime();
            $diff = $today->diff($exp_date);
            if ($diff->days <= 7 && $diff->days >= 0) {
                $expiring_soon_items[] = $product;
            }
        }
    }
    
    // Remove duplicate stats calculation since it's now done above
}

include '../includes/header.php';
?>

<style>
.inventory-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.inventory-card { background: white; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
.inventory-card:hover { transform: translateY(-2px); }
.stock-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
.stock-in-stock { background: #d1fae5; color: #065f46; }
.stock-low-stock { background: #fef3c7; color: #92400e; }
.stock-out-of-stock { background: #fee2e2; color: #991b1b; }
.stat-card { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.batch-info { font-size: 0.875rem; color: #6b7280; }
.expire-warning { background: #fef3c7; color: #92400e; padding: 0.5rem; border-radius: 0.375rem; margin-top: 0.5rem; }
.form-group { margin-bottom: 1.5rem; }
.form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
.form-input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; }
.form-input:focus { outline: none; border-color: #8B4513; box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1); }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Inventory Management</h1>
        <div class="flex items-center space-x-4">
            <a href="/bakery-website/admin/inventory-reports.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                <i class="fas fa-chart-bar mr-2"></i>Inventory Reports
            </a>
            <a href="/bakery-website/admin/dashboard.php" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <?php 
        $isSuccess = strpos($message, '✅') !== false;
        $bgClass = $isSuccess ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700';
        $textClass = $isSuccess ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800';
        ?>
        <div class="<?php echo $bgClass; ?> px-4 py-3 rounded mb-6 flex items-center">
            <span class="text-lg font-medium"><?php echo $message; ?></span>
            <button onclick="this.parentElement.style.display='none'" class="ml-auto <?php echo $textClass; ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Inventory Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Products</p>
                    <p class="text-2xl font-bold text-[#8B4513]"><?php echo $stats['total_products']; ?></p>
                </div>
                <div class="stat-icon bg-blue-50">
                    <i class="fas fa-box text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Stock Value</p>
                    <p class="text-2xl font-bold text-green-600">₱<?php echo number_format($stats['total_value'], 2); ?></p>
                </div>
                <div class="stat-icon bg-green-50">
                    <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Low Stock Items</p>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['low_stock_count']; ?></p>
                </div>
                <div class="stat-icon bg-yellow-50">
                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Out of Stock</p>
                    <p class="text-2xl font-bold text-red-600"><?php echo $stats['out_of_stock_count']; ?></p>
                </div>
                <div class="stat-icon bg-red-50">
                    <i class="fas fa-times-circle text-2xl text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    <?php if (!empty($out_of_stock_items) || !empty($low_stock_items) || !empty($expiring_soon_items)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-bold text-red-800 mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>Inventory Alerts
            </h3>
            
            <?php if (!empty($out_of_stock_items)): ?>
                <div class="mb-4">
                    <h4 class="font-bold text-red-700 mb-2">Out of Stock (<?php echo count($out_of_stock_items); ?> items)</h4>
                    <div class="space-y-2">
                        <?php foreach (array_slice($out_of_stock_items, 0, 5) as $item): ?>
                            <div class="flex justify-between items-center bg-white p-3 rounded">
                                <span class="font-medium"><?php echo htmlspecialchars($item['name']); ?></span>
                                <a href="/bakery-website/admin/inventory.php?action=update_stock&id=<?php echo $item['id']; ?>" 
                                   class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                                    Update Stock
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($low_stock_items)): ?>
                <div class="mb-4">
                    <h4 class="font-bold text-yellow-700 mb-2">Low Stock (<?php echo count($low_stock_items); ?> items)</h4>
                    <div class="space-y-2">
                        <?php foreach (array_slice($low_stock_items, 0, 5) as $item): ?>
                            <div class="flex justify-between items-center bg-white p-3 rounded">
                                <div>
                                    <span class="font-medium"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="text-sm text-gray-500 ml-2">
                                        Stock: <?php echo $item['stock']; ?> / Reorder at: <?php echo $item['reorder_level']; ?>
                                    </span>
                                </div>
                                <a href="/bakery-website/admin/inventory.php?action=update_stock&id=<?php echo $item['id']; ?>" 
                                   class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 text-sm">
                                    Reorder
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($expiring_soon_items)): ?>
                <div>
                    <h4 class="font-bold text-orange-700 mb-2">Expiring Soon (<?php echo count($expiring_soon_items); ?> items)</h4>
                    <div class="space-y-2">
                        <?php foreach (array_slice($expiring_soon_items, 0, 5) as $item): ?>
                            <div class="flex justify-between items-center bg-white p-3 rounded">
                                <div>
                                    <span class="font-medium"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="text-sm text-orange-600 ml-2">
                                        Expires: <?php echo date('M d, Y', strtotime($item['expiration_date'])); ?>
                                    </span>
                                </div>
                                <a href="/bakery-website/admin/inventory.php?action=update_stock&id=<?php echo $item['id']; ?>" 
                                   class="bg-orange-600 text-white px-3 py-1 rounded hover:bg-orange-700 text-sm">
                                    Update Batch
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Inventory Grid -->
    <div class="inventory-grid">
        <?php foreach ($products as $product): ?>
            <div class="inventory-card">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold text-[#8B4513]"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <span class="stock-badge stock-<?php echo $product['stock_status']; ?>">
                            <?php 
                            if ($product['stock_status'] === 'out_of_stock') echo 'Out of Stock';
                            elseif ($product['stock_status'] === 'low_stock') echo 'Low Stock';
                            else echo 'In Stock';
                            ?>
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Current Stock:</span>
                            <span class="font-bold text-lg"><?php echo $product['stock']; ?> units</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Reorder Level:</span>
                            <span class="font-medium"><?php echo $product['reorder_level']; ?> units</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Unit Price:</span>
                            <span class="font-medium">₱<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Value:</span>
                            <span class="font-bold text-green-600">₱<?php echo number_format($product['stock'] * $product['price'], 2); ?></span>
                        </div>
                        
                        <?php if ($product['batch_number']): ?>
                            <div class="batch-info">
                                <span class="text-gray-600">Batch:</span> <?php echo htmlspecialchars($product['batch_number']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['expiration_date']): ?>
                            <div class="batch-info">
                                <span class="text-gray-600">Expires:</span> <?php echo date('M d, Y', strtotime($product['expiration_date'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex space-x-2 mt-4">
                        <a href="/bakery-website/admin/inventory.php?action=update_stock&id=<?php echo $product['id']; ?>" 
                           class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 flex-1 text-center">
                            <i class="fas fa-edit mr-2"></i>Update Stock
                        </a>
                        <a href="/bakery-website/admin/products.php?action=edit&id=<?php echo $product['id']; ?>" 
                           class="bg-gray-600 text-white px-3 py-2 rounded hover:bg-gray-700 flex-1 text-center">
                            <i class="fas fa-box mr-2"></i>Edit Product
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Stock Update Modal -->
<?php if (isset($_GET['action']) && $_GET['action'] === 'update_stock' && isset($_GET['id'])):
    $product_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
?>
    <div id="stockModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold mb-4">Update Stock - <?php echo htmlspecialchars($product['name']); ?></h3>
            
            <form method="POST" action="/bakery-website/admin/inventory.php?action=update_stock" class="space-y-4">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Current Stock</label>
                        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" 
                               class="form-input" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" value="<?php echo $product['reorder_level']; ?>" 
                               class="form-input" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Batch Number</label>
                    <input type="text" name="batch_number" value="<?php echo htmlspecialchars($product['batch_number'] ?? ''); ?>" 
                           class="form-input" placeholder="e.g., BATCH-001">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Expiration Date</label>
                    <input type="date" name="expiration_date" 
                           value="<?php echo $product['expiration_date'] ?? ''; ?>"
                           class="form-input">
                </div>
                
                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex-1">
                        <i class="fas fa-save mr-2"></i>Update Stock
                    </button>
                    <button type="button" onclick="closeStockModal()" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 flex-1">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function closeStockModal() {
        document.getElementById('stockModal').style.display = 'none';
    }
    
    // Auto-close modal if not updating
    <?php if (!isset($_GET['action']) || $_GET['action'] !== 'update_stock'): ?>
        closeStockModal();
    <?php endif; ?>
    </script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
