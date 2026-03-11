<?php
/**
 * Test Customer Details
 * Debug script to check customer details functionality
 */

require_once '../config/database.php';

echo "<h2>Customer Details Debug Test</h2>";

try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br>";
    
    // Test if customers table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $result = $stmt->fetch();
    echo "📊 Found " . $result['count'] . " customers in database<br>";
    
    // Get first customer ID for testing
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'customer' LIMIT 1");
    $customer = $stmt->fetch();
    
    if ($customer) {
        echo "👤 Testing with customer: " . htmlspecialchars($customer['name']) . " (ID: " . $customer['id'] . ")<br>";
        
        // Test the customer details query
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
                SUM(CASE WHEN o.status != 'cancelled' THEN o.total_price ELSE 0 END) as total_spent,
                AVG(CASE WHEN o.status != 'cancelled' THEN o.total_price ELSE NULL END) as avg_order_value,
                MAX(o.created_at) as last_order_date,
                MIN(o.created_at) as first_order_date,
                CASE 
                    WHEN MAX(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'active'
                    WHEN MAX(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 'regular'
                    ELSE 'inactive'
                END as customer_status
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id
            WHERE u.id = ? AND u.role = 'customer'
            GROUP BY u.id
        ");
        $stmt->execute([$customer['id']]);
        $customer_details = $stmt->fetch();
        
        if ($customer_details) {
            echo "✅ Customer details query successful<br>";
            echo "📋 Customer: " . htmlspecialchars($customer_details['name']) . "<br>";
            echo "📧 Email: " . htmlspecialchars($customer_details['email']) . "<br>";
            echo "📦 Total Orders: " . $customer_details['total_orders'] . "<br>";
            echo "💰 Total Spent: ₱" . number_format($customer_details['total_spent'], 2) . "<br>";
            echo "📊 Status: " . $customer_details['customer_status'] . "<br>";
            
            // Test direct link
            echo "<br><a href='/bakery-website/admin/customer-details.php?id=" . $customer['id'] . "' target='_blank'>🔗 Test Customer Details Link</a>";
        } else {
            echo "❌ Customer details query failed - no results returned<br>";
        }
    } else {
        echo "⚠️ No customers found in database<br>";
        echo "💡 Please create a test customer account first<br>";
    }
    
    // Test orders table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $result = $stmt->fetch();
    echo "📦 Found " . $result['count'] . " orders in database<br>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If you see customers above, try clicking the test link</li>";
echo "<li>If no customers found, register a test account first</li>";
echo "<li>If database errors, check your database connection</li>";
echo "</ol>";
?>
