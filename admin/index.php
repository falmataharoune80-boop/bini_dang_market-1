<?php
/**
 * Tableau de bord de l'administration
 * Chemin: admin/index.php
 */

require_once 'functions.php';
require_once 'auth.php';
requireAdmin();

// Statistiques
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(amount) FROM orders WHERE status = 'completed'")->fetchColumn();

// Récupérer les utilisateurs pour l'affichage
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

$page = $_GET['page'] ?? 'dashboard';
$message = '';
$error = '';

// ========== AJOUTER UN UTILISATEUR ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires !";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->fetch()) {
            $error = "Cet email existe déjà !";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, location) VALUES (?, ?, ?, ?, 'Bini-Dang')");
            $stmt->execute([$name, $email, $hashed_password, $role]);
            $message = "✅ Utilisateur ajouté avec succès !";
            // Recharger la liste
            $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
        }
    }
}

// ========== PROMOUVOIR / RÉTROGRADER ==========
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['role'])) {
    $id = (int)$_GET['id'];
    $role = $_GET['role'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
        $message = "✅ Rôle modifié !";
        $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
    }
}

// ========== SUPPRIMER UN UTILISATEUR ==========
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        $message = "✅ Utilisateur supprimé !";
        $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Bini-Dang Market</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .admin-header { background: #000; color: white; padding: 1rem; }
        .admin-header h1 { font-size: 1.3rem; }
        .admin-nav { background: #333; padding: 0.5rem; }
        .admin-nav a { color: white; text-decoration: none; margin-right: 1rem; padding: 0.3rem 0.8rem; display: inline-block; }
        .admin-nav a:hover, .admin-nav a.active { background: #f97316; border-radius: 20px; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card h2 { font-size: 2rem; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; margin: 1rem 0; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .btn { padding: 0.3rem 0.6rem; border-radius: 20px; text-decoration: none; font-size: 0.8rem; display: inline-block; margin-right: 0.3rem; }
        .btn-admin { background: #f97316; color: white; }
        .btn-user { background: #6b7280; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-success { background: #16a34a; color: white; }
        .message-success { background: #d1fae5; color: #16a34a; padding: 0.75rem; border-radius: 10px; margin-bottom: 1rem; }
        .message-error { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 10px; margin-bottom: 1rem; }
        .form-container { background: white; padding: 1.5rem; border-radius: 10px; margin-top: 1rem; }
        .form-row { display: grid; grid-template-columns: repeat(2,1fr); gap: 1rem; margin-bottom: 1rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>👑 Bini-Dang Market - Administration</h1>
</div>
<div class="admin-nav">
<div class="admin-nav">
    <a href="index.php">📊 Dashboard</a>
    <a href="?page=users">👥 Utilisateurs</a>
    <a href="produits.php">📦 Produits</a>
    <a href="commandes.php">📋 Commandes</a>
    <a href="../index.php" style="background: #f97316;">🏠 Voir le site</a>  <!-- ← AJOUTER CETTE LIGNE -->
    <a href="../logout.php">🚪 Déconnexion</a>
</div>
</div>
<div class="container">
    <?php if ($message): ?>
        <div class="message-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="message-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($page == 'dashboard'): ?>
        <h1>Tableau de bord</h1>
        <div class="stats">
            <div class="stat-card"><h2><?= $totalUsers ?></h2><p>👥 Utilisateurs</p></div>
            <div class="stat-card"><h2><?= $totalProducts ?></h2><p> Produits</p></div>
            <div class="stat-card"><h2><?= $totalOrders ?></h2><p> Commandes</p></div>
            <div class="stat-card"><h2><?= number_format($totalRevenue, 0, ',', ' ') ?> FCFA</h2><p></p></div>
        </div>
    <?php endif; ?>

    <?php if ($page == 'users'): ?>
        <h1>👥 Gestion des utilisateurs</h1>
        
        <table>
            <thead>
                <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['role'] == 'admin' ? '👑 Admin' : '👤 Utilisateur' ?></td>
                    <td>
                        <?php if ($u['role'] == 'admin'): ?>
                            <a href="?page=users&action=role&id=<?= $u['id'] ?>&role=user" class="btn btn-user">Rétrograder</a>
                        <?php else: ?>
                            <a href="?page=users&action=role&id=<?= $u['id'] ?>&role=admin" class="btn btn-admin">Promouvoir</a>
                        <?php endif; ?>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="?page=users&delete&id=<?= $u['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="form-container">
            <h3>➕ Ajouter un utilisateur</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Nom complet" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Mot de passe" required>
                    </div>
                    <div class="form-group">
                        <select name="role">
                            <option value="user">👤 Utilisateur</option>
                            <option value="admin">👑 Administrateur</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn btn-success">➕ Ajouter l'utilisateur</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>