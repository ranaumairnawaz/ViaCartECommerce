<?php
// admin/coupons.php - Coupon Codes Management CMS

$admin_page = 'coupons';
require_once __DIR__ . '/admin_header.php';

$db = get_db();
$success_msg = '';
$error_msg = '';

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// Delete Coupon
if ($action === 'delete' && $edit_id > 0) {
    $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$edit_id]);
    $success_msg = 'Coupon deleted successfully!';
    $action = 'list';
}

// Add or Edit Coupon Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $discount_type = ($_POST['discount_type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent';
    $discount_value = floatval($_POST['discount_value'] ?? 0);
    $min_order_amount = !empty($_POST['min_order_amount']) ? floatval($_POST['min_order_amount']) : null;
    $max_uses = !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null;
    $starts_at = !empty($_POST['starts_at']) ? date('Y-m-d H:i:s', strtotime($_POST['starts_at'])) : null;
    $expires_at = !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($code) || $discount_value <= 0) {
        $error_msg = 'Coupon Code and a valid Discount Value are required.';
    } else {
        try {
            if ($edit_id > 0) {
                $stmt = $db->prepare("UPDATE coupons SET code = ?, description = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, max_uses = ?, starts_at = ?, expires_at = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$code, $description, $discount_type, $discount_value, $min_order_amount, $max_uses, $starts_at, $expires_at, $is_active, $edit_id]);
                $success_msg = 'Coupon updated successfully!';
            } else {
                $stmt = $db->prepare("INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_uses, starts_at, expires_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $description, $discount_type, $discount_value, $min_order_amount, $max_uses, $starts_at, $expires_at, $is_active]);
                $success_msg = 'New coupon code created successfully!';
            }
            $action = 'list';
        } catch (PDOException $e) {
            $error_msg = (strpos($e->getMessage(), 'Duplicate') !== false) ? 'That coupon code already exists. Choose a different code.' : 'Failed to save coupon.';
        }
    }
}

// Fetch single coupon for editing
$edit_coupon = null;
if ($action === 'edit' && $edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_coupon = $stmt->fetch();
}

// Fetch all coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">Coupon Codes CMS</h1>
        <p style="color:var(--text-muted);">Create discount codes customers can apply at checkout.</p>
    </div>
    <div>
        <?php if ($action === 'list'): ?>
            <a href="coupons?action=add" class="btn-primary">+ Create New Coupon</a>
        <?php else: ?>
            <a href="coupons" class="btn-outline">&larr; Back to Coupons List</a>
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
    <!-- Add / Edit Coupon Form -->
    <div class="admin-card">
        <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
            <?= $action === 'edit' ? 'Edit Coupon: ' . htmlspecialchars($edit_coupon['code']) : 'Create New Coupon Code' ?>
        </h3>

        <form method="POST" action="coupons?action=<?= $action ?>&id=<?= $edit_id ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label>Coupon Code *</label>
                    <input type="text" name="code" class="form-control" required style="text-transform:uppercase;" value="<?= htmlspecialchars($edit_coupon['code'] ?? '') ?>" placeholder="SAVE10">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($edit_coupon['description'] ?? '') ?>" placeholder="10% off your order">
                </div>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label>Discount Type *</label>
                    <select name="discount_type" class="form-control">
                        <option value="percent" <?= (($edit_coupon['discount_type'] ?? 'percent') === 'percent') ? 'selected' : '' ?>>Percentage (%)</option>
                        <option value="fixed" <?= (($edit_coupon['discount_type'] ?? '') === 'fixed') ? 'selected' : '' ?>>Fixed Amount (Rs.)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Discount Value *</label>
                    <input type="number" step="1" name="discount_value" class="form-control" required value="<?= htmlspecialchars($edit_coupon['discount_value'] ?? '') ?>" placeholder="10">
                </div>
                <div class="form-group">
                    <label>Minimum Order Amount (Rs.)</label>
                    <input type="number" step="1" name="min_order_amount" class="form-control" value="<?= htmlspecialchars($edit_coupon['min_order_amount'] ?? '') ?>" placeholder="Optional">
                </div>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label>Max Total Uses</label>
                    <input type="number" name="max_uses" class="form-control" value="<?= htmlspecialchars($edit_coupon['max_uses'] ?? '') ?>" placeholder="Unlimited if blank">
                </div>
                <div class="form-group">
                    <label>Starts At (optional)</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="<?= !empty($edit_coupon['starts_at']) ? date('Y-m-d\TH:i', strtotime($edit_coupon['starts_at'])) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Expires At (optional)</label>
                    <input type="datetime-local" name="expires_at" class="form-control" value="<?= !empty($edit_coupon['expires_at']) ? date('Y-m-d\TH:i', strtotime($edit_coupon['expires_at'])) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($edit_coupon) || $edit_coupon['is_active']) ? 'checked' : '' ?> style="width:18px; height:18px;">
                    Coupon is active and usable at checkout
                </label>
            </div>

            <button type="submit" class="btn-primary" style="padding:14px 28px;">
                Save Coupon <i class="fa-solid fa-floppy-disk"></i>
            </button>
        </form>
    </div>

<?php else: ?>

    <!-- Coupons List Table -->
    <div class="admin-card" style="overflow-x:auto;">
        <table class="cart-table responsive-stack">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Discount</th>
                    <th>Usage</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $cp): ?>
                    <tr>
                        <td data-label="Code"><code style="font-weight:700; font-size:0.95rem;"><?= htmlspecialchars($cp['code']) ?></code></td>
                        <td data-label="Description" style="color:var(--text-muted);"><?= htmlspecialchars($cp['description'] ?: '—') ?></td>
                        <td data-label="Discount" style="font-weight:700; color:var(--primary-color);">
                            <?= $cp['discount_type'] === 'percent' ? number_format($cp['discount_value'], 0) . '% OFF' : 'Rs. ' . number_format($cp['discount_value'], 0) . ' OFF' ?>
                        </td>
                        <td data-label="Usage"><?= $cp['used_count'] ?><?= $cp['max_uses'] ? ' / ' . $cp['max_uses'] : '' ?></td>
                        <td data-label="Expires"><?= $cp['expires_at'] ? date('M j, Y H:i', strtotime($cp['expires_at'])) : 'No expiry' ?></td>
                        <td data-label="Status">
                            <?php if ($cp['is_active']): ?>
                                <span style="color:#166534; font-weight:600;">● Active</span>
                            <?php else: ?>
                                <span style="color:#991b1b; font-weight:600;">● Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="coupons?action=edit&id=<?= $cp['id'] ?>" class="btn-outline" style="padding:6px 12px; font-size:0.85rem;">Edit</a>
                                <a href="coupons?action=delete&id=<?= $cp['id'] ?>" onclick="return confirm('Are you sure you want to delete this coupon?');" class="btn-outline" style="padding:6px 12px; font-size:0.85rem; color:#ef4444; border-color:#fee2e2;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($coupons)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No coupon codes created yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
