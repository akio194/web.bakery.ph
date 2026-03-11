<?php
/**
 * Wishlist Page (wishlist.php)
 * Displays user's favorite products
 */

require_once 'config/database.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to view your wishlist';
    header('Location: /bakery-website/login.php');
    exit();
}

// Redirect admin users - they cannot place orders
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /bakery-website/admin/dashboard.php');
    exit();
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle add/remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $product_id = $_POST['product_id'] ?? 0;
    
    if ($_POST['action'] === 'add') {
        // Add to wishlist
        $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        $_SESSION['success'] = 'Product added to wishlist!';
    } elseif ($_POST['action'] === 'remove') {
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $_SESSION['success'] = 'Product removed from wishlist!';
    }
    
    header('Location: /bakery-website/wishlist.php');
    exit();
}

// Get wishlist items
$stmt = $pdo->prepare("
    SELECT p.*, f.created_at as added_date
    FROM products p
    JOIN favorites f ON p.id = f.product_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-['Playfair_Display']">My Wishlist</h1>
    
    <?php if (empty($wishlist_items)): ?>
        <div class="text-center py-12">
            <i class="fas fa-heart text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-600">Your wishlist is empty</h3>
            <p class="text-gray-500 mt-2">Browse our menu and add items to your wishlist!</p>
            <a href="/bakery-website/menu.php" class="inline-block mt-4 bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300">
                Browse Menu
            </a>
        </div>
    <?php else: ?>
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-[#D2691E]"><?php echo count($wishlist_items); ?></div>
                <div class="stat-label">Items in Wishlist</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-[#D2691E]">₱<?php echo number_format(array_sum(array_column($wishlist_items, 'price')), 0); ?></div>
                <div class="stat-label">Total Value</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-[#D2691E]">
                    <?php 
                    $avg_price = count($wishlist_items) > 0 ? array_sum(array_column($wishlist_items, 'price')) / count($wishlist_items) : 0;
                    echo number_format($avg_price, 0);
                    ?>
                </div>
                <div class="stat-label">Average Price</div>
            </div>
        </div>
        
        <!-- Wishlist Items -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-[#8B4513]">Wishlist Items</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-300 relative">
                        <!-- Remove Button -->
                        <button onclick="removeFromWishlist(<?php echo $item['id']; ?>, this)" 
                                class="absolute top-3 right-3 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition duration-300 z-10">
                            <i class="fas fa-times"></i>
                        </button>
                        
                        <img src="/bakery-website/assets/images/<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'default-product.svg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="w-full h-48 object-cover rounded-t-lg"
                             onerror="this.src='/bakery-website/assets/images/default-product.svg'">
                        
                        <div class="product-info p-6">
                            <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="product-price">₱<?php echo number_format($item['price'], 2); ?></div>
                            
                            <?php if (!empty($item['description'])): ?>
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($item['description'], 0, 150)); ?>
                                    <?php if (strlen($item['description']) > 150): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="product-actions">
                                <button onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', <?php echo $item['price']; ?>, '<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'default-product.svg'; ?>')" 
                                        class="btn-add-cart">
                                    <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromWishlist(productId, button) {
    const productCard = button.closest('.bg-white');
    
    fetch('/bakery-website/api/toggle-wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            product_id: productId,
            action: 'remove'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animate removal
            productCard.style.opacity = '0';
            productCard.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                productCard.remove();
                showToast('Removed from wishlist', 'info');
            }, 300);
        } else {
            showToast(data.message || 'Error removing from wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    });
}

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

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(el => {
        el.textContent = cartCount;
        if (cartCount > 0) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
}
</script>

<style>
.product-image { width: 100%; height: 200px; object-fit: cover; }
.product-info { padding: 1.5rem; }
.product-name { 
    font-size: 1.25rem; 
    font-weight: bold; 
    color: #8B4513; 
    margin-bottom: 0.5rem; 
}
.product-price { 
    font-size: 1.5rem; 
    font-weight: bold; 
    color: #D2691E; 
    margin-bottom: 1rem; 
}
.product-description { 
    color: #6B7280; 
    margin-bottom: 1rem; 
    line-height: 1.5; 
}
.product-actions { 
    margin-top: 1rem; 
}
.btn-add-cart {
    background: #8B4513;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background-color 0.3s;
    font-weight: bold;
}
.btn-add-cart:hover {
    background: #D2691E;
}
.stat-label {
    @apply text-gray-600 text-sm mt-2;
}
</style>

<?php include 'includes/footer.php'; ?>