<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>
<div class="cancel-container">
    <div class="cancel-icon">❌</div>
    <h1>Paiement annulé</h1>
    <p>Vous avez annulé le paiement. Aucun montant n'a été débité.</p>
    <a href="cart.php" class="btn-add" style="display: inline-block; width: auto; padding: 0.5rem 1.5rem;">Retour au panier</a>
</div>
<style>
.cancel-container {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 1rem;
    max-width: 500px;
    margin: 3rem auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.cancel-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
</style>
<?php include 'includes/footer.php'; ?>