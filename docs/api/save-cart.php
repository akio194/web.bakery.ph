<?php
/**
 * API Endpoint: Save Cart to Session
 * Used before checkout
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

if (!isset($input['cart']) || !is_array($input['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
    exit();
}

// Save cart to session
$_SESSION['cart'] = $input['cart'];

echo json_encode(['success' => true]);
?>