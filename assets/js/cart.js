const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

// Get all divs that have a book id
const bookDivs = document.querySelectorAll('[id^="book-"]');

// Loop through each book and fetch its details
bookDivs.forEach(div => {

    // Get the book id from the div id
    const book_id = div.id.replace("book-", "");

    // Fetch book details from Google Books API
    fetch(`https://www.googleapis.com/books/v1/volumes/${book_id}?key=${API_KEY}`)
        .then(res => res.json())
        .then(book => {
            const info   = book.volumeInfo;
            const title  = info.title || "No title";
            const author = info.authors ? info.authors[0] : "Unknown";
            const cover  = info.imageLinks ? info.imageLinks.thumbnail : "";

            // Show book info inside the div
            div.innerHTML = `
                <img src="${cover}" alt="${title}">
                <h3>${title}</h3>
                <p>${author}</p>
            `;
        });
});

// Remove item from cart
function removeItem(id_cart) {
    fetch('../api/get_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_cart: id_cart })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Remove the div from the page
            document.getElementById("cart-item-" + id_cart).remove();
        }
    });
}