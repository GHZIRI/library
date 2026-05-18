<?php
require_once '../core/functions.php';


// If not logged in, redirect to login
if(!isLoggedIn()){
    redirect('login.php');
}



// Get book id from URL
$book_id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

// If no id, redirect to catalogue
if(empty($book_id)){
    redirect('catalogue.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Detail — Library</title>
</head>
<body>

    <!-- Book details will appear here -->
    <div id="bookDetail"></div>

    <script>
        const book_id = "<?= $book_id ?>";
        const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

        // Fetch book details from Google Books API
        fetch(`https://www.googleapis.com/books/v1/volumes/${book_id}?key=${API_KEY}`)
            .then(res => res.json())
            .then(book => {
                const info        = book.volumeInfo;
                const title       = info.title || "No title";
                const author      = info.authors ? info.authors[0] : "Unknown";
                const cover       = info.imageLinks ? info.imageLinks.thumbnail : "";
                const description = info.description || "No description available.";
                const buy_price   = 50;
                const rent_price  = 10;

                document.getElementById("bookDetail").innerHTML = `
                    <img src="${cover}" alt="${title}">
                    <h1>${title}</h1>
                    <p><b>Author:</b> ${author}</p>
                    <p>${description}</p>
                    <p><b>Buy Price:</b> ${buy_price} DH</p>
                    <p><b>Rent Price:</b> ${rent_price} DH/month</p>
                    <button onclick="addToCart('${book.id}', 'buy')">Buy</button>
                    <button onclick="addToCart('${book.id}', 'rental')">Rent</button>
                `;
            });

        // Add to cart function
        function addToCart(book_id, type) {
            fetch('../api/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ book_id: book_id, type: type })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Added to cart!");
                } else {
                    alert(data.message);
                }
            });
        }
    </script>

</body>
</html>