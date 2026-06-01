-- =====================================================
-- قاعدة بيانات مكتبة - Library Database
-- =====================================================

-- حذف قاعدة البيانات القديمة إن وجدت
DROP DATABASE IF EXISTS library;

-- إنشاء قاعدة البيانات
CREATE DATABASE library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library;

-- =====================================================
-- جدول الأنواع (Book Types)
-- =====================================================
CREATE TABLE book_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- إدراج الأنواع الأساسية
INSERT INTO book_types (type_name) VALUES
('رواية عادية'),
('رواية خوف'),
('رواية رومانسية');

-- =====================================================
-- جدول الكتب (Books)
-- =====================================================
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    type_id INT NOT NULL,
    cover_image VARCHAR(500),
    description TEXT,
    price_buy DECIMAL(10,2) NOT NULL,
    price_rental DECIMAL(10,2),
    available_buy BOOLEAN DEFAULT TRUE,
    available_rental BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES book_types(type_id) ON DELETE RESTRICT
);

-- =====================================================
-- جدول المستخدمين (Users)
-- =====================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name_user VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    phone VARCHAR(20),
    city VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- إدراج حساب الأدمين
-- البريد: admin@library.com
-- كلمة السر: admin123
INSERT INTO users (name_user, email, password, role) VALUES
('Admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- =====================================================
-- جدول طلبات الشراء (Orders Buy)
-- =====================================================
CREATE TABLE orders_buy (
    order_buy_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT
);

-- =====================================================
-- جدول طلبات الكراء (Orders Rental)
-- =====================================================
CREATE TABLE orders_rental (
    order_rental_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    rental_days INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    card_last_four VARCHAR(4),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'active', 'returned', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT
);

-- =====================================================
-- بيانات تجريبية - Sample Books
-- =====================================================
INSERT INTO books (title, author, type_id, price_buy, price_rental, available_buy, available_rental, description) VALUES
('الأيام', 'طه حسين', 1, 75.00, 15.00, TRUE, TRUE, 'رواية عظيمة تحكي قصة حياة المؤلف وصراعه مع العمى.'),
('جريمة وعقاب', 'فيودور دوستويفسكي', 2, 95.00, 25.00, TRUE, TRUE, 'رواية نفسية عميقة عن الجريمة والندم والخلاص.'),
('الشيخ والبحر', 'إرنست همنغواي', 1, 85.00, 20.00, TRUE, TRUE, 'قصة رجل عجوز وصراعه مع البحر والحياة.'),
('بيت الأرواح', 'إيزابيل الليندي', 3, 90.00, 22.00, TRUE, TRUE, 'رواية رومانسية تغطي عائلة عبر عدة أجيال.'),
('القلب الطيب', 'ألكسندر بوشكين', 2, 80.00, 18.00, TRUE, FALSE, 'رواية خوف كلاسيكية عن الحب والخيانة.');
