<?php
// admin/combos.php - Combo Deals Management CMS (bundle multiple products at a special price)

$admin_page = 'combos';
require_once __DIR__ . '/admin_header.php';

$db = get_db();
$success_msg = '';
$error_msg = '';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// Delete Combo
if ($action === 'delete' && $edit_id > 0) {
    $stmt = $db->prepare("DELETE FROM combos WHERE id = ?");
    $stmt->execute([$edit_id]);
    $success_msg = 'Combo deal deleted successfully!';
    $action = 'list';
}

// Add or Edit Combo Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = sanitize_slug($_POST['slug'] ?? $name);
    $description = trim($_POST['description'] ?? '');
    $combo_price = floatval($_POST['combo_price'] ?? 0);
    $deal_ends_at = !empty($_POST['deal_ends_at']) ? date('Y-m-d H:i:s', strtotime($_POST['deal_ends_at'])) : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $product_ids = $_POST['product_ids'] ?? [];
    $product_qtys = $_POST['product_qtys'] ?? [];

    $image_upload = upload_image('image_file');
    $image_url_input = trim($_POST['image_url'] ?? '');

    if (empty($name) || empty($slug) || $combo_price <= 0) {
        $error_msg = 'Combo Name, Slug, and a valid Combo Price are required.';
    } elseif (count(array_filter($product_ids)) < 2) {
        $error_msg = 'Please select at least 2 products to bundle into this combo.';
    } else {
        if ($edit_id > 0) {
            $stmt_old = $db->prepare("SELECT image FROM combos WHERE id = ?");
            $stmt_old->execute([$edit_id]);
            $old_combo = $stmt_old->fetch();
            $existing_image = $old_combo ? $old_combo['image'] : '';

            $image = $image_upload ?: (!empty($image_url_input) ? $image_url_input : $existing_image);

            $stmt = $db->prepare("UPDATE combos SET name = ?, slug = ?, image = ?, description = ?, combo_price = ?, deal_ends_at = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $image, $description, $combo_price, $deal_ends_at, $is_active, $edit_id]);

            $db->prepare("DELETE FROM combo_items WHERE combo_id = ?")->execute([$edit_id]);
            $combo_id = $edit_id;
            $success_msg = 'Combo deal updated successfully!';
        } else {
            $image = $image_upload ?: $image_url_input;
            $stmt = $db->prepare("INSERT INTO combos (name, slug, image, description, combo_price, deal_ends_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $image, $description, $combo_price, $deal_ends_at, $is_active]);
            $combo_id = $db->lastInsertId();
            $success_msg = 'New combo deal created successfully!';
        }

        $item_stmt = $db->prepare("INSERT INTO combo_items (combo_id, product_id, qty) VALUES (?, ?, ?)");
        foreach ($product_ids as $i => $pid) {
            $pid = intval($pid);
            $qty = max(1, intval($product_qtys[$i] ?? 1));
            if ($pid > 0) {
                $item_stmt->execute([$combo_id, $pid, $qty]);
            }
        }
        $action = 'list';
    }
}

// Fetch single combo for editing
$edit_combo = null;
$edit_combo_items = [];
if ($action === 'edit' && $edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM combos WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_combo = $stmt->fetch();

    $item_stmt = $db->prepare("SELECT product_id, qty FROM combo_items WHERE combo_id = ?");
    $item_stmt->execute([$edit_id]);
    foreach ($item_stmt->fetchAll() as $row) {
        $edit_combo_items[$row['product_id']] = $row['qty'];
    }
}

// All products for the selection checklist
$all_products = $db->query("SELECT id, name, price, image FROM products ORDER BY name ASC")->fetchAll();

// Fetch all combos with item counts
$combos = $db->query("
    SELECT c.*, COUNT(ci.id) as item_count
    FROM combos c
    LEFT JOIN combo_items ci ON c.id = ci.combo_id
    GROUP BY c.id ORDER BY c.id DESC
")->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">Combo Deals CMS</h1>
        <p style="color:var(--text-muted);">Bundle multiple products together at one special combo price.</p>
    </div>
    <div>
        <?php if ($action === 'list'): ?>
            <a href="combos?action=add" class="btn-primary">+ Create New Combo</a>
        <?php else: ?>
            <a href="combos" class="btn-outline">&larr; Back to Combos List</a>
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
    <!-- Add / Edit Combo Form -->
    <div class="admin-card">
        <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
            <?= $action === 'edit' ? 'Edit Combo: ' . htmlspecialchars($edit_combo['name']) : 'Create New Combo Deal' ?>
        </h3>

        <form method="POST" action="combos?action=<?= $action ?>&id=<?= $edit_id ?>" enctype="multipart/form-data">
            <div class="grid-2">
                <div class="form-group">
                    <label>Combo Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit_combo['name'] ?? '') ?>" placeholder="Summer Bag & Perfume Bundle">
                </div>
                <div class="form-group">
                    <label>Combo URL Slug *</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($edit_combo['slug'] ?? '') ?>" placeholder="summer-bag-perfume-bundle">
                </div>
            </div>

            <div class="form-group">
                <label>Combo Image (Upload File or Enter URL)</label>
                <input type="file" name="image_file" class="form-control" accept="image/*" data-preview="comboPreview">
                <input type="text" name="image_url" class="form-control" style="margin-top:8px;" placeholder="Or paste image URL (https://...)" value="<?= htmlspecialchars($edit_combo['image'] ?? '') ?>">
                <?php if (!empty($edit_combo['image'])): ?>
                    <img id="comboPreview" src="<?= htmlspecialchars(get_image_url($edit_combo['image'], true)) ?>" style="max-height:120px; margin-top:10px; border-radius:8px; border:1px solid var(--border-color);">
                <?php else: ?>
                    <img id="comboPreview" style="max-height:120px; margin-top:10px; display:none; border-radius:8px; border:1px solid var(--border-color);">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" style="min-height:70px;"><?= htmlspecialchars($edit_combo['description'] ?? '') ?></textarea>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Combo Price (Rs.) *</label>
                    <input type="number" step="1" name="combo_price" class="form-control" required value="<?= htmlspecialchars($edit_combo['combo_price'] ?? '') ?>" placeholder="2999">
                    <small style="color:var(--text-muted);">The bundle's original total (sum of product prices) is calculated automatically for the "you save" badge.</small>
                </div>
                <div class="form-group">
                    <label>Deal Expiry (optional)</label>
                    <input type="datetime-local" name="deal_ends_at" class="form-control" value="<?= !empty($edit_combo['deal_ends_at']) ? date('Y-m-d\TH:i', strtotime($edit_combo['deal_ends_at'])) : '' ?>">
                </div>
            </div>

            <div class="form-group" style="margin-top:10px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($edit_combo) || $edit_combo['is_active']) ? 'checked' : '' ?> style="width:18px; height:18px;">
                    Combo is active and visible on the Deals page
                </label>
            </div>

            <h4 style="font-size:1.1rem; font-weight:700; margin-top:24px; margin-bottom:12px; color:var(--primary-color);">Select Products to Bundle (minimum 2)</h4>
            <div style="max-height:340px; overflow-y:auto; border:1px solid var(--border-color); border-radius:12px; padding:14px;">
                <?php foreach ($all_products as $p): ?>
                    <?php $checked_qty = $edit_combo_items[$p['id']] ?? null; ?>
                    <div style="display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid var(--bg-muted);">
                        <input type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>" <?= $checked_qty ? 'checked' : '' ?> style="width:18px; height:18px; flex-shrink:0;">
                        <img src="<?= htmlspecialchars(get_image_url($p['image'] ?: 'https://images.unsplash.com/photo-1566150905458-1bf1fc15a6a0?w=100&auto=format&fit=crop&q=80', true)) ?>" style="width:36px; height:36px; object-fit:cover; border-radius:6px;">
                        <div style="flex:1;">
                            <strong style="font-size:0.9rem;"><?= htmlspecialchars($p['name']) ?></strong>
                            <span style="display:block; font-size:0.8rem; color:var(--text-muted);">Rs. <?= number_format($p['price'], 0) ?></span>
                        </div>
                        <label style="font-size:0.8rem; color:var(--text-muted);">Qty
                            <input type="number" name="product_qtys[]" min="1" value="<?= $checked_qty ?: 1 ?>" class="form-control" style="width:60px; padding:6px; display:inline-block;">
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-primary" style="padding:14px 28px; margin-top:20px;">
                Save Combo Deal <i class="fa-solid fa-floppy-disk"></i>
            </button>
        </form>
    </div>

<?php else: ?>

    <!-- Combos List Table -->
    <div class="admin-card" style="overflow-x:auto;">
        <table class="cart-table responsive-stack">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Products</th>
                    <th>Combo Price</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($combos as $combo): ?>
                    <tr>
                        <td data-label="Image">
                            <img src="<?= htmlspecialchars(get_image_url($combo['image'] ?: 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=100&auto=format&fit=crop&q=80', true)) ?>" style="width:60px; height:50px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                        </td>
                        <td data-label="Name"><strong><?= htmlspecialchars($combo['name']) ?></strong></td>
                        <td data-label="Products">
                            <span style="background:var(--bg-muted); padding:4px 10px; border-radius:99px; font-weight:700;">
                                <?= $combo['item_count'] ?> items
                            </span>
                        </td>
                        <td data-label="Combo Price" style="font-weight:700; color:var(--primary-color);">Rs. <?= number_format($combo['combo_price'], 0) ?></td>
                        <td data-label="Expiry">
                            <?= $combo['deal_ends_at'] ? date('M j, Y H:i', strtotime($combo['deal_ends_at'])) : 'No expiry' ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($combo['is_active']): ?>
                                <span style="color:#166534; font-weight:600;">● Active</span>
                            <?php else: ?>
                                <span style="color:#991b1b; font-weight:600;">● Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="combos?action=edit&id=<?= $combo['id'] ?>" class="btn-outline" style="padding:6px 12px; font-size:0.85rem;">Edit</a>
                                <a href="combos?action=delete&id=<?= $combo['id'] ?>" onclick="return confirm('Are you sure you want to delete this combo deal?');" class="btn-outline" style="padding:6px 12px; font-size:0.85rem; color:#ef4444; border-color:#fee2e2;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($combos)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No combo deals created yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
