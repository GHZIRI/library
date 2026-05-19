-- ==================
-- BOOKS TABLE
-- ==================
CREATE TABLE IF NOT EXISTS books (
    id_book      INT            AUTO_INCREMENT PRIMARY KEY,
    book_id      VARCHAR(100)   NOT NULL UNIQUE,  -- Google Books ID or custom
    title        VARCHAR(255)   NOT NULL,
    author       VARCHAR(255),
    description  LONGTEXT,
    cover_image  VARCHAR(500),
    language     VARCHAR(10)    DEFAULT 'ar',
    published_date VARCHAR(10),
    publisher    VARCHAR(100),
    price_buy    DECIMAL(10,2)  DEFAULT 0,
    price_rental DECIMAL(10,2)  DEFAULT 0,
    category     VARCHAR(100),
    rating       DECIMAL(3,1)   DEFAULT 0,
    created_at   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    INDEX (category),
    INDEX (title)
);

-- ==================
-- Sample Arabic Books
-- ==================
INSERT INTO books (book_id, title, author, cover_image, price_buy, price_rental, category) VALUES
('1', 'الحرب والسلام', 'ليو تولستوي', 'https://via.placeholder.com/150?text=War+and+Peace', 45.00, 5.00, 'أدب'),
('2', '1984', 'جورج أورويل', 'https://via.placeholder.com/150?text=1984', 35.00, 4.00, 'خيال'),
('3', 'الفهرست', 'ابن النديم', 'https://via.placeholder.com/150?text=Al-Fihrist', 50.00, 6.00, 'تراث'),
('4', 'مقدمة ابن خلدون', 'ابن خلدون', 'https://via.placeholder.com/150?text=Muqaddimah', 40.00, 5.00, 'فلسفة'),
('5', 'ألف ليلة وليلة', 'مجهول', 'https://via.placeholder.com/150?text=Arabian+Nights', 55.00, 7.00, 'أدب');
