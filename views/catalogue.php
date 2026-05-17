<?php
require_once '../core/functions.php';

// If not logged in, redirect to login
if (!isLoggedIn()) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
      <!-- Search Bar -->
    <input type="text"id="searchInput" placeholder="Search for a book...">
    <button onclick="searchBooks()">Search</button>
      <!-- Books will appear here -->
       <div id="booksContainer"></div>

        <script src="../assets/js/main.js"></script>
</body>
</html>