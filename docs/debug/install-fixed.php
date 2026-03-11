<?php
/**
 * Fixed Installation Script
 * Creates database, tables, and sample data without foreign key issues
 */

echo "<h1>🎯 Bakery Website Installation (Fixed)</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;}.success{color:green;}.error{color:red;}.step{background:#f8f9fa;padding:15px;border-radius:5px;margin:10px 0;}</style>";

try {
    // Step 1: Connect to MySQL (without database)
    echo "<div class='step'>";
    echo "<h2>Step 1: Connecting to MySQL...</h2>";
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Connected to MySQL server</p>";
    
    // Step 2: Create database
    echo "<h2>Step 2: Creating database...</h2>";
    $pdo->exec("DROP DATABASE IF EXISTS bakery_db");
    $pdo->exec("CREATE DATABASE bakery_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p class='success'>✅ Database 'bakery_db' created fresh</p>";
    
    // Step 3: Select database
    $pdo->exec("USE bakery_db");
    
    // Step 4: Create tables in correct order (without foreign keys first)
    echo "<h2>Step 3: Creating tables...</h2>";
    
    // Users table (no foreign keys)
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Users table created</p>";
    
    // Products table (no foreign keys)
    $sql = "CREATE TABLE products (
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
    echo "<p class='success'>✅ Products table created</p>";
    
    // Orders table (no foreign keys initially)
    $sql = "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20),
        delivery_address TEXT NOT NULL,
        order_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Orders table created</p>";
    
    // Order items table (no foreign keys initially)
    $sql = "CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Order items table created</p>";
    
    // Testimonials table (no foreign keys)
    $sql = "CREATE TABLE testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        rating INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "<p class='success'>✅ Testimonials table created</p>";
    
    echo "</div>";
    
    // Step 5: Insert sample data
    echo "<div class='step'>";
    echo "<h2>Step 4: Adding sample data...</h2>";
    
    // Add sample testimonials
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
    
    // Add sample products
    $products = [
        ['name' => 'Chocolate Cake', 'description' => 'Rich chocolate cake with smooth frosting', 'price' => 450.00, 'category' => 'cakes', 'image' => 'chocolate-cake.jpg', 'featured' => 1],
        ['name' => 'Birthday Cake', 'description' => 'Celebration cake with colorful decorations', 'price' => 650.00, 'category' => 'cakes', 'image' => 'birthday-cake.jpg', 'featured' => 1],
        ['name' => 'Fresh Croissant', 'description' => 'Buttery French croissant, flaky and delicious', 'price' => 85.00, 'category' => 'pastries', 'image' => 'croissant.jpg', 'featured' => 0],
        ['name' => 'Cupcakes', 'description' => 'Assorted flavored cupcakes with frosting', 'price' => 75.00, 'category' => 'cakes', 'image' => 'cupcakes.jpg', 'featured' => 1],
        ['name' => 'Sourdough Bread', 'description' => 'Artisan sourdough bread with crispy crust', 'price' => 120.00, 'category' => 'bread', 'image' => 'sourdough.jpg', 'featured' => 0],
        ['name' => 'Fresh Bagels', 'description' => 'Traditional bagels perfect for breakfast', 'price' => 95.00, 'category' => 'bread', 'image' => 'bagels.jpg', 'featured' => 0],
        ['name' => 'French Macarons', 'description' => 'Delicate French macarons in assorted colors', 'price' => 55.00, 'category' => 'pastries', 'image' => 'macarons.jpg', 'featured' => 1],
        ['name' => 'Apple Pie', 'description' => 'Classic apple pie with flaky crust', 'price' => 380.00, 'category' => 'pies', 'image' => 'apple-pie.jpg', 'featured' => 0],
        ['name' => 'Chocolate Croissant', 'description' => 'Chocolate-filled croissant with powdered sugar', 'price' => 95.00, 'category' => 'pastries', 'image' => 'croissant.jpg', 'featured' => 0]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, image, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $product['category'],
            $product['image'],
            $product['featured']
        ]);
    }
    echo "<p class='success'>✅ Sample products added</p>";
    
    // Add sample admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Admin User', 'admin@bakery.com', $admin_password, 'admin']);
    echo "<p class='success'>✅ Admin user created (email: admin@bakery.com, password: admin123)</p>";
    
    echo "</div>";
    
    echo "<div class='step' style='background:#d4edda;'>";
    echo "<h2 class='success'>🎉 Installation Complete!</h2>";
    echo "<p><strong>Your bakery website is now ready!</strong></p>";
    echo "<ul>";
    echo "<li>✅ Database and tables created</li>";
    echo "<li>✅ Sample products added</li>";
    echo "<li>✅ Testimonials added</li>";
    echo "<li>✅ Admin account created</li>";
    echo "</ul>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='index.php' style='font-size:18px;color:#28a745;'>🏠 Visit Homepage</a></li>";
    echo "<li><a href='menu.php' style='font-size:18px;color:#28a745;'>🍰 Browse Menu</a></li>";
    echo "<li><a href='login.php' style='font-size:18px;color:#28a745;'>🔐 Login as Admin</a></li>";
    echo "</ol>";
    echo "<p><strong>Admin Login:</strong> admin@bakery.com / admin123</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='step' style='background:#f8d7da;'>";
    echo "<h2 class='error'>❌ Installation Failed</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Make sure MySQL is running in XAMPP</li>";
    echo "<li>Check if MySQL port is correct (usually 3306 or 3307)</li>";
    echo "<li>Verify MySQL username is 'root' with no password</li>";
    echo "<li>Try running XAMPP as Administrator</li>";
    echo "</ul>";
    echo "</div>";
}
?>
