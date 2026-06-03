<?php
// =====================================================
// STEP 1: Start the session
// A session allows us to remember who is logged in.
// Without this, we cannot check if the admin is logged in.
// =====================================================
session_start();

// =====================================================
// STEP 2: Connect to the database
// We use the existing db.php file so we don't
// have to write the connection code again.
// =====================================================
require_once '../core/db.php';

// =====================================================
// STEP 3: Protect the page
// If the admin is NOT logged in, send them to login.php
// isset() checks if a variable exists
// =====================================================
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit; // Stop the rest of the code from running
}

// =====================================================
// STEP 4: Get the admin's name from the session
// We saved the name in login.php when the admin logged in.
// ?? 'Admin' means: if it doesn't exist, use 'Admin' instead.
// =====================================================
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// =====================================================
// STEP 5: Count the users
// SQL: SELECT COUNT(*) FROM users
//   - COUNT(*) = count all rows in the table
//   - FROM users = from the users table
// fetchColumn() gets just the first number from the result.
// =====================================================
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// =====================================================
// STEP 6: Count the books
// Same idea as above but for the books table.
// =====================================================
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();

// =====================================================
// STEP 7: Count the categories (book_types table)
// The categories are stored in a table called book_types.
// =====================================================
$total_categories = $pdo->query("SELECT COUNT(*) FROM book_types")->fetchColumn();

// =====================================================
// STEP 8: Count purchase orders
// =====================================================
$total_purchases = $pdo->query("SELECT COUNT(*) FROM orders_buy")->fetchColumn();

// =====================================================
// STEP 9: Count rental orders
// =====================================================
$total_rentals = $pdo->query("SELECT COUNT(*) FROM orders_rental")->fetchColumn();

// =====================================================
// STEP 10: Load all categories for the dropdown menu
// We need them in the form so the admin can choose a type.
// fetchAll() returns ALL rows as an array.
// =====================================================
$categories = $pdo->query("SELECT * FROM book_types")->fetchAll();

