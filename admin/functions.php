<?php
/**
 * Fichier de fonctions pour l'administration
 * Chemin: admin/functions.php
 */

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fichiers de configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

/**
 * Vérifie si l'utilisateur est connecté et est administrateur
 */
function isAdmin() {
    if (!isset($_SESSION['user_id'])) return false;
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return ($user && $user['role'] === 'admin');
}

/**
 * Vérifie et redirige si non admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}
?>