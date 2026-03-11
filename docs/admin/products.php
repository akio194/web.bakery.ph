<?php
/**
 * Product Management CRUD Page
 * Complete product management with Create, Read, Update, Delete functionality
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

// Create products table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(100) NOT NULL,
            image VARCHAR(255),
            is_featured TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (PDOException $e) {
    // Table creation failed, continue and let error handling catch it
}

// Handle CRUD operations
$message = '';
$action = $_GET['action'] ?? 'list';
$product = null; // Initialize product variable

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
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $category = sanitizeInput($_POST['category'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Handle image upload
            $image = ''; // Default to empty string
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/images/';
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $image = $fileName;
                }
            }
            
            // Fixed INSERT statement with correct parameter count
            $sql = "INSERT INTO products (name, description, price, category, image, is_featured) VALUES (?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            
            // Debug: Check if statement was prepared correctly
            if (!$stmt) {
                $message = 'Error preparing statement: ' . implode(' ', $pdo->errorInfo());
            } else {
                // Success messages (remove debug once working)
                if ($stmt->execute([$name, $description, $price, $category, $image, $is_featured])) {
                    $_SESSION['success_message'] = '✅ Product created successfully!';
                    header('Location: /bakery-website/admin/products.php');
                    exit();
                } else {
                    $message = '❌ Error creating product: ' . implode(' ', $stmt->errorInfo());
                }
            }
        }
        break;
        
    case 'edit':
        $product_id = (int)$_GET['id'] ?? 0;
        $product = null;
        
        if ($product_id) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $category = sanitizeInput($_POST['category'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Handle image upload
            $image = $product['image']; // Keep existing image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/images/';
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $image = $fileName;
                }
            }
            
            // Simple UPDATE without complex formatting
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, image = ?, is_featured = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            // Success messages with session and redirect
                if ($stmt->execute([$name, $description, $price, $category, $image, $is_featured, $product_id])) {
                    $_SESSION['success_message'] = '✅ Product updated successfully!';
                    header('Location: /bakery-website/admin/products.php');
                    exit();
                } else {
                    $_SESSION['error_message'] = '❌ Error updating product: ' . implode(' ', $stmt->errorInfo());
                    header('Location: /bakery-website/admin/products.php?action=edit&id=' . $product_id);
                    exit();
                }
        }
        
        if ($action === 'edit' && !$product) {
            $message = 'Product not found.';
            $action = 'list'; // Fall back to list view
        }
        break;
        
    case 'delete':
        $product_id = (int)$_GET['id'] ?? 0;
        if ($product_id) {
            // Get product image to delete
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            // Delete product
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$product_id])) {
                // Delete image file if exists
                if ($product && $product['image']) {
                    $imagePath = '../assets/images/' . $product['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $_SESSION['success_message'] = '✅ Product deleted successfully!';
                header('Location: /bakery-website/admin/products.php');
                exit();
            } else {
                $_SESSION['error_message'] = '❌ Error deleting product.';
                header('Location: /bakery-website/admin/products.php');
                exit();
            }
        }
        break;
        
    case 'toggle_featured':
        $product_id = (int)$_GET['id'] ?? 0;
        if ($product_id) {
            $stmt = $pdo->prepare("SELECT is_featured FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                $newFeatured = $product['is_featured'] ? 0 : 1;
                $stmt = $pdo->prepare("UPDATE products SET is_featured = ? WHERE id = ?");
                if ($stmt->execute([$newFeatured, $product_id])) {
                    $_SESSION['success_message'] = $newFeatured ? '⭐ Product featured successfully!' : '📌 Product unfeatured successfully!';
                    header('Location: /bakery-website/admin/products.php');
                    exit();
                }
            }
        }
        break;
}

// Get products for list view
$products = [];
if ($action === 'list') {
    $search = sanitizeInput($_GET['search'] ?? '');
    $category_filter = sanitizeInput($_GET['category'] ?? '');
    
    $query = "SELECT * FROM products WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR description LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($category_filter)) {
        $query .= " AND category = ?";
        $params[] = $category_filter;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get categories for filter
    $categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll();
}

include '../includes/header.php';
?>

<style>
.product-form { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.product-card { background: white; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
.product-card:hover { transform: translateY(-4px); }
.product-image { width: 100%; height: 200px; object-fit: cover; }
.product-info { padding: 1rem; }
.product-actions { display: flex; gap: 0.5rem; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
.featured-badge { background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
.form-group { margin-bottom: 1.5rem; }
.form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
.form-input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; }
.form-input:focus { outline: none; border-color: #8B4513; box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1); }
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-[#8B4513] font-['Playfair_Display']">Product Management</h1>
        <div class="flex items-center space-x-4">
            <?php if ($action === 'list'): ?>
                <a href="?action=create" class="bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add Product
                </a>
            <?php else: ?>
                <a href="?action=list" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
            <?php endif; ?>
            <a href="/bakery-website/admin/dashboard.php" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <?php 
        $isSuccess = strpos($message, '✅') !== false || strpos($message, '⭐') !== false || strpos($message, '📌') !== false;
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

    <?php if ($action === 'create' || ($action === 'edit' && $product)): ?>
        <!-- Create/Edit Product Form -->
        <div class="product-form">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">
                <?php echo $action === 'create' ? 'Add New Product' : 'Edit Product'; ?>
            </h2>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" required 
                               value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                               class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Price (₱) *</label>
                        <input type="number" name="price" step="0.01" min="0" required
                               value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>"
                               class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" required class="form-input">
                        <option value="">Select Category</option>
                        <option value="bread" <?php echo ($product['category'] ?? '') === 'bread' ? 'selected' : ''; ?>>Bread</option>
                        <option value="cake" <?php echo ($product['category'] ?? '') === 'cake' ? 'selected' : ''; ?>>Cake</option>
                        <option value="pastry" <?php echo ($product['category'] ?? '') === 'pastry' ? 'selected' : ''; ?>>Pastry</option>
                        <option value="cookie" <?php echo ($product['category'] ?? '') === 'cookie' ? 'selected' : ''; ?>>Cookie</option>
                        <option value="dessert" <?php echo ($product['category'] ?? '') === 'dessert' ? 'selected' : ''; ?>>Dessert</option>
                        <option value="beverage" <?php echo ($product['category'] ?? '') === 'beverage' ? 'selected' : ''; ?>>Beverage</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-input"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" accept="image/*" class="form-input">
                    <?php if ($product && $product['image']): ?>
                        <p class="mt-2 text-sm text-gray-600">Current image: <?php echo htmlspecialchars($product['image']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" <?php echo ($product['is_featured'] ?? 0) ? 'checked' : ''; ?> class="mr-2">
                        <span>Featured Product</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="submit" class="bg-[#8B4513] text-white px-6 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                        <?php echo $action === 'create' ? 'Create Product' : 'Update Product'; ?>
                    </button>
                    <a href="?action=list" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
    <?php elseif ($action === 'list'): ?>
        <!-- Products List -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-lg font-bold mb-4 text-gray-800">Filters</h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="action" value="list">
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="form-input">
                
                <select name="category" class="form-input">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category']; ?>" <?php echo $category_filter === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </form>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-600">No products found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your filters or add a new product</p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['image']): ?>
                            <img src="/bakery-website/assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-image bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-4xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-[#8B4513]"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <?php if ($product['is_featured']): ?>
                                    <span class="featured-badge">Featured</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-gray-600 mb-2"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <p class="text-xl font-bold text-[#D2691E] mb-4">₱<?php echo number_format($product['price'], 2); ?></p>
                            
                            <div class="product-actions">
                                <a href="?action=edit&id=<?php echo $product['id']; ?>" 
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <a href="?action=toggle_featured&id=<?php echo $product['id']; ?>" 
                                   class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 btn-sm"
                                   title="<?php echo $product['is_featured'] ? 'Remove from featured' : 'Add to featured'; ?>">
                                    <i class="fas fa-star"></i> <?php echo $product['is_featured'] ? 'Unfeature' : 'Feature'; ?>
                                </a>
                                
                                <a href="?action=delete&id=<?php echo $product['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this product?')"
                                   class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
