-- ============================================
-- database.sql
-- Online Auction System — Schema + Test Data
-- ============================================

CREATE DATABASE IF NOT EXISTS online_auction_system;
USE online_auction_system;

-- ---- TABLES ----

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'buyer',
    seller_verified TINYINT(1) NOT NULL DEFAULT 0,
    bio TEXT,
    phone VARCHAR(30),
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    starting_price DECIMAL(10,2) NOT NULL,
    reserve_price DECIMAL(10,2) NOT NULL,
    current_bid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image_path VARCHAR(255) NOT NULL DEFAULT 'placeholder.jpg',
    end_datetime DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    winner_bid_id INT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (listing_id) REFERENCES listings(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS seller_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    motivation TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ---- CATEGORIES ----

INSERT INTO categories(name)
SELECT 'Electronics' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name='Electronics');
INSERT INTO categories(name)
SELECT 'Collectibles' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name='Collectibles');
INSERT INTO categories(name)
SELECT 'Furniture' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name='Furniture');
INSERT INTO categories(name)
SELECT 'Clothing' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name='Clothing');

-- ---- USERS ----
-- Passwords are all: admin12345  (hash: $2y$12$biVVfbQdYPa178ym5xeMceogMWwTwvjNjpXtPtEyir7Nj.BnQC226)
-- For test users below the password is: password123

-- Admin
INSERT INTO users(name,email,password_hash,role,seller_verified,bio,phone,created_at)
SELECT 'Admin','admin@example.com','$2y$12$biVVfbQdYPa178ym5xeMceogMWwTwvjNjpXtPtEyir7Nj.BnQC226','admin',1,'System admin','000',NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='admin@example.com');

-- Verified Seller 1
INSERT INTO users(name,email,password_hash,role,seller_verified,bio,phone,created_at)
SELECT 'John Seller','seller@example.com','$2y$12$biVVfbQdYPa178ym5xeMceogMWwTwvjNjpXtPtEyir7Nj.BnQC226','buyer',1,'Selling electronics and collectibles','01712345678',DATE_SUB(NOW(), INTERVAL 30 DAY)
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='seller@example.com');

-- Verified Seller 2
INSERT INTO users(name,email,password_hash,role,seller_verified,bio,phone,created_at)
SELECT 'Maria Shop','maria@example.com','$2y$12$biVVfbQdYPa178ym5xeMceogMWwTwvjNjpXtPtEyir7Nj.BnQC226','buyer',1,'Furniture & home goods','01898765432',DATE_SUB(NOW(), INTERVAL 20 DAY)
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='maria@example.com');

-- Buyer 1
INSERT INTO users(name,email,password_hash,role,seller_verified,bio,phone,created_at)
SELECT 'Sarah Buyer','buyer@example.com','$2y$12$biVVfbQdYPa178ym5xeMceogMWwTwvjNjpXtPtEyir7Nj.BnQC226','buyer',0,'Regular buyer','01611111111',DATE_SUB(NOW(), INTERVAL 15 DAY)
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='buyer@example.com');

-- Buyer 2
INSERT INTO users(name,email,password_hash,role,seller_verified,bio,phone,created_at)
SELECT 'Ahmed Khan','ahmed@example.com','$2y$12$biVVfbQdYPa178ym5xeMceogMWwTwvjNjpXtPtEyir7Nj.BnQC226','buyer',0,'Tech enthusiast','01522222222',DATE_SUB(NOW(), INTERVAL 10 DAY)
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='ahmed@example.com');

-- Pending seller request user
INSERT INTO users(name,email,password_hash,role,seller_verified,bio,phone,created_at)
SELECT 'Pending User','pending@example.com','$2y$12$biVVfbQdYPa178ym5xeMceogMWwTwvjNjpXtPtEyir7Nj.BnQC226','buyer',0,'Wants to sell stuff','01533333333',DATE_SUB(NOW(), INTERVAL 5 DAY)
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='pending@example.com');

-- ---- LISTINGS (test data) ----
-- We use conditional inserts so re-running the file is safe.
-- For simplicity, the test listings below assume user IDs 2,3 are sellers and 4,5 are buyers.
-- Adjust IDs if your auto-increment differs.

-- EXPIRED listing (should be closed by the cron / closeExpiredAuctions)
-- Reserve MET — buyer 4 wins
INSERT INTO listings(seller_id,category_id,title,description,starting_price,reserve_price,current_bid,image_path,end_datetime,status,winner_bid_id,created_at)
SELECT
    (SELECT id FROM users WHERE email='seller@example.com'),
    (SELECT id FROM categories WHERE name='Electronics'),
    'iPhone 13 Pro — 256GB',
    'Used iPhone 13 Pro in excellent condition. Comes with original charger and box.',
    300.00, 400.00, 0.00,
    'iphone13.jpg',
    DATE_SUB(NOW(), INTERVAL 1 DAY),   -- expired yesterday
    'active',                           -- still active so closeExpiredAuctions will close it
    NULL,
    DATE_SUB(NOW(), INTERVAL 7 DAY)
WHERE NOT EXISTS (SELECT 1 FROM listings WHERE title='iPhone 13 Pro — 256GB');

