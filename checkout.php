<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUser();
$error = '';
$success = false;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Récupérer le produit si achat direct
$direct_product = null;
if ($product_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'available'");
    $stmt->execute([$product_id]);
    $direct_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Bini-Dang Market</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .checkout-header {
            background: #000;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .checkout-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        .checkout-header p {
            font-size: 0.85rem;
            color: #f97316;
        }
        .checkout-body {
            padding: 1.5rem;
        }
        .order-summary {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .order-summary h3 {
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            font-weight: bold;
            font-size: 1.1rem;
            border-top: 2px solid #e5e7eb;
        }
        .payment-methods {
            margin-bottom: 1.5rem;
        }
        .payment-methods h3 {
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        .payment-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem;
            margin-bottom: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .payment-option:hover {
            border-color: #f97316;
        }
        .payment-option.selected {
            border-color: #000;
            background: #f9fafb;
        }
        .payment-option input {
            margin: 0;
            width: auto;
        }
        .payment-option label {
            flex: 1;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .payment-logo {
            width: 40px;
            height: auto;
            vertical-align: middle;
            border-radius: 5px;
        }
        .phone-field, .card-field {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #fef3c7;
            border-radius: 0.5rem;
            display: none;
        }
        .card-field {
            background: #f3f4f6;
        }
        .phone-field label, .card-field label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.3rem;
        }
        .phone-field input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
        }
        .phone-field small {
            display: block;
            margin-top: 0.3rem;
            color: #666;
            font-size: 0.7rem;
        }
        #card-element {
            background: white;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
        }
        .btn-pay {
            background: #000;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 0.75rem;
            width: 100%;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
        }
        .btn-pay:hover {
            background: #333;
        }
        .btn-pay:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .back-link:hover {
            color: #000;
        }
        .loading {
            text-align: center;
            padding: 1rem;
            color: #666;
        }
        @media (max-width: 640px) {
            .checkout-body {
                padding: 1rem;
            }
            .payment-logo {
                width: 30px;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="checkout-container">
    <div class="checkout-header">
        <h1>💳 Paiement sécurisé</h1>
        <p>Choisissez votre mode de paiement</p>
    </div>
    <div class="checkout-body">
        <div id="cart-summary" class="order-summary">
            <h3>📦 Récapitulatif de votre commande</h3>
            <div id="cart-items" class="loading">Chargement...</div>
        </div>
        
        <div class="payment-methods">
            <h3>💰 Mode de paiement</h3>
            
            <!-- Orange Money avec logo -->
            <div class="payment-option" data-method="orange_money">
                <input type="radio" name="payment_method" value="orange_money" id="orange_money">
                <label for="orange_money">
                    <img src="uploads/Orange-Money.jpg" alt="Orange Money" class="payment-logo">
                    Orange Money
                </label>
            </div>
            
            <!-- MTN Mobile Money avec logo -->
            <div class="payment-option" data-method="momo">
                <input type="radio" name="payment_method" value="momo" id="momo">
                <label for="momo">
                    <img src="uploads/MIMI.jpg" alt="MIMI" class="payment-logo">
                    MTN Mobile Money (MoMo)
                </label>
            </div>
            
            <!-- Carte bancaire -->
            <div class="payment-option" data-method="card">
                <input type="radio" name="payment_method" value="card" id="card">
                <label for="card">
                    💳 Carte bancaire (Visa/Mastercard)
                </label>
            </div>
        </div>
        
        <!-- Champ pour Orange Money / MTN MoMo -->
        <div id="phone-field" class="phone-field">
            <label>📞 Numéro de téléphone</label>
            <input type="tel" id="phone_number" placeholder="Ex: 612345678" pattern="[0-9]{9}" maxlength="9">
            <small>Format: 9 chiffres (ex: 612345678)</small>
        </div>
        
        <!-- Champ pour Carte bancaire Stripe -->
        <div id="card-field" class="card-field">
            <label>💳 Informations de la carte</label>
            <div id="card-element"></div>
            <div id="card-errors" style="color: red; font-size: 0.8rem; margin-top: 0.5rem;"></div>
        </div>
        
        <button id="pay-btn" class="btn-pay">✅ Confirmer et payer</button>
        <?php if ($product_id > 0): ?>
            <a href="product.php?id=<?= $product_id ?>" class="back-link">← Retour au produit</a>
        <?php else: ?>
            <a href="cart.php" class="back-link">← Retour au panier</a>
        <?php endif; ?>
    </div>
</div>

<script>
// Configuration Stripe
const stripe = Stripe('<?= STRIPE_PUBLIC_KEY ?>');
let elements = null;
let card = null;

// Variables globales
let cartProducts = [];
let totalAmount = 0;
let selectedMethod = 'orange_money';
let isDirectPurchase = <?= $product_id > 0 ? 'true' : 'false' ?>;
let directProduct = <?= $direct_product ? json_encode($direct_product) : 'null' ?>;

// Charger les produits du panier ou le produit direct
function loadCart() {
    if (isDirectPurchase && directProduct) {
        // Achat direct d'un seul produit
        cartProducts = [directProduct];
        totalAmount = parseFloat(directProduct.price);
        let html = `
            <div class="order-item">
                <span>${escapeHtml(directProduct.title)}</span>
                <span>${Number(directProduct.price).toLocaleString()} FCFA</span>
            </div>
            <div class="order-total">
                <span>Total</span>
                <span>${totalAmount.toLocaleString()} FCFA</span>
            </div>
        `;
        document.getElementById('cart-items').innerHTML = html;
        return;
    }
    
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        window.location.href = 'cart.php';
        return;
    }
    
    let ids = cart.join(',');
    
    fetch('api/products.php?ids=' + ids)
        .then(res => res.json())
        .then(products => {
            cartProducts = products;
            let html = '';
            totalAmount = 0;
            
            products.forEach(p => {
                html += `
                    <div class="order-item">
                        <span>${escapeHtml(p.title)}</span>
                        <span>${Number(p.price).toLocaleString()} FCFA</span>
                    </div>
                `;
                totalAmount += parseFloat(p.price);
            });
            
            html += `
                <div class="order-total">
                    <span>Total</span>
                    <span>${totalAmount.toLocaleString()} FCFA</span>
                </div>
            `;
            
            document.getElementById('cart-items').innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('cart-items').innerHTML = '<div class="loading">❌ Erreur de chargement du panier</div>';
        });
}

// Enregistrer la commande dans la base de données
async function saveOrder(paymentMethod, transactionId = null) {
    const productsData = cartProducts.map(p => ({
        id: p.id,
        title: p.title,
        price: p.price,
        seller_id: p.seller_id
    }));
    
    const response = await fetch('api/save-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            products: productsData,
            total: totalAmount,
            payment_method: paymentMethod,
            transaction_id: transactionId
        })
    });
    
    const result = await response.json();
    if (!result.success) {
        throw new Error(result.error || 'Erreur lors de l\'enregistrement');
    }
    return result;
}

