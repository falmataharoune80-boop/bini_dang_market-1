<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$categories = getCategories();
$error = '';
$success = '';
$is_edit = false;
$product_id = 0;
$product = null;
$existing_images = [];

// Vérifier si on est en mode édition
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        $is_edit = true;
        // Récupérer les images existantes
        $stmt_img = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
        $stmt_img->execute([$product_id]);
        $existing_images = $stmt_img->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $location = trim($_POST['location'] ?? 'Bini-Dang');
    $quantity = intval($_POST['quantity'] ?? 1);
    $status = $quantity > 0 ? 'available' : 'sold';
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $delete_images = isset($_POST['delete_images']) ? $_POST['delete_images'] : [];
    
    if (empty($title) || empty($description) || $price <= 0 || $category_id <= 0) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($quantity < 0) {
        $error = "La quantité ne peut pas être négative.";
    } else {
        // Créer le dossier uploads s'il n'existe pas
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }
        
        // Gestion des images uploadées
        $uploadedImages = [];
        
        // Upload des nouvelles images
        if (!empty($_FILES['images']['name'][0])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        $filename = time() . '_' . uniqid() . '_' . basename($_FILES['images']['name'][$i]);
                        $target_file = 'uploads/' . $filename;
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                            $uploadedImages[] = '/uploads/' . $filename;
                        }
                    }
                }
            }
        }
        
        // Gestion des images par URL
        if (!empty($_POST['image_urls'])) {
            $urls = explode("\n", trim($_POST['image_urls']));
            foreach ($urls as $url) {
                $url = trim($url);
                if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $uploadedImages[] = $url;
                }
            }
        }
        
        if ($edit_id > 0) {
            // Mise à jour du produit
            $stmt = $pdo->prepare("
                UPDATE products 
                SET title = ?, description = ?, price = ?, category_id = ?, location = ?, quantity = ?, status = ?
                WHERE id = ? AND seller_id = ?
            ");
            $stmt->execute([$title, $description, $price, $category_id, $location, $quantity, $status, $edit_id, $_SESSION['user_id']]);
            
            // Supprimer les images sélectionnées
            if (!empty($delete_images)) {
                foreach ($delete_images as $img_url) {
                    // Supprimer de la base de données
                    $stmt_del = $pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND image_url = ?");
                    $stmt_del->execute([$edit_id, $img_url]);
                    
                    // Supprimer le fichier physique si c'est une image locale
                    if (strpos($img_url, '/uploads/') === 0) {
                        $file_path = '.' . $img_url;
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
            
            // Ajouter les nouvelles images
            foreach ($uploadedImages as $imgUrl) {
                $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                $stmt_img->execute([$edit_id, $imgUrl]);
            }
            
            $success = "✅ Produit modifié avec succès !";
            header("Refresh:2; url=product.php?id=$edit_id");
            
        } else {
            // Nouveau produit
            $stmt = $pdo->prepare("
                INSERT INTO products (title, description, price, category_id, seller_id, location, quantity, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $description, $price, $category_id, $_SESSION['user_id'], $location, $quantity, $status]);
            $product_id = $pdo->lastInsertId();
            
            // Ajouter les images
            if (empty($uploadedImages)) {
                $uploadedImages[] = '/uploads/default.jpg';
            }
            foreach ($uploadedImages as $imgUrl) {
                $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                $stmt_img->execute([$product_id, $imgUrl]);
            }
            
            $success = "✅ Produit ajouté avec succès !";
            header("Refresh:2; url=product.php?id=$product_id");
        }
    }
}

include 'includes/header.php';
?>

<style>
    .form-container {
        max-width: 800px;
        margin: 2rem auto;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .form-header {
        background: #000;
        color: white;
        padding: 1.5rem;
        text-align: center;
        border-radius: 1rem 1rem 0 0;
    }
    .form-header h1 {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }
    .form-body {
        padding: 2rem;
    }
    .form-group {
        margin-bottom: 1.2rem;
    }
    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 0.4rem;
    }
    .form-group input, 
    .form-group select, 
    .form-group textarea {
        width: 100%;
        padding: 0.6rem;
        border: 1px solid #ddd;
        border-radius: 0.5rem;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .form-row-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    .btn-submit {
        background: #000;
        color: white;
        border: none;
        border-radius: 40px;
        padding: 0.75rem;
        width: 100%;
        font-size: 1rem;
        cursor: pointer;
    }
    .btn-submit:hover {
        background: #333;
    }
    .alert-success {
        background: #d1fae5;
        color: #16a34a;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    .alert-error {
        background: #fee2e2;
        color: #dc2626;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    /* Styles pour les images */
    .images-section {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.2rem;
    }
    .current-images {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    .image-item {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .image-item .delete-checkbox {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(0,0,0,0.6);
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .image-item .delete-checkbox input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .upload-info {
        font-size: 0.7rem;
        color: #666;
        margin-top: 0.3rem;
    }
    .image-urls textarea {
        font-family: monospace;
        font-size: 0.8rem;
    }
    
    @media (max-width: 640px) {
        .form-body { padding: 1rem; }
        .form-row, .form-row-3 { grid-template-columns: 1fr; gap: 0; }
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h1><?= $is_edit ? '✏️ Modifier le produit' : '📦 Publier une annonce' ?></h1>
        <p><?= $is_edit ? 'Modifiez les informations de votre produit' : 'Vendez vos produits facilement' ?></p>
    </div>
    <div class="form-body">
        <?php if ($success): ?>
            <div class="alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <?php if ($is_edit): ?>
                <input type="hidden" name="edit_id" value="<?= $product_id ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="title" value="<?= $is_edit ? htmlspecialchars($product['title']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="5" required><?= $is_edit ? htmlspecialchars($product['description']) : '' ?></textarea>
            </div>
            
            <div class="form-row-3">
                <div class="form-group">
                    <label>Prix (FCFA) *</label>
                    <input type="number" name="price" step="1" value="<?= $is_edit ? $product['price'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Quantité disponible *</label>
                    <input type="number" name="quantity" step="1" min="0" value="<?= $is_edit ? ($product['quantity'] ?? 1) : 1 ?>" required>
                    <small class="upload-info">Nombre d'articles disponibles à la vente</small>
                </div>
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="category_id" required>
                        <option value="">-- Sélectionnez --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($is_edit && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Localisation</label>
                <input type="text" name="location" value="<?= $is_edit ? htmlspecialchars($product['location']) : 'Bini-Dang' ?>">
            </div>
            
            <?php if ($is_edit): ?>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status">
                        <option value="available" <?= $product['status'] == 'available' ? 'selected' : '' ?>>Disponible</option>
                        <option value="sold" <?= $product['status'] == 'sold' ? 'selected' : '' ?>>Épuisé</option>
                    </select>
                </div>
            <?php endif; ?>
            
            <!-- Section Images -->
            <div class="images-section">
                <label>🖼️ Images du produit</label>
                
                <?php if ($is_edit && !empty($existing_images)): ?>
                    <div style="margin-top: 0.5rem;">
                        <p style="font-size: 0.8rem; margin-bottom: 0.5rem;">Images actuelles (cochez pour supprimer) :</p>
                        <div class="current-images">
                            <?php foreach ($existing_images as $img): ?>
                                <div class="image-item">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Image produit">
                                    <div class="delete-checkbox">
                                        <input type="checkbox" name="delete_images[]" value="<?= htmlspecialchars($img) ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 1rem;">
                    <p style="font-size: 0.8rem; margin-bottom: 0.5rem;">Ajouter de nouvelles images (fichiers) :</p>
                    <input type="file" name="images[]" multiple accept="image/*" style="padding: 0.3rem;">
                    <div class="upload-info">Formats acceptés : JPG, PNG, GIF, WEBP.</div>
                </div>
                
            </div>
            
            <button type="submit" class="btn-submit">
                <?= $is_edit ? '📤 Mettre à jour' : '📤 Publier l\'annonce' ?>
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>