-- Bids for listing above (we'll reference them by subquery after insert)
-- NOTE: run closeExpiredAuctions once after seeding to set winner_bid_id automatically.
INSERT INTO bids(listing_id,buyer_id,amount,created_at)
SELECT
    (SELECT id FROM listings WHERE title='iPhone 13 Pro — 256GB'),
    (SELECT id FROM users WHERE email='buyer@example.com'),
    350.00,
    DATE_SUB(NOW(), INTERVAL 6 DAY)
WHERE NOT EXISTS (
    SELECT 1 FROM bids WHERE listing_id=(SELECT id FROM listings WHERE title='iPhone 13 Pro — 256GB')
      AND buyer_id=(SELECT id FROM users WHERE email='buyer@example.com')
      AND amount=350.00
);

INSERT INTO bids(listing_id,buyer_id,amount,created_at)
SELECT
    (SELECT id FROM listings WHERE title='iPhone 13 Pro — 256GB'),
    (SELECT id FROM users WHERE email='ahmed@example.com'),
    420.00,
    DATE_SUB(NOW(), INTERVAL 5 DAY)
WHERE NOT EXISTS (
    SELECT 1 FROM bids WHERE listing_id=(SELECT id FROM listings WHERE title='iPhone 13 Pro — 256GB')
      AND buyer_id=(SELECT id FROM users WHERE email='ahmed@example.com')
      AND amount=420.00
);

INSERT INTO bids(listing_id,buyer_id,amount,created_at)
SELECT
    (SELECT id FROM listings WHERE title='iPhone 13 Pro — 256GB'),
    (SELECT id FROM users WHERE email='buyer@example.com'),
    450.00,
    DATE_SUB(NOW(), INTERVAL 4 DAY)
WHERE NOT EXISTS (
    SELECT 1 FROM bids WHERE listing_id=(SELECT id FROM listings WHERE title='iPhone 13 Pro — 256GB')
      AND buyer_id=(SELECT id FROM users WHERE email='buyer@example.com')
      AND amount=450.00
);

-- EXPIRED listing — reserve NOT met
INSERT INTO listings(seller_id,category_id,title,description,starting_price,reserve_price,current_bid,image_path,end_datetime,status,winner_bid_id,created_at)
SELECT
    (SELECT id FROM users WHERE email='maria@example.com'),
    (SELECT id FROM categories WHERE name='Furniture'),
    'Antique Oak Dining Table',
    'Beautiful antique oak dining table, seats 8. Some wear consistent with age.',
    200.00, 800.00, 0.00,
    'table.jpg',
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    'active',
    NULL,
    DATE_SUB(NOW(), INTERVAL 10 DAY)
WHERE NOT EXISTS (SELECT 1 FROM listings WHERE title='Antique Oak Dining Table');

INSERT INTO bids(listing_id,buyer_id,amount,created_at)
SELECT
    (SELECT id FROM listings WHERE title='Antique Oak Dining Table'),
    (SELECT id FROM users WHERE email='buyer@example.com'),
    250.00,
    DATE_SUB(NOW(), INTERVAL 3 DAY)
WHERE NOT EXISTS (
    SELECT 1 FROM bids WHERE listing_id=(SELECT id FROM listings WHERE title='Antique Oak Dining Table')
      AND buyer_id=(SELECT id FROM users WHERE email='buyer@example.com')
      AND amount=250.00
);

-- ACTIVE listing (ends in future)
INSERT INTO listings(seller_id,category_id,title,description,starting_price,reserve_price,current_bid,image_path,end_datetime,status,winner_bid_id,created_at)
SELECT
    (SELECT id FROM users WHERE email='seller@example.com'),
    (SELECT id FROM categories WHERE name='Electronics'),
    'Sony PlayStation 5 Console',
    'Brand new PS5 disc edition, sealed in box. Ships same day.',
    400.00, 450.00, 0.00,
    'ps5.jpg',
    DATE_ADD(NOW(), INTERVAL 3 DAY),
    'active',
    NULL,
    DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE NOT EXISTS (SELECT 1 FROM listings WHERE title='Sony PlayStation 5 Console');

INSERT INTO bids(listing_id,buyer_id,amount,created_at)
SELECT
    (SELECT id FROM listings WHERE title='Sony PlayStation 5 Console'),
    (SELECT id FROM users WHERE email='ahmed@example.com'),
    410.00,
    DATE_SUB(NOW(), INTERVAL 12 HOUR)
WHERE NOT EXISTS (
    SELECT 1 FROM bids WHERE listing_id=(SELECT id FROM listings WHERE title='Sony PlayStation 5 Console')
      AND buyer_id=(SELECT id FROM users WHERE email='ahmed@example.com')
      AND amount=410.00
);

-- ACTIVE listing with no bids
INSERT INTO listings(seller_id,category_id,title,description,starting_price,reserve_price,current_bid,image_path,end_datetime,status,winner_bid_id,created_at)
SELECT
    (SELECT id FROM users WHERE email='maria@example.com'),
    (SELECT id FROM categories WHERE name='Collectibles'),
    'Rare 1960s Vinyl Record Collection',
    '10 original vinyl records from the 1960s including Beatles, Rolling Stones. All in excellent condition.',
    100.00, 150.00, 0.00,
    'vinyl.jpg',
    DATE_ADD(NOW(), INTERVAL 5 DAY),
    'active',
    NULL,
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM listings WHERE title='Rare 1960s Vinyl Record Collection');

-- SELLER REQUEST for pending user
INSERT INTO seller_requests(user_id,motivation,status,created_at)
SELECT
    (SELECT id FROM users WHERE email='pending@example.com'),
    'I want to sell my old electronics collection. I have been a buyer for a year and am trustworthy.',
    'pending',
    DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE NOT EXISTS (
    SELECT 1 FROM seller_requests WHERE user_id=(SELECT id FROM users WHERE email='pending@example.com')
);
