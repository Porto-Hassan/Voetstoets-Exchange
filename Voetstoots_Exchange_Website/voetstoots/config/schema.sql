-- ============================================================
-- Voetstoots Exchange - Database Schema
-- Module: ITECA3-12 | Web Development and E-Commerce
-- ============================================================

CREATE DATABASE IF NOT EXISTS voetstoots_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE voetstoots_db;

-- ------------------------------------------------------------
-- Categories
-- ------------------------------------------------------------
CREATE TABLE categories (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(100) NOT NULL,
    slug      VARCHAR(100) NOT NULL UNIQUE,
    icon      VARCHAR(50)  DEFAULT 'bi-tag'
) ENGINE=InnoDB;

INSERT INTO categories (name, slug, icon) VALUES
('Fresh Produce',       'fresh-produce',    'bi-basket'),
('Homemade Food',       'homemade-food',    'bi-egg-fried'),
('Crafts & Art',        'crafts-art',       'bi-palette'),
('Clothing & Textiles', 'clothing-textiles','bi-scissors'),
('Farming Supplies',    'farming-supplies', 'bi-tools');

-- ------------------------------------------------------------
-- Users (buyers, sellers, admins)
-- ------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(150) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(20)  DEFAULT NULL,
    location      VARCHAR(150) DEFAULT NULL,
    role          ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
    bio           TEXT         DEFAULT NULL,
    profile_img   VARCHAR(255) DEFAULT NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin account (password: Admin@1234)
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Administrator', 'admin@voetstoots.co.za',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ------------------------------------------------------------
-- Listings
-- ------------------------------------------------------------
CREATE TABLE listings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    seller_id     INT          NOT NULL,
    category_id   INT          NOT NULL,
    title         VARCHAR(200) NOT NULL,
    description   TEXT         NOT NULL,
    price         DECIMAL(10,2) NOT NULL,
    quantity      INT          NOT NULL DEFAULT 1,
    location      VARCHAR(150) DEFAULT NULL,
    image         VARCHAR(255) DEFAULT NULL,
    status        ENUM('active','pending','sold','removed') NOT NULL DEFAULT 'pending',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id)   REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)  ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Orders
-- ------------------------------------------------------------
CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id        INT          NOT NULL,
    total_amount    DECIMAL(10,2) NOT NULL,
    status          ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    payment_method  ENUM('simulated','payfast','ozow') NOT NULL DEFAULT 'simulated',
    payment_status  ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
    reference       VARCHAR(50)  NOT NULL UNIQUE,
    delivery_address TEXT        DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Order Items
-- ------------------------------------------------------------
CREATE TABLE order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT           NOT NULL,
    listing_id  INT           NOT NULL,
    quantity    INT           NOT NULL DEFAULT 1,
    unit_price  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Reviews
-- ------------------------------------------------------------
CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    listing_id  INT       NOT NULL,
    buyer_id    INT       NOT NULL,
    rating      TINYINT   NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT      DEFAULT NULL,
    created_at  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id)   REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;
