<?php
// Helper Functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (isLoggedIn()) {
        global $db;
        return $db->fetch("SELECT * FROM users WHERE id = " . $_SESSION['user_id']);
    }
    return null;
}

// Format price
function formatPrice($price) {
    return number_format($price, 2, '.', '') . ' MAD';
}

// Sanitize input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Flash message
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// Generate unique order number
function generateOrderNumber() {
    return 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
}

// Format date
function formatDate($date, $format = 'Y-m-d H:i') {
    return date($format, strtotime($date));
}

// Check if email exists
function emailExists($email) {
    global $db;
    $result = $db->fetch("SELECT id FROM users WHERE email = '" . $db->escape($email) . "'");
    return $result ? true : false;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Get cart count
function getCartCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    global $db;
    $result = $db->fetch("SELECT COUNT(*) as count FROM cart WHERE user_id = " . $_SESSION['user_id']);
    return $result['count'] ?? 0;
}

// Get cart total
function getCartTotal() {
    if (!isLoggedIn()) {
        return 0;
    }
    global $db;
    $result = $db->fetch("
        SELECT SUM(CASE 
            WHEN cart.type = 'buy' THEN books.buy_price * cart.quantity
            ELSE books.rent_price * cart.quantity
        END) as total
        FROM cart
        JOIN books ON cart.book_id = books.id
        WHERE cart.user_id = " . $_SESSION['user_id']
    );
    return $result['total'] ?? 0;
}
?>
