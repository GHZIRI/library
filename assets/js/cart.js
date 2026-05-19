// ── Google Books API Configuration ──────────────────────────────────────────
const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

// ── Initialize Cart on DOM Ready ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadCartBooks();
});

// ── Load and Display Cart Books ─────────────────────────────────────────────
const loadCartBooks = () => {
    const bookDivs = document.querySelectorAll('[id^="book-"]');

    if (bookDivs.length === 0) return;

    bookDivs.forEach(div => {
        const bookId = div.id.replace("book-", "");
        fetchBookDetails(bookId, div);
    });
};

// ── Fetch Book Details from Google Books API ───────────────────────────────
const fetchBookDetails = async (bookId, containerElement) => {
    try {
        const url = `https://www.googleapis.com/books/v1/volumes/${bookId}?key=${API_KEY}`;
        const response = await fetch(url);
        
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        
        const book = await response.json();
        displayBookInCart(book, containerElement);
    } catch (error) {
        console.error("Error fetching book:", error);
        containerElement.innerHTML = '<p class="error-text">Failed to load book details</p>';
    }
};

// ── Display Book Details in Cart ────────────────────────────────────────────
const displayBookInCart = (book, containerElement) => {
    const info = book.volumeInfo;
    const title = info.title || "No title";
    const author = info.authors?.join(", ") || "Unknown";
    const cover = info.imageLinks?.thumbnail || "";

    containerElement.innerHTML = `
        ${cover ? `<img src="${cover}" alt="${title}" style="width:100%;border-radius:6px;">` : ''}
        <h3>${title}</h3>
        <p>${author}</p>
    `;
};

// ── Remove Item from Cart ───────────────────────────────────────────────────
const removeItem = async (idCart) => {
    try {
        const response = await fetch('../api/get_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_cart: idCart })
        });

        const data = await response.json();

        if (data.success) {
            // Remove the item div with smooth animation
            const itemElement = document.getElementById(`cart-item-${idCart}`);
            if (itemElement) {
                itemElement.style.opacity = '0';
                itemElement.style.transition = 'opacity 0.3s ease';
                
                setTimeout(() => {
                    itemElement.remove();
                    showNotification("Item removed from cart", "success");
                }, 300);
            }
        } else {
            showNotification("Failed to remove item", "error");
        }
    } catch (error) {
        console.error("Error removing item:", error);
        showNotification("Error removing item", "error");
    }
};

// ── Show Notification ───────────────────────────────────────────────────────
const showNotification = (message, type = "info") => {
    const alertBox = document.getElementById("alertBox");
    if (!alertBox) return;

    const alertClass = {
        success: "alert-success",
        error: "alert-error",
        info: "alert-info"
    }[type] || "alert-info";

    alertBox.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
    
    setTimeout(() => {
        alertBox.innerHTML = "";
    }, 3000);
};