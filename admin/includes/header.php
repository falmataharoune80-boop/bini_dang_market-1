<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Bini-Dang Market</title>
    <link rel="stylesheet" href="/bini_dang_market/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; }
        .admin-nav {
            background: #000000;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .admin-nav .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .admin-nav .logo {
            color: white;
            font-size: 1.3rem;
            font-weight: bold;
            text-decoration: none;
        }
        .admin-nav .logo span {
            color: #f97316;
        }
        .admin-nav .nav-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .admin-nav .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            transition: background 0.2s;
        }
        .admin-nav .nav-links a:hover {
            background: #f97316;
        }
        .admin-nav .nav-links a.active {
            background: #f97316;
        }
        .admin-content {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .logout-btn {
            background: #dc2626;
            padding: 0.3rem 1rem;
            border-radius: 20px;
        }
        .logout-btn:hover {
            background: #b91c1c !important;
        }
        @media (max-width: 768px) {
            .admin-nav .container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="admin-nav">
    <div class="container">
        <a href="index.php" class="logo">Bini-Dang <span>Admin</span></a>
        <div class="nav-links">
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📊 Dashboard</a>
            <a href="utilisateurs.php" class="<?= basename($_SERVER['PHP_SELF']) == 'utilisateurs.php' ? 'active' : '' ?>">👥 Utilisateurs</a>
            <a href="produits.php" class="<?= basename($_SERVER['PHP_SELF']) == 'produits.php' ? 'active' : '' ?>">📦 Produits</a>
            <a href="commandes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'commandes.php' ? 'active' : '' ?>">📋 Commandes</a>
            <a href="../logout.php" class="logout-btn">🚪 Déconnexion</a>
        </div>
    </div>
</div>
<div class="admin-content">