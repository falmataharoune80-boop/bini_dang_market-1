<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<style>
    .cart-container {
        max-width: 1000px;
        margin: 2rem auto;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .cart-header {
        background: #000;
        color: white;
        padding: 1rem 1.5rem;
        font-size: 1.2rem;
        font-weight: bold;
    }
    .cart-items {
        padding: 1.5rem;
    }
    .cart-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .cart-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 0.5rem;
    }
    .cart-item-info {
        flex: 1;
    }
    .cart-item-title {
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    .cart-item-price {
        color: #000;
        font-weight: bold;
    }
    .cart-item-remove {
        background: #dc2626;
        color: white;
        border: none;
        border-radius: 40px;
        padding: 0.3rem 0.8rem;
        cursor: pointer;
        font-size: 0.7rem;
    }
    .cart-item-remove:hover {
        background: #b91c1c;
    }
    .cart-total {
        padding: 1rem;
        text-align: right;
        font-size: 1.2rem;
        font-weight: bold;
        border-top: 2px solid #e5e7eb;
        background: #f9fafb;
    }
    .cart-actions {
        display: flex;
        justify-content: space-between;
        padding: 1rem;
        background: #f9fafb;
        gap: 1rem;
    }
    .btn-continue {
        background: transparent;
        border: 1px solid #000;
        color: #000;
        padding: 0.6rem 1.2rem;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-continue:hover {
        background: #000;
        color: white;
    }
    .btn-checkout {
        background: #000;
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.2s;
    }
    .btn-checkout:hover {
        background: #333;
    }
    .empty-cart {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }
    @media (max-width: 640px) {
        .cart-item {
            flex-direction: column;
            text-align: center;
        }
        .cart-actions {
            flex-direction: column;
        }
        .btn-continue, .btn-checkout {
            text-align: center;
        }
    }
</style>

<div class="cart-container">
    <div class="cart-header">
        🛒 Mon panier
    </div>
    <div id="cart-content">
        <div class="cart-items">
            <div class="empty-cart">Chargement...</div>
        </div>
    </div>
</div>

<script>
function loadCart() {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        document.getElementById('cart-content').innerHTML = `
            <div class="cart-items">
                <div class="empty-cart">
                    🛒 Votre panier est vide<br><br>
                    <a href="index.php" class="btn-continue">Continuer mes achats</a>
                </div>
            </div>
        `;
        return;
    }
    
    let ids = cart.join(',');
    
    fetch('api/products.php?ids=' + ids)
        .then(res => res.json())
        .then(products => {
            let itemsHtml = '<div class="cart-items">';
            let total = 0;
            
            products.forEach(p => {
                itemsHtml += `
                    <div class="cart-item">
                        <img src="${p.image || '/uploads/default.jpg'}" alt="${escapeHtml(p.title)}">
                        <div class="cart-item-info">
                            <div class="cart-item-title">${escapeHtml(p.title)}</div>
                            <div class="cart-item-price">${Number(p.price).toLocaleString()} FCFA</div>
                        </div>
                        <button onclick="removeFromCart(${p.id})" class="cart-item-remove">🗑️ Supprimer</button>
                    </div>
                `;
                total += parseFloat(p.price);
            });
            
            itemsHtml += `
                    <div class="cart-total">
                        Total : ${total.toLocaleString()} FCFA
                    </div>
                    <div class="cart-actions">
                        <a href="index.php" class="btn-continue">Continuer mes achats</a>
                        <a href="checkout.php?cart=1" class="btn-checkout">Passer à la caisse</a>
                    </div>
                </div>
            `;
            
            document.getElementById('cart-content').innerHTML = itemsHtml;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('cart-content').innerHTML = `
                <div class="cart-items">
                    <div class="empty-cart">Erreur de chargement du panier</div>
                </div>
            `;
        });
}

function removeFromCart(id) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart = cart.filter(item => item != id);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

loadCart();
</script>

<?php include 'includes/footer.php'; ?>