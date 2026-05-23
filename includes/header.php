<?php
require_once __DIR__ . '/functions.php';
$isLogged = isLoggedIn();
$user = $isLogged ? getUser() : null;
$isAdmin = ($isLogged && $user['role'] == 'admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bini-Dang Market</title>
    <link rel="stylesheet" href="/bini_dang_market/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="header-inner">
            <div class="logo-area">
                <a href="index.php" class="logo">Bini-Dang Market</a>
                <a href="cart.php" class="cart-icon">🛒</a>
            </div>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="search.php">Rechercher</a>
                
                <?php if ($isLogged): ?>
                    <!-- Menu utilisateur normal -->
                    <a href="post-ad.php">+ Publier</a>
                    <a href="chat.php"> Messages</a>
                    <a href="profile.php">Mon profil</a>
                    
                    <!-- Menu ADMIN (visible uniquement si l'utilisateur est admin) -->
                    <?php if ($isAdmin): ?>
                        <div class="admin-menu">
                            <span class="admin-badge">👑 Admin</span>
                            <div class="admin-dropdown">
                                <a href="admin/index.php">📊 Dashboard</a>
                                <a href="admin/index.php?page=users">👥 Utilisateurs</a>
                                <a href="admin/produits.php">📦 Produits</a>
                                <a href="admin/commandes.php">📋 Commandes</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <a href="logout.php" class="btn-orange">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="btn-orange">Connexion</a>
                    <a href="register.php" class="btn-orange">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
<main class="container">

<style>
/* Styles pour le menu admin déroulant */
.admin-menu {
    position: relative;
    display: inline-block;
    cursor: pointer;
}
.admin-badge {
    background: #f97316;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
}
.admin-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 180px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0.5rem;
    z-index: 1000;
    overflow: hidden;
}
.admin-dropdown a {
    display: block;
    padding: 0.6rem 1rem;
    color: #333;
    text-decoration: none;
    font-size: 0.85rem;
    border-bottom: 1px solid #eee;
}
.admin-dropdown a:hover {
    background: #f97316;
    color: white;
}
.admin-menu:hover .admin-dropdown {
    display: block;
}
</style>