<?php
header('Content-Type: application/json');
session_start();
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Add to cart logic
?>