// =====================================================
// STEP 11: Handle the "Add Book" form submission
// When the admin submits the form, $_SERVER['REQUEST_METHOD']
// will equal 'POST'. We check this before saving anything.
// =====================================================
$success_message = '';
$error_message   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read each field sent from the form.
    // trim() removes accidental spaces at the start/end.
    $title       = trim($_POST['title']       ?? '');
    $author      = trim($_POST['author']      ?? '');
    $type_id     = intval($_POST['type_id']   ?? 0);   // intval() converts to integer
    $price_buy   = floatval($_POST['price_buy']   ?? 0); // floatval() converts to decimal
    $price_rental = floatval($_POST['price_rental'] ?? 0);

    // Simple validation: make sure nothing important is empty
    if (empty($title) || empty($author) || $type_id <= 0 || $price_buy <= 0) {
        $error_message = '❌ Please fill in all required fields (Title, Author, Category, Buy Price).';
    } else {
        // =====================================================
        // SQL INSERT statement explained:
        //   INSERT INTO books       ← add a row to the books table
        //   (title, author, ...)    ← these are the column names
        //   VALUES (?, ?, ...)      ← ? is a placeholder (safe from SQL injection)
        // =====================================================
        $sql = "INSERT INTO books 
                    (title, author, type_id, price_buy, price_rental, available_buy, available_rental)
                VALUES 
                    (?, ?, ?, ?, ?, 1, ?)";

        // prepare() makes the query safe by separating SQL from data
        $stmt = $pdo->prepare($sql);

        // execute() runs the query and fills in the ? placeholders
        $ok = $stmt->execute([
            $title,
            $author,
            $type_id,
            $price_buy,
            $price_rental,
            ($price_rental > 0) ? 1 : 0   // only rentable if price_rental > 0
        ]);

        if ($ok) {
            $success_message = '✅ Book "' . htmlspecialchars($title) . '" added successfully!';
            // Update the book count so the stat card shows the new number
            $total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
        } else {
            $error_message = '❌ Something went wrong. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>

    <!-- =====================================================
         HEADER SECTION
         - Shows the page title
         - Greets the admin by name
         - Has a Logout button
         ===================================================== -->
    <div class="header">
        <div>
            <h1>📊 Admin Dashboard</h1>
            <!-- We use htmlspecialchars() to prevent XSS attacks.
                 It converts special characters like < > to safe text. -->
            <p>Welcome back, <strong><?php echo htmlspecialchars($admin_name); ?></strong>!</p>
        </div>

        <!-- Logout link: goes to logout.php which destroys the session -->
        <a href="logout.php" class="logout-btn">🚪 Logout</a>
    </div>


    <!-- =====================================================
         STATS CARDS SECTION
         Each card shows one number from the database.
         ===================================================== -->
    <p class="section-title">📈 Quick Statistics</p>

    <div class="cards-grid">

        <!-- Card 1: Total Users -->
        <div class="card">
            <div class="icon">👥</div>
            <!-- $total_users holds the number we got from the database -->
            <p class="number"><?php echo $total_users; ?></p>
            <p class="label">Total Users</p>
        </div>

        <!-- Card 2: Total Books -->
        <div class="card green">
            <div class="icon">📚</div>
            <p class="number"><?php echo $total_books; ?></p>
            <p class="label">Total Books</p>
        </div>

        <!-- Card 3: Total Categories -->
        <div class="card orange">
            <div class="icon">🏷️</div>
            <p class="number"><?php echo $total_categories; ?></p>
            <p class="label">Total Categories</p>
        </div>

        <!-- Card 4: Purchase Orders -->
        <div class="card purple">
            <div class="icon">🛒</div>
            <p class="number"><?php echo $total_purchases; ?></p>
            <p class="label">Purchase Orders</p>
        </div>

        <!-- Card 5: Rental Orders -->
        <div class="card red">
            <div class="icon">🔄</div>
            <p class="number"><?php echo $total_rentals; ?></p>
            <p class="label">Rental Orders</p>
        </div>

    </div>


    <!-- =====================================================
         ADD NEW BOOK FORM
         This form sends data to this same page (action="").
         PHP above reads the data and saves it to the database.
         ===================================================== -->
    <p class="section-title">➕ Add New Book</p>

    <div class="form-box">

        <!-- Show success message if book was added -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Show error message if something went wrong -->
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!--
            method="POST"  → send data securely in the request body
            action=""      → submit to this same page (dashboard.php)
        -->
        <form method="POST" action="">

            <!-- Row 1: Title + Author side by side -->
            <div class="form-row">
                <div class="form-group">
                    <label for="title">📖 Book Title *</label>
                    <input type="text" id="title" name="title"
                           placeholder="e.g. The Great Gatsby"
                           required maxlength="200">
                </div>

                <div class="form-group">
                    <label for="author">✍️ Author Name *</label>
                    <input type="text" id="author" name="author"
                           placeholder="e.g. F. Scott Fitzgerald"
                           required maxlength="150">
                </div>
            </div>

            <!-- Row 2: Category dropdown -->
            <div class="form-group">
                <label for="type_id">🏷️ Category *</label>
                <!--
                    <select> shows a dropdown list.
                    We loop through $categories (from the DB)
                    and create one <option> for each category.
                -->
                <select id="type_id" name="type_id" required>
                    <option value="">-- Select a category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['type_id']; ?>">
                            <?php echo htmlspecialchars($cat['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Row 3: Buy Price + Rental Price side by side -->
            <div class="form-row">
                <div class="form-group">
                    <label for="price_buy">💰 Buy Price (MAD) *</label>
                    <!--
                        type="number"  → only numbers allowed
                        step="0.01"    → allows decimals like 55.50
                        min="0.01"     → must be at least 0.01
                    -->
                    <input type="number" id="price_buy" name="price_buy"
                           placeholder="e.g. 55.00"
                           step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label for="price_rental">🔄 Rental Price/Day (MAD)</label>
                    <input type="number" id="price_rental" name="price_rental"
                           placeholder="e.g. 5.00 (leave 0 if not for rent)"
                           step="0.01" min="0" value="0">
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn-submit">✅ Add Book to Library</button>

        </form>
    </div>

    <!-- =====================================================
         INFO BOX SECTION
         Shows extra information like the current date
         and a link to the main dashboard.
         ===================================================== -->
    <p class="section-title">ℹ️ System Info</p>

    <div class="info-box">
        <!-- date('Y-m-d') returns today's date, like: 2026-06-02 -->
        <p>📅 <strong>Today's Date:</strong> <?php echo date('Y - m - d'); ?></p>

        <p>🔗 <strong>Full Dashboard:</strong>
            <a href="dashboard.php">Go to full admin panel</a>
        </p>

        <p>👁️ <strong>View Library:</strong>
            <a href="../views/catalogue.php" target="_blank">Open catalog</a>
        </p>
    </div>

</body>
</html>
