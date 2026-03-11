<?php
/**
 * Database Connection Test
 * Tests database connection and table existence
 */

require_once '../config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        echo "<p style='color: green;'>✓ Products table exists</p>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll();
        
        echo "<h3>Products Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 20px;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Count existing products
        $countStmt = $pdo->query("SELECT COUNT(*) FROM products");
        $productCount = $countStmt->fetchColumn();
        echo "<p>Total products in database: " . $productCount . "</p>";
        
    } else {
        echo "<p style='color: red;'>✗ Products table does not exist</p>";
        echo "<p>You need to run the installation script first.</p>";
    }
    
    // Test simple query
    echo "<h3>Testing Simple Query:</h3>";
    $testStmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    if ($testStmt) {
        $testStmt->execute();
        $count = $testStmt->fetchColumn();
        echo "<p style='color: green;'>✓ Simple query successful. Found $count products</p>";
    } else {
        echo "<p style='color: red;'>✗ Simple query failed: " . implode(', ', $pdo->errorInfo()) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='/bakery-website/admin/products.php'>← Back to Products</a></p>";
?>
