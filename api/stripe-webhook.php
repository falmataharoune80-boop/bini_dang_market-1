<?php
/**
 * Bini-Dang Market - Webhook Stripe
 * Écoute les événements Stripe pour mettre à jour la base de données après un paiement.
 */

require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // Assurez-vous que le SDK Stripe est installé

// 🔐 Configuration (à placer dans votre .env ou config.php)
$endpoint_secret = 'whsec_VOTRE_SECRET_WEBHOOK'; // À remplacer par votre clé

// 📡 Récupération du payload et de la signature
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
$event = null;

// ✅ Vérification de la signature (sécurité indispensable)
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    // Payload invalide
    http_response_code(400);
    error_log("Webhook Error: Invalid payload - " . $e->getMessage());
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Signature invalide
    http_response_code(400);
    error_log("Webhook Error: Invalid signature - " . $e->getMessage());
    exit();
}

// 🗄️ Gestion des événements
switch ($event->type) {
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object;
        handleSuccessfulPayment($paymentIntent, $pdo);
        break;

    case 'checkout.session.completed':
        $session = $event->data->object;
        handleCheckoutSessionCompleted($session, $pdo);
        break;

    // Vous pouvez ajouter d'autres événements si nécessaire
    default:
        // Événement non géré
        http_response_code(200);
        echo "Événement non géré : " . $event->type;
}

/**
 * Traite un paiement réussi (payment_intent.succeeded)
 */
function handleSuccessfulPayment($paymentIntent, $pdo) {
    // Récupération des métadonnées
    $orderId = $paymentIntent->metadata->order_id ?? null;
    $transactionId = $paymentIntent->id;

    if (!$orderId) {
        error_log("Webhook: Aucun order_id dans les métadonnées pour PI: $transactionId");
        return;
    }

    // Mise à jour de la transaction dans la base de données
    $stmt = $pdo->prepare("
        UPDATE transactions 
        SET status = 'success', 
            stripe_payment_intent_id = ?, 
            payment_date = NOW() 
        WHERE stripe_payment_intent_id = ? OR reference = ?
    ");
    $stmt->execute([$transactionId, $transactionId, $orderId]);

    if ($stmt->rowCount() > 0) {
        // Récupérer les informations de la transaction pour mettre à jour la commande
        $stmt2 = $pdo->prepare("SELECT order_id FROM transactions WHERE stripe_payment_intent_id = ?");
        $stmt2->execute([$transactionId]);
        $transaction = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            // Marquer la commande comme complétée
            $stmt3 = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmt3->execute([$transaction['order_id']]);

            // Ajouter à l'historique des achats
            addToPurchaseHistory($transaction['order_id'], $pdo);
        }

        http_response_code(200);
        echo "Transaction et commande mises à jour avec succès.";
    } else {
        http_response_code(500);
        echo "Erreur : Transaction non trouvée.";
    }
}

/**
 * Traite la finalisation d'une session Checkout (checkout.session.completed)
 */
function handleCheckoutSessionCompleted($session, $pdo) {
    $orderId = $session->metadata->order_id ?? null;
    $customerId = $session->customer ?? null;
    $paymentIntentId = $session->payment_intent ?? null;

    if (!$orderId) {
        error_log("Webhook: Aucun order_id dans les métadonnées pour la session: " . $session->id);
        return;
    }

    // Mise à jour de la commande
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'completed', 
            stripe_payment_intent = ? 
        WHERE id = ?
    ");
    $stmt->execute([$paymentIntentId, $orderId]);

    // Mise à jour ou création de la transaction
    $stmt2 = $pdo->prepare("
        INSERT INTO transactions (reference, order_id, buyer_id, seller_id, amount, payment_method, stripe_payment_intent_id, status, payment_date)
        SELECT ?, id, buyer_id, seller_id, amount, 'stripe', ?, 'success', NOW()
        FROM orders WHERE id = ?
        ON DUPLICATE KEY UPDATE 
            status = 'success', 
            stripe_payment_intent_id = ?, 
            payment_date = NOW()
    ");
    $reference = 'TX-' . time() . '-' . $orderId;
    $stmt2->execute([$reference, $paymentIntentId, $orderId, $paymentIntentId]);

    // Ajout à l'historique des achats
    addToPurchaseHistory($orderId, $pdo);

    // Optionnel : Mettre à jour les méthodes de paiement de l'utilisateur
    if ($customerId) {
        $stmt3 = $pdo->prepare("
            UPDATE users 
            SET stripe_customer_id = ? 
            WHERE id = (SELECT buyer_id FROM orders WHERE id = ?)
        ");
        $stmt3->execute([$customerId, $orderId]);
    }

    http_response_code(200);
    echo "Session Checkout traitée avec succès.";
}

/**
 * Ajoute un achat à l'historique de l'utilisateur
 */
function addToPurchaseHistory($orderId, $pdo) {
    $stmt = $pdo->prepare("
        INSERT INTO purchase_history (user_id, order_id, product_id, product_name, unit_price, total_price, purchase_date, status)
        SELECT o.buyer_id, o.id, p.id, p.title, o.amount, o.amount, NOW(), 'completed'
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.id = ? AND o.status = 'completed'
        ON DUPLICATE KEY UPDATE status = 'completed'
    ");
    $stmt->execute([$orderId]);
}

// Réponse HTTP par défaut
http_response_code(200);
echo "Webhook reçu et traité.";