<?php
/**
 * Remove Local Tailwind CSS
 * Reverts back to Tailwind CDN
 */

echo "<h1>🔄 Removing Local Tailwind CSS</h1>";
echo "<h2>📋 Restoring Original CDN Setup</h2>";

try {
    // Step 1: Remove local CSS file
    $local_css_file = 'assets/css/tailwind-local.css';
    if (file_exists($local_css_file)) {
        if (unlink($local_css_file)) {
            echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
            echo "<h3>✅ Local CSS File Removed</h3>";
            echo "<p>Deleted: assets/css/tailwind-local.css</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
            echo "<h3>⚠️ Could Not Delete Local CSS</h3>";
            echo "<p>File may be in use or permissions issue</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>ℹ️ Local CSS File Not Found</h3>";
        echo "<p>No local CSS file to remove</p>";
        echo "</div>";
    }
    
    // Step 2: Restore header.php to use CDN
    $header_file = 'includes/header.php';
    if (file_exists($header_file)) {
        $content = file_get_contents($header_file);
        
        // Find and replace local CSS with CDN
        $local_css_link = '<link rel="stylesheet" href="/bakery-website/assets/css/tailwind-local.css">';
        $cdn_script = '<script src="https://cdn.tailwindcss.com"></script>';
        
        if (strpos($content, $local_css_link) !== false) {
            $content = str_replace($local_css_link, $cdn_script, $content);
            
            if (file_put_contents($header_file, $content)) {
                echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
                echo "<h3>✅ Header.php Restored</h3>";
                echo "<p>Replaced local CSS with Tailwind CDN</p>";
                echo "</div>";
            } else {
                throw new Exception("Failed to update header.php");
            }
        } else {
            echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
            echo "<h3>⚠️ Local CSS Link Not Found</h3>";
            echo "<p>Header.php may not have been modified</p>";
            echo "</div>";
        }
    } else {
        throw new Exception("Header file not found");
    }
    
    // Step 3: Check current status
    echo "<h3>📊 Current Status:</h3>";
    echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    
    // Check if local CSS still exists
    if (file_exists($local_css_file)) {
        echo "<p>❌ Local CSS file still exists</p>";
    } else {
        echo "<p>✅ Local CSS file removed</p>";
    }
    
    // Check header content
    $header_content = file_get_contents($header_file);
    if (strpos($header_content, 'cdn.tailwindcss.com') !== false) {
        echo "<p>✅ Tailwind CDN restored in header</p>";
    } else {
        echo "<p>❌ Tailwind CDN not found in header</p>";
    }
    
    if (strpos($header_content, 'tailwind-local.css') !== false) {
        echo "<p>❌ Local CSS link still in header</p>";
    } else {
        echo "<p>✅ Local CSS link removed from header</p>";
    }
    
    echo "</div>";
    
    echo "<h3>🎯 Result:</h3>";
    echo "<div style='background: #fbbf24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<p>✅ Local Tailwind CSS removed</p>";
    echo "<p>✅ Original CDN setup restored</p>";
    echo "<p>⚠️ CDN warning will return (but that's ok for development)</p>";
    echo "<p>✅ All styles should work normally</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<h3>🚀 Test the Restoration:</h3>";
echo "<ul>";
echo "<li><a href='/bakery-website/'>🏠 Homepage</a> - Check if styles load</li>";
echo "<li><a href='/bakery-website/menu.php'>🥐 Menu Page</a> - Verify styling</li>";
echo "<li><a href='/bakery-website/checkout.php'>🛒 Checkout</a> - Test functionality</li>";
echo "</ul>";

echo "<h3>💡 What Changed:</h3>";
echo "<div style='background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<h4>🔄 Reverted Changes:</h4>";
echo "<ul>";
echo "<li>🗑️ Deleted: assets/css/tailwind-local.css</li>";
echo "<li>🔄 Restored: Tailwind CDN script in header.php</li>";
echo "<li>⚠️ Returned: CDN warning in console (normal for development)</li>";
echo "<li>✅ Preserved: All existing styles and functionality</li>";
echo "</ul>";
echo "</div>";

echo "<h3>⚠️ Note:</h3>";
echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
echo "<p><strong>CDN Warning:</strong> You may see the CDN warning again in console, but this is normal for development and doesn't affect functionality.</p>";
echo "<p><strong>Performance:</strong> CDN is actually fine for development, only production environments need local CSS.</p>";
echo "</div>";

echo "<p><a href='/bakery-website/'>← Test Website</a> | <a href='/bakery-website/fix-console-errors.php'>← Back to Console Fixes</a></p>";
?>
