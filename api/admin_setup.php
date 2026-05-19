<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * ADMIN SETUP: Create Books Table and Insert Sample Data
 * ════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__) . '/../core/db.php';

$action = $_GET['action'] ?? 'help';

try {
    // ════════════════════════════════════════════════════════════════════════
    // ACTION 1: Create Books Table
    // ════════════════════════════════════════════════════════════════════════
    if ($action === 'create_books_table') {
        $sql = "CREATE TABLE IF NOT EXISTS books (
            id_book      INT            AUTO_INCREMENT PRIMARY KEY,
            book_id      VARCHAR(100)   NOT NULL UNIQUE,
            title        VARCHAR(255)   NOT NULL,
            author       VARCHAR(255),
            description  LONGTEXT,
            cover_image  VARCHAR(500),
            language     VARCHAR(10)    DEFAULT 'en',
            published_date VARCHAR(10),
            publisher    VARCHAR(100),
            price_buy    DECIMAL(10,2)  DEFAULT 0,
            price_rental DECIMAL(10,2)  DEFAULT 0,
            category     VARCHAR(100),
            rating       DECIMAL(3,1)   DEFAULT 0,
            created_at   DATETIME       DEFAULT CURRENT_TIMESTAMP,
            INDEX (category),
            INDEX (title),
            INDEX (author)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => '✅ Books table created successfully!'
        ]);
        exit();
    }

    // ════════════════════════════════════════════════════════════════════════
    // ACTION 2: Insert Sample Books
    // ════════════════════════════════════════════════════════════════════════
    if ($action === 'insert_sample_books') {
        $sql = "INSERT IGNORE INTO books 
            (book_id, title, author, cover_image, price_buy, price_rental, category, description) 
        VALUES 
            (
                '1',
                'The Great Gatsby',
                'F. Scott Fitzgerald',
                'https://via.placeholder.com/150?text=Great+Gatsby',
                25.00,
                3.50,
                'Fiction',
                'A classic novel set in the Jazz Age of the 1920s'
            ),
            (
                '2',
                '1984',
                'George Orwell',
                'https://via.placeholder.com/150?text=1984',
                20.00,
                3.00,
                'Fiction',
                'A dystopian novel about totalitarianism and surveillance'
            ),
            (
                '3',
                'To Kill a Mockingbird',
                'Harper Lee',
                'https://via.placeholder.com/150?text=Mockingbird',
                22.00,
                3.00,
                'Fiction',
                'An American classic addressing racial injustice'
            ),
            (
                '4',
                'Pride and Prejudice',
                'Jane Austen',
                'https://via.placeholder.com/150?text=Pride+Prejudice',
                18.00,
                2.50,
                'Romance',
                'A romantic novel with witty social commentary'
            ),
            (
                '5',
                'The Catcher in the Rye',
                'J.D. Salinger',
                'https://via.placeholder.com/150?text=Catcher+in+Rye',
                19.00,
                2.50,
                'Fiction',
                'A coming-of-age novel about teenage alienation'
            ),
            (
                '6',
                'The Hobbit',
                'J.R.R. Tolkien',
                'https://via.placeholder.com/150?text=The+Hobbit',
                28.00,
                4.00,
                'Fantasy',
                'An epic fantasy adventure of a hobbit named Bilbo'
            ),
            (
                '7',
                'Moby Dick',
                'Herman Melville',
                'https://via.placeholder.com/150?text=Moby+Dick',
                24.00,
                3.50,
                'Adventure',
                'An epic maritime adventure novel'
            ),
            (
                '8',
                'Jane Eyre',
                'Charlotte Brontë',
                'https://via.placeholder.com/150?text=Jane+Eyre',
                21.00,
                3.00,
                'Romance',
                'A Gothic romance novel with a strong female protagonist'
            );";

        $pdo->exec($sql);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => '✅ Sample books inserted successfully!'
        ]);
        exit();
    }

    // ════════════════════════════════════════════════════════════════════════
    // ACTION 3: Check Status
    // ════════════════════════════════════════════════════════════════════════
    if ($action === 'status') {
        // Check if table exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='library' AND TABLE_NAME='books'");
        $tableExists = $stmt->fetchColumn();

        // Count books
        $stmt = $pdo->query("SELECT COUNT(*) FROM books");
        $bookCount = $stmt->fetchColumn();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'table_exists' => (bool)$tableExists,
            'book_count' => (int)$bookCount,
            'message' => $tableExists ? 'Table exists with ' . $bookCount . ' books' : 'Table does not exist'
        ]);
        exit();
    }

    // ════════════════════════════════════════════════════════════════════════
    // Invalid Action
    // ════════════════════════════════════════════════════════════════════════
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action',
        'available_actions' => [
            'create_books_table' => 'Create the books table',
            'insert_sample_books' => 'Insert 8 sample books',
            'status' => 'Check current status'
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
