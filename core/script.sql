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
   created_at DATETIME DEFAULT CURRENT_TIMESTAMP  

);


CREATE TABLE books (
    id_book INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100),
    price_buy DECIMAL(10,2),
    price_rent DECIMAL(10,2),
    stock INT DEFAULT 0,
    image VARCHAR(255) 
)

-- ==================

-- ==================
CREATE TABLE orders_buy (
    id_buy INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    book_id VARCHAR(100) NOT NULL,
    name_buy VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
   phone_number VARCHAR(15) NOT NULL,
    book_id INT NOT NULL,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users (id_user),
   created_at DATETIME DEFAULT CURRENT_TIMESTAMP  

);

CREATE TABLE orders_Rental (
    id_rental INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    book_id VARCHAR(100) NOT NULL,
    name_rental VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    book_id INT NOT NULL,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users (id_user),
   created_at DATETIME DEFAULT CURRENT_TIMESTAMP  

);