<?php
require_once '../core/functions.php';

// Only logged in users
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Only admin can update status
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Get data from JavaScript
$data   = json_decode(file_get_contents('php://input'), true);
$type   = $data['type'];
$id     = (int) $data['id'];
$status = sanitize($data['status']);

// Update status based on order type
if ($type === 'buy') {
    $stmt = $pdo->prepare("UPDATE orders_buy SET status = ? WHERE id_buy = ?");
    $result = $stmt->execute([$status, $id]);

} else if ($type === 'rental') {
    $stmt = $pdo->prepare("UPDATE orders_rental SET status = ? WHERE id_rental = ?");
    $result = $stmt->execute([$status, $id]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit();
}

// Return result
if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Something went wrong']);
}