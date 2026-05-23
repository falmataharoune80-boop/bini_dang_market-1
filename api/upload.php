<?php
require_once '../includes/config.php';
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

$target_dir = "../uploads/";
if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $filename = time() . '_' . uniqid() . '_' . basename($_FILES['image']['name']);
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        echo json_encode(['url' => '/uploads/' . $filename]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Upload failed']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No image uploaded']);
}
?>