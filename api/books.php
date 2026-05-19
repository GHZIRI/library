<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * API: Fetch Books from Database ✅ FIXED - NO LOGIN REQUIRED
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * Purpose: جلب قائمة الكتب من قاعدة البيانات المحلية
 * 
 * Usage:
 *   GET /api/books.php?action=search&q=search_query&limit=20
 *   GET /api/books.php?action=categories
 */

// ════════════════════════════════════════════════════════════════════════════
// Include Dependencies
// ════════════════════════════════════════════════════════════════════════════
require_once dirname(__FILE__) . '/../core/db.php';

// ════════════════════════════════════════════════════════════════════════════
// Response Headers
// ════════════════════════════════════════════════════════════════════════════
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// Get Parameters
// ════════════════════════════════════════════════════════════════════════════
$action   = $_GET['action']   ?? 'search';
$query    = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset   = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Validate limits
if ($limit > 100) $limit = 100;
if ($limit < 1) $limit = 20;
if ($offset < 0) $offset = 0;

// ════════════════════════════════════════════════════════════════════════════
// 1️⃣ SEARCH BOOKS (DEFAULT ACTION)
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'search') {
    try {
        // Verify database connection
        if (!isset($pdo)) {
            throw new Exception('Database not connected');
        }

        // Build SQL query
        $sql = "SELECT * FROM books WHERE 1=1";
        $params = [];

        // Search by title or author
        if (!empty($query)) {
            $sql .= " AND (title LIKE ? OR author LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add pagination
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        // Get total count (for pagination)
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

        // Execute query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'items' => $books,
            'total' => $totalCount,
            'count' => count($books),
            'message' => count($books) > 0 ? 'Books found' : 'No books found'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error',
            'debug' => $e->getMessage()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// 2️⃣ GET CATEGORIES
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'categories') {
    try {
        $stmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching categories'
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// 3️⃣ GET SINGLE BOOK BY ID
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'get' && isset($_GET['id'])) {
    try {
        $id = trim($_GET['id']);
        
        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'book' => $book
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Book not found'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving book'
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// Invalid action
// ════════════════════════════════════════════════════════════════════════════
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action. Use action=search, action=categories, or action=get&id=book_id'
]);

// ════════════════════════════════════════════════════════════════════════════
// Response Headers
// ════════════════════════════════════════════════════════════════════════════
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// ════════════════════════════════════════════════════════════════════════════
// Get Parameters
// ════════════════════════════════════════════════════════════════════════════
$action   = $_GET['action']   ?? 'search';
$query    = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$id       = isset($_GET['id']) ? trim($_GET['id']) : '';
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset   = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Validate limits
if ($limit > 100) $limit = 100;
if ($limit < 1) $limit = 20;
if ($offset < 0) $offset = 0;

// ════════════════════════════════════════════════════════════════════════════
// 1️⃣ GET SINGLE BOOK BY ID
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'get' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'book' => $book
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Book not found'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// 2️⃣ SEARCH BOOKS
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'search') {
    try {
        // Build query
        $sql = "SELECT * FROM books WHERE 1=1";
        $params = [];

        // Search by title or author
        if (!empty($query)) {
            $sql .= " AND (title LIKE ? OR author LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Filter by category
        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM books WHERE 1=1" . 
            (!empty($query) ? " AND (title LIKE ? OR author LIKE ?)" : "") .
            (!empty($category) ? " AND category = ?" : ""));
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Add pagination
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        // Execute query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Search failed',
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// 3️⃣ GET CATEGORIES
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'categories') {
    try {
        $stmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching categories'
        ]);
    }
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
// Invalid action
// ════════════════════════════════════════════════════════════════════════════
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);
?>
