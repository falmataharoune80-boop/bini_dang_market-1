<?php
require_once '../includes/config.php';
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO payment_methods (user_id, type, account_identifier) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $data['type'], $data['identifier']]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    echo json_encode(['success' => true]);
    exit;
}
?>