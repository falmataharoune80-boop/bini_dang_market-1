<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>
<style>
.login-container {
    max-width: 500px;
    margin: 2rem auto;
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.login-container h1 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #1f2937;
}
.form-group {
    margin-bottom: 1rem;
}
.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s;
}
.form-group input:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 2px rgba(249,115,22,0.1);
}
.btn-login {
    background-color: #000000;
    color: white;
    border: none;
    border-radius: 40px;
    padding: 0.75rem;
    width: 100%;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    margin-top: 0.5rem;
}
.btn-login:hover {
    background-color: #333333;
}
.alert {
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}
.alert.error {
    background: #fee2e2;
    color: #dc2626;
}
.text-center {
    text-align: center;
    margin-top: 1.5rem;
}
.text-center a {
    color: #f97316;
    text-decoration: none;
}
.text-center a:hover {
    text-decoration: underline;
}
</style>

<div class="login-container">
    <h1>Connexion</h1>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert error">Email ou mot de passe incorrect</div>
    <?php endif; ?>
    <form method="POST" action="includes/auth.php">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Mot de passe" required>
        </div>
        <button type="submit" class="btn-login">Se connecter</button>
    </form>
    <p class="text-center">Pas de compte ? <a href="register.php">Inscription</a></p>
</div>
<?php include 'includes/footer.php'; ?>