<?php
/**
 * Update Product Image
 * Updates the image field for a product
 */

header('Content-Type: application/json');

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['product_id']) || !isset($input['image'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID and image are required'
        ]);
        exit;
    }
    
    $product_id = (int)$input['product_id'];
    $image = trim($input['image']);
    
    if (empty($image)) {
        echo json_encode([
            'success' => false,
            'message' => 'Image filename cannot be empty'
        ]);
        exit;
    }
    
    // Update product image
    $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
    $result = $stmt->execute([$image, $product_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Product image updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update product image'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
