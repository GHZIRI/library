<?php
/**
 * Catalog Page - Homepage
 * 
 * Displays all available books with search and filter capabilities.
 */

require_once '../core/functions.php';

// Get parameters from URL
$search = sanitize($_GET['search'] ?? '');
$type_id = sanitize($_GET['type_id'] ?? '');

// Get books and categories
$books = getAllBooks($search, $type_id);
$types = getAllTypes();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - Catalog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Modern adjustments for Left-to-Right layout */
        .navbar-links {
            display: flex;
            gap: 20px;
            list-style: none;
            flex-direction: row;
        }
        .search-filter {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .search-filter input, .search-filter select {
            padding: 10px 15px;
            border: 1px solid var(--border);
            border-radius: 5px;
            font-size: 14px;
        }
        .search-filter input {
            flex: 2;
        }
        .search-filter select {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 Library</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">Home</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="user_dashboard.php">My Dashboard</a></li>
                    <li><a href="../core/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login" style="background: var(--primary); color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none;">Login</a></li>
                <?php endif; ?>
                <li><a href="../admin/login.php">Admin Panel</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container">
        <div class="hero" style="text-align: center; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; padding: 40px; border-radius: 15px; margin-top: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h1 style="font-size: 36px; margin-bottom: 10px; font-weight: 700;">📖 Welcome to Our Library</h1>
            <p style="font-size: 18px; opacity: 0.9;">Discover the best books and find your perfect read</p>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="container">
        <form method="GET" action="catalogue.php" class="search-filter">
            <!-- Search Input -->
            <input 
                type="text" 
                name="search" 
                placeholder="Search for books or authors..." 
                value="<?php echo htmlspecialchars($search); ?>">

            <!-- Category Filter -->
            <select name="type_id">
                <option value="">All Categories</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?php echo $type['type_id']; ?>" 
                        <?php echo ($type_id == $type['type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['type_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Action Buttons -->
            <button type="submit" class="btn btn-primary" style="padding: 10px 25px;">🔍 Search</button>
            <a href="catalogue.php" class="btn btn-secondary" style="padding: 10px 20px; display: flex; align-items: center; justify-content: center; text-decoration: none;">Clear</a>
        </form>
    </div>

    <!-- Books Grid -->
    <div class="container">
        <?php if (empty($books)): ?>
            <div style="text-align: center; padding: 60px 20px; background-color: white; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <p style="font-size: 64px; margin-bottom: 15px;">📭</p>
                <p style="font-size: 20px; font-weight: 700; color: var(--dark); margin-bottom: 10px;">No Results Found</p>
                <p style="color: var(--gray);">Try changing your search terms or filters.</p>
            </div>
        <?php else: ?>
            <div class="books-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; margin-bottom: 50px;">
                <?php foreach ($books as $book): ?>
                    <div class="book-card" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; height: 100%; transition: transform 0.2s, box-shadow 0.2s;">
                        <!-- Book Cover -->
                        <div class="book-card-image" style="height: 250px; background: var(--light); display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 64px;">
                            <?php if (!empty($book['cover_image'])): ?>
                                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                📖
                            <?php endif; ?>
                        </div>

                        <!-- Book Body -->
                        <div class="book-card-body" style="padding: 20px; display: flex; flex-direction: column; flex-grow: 1; text-align: left;">
                            <h3 class="book-card-title" style="font-size: 18px; margin-bottom: 8px; font-weight: 700; color: var(--dark);"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-card-author" style="color: var(--gray); font-size: 14px; margin-bottom: 15px;">✍️ By <?php echo htmlspecialchars($book['author']); ?></p>
                            
                            <div style="margin-top: auto;">
                                <span class="book-card-type" style="display: inline-block; background: var(--light); color: var(--primary); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 12px;"><?php echo htmlspecialchars($book['type_name']); ?></span>
                                <p class="book-card-price" style="font-size: 16px; font-weight: 700; color: var(--secondary); margin-bottom: 15px;">Price: <?php echo formatPrice($book['price_buy']); ?></p>

                                <!-- Action Buttons -->
                                <div class="book-card-actions" style="display: flex; gap: 10px;">
                                    <form method="GET" action="buy.php" style="flex: 1;">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 8px 0; border: none; font-weight: 600;">🛒 Buy</button>
                                    </form>
                                    <?php if ($book['available_rental']): ?>
                                        <form method="GET" action="rent.php" style="flex: 1;">
                                            <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                            <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 8px 0; border: none; font-weight: 600;">📖 Rent</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Library. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
