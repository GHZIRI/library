const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

const bookDivs = document.querySelectorAll('[id^="book-"]');

bookDivs.forEach(div => {
    const book_id = div.id.replace("book-", "");

    fetch(`https://www.googleapis.com/books/v1/volumes/${book_id}?key=${API_KEY}`)
        .then(res => res.json())
        .then(book => {
            const info   = book.volumeInfo;
            const title  = info.title || "No title";
            const author = info.authors ? info.authors[0] : "Unknown";
            const cover  = info.imageLinks ? info.imageLinks.thumbnail : "";

            div.innerHTML = `
                <img src="${cover}" alt="${title}">
                <h3>${title}</h3>
                <p>${author}</p>
            `;
        });
});

function removeItem(id_cart) {
    fetch('../api/get_cart.php?action=remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_cart: id_cart })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById("cart-item-" + id_cart).remove();
        }
    });
}