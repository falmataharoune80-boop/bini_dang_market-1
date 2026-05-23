<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$id = (int)$_GET['id'];

// Récupérer les détails du produit (incluant la localisation du vendeur)
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, 
           u.name as seller_name, u.id as seller_id, u.phone as seller_phone, u.email as seller_email, u.location as seller_location,
           (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<style>
    .product-detail {
        max-width: 1000px;
        margin: 2rem auto;
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .product-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        padding: 2rem;
    }
    .product-detail-image img {
        width: 100%;
        border-radius: 0.5rem;
    }
    .product-detail-info h1 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .product-detail-price {
        font-size: 1.8rem;
        font-weight: bold;
        color: #000000;
        margin: 0.5rem 0;
    }
    .product-description {
        margin: 1rem 0;
        line-height: 1.6;
    }
    
    /* Style pour le statut du produit */
    .product-status {
        display: inline-block;
        padding: 0.3rem 1rem;
        border-radius: 30px;
        font-weight: bold;
        margin: 0.5rem 0;
    }
    .status-available {
        background-color: #d1fae5;
        color: #16a34a;
    }
    .status-sold {
        background-color: #fee2e2;
        color: #dc2626;
    }
    
    .product-seller {
        margin: 1rem 0;
        padding: 1rem;
        background: #f3f4f6;
        border-radius: 0.5rem;
    }
    .product-seller h3 {
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }
    .seller-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .seller-info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }
    .seller-info-item i {
        width: 24px;
        color: #f97316;
    }
    
    .product-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }
    .btn-add, .btn-buy {
        background-color: #000000;
        color: white;
        border: none;
        border-radius: 40px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        flex: 1;
    }
    .btn-add:hover, .btn-buy:hover {
        background-color: #333333;
    }
    .btn-contact {
        background-color: transparent;
        color: #000000;
        border: 1px solid #000000;
        border-radius: 40px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        flex: 1;
    }
    .btn-contact:hover {
        background-color: #000000;
        color: white;
    }
    .btn-edit {
        background-color: #f97316;
        color: white;
        border: none;
        border-radius: 40px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        flex: 1;
    }
    .btn-disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
    }
    @media (max-width: 768px) {
        .product-detail-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            padding: 1rem;
        }
        .product-actions {
            flex-direction: column;
        }
    }
</style>

<div class="product-detail">
    <div class="product-detail-grid">
        <div class="product-detail-image">
            <img src="<?= htmlspecialchars($product['image'] ?? '/uploads/default.jpg') ?>" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>
        <div class="product-detail-info">
            <h1><?= htmlspecialchars($product['title']) ?></h1>
            <div class="product-detail-price"><?= number_format($product['price'], 0, ',', ' ') ?> FCFA</div>
            
            <!-- Statut du produit -->
            <div class="product-status <?= $product['status'] == 'available' ? 'status-available' : 'status-sold' ?>">
                <?= $product['status'] == 'available' ? '✓ Disponible' : '✗ Épuisé' ?>
            </div>
            
            <div class="product-description">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
            
            <div class="product-seller">
                
                <div class="seller-info">
                    <div class="seller-info-item">
                        <i></i> <strong>Nom :</strong> <?= htmlspecialchars($product['seller_name']) ?>
                    </div>
                    <div class="seller-info-item">
                        <i>📍</i> <strong>Localisation :</strong> <?= htmlspecialchars($product['seller_location'] ?? 'Bini-Dang') ?>
                    </div>
                    <div class="seller-info-item">
                        <i></i> <strong>Téléphone :</strong> <?= htmlspecialchars($product['seller_phone'] ?? 'Non renseigné') ?>
                    </div>
                    <div class="seller-info-item">
                        <i></i> <strong>Email :</strong> <?= htmlspecialchars($product['seller_email']) ?>
                    </div>
                </div>
            </div>
            
            <div class="product-actions">
                <?php if ($product['status'] == 'available'): ?>
                    
                    <?php if (isLoggedIn() && $_SESSION['user_id'] != $product['seller_id']): ?>
                        <!-- ✅ Acheteur DIFFÉRENT du vendeur : peut acheter -->
                        <a href="checkout.php?product_id=<?= $product['id'] ?>" class="btn-buy"> Acheter maintenant</a>
                        <button onclick="addToCart(<?= $product['id'] ?>)" class="btn-add">🛒 Ajouter au panier</button>
                        
                    <?php elseif (isLoggedIn() && $_SESSION['user_id'] == $product['seller_id']): ?>
                        <!-- 🚫 Le vendeur voit son propre produit : ne peut pas acheter -->
                        <button class="btn-add btn-disabled" disabled> Vous êtes le vendeur</button>
                        
                    <?php else: ?>
                        <!-- 🔒 Utilisateur non connecté -->
                        <a href="login.php" class="btn-add">🔐 Connectez-vous pour acheter</a>
                    <?php endif; ?>
                    
                    <!-- 💬 Bouton Contacter (tout le monde peut contacter) -->
                    <?php if (isLoggedIn() && $_SESSION['user_id'] != $product['seller_id']): ?>
                        <a href="chat.php?with=<?= $product['seller_id'] ?>" class="btn-contact">💬 Contacter le vendeur</a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php" class="btn-contact">🔐 Connectez-vous pour contacter</a>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <button class="btn-add btn-disabled" disabled>❌ Produit épuisé</button>
                <?php endif; ?>
            </div>
            
            <!-- Bouton Modifier (visible uniquement pour le vendeur) -->
            <?php if (isLoggedIn() && $_SESSION['user_id'] == $product['seller_id']): ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="post-ad.php?id=<?= $product['id'] ?>" class="btn-edit"> Modifier ce produit</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function addToCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.push(productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    alert('✓ Produit ajouté au panier !');
}
</script>

<?php include 'includes/footer.php'; ?>