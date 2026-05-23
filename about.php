<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>
<div class="about-container">
    <h1 class="page-title">À propos de Bini-Dang Market</h1>
    
    <div class="about-content">
        <div class="about-section">
            <h2>Notre histoire</h2>
            <p>Bini-Dang Market est né de la volonté de créer un espace d'échange commercial entre les étudiants et les résidents de Bini-Dang. Notre plateforme facilite les achats et ventes de produits neufs et d'occasion en toute sécurité.</p>
        </div>
        
        <div class="about-section">
            <h2>Notre mission</h2>
            <p>Faciliter le commerce local en offrant une plateforme simple, sécurisée et accessible à tous. Nous souhaitons dynamiser l'économie de Bini-Dang en permettant à chacun de vendre et d'acheter facilement.</p>
        </div>
        
        <div class="about-section">
            <h2>Nos valeurs</h2>
            <ul>
                <li>🤝 Confiance et transparence</li>
                <li>🌱 Soutien à l'économie locale</li>
                <li>🔒 Sécurité des transactions</li>
                <li>💬 Service client réactif</li>
            </ul>
        </div>
        
        <div class="about-section">
            <h2>Contactez-nous</h2>
            <p>📧 Email : contact@binidang-market.com</p>
            <p>📞 Téléphone : +237 612 345 678</p>
            <p>📍 Adresse : Bini-Dang, Cameroun</p>
        </div>
    </div>
</div>

<style>
.about-container {
    max-width: 900px;
    margin: 2rem auto;
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.about-section {
    margin-bottom: 2rem;
}
.about-section h2 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #000000;
    border-left: 4px solid #f97316;
    padding-left: 1rem;
}
.about-section p {
    line-height: 1.6;
    color: #4b5563;
}
.about-section ul {
    list-style: none;
    padding-left: 0;
}
.about-section li {
    padding: 0.5rem 0;
    font-size: 1.1rem;
}
</style>
<?php include 'includes/footer.php'; ?>