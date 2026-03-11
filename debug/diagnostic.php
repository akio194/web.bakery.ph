<?php
/**
 * Diagnostic Page
 * Checks for common issues preventing website from loading
 */

echo "<h1>Bakery Website Diagnostic</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;}.ok{color:green;}.error{color:red;}.warning{color:orange;}</style>";

// Check PHP version
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . PHP_VERSION . " " . (version_compare(PHP_VERSION, '7.4.0', '>=') ? "<span class='ok'>✅</span>" : "<span class='error'>❌ Update required</span>") . "</p>";

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
echo "<h2>PHP Extensions</h2>";
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>$ext: " . ($loaded ? "<span class='ok'>✅ Loaded</span>" : "<span class='error'>❌ Missing</span>") . "</p>";
}

// Check database connection
echo "<h2>Database Connection</h2>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "<p class='ok'>✅ Database connection successful</p>";
    
    // Check if tables exist
    $tables = ['users', 'products', 'orders', 'order_items', 'testimonials'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
        $stmt->execute();
        $exists = $stmt->rowCount() > 0;
        echo "<p>Table '$table': " . ($exists ? "<span class='ok'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>") . "</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check file permissions
echo "<h2>File Permissions</h2>";
$paths = [
    'config/database.php' => 'Database Config',
    'includes/header.php' => 'Header Include',
    'includes/footer.php' => 'Footer Include',
    'assets/images/' => 'Images Directory'
];

foreach ($paths as $path => $description) {
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);
    echo "<p>$description: " . 
        ($exists ? "<span class='ok'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>") . " " .
        ($readable ? "<span class='ok'>✅ Readable</span>" : "<span class='error'>❌ Not readable</span>") . "</p>";
}

// Check .htaccess (if Apache)
echo "<h2>Web Server</h2>";
if (function_exists('apache_get_version')) {
    echo "<p>Server: Apache " . apache_get_version() . " <span class='ok'>✅</span></p>";
} else {
    echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . " <span class='ok'>✅</span></p>";
}

// Check error reporting
echo "<h2>Error Reporting</h2>";
echo "<p>Error Reporting: " . (ini_get('display_errors') ? "<span class='warning'>⚠️ ON</span>" : "<span class='ok'>✅ OFF</span>") . "</p>";
echo "<p>Error Log: " . (ini_get('error_log') ?: "<span class='warning'>⚠️ Not set</span>") . "</p>";

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='setup-database.php'>1. Setup Database Tables</a></li>";
echo "<li><a href='setup-products.php'>2. Add Sample Products</a></li>";
echo "<li><a href='index.php'>3. Visit Homepage</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>If you're still having issues:</strong></p>";
echo "<ul>";
echo "<li>Check XAMPP control panel - Apache and MySQL should be running</li>";
echo "<li>Make sure you're accessing via http://localhost/bakery-website/</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Verify file permissions in XAMPP/htdocs/bakery-website/</li>";
echo "</ul>";
?>
