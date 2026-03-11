<?php
/**
 * Simple Test Script
 */

require_once 'config/database.php';
require_once 'config/validation.php';

echo "<h1>Quick Test Results</h1>";
echo "<style>.pass{color:green;}.fail{color:red;}</style>";

// Test 1: Database Connection
try {
    $pdo = getDBConnection();
    echo "<p>✓ Database: <span class='pass'>Connected</span></p>";
} catch (Exception $e) {
    echo "<p>✗ Database: <span class='fail'>" . $e->getMessage() . "</span></p>";
}

// Test 2: Validation
echo "<p>✓ Email Validation: <span class='pass'>" . (isValidEmail('test@test.com') ? 'Working' : 'Failed') . "</span></p>";
echo "<p>✓ Password Validation: <span class='pass'>" . (empty(validatePassword('Test123')) ? 'Working' : 'Failed') . "</span></p>";
echo "<p>✓ Name Validation: <span class='pass'>" . (isValidName('John Doe') ? 'Working' : 'Failed') . "</span></p>";

// Test 3: CSRF Token
session_start();
$token = generateCSRFToken();
echo "<p>✓ CSRF Token: <span class='pass'>" . (!empty($token) ? 'Generated' : 'Failed') . "</span></p>";

// Test 4: Search Query
try {
    $pdo = getDBConnection();
    $search = 'cake';
    $sql = "SELECT * FROM products WHERE (name LIKE ? OR description LIKE ? OR category LIKE ?) LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $products = $stmt->fetchAll();
    echo "<p>✓ Search Query: <span class='pass'>Working (" . count($products) . " results)</span></p>";
} catch (Exception $e) {
    echo "<p>✗ Search Query: <span class='fail'>" . $e->getMessage() . "</span></p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Homepage</a> | <a href='menu.php'>Menu</a> | <a href='login.php'>Login</a></p>";
?>
