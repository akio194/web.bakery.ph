<?php
/**
 * Restore Checkout Backup
 * Restores checkout.php from the latest backup
 */

echo "<h1>💾 Restore Checkout Backup</h1>";

try {
    // Find latest backup file
    $backup_files = glob('checkout.php.backup.*');
    if (empty($backup_files)) {
        echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>❌ No backup files found</h3>";
        echo "<p>No checkout.php.backup.* files found to restore from.</p>";
        echo "</div>";
        exit();
    }
    
    // Sort by modification time (newest first)
    usort($backup_files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    $latest_backup = $backup_files[0];
    
    echo "<h3>📁 Found backup: " . htmlspecialchars($latest_backup) . "</h3>";
    echo "<p>Created: " . date('Y-m-d H:i:s', filemtime($latest_backup)) . "</p>";
    
    // Restore the backup
    if (copy($latest_backup, 'checkout.php')) {
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>✅ Backup restored successfully!</h3>";
        echo "<p>checkout.php has been restored from backup.</p>";
        echo "<p><a href='/bakery-website/checkout.php'>Test Checkout</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>❌ Restore failed</h3>";
        echo "<p>Could not copy backup to checkout.php. Check file permissions.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>❌ Error during restore</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<p><a href='/bakery-website/fix-checkout.php'>← Back to Fix Tool</a></p>";
?>
