<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

try {
    if (isset($_GET['all'])) {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
            FROM products p
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    
    if (isset($_GET['ids'])) {
        $ids = explode(',', $_GET['ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("
            SELECT p.id, p.title, p.price,
                   (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
            FROM products p
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($ids);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>