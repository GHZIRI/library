<?php
require_once '../core/functions.php';

// Only logged in users
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Only admin can manage users
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Get data from JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id_user = (int) ($data['id_user'] ?? 0);

// Prevent admin from deleting themselves
if ($id_user === currentUser()['id_user']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit();
}

// Delete user and their related data
if ($action === 'delete' && $id_user > 0) {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete user's cart items
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id_user = ?");
        $stmt->execute([$id_user]);

        // Delete user's buy orders
        $stmt = $pdo->prepare("DELETE FROM orders_buy WHERE id_user = ?");
        $stmt->execute([$id_user]);

        // Delete user's rental orders
        $stmt = $pdo->prepare("DELETE FROM orders_rental WHERE id_user = ?");
        $stmt->execute([$id_user]);

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
        $result = $stmt->execute([$id_user]);

        $pdo->commit();

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
