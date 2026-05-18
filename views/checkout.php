<?php
require_once '../core/functions.php';


// If not logged in, redirect to login
if(!isLoggedIn()){
    redirect('login.php');
}


// Get current user
$user_id = currentUser()['id_user'];


// Get cart items
$cartItems = getCart($user_id);


// If cart is empty, redirect to catalogue
if(empty($cartItems)){
    redirect('catalogue.php');
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
         <h1>Checkout</h1>


         <!-- Order Form -->
          <form action="payment.php" method="post">
               <!-- Personal Info -->
        <h3>Your Information</h3>
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="text" name="city" placeholder="City" required><br>
        <input type="text" name="phone" placeholder="Phone Number" required><br>

        <!-- Order Type -->
        <h3>Order Type</h3>
        <select name="type">
            <option value="buy">Buy</option>
            <option value="rental">Rent</option>
        </select><br>

        <!-- Rental months — only shows if rental selected -->
        <div id="rentalMonths" style="display:none">
            <input type="number" name="rental_months" placeholder="Number of months" min="1" max="12">
        </div>

        <!-- Cart Items -->
        <h3>Your Books</h3>
        <div id="cartBooks">
            <?php foreach ($cartItems as $item) { ?>
                <div id="book-<?= $item['book_id'] ?>">
                    <p>Loading...</p>
                </div>
                <!-- Send book_id and type as hidden inputs -->
                <input type="hidden" name="book_ids[]" value="<?= $item['book_id'] ?>">
            <?php } ?>
        </div>

        <button type="submit">Confirm Order</button>

    </form>

         <SCript> const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

        // Show rental months input if rental selected
        document.querySelector('select[name="type"]').addEventListener('change', function() {
            const rentalDiv = document.getElementById('rentalMonths');
            rentalDiv.style.display = this.value === 'rental' ? 'block' : 'none';
        });

        // Load book details from Google Books API
        const bookDivs = document.querySelectorAll('[id^="book-"]');
        bookDivs.forEach(div => {
            const book_id = div.id.replace("book-", "");
            fetch(`https://www.googleapis.com/books/v1/volumes/${book_id}?key=${API_KEY}`)
                .then(res => res.json())
                .then(book => {
                    const info  = book.volumeInfo;
                    const title = info.title || "No title";
                    const cover = info.imageLinks ? info.imageLinks.thumbnail : "";
                    div.innerHTML = `
                        <img src="${cover}" alt="${title}">
                        <p>${title}</p>
                    `;
                });
        });
    </script>
</body>
</html>