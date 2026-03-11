<?php
/**
 * Create Missing Tables
 * Creates any missing database tables for the bakery system
 */

require_once 'config/database.php';

echo "<h2>🔧 Creating Missing Database Tables</h2>";

try {
    $pdo = getDBConnection();
    echo "✅ Database connected successfully<br>";
    
    // Create favorites table
    echo "<h3>❤️ Creating Favorites Table...</h3>";
    $sql = "CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_product (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "✅ Favorites table created successfully<br>";
    
    // Check if users table has role column
    echo "<h3>👥 Checking Users Table Structure...</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('role', $columns)) {
        echo "📝 Adding role column to users table...<br>";
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
        echo "✅ Role column added<br>";
    } else {
        echo "✅ Role column already exists<br>";
    }
    
    // Update existing users to have customer role if not set
    $stmt = $pdo->query("UPDATE users SET role = 'customer' WHERE role IS NULL OR role = ''");
    echo "✅ User roles updated<br>";
    
    // Check if products table exists and has required columns
    echo "<h3>🥐 Checking Products Table...</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() == 0) {
        echo "📝 Creating products table...<br>";
        $sql = "CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            price DECIMAL(10,2) NOT NULL,
            image VARCHAR(255),
            is_featured BOOLEAN DEFAULT 0,
            stock_quantity INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Products table created<br>";
    } else {
        echo "✅ Products table already exists<br>";
        
        // Check for missing columns
        $stmt = $pdo->query("DESCRIBE products");
        $product_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('is_featured', $product_columns)) {
            $pdo->exec("ALTER TABLE products ADD COLUMN is_featured BOOLEAN DEFAULT 0");
            echo "✅ Added is_featured column to products<br>";
        }
        
        if (!in_array('stock_quantity', $product_columns)) {
            $pdo->exec("ALTER TABLE products ADD COLUMN stock_quantity INT DEFAULT 0");
            echo "✅ Added stock_quantity column to products<br>";
        }
    }
    
    // Check if orders table exists
    echo "<h3>📦 Checking Orders Table...</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        echo "📝 Creating orders table...<br>";
        $sql = "CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            customer_name VARCHAR(255),
            customer_email VARCHAR(255),
            customer_phone VARCHAR(50),
            delivery_address TEXT,
            total_price DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            payment_method VARCHAR(50),
            order_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Orders table created<br>";
    } else {
        echo "✅ Orders table already exists<br>";
    }
    
    // Check if order_items table exists
    echo "<h3>🛍️ Checking Order Items Table...</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() == 0) {
        echo "📝 Creating order_items table...<br>";
        $sql = "CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Order items table created<br>";
    } else {
        echo "✅ Order items table already exists<br>";
    }
    
    // Show current table status
    echo "<h3>📊 Current Database Tables:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div style='background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    foreach ($tables as $table) {
        echo "✅ $table<br>";
    }
    echo "</div>";
    
    // Check record counts
    echo "<h3>📈 Current Record Counts:</h3>";
    echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    
    foreach (['users', 'products', 'orders', 'order_items', 'favorites'] as $table) {
        if (in_array($table, $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "📊 $table: $count records<br>";
        }
    }
    echo "</div>";
    
    echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "✅ <strong>All required tables are now ready!</strong><br>";
    echo "You can now run the test data script successfully.";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>🔄 Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='/bakery-website/add-test-data.php'>🧁 Add Test Data</a> - Run this to populate with sample data</li>";
echo "<li><a href='/bakery-website/admin/debug-customers.php'>🔍 Debug Customers</a> - Check customer functionality</li>";
echo "<li><a href='/bakery-website/admin/dashboard.php'>👨‍💼 Admin Dashboard</a> - Start testing admin features</li>";
echo "</ol>";
?>
