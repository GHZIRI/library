DROP DATABASE IF EXISTS library;
CREATE DATABASE library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library;

-- ==================
-- USERS
-- ==================
CREATE TABLE users (
    id_user    INT           AUTO_INCREMENT PRIMARY KEY,
    name_user  VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME      DEFAULT CURRENT_TIMESTAMP
);

-- ==================
-- ORDERS BUY
-- ==================
CREATE TABLE orders_buy (
    id_buy       INT            AUTO_INCREMENT PRIMARY KEY,
    id_user      INT            NOT NULL,
    book_id      VARCHAR(100)   NOT NULL,
    name_buy     VARCHAR(100)   NOT NULL,
    city         VARCHAR(100)   NOT NULL,
    phone_number VARCHAR(15)    NOT NULL,
    quantity     INT            DEFAULT 1,
    total_price  DECIMAL(10,2)  NOT NULL,
    status       ENUM('pending', 'confirmed', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- ==================
-- ORDERS RENTAL
-- ==================
CREATE TABLE orders_rental (
    id_rental    INT            AUTO_INCREMENT PRIMARY KEY,
    id_user      INT            NOT NULL,
    book_id      VARCHAR(100)   NOT NULL,
    name_rental  VARCHAR(100)   NOT NULL,
    city         VARCHAR(100)   NOT NULL,
    phone_number VARCHAR(15)    NOT NULL,
    rental_months INT           NOT NULL,
    total_price  DECIMAL(10,2)  NOT NULL,
    start_date   DATE           NOT NULL,
    end_date     DATE           NOT NULL,
    status       ENUM('pending', 'active', 'returned', 'cancelled') DEFAULT 'pending',
    created_at   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- ==================
-- CART
-- ==================
CREATE TABLE cart (
    id_cart       INT          AUTO_INCREMENT PRIMARY KEY,
    id_user       INT          NOT NULL,
    book_id       VARCHAR(100) NOT NULL,
    type          ENUM('buy', 'rental') NOT NULL,
    rental_months INT          DEFAULT NULL,
    quantity      INT          DEFAULT 1,
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- ==================
-- BOOKS  ← هذا الجدول كان مفقوداً وهو سبب المشكلة!
-- ==================
CREATE TABLE books (
    book_id     INT            AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255)   NOT NULL,
    author      VARCHAR(150)   NOT NULL,
    category    VARCHAR(100)   DEFAULT NULL,
    description TEXT           DEFAULT NULL,
    cover       VARCHAR(500)   DEFAULT NULL,
    price       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    stock       INT            NOT NULL DEFAULT 0,
    created_at  DATETIME       DEFAULT CURRENT_TIMESTAMP
);

-- ==================
-- ADMIN DEFAULT
-- الباسورد: admin123
-- مشفّر بـ password_hash() لأن PHP تستخدم password_verify()
-- لا يمكن وضع النص العادي هنا — يجب أن يكون hash
-- ==================
INSERT INTO users (name_user, email, password, role) VALUES
('Admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


