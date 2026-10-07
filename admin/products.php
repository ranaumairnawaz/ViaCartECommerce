<?php
// admin/products.php - Products Management CMS

$admin_page = 'products';
require_once __DIR__ . '/admin_header.php';

$db = get_db();
$success_msg = '';
$error_msg = '';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);
$cat_filter = intval($_GET['cat_id'] ?? 0);

// Delete Product
if ($action === 'delete' && $edit_id > 0) {
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $success_msg = 'Product deleted successfully!';
    $action = 'list';
}

// Add or Edit Product Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $deal_ends_at = !empty($_POST['deal_ends_at']) ? date('Y-m-d H:i:s', strtotime($_POST['deal_ends_at'])) : null;
    $stock = intval($_POST['stock'] ?? 10);
    $description = trim($_POST['description'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $colors = isset($_POST['colors']) && is_array($_POST['colors']) ? implode(',', array_map('trim', $_POST['colors'])) : '';
    $sizes = isset($_POST['sizes']) && is_array($_POST['sizes']) ? implode(',', array_map('trim', $_POST['sizes'])) : '';

    // Handle Image Upload or Text URL
    $image_upload = upload_image('image_file');
    $image_url_input = trim($_POST['image_url'] ?? '');

    // Handle Gallery Images
    $gallery_urls = !empty($_POST['gallery_urls']) ? array_map('trim', explode(',', $_POST['gallery_urls'])) : [];

    // Process multiple file uploads for gallery
    if (isset($_FILES['gallery_files']) && is_array($_FILES['gallery_files']['name'])) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0755, true);

        $allowed = ['jpg', 'jpeg', 'jfif', 'png', 'webp', 'gif', 'svg'];

        foreach ($_FILES['gallery_files']['name'] as $key => $gname) {
            if ($_FILES['gallery_files']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($gname, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $new_filename = uniqid('gimg_', true) . '.' . $ext;
                    $target_file = $upload_dir . $new_filename;
                    if (move_uploaded_file($_FILES['gallery_files']['tmp_name'][$key], $target_file)) {
                        $gallery_urls[] = 'uploads/' . $new_filename;
                    }
                }
            }
        }
    }

    // Filter empty values and create comma-separated string
    $gallery_urls = array_filter($gallery_urls);
    $gallery_images_str = implode(',', $gallery_urls);

    if (empty($name) || $category_id <= 0 || $price <= 0) {
        $error_msg = 'Product Name, valid Category, and Price are required.';
    } else {
        if ($edit_id > 0) {
            // Update
            $stmt_old = $db->prepare("SELECT image, gallery_images FROM products WHERE id = ?");
            $stmt_old->execute([$edit_id]);
            $old_prod = $stmt_old->fetch();
            $existing_image = $old_prod ? $old_prod['image'] : '';
            $existing_gallery = $old_prod ? $old_prod['gallery_images'] : '';

            $image = $image_upload ?: (!empty($image_url_input) ? $image_url_input : $existing_image);

            // Append new gallery images to existing, or if none uploaded, keep existing
            $final_gallery = !empty($gallery_images_str) ?
                (!empty($existing_gallery) ? $existing_gallery . ',' . $gallery_images_str : $gallery_images_str)
                : $existing_gallery;

            $stmt = $db->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, old_price = ?, deal_ends_at = ?, stock = ?, description = ?, colors = ?, sizes = ?, is_featured = ?, meta_title = ?, meta_description = ?, image = ?, gallery_images = ? WHERE id = ?");
            $stmt->execute([$name, $category_id, $price, $old_price, $deal_ends_at, $stock, $description, $colors, $sizes, $is_featured, $meta_title, $meta_description, $image, $final_gallery, $edit_id]);
            $success_msg = 'Product updated successfully!';
        } else {
            // Insert
            $image = $image_upload ?: $image_url_input;
            $stmt = $db->prepare("INSERT INTO products (name, category_id, price, old_price, deal_ends_at, stock, description, colors, sizes, image, gallery_images, is_featured, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category_id, $price, $old_price, $deal_ends_at, $stock, $description, $colors, $sizes, $image, $gallery_images_str, $is_featured, $meta_title, $meta_description]);
            $success_msg = 'New product added to catalog successfully!';
        }
        $action = 'list';
    }
}

// Fetch edit product
$edit_product = null;
if ($action === 'edit' && $edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
}

// Fetch categories dropdown
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch products list with category name
$where_clause = $cat_filter > 0 ? "WHERE p.category_id = $cat_filter" : "";
$products = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    $where_clause 
    ORDER BY p.id DESC
