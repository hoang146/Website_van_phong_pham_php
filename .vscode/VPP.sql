-- Tạo database (nếu chưa có)
CREATE DATABASE IF NOT EXISTS vanphongpham CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vanphongpham;

-- Xóa bảng nếu tồn tại (tránh lỗi khi chạy lại)
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Bảng users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    fullname VARCHAR(100),
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Bảng products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

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

-- Dữ liệu mẫu
INSERT INTO users (username, password, email, fullname, role)
VALUES 
('admin', MD5('123456'), 'admin@example.com', 'Quản trị viên', 'admin'),
('user1', MD5('123456'), 'user1@example.com', 'Người dùng 1', 'user');

INSERT INTO categories (name, description)
VALUES 
('Bút', 'Các loại bút bi, bút chì, bút mực'),
('Vở', 'Sổ, tập, vở học sinh'),
('Thước', 'Thước kẻ, eke, compa');

INSERT INTO products (category_id, name, price, description, image, stock)
VALUES 
(1, 'Bút bi Thiên Long', 5000, 'Bút bi xanh TL-027', 'butbi.jpg', 100),
(1, 'Bút chì 2B', 3000, 'Bút chì gỗ 2B', 'butchi.jpg', 200),
(2, 'Vở 200 trang', 12000, 'Vở kẻ ngang 200 trang', 'vo200.jpg', 50),
(3, 'Thước kẻ 20cm', 4000, 'Thước nhựa 20cm', 'thuoc20.jpg', 80);

UPDATE users SET role = 'admin' WHERE username = 'uio';