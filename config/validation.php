<?php
/**
 * Validation Helper Functions
 * Provides input validation and sanitization functions
 */

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * @param string $password
 * @return array
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

/**
 * Validate name (letters, spaces, hyphens only)
 * @param string $name
 * @return bool
 */
function isValidName($name) {
    return preg_match('/^[a-zA-Z\s\-]+$/', $name) && strlen(trim($name)) >= 2;
}

/**
 * Validate phone number
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    return preg_match('/^[0-9\-\+\(\)\s]+$/', $phone) && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10;
}

/**
 * Validate address
 * @param string $address
 * @return bool
 */
function isValidAddress($address) {
    return strlen(trim($address)) >= 5;
}

/**
 * Sanitize input
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate product quantity
 * @param int $quantity
 * @return bool
 */
function isValidQuantity($quantity) {
    return is_numeric($quantity) && $quantity > 0 && $quantity <= 100;
}

/**
 * Validate price
 * @param float $price
 * @return bool
 */
function isValidPrice($price) {
    return is_numeric($price) && $price > 0 && $price <= 99999.99;
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Display validation errors
 * @param array $errors
 * @return string
 */
function displayValidationErrors($errors) {
    if (empty($errors)) return '';
    
    $html = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
    $html .= '<strong>Please fix the following errors:</strong>';
    $html .= '<ul class="list-disc list-inside mt-2">';
    
    foreach ($errors as $error) {
        $html .= '<li>' . htmlspecialchars($error) . '</li>';
    }
    
    $html .= '</ul></div>';
    return $html;
}
?>
