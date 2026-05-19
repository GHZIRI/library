<?php
require_once '../core/functions.php';


// Only logged in users
if(!isLoggedIn()){
    echo json_encode([
        'success' => false,
        'message' =>'Not logged in'
        ]);
        exit();
}



// Get search query from URL
$query = isset($_GET['q']) ? sanitize($_GET['q']): 'روايات عربية';




// Build Google Books API URL
$API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";
$url = "https://www.googleapis.com/books/v1/volumes?q={$query}&langRestrict=ar&maxResults=20&key={$API_KEY}";

$response = file_get_contents($url);
$data     = json_decode($response, true);

// Return books as JSON
echo json_encode([
    'success' => true,
    'books'   => $data['items'] ?? []
]);