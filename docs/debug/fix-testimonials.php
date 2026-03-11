<?php
/**
 * Fix Testimonials Table
 * Creates testimonials table and adds sample data if it doesn't exist
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>🔧 Fixing Testimonials</h2>";
    
    // Check if testimonials table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'testimonials'");
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "<p>📝 Creating testimonials table...</p>";
        
        // Create testimonials table
        $pdo->exec("
            CREATE TABLE testimonials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_name VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                rating INT DEFAULT 5,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        echo "<p style='color: green;'>✅ Testimonials table created</p>";
    } else {
        echo "<p>✅ Testimonials table already exists</p>";
    }
    
    // Check if there are any testimonials
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM testimonials");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        echo "<p>📝 Adding sample testimonials...</p>";
        
        // Add sample testimonials
        $sample_testimonials = [
            [
                'customer_name' => 'Maria Santos',
                'content' => 'The best chocolate cake I have ever tasted! Fresh, moist, and absolutely delicious. Will definitely order again!',
                'rating' => 5
            ],
            [
                'customer_name' => 'John Reyes',
                'content' => 'Their croissants are amazing - so flaky and buttery! Perfect for breakfast with coffee. Highly recommended!',
                'rating' => 5
            ],
            [
                'customer_name' => 'Sarah Chen',
                'content' => 'Ordered a birthday cake for my daughter and it was perfect! Beautiful design and tasted even better than it looked.',
                'rating' => 5
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, content, rating) VALUES (?, ?, ?)");
        
        foreach ($sample_testimonials as $testimonial) {
            $stmt->execute([
                $testimonial['customer_name'],
                $testimonial['content'],
                $testimonial['rating']
            ]);
        }
        
        echo "<p style='color: green;'>✅ Added " . count($sample_testimonials) . " sample testimonials</p>";
    } else {
        echo "<p>✅ Found {$count} existing testimonials</p>";
    }
    
    // Verify the structure
    echo "<h3>🔍 Current Testimonials:</h3>";
    $stmt = $pdo->query("SELECT customer_name, content, rating FROM testimonials WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
    $testimonials = $stmt->fetchAll();
    
    if (!empty($testimonials)) {
        foreach ($testimonials as $index => $testimonial) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "<h4>⭐ {$testimonial['customer_name']} (Rating: {$testimonial['rating']}/5)</h4>";
            echo "<p><em>\"{$testimonial['content']}\"</em></p>";
            echo "</div>";
        }
    }
    
    echo "<h3>🎉 Fixed!</h3>";
    echo "<p>The index.php page should now work without warnings.</p>";
    echo "<p><a href='index.php' style='background: #8B4513; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 View Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: {$e->getMessage()}</p>";
}
?>
