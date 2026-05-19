<?php
require_once '../core/functions.php';

// Only logged in users
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit();
}

// Get search query from URL
$query = isset($_GET['q']) ? sanitize($_GET['q']) : 'روايات عربية';

// Validate query (prevent empty or very long queries)
if (empty($query) || strlen($query) > 255) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid search query'
    ]);
    exit();
}

// Note: API_KEY should be in environment variable or config file, not hardcoded
// For now, this endpoint is deprecated - books are fetched client-side from JavaScript
// This keeps the API key secure in the frontend

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Use client-side API call instead',
    'books' => []
]);