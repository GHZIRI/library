<?php
header('Content-Type: application/json');
session_start();
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Get books from API or database
?>
