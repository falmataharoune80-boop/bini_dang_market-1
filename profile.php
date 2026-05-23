<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUser();
$is_own_profile = true;

// Récupérer les produits du vendeur
$stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$myProducts = $stmt->fetchAll();

// Récupérer les statistiques d'achat
$stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as total_amount FROM orders WHERE buyer_id = ? AND payment_status = 'paid'");
$stmt->execute([$_SESSION['user_id']]);
$buyer_stats = $stmt->fetch();

// Récupérer les statistiques de vente
$stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as total_amount FROM orders WHERE seller_id = ? AND payment_status = 'paid'");
$stmt->execute([$_SESSION['user_id']]);
$seller_stats = $stmt->fetch();

// Récupérer les produits vendus
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND status = 'sold'");
$stmt->execute([$_SESSION['user_id']]);
$products_sold = $stmt->fetchColumn();

// Récupérer les commandes récentes (achats)
$stmt = $pdo->prepare("
    SELECT o.*, p.title as product_name 
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.buyer_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll();

// Récupérer TOUTES les commandes de l'utilisateur (pour la section)
$stmt = $pdo->prepare("
    SELECT o.*, p.title as product_name 
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.buyer_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$user_orders = $stmt->fetchAll();

// Mise à jour du profil
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    
    if (empty($name)) {
        $error = "Le nom ne peut pas être vide.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, location = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $location, $_SESSION['user_id']]);
        $success = true;
        $user = getUser();
    }
}

include 'includes/header.php';
?>

<style>
    /* Container principal */
    .profile-container {
        max-width: 900px;
        margin: 1rem auto;
    }
    
    /* Carte principale */
    .profile-card {
        background: white;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
    }
    
    /* En-tête du profil */
    .profile-header {
        background: linear-gradient(135deg, #000000 0%, #333333 100%);
        color: white;
        padding: 1.2rem;
        text-align: center;
    }
    .profile-avatar {
        width: 70px;
        height: 70px;
        background: #f97316;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.8rem;
        font-size: 2rem;
    }
    .profile-header h1 {
        font-size: 1.3rem;
        margin-bottom: 0.2rem;
    }
    .profile-email {
        color: #f97316;
        font-size: 0.8rem;
    }
    .profile-location {
        font-size: 0.7rem;
        color: #9ca3af;
        margin-top: 0.2rem;
    }
    
    /* Corps du profil */
    .profile-body {
        padding: 1rem;
    }
    .profile-section {
        margin-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1rem;
    }
    .profile-section h2 {
        font-size: 0.95rem;
        margin-bottom: 0.8rem;
        color: #000000;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* FORMULAIRES - TAILLE MOYENNE */
    .form-group {
        margin-bottom: 0.8rem;
    }
    .form-group label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.25rem;
        color: #374151;
        font-size: 0.8rem;
    }
    .form-group input {
        width: 100%;
        padding: 0.5rem 0.7rem;
        border: 1px solid #d1d5db;
        border-radius: 0.4rem;
        font-size: 0.85rem;
        transition: all 0.2s;
        background: #fefefe;
    }
    .form-group input:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 2px rgba(249,115,22,0.1);
    }
    .form-group input:disabled {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }
    
    /* Bouton - taille réduite */
    .btn-save {
        background-color: #000000;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 0.35rem 0.9rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-block;
    }
    .btn-save:hover {
        background-color: #333333;
    }
    
    /* Alertes */
    .alert-success {
        background-color: #d1fae5;
        color: #16a34a;
        padding: 0.4rem;
        border-radius: 0.4rem;
        margin-bottom: 0.8rem;
        font-size: 0.7rem;
    }
    .alert-error {
        background-color: #fee2e2;
        color: #dc2626;
        padding: 0.4rem;
        border-radius: 0.4rem;
        margin-bottom: 0.8rem;
        font-size: 0.7rem;
    }
    
    /* Grille des produits */
    .products-grid-mini {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 0.6rem;
    }
    .product-item {
        background: #f9fafb;
        border-radius: 0.5rem;
        padding: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #e5e7eb;
    }
    .product-item-info {
        flex: 1;
    }
    .product-item-title {
        font-weight: 600;
        font-size: 0.8rem;
        margin-bottom: 0.1rem;
    }
    .product-item-price {
        font-size: 0.65rem;
        color: #000;
        font-weight: bold;
    }
    .product-item-status {
        font-size: 0.55rem;
        padding: 0.1rem 0.3rem;
        border-radius: 15px;
        display: inline-block;
    }
    .status-available {
        background: #d1fae5;
        color: #16a34a;
    }
    .status-sold {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-edit {
        background: #f97316;
        color: white;
        padding: 0.1rem 0.4rem;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.6rem;
        margin-left: 0.5rem;
    }
    .btn-edit:hover {
        background: #ea580c;
    }
    
    /* Statistiques - style compact */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.6rem;
        margin-top: 0.6rem;
    }
    .stat-card {
        background: #f9fafb;
        padding: 0.5rem;
        text-align: center;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }
    .stat-value {
        font-size: 1.1rem;
        font-weight: bold;
        color: #000;
    }
    .stat-label {
        font-size: 0.55rem;
        color: #6b7280;
    }
    .stat-small {
        font-size: 0.5rem;
        color: #f97316;
        margin-top: 0.2rem;
    }
    
    /* Actions rapides */
    .actions-grid {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .action-card {
        flex: 1;
        min-width: 90px;
        background: #f9fafb;
        border-radius: 0.5rem;
        padding: 0.5rem;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
        border: 1px solid #e5e7eb;
    }
    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        border-color: #f97316;
    }
    .action-icon {
        font-size: 1.2rem;
        display: block;
        margin-bottom: 0.2rem;
    }
    .action-title {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.65rem;
        margin-bottom: 0.1rem;
    }
    .action-desc {
        font-size: 0.5rem;
        color: #6b7280;
    }
    
    /* Liste des commandes */
    .orders-list {
        background: #f9fafb;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.75rem;
    }
    .order-item:last-child {
        border-bottom: none;
    }
    .order-product {
        flex: 2;
    }
    .order-amount {
        flex: 1;
        text-align: right;
        font-weight: bold;
    }
    .order-status {
        flex: 1.5;
        text-align: center;
    }
    .order-date {
        flex: 1;
        text-align: right;
        font-size: 0.65rem;
        color: #6b7280;
    }
    .status-paid {
        background: #d1fae5;
        color: #16a34a;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        font-size: 0.7rem;
        display: inline-block;
    }
    .status-pending {
        background: #fef3c7;
        color: #d97706;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        font-size: 0.7rem;
        display: inline-block;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .profile-body {
            padding: 0.8rem;
        }
        .actions-grid {
            flex-direction: column;
        }
        .products-grid-mini {
            grid-template-columns: 1fr;
        }
        .order-item {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .order-product {
            flex: 100%;
        }
        .order-amount, .order-status, .order-date {
            flex: auto;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">👤</div>
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <div class="profile-email">✉️ <?= htmlspecialchars($user['email']) ?></div>
            <div class="profile-location">📍 <?= htmlspecialchars($user['location'] ?? 'Bini-Dang') ?></div>
            <div class="profile-location">📱 <?= htmlspecialchars($user['phone'] ?? 'Non renseigné') ?></div>
        </div>
        
        <div class="profile-body">
            <?php if ($success): ?>
                <div class="alert-success">✓ Profil mis à jour avec succès !</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <!-- Formulaire de modification du profil -->
            <div class="profile-section">
                <h2>📋 Informations personnelles</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Localisation</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? 'Bini-Dang') ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn-save">💾 Mettre à jour</button>
                </form>
            </div>
            
            <!-- Statistiques Achats / Ventes -->
            <div class="profile-section">
                <h2>📊 Mes statistiques</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= $buyer_stats['total'] ?? 0 ?></div>
                        <div class="stat-label">Achats</div>
                        <div class="stat-small"><?= number_format($buyer_stats['total_amount'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $seller_stats['total'] ?? 0 ?></div>
                        <div class="stat-label">Ventes</div>
                        <div class="stat-small"><?= number_format($seller_stats['total_amount'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= count($myProducts) ?></div>
                        <div class="stat-label">Produits</div>
                        <div class="stat-small"><?= $products_sold ?> vendus</div>
                    </div>
                </div>
            </div>
            
            <!-- Toutes les commandes de l'utilisateur -->
            <div class="profile-section">
                <h2>📋 Mes commandes</h2>
                <?php if (empty($user_orders)): ?>
                    <p style="color: #6b7280; font-size: 0.75rem;">Aucune commande passée.</p>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($user_orders as $order): ?>
                        <div class="order-item">
                            <div class="order-product"><?= htmlspecialchars($order['product_name']) ?></div>
                            <div class="order-amount"><?= number_format($order['amount'], 0, ',', ' ') ?> FCFA</div>
                            <div class="order-status">
                                <?php if ($order['payment_status'] == 'paid'): ?>
                                    <span class="status-paid">✅ Payé</span>
                                <?php else: ?>
                                    <span class="status-pending">⏳ En attente</span>
                                <?php endif; ?>
                            </div>
                            <div class="order-date"><?= date('d/m/Y', strtotime($order['created_at'])) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mes produits -->
            <div class="profile-section">
                <h2> Mes produits</h2>
                <?php if (empty($myProducts)): ?>
                    <p style="color: #6b7280; font-size: 0.75rem;">Aucun produit publié.</p>
                    <a href="post-ad.php" class="btn-save" style="display: inline-block;">+ Publier une annonce</a>
                <?php else: ?>
                    <div class="products-grid-mini">
                        <?php foreach ($myProducts as $prod): ?>
                            <div class="product-item">
                                <div class="product-item-info">
                                    <div class="product-item-title"><?= htmlspecialchars($prod['title']) ?></div>
                                    <div class="product-item-price"><?= number_format($prod['price'], 0, ',', ' ') ?> FCFA</div>
                                    <span class="product-item-status <?= $prod['status'] == 'available' ? 'status-available' : 'status-sold' ?>">
                                        <?= $prod['status'] == 'available' ? 'Disponible' : 'Épuisé' ?>
                                    </span>
                                </div>
                                <a href="post-ad.php?id=<?= $prod['id'] ?>" class="btn-edit"> Modifier</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="post-ad.php" class="btn-save" style="display: inline-block; margin-top: 0.6rem;">+ Publier une nouvelle annonce</a>
                <?php endif; ?>
            </div>
            
            <!-- Actions rapides -->
            <div class="profile-section">
                <h2>⚡ Actions rapides</h2>
                <div class="actions-grid">
                    <a href="cart.php" class="action-card">
                        <div class="action-icon">🛒</div>
                        <div class="action-title">Mon panier</div>
                        <div class="action-desc">Voir mes articles</div>
                    </a>
                    <a href="chat.php" class="action-card">
                        <div class="action-icon">💬</div>
                        <div class="action-title">Messagerie</div>
                        <div class="action-desc">Discuter</div>
                    </a>
                    <a href="post-ad.php" class="action-card">
                        <div class="action-icon">📦</div>
                        <div class="action-title">Vendre</div>
                        <div class="action-desc">Publier une annonce</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>