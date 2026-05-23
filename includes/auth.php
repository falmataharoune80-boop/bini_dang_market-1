<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // ========== INSCRIPTION ==========
        if ($_POST['action'] === 'register') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $location = trim($_POST['location'] ?? 'Bini-Dang');
            $phone = trim($_POST['phone'] ?? '');

            // Vérifier que les champs ne sont pas vides
            if (empty($name) || empty($email) || empty($password)) {
                header('Location: ../register.php?error=missing_fields');
                exit;
            }

            // Vérifier si l'email existe déjà
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                header('Location: ../register.php?error=email_exists');
                exit;
            }

            // Hachage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insertion
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, location, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $location, $phone]);

            // Connexion automatique
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: ../index.php');
            exit;
        }

        // ========== CONNEXION ==========
        elseif ($_POST['action'] === 'login') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: ../index.php');
                exit;
            } else {
                header('Location: ../login.php?error=1');
                exit;
            }
        }
    }
}
// Si aucune action valide, rediriger
header('Location: ../index.php');
exit;