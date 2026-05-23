<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Bini-Dang Market</title>
    <link rel="stylesheet" href="/bini_dang_market/css/style.css">
    <style>
        .admin-nav {
            background: #000;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-nav .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            transition: background 0.2s;
        }
        .admin-nav a:hover {
            background: #f97316;
        }
        .admin-nav a.active {
            background: #f97316;
        }
    </style>
</head>
<body>
<div class="admin-nav">
    <div class="container">
        <a href="index.php">📊 Dashboard</a>
        <a href="commandes.php" class="active">📋 Commandes</a>
        <a href="produits.php">📦 Produits</a>
        <a href="utilisateurs.php">👥 Utilisateurs</a>
        <a href="../logout.php">🚪 Déconnexion</a>
    </div>
</div>