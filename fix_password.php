<?php
require_once 'includes/config.php';

// Liste des utilisateurs à corriger
$users = [
    ['email' => 'admin@bini.com', 'password' => 'admin123', 'name' => 'Administrateur'],
    ['email' => 'gustave@gmail.com', 'password' => '123456', 'name' => 'Gustave'],
    ['email' => 'jean@example.com', 'password' => 'password123', 'name' => 'Jean Dupont'],
    ['email' => 'marie@example.com', 'password' => 'password123', 'name' => 'Marie Claire'],
];

echo "<h2>Mise à jour des mots de passe</h2>";
echo "<ul>";

foreach ($users as $user) {
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, $user['email']]);
    
    if ($stmt->rowCount() > 0) {
        echo "<li>✅ Mot de passe mis à jour pour : " . $user['email'] . " (" . $user['name'] . ")</li>";
    } else {
        // Si l'utilisateur n'existe pas, on le crée
        $stmt2 = $pdo->prepare("INSERT INTO users (name, email, password, role, location) VALUES (?, ?, ?, 'user', 'Bini-Dang')");
        $stmt2->execute([$user['name'], $user['email'], $hashed_password]);
        echo "<li>➕ Utilisateur créé : " . $user['email'] . " (" . $user['name'] . ")</li>";
    }
}

// S'assurer que Gustave est admin
$stmt3 = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = 'gustave@gmail.com'");
$stmt3->execute();
echo "<li>👑 Gustave est maintenant administrateur</li>";

echo "</ul>";
echo "<hr>";
echo "<h3>🔑 Identifiants de connexion :</h3>";
echo "<ul>";
echo "<li><strong>admin@bini.com</strong> / admin123</li>";
echo "<li><strong>gustave@gmail.com</strong> / 123456</li>";
echo "<li><strong>jean@example.com</strong> / password123</li>";
echo "<li><strong>marie@example.com</strong> / password123</li>";
echo "</ul>";
echo "<p>Vous pouvez maintenant vous connecter !</p>";
?>