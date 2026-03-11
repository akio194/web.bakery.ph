<?php
/**
 * Setup Products Table
 * Creates the products table if it doesn't exist
 */

require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();

try {
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(100) NOT NULL,
        image VARCHAR(255),
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Check if table exists and show structure
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll();
    
    echo "<h2>Products Table Structure:</h2>";
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
    
    // Test a simple insert
    echo "<h2>Testing Simple Insert:</h2>";
    $testStmt = $pdo->prepare("INSERT INTO products (name, description, price, category, is_featured) VALUES (?, ?, ?, ?, ?)");
    
    if ($testStmt) {
        echo "<p style='color: green;'>✓ Statement prepared successfully</p>";
        
        $testResult = $testStmt->execute(['Test Product', 'Test Description', 99.99, 'test', 0]);
        
        if ($testResult) {
            echo "<p style='color: green;'>✓ Test insert successful</p>";
            
            // Clean up test data
            $deleteStmt = $pdo->prepare("DELETE FROM products WHERE name = ?");
            $deleteStmt->execute(['Test Product']);
            echo "<p style='color: blue;'>✓ Test data cleaned up</p>";
        } else {
            echo "<p style='color: red;'>✗ Test insert failed: " . implode(', ', $testStmt->errorInfo()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Statement preparation failed: " . implode(', ', $pdo->errorInfo()) . "</p>";
    }
    
    echo "<p><a href='/bakery-website/admin/products.php'>← Back to Products</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check your database connection and table structure.</p>";
}
?>
