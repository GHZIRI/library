<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * API: Fetch Books from Database ✅ NO LOGIN REQUIRED
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * @description Public API endpoint for fetching books from the database
 * Provides book search, category listing, and individual book retrieval
 * No authentication required - allows public browsing
 * 
 * @usage GET /api/books.php?action=search&q=search_query&limit=20
 *        GET /api/books.php?action=categories
 *        GET /api/books.php?action=get&id=book_id
 */

/**
 * Include database connection and helper functions
 * Establishes PDO connection stored in $pdo global variable
 */
require_once dirname(__FILE__) . '/../core/db.php';

/**
 * Configure HTTP response headers for API communication
 * Sets JSON content type and enables CORS for cross-origin requests
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

/**
 * Handle CORS preflight requests
 * Required for browser-based CORS requests from frontend JavaScript
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Extract and validate query parameters from GET request
 * @param string $action - Operation type: 'search', 'categories', or 'get'
 * @param string $query - Search term for title/author filtering
 * @param int $limit - Maximum number of results (capped at 100)
 * @param int $offset - Pagination offset for results
 */
$action   = $_GET['action']   ?? 'search';
$query    = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset   = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

/**
 * Validate and sanitize limit/offset values
 * Prevents extremely large queries and SQL injection attempts
 * Ensures reasonable pagination constraints
 */
if ($limit > 100) $limit = 100;  // Cap maximum results at 100
if ($limit < 1) $limit = 20;     // Use default if invalid
if ($offset < 0) $offset = 0;    // Prevent negative offsets

// ════════════════════════════════════════════════════════════════════════════
// ACTION 1: Get Single Book by ID
// ════════════════════════════════════════════════════════════════════════════

/**
 * Retrieves a single book record by its unique book_id
 * Returns 404 if book not found, 200 on success
 * 
 * Query Parameters:
 *   @param string $id - Unique book identifier
 * 
 * Response:
 *   @returns {object} JSON with 'success' boolean and 'book' object
 */
if ($action === 'search') {
    try {
        // Verify database connection
        if (!isset($pdo)) {
            throw new Exception('Database not connected');
        }

        // Build base SQL query
        $sql = "SELECT * FROM books WHERE 1=1";
        $params = [];

        // Add search filter for title or author matching
        if (!empty($query)) {
            $sql .= " AND (title LIKE ? OR author LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add pagination - order by newest first
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        // Get total count of matching books for pagination metadata
        $countSql = "SELECT COUNT(*) as total FROM books WHERE 1=1";
        if (!empty($query)) {
            $countSql .= " AND (title LIKE ? OR author LIKE ?)";
            $countStmt = $pdo->prepare($countSql);
            $searchTerm = "%{$query}%";
            $countStmt->execute([$searchTerm, $searchTerm]);
        } else {
            $countStmt = $pdo->query($countSql);
        }
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Execute search query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return successful response with books and pagination info
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'items' => $books,
            'total' => $totalCount,
            'count' => count($books),
            'message' => count($books) > 0 ? 'Books found' : 'No books found'
        ]);

    } catch (PDOException $e) {
        // Handle database errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error',
            'debug' => $e->getMessage()
        ]);
    } catch (Exception $e) {
        // Handle general errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// ACTION 2: Get Distinct Categories
// ════════════════════════════════════════════════════════════════════════════

/**
 * Retrieves all unique book categories from the database
 * Useful for category filters and navigation menus
 * 
 * Response:
 *   @returns {object} JSON with 'success' boolean and 'categories' array
 */
if ($action === 'categories') {
    try {
        // Query all distinct categories, sorted alphabetically
        $stmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Return successful response with category list
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        // Handle query errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching categories'
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// ACTION 3: Get Single Book by ID
// ════════════════════════════════════════════════════════════════════════════

/**
 * Retrieves a single book record by its unique book_id
 * Returns 404 if book not found
 * 
 * Query Parameters:
 *   @param string $id - Unique book identifier
 * 
 * Response:
 *   @returns {object} JSON with 'success' boolean and 'book' object or error
 */
if ($action === 'get' && isset($_GET['id'])) {
    try {
        // Sanitize book ID to prevent SQL injection
        $id = trim($_GET['id']);
        
        // Query single book by ID
        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            // Book found - return success response
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'book' => $book
            ]);
        } else {
            // Book not found - return 404
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Book not found'
            ]);
        }
    } catch (Exception $e) {
        // Handle database errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving book'
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// Invalid or Missing Action - Default Error Response
// ════════════════════════════════════════════════════════════════════════════

/**
 * Default response for unrecognized or missing actions
 * Returns 400 Bad Request with list of valid actions
 */
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action. Use action=search, action=categories, or action=get&id=book_id'
]);

