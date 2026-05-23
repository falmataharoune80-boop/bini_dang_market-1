<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user = getUser($_SESSION['user_id']);
if ($user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Statistiques générales
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_products_sold = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'sold'")->fetchColumn();

// Ventes par mode de paiement
$payment_stats = $pdo->query("
    SELECT payment_method, COUNT(*) as count, SUM(amount) as total 
    FROM orders WHERE payment_status = 'paid' 
    GROUP BY payment_method
")->fetchAll();

// Dernières commandes
$recent_orders = $pdo->query("
    SELECT o.*, p.title as product_name, buyer.name as buyer_name, seller.name as seller_name
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users buyer ON o.buyer_id = buyer.id
    JOIN users seller ON o.seller_id = seller.id
    ORDER BY o.created_at DESC
    LIMIT 20
")->fetchAll();

include 'includes/header.php';
?>

<style>
    .stats-dashboard {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: white;
        padding: 1rem;
        border-radius: 0.5rem;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .stat-card h3 { font-size: 1.8rem; margin-bottom: 0.5rem; }
    .orders-table { width: 100%; background: white; border-collapse: collapse; }
    .orders-table th, .orders-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
    .orders-table th { background: #f9fafb; }
    .status-paid { color: #16a34a; font-weight: bold; }
    .status-pending { color: #f97316; font-weight: bold; }
</style>

<div style="max-width: 1200px; margin: 2rem auto;">
    <h1>📊 Dashboard des ventes</h1>
    
    <div class="stats-dashboard">
        <div class="stat-card">
            <h3><?= $total_orders ?></h3>
            <p>Commandes totales</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($total_revenue, 0, ',', ' ') ?> FCFA</h3>
            <p>Chiffre d'affaires</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_products_sold ?></h3>
            <p>Produits vendus</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_users ?></h3>
            <p>Utilisateurs</p>
        </div>
    </div>
    
    <h2>📈 Ventes par mode de paiement</h2>
    <div class="stats-dashboard" style="grid-template-columns: repeat(3, 1fr);">
        <?php foreach ($payment_stats as $stat): ?>
        <div class="stat-card">
            <h3><?= $stat['count'] ?></h3>
            <p><?= ucfirst($stat['payment_method']) ?></p>
            <small><?= number_format($stat['total'], 0, ',', ' ') ?> FCFA</small>
        </div>
        <?php endforeach; ?>
    </div>
    
    <h2>📋 Dernières commandes</h2>
    <table class="orders-table">
        <thead>
            <tr><th>Réf</th><th>Produit</th><th>Acheteur</th><th>Vendeur</th><th>Montant</th><th>Paiement</th><th>Statut</th><th>Date</th></tr>
        </thead>
        <tbody>
            <?php foreach ($recent_orders as $order): ?>
            <tr>
                <td><?= $order['reference'] ?></td>
                <td><?= htmlspecialchars($order['product_name']) ?></td>
                <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                <td><?= htmlspecialchars($order['seller_name']) ?></td>
                <td><?= number_format($order['amount'], 0, ',', ' ') ?> FCFA</td>
                <td><?= $order['payment_method'] ?></td>
                <td class="status-<?= $order['payment_status'] ?>"><?= $order['payment_status'] == 'paid' ? 'Payé' : 'En attente' ?></td>
                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>