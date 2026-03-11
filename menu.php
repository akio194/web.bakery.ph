<?php
/**
 * Menu Page (menu.php)
 * Displays all bakery products with enhanced search and filter functionality
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Redirect admin users - they should only access admin pages
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /bakery-website/admin/dashboard.php');
    exit();
}

$pdo = getDBConnection();

// Get user's wishlist items if logged in
$user_favorites = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get search and filter parameters with validation
$search = sanitizeInput($_GET['search'] ?? '');
$category = sanitizeInput($_GET['category'] ?? '');
$sort_by = sanitizeInput($_GET['sort'] ?? 'created_at');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Validate sort options
$valid_sorts = ['name', 'price', 'created_at'];
if (!in_array($sort_by, $valid_sorts)) {
    $sort_by = 'created_at';
}

// Build base query
$sql = "SELECT * FROM products WHERE 1=1";
$count_sql = "SELECT COUNT(*) FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $count_sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $count_sql .= " AND category = ?";
    $params[] = $category;
}

// Add sorting
$sql .= " ORDER BY $sort_by " . ($sort_by === 'price' ? 'ASC' : 'DESC');

// Add pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

// Execute queries
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count for pagination
$count_params = array_slice($params, 0, -2); // Remove LIMIT and OFFSET params
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Get unique categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-['Playfair_Display']">Our Bakery Menu</h1>
    
    <!-- Enhanced Search and Filter -->
    <div class="mb-8 bg-white p-6 rounded-lg shadow-md">
        <form method="GET" class="mb-4">
            <div class="flex flex-col md:flex-row gap-4">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search products, categories..." 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                <button type="submit" class="bg-[#8B4513] text-white px-6 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </div>
        </form>
        
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <div class="flex items-center gap-2">
                <label class="text-gray-700 font-medium">Category:</label>
                <select name="category" onchange="updateFilters()" id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category']; ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-center gap-2">
                <label class="text-gray-700 font-medium">Sort by:</label>
                <select name="sort" onchange="updateFilters()" id="sort-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="price" <?php echo $sort_by === 'price' ? 'selected' : ''; ?>>Price (Low to High)</option>
                </select>
            </div>
            
            <div class="ml-auto text-gray-600">
                <?php echo $total_products; ?> products found
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <i class="fas fa-search text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-600">No products found</h3>
            <p class="text-gray-500 mt-2">Try adjusting your search or filter</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300 relative">
                <!-- Wishlist Heart Icon -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button onclick="toggleWishlist(<?php echo $product['id']; ?>, this)" 
                            class="absolute top-3 right-3 z-10 bg-white rounded-full p-2 shadow-md hover:shadow-lg transition duration-300">
                        <i class="fas fa-heart <?php echo in_array($product['id'], $user_favorites) ? 'text-red-500' : 'text-gray-300'; ?>"></i>
                    </button>
                <?php endif; ?>
                
                <img src="/bakery-website/assets/images/<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'default-product.svg'; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="w-full h-48 object-cover"
                     onerror="this.src='/bakery-website/assets/images/default-product.svg'">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-[#8B4513]"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <span class="bg-[#FDF8F5] text-[#8B4513] px-2 py-1 rounded text-sm"><?php echo ucfirst($product['category']); ?></span>
                    </div>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-[#D2691E]">₱<?php echo number_format($product['price'], 2); ?></span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo htmlspecialchars($product['image']); ?>')" 
                                    class="bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <a href="/bakery-website/login.php" class="bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">
                                Login to Order
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-12 flex justify-center">
            <nav class="flex items-center space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
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
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="px-3 py-2 text-[#8B4513] bg-white border border-gray-300 rounded-lg hover:bg-[#FDF8F5] transition duration-300">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="px-3 py-2 text-[#8B4513] bg-white border border-gray-300 rounded-lg hover:bg-[#FDF8F5] transition duration-300">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
function addToCart(productId, productName, productPrice, productImage) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1,
            image: productImage || 'default-product.svg'
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showToast('Added to cart!', 'success');
}

function updateFilters() {
    const category = document.getElementById('category-filter').value;
    const sort = document.getElementById('sort-filter').value;
    const search = new URLSearchParams(window.location.search).get('search') || '';
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (category) params.set('category', category);
    if (sort) params.set('sort', sort);
    
    window.location.href = 'menu.php' + (params.toString() ? '?' + params.toString() : '');
}

function toggleWishlist(productId, button) {
    const heartIcon = button.querySelector('i');
    const isCurrentlyFavorited = heartIcon.classList.contains('text-red-500');
    
    fetch('/bakery-website/api/toggle-wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isCurrentlyFavorited) {
                heartIcon.classList.remove('text-red-500');
                heartIcon.classList.add('text-gray-300');
                showToast('Removed from wishlist', 'info');
            } else {
                heartIcon.classList.remove('text-gray-300');
                heartIcon.classList.add('text-red-500');
                showToast('Added to wishlist!', 'success');
            }
        } else {
            showToast(data.message || 'Error updating wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    });
}

// Add loading state to add to cart buttons
document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('button[onclick^="addToCart"]');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            setButtonLoading(this, true);
            setTimeout(() => {
                setButtonLoading(this, false);
            }, 500);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>