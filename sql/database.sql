-- =====================================================
-- Bini-Dang Market - Base de données complète
-- =====================================================

DROP DATABASE IF EXISTS bini_dang_market;
CREATE DATABASE bini_dang_market;
USE bini_dang_market;

-- 1. Utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(100) DEFAULT 'Bini-Dang',
    profile_picture VARCHAR(255) DEFAULT '/uploads/avatar-default.jpg',
    payment_preference ENUM('cash', 'orange_money', 'stripe') DEFAULT 'cash',
    role ENUM('user', 'admin') DEFAULT 'user',
    rating DECIMAL(2,1) DEFAULT 0,
    num_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Catégories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) DEFAULT 'store',
    is_active BOOLEAN DEFAULT TRUE
);

-- 3. Produits
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'sold', 'reserved') DEFAULT 'available',
    original_price DECIMAL(10,2),
    category_id INT,
    seller_id INT NOT NULL,
    location VARCHAR(100) DEFAULT 'Bini-Dang',
    is_flash_sale BOOLEAN DEFAULT FALSE,
    flash_sale_discount INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 0,
    num_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Images des produits
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 5. Messages
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- 6. Évaluations
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    reviewed_user_id INT NOT NULL,
    product_id INT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewer_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- 7. Commandes
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(100) UNIQUE,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'orange_money', 'stripe') NOT NULL,
    stripe_payment_intent VARCHAR(255),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    delivery_method ENUM('pickup', 'delivery') DEFAULT 'pickup',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 8. Méthodes de paiement
CREATE TABLE payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('orange_money', 'stripe', 'cash') NOT NULL,
    account_identifier VARCHAR(255),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 9. Transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(100) UNIQUE NOT NULL,
    order_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('orange_money', 'stripe', 'cash') NOT NULL,
    stripe_payment_intent_id VARCHAR(255) NULL,
    orange_money_transaction_id VARCHAR(255) NULL,
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
);

-- 10. Historique achats
CREATE TABLE purchase_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    purchase_date TIMESTAMP NOT NULL,
    status VARCHAR(50) DEFAULT 'completed',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =====================================================
-- DONNÉES DE DÉMONSTRATION
-- =====================================================

INSERT INTO categories (name, icon) VALUES
('Women\'s Fashion', 'tshirt'),
('Men\'s Fashion', 'tshirt'),
('Electronics', 'microchip'),
('Home & Lifestyle', 'home'),
('Medicine', 'capsules'),
('Sports & Outdoor', 'futbol'),
('Baby\'s & Toys', 'baby'),
('Groceries & Pets', 'cart-shopping'),
('Health & Beauty', 'spa'),
('Phones', 'mobile-alt'),
('Computers', 'laptop'),
('SmartWatch', 'clock'),
('Camera', 'camera'),
('Headphones', 'headphones'),
('Gaming', 'gamepad');

-- Admin (mot de passe: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Administrateur', 'admin@bini.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Vendeur (mot de passe: password123)
INSERT INTO users (name, email, password, phone, location) VALUES
('Jean Dupont', 'jean@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+237 612345678', 'Bini-Dang');

-- Acheteur (mot de passe: password123)
INSERT INTO users (name, email, password, phone, location) VALUES
('Marie Claire', 'marie@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+237 698765432', 'Bini-Dang');

-- Produits
INSERT INTO products (title, description, price, category_id, seller_id, status, rating) VALUES
('Robe de soirée', 'Robe élégante pour soirée', 45000, (SELECT id FROM categories WHERE name='Women\'s Fashion'), 2, 'available', 4.5),
('Sac à main en cuir', 'Sac femme en cuir véritable', 35000, (SELECT id FROM categories WHERE name='Women\'s Fashion'), 2, 'available', 4.7),
('Costume 3 pièces', 'Costume homme luxe', 85000, (SELECT id FROM categories WHERE name='Men\'s Fashion'), 2, 'available', 4.8),
('iPhone 13', '128Go reconditionné', 450000, (SELECT id FROM categories WHERE name='Phones'), 2, 'available', 4.9),
('Ordinateur portable', '8Go RAM, 256Go SSD', 550000, (SELECT id FROM categories WHERE name='Computers'), 2, 'available', 4.7),
('Montre connectée', 'Suivi cardiaque', 45000, (SELECT id FROM categories WHERE name='SmartWatch'), 2, 'available', 4.6),
('Casque Bluetooth', 'Casque sans fil', 25000, (SELECT id FROM categories WHERE name='Electronics'), 2, 'available', 4.6);

-- Images
INSERT INTO product_images (product_id, image_url) VALUES
(1, '/uploads/robe.jpg'),
(2, '/uploads/sac.jpg'),
(3, '/uploads/costume.jpg'),
(4, '/uploads/iphone.jpg'),
(5, '/uploads/ordi.jpg'),
(6, '/uploads/montre.jpg'),
(7, '/uploads/casque.jpg');