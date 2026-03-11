<?php
/**
 * Register Page (register.php)
 * Handles new user registration
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /bakery-website/menu.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please try again.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
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
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required';
        } else {
            $passwordErrors = validatePassword($password);
            if (!empty($passwordErrors)) {
                $errors = array_merge($errors, $passwordErrors);
            }
        }
        
        // Validate password confirmation
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            $pdo = getDBConnection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
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
            <h2 class="text-3xl font-bold text-[#8B4513] font-['Playfair_Display']">Create Account</h2>
            <p class="text-gray-600 mt-2">Join our bakery family</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
            </div>
            
            <div>
                <label class="block text-gray-700 font-bold mb-2">Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
            </div>
            
            <div>
                <label class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                <p class="text-sm text-gray-500 mt-1">Minimum 6 characters, 1 uppercase, 1 number</p>
            </div>
            
            <div>
                <label class="block text-gray-700 font-bold mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
            </div>
            
            <button type="submit" class="w-full bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300">
                Register
            </button>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-gray-600">Already have an account? 
                <a href="/bakery-website/login.php" class="text-[#D2691E] hover:text-[#8B4513] font-bold">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>