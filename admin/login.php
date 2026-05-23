<?php
/**
 * Page de connexion pour l'administration
 * Chemin: admin/login.php
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si déjà admin, rediriger vers dashboard
if (isset($_SESSION['user_id'])) {
    require_once '../includes/config.php';
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user && $user['role'] === 'admin') {
        header('Location: index.php');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/config.php';
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Email ou mot de passe admin incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Connexion</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .login-container { 
            max-width: 400px; 
            margin: 100px auto; 
            background: white; 
            padding: 2rem; 
            border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .login-container h1 { text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem; }
        .login-container input { 
            width: 100%; 
            padding: 0.6rem; 
            margin-bottom: 1rem; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
        }
        .login-container button { 
            width: 100%; 
            padding: 0.6rem; 
            background: #000; 
            color: white; 
            border: none; 
            border-radius: 40px; 
            cursor: pointer; 
            font-size: 1rem;
        }
        .login-container button:hover { background: #333; }
        .error { color: red; text-align: center; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="login-container">
    <h1>🔐 Administration Bini-Dang</h1>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email admin" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>