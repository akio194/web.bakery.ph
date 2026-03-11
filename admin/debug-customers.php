<?php
/**
 * Debug Customers Page
 * Simple version to test customer functionality
 */

require_once '../config/database.php';
requireAdmin();

echo "<h2>Customers Debug Page</h2>";

try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists<br>";
    } else {
        echo "❌ Users table missing<br>";
    }
    
    // Check if orders table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Orders table exists<br>";
    } else {
        echo "❌ Orders table missing<br>";
    }
    
    // Get all users with their roles
    echo "<h3>All Users in Database:</h3>";
    $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Action</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            
            if ($user['role'] === 'customer') {
                echo "<td><a href='/bakery-website/admin/customer-details.php?id=" . $user['id'] . "' target='_blank'>View Details</a></td>";
            } else {
                echo "<td>Not a customer</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Count customers specifically
        $customer_count = 0;
        foreach ($users as $user) {
            if ($user['role'] === 'customer') {
                $customer_count++;
            }
        }
        echo "<br><strong>Total Customers: " . $customer_count . "</strong><br>";
        
        if ($customer_count === 0) {
            echo "<br><div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem;'>";
            echo "⚠️ <strong>No customers found!</strong><br>";
            echo "You need to register a customer account first. Go to <a href='/bakery-website/register.php'>Register Page</a> and create a customer account.<br>";
            echo "Then the View button will work on the customers page.";
            echo "</div>";
        }
        
    } else {
        echo "❌ No users found in database<br>";
        echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem;'>";
        echo "⚠️ <strong>No users found!</strong><br>";
        echo "Please run the installation script to create sample data.";
        echo "</div>";
    }
    
    // Test customer details query for first customer
    if ($customer_count > 0) {
        echo "<h3>Test Customer Details Query:</h3>";
        $first_customer = null;
        foreach ($users as $user) {
            if ($user['role'] === 'customer') {
                $first_customer = $user;
                break;
            }
        }
        
        if ($first_customer) {
            echo "Testing with customer: " . htmlspecialchars($first_customer['name']) . " (ID: " . $first_customer['id'] . ")<br>";
            
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        u.id,
                        u.name,
                        u.email,
                        u.phone,
                        u.address,
                        u.created_at as registration_date,
                        COUNT(DISTINCT o.id) as total_orders,
                        COUNT(DISTINCT CASE WHEN o.status != 'cancelled' THEN o.id END) as completed_orders,
                        SUM(CASE WHEN o.status != 'cancelled' THEN o.total_price ELSE 0 END) as total_spent
                    FROM users u
                    LEFT JOIN orders o ON u.id = o.user_id
                    WHERE u.id = ? AND u.role = 'customer'
                    GROUP BY u.id
                ");
                $stmt->execute([$first_customer['id']]);
                $customer_details = $stmt->fetch();
                
                if ($customer_details) {
                    echo "✅ Customer details query successful<br>";
                    echo "<pre>";
                    print_r($customer_details);
                    echo "</pre>";
                } else {
                    echo "❌ Customer details query returned no results<br>";
                }
            } catch (PDOException $e) {
                echo "❌ Customer details query error: " . $e->getMessage() . "<br>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='/bakery-website/admin/customers.php'>Customers Page</a></li>";
echo "<li><a href='/bakery-website/admin/test-customer-details.php'>Test Customer Details</a></li>";
echo "<li><a href='/bakery-website/register.php'>Register New Customer</a></li>";
echo "<li><a href='/bakery-website/admin/dashboard.php'>Admin Dashboard</a></li>";
echo "</ul>";
?>
