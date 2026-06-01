<?php
/**
 * ملف الدوال المشتركة
 * 
 * يحتوي على دوال مساعدة تُستخدم في جميع أنحاء الموقع
 */

// تشغيل الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// استيراد ملف الاتصال
require_once __DIR__ . '/db.php';

// =====================================================
// دوال التحقق من الدخول (Authentication)
// =====================================================

/**
 * التحقق من هل المستخدم مسجل دخول
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * التحقق من هل المستخدم أدمين
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * الحصول على معرف المستخدم الحالي
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * إعادة التوجيه (Redirect)
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * فرض تسجيل الدخول
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * فرض حساب أدمين
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect('../');
    }
}

// =====================================================
// دوال التنظيف والحماية (Security)
// =====================================================

/**
 * تنظيف المدخلات (XSS Protection)
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * توليد رمز CSRF للحماية من الهجمات
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * التحقق من صحة رمز CSRF
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// =====================================================
// دوال المساعدة
// =====================================================

/**
 * تنسيق السعر بـ MAD
 */
function formatPrice($price) {
    return number_format($price, 2, '.', '') . ' MAD';
}

/**
 * تنسيق التاريخ
 */
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * حساب تاريخ الانتهاء من الكراء
 */
function calculateEndDate($days) {
    return date('Y-m-d', strtotime("+{$days} days"));
}

// =====================================================
// دوال الرسائل (Flash Messages)
// =====================================================

/**
 * حفظ رسالة مؤقتة
 */
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

/**
 * الحصول على رسالة مؤقتة وحذفها
 */
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// =====================================================
// دوال قاعدة البيانات
// =====================================================

/**
 * الحصول على جميع الأنواع
 */
function getAllTypes() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM book_types ORDER BY type_name ASC");
    return $stmt->fetchAll();
}

/**
 * الحصول على جميع الكتب (مع البحث والفلتر)
 */
function getAllBooks($search = '', $type_id = '') {
    global $pdo;
    
    $query = "SELECT b.*, bt.type_name 
              FROM books b 
              JOIN book_types bt ON b.type_id = bt.type_id 
              WHERE b.available_buy = TRUE";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (b.title LIKE ? OR b.author LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($type_id)) {
        $query .= " AND b.type_id = ?";
        $params[] = $type_id;
    }
    
    $query .= " ORDER BY b.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * الحصول على كتاب واحد بـ ID
 */
function getBookById($book_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT b.*, bt.type_name 
        FROM books b 
        JOIN book_types bt ON b.type_id = bt.type_id 
        WHERE b.book_id = ?
    ");
    $stmt->execute([$book_id]);
    return $stmt->fetch();
}

/**
 * إضافة كتاب جديد (للأدمين)
 */
function addBook($data) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO books (title, author, type_id, cover_image, description, 
                          price_buy, price_rental, available_buy, available_rental)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['title'],
        $data['author'],
        $data['type_id'],
        $data['cover_image'] ?? null,
        $data['description'] ?? null,
        $data['price_buy'],
        $data['price_rental'] ?? null,
        $data['available_buy'] ?? 1,
        $data['available_rental'] ?? 0,
    ]);
}

/**
 * حذف كتاب (للأدمين)
 */
function deleteBook($book_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
    return $stmt->execute([$book_id]);
}

/**
 * الحصول على بيانات المستخدم
 */
function getUserById($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * الحصول على المستخدم برسالة بريده
 */
function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * إنشاء حساب جديد
 */
function createUser($data) {
    global $pdo;
    
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (name_user, email, password, role)
        VALUES (?, ?, ?, 'user')
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['email'],
        $password_hash
    ]);
}

/**
 * إنشاء طلب شراء جديد
 */
function createBuyOrder($data) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO orders_buy (user_id, book_id, name, phone, city, quantity, total_price)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['user_id'] ?? null,
        $data['book_id'],
        $data['name'],
        $data['phone'],
        $data['city'],
        $data['quantity'],
        $data['total_price']
    ]);
}

/**
 * إنشاء طلب كراء جديد
 */
function createRentalOrder($data) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO orders_rental 
        (user_id, book_id, name, phone, city, rental_days, total_price, card_last_four, start_date, end_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['user_id'],
        $data['book_id'],
        $data['name'],
        $data['phone'],
        $data['city'],
        $data['rental_days'],
        $data['total_price'],
        $data['card_last_four'],
        date('Y-m-d'),
        $data['end_date']
    ]);
}
