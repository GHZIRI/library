<?php
require_once '../core/functions.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Only logged in users
if (!isLoggedIn()) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in. Please login first.'
    ]);
    exit();
}

// Get user ID
$user = currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User session expired'
    ]);
    exit();
}

$user_id = $user['id_user'];
$action  = $_GET['action'] ?? 'get';

// ── Remove item from cart
if ($action === 'remove') {
    // Get request body
    $input = file_get_contents('php://input');
    if (empty($input)) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'message' => 'No data provided'
        ]);
        exit();
    }

    $data    = json_decode($input, true);
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON format'
        ]);
        exit();
    }

    // Validate id_cart
    $id_cart = isset($data['id_cart']) ? (int) $data['id_cart'] : null;
    if ($id_cart <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid cart item ID'
        ]);
        exit();
    }

    // Remove from cart
    $result = removeFromCart($id_cart, $user_id);
    
    if ($result) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Item removed']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
    }
    exit();
}

// ── Get all cart items
if ($action === 'get') {
    try {
        $cartItems = getCart($user_id);
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'items' => $cartItems,
            'count' => count($cartItems)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving cart'
        ]);
    }
    exit();
}

// Invalid action
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action specified'
]);