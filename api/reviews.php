<?php
require_once '../includes/config.php';
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewed_user_id = (int)$_POST['reviewed_user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    $stmt = $pdo->prepare("INSERT INTO reviews (reviewer_id, reviewed_user_id, product_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $reviewed_user_id, $product_id, $rating, $comment]);
    
    $avg = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE reviewed_user_id = ?");
    $avg->execute([$reviewed_user_id]);
    $new_rating = $avg->fetchColumn();
    
    $pdo->prepare("UPDATE users SET rating = ?, num_reviews = num_reviews + 1 WHERE id = ?")->execute([$new_rating, $reviewed_user_id]);
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>