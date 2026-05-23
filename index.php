<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

// Traitement du changement d'image (admin uniquement)
$admin_message = '';
if (isLoggedIn() && getUser()['role'] == 'admin' && isset($_POST['update_image'])) {
    $product_id = (int)$_POST['product_id'];
    $image_url = trim($_POST['image_url']);
    
    if (!empty($image_url)) {
        $check = $pdo->prepare("SELECT id FROM product_images WHERE product_id = ?");
        $check->execute([$product_id]);
        
        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE product_images SET image_url = ? WHERE product_id = ?");
            $stmt->execute([$image_url, $product_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
            $stmt->execute([$product_id, $image_url]);
        }
        $admin_message = "Image mise à jour avec succès !";
    }
}

// Traitement de l'upload d'image
if (isLoggedIn() && getUser()['role'] == 'admin' && isset($_FILES['product_image'])) {
    $product_id = (int)$_POST['upload_product_id'];
    $target_dir = "uploads/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $filename = time() . '_' . basename($_FILES['product_image']['name']);
    $target_file = $target_dir . $filename;
    $image_url = 'uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $check = $pdo->prepare("SELECT id FROM product_images WHERE product_id = ?");
        $check->execute([$product_id]);
        
        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE product_images SET image_url = ? WHERE product_id = ?");
            $stmt->execute([$image_url, $product_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
            $stmt->execute([$product_id, $image_url]);
        }
        $admin_message = "Image uploadée avec succès !";
    } else {
        $admin_message = "Erreur lors de l'upload.";
    }
}

// Ajouter une image par défaut pour tous les produits qui n'en ont pas
$default_image = '/uploads/default.jpg';

// Vérifier si l'image par défaut existe, sinon la créer
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bini_dang_market/uploads/default.jpg')) {
    // Créer un dossier uploads s'il n'existe pas
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }
    // Note: Vous devez placer manuellement une image default.jpg dans le dossier uploads
}

$is_admin = (isLoggedIn() && getUser()['role'] == 'admin');
?>
<h1 class="page-title">Notre catalogue</h1>

<!-- Catégories avec icônes noires -->
<div class="categories-section">
    <h2>Catégories</h2>
    <div class="categories-grid">
        <?php foreach (getCategories() as $cat): ?>
        <a href="search.php?category=<?= urlencode($cat['name']) ?>" class="category-card">
            <div class="category-icon">
                <?php
                $icons = [
                    'Women\'s Fashion' => '👗',
                    'Men\'s Fashion' => '👔',
                    'Electronics' => '📱',
                    'Home & Lifestyle' => '🏠',
                    'Medicine' => '💊',
                    'Sports & Outdoor' => '⚽',
                    'Baby\'s & Toys' => '🍼',
                    'Groceries & Pets' => '🛒',
                    'Health & Beauty' => '💄',
                    'Phones' => '📱',
                    'Computers' => '💻',
                    'SmartWatch' => '⌚',
                    'Camera' => '📷',
                    'Headphones' => '🎧',
                    'Gaming' => '🎮',
                ];
                echo $icons[$cat['name']] ?? '📦';
                ?>
            </div>
            <span><?= htmlspecialchars($cat['name']) ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Message admin -->
<?php if ($is_admin && $admin_message): ?>
    <div style="background: #d1fae5; color: #16a34a; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <?= htmlspecialchars($admin_message) ?>
    </div>
<?php endif; ?>

<!-- Produits récents -->
<h2>Produits récents</h2>
<div class="products-grid">
    <?php foreach (getProducts(8) as $p): 
        // Si le produit n'a pas d'image, utiliser l'image par défaut
        $product_image = !empty($p['image']) ? $p['image'] : $default_image;
    ?>
    <div class="product-card">
        <img src="<?= htmlspecialchars($product_image) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
        <div class="product-info">
            <a href="product.php?id=<?= $p['id'] ?>" class="product-title"><?= htmlspecialchars($p['title']) ?></a>
            <div class="product-price"><?= number_format($p['price'], 0, ',', ' ') ?> FCFA</div>
            <div class="product-status <?= $p['status'] == 'available' ? 'status-available' : 'status-sold' ?>">
                <?= $p['status'] == 'available' ? '✓ Disponible' : '✗ Épuisé' ?>
            </div>
            <?php if ($p['status'] == 'available'): ?>
                <button onclick="addToCart(<?= $p['id'] ?>)" class="btn-add">Ajouter au panier</button>
            <?php else: ?>
                <button class="btn-add btn-disabled" disabled>Indisponible</button>
            <?php endif; ?>
            
            <!-- Interface admin pour modifier l'image -->
            <?php if ($is_admin): ?>
            <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #ccc;">
                <details>
                    <summary style="font-size: 0.7rem; color: #f97316; cursor: pointer;">🖼️ Changer l'image (admin)</summary>
                    <form method="POST" enctype="multipart/form-data" style="margin-top: 0.5rem;">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="file" name="product_image" accept="image/*" style="font-size: 0.7rem; width: 100%;">
                        <input type="hidden" name="upload_product_id" value="<?= $p['id'] ?>">
                        <button type="submit" style="background: #000; color: white; border: none; border-radius: 20px; padding: 0.2rem 0.5rem; font-size: 0.7rem; margin-top: 0.3rem; width: 100%;">Upload</button>
                    </form>
                    <form method="POST" style="margin-top: 0.3rem;">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="text" name="image_url" placeholder="URL de l'image" style="font-size: 0.7rem; width: 100%; padding: 0.2rem; margin-bottom: 0.3rem;">
                        <button type="submit" name="update_image" style="background: #f97316; color: white; border: none; border-radius: 20px; padding: 0.2rem 0.5rem; font-size: 0.7rem; width: 100%;">URL</button>
                    </form>
                </details>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function addToCart(id) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.push(id);
    localStorage.setItem('cart', JSON.stringify(cart));
    alert('Produit ajouté au panier');
}
</script>
<?php include 'includes/footer.php'; ?>