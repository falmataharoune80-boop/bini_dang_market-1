// Fonction pour ajouter au panier
function addToCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.push(productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    alert('Produit ajouté au panier !');
}

// Fonction pour charger le panier
function loadCart() {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    return cart;
}

// Fonction pour vider le panier
function clearCart() {
    localStorage.removeItem('cart');
}

// Exporter les fonctions pour une utilisation globale
window.addToCart = addToCart;
window.loadCart = loadCart;
window.clearCart = clearCart;