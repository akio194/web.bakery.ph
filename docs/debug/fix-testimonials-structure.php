<?php
/**
 * Fix Testimonials Table Structure
 * Checks current structure and fixes missing columns
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>🔧 Fixing Testimonials Table Structure</h2>";
    
    // Check if testimonials table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'testimonials'");
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "<p>📝 Creating testimonials table from scratch...</p>";
        
        // Drop and recreate table
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
        
        echo "<p style='color: green;'>✅ Testimonials table created with correct structure</p>";
    } else {
        echo "<p>✅ Testimonials table exists, checking structure...</p>";
        
        // Check current columns
        $stmt = $pdo->query("DESCRIBE testimonials");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>🔍 Current columns:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>{$column}</li>";
        }
        echo "</ul>";
        
        // Add missing columns
        $needed_columns = ['customer_name', 'content', 'rating', 'status'];
        $missing_columns = [];
        
        foreach ($needed_columns as $col) {
            if (!in_array($col, $columns)) {
                $missing_columns[] = $col;
            }
        }
        
        if (!empty($missing_columns)) {
            echo "<h3>🔧 Adding missing columns...</h3>";
            
            foreach ($missing_columns as $col) {
                switch ($col) {
                    case 'customer_name':
                        $pdo->exec("ALTER TABLE testimonials ADD COLUMN customer_name VARCHAR(255) NOT NULL DEFAULT 'Anonymous Customer'");
                        echo "<p style='color: green;'>✅ Added customer_name column</p>";
                        break;
                    case 'content':
                        $pdo->exec("ALTER TABLE testimonials ADD COLUMN content TEXT NOT NULL DEFAULT 'Great products and excellent service!'");
                        echo "<p style='color: green;'>✅ Added content column</p>";
                        break;
                    case 'rating':
                        $pdo->exec("ALTER TABLE testimonials ADD COLUMN rating INT DEFAULT 5");
                        echo "<p style='color: green;'>✅ Added rating column</p>";
                        break;
                    case 'status':
                        $pdo->exec("ALTER TABLE testimonials ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
                        echo "<p style='color: green;'>✅ Added status column</p>";
                        break;
                }
            }
        } else {
            echo "<p style='color: green;'>✅ All required columns exist</p>";
        }
    }
    
    // Check if there's any data and update if needed
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM testimonials");
    $count = $stmt->fetch()['count'];
    
    echo "<h3>📊 Current data:</h3>";
    echo "<p>Total testimonials: {$count}</p>";
    
    if ($count > 0) {
        // Show a sample of current data
        $stmt = $pdo->query("SELECT * FROM testimonials LIMIT 3");
        $testimonials = $stmt->fetchAll();
        
        echo "<h4>Sample data:</h4>";
        foreach ($testimonials as $testimonial) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "<strong>ID:</strong> {$testimonial['id']}<br>";
            foreach ($testimonial as $key => $value) {
                if ($key !== 'id') {
                    echo "<strong>{$key}:</strong> " . (is_string($value) ? htmlspecialchars($value) : $value) . "<br>";
                }
            }
            echo "</div>";
        }
    }
    
    // Add sample data if table is empty
    if ($count == 0) {
        echo "<h3>📝 Adding sample testimonials...</h3>";
        
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
    }
    
    echo "<h3>🎉 Fixed!</h3>";
    echo "<p>The testimonials table now has the correct structure with customer_name and content columns.</p>";
    echo "<p><a href='index.php' style='background: #8B4513; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Test Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: {$e->getMessage()}</p>";
}
?>
