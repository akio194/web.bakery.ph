<?php
/**
 * Setup Favorites Table
 * Creates the favorites/wishlist table for customer features
 */

require_once 'config/database.php';

$pdo = getDBConnection();

try {
    // Create favorites table
    $sql = "CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_product (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    
    echo "✅ Favorites table created successfully!";
    
} catch (PDOException $e) {
    echo "❌ Error creating favorites table: " . $e->getMessage();
}
?>
