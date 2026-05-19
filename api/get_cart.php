<?php
require_once '../core/functions.php';

// Only logged in users
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = currentUser()['id_user'];
$action  = $_GET['action'] ?? 'get';

// Remove item from cart
if ($action === 'remove') {
    $data    = json_decode(file_get_contents('php://input'), true);
    $id_cart = (int) $data['id_cart'];
    $result  = removeFromCart($id_cart, $user_id);
    echo json_encode(['success' => $result]);

// Get all cart items
} else {
    $cartItems = getCart($user_id);
    echo json_encode(['success' => true, 'items' => $cartItems]);
}