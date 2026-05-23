<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Vérification admin (simplifiée sans auth.php)
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

// Traitement des actions
$message = '';
$error = '';

// Ajouter un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? 'Bini-Dang');
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires (nom, email, mot de passe)";
    } else {
        // Vérifier si l'email existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "Cet email existe déjà !";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, location) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role, $phone, $location]);
            $message = "✅ Utilisateur ajouté avec succès !";
        }
    }
}

// Modifier le rôle
if (isset($_GET['action']) && $_GET['action'] === 'role' && isset($_GET['id']) && isset($_GET['role'])) {
    $id = (int)$_GET['id'];
    $new_role = $_GET['role'];
    
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $id]);
        $message = "✅ Rôle modifié avec succès !";
    } else {
        $error = "❌ Vous ne pouvez pas modifier votre propre rôle !";
    }
    // Redirection après action
    header("Location: utilisateurs.php");
    exit;
}

// Supprimer un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($id != $_SESSION['user_id']) {
        // Supprimer d'abord les produits de l'utilisateur
        $pdo->prepare("DELETE FROM products WHERE seller_id = ?")->execute([$id]);
        // Puis supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $message = "✅ Utilisateur supprimé avec succès !";
    } else {
        $error = "❌ Vous ne pouvez pas supprimer votre propre compte !";
    }
    header("Location: utilisateurs.php");
    exit;
}

// Récupérer tous les utilisateurs
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs - Administration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .admin-header { background: #000; color: white; padding: 1rem; }
        .admin-header h1 { font-size: 1.3rem; }
        .admin-nav { background: #333; padding: 0.5rem; }
        .admin-nav a { color: white; text-decoration: none; margin-right: 1rem; padding: 0.3rem 0.8rem; display: inline-block; }
        .admin-nav a:hover { background: #f97316; border-radius: 20px; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        
        .users-container {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .btn-add {
            background: #000000;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-add:hover {
            background: #333333;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table th,
        .users-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .users-table th {
            background: #f9fafb;
            font-weight: 600;
        }
        .badge-admin {
            background: #f97316;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
        }
        .badge-user {
            background: #9ca3af;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-role {
            background: #f97316;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-delete {
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-delete:hover {
            background: #b91c1c;
        }
        .alert {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert.success {
            background: #d1fae5;
            color: #16a34a;
        }
        .alert.error {
            background: #fee2e2;
            color: #dc2626;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            max-width: 500px;
            width: 90%;
        }
        .modal-content h3 {
            margin-bottom: 1rem;
        }
        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
        }
        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        @media (max-width: 768px) {
            .users-table {
                font-size: 0.8rem;
            }
            .users-table th,
            .users-table td {
                padding: 0.5rem;
            }
            .action-buttons {
                flex-direction: column;
                gap: 0.3rem;
            }
        }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>👑 Bini-Dang Market - Administration</h1>
</div>
<div class="admin-nav">
    <a href="index.php">📊 Dashboard</a>
    <a href="utilisateurs.php" style="background:#f97316; border-radius:20px;">👥 Utilisateurs</a>
    <a href="produits.php">📦 Produits</a>
    <a href="commandes.php">📋 Commandes</a>
    <a href="../logout.php">🚪 Déconnexion</a>
</div>
<div class="container">
    <div class="users-container">
        <div class="users-header">
            <h1>👥 Gestion des utilisateurs</h1>
            <button class="btn-add" onclick="openModal()">+ Ajouter un utilisateur</button>
        </div>
        
        <?php if ($message): ?>
            <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Localisation</th>
                    <th>Rôle</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($u['location'] ?? 'Bini-Dang') ?></td>
                    <td>
                        <?php if ($u['role'] == 'admin'): ?>
                            <span class="badge-admin">👑 Admin</span>
                        <?php else: ?>
                            <span class="badge-user"> Utilisateur</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td class="action-buttons">
                        <?php if ($u['role'] == 'admin'): ?>
                            <a href="?action=role&id=<?= $u['id'] ?>&role=user" class="btn-role" onclick="return confirm('Rétrograder cet utilisateur ?')">⬇️ Rétrograder</a>
                        <?php else: ?>
                            <a href="?action=role&id=<?= $u['id'] ?>&role=admin" class="btn-role" onclick="return confirm('Promouvoir cet utilisateur en admin ?')">⬆️ Promouvoir</a>
                        <?php endif; ?>
                        
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="?action=delete&id=<?= $u['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer cet utilisateur ? Cette action est irréversible.')">🗑️ Supprimer</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pour ajouter un utilisateur -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <h3>➕ Ajouter un utilisateur</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Nom complet" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="text" name="phone" placeholder="Téléphone">
            <input type="text" name="location" placeholder="Localisation" value="Bini-Dang">
            <select name="role">
                <option value="user">👤 Utilisateur</option>
                <option value="admin">👑 Administrateur</option>
            </select>
            <div class="modal-buttons">
                <button type="button" class="btn-add" style="background: #9ca3af;" onclick="closeModal()">Annuler</button>
                <button type="submit" name="add_user" class="btn-add">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('addUserModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('addUserModal').style.display = 'none';
}
// Fermer le modal en cliquant à l'extérieur
window.onclick = function(event) {
    let modal = document.getElementById('addUserModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>