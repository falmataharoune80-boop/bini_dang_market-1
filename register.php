<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>
<style>
/* Styles spécifiques pour le formulaire d'inscription */
.register-container {
    max-width: 500px;
    margin: 2rem auto;
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.register-container h1 {
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
.btn-register {
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
.btn-register:hover {
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
.location-note {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: -0.5rem;
    margin-bottom: 0.5rem;
}
</style>

<div class="register-container">
    <h1>Inscription</h1>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'email_exists'): ?>
        <div class="alert error">Cet email est déjà utilisé. <a href="login.php">Connectez-vous</a></div>
    <?php endif; ?>
    <form method="POST" action="includes/auth.php">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
            <input type="text" name="name" placeholder="Nom complet" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Mot de passe" required>
        </div>
        <div class="form-group">
            <input type="text" name="phone" placeholder="Téléphone">
        </div>
        <div class="form-group">
            <input type="text" name="location" placeholder="Localisation" value="Bini-Dang">
            <div class="location-note"></div>
        </div>
        <button type="submit" class="btn-register">S'inscrire</button>
    </form>
    <p class="text-center">Déjà inscrit ? <a href="login.php">Connectez-vous</a></p>
</div>
<?php include 'includes/footer.php'; ?>