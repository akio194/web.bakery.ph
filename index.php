<?php
/**
 * Homepage (index.php)
 * Displays hero section, featured products, about, and testimonials
 */

require_once 'config/database.php';

// Redirect admin users - they should only access admin pages
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /bakery-website/admin/dashboard.php');
    exit();
}

$pdo = getDBConnection();

// Fetch featured products
$stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 LIMIT 4");
$featuredProducts = $stmt->fetchAll();

// Fetch testimonials
$stmt = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 3");
$testimonials = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative h-[600px] bg-cover bg-center" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1509440159596-0249088772ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
    <div class="absolute inset-0 flex items-center justify-center text-center">
        <div class="text-white px-4">
            <h1 class="text-5xl md:text-6xl font-bold mb-4 font-['Playfair_Display']">Sweet Delights Bakery</h1>
            <p class="text-xl md:text-2xl mb-8">Freshly baked with love every day</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/bakery-website/menu.php" class="bg-[#D2691E] text-white px-8 py-4 rounded-lg text-lg hover:bg-[#8B4513] transition duration-300 inline-block">Order Now</a>
            <?php else: ?>
                <a href="/bakery-website/register.php" class="bg-[#D2691E] text-white px-8 py-4 rounded-lg text-lg hover:bg-[#8B4513] transition duration-300 inline-block">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="container mx-auto px-4 py-16">
    <h2 class="text-4xl font-bold text-center mb-12 text-[#8B4513] font-['Playfair_Display']">Our Featured Treats</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php foreach ($featuredProducts as $product): ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
            <img src="/bakery-website/assets/images/<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'default-product.jpg'; ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="w-full h-48 object-cover"
                 onerror="this.src='/bakery-website/assets/images/default-product.jpg'">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-2 text-[#8B4513]"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
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
</section>

<!-- About Section -->
<section class="bg-white py-16">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-12">
            <div class="md:w-1/2">
                <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" 
                     alt="Our Bakery" 
                     class="rounded-lg shadow-xl">
            </div>
            <div class="md:w-1/2">
                <h2 class="text-4xl font-bold mb-6 text-[#8B4513] font-['Playfair_Display']">Our Story</h2>
                <p class="text-gray-700 mb-4 text-lg">
                    Founded in 2010, Sweet Delights Bakery has been serving the community with the finest baked goods made from traditional recipes and the highest quality ingredients.
                </p>
                <p class="text-gray-700 mb-6 text-lg">
                    Every morning, our bakers arrive early to prepare fresh bread, pastries, and cakes. We believe in the power of good food to bring people together and create lasting memories.
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-[#D2691E]">10+</div>
                        <div class="text-gray-600">Years of Experience</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-[#D2691E]">50+</div>
                        <div class="text-gray-600">Daily Recipes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="container mx-auto px-4 py-16">
    <h2 class="text-4xl font-bold text-center mb-12 text-[#8B4513] font-['Playfair_Display']">What Our Customers Say</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($testimonials as $testimonial): ?>
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center mb-4">
                <div class="text-yellow-400 flex">
                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                        <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
            </div>
            <p class="text-gray-700 mb-4 italic">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
            <div class="font-bold text-[#8B4513]">- <?php echo htmlspecialchars($testimonial['customer_name']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

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
            image: productImage || 'default-product.jpg'
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showToast('Added to cart!', 'success');
}
</script>

<?php include 'includes/footer.php'; ?>