<?php
// Démarrer la session UNIQUEMENT si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Affichage des erreurs (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_NAME', 'bini_dang_market');
define('DB_USER', 'root');
define('DB_PASS', '');

define('STRIPE_PUBLIC_KEY', 'pk_test_votre_cle');
define('STRIPE_SECRET_KEY', 'sk_test_votre_cle');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>