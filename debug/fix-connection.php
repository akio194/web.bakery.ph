<?php
/**
 * MySQL Connection Fix Script
 * Diagnoses and fixes MySQL connection issues
 */

echo "<h1>MySQL Connection Diagnostic & Fix</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;}.success{color:green;}.error{color:red;}.warning{color:orange;}</style>";

// Test different MySQL connection methods
echo "<h2>Testing Connection Methods</h2>";

$methods = [
    ['host' => 'localhost', 'port' => '3306', 'socket' => ''],
    ['host' => '127.0.0.1', 'port' => '3306', 'socket' => ''],
    ['host' => 'localhost', 'port' => '', 'socket' => '/xampp/mysql/mysql.sock'],
    ['host' => 'localhost', 'port' => '3307', 'socket' => ''], // Alternative port
];

foreach ($methods as $i => $method) {
    try {
        $dsn = 'mysql:host=' . $method['host'] . 
                (empty($method['port']) ? '' : ';port=' . $method['port']) .
                (empty($method['socket']) ? '' : ';unix_socket=' . $method['socket']);
        
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        echo "<p class='success'>✅ Method " . ($i + 1) . ": Connected via " . $method['host'] . 
             (empty($method['port']) ? '' : ':' . $method['port']) . "</p>";
        
        // If connected, try to create database
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS bakery_db");
            echo "<p class='success'>✅ Database 'bakery_db' created/verified</p>";
            
            // Test switching to the database
            $pdo->exec("USE bakery_db");
            echo "<p class='success'>✅ Successfully switched to bakery_db</p>";
            
            echo "<h2 class='success'>🎉 Connection Fixed! Database Ready!</h2>";
            echo "<p><a href='setup-products.php'>Add Sample Products</a> | <a href='index.php'>Visit Homepage</a></p>";
            exit;
            
        } catch (Exception $e) {
            echo "<p class='warning'>⚠️ Connected but database creation failed: " . $e->getMessage() . "</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Method " . ($i + 1) . " failed: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>XAMPP Troubleshooting Steps</h2>";
echo "<div style='background:#f5f5f5;padding:15px;border-radius:5px;'>";
echo "<h3>1. Check XAMPP Control Panel</h3>";
echo "<p>Open XAMPP Control Panel and verify:</p>";
echo "<ul>";
echo "<li>✅ Apache is running (green indicator)</li>";
echo "<li>✅ MySQL is running (green indicator)</li>";
echo "<li>✅ Both show 'Running' in the status column</li>";
echo "</ul>";

echo "<h3>2. Check MySQL Port</h3>";
echo "<p>MySQL might be running on a different port:</p>";
echo "<ul>";
echo "<li>Open XAMPP Control Panel</li>";
echo "<li>Click 'Config' next to MySQL</li>";
echo "<li>Look for 'port=3306' in the my.ini file</li>";
echo "<li>Note the actual port number</li>";
echo "</ul>";

echo "<h3>3. Check for Port Conflicts</h3>";
echo "<p>Another service might be using port 3306:</p>";
echo "<ul>";
echo "<li>Open Command Prompt as Administrator</li>";
echo "<li>Run: <code>netstat -ano | findstr :3306</code></li>";
echo "<li>If nothing shows, port is free</li>";
echo "<li>If something shows, stop that service or change MySQL port</li>";
echo "</ul>";

echo "<h3>4. Restart MySQL Service</h3>";
echo "<ul>";
echo "<li>In XAMPP Control Panel, click 'Stop' next to MySQL</li>";
echo "<li>Wait 10 seconds</li>";
echo "<li>Click 'Start' next to MySQL</li>";
echo "<li>Check for error messages in XAMPP log</li>";
echo "</ul>";

echo "<h3>5. Alternative: Use Different Port</h3>";
echo "<p>If port 3306 is blocked, change MySQL to port 3307:</p>";
echo "<ul>";
echo "<li>Stop MySQL in XAMPP</li>";
echo "<li>Edit my.ini (Config next to MySQL)</li>";
echo "<li>Change 'port=3306' to 'port=3307'</li>";
echo "<li>Save and restart MySQL</li>";
echo "<li>Update database config to use port 3307</li>";
echo "</ul>";

echo "<h3>6. Check Windows Firewall</h3>";
echo "<ul>";
echo "<li>Windows Defender might block MySQL</li>";
echo "<li>Add exception for port 3306 in Windows Firewall</li>";
echo "<li>Or temporarily disable firewall for testing</li>";
echo "</ul>";

echo "</div>";

echo "<h2>Quick Test Commands</h2>";
echo "<p>Run these in Command Prompt to test:</p>";
echo "<pre>";
echo "# Test if MySQL is listening
netstat -ano | findstr :3306

# Test MySQL connection
mysql -u root -p

# Test with specific port
mysql -u root -p -P 3306 -h localhost
</pre>";

echo "<h2>Manual Database Creation</h2>";
echo "<p>If automatic creation fails, create manually:</p>";
echo "<ol>";
echo "<li>Open phpMyAdmin: http://localhost/phpmyadmin/</li>";
echo "<li>Click 'New' database</li>";
echo "<li>Enter name: <strong>bakery_db</strong></li>";
echo "<li>Click 'Create'</li>";
echo "<li>Then visit: <a href='setup-database.php'>setup-database.php</a></li>";
echo "</ol>";
?>
