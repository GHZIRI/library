<?php
// =====================================================
// Load database connection and start session
// =====================================================
require_once 'db.php';
session_start();

// =====================================================
// AUTH FUNCTIONS
// =====================================================

// Check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Get the current logged-in user data, returns null if not logged in
function currentUser() {
    return $_SESSION['user'] ?? null;
}

// Check if the current user is an admin
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Destroy the session and redirect to login page
function logout() {
    session_destroy();
    redirect('../views/login.php');
}

// Redirect the user to a given URL and stop code execution
function redirect($url) {
    header("Location: $url");
    exit();
}

// Sanitize user input to prevent XSS attacks
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// =====================================================
// CART FUNCTIONS
// =====================================================

// Add a book to the cart, returns false if already exists
function addToCart($user_id, $book_id, $type, $rental_months = null, $quantity = 1) {
    global $pdo;
    // Check if the book is already in the cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE id_user = ? AND book_id = ? AND type = ?");
    $stmt->execute([$user_id, $book_id, $type]);
    if ($stmt->fetch()) return false;

    // Insert the book into the cart
    $stmt = $pdo->prepare("INSERT INTO cart (id_user, book_id, type, rental_months, quantity) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $book_id, $type, $rental_months, $quantity]);
}

// Get all cart items for a specific user
function getCart($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE id_user = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Remove a specific item from the cart
function removeFromCart($id_cart, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id_cart = ? AND id_user = ?");
    return $stmt->execute([$id_cart, $user_id]);
}

// Clear all items from the cart for a specific user
function clearCart($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id_user = ?");
    return $stmt->execute([$user_id]);
}

// =====================================================
// ORDER FUNCTIONS
// =====================================================

// Create a new buy order and save it to the database
function createBuyOrder($id_user, $book_id, $name, $city, $phone, $quantity, $total_price) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO orders_buy (id_user, book_id, name_buy, city, phone_number, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$id_user, $book_id, $name, $city, $phone, $quantity, $total_price]);
}

// Create a new rental order and save it to the database
function createRentalOrder($id_user, $book_id, $name, $city, $phone, $rental_months, $total_price, $start_date, $end_date) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO orders_rental (id_user, book_id, name_rental, city, phone_number, rental_months, total_price, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$id_user, $book_id, $name, $city, $phone, $rental_months, $total_price, $start_date, $end_date]);
}

// Get all orders (buy + rental) for a specific user
function getUserOrders($user_id) {
    global $pdo;
    // Get all buy orders
    $buy = $pdo->prepare("SELECT *, 'buy' as order_type FROM orders_buy WHERE id_user = ? ORDER BY created_at DESC");
    $buy->execute([$user_id]);

    // Get all rental orders
    $rent = $pdo->prepare("SELECT *, 'rental' as order_type FROM orders_rental WHERE id_user = ? ORDER BY created_at DESC");
    $rent->execute([$user_id]);

    return [
        'buy'    => $buy->fetchAll(PDO::FETCH_ASSOC),
        'rental' => $rent->fetchAll(PDO::FETCH_ASSOC)
    ];
}