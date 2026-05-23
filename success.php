<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>
<div class="success-container">
    <div class="success-icon">✅</div>
    <h1>Paiement réussi !</h1>
    <p>Merci pour votre achat. Votre commande a bien été enregistrée.</p>
    <p>Un email de confirmation vous sera envoyé.</p>
    <a href="index.php" class="btn-add" style="display: inline-block; width: auto; padding: 0.5rem 1.5rem;">Retour à l'accueil</a>
</div>
<style>
.success-container {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 1rem;
    max-width: 500px;
    margin: 3rem auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
</style>
<?php include 'includes/footer.php'; ?>