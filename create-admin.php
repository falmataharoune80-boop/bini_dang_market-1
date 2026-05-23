<?php
require_once 'includes/config.php';

$name = 'Noubadoum';
$email = 'nouba@gmail.com';
$password = password_hash('88776655', PASSWORD_DEFAULT);
$phone = '656320677';
$location = 'Bini-Dang';
$role = 'admin';

try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, location, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $phone, $location, $role]);
    echo "✅ Administrateur créé avec succès !<br>";
    echo "Email : nouba@gmail.com<br>";
    echo "Mot de passe : 88776655";
    echo "phone : 656320677";
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>