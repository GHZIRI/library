<?php
require_once '../core/functions.php';

// Only logged in users can add to cart
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get data sent from JavaScript
$data    = json_decode(file_get_contents('php://input'), true);
$book_id = sanitize($data['book_id']);
$type    = sanitize($data['type']);
$user_id = currentUser()['id_user'];

// Add to cart
$result = addToCart($user_id, $book_id, $type);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Already in cart!']);
}