DROP DATABASE IF EXISTS library;
CREATE DATABASE library;
USE library;

-- ==================
-- USERS
-- ==================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    name_user VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(250) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ==================

-- ==================
CREATE TABLE orders_buy (
    id_buy INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    book_id VARCHAR(100) NOT NULL,
    name_buy VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    quantity INT DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- ==================
-- ORDERS - كراء
-- ==================
CREATE TABLE orders_rental (
    id_rental INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    book_id VARCHAR(100) NOT NULL,
    name_rental VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    rental_months INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'active', 'returned', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- ==================
-- CART
-- ==================
CREATE TABLE cart (
    id_cart INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    book_id VARCHAR(100) NOT NULL,
    type ENUM('buy', 'rental') NOT NULL,
    rental_months INT DEFAULT NULL,
    quantity INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- ==================
-- ADMIN DEFAULT
-- ==================
INSERT INTO users (name_user, email, password, role) VALUES
('Admin', 'admin@library.com', SHA2('admin123', 256), 'admin');