<?php
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser($id = null) {
    global $pdo;
    $id = $id ?? $_SESSION['user_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProducts($limit = 8) {
    global $pdo;
    $limit = (int)$limit;
    $sql = "
        SELECT p.*, 
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
        FROM products p
        WHERE p.status = 'available'
        ORDER BY p.created_at DESC
        LIMIT $limit
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addProduct($data, $images) {
    global $pdo;
    
    $title = $data['title'];
    $description = $data['description'];
    $price = $data['price'];
    $original_price = $data['is_flash_sale'] ? $data['price'] : null;
    $category_id = $data['category_id'];
    $seller_id = $_SESSION['user_id'];
    $location = $data['location'];
    $is_flash_sale = $data['is_flash_sale'] ? 1 : 0;
    $flash_sale_discount = $data['flash_sale_discount'] ?? 0;
    
    // Appliquer la réduction si vente flash
    if ($is_flash_sale && $flash_sale_discount > 0) {
        $price = $data['price'] * (1 - $flash_sale_discount / 100);
    }
    
    $stmt = $pdo->prepare("INSERT INTO products (title, description, price, original_price, category_id, seller_id, location, is_flash_sale, flash_sale_discount)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $price, $original_price, $category_id, $seller_id, $location, $is_flash_sale, $flash_sale_discount]);
    
    $productId = $pdo->lastInsertId();
    
    // Insérer les images
    foreach ($images as $imgUrl) {
        $stmt2 = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        $stmt2->execute([$productId, $imgUrl]);
    }
    
    return $productId;
}
?>