")->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">Products CMS</h1>
        <p style="color:var(--text-muted);">Create, edit, and manage product inventory, pricing, and SEO data.</p>
    </div>
    <div>
        <?php if ($action === 'list'): ?>
            <a href="products?action=add" class="btn-primary">+ Add New Product</a>
        <?php else: ?>
            <a href="products" class="btn-outline">&larr; Back to Products List</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($success_msg)): ?>
    <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>

    <!-- Product Add / Edit Form -->
    <div class="admin-card">
        <h3
            style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
            <?= $action === 'edit' ? 'Edit Product: ' . htmlspecialchars($edit_product['name']) : 'Add New Product' ?>
        </h3>

        <form method="POST" action="products?action=<?= $action ?>&id=<?= $edit_id ?>" enctype="multipart/form-data">
            <div class="grid-2" style="grid-template-columns:2fr 1fr;">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>" placeholder="Wylabrinna Envelope Bag">
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($edit_product['category_id']) && $edit_product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label>Selling Price (Rs.) *</label>
                    <input type="number" step="1" name="price" class="form-control" required
                        value="<?= htmlspecialchars($edit_product['price'] ?? '') ?>" placeholder="1490">
                </div>

                <div class="form-group">
                    <label>Original MSRP / Old Price (Rs.)</label>
                    <input type="number" step="1" name="old_price" class="form-control"
                        value="<?= htmlspecialchars($edit_product['old_price'] ?? '') ?>" placeholder="1890">
                    <small style="color:var(--text-muted);">Set this higher than Selling Price to auto-list it on the Deals
                        page.</small>
                </div>

                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock" class="form-control" required
                        value="<?= htmlspecialchars($edit_product['stock'] ?? 15) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Deal Expiry (optional)</label>
                <input type="datetime-local" name="deal_ends_at" class="form-control"
                    value="<?= !empty($edit_product['deal_ends_at']) ? date('Y-m-d\TH:i', strtotime($edit_product['deal_ends_at'])) : '' ?>">
                <small style="color:var(--text-muted);">If set, a countdown timer shows on the Deals page and the discount
                    disappears automatically after this time. Leave blank for an ongoing deal.</small>
            </div>

            <div class="form-group">
                <label>Product Main Image (Upload File or Enter URL)</label>
                <input type="file" name="image_file" class="form-control" accept="image/*" data-preview="prodPreview">
                <input type="text" name="image_url" class="form-control" style="margin-top:8px;"
                    placeholder="Or paste main product image URL..."
                    value="<?= htmlspecialchars($edit_product['image'] ?? '') ?>">
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Supports WEBP, GIF, PNG, JPG,
                    JFIF, SVG, and all standard image formats.</small>
                <?php if (!empty($edit_product['image'])): ?>
                    <img id="prodPreview" src="<?= htmlspecialchars(get_image_url($edit_product['image'], true)) ?>"
                        style="max-height:120px; margin-top:10px; border-radius:8px; border:1px solid var(--border-color);">
                <?php else: ?>
                    <img id="prodPreview"
                        style="max-height:120px; margin-top:10px; display:none; border-radius:8px; border:1px solid var(--border-color);">
                <?php endif; ?>
            </div>

            <div class="form-group"
                style="background:var(--bg-muted); padding:16px; border-radius:12px; border:1px dashed var(--border-color);">
                <label>Gallery Images (Optional - upload multiple or paste comma-separated URLs)</label>
                <input type="file" name="gallery_files[]" multiple class="form-control" accept="image/*">
                <textarea name="gallery_urls" class="form-control" style="margin-top:8px; min-height:60px;"
                    placeholder="Paste image URLs separated by comma..."></textarea>
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Uploading new images will append them
                    to the existing gallery.</small>

                <?php
                $existing_gallery = !empty($edit_product['gallery_images']) ? explode(',', $edit_product['gallery_images']) : [];
                if (!empty($existing_gallery)):
                    ?>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:10px;">
                        <?php foreach ($existing_gallery as $gimg): ?>
                            <img src="<?= htmlspecialchars(get_image_url($gimg, true)) ?>"
                                style="width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control"
                    style="min-height:100px;"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
            </div>

            <?php
            $all_colors = ['Red', 'Pink', 'Black', 'White', 'Brown', 'Beige', 'Navy Blue', 'Burgundy', 'Grey', 'Olive Green', 'Camel', 'Sky Blue'];
            $all_sizes = ['S' => 'Small (S)', 'M' => 'Medium (M)', 'L' => 'Large (L)', 'XL' => 'Extra Large (XL)'];
            $sel_colors = !empty($edit_product['colors']) ? array_map('trim', explode(',', $edit_product['colors'])) : [];
            $sel_sizes = !empty($edit_product['sizes']) ? array_map('trim', explode(',', $edit_product['sizes'])) : [];
            $color_swatches = [
                'Red' => '#ef4444',
                'Pink' => '#f472b6',
                'Black' => '#1a1a1a',
                'White' => '#f5f5f5',
                'Brown' => '#92400e',
                'Beige' => '#d4b896',
                'Navy Blue' => '#1e3a5f',
                'Burgundy' => '#800020',
                'Grey' => '#9ca3af',
                'Olive Green' => '#6b7c43',
                'Camel' => '#c19a6b',
                'Sky Blue' => '#7dd3fc',
            ];
            ?>

            <!-- Colors -->
            <div class="form-group">
                <label style="font-weight:700; margin-bottom:10px; display:block;">Available Colors</label>
                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                    <?php foreach ($all_colors as $color): ?>
                        <label
                            style="display:flex; align-items:center; gap:8px; cursor:pointer; background:var(--bg-muted); padding:8px 14px; border-radius:8px; border:2px solid <?= in_array($color, $sel_colors) ? 'var(--primary-color)' : 'var(--border-color)' ?>;">
                            <input type="checkbox" name="colors[]" value="<?= htmlspecialchars($color) ?>" <?= in_array($color, $sel_colors) ? 'checked' : '' ?> style="display:none;"
                                onchange="this.closest('label').style.borderColor = this.checked ? 'var(--primary-color)' : 'var(--border-color)'">
                            <span
                                style="width:18px; height:18px; border-radius:50%; background:<?= $color_swatches[$color] ?>; border:2px solid rgba(0,0,0,0.12); flex-shrink:0; display:inline-block;"></span>
                            <span style="font-size:0.88rem; font-weight:600;"><?= htmlspecialchars($color) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color:var(--text-muted); margin-top:6px; display:block;">Select all colors this product is
                    available in.</small>
            </div>

            <!-- Sizes -->
            <div class="form-group">
                <label style="font-weight:700; margin-bottom:10px; display:block;">Available Sizes</label>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($all_sizes as $sval => $slabel): ?>
                        <label
                            style="display:flex; align-items:center; gap:8px; cursor:pointer; background:var(--bg-muted); padding:10px 20px; border-radius:8px; border:2px solid <?= in_array($sval, $sel_sizes) ? 'var(--primary-color)' : 'var(--border-color)' ?>; font-weight:700;">
                            <input type="checkbox" name="sizes[]" value="<?= htmlspecialchars($sval) ?>" <?= in_array($sval, $sel_sizes) ? 'checked' : '' ?> style="display:none;"
                                onchange="this.closest('label').style.borderColor = this.checked ? 'var(--primary-color)' : 'var(--border-color)'">
                            <?= htmlspecialchars($slabel) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color:var(--text-muted); margin-top:6px; display:block;">Select all sizes this product is
                    available in.</small>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_featured" value="1" <?= (!isset($edit_product['is_featured']) || $edit_product['is_featured'] == 1) ? 'checked' : '' ?>>
                    <span>Show in Homepage Featured Products Section</span>
                </label>
            </div>

            <h4 style="font-size:1.1rem; font-weight:700; margin-top:30px; margin-bottom:16px; color:var(--primary-color);">
                Product SEO Metadata</h4>
            <div class="grid-2">
                <div class="form-group">
                    <label>SEO Title Tag</label>
                    <input type="text" name="meta_title" class="form-control"
                        value="<?= htmlspecialchars($edit_product['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>SEO Meta Description</label>
                    <textarea name="meta_description" class="form-control"
                        style="min-height:60px;"><?= htmlspecialchars($edit_product['meta_description'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="padding:14px 28px;">
                Save Product <i class="fa-solid fa-floppy-disk"></i>
            </button>
        </form>
    </div>

<?php else: ?>

    <!-- Products Table -->
    <div class="admin-card" style="overflow-x:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <form method="GET" action="products" style="display:flex; gap:10px; align-items:center;">
                    <label style="font-size:0.9rem; font-weight:600;">Filter by Category:</label>
                    <select name="cat_id" class="form-control" style="width:200px; padding:8px;"
                        onchange="this.form.submit()">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat_filter == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <table class="cart-table responsive-stack">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td data-label="Image">
                            <img src="<?= htmlspecialchars(get_image_url($prod['image'] ?: 'https://images.unsplash.com/photo-1566150905458-1bf1fc15a6a0?w=100&auto=format&fit=crop&q=80', true)) ?>"
                                style="width:50px; height:50px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                        </td>
                        <td data-label="Title">
                            <strong><?= htmlspecialchars($prod['name']) ?></strong>
                        </td>
                        <td data-label="Category"><?= htmlspecialchars($prod['category_name']) ?></td>
                        <td data-label="Price">
                            <strong style="color:var(--primary-color);">Rs. <?= number_format($prod['price'], 0) ?></strong>
                            <?php if ($prod['old_price']): ?>
                                <span
                                    style="font-size:0.8rem; color:var(--text-light); text-decoration:line-through; display:block;">Rs.
                                    <?= number_format($prod['old_price'], 0) ?></span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Stock">
                            <?php if ($prod['stock'] > 0): ?>
                                <span style="color:#166534; font-weight:600;"><?= $prod['stock'] ?> units</span>
                            <?php else: ?>
                                <span style="color:#991b1b; font-weight:600;">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Featured">
                            <?= $prod['is_featured'] ? '<i class="fa-solid fa-star"></i> Yes' : 'No' ?>
                        </td>
                        <td data-label="Actions">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="products?action=edit&id=<?= $prod['id'] ?>" class="btn-outline"
                                    style="padding:6px 12px; font-size:0.85rem;">Edit</a>
                                <a href="products?action=delete&id=<?= $prod['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this product?');"
                                    class="btn-outline"
                                    style="padding:6px 12px; font-size:0.85rem; color:#ef4444; border-color:#fee2e2;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>