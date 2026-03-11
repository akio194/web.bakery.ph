<?php
/**
 * Login Page (login.php)
 * Handles user authentication
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: /bakery-website/admin/dashboard.php');
    } else {
        header('Location: /bakery-website/menu.php');
    }
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        if (empty($errors)) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header('Location: /bakery-website/admin/dashboard.php');
                } else {
                    // Redirect regular users to intended page or menu
                    $redirect = $_SESSION['redirect_url'] ?? '/bakery-website/menu.php';
                    unset($_SESSION['redirect_url']);
                    header('Location: ' . $redirect);
                }
                exit();
            } else {
                $errors[] = 'Invalid email or password';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <i class="fas fa-cake-candles text-5xl text-[#8B4513] mb-4"></i>
            <h2 class="text-3xl font-bold text-[#8B4513] font-['Playfair_Display']">Welcome Back</h2>
            <p class="text-gray-600 mt-2">Please login to your account</p>
        </div>
        
        <?php echo displayValidationErrors($errors); ?>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Email Address</label>
                <input type="email" name="email" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
            </div>
            
            <div>
                <label class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" class="mr-2">
                    <label for="remember" class="text-gray-600">Remember me</label>
                </div>
                <a href="#" class="text-[#D2691E] hover:text-[#8B4513]">Forgot password?</a>
            </div>
            
            <button type="submit" class="w-full bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300">
                Login
            </button>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-gray-600">Don't have an account? 
                <a href="/bakery-website/register.php" class="text-[#D2691E] hover:text-[#8B4513] font-bold">Register here</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>