<?php
require_once '../includes/config.php';
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$product_id = (int)$data['product_id'];
$payment_method = $data['payment_method'] ?? 'stripe';

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

$reference = 'CMD-' . date('Ymd') . '-' . rand(1000, 9999);
$stmt = $pdo->prepare("INSERT INTO orders (reference, buyer_id, seller_id, product_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$stmt->execute([$reference, $_SESSION['user_id'], $product['seller_id'], $product_id, $product['price'], $payment_method]);
$order_id = $pdo->lastInsertId();

$tx_ref = 'TX-' . time() . '-' . $order_id;
$stmt = $pdo->prepare("INSERT INTO transactions (reference, order_id, buyer_id, seller_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$stmt->execute([$tx_ref, $order_id, $_SESSION['user_id'], $product['seller_id'], $product['price'], $payment_method]);

if ($payment_method === 'stripe') {
    // Simuler Stripe (en production, appeler l'API Stripe ici)
    echo json_encode(['clientSecret' => 'simulated_client_secret', 'order_id' => $order_id]);
} else {
    $pdo->prepare("UPDATE transactions SET status = 'success', payment_date = NOW() WHERE id = ?")->execute([$pdo->lastInsertId()]);
    $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?")->execute([$order_id]);
    echo json_encode(['success' => true, 'order_id' => $order_id]);
}
?>