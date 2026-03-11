<?php
/**
 * Test Script for Bakery Website Features
 * Tests all implemented improvements
 */

require_once 'config/database.php';
require_once 'config/validation.php';

echo "<h1>Bakery Website Feature Tests</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;}.test{margin:10px 0;padding:10px;border:1px solid #ccc;}.pass{background:#d4edda;}.fail{background:#f8d7da;}</style>";

// Test 1: Database Connection
echo "<div class='test'>";
echo "<h2>1. Database Connection</h2>";
try {
    $pdo = getDBConnection();
    echo "<span class='pass'>✓ Database connection successful</span>";
} catch (Exception $e) {
    echo "<span class='fail'>✗ Database connection failed: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Test 2: Validation Functions
echo "<div class='test'>";
echo "<h2>2. Validation Functions</h2>";

// Email validation
$validEmail = isValidEmail('test@example.com');
$invalidEmail = isValidEmail('invalid-email');
echo "Email validation (valid): " . ($validEmail ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
echo "Email validation (invalid): " . ($invalidEmail ? "<span class='fail'>✗ Fail</span>" : "<span class='pass'>✓ Pass</span>") . "<br>";

// Password validation
$validPass = validatePassword('Test123');
$invalidPass = validatePassword('weak');
echo "Password validation (valid): " . (empty($validPass) ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
echo "Password validation (invalid): " . (!empty($invalidPass) ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";

// Name validation
$validName = isValidName('John Doe');
$invalidName = isValidName('John123');
echo "Name validation (valid): " . ($validName ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
echo "Name validation (invalid): " . ($invalidName ? "<span class='fail'>✗ Fail</span>" : "<span class='pass'>✓ Pass</span>") . "<br>";

// Phone validation
$validPhone = isValidPhone('123-456-7890');
$invalidPhone = isValidPhone('123');
echo "Phone validation (valid): " . ($validPhone ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
echo "Phone validation (invalid): " . ($invalidPhone ? "<span class='fail'>✗ Fail</span>" : "<span class='pass'>✓ Pass</span>") . "<br>";

echo "</div>";

// Test 3: CSRF Token Generation
echo "<div class='test'>";
echo "<h2>3. CSRF Token Generation</h2>";
session_start();
$token1 = generateCSRFToken();
$token2 = generateCSRFToken();
echo "Token generation: " . (!empty($token1) ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
echo "Token consistency: " . ($token1 === $token2 ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
echo "</div>";

// Test 4: Input Sanitization
echo "<div class='test'>";
echo "<h2>4. Input Sanitization</h2>";
$dirtyInput = '<script>alert("xss")</script>';
$cleanInput = sanitizeInput($dirtyInput);
echo "Sanitization test: " . ($cleanInput !== $dirtyInput ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
echo "Original: " . htmlspecialchars($dirtyInput) . "<br>";
echo "Sanitized: " . $cleanInput . "<br>";
echo "</div>";

// Test 5: Database Tables Check
echo "<div class='test'>";
echo "<h2>5. Database Tables Check</h2>";
try {
    $pdo = getDBConnection();
    
    // Check if tables exist
    $tables = ['users', 'products', 'orders', 'order_items'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
        $stmt->execute();
        $exists = $stmt->rowCount() > 0;
        echo "Table '$table': " . ($exists ? "<span class='pass'>✓ Exists</span>" : "<span class='fail'>✗ Missing</span>") . "<br>";
    }
} catch (Exception $e) {
    echo "<span class='fail'>✗ Error checking tables: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Test 6: Product Search Query
echo "<div class='test'>";
echo "<h2>6. Product Search Query Test</h2>";
try {
    $pdo = getDBConnection();
    
    // Test search query
    $search = 'cake';
    $sql = "SELECT * FROM products WHERE (name LIKE ? OR description LIKE ? OR category LIKE ?) ORDER BY created_at DESC LIMIT 9";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $products = $stmt->fetchAll();
    
    echo "Search query execution: " . ($stmt !== false ? "<span class='pass'>✓ Pass</span>" : "<span class='fail'>✗ Fail</span>") . "<br>";
    echo "Products found: " . count($products) . "<br>";
    
} catch (Exception $e) {
    echo "<span class='fail'>✗ Search query error: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Test 7: File Existence
echo "<div class='test'>";
echo "<h2>7. File Existence Check</h2>";
$files = [
    'config/validation.php' => 'Validation Helper',
    'order-confirmation.php' => 'Order Confirmation Page',
    'includes/header.php' => 'Header Include',
    'login.php' => 'Login Page',
    'register.php' => 'Register Page',
    'checkout.php' => 'Checkout Page',
    'menu.php' => 'Menu Page'
];

foreach ($files as $file => $description) {
    $exists = file_exists($file);
    echo "$description: " . ($exists ? "<span class='pass'>✓ Exists</span>" : "<span class='fail'>✗ Missing</span>") . "<br>";
}
echo "</div>";

echo "<h2>Test Summary</h2>";
echo "<p>All tests completed. Check individual results above.</p>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='login.php'>Go to Login</a> | <a href='menu.php'>Go to Menu</a></p>";
?>
