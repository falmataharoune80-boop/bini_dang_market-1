<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Activer l'affichage des erreurs pour le débogage (à désactiver en production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isLoggedIn()) {
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

// Récupérer les données envoyées
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['error' => 'Données invalides : ' . $input]);
    exit;
}

$buyer_id = $_SESSION['user_id'];
$products = isset($data['products']) ? $data['products'] : [];
$total = isset($data['total']) ? (float)$data['total'] : 0;
$payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'inconnu';
$transaction_id = isset($data['transaction_id']) ? $data['transaction_id'] : null;

if (empty($products)) {
    echo json_encode(['error' => 'Aucun produit dans la commande']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    foreach ($products as $product) {
        // Vérifier que le produit a un ID valide
        if (!isset($product['id']) || empty($product['id'])) {
            throw new Exception("Produit invalide: ID manquant");
        }
        
        // Récupérer les informations du produit (y compris la quantité)
        $stmt = $pdo->prepare("SELECT seller_id, title, price, quantity FROM products WHERE id = ? AND status = 'available'");
        $stmt->execute([$product['id']]);
        $product_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product_info) {
            throw new Exception("Produit non trouvé ou indisponible: ID " . $product['id']);
        }
        
        // Vérifier la quantité disponible
        if ($product_info['quantity'] <= 0) {
            throw new Exception("Stock insuffisant pour le produit: " . $product_info['title']);
        }
        
        $seller_id = $product_info['seller_id'];
        $product_title = $product_info['title'];
        $product_price = isset($product['price']) ? (float)$product['price'] : (float)$product_info['price'];
        
        // Décrémenter la quantité
        $new_quantity = $product_info['quantity'] - 1;
        $new_status = $new_quantity > 0 ? 'available' : 'sold';
        
        $stmt = $pdo->prepare("UPDATE products SET quantity = ?, status = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $new_status, $product['id']]);
        
        // Générer une référence unique
        $reference = 'CMD-' . date('Ymd') . '-' . uniqid();
        
        // Insérer la commande avec statut 'paid'
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                reference, 
                buyer_id, 
                seller_id, 
                product_id, 
                amount, 
                payment_method, 
                payment_status, 
                status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'paid', 'completed', NOW())
        ");
        $stmt->execute([
            $reference, 
            $buyer_id, 
            $seller_id, 
            $product['id'], 
            $product_price, 
            $payment_method
        ]);
        $order_id = $pdo->lastInsertId();
        
        // Enregistrer la transaction
        if ($transaction_id) {
            $stmt = $pdo->prepare("
                INSERT INTO transactions (order_id, transaction_id, amount, payment_method, status, created_at)
                VALUES (?, ?, ?, ?, 'success', NOW())
            ");
            $stmt->execute([$order_id, $transaction_id, $product_price, $payment_method]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO transactions (order_id, amount, payment_method, status, created_at)
                VALUES (?, ?, ?, 'success', NOW())
            ");
            $stmt->execute([$order_id, $product_price, $payment_method]);
        }
        
        // Ajouter à l'historique d'achat
        $stmt = $pdo->prepare("
            INSERT INTO purchase_history (user_id, order_id, product_id, product_name, unit_price, total_price, purchase_date, status)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 'completed')
        ");
        $stmt->execute([$buyer_id, $order_id, $product['id'], $product_title, $product_price, $product_price]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Commande enregistrée avec succès',
        'order_count' => count($products)
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>