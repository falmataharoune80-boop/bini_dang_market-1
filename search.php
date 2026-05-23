<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

// Récupérer les paramètres de recherche
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$min_price = isset($_GET['min']) ? (float)$_GET['min'] : '';
$max_price = isset($_GET['max']) ? (float)$_GET['max'] : '';

// Construire la requête SQL
$sql = "SELECT p.*, 
               c.name as category_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'available'";

$params = [];

if (!empty($search)) {
    $sql .= " AND p.title LIKE ?";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $sql .= " AND c.name = ?";
    $params[] = $category;
}

if (!empty($min_price)) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Afficher le nombre de produits pour déboguer (à supprimer après)
// echo "<!-- " . count($products) . " produits trouvés -->";
?>

<style>
    .search-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    .search-header {
        margin-bottom: 2rem;
    }
    .search-header h1 {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }
    .search-info {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }
    .filters {
        background: white;
        padding: 1.5rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .filters form {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-group {
        flex: 1;
        min-width: 150px;
    }
    .filter-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: bold;
        margin-bottom: 0.3rem;
        color: #374151;
    }
    .filter-group input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 0.5rem;
    }
    .filter-group button {
        background: #000;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 40px;
        cursor: pointer;
        width: 100%;
    }
    .filter-group button:hover {
        background: #333;
    }
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    .product-card {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        transition: transform 0.2s;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .product-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .product-info {
        padding: 1rem;
    }
    .product-title {
        font-weight: 600;
        font-size: 1rem;
        color: #1f2937;
        text-decoration: none;
        display: block;
    }
    .product-title:hover {
        color: #f97316;
    }
    .product-price {
        font-weight: bold;
        font-size: 1.2rem;
        color: #000;
        margin: 0.5rem 0;
    }
    .product-status {
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
    }
    .status-available {
        color: #16a34a;
        font-weight: 600;
    }
    .status-sold {
        color: #dc2626;
        font-weight: 600;
    }
    .btn-add {
        background: #000;
        color: white;
        border: none;
        border-radius: 40px;
        padding: 0.5rem 0;
        width: 100%;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-add:hover {
        background: #333;
    }
    .no-results {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 1rem;
        color: #6b7280;
    }
    @media (max-width: 768px) {
        .filters form {
            flex-direction: column;
        }
        .filter-group button {
            margin-top: 0.5rem;
        }
    }
</style>

<div class="search-container">
    <div class="search-header">
        <h1>🔍 Recherche de produits</h1>
        <?php if (!empty($search) || !empty($category)): ?>
            <div class="search-info">
                <?php if (!empty($search)): ?>
                    Résultats pour <strong>"<?= htmlspecialchars($search) ?>"</strong>
                <?php endif; ?>
                <?php if (!empty($category)): ?>
                    dans la catégorie <strong><?= htmlspecialchars($category) ?></strong>
                <?php endif; ?>
                (<?= count($products) ?> produit(s) trouvé(s))
            </div>
        <?php endif; ?>
    </div>

    <!-- Formulaire de filtres -->
    <div class="filters">
        <form method="GET" action="">
            <?php if (!empty($search)): ?>
                <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>
            <?php if (!empty($category)): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <?php endif; ?>
            <div class="filter-group">
                <label>Prix minimum (FCFA)</label>
                <input type="number" name="min" placeholder="0" value="<?= $min_price ?: '' ?>" step="1000">
            </div>
            <div class="filter-group">
                <label>Prix maximum (FCFA)</label>
                <input type="number" name="max" placeholder="Ex: 100000" value="<?= $max_price ?: '' ?>" step="1000">
            </div>
            <div class="filter-group">
                <button type="submit">🔍 Filtrer</button>
            </div>
        </form>
    </div>

    <!-- Liste des produits -->
    <?php if (empty($products)): ?>
        <div class="no-results">
            🛒 Aucun produit trouvé.<br>
            <a href="index.php" style="color: #f97316;">Retour à l'accueil</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="<?= htmlspecialchars($p['image'] ?? '/uploads/default.jpg') ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                </a>
                <div class="product-info">
                    <a href="product.php?id=<?= $p['id'] ?>" class="product-title"><?= htmlspecialchars($p['title']) ?></a>
                    <div class="product-price"><?= number_format($p['price'], 0, ',', ' ') ?> FCFA</div>
                    <div class="product-status <?= $p['status'] == 'available' ? 'status-available' : 'status-sold' ?>">
                        <?= $p['status'] == 'available' ? '✓ Disponible' : '✗ Épuisé' ?>
                    </div>
                    <?php if ($p['status'] == 'available'): ?>
                        <button onclick="addToCart(<?= $p['id'] ?>)" class="btn-add">Ajouter au panier</button>
                    <?php else: ?>
                        <button class="btn-add" disabled style="background:#9ca3af; cursor:not-allowed;">Indisponible</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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