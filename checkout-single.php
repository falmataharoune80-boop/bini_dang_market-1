<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$product_id = (int)$_GET['product_id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'available'");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
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
        .checkout-body {
            padding: 1.5rem;
        }
        .order-summary {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
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
            font-weight: bold;
            font-size: 1.2rem;
            border-top: 2px solid #e5e7eb;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
        }
        .payment-methods {
            margin-bottom: 1.5rem;
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
        }
        .payment-logo {
            width: 35px;
            height: auto;
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
        .phone-field input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
        }
        #card-element {
            background: white;
            padding: 0.6rem;
            border: 1px solid #ddd;
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
            margin-top: 1rem;
        }
        .btn-pay:hover {
            background: #333;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #6b7280;
            text-decoration: none;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 640px) {
            .checkout-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="checkout-container">
    <div class="checkout-header">
        <h1>💳 Paiement sécurisé</h1>
        <p>Finalisez votre achat</p>
    </div>
    <div class="checkout-body">
        <div class="order-summary">
            <h3> Récapitulatif</h3>
            <div class="order-item">
                <span><?= htmlspecialchars($product['title']) ?></span>
                <span><?= number_format($product['price'], 0, ',', ' ') ?> FCFA</span>
            </div>
            <div class="order-total">
                <span>Total</span>
                <span><?= number_format($product['price'], 0, ',', ' ') ?> FCFA</span>
            </div>
        </div>
        
        <div class="payment-methods">
            <h3> Mode de paiement</h3>
            
            <div class="payment-option" data-method="orange_money">
                <input type="radio" name="payment_method" value="orange_money" id="orange_money">
                <label for="orange_money">
                    <img src="uploads/Orange-Money.jpg" alt="Orange Money" class="payment-logo" onerror="this.style.display='none'">
                    Orange Money
                </label>
            </div>
            
            <div class="payment-option" data-method="momo">
                <input type="radio" name="payment_method" value="momo" id="momo">
                <label for="momo">
                    <img src="uploads/MTN-Mobile-Money.jpg" alt="MTN Mobile Money" class="payment-logo" onerror="this.style.display='none'">
                    MTN Mobile Money
                </label>
            </div>
            
            <div class="payment-option" data-method="card">
                <input type="radio" name="payment_method" value="card" id="card">
                <label for="card">💳 Carte bancaire (Visa/Mastercard)</label>
            </div>
        </div>
        
        <div id="phone-field" class="phone-field">
            <label> Numéro de téléphone</label>
            <input type="tel" id="phone_number" placeholder="Ex: 612345678" maxlength="9">
            <small>Format: 9 chiffres (ex: 612345678)</small>
        </div>
        
        <div id="card-field" class="card-field">
            <label>💳 Informations de la carte</label>
            <div id="card-element"></div>
            <div id="card-errors" style="color: red; font-size: 0.8rem;"></div>
        </div>
        
        <button id="pay-btn" class="btn-pay">✅ Confirmer et payer</button>
        <a href="product.php?id=<?= $product['id'] ?>" class="back-link">← Retour au produit</a>
    </div>
</div>

<script>
const stripe = Stripe('<?= STRIPE_PUBLIC_KEY ?>');
let elements = null;
let card = null;
let selectedMethod = 'orange_money';

function initStripe() {
    elements = stripe.elements();
    card = elements.create('card', {
        style: {
            base: { fontSize: '16px', color: '#32325d' }
        }
    });
    card.mount('#card-element');
    card.addEventListener('change', (event) => {
        document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
    });
}

// Gestion des modes de paiement
document.querySelectorAll('.payment-option').forEach(opt => {
    const radio = opt.querySelector('input');
    opt.addEventListener('click', () => {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        opt.classList.add('selected');
        radio.checked = true;
        selectedMethod = radio.value;
        
        if (selectedMethod === 'card') {
            document.getElementById('phone-field').style.display = 'none';
            document.getElementById('card-field').style.display = 'block';
            if (!card) initStripe();
        } else {
            document.getElementById('phone-field').style.display = 'block';
            document.getElementById('card-field').style.display = 'none';
        }
    });
});

document.getElementById('pay-btn').onclick = async () => {
    const payBtn = document.getElementById('pay-btn');
    payBtn.disabled = true;
    payBtn.textContent = 'Traitement...';
    
    if (selectedMethod === 'card') {
        // Simuler Stripe (ou intégrer vraiment)
        setTimeout(() => {
            alert('Paiement par carte réussi (simulation)');
            localStorage.removeItem('cart');
            window.location.href = 'success.php';
        }, 1000);
    } else {
        const phone = document.getElementById('phone_number').value;
        if (!phone) {
            alert('Veuillez saisir votre numéro de téléphone');
            payBtn.disabled = false;
            payBtn.textContent = '✅ Confirmer et payer';
            return;
        }
        alert(`Paiement de <?= number_format($product['price'], 0, ',', ' ') ?> FCFA initié via ${selectedMethod === 'orange_money' ? 'Orange Money' : 'MTN MoMo'} au ${phone}`);
        localStorage.removeItem('cart');
        window.location.href = 'success.php';
    }
};

// Sélectionner le premier mode par défaut
document.querySelector('.payment-option').click();
</script>

<?php include 'includes/footer.php'; ?>