<?php
require_once '../core/functions.php';

// If not logged in → login
if (!isLoggedIn()) {
    redirect('../views/login.php');
}

// If not admin → catalogue
if (!isAdmin()) {
    redirect('../views/catalogue.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books — Library</title>
</head>
<body>

    <h1>Manage Books</h1>
    <a href="dashboard.php">← Back to Dashboard</a>

    <!-- Search Books -->
    <h2>Search Books</h2>
    <input type="text" id="searchInput" placeholder="Search for a book...">
    <button onclick="searchBooks()">Search</button>

    <!-- Books will appear here -->
    <div id="booksContainer"></div>

    <script>
        const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

        // Load Arabic books by default
        window.onload = function() {
            fetchBooks("روايات عربية");
        }

        // Search books
        function searchBooks() {
            const query = document.getElementById("searchInput").value;
            if (query.trim() === "") return;
            fetchBooks(query);
        }

        // Fetch books from Google Books API
        function fetchBooks(query) {
            const url = `https://www.googleapis.com/books/v1/volumes?q=${query}&langRestrict=ar&maxResults=20&key=${API_KEY}`;
            fetch(url)
                .then(res => res.json())
                .then(data => displayBooks(data.items))
                .catch(err => console.error("Error:", err));
        }

        // Display books as cards
        function displayBooks(books) {
            const container = document.getElementById("booksContainer");
            container.innerHTML = "";

            if (!books) {
                const msg = document.createElement('p');
                msg.textContent = 'No books found.';
                container.appendChild(msg);
                return;
            }

            books.forEach(element => {
                const info    = element.volumeInfo;
                const title   = info.title || "No title";
                const author  = info.authors ? info.authors[0] : "Unknown";
                const cover   = info.imageLinks ? info.imageLinks.thumbnail : "";
                const id      = element.id;

                const bookDiv = document.createElement('div');
                
                if (cover) {
                    const img = document.createElement('img');
                    img.src = cover;
                    img.alt = title;
                    bookDiv.appendChild(img);
                }
                
                const titleEl = document.createElement('h3');
                titleEl.textContent = title;
                bookDiv.appendChild(titleEl);
                
                const authorEl = document.createElement('p');
                authorEl.textContent = author;
                bookDiv.appendChild(authorEl);
                
                const idEl = document.createElement('p');
                idEl.innerHTML = '<b>Book ID:</b> ';
                const idSpan = document.createElement('span');
                idSpan.textContent = id;
                idEl.appendChild(idSpan);
                bookDiv.appendChild(idEl);
                
                const link = document.createElement('a');
                link.href = '../views/book_detail.php?id=' + encodeURIComponent(id);
                link.target = '_blank';
                link.textContent = 'View Detail';
                bookDiv.appendChild(link);
                
                container.appendChild(bookDiv);
            });
        }
    </script>

</body>
</html>