<?php
/**
 * API Endpoint: Load Products for Infinite Scroll
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

try {
    $pdo = getDBConnection();
    
    // Get products with pagination
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $products = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'page' => $page,
        'has_more' => count($products) === $limit
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading products'
    ]);
}
?>