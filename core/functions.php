<?php

// ── Bootstrap ────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';


// ── Auth helpers ─────────────────────────────────────────────────────────────

// Check if user is logged in
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

// Check if logged-in user is admin
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

// Get current logged-in user array
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

// Redirect helper
function redirect(string $url): never {
    header("Location: {$url}");
    exit();
}

// Require login — redirect to login page if not logged in
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('../views/login.php');
    }
}


// ── Input helpers ─────────────────────────────────────────────────────────────

// Sanitize input (XSS protection)
function sanitize(mixed $data): mixed {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string) $data), ENT_QUOTES, 'UTF-8');
}

// Format price
function formatPrice(float $price): string {
    return number_format($price, 2, '.', '') . ' MAD';
}

// Format date
function formatDate(string $date, string $format = 'Y-m-d H:i'): string {
    return date($format, strtotime($date));
}

// Generate unique order number
function generateOrderNumber(): string {
    return 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
}


// ── Flash messages ────────────────────────────────────────────────────────────

function setFlash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// ── CSRF Protection ───────────────────────────────────────────────────────────

/**
 * Generate a CSRF token for form security
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from form submission
 */
function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token for forms
 */
function getCSRFToken(): string {
    return generateCSRFToken();
}


// ── Password helpers ──────────────────────────────────────────────────────────

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}


// ── Cart functions ────────────────────────────────────────────────────────────

// Add a book to cart (prevent duplicates per user+book+type)
function addToCart(int $user_id, string $book_id, string $type, int $rental_months = 1): bool {
    global $pdo;

    // Check if already in cart
    $stmt = $pdo->prepare(
        "SELECT id_cart FROM cart WHERE id_user = ? AND book_id = ? AND type = ?"
    );
    $stmt->execute([$user_id, $book_id, $type]);

    if ($stmt->fetch()) {
        return false; // Already in cart
    }

    $stmt = $pdo->prepare(
        "INSERT INTO cart (id_user, book_id, type, rental_months) VALUES (?, ?, ?, ?)"
    );
    return $stmt->execute([
        $user_id,
        $book_id,
        $type,
        $type === 'rental' ? $rental_months : null,
    ]);
}

// Get all cart items for a user
function getCart(int $user_id): array {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE id_user = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Remove a single item from cart (only if it belongs to the user)
function removeFromCart(int $id_cart, int $user_id): bool {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id_cart = ? AND id_user = ?");
    return $stmt->execute([$id_cart, $user_id]);
}

// Clear all cart items for a user
function clearCart(int $user_id): bool {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id_user = ?");
    return $stmt->execute([$user_id]);
}

// Get cart item count for the logged-in user
function getCartCount(): int {
    if (!isLoggedIn()) {
        return 0;
    }
    global $pdo;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE id_user = ?");
    $stmt->execute([currentUser()['id_user']]);
    return (int) $stmt->fetchColumn();
}


// ── Order functions ───────────────────────────────────────────────────────────

// Create a buy order
function createBuyOrder(
    int    $user_id,
    string $book_id,
    string $name,
    string $city,
    string $phone,
    int    $quantity,
    float  $total_price
): bool {
    global $pdo;

    $stmt = $pdo->prepare(
        "INSERT INTO orders_buy (id_user, book_id, name_buy, city, phone_number, quantity, total_price)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    return $stmt->execute([$user_id, $book_id, $name, $city, $phone, $quantity, $total_price]);
}

// Create a rental order
function createRentalOrder(
    int    $user_id,
    string $book_id,
    string $name,
    string $city,
    string $phone,
    int    $rental_months,
    float  $total_price,
    string $start_date,
    string $end_date
): bool {
    global $pdo;

    $stmt = $pdo->prepare(
        "INSERT INTO orders_rental
            (id_user, book_id, name_rental, city, phone_number, rental_months, total_price, start_date, end_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    return $stmt->execute([
        $user_id, $book_id, $name, $city, $phone,
        $rental_months, $total_price, $start_date, $end_date,
    ]);
}

// Get all orders (buy + rental) for a user
function getUserOrders(int $user_id): array {
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT * FROM orders_buy WHERE id_user = ? ORDER BY created_at DESC"
    );
    $stmt->execute([$user_id]);
    $buy = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT * FROM orders_rental WHERE id_user = ? ORDER BY created_at DESC"
    );
    $stmt->execute([$user_id]);
    $rental = $stmt->fetchAll();

    return ['buy' => $buy, 'rental' => $rental];
}
