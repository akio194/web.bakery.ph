<?php
/**
 * Add Test Data Script
 * Populates the bakery database with realistic sample data
 */

require_once 'config/database.php';

echo "<h2>🧁 Adding Test Data to Sweet Delights Bakery</h2>";

try {
    $pdo = getDBConnection();
    echo "✅ Database connected successfully<br>";
    
    // Clear existing test data (optional - comment out if you want to keep existing data)
    echo "<h3>🗑️ Cleaning existing data...</h3>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DELETE FROM order_items");
    $pdo->exec("DELETE FROM orders");
    $pdo->exec("DELETE FROM favorites");
    $pdo->exec("DELETE FROM users WHERE role = 'customer'");
    $pdo->exec("DELETE FROM products");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Existing data cleared<br>";
    
    // Add Products
    echo "<h3>🥐 Adding Bakery Products...</h3>";
    $products = [
        ['Classic Croissant', 'Buttery, flaky croissant baked fresh daily', 'pastries', 45.00, 'croissant.jpg', 1],
        ['Chocolate Croissant', 'Rich chocolate filling in our classic croissant', 'pastries', 55.00, 'chocolate-croissant.jpg', 1],
        ['Almond Croissant', 'Frangipane and sliced almonds on croissant', 'pastries', 65.00, 'almond-croissant.jpg', 1],
        ['Blueberry Muffin', 'Fresh blueberries in a moist muffin', 'muffins', 35.00, 'blueberry-muffin.jpg', 1],
        ['Chocolate Chip Muffin', 'Loaded with chocolate chips', 'muffins', 35.00, 'chocolate-muffin.jpg', 1],
        ['Banana Nut Muffin', 'Ripe bananas with walnuts', 'muffins', 35.00, 'banana-muffin.jpg', 1],
        ['Sourdough Bread', 'Artisan sourdough with perfect crust', 'bread', 120.00, 'sourdough.jpg', 1],
        ['Whole Wheat Bread', 'Healthy and wholesome', 'bread', 85.00, 'whole-wheat.jpg', 1],
        ['French Baguette', 'Classic French baguette', 'bread', 65.00, 'baguette.jpg', 1],
        ['Chocolate Cake', 'Rich chocolate layer cake', 'cakes', 350.00, 'chocolate-cake.jpg', 1],
        ['Vanilla Birthday Cake', 'Classic vanilla with buttercream', 'cakes', 400.00, 'birthday-cake.jpg', 1],
        ['Red Velvet Cake', 'Southern favorite with cream cheese frosting', 'cakes', 450.00, 'red-velvet.jpg', 1],
        ['New York Cheesecake', 'Creamy and rich', 'cakes', 280.00, 'cheesecake.jpg', 1],
        ['Tiramisu', 'Classic Italian dessert', 'desserts', 180.00, 'tiramisu.jpg', 1],
        ['Chocolate Brownie', 'Fudgy brownie with walnuts', 'desserts', 65.00, 'brownie.jpg', 1],
        ['Apple Pie', 'Traditional apple pie with cinnamon', 'pies', 220.00, 'apple-pie.jpg', 1],
        ['Pumpkin Pie', 'Seasonal favorite', 'pies', 200.00, 'pumpkin-pie.jpg', 1],
        ['Chocolate Chip Cookies', 'Classic homemade cookies', 'cookies', 25.00, 'chocolate-cookies.jpg', 1],
        ['Oatmeal Raisin Cookies', 'Chewy and wholesome', 'cookies', 25.00, 'oatmeal-cookies.jpg', 1],
        ['Sugar Cookies', 'Decorated sugar cookies', 'cookies', 30.00, 'sugar-cookies.jpg', 1],
        ['Cinnamon Roll', 'Sweet with cream cheese frosting', 'pastries', 55.00, 'cinnamon-roll.jpg', 1],
        ['Pain au Chocolat', 'French chocolate pastry', 'pastries', 50.00, 'pain-chocolat.jpg', 1],
        ['Fruit Tart', 'Fresh seasonal fruits', 'desserts', 150.00, 'fruit-tart.jpg', 1],
        ['Éclair', 'Classic French pastry', 'desserts', 85.00, 'eclair.jpg', 1],
        ['Bagel', 'New York style bagel', 'bread', 40.00, 'bagel.jpg', 1]
    ];
    
    $product_stmt = $pdo->prepare("INSERT INTO products (name, description, category, price, image, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $product_stmt->execute($product);
    }
    echo "✅ Added " . count($products) . " products<br>";
    
    // Add Customers
    echo "<h3>👥 Adding Test Customers...</h3>";
    $customers = [
        ['John Smith', 'john.smith@email.com', 'password123', '555-0101', '123 Main St, Manila', 'customer'],
        ['Maria Garcia', 'maria.garcia@email.com', 'password123', '555-0102', '456 Oak Ave, Quezon City', 'customer'],
        ['Chen Wei', 'chen.wei@email.com', 'password123', '555-0103', '789 Pine Rd, Makati', 'customer'],
        ['Sarah Johnson', 'sarah.johnson@email.com', 'password123', '555-0104', '321 Elm St, Pasay', 'customer'],
        ['Raj Patel', 'raj.patel@email.com', 'password123', '555-0105', '654 Maple Dr, Mandaluyong', 'customer'],
        ['Emily Davis', 'emily.davis@email.com', 'password123', '555-0106', '987 Cedar Ln, Paranaque', 'customer'],
        ['Carlos Rodriguez', 'carlos.rodriguez@email.com', 'password123', '555-0107', '147 Birch Way, Las Pinas', 'customer'],
        ['Lisa Wong', 'lisa.wong@email.com', 'password123', '555-0108', '258 Spruce St, Muntinlupa', 'customer'],
        ['Ahmed Hassan', 'ahmed.hassan@email.com', 'password123', '555-0109', '369 Willow Ave, San Juan', 'customer'],
        ['Nina Petrov', 'nina.petrov@email.com', 'password123', '555-0110', '741 Ash Blvd, Pasig', 'customer'],
        ['James Wilson', 'james.wilson@email.com', 'password123', '555-0111', '852 Poplar Ct, Taguig', 'customer'],
        ['Sofia Martinez', 'sofia.martinez@email.com', 'password123', '555-0112', '963 Fir Rd, Cainta', 'customer'],
        ['David Kim', 'david.kim@email.com', 'password123', '555-0113', '147 Pine St, Marikina', 'customer'],
        ['Anna Schmidt', 'anna.schmidt@email.com', 'password123', '555-0114', '258 Oak Ave, Valenzuela', 'customer'],
        ['Mohamed Ali', 'mohamed.ali@email.com', 'password123', '555-0115', '369 Elm Dr, Caloocan', 'customer']
    ];
    
    $customer_stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    foreach ($customers as $customer) {
        $customer_stmt->execute($customer);
    }
    echo "✅ Added " . count($customers) . " customers<br>";
    
    // Get customer IDs for orders
    $customer_ids = $pdo->query("SELECT id FROM users WHERE role = 'customer' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $product_ids = $pdo->query("SELECT id, price FROM products ORDER BY id")->fetchAll();
    
    // Add Orders
    echo "<h3>📦 Adding Test Orders...</h3>";
    $order_statuses = ['pending', 'processing', 'completed', 'completed', 'completed'];
    $payment_methods = ['cash', 'gcash', 'credit_card', 'bank_transfer'];
    
    $orders_added = 0;
    foreach ($customer_ids as $index => $customer_id) {
        // Each customer gets 1-5 orders
        $num_orders = rand(1, 5);
        
        for ($i = 0; $i < $num_orders; $i++) {
            // Random order date within last 3 months
            $days_ago = rand(1, 90);
            $order_date = date('Y-m-d H:i:s', strtotime("-$days_ago days"));
            
            // Create order
            $order_stmt = $pdo->prepare("
                INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, total_price, status, payment_method, order_notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $customer = $customers[$index];
            $status = $order_statuses[array_rand($order_statuses)];
            $payment_method = $payment_methods[array_rand($payment_methods)];
            
            // Generate random order items
            $num_items = rand(1, 4);
            $selected_products = array_rand($product_ids, $num_items);
            if (!is_array($selected_products)) {
                $selected_products = [$selected_products];
            }
            
            $total_price = 0;
            $order_items = [];
            
            foreach ($selected_products as $product_index) {
                $product = $product_ids[$product_index];
                $quantity = rand(1, 3);
                $item_total = $product['price'] * $quantity;
                $total_price += $item_total;
                
                $order_items[] = [
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                    'price' => $product['price']
                ];
            }
            
            $order_notes = rand(0, 3) === 0 ? [
                'Please deliver after 6 PM',
                'Extra napkins please',
                'Gift wrapping needed',
                'Call upon arrival',
                'Leave at door please'
            ][array_rand([0, 1, 2, 3, 4])] : '';
            
            $order_stmt->execute([
                $customer_id,
                $customer[0],
                $customer[1],
                $customer[3],
                $customer[4],
                $total_price,
                $status,
                $payment_method,
                $order_notes,
                $order_date
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Add order items
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($order_items as $item) {
                $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }
            
            $orders_added++;
        }
    }
    
    echo "✅ Added $orders_added orders<br>";
    
    // Add some favorites/wishlist items
    echo "<h3>❤️ Adding Wishlist Items...</h3>";
    $favorites_added = 0;
    foreach ($customer_ids as $customer_id) {
        // Each customer has 2-8 favorite items
        $num_favorites = rand(2, 8);
        $favorite_products = array_rand($product_ids, $num_favorites);
        if (!is_array($favorite_products)) {
            $favorite_products = [$favorite_products];
        }
        
        $fav_stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
        foreach ($favorite_products as $product_index) {
            $fav_stmt->execute([$customer_id, $product_ids[$product_index]['id']]);
            $favorites_added++;
        }
    }
    
    echo "✅ Added $favorites_added wishlist items<br>";
    
    // Display summary
    echo "<h3>📊 Test Data Summary:</h3>";
    echo "<div style='background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<strong>Products:</strong> " . count($products) . "<br>";
    echo "<strong>Customers:</strong> " . count($customers) . "<br>";
    echo "<strong>Orders:</strong> $orders_added<br>";
    echo "<strong>Wishlist Items:</strong> $favorites_added<br>";
    echo "</div>";
    
    echo "<h3>🔗 Test Accounts:</h3>";
    echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<strong>You can now test with these accounts:</strong><br><br>";
    foreach (array_slice($customers, 0, 5) as $customer) {
        echo "📧 <strong>Email:</strong> " . $customer[1] . "<br>";
        echo "🔑 <strong>Password:</strong> password123<br><br>";
    }
    echo "</div>";
    
    echo "<h3>🚀 What to Test:</h3>";
    echo "<ul>";
    echo "<li><a href='/bakery-website/login.php'>🔐 Login</a> with any test customer account</li>";
    echo "<li><a href='/bakery-website/menu.php'>🥐 Browse Products</a> and add to cart</li>";
    echo "<li><a href='/bakery-website/wishlist.php'>❤️ View Wishlist</a> functionality</li>";
    echo "<li><a href='/bakery-website/orders.php'>📦 Check Order History</a></li>";
    echo "<li><a href='/bakery-website/checkout.php'>🛒 Test Checkout</a> process</li>";
    echo "<li><a href='/bakery-website/admin/customers.php'>👥 Admin: View Customers</a> (View button should work now!)</li>";
    echo "<li><a href='/bakery-website/admin/orders.php'>📋 Admin: Manage Orders</a></li>";
    echo "<li><a href='/bakery-website/admin/products.php'>🥖 Admin: Manage Products</a></li>";
    echo "<li><a href='/bakery-website/admin/analytics.php'>📊 Admin: View Analytics</a></li>";
    echo "</ul>";
    
    echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "✅ <strong>Test data added successfully!</strong><br>";
    echo "Your bakery website is now populated with realistic data for testing all features.";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>🔄 Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='/bakery-website/'>🏠 Homepage</a></li>";
echo "<li><a href='/bakery-website/admin/dashboard.php'>👨‍💼 Admin Dashboard</a></li>";
echo "<li><a href='/bakery-website/admin/debug-customers.php'>🔍 Debug Customers</a></li>";
echo "<li><a href='/bakery-website/menu.php'>🥐 Browse Menu</a></li>";
echo "</ul>";
?>
