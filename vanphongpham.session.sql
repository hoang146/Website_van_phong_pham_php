-- Tạo database
CREATE DATABASE IF NOT EXISTS vanphongpham CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vanphongpham;

-- Xóa bảng nếu tồn tại
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS product_tags;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS user_cart;

-- Bảng users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    fullname VARCHAR(100),
    phone VARCHAR(20),
    address VARCHAR(255),
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Bảng tags
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL UNIQUE,
    brand VARCHAR(100),
    origin VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    price_sale DECIMAL(10,2) DEFAULT NULL,
    stock INT DEFAULT 0,
    warranty_days INT DEFAULT 0,  -- số ngày bảo hành
    return_days INT DEFAULT 0,    -- số ngày đổi trả
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng ảnh sản phẩm (1-n)
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_path VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng quan hệ N-N giữa sản phẩm và tag
CREATE TABLE product_tags (
    product_id INT,
    tag_id INT,
    PRIMARY KEY (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);


-- Bảng orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    shipping_name VARCHAR(100),
    payment_method VARCHAR(50),
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','paid','shipped','completed','cancelled') DEFAULT 'pending',
    shipping_address VARCHAR(255),
    shipping_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Nếu đã có bảng orders, thêm cột payment_method và giá trị ENUM mới bằng ALTER TABLE
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50);
ALTER TABLE orders MODIFY COLUMN status ENUM('pending','processing','paid','shipped','completed','cancelled') DEFAULT 'pending';

-- Bảng order_items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng cart
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng lưu giỏ hàng cho từng user
CREATE TABLE IF NOT EXISTS user_cart (
    user_id INT NOT NULL,
    cart_data TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
);

-- Dữ liệu mẫu
-- Users
INSERT INTO users (username, password, email, fullname, phone, address, role)
VALUES ('admin', MD5('123456'), 'admin@example.com', 'Quản trị viên', '0123456789', '123 Admin Street', 'admin')
ON DUPLICATE KEY UPDATE username = VALUES(username);

INSERT INTO users (username, password, email, fullname, phone, address, role)
VALUES ('user1', MD5('123456'), 'user1@example.com', 'Người dùng 1', '0987654321', '456 User Street', 'user')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Categories
INSERT INTO categories (name, description)
VALUES ('Bút', 'Các loại bút bi, bút chì, bút mực')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, description)
VALUES ('Vở', 'Sổ, tập, vở học sinh')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, description)
VALUES ('Thước', 'Thước kẻ, eke, compa')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, description)
VALUES ('Dụng cụ khác', 'Kéo, hồ dán, bấm kim...')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Tags
INSERT INTO tags (name) VALUES 
('Khuyến mãi'), 
('Bán chạy'), 
('Mới về'), 
('Hot')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Kiểm tra
SELECT * FROM users;
SELECT * FROM categories;
SELECT * FROM products;

ALTER TABLE orders ADD COLUMN shipping_name VARCHAR(100) AFTER user_id;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL;

ALTER TABLE orders
  ADD COLUMN vnpay_transaction_id VARCHAR(100) DEFAULT NULL,
  ADD COLUMN payment_time DATETIME DEFAULT NULL;

ALTER TABLE users ADD COLUMN pin_code VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN auth_secret VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN is_2fa_enabled TINYINT DEFAULT 0;
