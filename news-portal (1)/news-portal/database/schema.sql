-- ============================================
-- News Portal Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS news_portal
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE news_portal;

-- ---------------------------
-- Admins table
-- ---------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- stores password_hash() output
    full_name VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------
-- Categories table
-- ---------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------
-- News articles table
-- ---------------------------
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    summary VARCHAR(500) DEFAULT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,      -- filename stored in /uploads
    category_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'published',
    views INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------
-- Seed data
-- ---------------------------

-- NOTE: No admin account is created here on purpose.
-- Run setup/create_admin.php once in your browser after importing this
-- schema — it uses PHP's own password_hash() function to create the
-- first admin account safely (a hash typed by hand elsewhere would not
-- reliably match your PHP install's bcrypt settings).

INSERT INTO categories (name) VALUES
('Politics'), ('Business'), ('Technology'), ('Sports'), ('Entertainment')
ON DUPLICATE KEY UPDATE name = name;
