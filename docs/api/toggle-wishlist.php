<?php
/**
 * Toggle Wishlist API
 * Adds or removes items from user's wishlist
 */

header('Content-Type: application/json');
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage wishlist']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? 0;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

try {
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    // Check if item is already in wishlist
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist']);
    } else {
        // Add to wishlist
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
