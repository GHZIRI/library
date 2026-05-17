// Google Books API Key
const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

// When the page loads, show Arabic books automatically
window.onload = function() {
    fetchBooks("روايات عربية");
}


// Get the search input and fetch books
function searchBooks(){
    const query = document.getElementById("searchInput").value;
    if(query.trim() === "") return;
    fetchBooks(query);
}


// Fetch books from Google Books API
function fetchBooks(query){
    const url = `https://www.googleapis.com/books/v1/volumes?q=${query}&langRestrict=ar&maxResults=20&key=${API_KEY}`;

    fetch(url)
    .then(res => res.json())
    .then(data => displayBooks(data.items))
    .catch(err => console.error("Error:", err));

}


// Display books as cards on the page
function displayBooks(books){
      const container = document.getElementById("booksContainer");
      container.innerHTML = "";
// If no books found
    if(!books){
        container.innerHTML = "<p>No books found.</p>";
        return;
    }
// Loop through each book and create a card
    books.forEach(element => {
        const info = element.volumeInfo;
        const title = info.title || "No title";
        const author = info.authors ? info.authors[0] : "Unknown author";
        const cover  = info.imageLinks ? info.imageLinks.thumbnail : "../assets/images/no-cover.jpg";
        const id = element.id;
        
        
          container.innerHTML += `
            <div class="book-card">
                <img src="${cover}" alt="${title}">
                <h3>${title}</h3>
                <p>${author}</p>
                <a href="book_detail.php?id=${id}">View Details</a>
            </div>
        `;
    });
}