// Initialiser Stripe Elements
function initStripe() {
    elements = stripe.elements();
    card = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                fontFamily: '"Segoe UI", Arial, sans-serif',
                '::placeholder': { color: '#aab7c4' }
            }
        }
    });
    card.mount('#card-element');
    
    card.addEventListener('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
}

// Gestion des modes de paiement
const paymentOptions = document.querySelectorAll('.payment-option');
const phoneField = document.getElementById('phone-field');
const cardField = document.getElementById('card-field');
const phoneInput = document.getElementById('phone_number');
const payBtn = document.getElementById('pay-btn');

paymentOptions.forEach(option => {
    const radio = option.querySelector('input[type="radio"]');
    
    option.addEventListener('click', () => {
        radio.checked = true;
        
        paymentOptions.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        
        selectedMethod = radio.value;
        
        // Afficher le bon champ
        if (selectedMethod === 'orange_money' || selectedMethod === 'momo') {
            phoneField.style.display = 'block';
            cardField.style.display = 'none';
        } else if (selectedMethod === 'card') {
            phoneField.style.display = 'none';
            cardField.style.display = 'block';
            if (!card) {
                initStripe();
            }
        }
    });
});

// Paiement avec carte bancaire (Stripe)
async function payWithCard() {
    payBtn.disabled = true;
    payBtn.textContent = 'Traitement en cours...';
    
    try {
        // Créer l'intention de paiement
        const response = await fetch('api/create-payment-intent.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount: totalAmount })
        });
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Confirmer le paiement
        const result = await stripe.confirmCardPayment(data.clientSecret, {
            payment_method: { card: card }
        });
        
        if (result.error) {
            throw new Error(result.error.message);
        }
        
        // Enregistrer la commande dans la BDD
        await saveOrder('carte_bancaire', result.paymentIntent.id);
        
        // Paiement réussi
        if (!isDirectPurchase) {
            localStorage.removeItem('cart');
        }
        alert('✅ Paiement réussi ! Commande enregistrée.');
        window.location.href = 'success.php';
        
    } catch (error) {
        alert('Erreur: ' + error.message);
        payBtn.disabled = false;
        payBtn.textContent = '✅ Confirmer et payer';
    }
}

// Paiement avec mobile money
async function payWithMobileMoney() {
    const phone = phoneInput.value.trim();
    
    if (!phone) {
        alert('Veuillez saisir votre numéro de téléphone');
        return;
    }
    
    if (phone.length !== 9 || !/^\d+$/.test(phone)) {
        alert('Numéro invalide. Format: 612345678 (9 chiffres)');
        return;
    }
    
    payBtn.disabled = true;
    payBtn.textContent = 'Traitement en cours...';
    
    try {
        // Enregistrer la commande dans la BDD
        await saveOrder(selectedMethod === 'orange_money' ? 'orange_money' : 'momo');
        
        if (!isDirectPurchase) {
            localStorage.removeItem('cart');
        }
        alert(`✅ Paiement de ${totalAmount.toLocaleString()} FCFA initié via ${selectedMethod === 'orange_money' ? 'Orange Money' : 'MTN MoMo'} au ${phone}. Commande enregistrée.`);
        window.location.href = 'success.php';
        
    } catch (error) {
        alert('Erreur: ' + error.message);
        payBtn.disabled = false;
        payBtn.textContent = '✅ Confirmer et payer';
    }
}

// Bouton de paiement
payBtn.addEventListener('click', async () => {
    // Vérifier qu'il y a des produits
    if (cartProducts.length === 0) {
        alert('Votre panier est vide');
        window.location.href = 'cart.php';
        return;
    }
    
    if (selectedMethod === 'card') {
        await payWithCard();
    } else {
        await payWithMobileMoney();
    }
});

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Sélectionner le premier mode par défaut
setTimeout(() => {
    const firstOption = document.querySelector('.payment-option');
    if (firstOption) {
        firstOption.click();
    }
}, 500);

loadCart();
</script>

<?php include 'includes/footer.php'; ?>