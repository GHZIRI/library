// ── Google Books API Configuration ──────────────────────────────────────────
const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

// ── Initialize App on DOM Ready ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    fetchBooks("روايات عربية");
    setupEventListeners();
});

// ── Setup Event Listeners ───────────────────────────────────────────────────
const setupEventListeners = () => {
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("keypress", (event) => {
            if (event.key === "Enter") {
                event.preventDefault();
                searchBooks();
            }
        });
    }
};

// ── Search Books by Query ───────────────────────────────────────────────────
const searchBooks = () => {
    const query = document.getElementById("searchInput")?.value?.trim();
    if (!query) {
        showAlert("Please enter a search query", "error");
        return;
    }
    fetchBooks(query);
};

// ── Fetch Books from Google Books API ───────────────────────────────────────
const fetchBooks = async (query) => {
    try {
        const container = document.getElementById("booksContainer");
        if (!container) return;

        // Show loading state
        container.innerHTML = '<div class="loading">Loading books...</div>';

        const url = `https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(query)}&langRestrict=ar&maxResults=20&key=${API_KEY}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        
        const data = await response.json();
        displayBooks(data.items || []);
    } catch (error) {
        console.error("Error fetching books:", error);
        const container = document.getElementById("booksContainer");
        if (container) {
            container.innerHTML = '<div class="error-state">❌ Failed to load books. Please try again.</div>';
        }
    }
};

// ── Display Books as Cards ──────────────────────────────────────────────────
const displayBooks = (books) => {
    const container = document.getElementById("booksContainer");
    if (!container) return;

    container.innerHTML = "";

    if (!books || books.length === 0) {
        container.innerHTML = '<div class="empty-state">📚 No books found. Try a different search.</div>';
        return;
    }

    const booksHTML = books
        .map(element => {
            const info = element.volumeInfo;
            const title = info.title || "No title";
            const author = info.authors?.join(", ") || "Unknown author";
            const cover = info.imageLinks?.thumbnail || "../assets/images/no-cover.jpg";
            const id = element.id;

            return `
                <div class="book-card">
                    <img src="${cover}" alt="${title}" loading="lazy">
                    <h3 title="${title}">${title}</h3>
                    <p>${author}</p>
                    <a href="book_detail.php?id=${id}">View Details</a>
                </div>
            `;
        })
        .join("");

    container.innerHTML = booksHTML;
};

// ── Utility: Show Alerts ────────────────────────────────────────────────────
const showAlert = (message, type = "info") => {
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
