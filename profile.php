<?php
/**
 * Profile Page (profile.php)
 * Displays user profile and order history
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to view your profile';
    header('Location: /bakery-website/login.php');
    exit();
}

// Redirect admin users - they should only access admin pages
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /bakery-website/admin/dashboard.php');
    exit();
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Security token expired. Please try again.';
        header('Location: /bakery-website/profile.php');
        exit();
    }
    
    // Validate form data
    $errors = [];
    
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    
    // Validate name
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (!isValidName($name)) {
        $errors[] = 'Name must contain only letters, spaces, and hyphens (minimum 2 characters)';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Validate phone
    if (!empty($phone)) {
        if (!isValidPhone($phone)) {
            $errors[] = 'Please enter a valid phone number';
        }
    }
    
    // Validate address (optional)
    if (!empty($address) && !isValidAddress($address)) {
        $errors[] = 'Please enter a complete address';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    name = ?, email = ?, phone = ?, address = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $address, $user_id]);
            
            $_SESSION['success'] = 'Profile updated successfully!';
            header('Location: /bakery-website/profile.php');
            exit();
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error updating profile: ' . $e->getMessage();
            header('Location: /bakery-website/profile.php');
            exit();
        }
    }
}

// Get user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Get user statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_price), 0) as total_spent FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    // Get recent orders
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll();

} catch (Exception $e) {
    error_log('Error loading profile: ' . $e->getMessage());
    $user = [];
    $stats = ['total_orders' => 0, 'total_spent' => 0];
    $recent_orders = [];
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-['Playfair_Display']">My Profile</h1>
        
        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6 text-[#8B4513]">Profile Information</h2>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Address (Optional)</label>
                            <textarea name="address" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]"
                                      placeholder="Enter your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300 font-bold">
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Statistics -->
            <div>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6 text-[#8B4513]">Account Statistics</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Orders:</span>
                            <span class="font-bold text-[#D2691E]"><?php echo $stats['total_orders']; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Spent:</span>
                            <span class="font-bold text-[#D2691E]">₱<?php echo number_format($stats['total_spent'], 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Member Since:</span>
                            <span class="font-bold text-[#D2691E]"><?php echo date('F j, Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[#8B4513]">Recent Orders</h2>
                    <a href="/bakery-website/orders.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300">
                        View All Orders <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <?php if (empty($recent_orders)): ?>
                    <p class="text-gray-500 text-center py-8">No orders yet</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="border-b pb-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-[#8B4513]">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo date('F j, Y, g:i A', strtotime($order['created_at'])); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo $order['item_count']; ?> items</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-[#D2691E]">$<?php echo number_format($order['total_price'] ?? 0, 2); ?></span>
                                        <a href="/bakery-website/orders.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300">
                                            View Details <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>