// ════════════════════════════════════════════════════════════════════════════
// Additional Response Headers (For Compatibility)
// ════════════════════════════════════════════════════════════════════════════

/**
 * Duplicate header configuration - kept for backward compatibility
 * Sets JSON response type and CORS headers
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// ════════════════════════════════════════════════════════════════════════════
// Additional Parameter Extraction (For Compatibility)
// ════════════════════════════════════════════════════════════════════════════

/**
 * Re-extract query parameters in case alternative code paths need them
 * Note: These may be redundant depending on executed action
 * 
 * Parameters:
 *   @param string $category - Optional category filter for searches
 *   @param string $id - Book identifier for get action
 */
$action   = $_GET['action']   ?? 'search';
$query    = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$id       = isset($_GET['id']) ? trim($_GET['id']) : '';
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset   = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Re-validate limit and offset values for safety
if ($limit > 100) $limit = 100;  // Cap maximum results
if ($limit < 1) $limit = 20;     // Use default if invalid
if ($offset < 0) $offset = 0;    // Prevent negative offsets

// ════════════════════════════════════════════════════════════════════════════
// LEGACY ACTION HANDLERS (Duplicate - Kept for backward compatibility)
// ════════════════════════════════════════════════════════════════════════════

/**
 * DUPLICATE ACTION 1: Get Single Book by ID (Legacy Path)
 * This is a duplicate of the action handlers above
 * Kept for backward compatibility with legacy code paths
 * 
 * Retrieves a single book record by its unique book_id
 * Returns 404 if not found, 200 on success
 */
if ($action === 'get' && $id) {
    try {
        // Query single book by ID
        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            // Book found - return success response
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'book' => $book
            ]);
        } else {
            // Book not found - return 404
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Book not found'
            ]);
        }
    } catch (Exception $e) {
        // Handle database errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }
    exit();
}

/**
 * DUPLICATE ACTION 2: Search Books (Legacy Path)
 * This is a duplicate of the action handlers above with additional category filtering
 * Kept for backward compatibility
 * 
 * Searches for books by title or author, with optional category filter
 * Supports pagination with limit and offset parameters
 * Includes total count for pagination metadata
 */
if ($action === 'search') {
    try {
        // Build query with conditional filters
        $sql = "SELECT * FROM books WHERE 1=1";
        $params = [];

        // Add search filter for title or author matching
        if (!empty($query)) {
            $sql .= " AND (title LIKE ? OR author LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add optional category filter
        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        // Build count query with same filters for pagination info
        $countSql = "SELECT COUNT(*) as total FROM books WHERE 1=1" . 
            (!empty($query) ? " AND (title LIKE ? OR author LIKE ?)" : "") .
            (!empty($category) ? " AND category = ?" : "");
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Add pagination - order by newest first
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        // Execute search query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return successful response with pagination info
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'items' => $books,
            'total' => $totalCount,
            'count' => count($books),
            'limit' => $limit,
            'offset' => $offset
        ]);
    } catch (Exception $e) {
        // Handle database errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Search failed',
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * DUPLICATE ACTION 3: Get Categories (Legacy Path)
 * This is a duplicate of the action handler above
 * Kept for backward compatibility
 * 
 * Retrieves all unique book categories
 * Used for category filtering and navigation menus
 */
if ($action === 'categories') {
    try {
        // Query all distinct categories, sorted alphabetically
        $stmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Return successful response with category list
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        // Handle query errors
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching categories'
        ]);
    }
    exit();
}

/**
 * DUPLICATE: Final Invalid Action Response
 * Fallback error response if no action matched
 * Returns 400 Bad Request status code
 */
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);

/**
 * ════════════════════════════════════════════════════════════════════════════
 * END OF API ENDPOINT
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * Summary of Public API Endpoints:
 *   1. GET /api/books.php?action=search&q=query&limit=20&offset=0
 *      - Search books by title/author with pagination
 *      - Parameters: q (search query), limit (max results), offset (pagination)
 *      - Returns: Array of matching books with total count
 *
 *   2. GET /api/books.php?action=categories
 *      - Get all available book categories
 *      - No parameters required
 *      - Returns: Array of category names
 *
 *   3. GET /api/books.php?action=get&id=book_id
 *      - Retrieve single book by ID
 *      - Parameters: id (unique book identifier)
 *      - Returns: Single book object or 404 error
 *
 * No authentication required for any endpoint - public API for browsing
 * ════════════════════════════════════════════════════════════════════════════
 */
?>
