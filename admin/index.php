<?php
// admin/index.php - Admin Dashboard Overview

$admin_page = 'dashboard';
require_once __DIR__ . '/admin_header.php';

$db = get_db();

// Metrics
$total_sales = $db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn() ?: 0;
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn() ?: 0;
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;

// Low Stock Products Alert (Stock <= 3)
$low_stock_products = $db->query("SELECT * FROM products WHERE stock <= 3 ORDER BY stock ASC")->fetchAll();

// Recent Orders
$recent_orders = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">CMS Dashboard</h1>
        <p style="color:var(--text-muted);">Welcome back! Here is a summary of your e-commerce store performance.</p>
    </div>
    <div>
        <a href="products?action=add" class="btn-primary">+ Add New Product</a>
    </div>
</div>

<!-- Stat Cards Grid -->
<div class="stat-grid">
    <div class="stat-card">
        <span style="font-size:0.85rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">TOTAL REVENUE</span>
        <div class="stat-val" style="color:var(--primary-color);">Rs. <?= number_format($total_sales, 0) ?></div>
    </div>
    <div class="stat-card">
        <span style="font-size:0.85rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">TOTAL ORDERS</span>
        <div class="stat-val"><?= $total_orders ?></div>
    </div>
    <div class="stat-card">
        <span style="font-size:0.85rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">PENDING ORDERS</span>
        <div class="stat-val" style="color:#d97706;"><?= $pending_orders ?></div>
    </div>
    <div class="stat-card">
        <span style="font-size:0.85rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">CATALOG PRODUCTS</span>
        <div class="stat-val"><?= $total_products ?></div>
    </div>
</div>

<!-- Low Stock Warning Banner if any -->
<?php if (!empty($low_stock_products)): ?>
    <div class="admin-card" style="border-left:4px solid #ef4444; background:#fef2f2;">
        <h3 style="font-size:1.1rem; font-weight:800; color:#991b1b; margin-bottom:12px;"><i class="fa-solid fa-triangle-exclamation"></i> Low Stock Inventory Alert</h3>
        <div style="display:flex; gap:16px; flex-wrap:wrap;">
            <?php foreach ($low_stock_products as $lp): ?>
                <div style="background:#fff; border:1px solid #fca5a5; padding:10px 16px; border-radius:8px; display:flex; align-items:center; gap:10px;">
                    <img src="<?= htmlspecialchars(get_image_url($lp['image'], true)) ?>" style="width:36px; height:36px; object-fit:cover; border-radius:6px;">
                    <div>
                        <strong style="display:block; font-size:0.9rem; color:#991b1b;"><?= htmlspecialchars($lp['name']) ?></strong>
                        <span style="font-size:0.8rem; font-weight:700; color:#ef4444;"><?= $lp['stock'] ?> left in stock</span>
                    </div>
                    <a href="products?action=edit&id=<?= $lp['id'] ?>" class="btn-outline" style="padding:4px 8px; font-size:0.75rem; margin-left:10px;">Restock</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Orders Table -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="font-size:1.2rem; font-weight:800;">Recent Customer Orders</h3>
        <a href="orders" class="btn-outline" style="font-size:0.85rem;">View All Orders &rarr;</a>
    </div>

    <?php if (empty($recent_orders)): ?>
        <p style="color:var(--text-muted); text-align:center; padding:30px 0;">No orders received yet.</p>
    <?php else: ?>
        <table class="cart-table responsive-stack">
            <thead>
                <tr>
                    <th>Order Code</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $ord): ?>
                    <tr>
                        <td data-label="Order Code"><strong><?= htmlspecialchars($ord['order_code']) ?></strong></td>
                        <td data-label="Customer"><?= htmlspecialchars($ord['customer_name']) ?></td>
                        <td data-label="Date"><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
                        <td data-label="Amount" style="font-weight:700;">Rs. <?= number_format($ord['total_amount'], 0) ?></td>
                        <td data-label="Status">
                            <span class="status-badge status-<?= htmlspecialchars($ord['status']) ?>">
                                <?= htmlspecialchars($ord['status']) ?>
                            </span>
                        </td>
                        <td data-label="Action">
                            <a href="orders?view=<?= $ord['id'] ?>" class="btn-outline" style="padding:6px 12px; font-size:0.8rem;">Manage Status</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
