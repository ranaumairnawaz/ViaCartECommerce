<?php
// admin/categories.php - Categories Management CMS

$admin_page = 'categories';
require_once __DIR__ . '/admin_header.php';

$db = get_db();
$success_msg = '';
$error_msg = '';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// Delete Category
if ($action === 'delete' && $edit_id > 0) {
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $success_msg = 'Category deleted successfully!';
    $action = 'list';
}

// Add or Edit Category Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = sanitize_slug($_POST['slug'] ?? $name);
    $description = trim($_POST['description'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');

    // Handle Image Upload or Text URL
    $image_upload = upload_image('image_file');
    $image_url_input = trim($_POST['image_url'] ?? '');

    if (empty($name) || empty($slug)) {
        $error_msg = 'Category Name and Slug are required.';
    } else {
        if ($edit_id > 0) {
            // Update
            $stmt_old = $db->prepare("SELECT image FROM categories WHERE id = ?");
            $stmt_old->execute([$edit_id]);
            $old_cat = $stmt_old->fetch();
            $existing_image = $old_cat ? $old_cat['image'] : '';

            $image = $image_upload ?: (!empty($image_url_input) ? $image_url_input : $existing_image);

            $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, image = ?, description = ?, meta_title = ?, meta_description = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $image, $description, $meta_title, $meta_description, $edit_id]);
            $success_msg = 'Category updated successfully!';
        } else {
            // Insert
            $image = $image_upload ?: $image_url_input;
            $stmt = $db->prepare("INSERT INTO categories (name, slug, image, description, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $image, $description, $meta_title, $meta_description]);
            $success_msg = 'New category added successfully!';
        }
        $action = 'list';
    }
}

// Fetch single category for editing
$edit_category = null;
if ($action === 'edit' && $edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch();
}

// Fetch all categories
$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC")->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">Categories CMS</h1>
        <p style="color:var(--text-muted);">Manage product categories displayed on homepage and shop navigation.</p>
    </div>
    <div>
        <?php if ($action === 'list'): ?>
            <a href="categories?action=add" class="btn-primary">+ Add New Category</a>
        <?php else: ?>
            <a href="categories" class="btn-outline">&larr; Back to Categories List</a>
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
    <!-- Add / Edit Category Form -->
    <div class="admin-card">
        <h3
            style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
            <?= $action === 'edit' ? 'Edit Category: ' . htmlspecialchars($edit_category['name']) : 'Add New Category' ?>
        </h3>

        <form method="POST" action="categories?action=<?= $action ?>&id=<?= $edit_id ?>" enctype="multipart/form-data">
            <div class="grid-2">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?= htmlspecialchars($edit_category['name'] ?? '') ?>" placeholder="Envelope Bag">
                </div>
                <div class="form-group">
                    <label>Category URL Slug *</label>
                    <input type="text" name="slug" class="form-control"
                        value="<?= htmlspecialchars($edit_category['slug'] ?? '') ?>" placeholder="envelope-bag">
                </div>
            </div>

            <div class="form-group">
                <label>Category Image (Upload File or Enter URL)</label>
                <input type="file" name="image_file" class="form-control" accept="image/*" data-preview="catPreview">
                <input type="text" name="image_url" class="form-control" style="margin-top:8px;"
                    placeholder="Or paste image URL (https://...)"
                    value="<?= htmlspecialchars($edit_category['image'] ?? '') ?>">
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Supports WEBP, GIF, PNG, JPG, JFIF,
                    SVG, and all standard image formats.</small>
                <?php if (!empty($edit_category['image'])): ?>
                    <img id="catPreview" src="<?= htmlspecialchars(get_image_url($edit_category['image'], true)) ?>"
                        style="max-height:120px; margin-top:10px; border-radius:8px; border:1px solid var(--border-color);">
                <?php else: ?>
                    <img id="catPreview"
                        style="max-height:120px; margin-top:10px; display:none; border-radius:8px; border:1px solid var(--border-color);">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control"
                    style="min-height:80px;"><?= htmlspecialchars($edit_category['description'] ?? '') ?></textarea>
            </div>

            <h4 style="font-size:1.1rem; font-weight:700; margin-top:30px; margin-bottom:16px; color:var(--primary-color);">
                Category SEO Metadata</h4>
            <div class="grid-2">
                <div class="form-group">
                    <label>SEO Title Tag</label>
                    <input type="text" name="meta_title" class="form-control"
                        value="<?= htmlspecialchars($edit_category['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>SEO Meta Description</label>
                    <textarea name="meta_description" class="form-control"
                        style="min-height:60px;"><?= htmlspecialchars($edit_category['meta_description'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="padding:14px 28px;">
                Save Category <i class="fa-solid fa-floppy-disk"></i>
            </button>
        </form>
    </div>

<?php else: ?>

    <!-- Categories List Table -->
    <div class="admin-card" style="overflow-x:auto;">
        <table class="cart-table responsive-stack">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products Count</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td data-label="Image">
                            <img src="<?= htmlspecialchars(get_image_url($cat['image'] ?: 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=100&auto=format&fit=crop&q=80', true)) ?>"
                                style="width:60px; height:50px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                        </td>
                        <td data-label="Name"><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                        <td data-label="Slug"><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                        <td data-label="Products">
                            <span style="background:var(--bg-muted); padding:4px 10px; border-radius:99px; font-weight:700;">
                                <?= $cat['product_count'] ?> items
                            </span>
                        </td>
                        <td data-label="Description" style="color:var(--text-muted); max-width:260px;">
                            <?= htmlspecialchars(substr($cat['description'] ?? '', 0, 80)) ?>...</td>
                        <td data-label="Actions">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="categories?action=edit&id=<?= $cat['id'] ?>" class="btn-outline"
                                    style="padding:6px 12px; font-size:0.85rem;">Edit</a>
                                <a href="categories?action=delete&id=<?= $cat['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this category? All assigned products will be affected.');"
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