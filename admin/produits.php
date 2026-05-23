<?php
/**
 * Gestion des produits
 * Chemin: admin/produits.php
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

$message = '';

// ========== SUPPRIMER UN PRODUIT ==========
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Désactiver temporairement les vérifications de clés étrangères
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // 1. Supprimer les commandes liées à ce produit
        $stmt = $pdo->prepare("DELETE FROM orders WHERE product_id = ?");
        $stmt->execute([$id]);
        
        // 2. Supprimer les transactions liées à ces commandes
        $pdo->prepare("DELETE FROM transactions WHERE order_id IN (SELECT id FROM orders WHERE product_id = ?)")->execute([$id]);
        
        // 3. Récupérer les images du produit pour les supprimer physiquement
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll();
        
        foreach ($images as $img) {
            $file_path = '../' . $img['image_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // 4. Supprimer les images de la base de données
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
        
        // 5. Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        // Réactiver les vérifications
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        $message = "✅ Produit et ses commandes associées supprimés avec succès !";
    } catch (Exception $e) {
        // Réactiver les vérifications en cas d'erreur
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        $message = "❌ Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Récupérer tous les produits
$products = $pdo->query("
    SELECT p.*, c.name as category_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des produits</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .admin-header { background: #000; color: white; padding: 1rem; }
        .admin-header h1 { font-size: 1.3rem; }
        .admin-nav { background: #333; padding: 0.5rem; }
        .admin-nav a { color: white; text-decoration: none; margin-right: 1rem; padding: 0.3rem 0.8rem; display: inline-block; }
        .admin-nav a:hover { background: #f97316; border-radius: 20px; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        
        .btn { 
            padding: 0.3rem 0.6rem; 
            border-radius: 20px; 
            text-decoration: none; 
            font-size: 0.8rem; 
            display: inline-block; 
            margin-right: 0.5rem; 
        }
        .btn-primary { background: #f97316; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-primary:hover { background: #ea580c; }
        
        .product-image { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        .alert-success {
            background: #d1fae5;
            color: #16a34a;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .warning-note {
            background: #fef3c7;
            color: #d97706;
            padding: 0.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.8rem;
        }
        @media (max-width: 768px) {
            .actions-cell {
                flex-direction: column;
                gap: 5px;
            }
            .btn {
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>👑 Bini-Dang Market - Gestion des produits</h1>
</div>
<div class="admin-nav">
    <a href="index.php">📊 Dashboard</a>
    <a href="utilisateurs.php">👥 Utilisateurs</a>
    <a href="produits.php" style="background:#f97316; border-radius:20px;">📦 Produits</a>
    <a href="commandes.php">📋 Commandes</a>
    <a href="../index.php" style="background: #f97316;">🏠 Voir le site</a>
    <a href="../logout.php">🚪 Déconnexion</a>
</div>

<div class="container">
    <h1> Gestion des produits</h1>
    
    <div class="warning-note">
        ⚠️ Attention : La suppression d'un produit entraînera également la suppression des commandes associées.
    </div>
    
    <?php if (isset($message) && strpos($message, '✅') !== false): ?>
        <div class="alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif (isset($message) && strpos($message, '❌') !== false): ?>
        <div class="alert-error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td>
                    <?php if ($p['image']): ?>
                        <img src="../<?= htmlspecialchars($p['image']) ?>" class="product-image" alt="<?= htmlspecialchars($p['title']) ?>">
                    <?php else: ?>
                        <span style="color:#999;">Pas d'image</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                <td><?= number_format($p['price'], 0, ',', ' ') ?> FCFA</td>
                <td>
                    <?php if ($p['status'] == 'available'): ?>
                        <span style="color:green;">✓ Disponible</span>
                    <?php else: ?>
                        <span style="color:red;">✗ Épuisé</span>
                    <?php endif; ?>
                </td>
                <td class="actions-cell">
                    <a href="../product.php?id=<?= $p['id'] ?>" class="btn btn-primary" target="_blank">👁️ Voir</a>
                    <a href="?delete&id=<?= $p['id'] ?>" class="btn btn-danger" onclick="return confirm('⚠️ ATTENTION : Supprimer ce produit supprimera également toutes ses commandes associées. Êtes-vous sûr ?')">🗑️ Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>