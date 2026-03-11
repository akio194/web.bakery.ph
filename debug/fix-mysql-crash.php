<?php
/**
 * MySQL Crash Fix Script
 * Provides step-by-step solutions for MySQL shutdown issues
 */

echo "<h1>MySQL Crash Fix Guide</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;}.success{color:green;}.error{color:red;}.warning{color:orange;}.box{background:#f8f9fa;border:1px solid #dee2e6;padding:15px;border-radius:5px;margin:10px 0;}</style>";

echo "<div class='box'>";
echo "<h2>🚨 MySQL Shutdown Detected</h2>";
echo "<p><strong>Error:</strong> MySQL shutdown unexpectedly</p>";
echo "<p><strong>Cause:</strong> Blocked port, missing dependencies, or improper privileges</p>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>🔧 Step-by-Step Fix</h2>";

echo "<h3>Step 1: Check for Port Conflicts</h3>";
echo "<p>MySQL often crashes when another service uses port 3306:</p>";
echo "<ol>";
echo "<li>Open <strong>Command Prompt</strong> as Administrator</li>";
echo "<li>Run: <code>netstat -ano | findstr :3306</code></li>";
echo "<li>If you see results, another service is using port 3306</li>";
echo "<li><strong>Common conflicts:</strong> Skype, other MySQL instances, antivirus software</li>";
echo "</ol>";

echo "<h3>Step 2: Kill Conflicting Processes</h3>";
echo "<p>If port 3306 is in use:</p>";
echo "<ol>";
echo "<li>Find the PID from netstat output</li>";
echo "<li>Run: <code>taskkill /F /PID [PID_NUMBER]</code></li>";
echo "<li>Or use Task Manager → Details → End mysql.exe processes</li>";
echo "</ol>";

echo "<h3>Step 3: Clean MySQL Restart</h3>";
echo "<ol>";
echo "<li>In XAMPP Control Panel: Click <strong>Stop</strong> for MySQL</li>";
echo "<li>Wait <strong>15 seconds</strong> (important!)</li>";
echo "<li>Open Task Manager and verify <strong>no mysql.exe processes</strong></li>";
echo "<li>Click <strong>Start</strong> for MySQL in XAMPP</li>";
echo "</ol>";

echo "<h3>Step 4: Check MySQL Error Log</h3>";
echo "<p>Find the exact cause:</p>";
echo "<ol>";
echo "<li>In XAMPP Control Panel: Click <strong>Logs</strong> next to MySQL</li>";
echo "<li>Look for the <strong>latest error</strong> at the bottom</li>";
echo "<li>Common errors to look for:</li>";
echo "<ul>";
echo "<li><code>errno: 13</code> - Permission denied</li>";
echo "<li><code>errno: 28</code> - No space left on device</li>";
echo "<li><code>Can't start server</code> - Port already in use</li>";
echo "</ul>";
echo "</ol>";

echo "<h3>Step 5: Fix Common Issues</h3>";
echo "<table style='width:100%;border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><td style='padding:10px;border:1px solid #ddd;'><strong>Issue</strong></td><td style='padding:10px;border:1px solid #ddd;'><strong>Solution</strong></td></tr>";
echo "<tr><td style='padding:10px;border:1px solid #ddd;'>Port blocked</td><td style='padding:10px;border:1px solid #ddd;'>Change MySQL port to 3307 in my.ini</td></tr>";
echo "<tr><td style='padding:10px;border:1px solid #ddd;'>Permission denied</td><td style='padding:10px;border:1px solid #ddd;'>Run XAMPP as Administrator</td></tr>";
echo "<tr><td style='padding:10px;border:1px solid #ddd;'>Antivirus blocking</td><td style='padding:10px;border:1px solid #ddd;'>Add exception for MySQL/Port 3306</td></tr>";
echo "<tr><td style='padding:10px;border:1px solid #ddd;'>Corrupted data</td><td style='padding:10px;border:1px solid #ddd;'>Delete mysql/data folder and restart</td></tr>";
echo "</table>";
echo "</ol>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>🚀 Quick Fix Solutions</h2>";

echo "<h3>Solution A: Change MySQL Port</h3>";
echo "<ol>";
echo "<li>Stop MySQL in XAMPP</li>";
echo "<li>Click <strong>Config</strong> → my.ini</li>";
echo "<li>Find: <code>port=3306</code></li>";
echo "<li>Change to: <code>port=3307</code></li>";
echo "<li>Save and restart MySQL</li>";
echo "<li>Update database config to use port 3307</li>";
echo "</ol>";

echo "<h3>Solution B: Use MariaDB Instead</h3>";
echo "<p>XAMPP sometimes includes MariaDB which is more stable:</p>";
echo "<ol>";
echo "<li>In XAMPP, check if MariaDB is available</li>";
echo "<li>Start MariaDB instead of MySQL</li>";
echo "<li>Update database connection if needed</li>";
echo "</ol>";

echo "<h3>Solution C: Reinstall MySQL</h3>";
echo "<ol>";
echo "<li>Backup any important databases</li>";
echo "<li>Stop all XAMPP services</li>";
echo "<li>Uninstall XAMPP MySQL component</li>";
echo "<li>Reinstall XAMPP or just MySQL</li>";
echo "</ol>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>📋 Test Connection After Fix</h2>";
echo "<p>After applying fixes, test the connection:</p>";
echo "<ol>";
echo "<li><a href='create-database.php'>Test Database Creation</a></li>";
echo "<li><a href='setup-products.php'>Add Sample Products</a></li>";
echo "<li><a href='index.php'>Test Homepage</a></li>";
echo "</ol>";
echo "</div>";

echo "<div class='box' style='background:#fff3cd;'>";
echo "<h2>⚠️ Important Notes</h2>";
echo "<ul>";
echo "<li><strong>Always run XAMPP as Administrator</strong></li>";
echo "<li><strong>Wait 15+ seconds</strong> between Stop/Start operations</li>";
echo "<li><strong>Check Windows Event Viewer</strong> for system-level errors</li>";
echo "<li><strong>Temporarily disable antivirus</strong> for testing</li>";
echo "<li><strong>Restart computer</strong> if crashes persist</li>";
echo "</ul>";
echo "</div>";
?>
