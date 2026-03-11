<?php
/**
 * Create Database Script
 * Creates the database and tables for the bakery website
 */

echo "<h1>Create Database & Tables</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;}.success{color:green;}.error{color:red;}</style>";

try {
    // Connect to MySQL without database name
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS bakery_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Database 'bakery_db' created/verified</p>";
    
    // Switch to the new database
    $pdo->exec("USE bakery_db");
    
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
    )";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Products table created</p>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Users table created</p>";
    
    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20),
        delivery_address TEXT NOT NULL,
        order_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Orders table created</p>";
    
    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Order items table created</p>";
    
    // Create testimonials table
    $sql = "CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        rating INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Testimonials table created</p>";
    
    // Insert sample testimonials
    $pdo->exec("DELETE FROM testimonials");
    $testimonials = [
        ['name' => 'Sarah Johnson', 'rating' => 5, 'comment' => 'Best chocolate cake I have ever tasted! Will definitely order again.'],
        ['name' => 'Mike Chen', 'rating' => 5, 'comment' => 'Fresh bread and excellent service. The croissants are amazing!'],
        ['name' => 'Emily Rodriguez', 'rating' => 4, 'comment' => 'Great variety of pastries and the cupcakes are delicious.']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO testimonials (name, rating, comment) VALUES (?, ?, ?)");
    foreach ($testimonials as $testimonial) {
        $stmt->execute([$testimonial['name'], $testimonial['rating'], $testimonial['comment']]);
    }
    echo "<p class='success'>✅ Sample testimonials added</p>";
    
    echo "<h2 class='success'>🎉 Database setup completed successfully!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='setup-products.php'>Add Sample Products</a></li>";
    echo "<li><a href='index.php'>Visit Homepage</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Database creation failed: " . $e->getMessage() . "</h2>";
    echo "<p><strong>Troubleshooting:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL service is running</li>";
    echo "<li>Check that MySQL username is 'root' and password is empty</li>";
    echo "<li>Verify MySQL is running on port 3306</li>";
    echo "<li>Try accessing phpMyAdmin to verify MySQL is working</li>";
    echo "</ul>";
}
?>
