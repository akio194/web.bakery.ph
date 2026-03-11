<?php
/**
 * Test Order Creation API
 * Tests if backend can create orders
 */

header('Content-Type: application/json');
session_start();
require_once '../config/database.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }
    
    // Check if user is admin
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Admin users cannot create orders']);
        exit();
    }
    
    $pdo = getDBConnection();
    
    // Test order data
    $test_order = [
        'user_id' => $_SESSION['user_id'],
        'customer_name' => 'Test Order User',
        'customer_email' => 'test@example.com',
        'customer_phone' => '123-456-7890',
        'delivery_address' => '123 Test Street',
        'total_price' => 99.99,
        'status' => 'pending',
        'payment_method' => 'test',
        'order_notes' => 'Automated test order'
    ];
    
    // Test database tables
    $required_tables = ['users', 'products', 'orders', 'order_items'];
    foreach ($required_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => "Missing table: $table"]);
            exit();
        }
    }
    
    // Create test order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, total_price, status, payment_method, order_notes, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $test_order['user_id'],
        $test_order['customer_name'],
        $test_order['customer_email'],
        $test_order['customer_phone'],
        $test_order['delivery_address'],
        $test_order['total_price'],
        $test_order['status'],
        $test_order['payment_method'],
        $test_order['order_notes']
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Clean up test order immediately
    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order creation test successful',
        'test_order_id' => $order_id
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'General error: ' . $e->getMessage()
    ]);
}
?>
