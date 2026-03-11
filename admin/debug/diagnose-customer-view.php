<?php
/**
 * Diagnose Customer View Issue
 * Step-by-step debugging for customer view functionality
 */

require_once '../config/database.php';

echo "<h1>🔍 Customer View Diagnosis</h1>";
echo "<h2>Step 1: Basic Checks</h2>";

try {
    // Test 1: Database Connection
    $pdo = getDBConnection();
    echo "✅ <strong>Database Connection:</strong> Working<br>";
    
    // Test 2: Check if customers.php exists and is accessible
    echo "✅ <strong>Current File:</strong> " . __FILE__ . "<br>";
    
    // Test 3: Check if customer-details.php exists
    if (file_exists('../admin/customer-details.php')) {
        echo "✅ <strong>customer-details.php:</strong> File exists<br>";
    } else {
        echo "❌ <strong>customer-details.php:</strong> File missing<br>";
    }
    
    echo "<h2>Step 2: Database Structure Check</h2>";
    
    // Test 4: Check tables exist
    $tables_to_check = ['users', 'orders', 'order_items'];
    foreach ($tables_to_check as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ <strong>Table '$table':</strong> Exists<br>";
        } else {
            echo "❌ <strong>Table '$table':</strong> Missing<br>";
        }
    }
    
    // Test 5: Check for customers
    echo "<h2>Step 3: Customer Data Check</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $result = $stmt->fetch();
    $customer_count = $result['count'];
    
    echo "📊 <strong>Total Customers:</strong> $customer_count<br>";
    
    if ($customer_count > 0) {
        echo "✅ <strong>Status:</strong> Customers found<br>";
        
        // Show first few customers
        $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'customer' LIMIT 5");
        $customers = $stmt->fetchAll();
        
        echo "<h3>Sample Customers:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Test Link</th></tr>";
        
        foreach ($customers as $customer) {
            echo "<tr>";
            echo "<td>" . $customer['id'] . "</td>";
            echo "<td>" . htmlspecialchars($customer['name']) . "</td>";
            echo "<td>" . htmlspecialchars($customer['email']) . "</td>";
            echo "<td><a href='/bakery-website/admin/customer-details.php?id=" . $customer['id'] . "' target='_blank'>🔗 Test View</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test 6: Test customer details query for first customer
        $first_customer = $customers[0];
        echo "<h2>Step 4: Test Customer Details Query</h2>";
        echo "<p>Testing with customer: <strong>" . htmlspecialchars($first_customer['name']) . "</strong> (ID: " . $first_customer['id'] . ")</p>";
        
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
            $stmt->execute([$first_customer['id']]);
            $customer_details = $stmt->fetch();
            
            if ($customer_details) {
                echo "✅ <strong>Query Success:</strong> Customer details retrieved<br>";
                echo "<pre style='background: #f0fdf4; padding: 1rem; border-radius: 0.5rem;'>";
                print_r($customer_details);
                echo "</pre>";
                
                // Test 7: Test direct file access
                echo "<h2>Step 5: Test Direct File Access</h2>";
                $test_url = "http://localhost/bakery-website/admin/customer-details.php?id=" . $first_customer['id'];
                echo "<p>Try accessing: <a href='$test_url' target='_blank'>$test_url</a></p>";
                
                // Check if file has syntax errors
                echo "<h2>Step 6: Check File Syntax</h2>";
                $file_content = file_get_contents('../admin/customer-details.php');
                if (strpos($file_content, '<?php') === 0) {
                    echo "✅ <strong>PHP Syntax:</strong> File starts with PHP tag<br>";
                } else {
                    echo "❌ <strong>PHP Syntax:</strong> File doesn't start with PHP tag<br>";
                }
                
                // Check for common syntax errors
                $syntax_errors = [
                    'Parse error',
                    'Fatal error',
                    'syntax error',
                    'unexpected',
                    'T_ENCAPSED_AND_WHITESPACE'
                ];
                
                foreach ($syntax_errors as $error) {
                    if (strpos($file_content, $error) !== false) {
                        echo "⚠️ <strong>Possible Syntax Issue:</strong> Contains '$error'<br>";
                    }
                }
                
            } else {
                echo "❌ <strong>Query Failed:</strong> No customer details returned<br>";
            }
            
        } catch (PDOException $e) {
            echo "❌ <strong>Query Error:</strong> " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "❌ <strong>Status:</strong> No customers found<br>";
        echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>🛠️ Solution:</h3>";
        echo "<p>You need to create customer accounts first. Options:</p>";
        echo "<ol>";
        echo "<li><a href='/bakery-website/register.php'>Register a customer account</a></li>";
        echo "<li><a href='/bakery-website/add-test-data.php'>Run test data script</a></li>";
        echo "<li><a href='/bakery-website/create-missing-tables.php'>Create missing tables</a></li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "❌ <strong>Database Error:</strong> " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "❌ <strong>General Error:</strong> " . $e->getMessage() . "<br>";
}

echo "<h2>🔧 Quick Fix Steps</h2>";
echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<ol>";
echo "<li><strong>Run this diagnosis:</strong> It shows exactly what's wrong</li>";
echo "<li><strong>Create missing tables:</strong> <a href='/bakery-website/create-missing-tables.php'>Click here</a></li>";
echo "<li><strong>Add test data:</strong> <a href='/bakery-website/add-test-data.php'>Click here</a></li>";
echo "<li><strong>Test customer view:</strong> <a href='/bakery-website/admin/customers.php'>Customers Page</a></li>";
echo "</ol>";
echo "</div>";
?>
