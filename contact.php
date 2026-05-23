<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$message_sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } else {
        // En production, envoyer un email ici
        $message_sent = true;
    }
}
?>
<div class="contact-container">
    <h1 class="page-title">Contactez-nous</h1>
    
    <div class="contact-grid">
        <div class="contact-info">
            <h2>Nos coordonnées</h2>
            <div class="info-item">
                <span class="info-icon">📍</span>
                <span>Bini-Dang, Cameroun</span>
            </div>
            <div class="info-item">
                <span class="info-icon">📞</span>
                <span>+237 612 345 678</span>
            </div>
            <div class="info-item">
                <span class="info-icon">📧</span>
                <span>contact@binidang-market.com</span>
            </div>
            <div class="info-item">
                <span class="info-icon">🕐</span>
                <span>Lun - Sam : 8h - 18h</span>
            </div>
            
            <div class="social-links">
                <h3>Suivez-nous</h3>
                <a href="#" class="social-icon">📘 Facebook</a>
                <a href="#" class="social-icon">📸 Instagram</a>
                <a href="#" class="social-icon">💬 WhatsApp</a>
            </div>
        </div>
        
        <div class="contact-form">
            <h2>Envoyez-nous un message</h2>
            <?php if ($message_sent): ?>
                <div class="alert success">Message envoyé ! Nous vous répondrons rapidement.</div>
            <?php elseif ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Votre nom" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Votre email" required>
                </div>
                <div class="form-group">
                    <textarea name="message" rows="5" placeholder="Votre message" required></textarea>
                </div>
                <button type="submit" class="btn-add">Envoyer</button>
            </form>
        </div>
    </div>
</div>

<style>
.contact-container {
    max-width: 1000px;
    margin: 2rem auto;
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}
.contact-info h2, .contact-form h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: #000000;
}
.info-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.6rem 0;
    border-bottom: 1px solid #e5e7eb;
}
.info-icon {
    font-size: 1.2rem;
    width: 2rem;
}
.social-links {
    margin-top: 1.5rem;
}
.social-links h3 {
    margin-bottom: 0.8rem;
}
.social-icon {
    display: inline-block;
    margin-right: 1rem;
    color: #f97316;
    text-decoration: none;
}
.social-icon:hover {
    text-decoration: underline;
}
.contact-form .form-group {
    margin-bottom: 1rem;
}
.contact-form input, .contact-form textarea {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
}
@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?php include 'includes/footer.php'; ?>