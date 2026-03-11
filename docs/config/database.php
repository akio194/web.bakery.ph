<?php
/**
 * Database Configuration File
 * Handles database connection using PDO
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bakery_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create database connection using PDO
 * @return PDO Returns PDO database connection object
 */
function getDBConnection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log error and show user-friendly message
        error_log('Database connection failed: ' . $e->getMessage());
        die('Sorry, there was a problem connecting to the database. Please try again later.');
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool Returns true if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool Returns true if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /bakery-website/login.php');
        exit();
    }
}

/**
 * Redirect to home if already logged in (for login/register pages)
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /bakery-website/menu.php');
        exit();
    }
}

/**
 * Redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /bakery-website/index.php');
        exit();
    }
}
?>