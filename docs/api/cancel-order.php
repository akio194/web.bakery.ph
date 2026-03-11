<?php
/**
 * API Endpoint: Cancel Order
 */

require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Check if order belongs to user and is pending
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found or cannot be cancelled']);
        exit();
    }
    
    // Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error cancelling order']);
}
?>