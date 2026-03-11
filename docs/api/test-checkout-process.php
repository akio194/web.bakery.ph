<?php
/**
 * Test Checkout Process API
 * Tests the complete checkout process
 */

header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../config/validation.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }
    
    // Check if user is admin
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Admin users cannot checkout']);
        exit();
    }
    
    // Get POST data
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit();
    }
    
    // Get cart data
    $cart_data = json_decode($data['cart_data'], true) ?? [];
    
    if (empty($cart_data)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit();
    }
    
    $pdo = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Test order creation without actually creating
    $total_price = 0;
    foreach ($cart_data as $item) {
        $total_price += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart data received successfully',
        'cart_items' => count($cart_data),
        'user_id' => $user_id,
        'debug_info' => [
            'cart_data_received' => !empty($data['cart_data']),
            'cart_items_count' => count($cart_data),
            'user_authenticated' => true,
            'user_role' => $_SESSION['user_role'] ?? 'customer'
        ],
        'order_test' => [
            'total_price' => $total_price,
            'item_count' => count($cart_data),
            'can_create_order' => $total_price > 0
        ]
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
