<?php
/**
 * Gestion des commandes - Administration
 * Chemin: admin/commandes.php
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Vérification admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Récupérer toutes les commandes (sans filtre par défaut)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sql = "
    SELECT o.*, 
           p.title as product_name,
           p.price as product_price,
           buyer.name as buyer_name,
           buyer.email as buyer_email,
           seller.name as seller_name
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users buyer ON o.buyer_id = buyer.id
    JOIN users seller ON o.seller_id = seller.id
";

if ($filter == 'paid') {
    $sql .= " WHERE o.payment_status = 'paid' ";
} elseif ($filter == 'pending') {
    $sql .= " WHERE o.payment_status = 'pending' ";
}

$sql .= " ORDER BY o.created_at DESC";

$orders = $pdo->query($sql)->fetchAll();

// Statistiques
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$paid_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes - Administration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; }
        
        /* Header */
        .admin-header { background: #000; color: white; padding: 1rem; }
        .admin-header h1 { font-size: 1.3rem; }
        
        /* Navigation */
        .admin-nav { background: #333; padding: 0.5rem; }
        .admin-nav a { color: white; text-decoration: none; margin-right: 1rem; padding: 0.3rem 0.8rem; display: inline-block; transition: background 0.2s; }
        .admin-nav a:hover, .admin-nav a.active { background: #f97316; border-radius: 20px; }
        
        /* Container */
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        
        /* Statistiques */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #000;
        }
        .stat-card .label {
            color: #6b7280;
            font-size: 0.8rem;
        }
        
        /* Filtres */
        .filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            background: white;
            padding: 0.4rem 1rem;
            border-radius: 30px;
            text-decoration: none;
            color: #374151;
            font-size: 0.85rem;
            border: 1px solid #ddd;
            transition: all 0.2s;
        }
        .filter-btn:hover {
            background: #e5e7eb;
        }
        .filter-btn.active {
            background: #000;
            color: white;
            border-color: #000;
        }
        
        /* Tableau */
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f5f5f5;
            font-weight: 600;
        }
        tr:hover {
            background: #f9fafb;
        }
        
        /* Badges */
        .badge-paid {
            background: #d1fae5;
            color: #16a34a;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
        }
        .badge-pending {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
        }
        
        /* États vides */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
        }
        .empty-state p {
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            table { font-size: 0.7rem; }
            th, td { padding: 0.5rem; }
            .stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>👑 Bini-Dang Market - Administration</h1>
</div>
<div class="admin-nav">
    <a href="index.php">📊 Dashboard</a>
    <a href="utilisateurs.php">👥 Utilisateurs</a>
    <a href="produits.php">📦 Produits</a>
    <a href="commandes.php" class="active">📋 Commandes</a>
    <a href="../index.php" style="background: #f97316;">🏠 Voir le site</a>
    <a href="../logout.php">🚪 Déconnexion</a>
</div>
<div class="container">
    <h1> Gestion des commandes</h1>
    
    <!-- Statistiques -->
    <div class="stats">
        <div class="stat-card">
            <div class="number"><?= $total_orders ?></div>
            <div class="label"> Total commandes</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $paid_orders ?></div>
            <div class="label">✅ Commandes payées</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= number_format($total_revenue, 0, ',', ' ') ?> FCFA</div>
            <div class="label"> Chiffre d'affaires</div>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="filters">
        <a href="?filter=all" class="filter-btn <?= $filter == 'all' ? 'active' : '' ?>"> Toutes les commandes</a>
        <a href="?filter=paid" class="filter-btn <?= $filter == 'paid' ? 'active' : '' ?>">✅ Commandes payées</a>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <p>📭 Aucune commande pour le moment.</p>
            <p style="font-size: 0.8rem;">Les commandes apparaîtront ici lorsque des clients passeront des achats.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Référence</th>
                    <th>Produit</th>
                    <th>Acheteur</th>
                    <th>Vendeur</th>
                    <th>Montant</th>
                    <th>Paiement</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><strong><?= htmlspecialchars($order['reference']) ?></strong></td>
                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                    <td>
                        <?= htmlspecialchars($order['buyer_name']) ?><br>
                        <small style="color: #6b7280;"><?= htmlspecialchars($order['buyer_email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($order['seller_name']) ?></td>
                    <td><strong><?= number_format($order['amount'], 0, ',', ' ') ?> FCFA</strong></td>
                    <td><?= htmlspecialchars($order['payment_method']) ?></td>
                    <td>
                        <?php if ($order['payment_status'] == 'paid'): ?>
                            <span class="badge-paid">✅ Payé</span>
                        <?php else: ?>
                            
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>