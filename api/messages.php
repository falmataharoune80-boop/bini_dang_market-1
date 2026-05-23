<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

// Le reste du code...

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Récupérer les conversations
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'conversations') {
    try {
        $sql = "SELECT DISTINCT 
                    CASE WHEN sender_id = $user_id THEN receiver_id ELSE sender_id END as other_user_id,
                    u.name
                FROM messages m
                JOIN users u ON u.id = CASE WHEN sender_id = $user_id THEN receiver_id ELSE sender_id END
                WHERE sender_id = $user_id OR receiver_id = $user_id
                ORDER BY m.created_at DESC";
        
        $result = $pdo->query($sql);
        $conversations = $result->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($conversations as &$conv) {
            $lastSql = "SELECT message FROM messages 
                       WHERE (sender_id = $user_id AND receiver_id = {$conv['other_user_id']}) 
                          OR (sender_id = {$conv['other_user_id']} AND receiver_id = $user_id)
                       ORDER BY created_at DESC LIMIT 1";
            $lastResult = $pdo->query($lastSql);
            $last = $lastResult->fetch();
            $conv['last_message'] = $last ? $last['message'] : '';
            
            $unreadSql = "SELECT COUNT(*) FROM messages 
                         WHERE sender_id = {$conv['other_user_id']} AND receiver_id = $user_id AND is_read = 0";
            $conv['unread_count'] = (int)$pdo->query($unreadSql)->fetchColumn();
        }
        
        echo json_encode($conversations);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Récupérer les messages avec un utilisateur
if ($method === 'GET' && isset($_GET['with'])) {
    $with = (int)$_GET['with'];
    try {
        // Marquer comme lus
        $pdo->query("UPDATE messages SET is_read = 1 WHERE sender_id = $with AND receiver_id = $user_id");
        
        $sql = "SELECT * FROM messages 
                WHERE (sender_id = $user_id AND receiver_id = $with) 
                   OR (sender_id = $with AND receiver_id = $user_id)
                ORDER BY created_at ASC";
        $result = $pdo->query($sql);
        $messages = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($messages);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Envoyer un message
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $receiver_id = (int)($data['receiver_id'] ?? 0);
    $message = trim($data['message'] ?? '');
    
    if ($receiver_id == 0) {
        echo json_encode(['error' => 'Destinataire invalide']);
        exit;
    }
    
    if (empty($message)) {
        echo json_encode(['error' => 'Message vide']);
        exit;
    }
    
    $message_safe = addslashes($message);
    
    try {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) 
                VALUES ($user_id, $receiver_id, '$message_safe', 0, NOW())";
        $pdo->query($sql);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Action non reconnue']);
?>