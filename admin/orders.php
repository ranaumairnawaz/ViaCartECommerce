<?php
// admin/orders.php - Order Fulfillment & Tracking Management CMS

$admin_page = 'orders';
require_once __DIR__ . '/admin_header.php';

$db = get_db();
$success_msg = '';

$view_id = intval($_GET['view'] ?? 0);
$status_filter = $_GET['status'] ?? '';
$search_query = trim($_GET['search'] ?? '');

// Update Order Status & Tracking Info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'Pending');
    $carrier_name = trim($_POST['carrier_name'] ?? '');
    $status_notes = trim($_POST['status_notes'] ?? '');

    // Fetch existing order to check phone & details
    $stmt_old = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt_old->execute([$order_id]);
    $old_order = $stmt_old->fetch();

    $stmt = $db->prepare("UPDATE orders SET status = ?, carrier_name = ?, status_notes = ? WHERE id = ?");
    $stmt->execute([$status, $carrier_name, $status_notes, $order_id]);
    $success_msg = 'Order status and tracking info updated successfully!';

    // Send WhatsApp Status Notification to Customer
    if ($old_order && !empty($old_order['customer_phone'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $domain = $_SERVER['HTTP_HOST'];
        $tracking_url = "$protocol://$domain/ViaCart/track-order?query=" . urlencode($old_order['order_code']);

        $wa_msg = "Hi " . $old_order['customer_name'] . "! Update on your ViaCart Order *" . $old_order['order_code'] . "* 📦\n\n" .
                  "*New Status:* " . strtoupper($status) . "\n" .
                  ($carrier_name ? "*Carrier:* $carrier_name\n" : "") .
                  ($status_notes ? "*Update Note:* $status_notes\n" : "") . "\n" .
                  "Track live order progress:\n$tracking_url";
        
        send_whatsapp_message($old_order['customer_phone'], $wa_msg);
    }
}

// Fetch single order if view requested
$single_order = null;
if ($view_id > 0) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$view_id]);
    $single_order = $stmt->fetch();
}

// Build Filter SQL Query
$where_clauses = ["1=1"];
$params = [];

if (!empty($status_filter)) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($search_query)) {
    $where_clauses[] = "(order_code LIKE ? OR tracking_code LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_sql = implode(" AND ", $where_clauses);
$stmt_orders = $db->prepare("SELECT * FROM orders WHERE $where_sql ORDER BY id DESC");
$stmt_orders->execute($params);
$orders = $stmt_orders->fetchAll();
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">Orders & Live Tracking CMS</h1>
        <p style="color:var(--text-muted);">Manage customer orders, update tracking statuses, assign shipping carriers, and post status notes.</p>
    </div>
</div>

<?php if (!empty($success_msg)): ?>
    <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<!-- Single Order Detailed Fulfillment View -->
<?php if ($single_order): ?>
    <?php
    $items = json_decode($single_order['items_json'], true) ?: [];
    $cust_phone_clean = preg_replace('/[^0-9]/', '', $single_order['customer_phone']);
    ?>
    <div class="admin-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="font-size:1.5rem; font-weight:800;">Order Details: <?= htmlspecialchars($single_order['order_code']) ?></h3>
                <span style="font-size:0.9rem; color:var(--text-muted);">Placed on <?= date('F j, Y, g:i a', strtotime($single_order['created_at'])) ?></span>
            </div>
            <div style="display:flex; gap:10px;">
                <button onclick="window.print();" class="btn-outline" style="font-size:0.88rem;"><i class="fa-solid fa-print"></i> Print Packing Slip</button>
                <a href="orders" class="btn-outline" style="font-size:0.88rem;">&larr; Back to All Orders</a>
            </div>
        </div>

        <div class="grid-2" style="gap:30px; margin-bottom:30px;">
            <!-- Customer Details -->
            <div style="background:var(--bg-muted); padding:20px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <h4 style="font-size:1.1rem; font-weight:700; margin-bottom:12px;">Customer Shipping Info</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($single_order['customer_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($single_order['customer_email']) ?></p>
                <p style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                    <strong>Phone / WhatsApp:</strong> <?= htmlspecialchars($single_order['customer_phone']) ?>
                    <?php if (!empty($cust_phone_clean)): ?>
                        <a href="https://wa.me/<?= $cust_phone_clean ?>?text=<?= urlencode('Hello ' . $single_order['customer_name'] . ', regarding your ViaCart Order ' . $single_order['order_code']) ?>" target="_blank" style="background:#25D366; color:#fff; padding:3px 10px; border-radius:6px; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                            <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    <?php endif; ?>
                </p>
                <p><strong>Payment Method:</strong> <?= htmlspecialchars($single_order['payment_method']) ?></p>
                <?php if (!empty($single_order['coupon_code'])): ?>
                    <p><strong>Coupon Used:</strong> <code><?= htmlspecialchars($single_order['coupon_code']) ?></code> (-Rs. <?= number_format($single_order['discount_amount'], 0) ?>)</p>
                <?php endif; ?>
                <p style="margin-top:10px;"><strong>Address:</strong><br><?= nl2br(htmlspecialchars($single_order['customer_address'])) ?></p>
            </div>

            <!-- Update Status & Tracking Form -->
            <form method="POST" action="orders?view=<?= $single_order['id'] ?>" style="background:#fff; padding:20px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <input type="hidden" name="order_id" value="<?= $single_order['id'] ?>">
                <h4 style="font-size:1.1rem; font-weight:700; margin-bottom:16px; color:var(--primary-color);">Update Fulfillment Status & Notify Customer</h4>

                <div class="form-group">
                    <label>Fulfillment Status</label>
                    <select name="status" class="form-control" style="font-weight:700;">
                        <?php
                        $status_options = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                        foreach ($status_options as $opt):
                        ?>
                            <option value="<?= $opt ?>" <?= $single_order['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Shipping Carrier Name</label>
                    <input type="text" name="carrier_name" class="form-control" value="<?= htmlspecialchars($single_order['carrier_name'] ?? 'Express Logistics') ?>" placeholder="DHL, FedEx, UPS">
                </div>

                <div class="form-group">
                    <label>Status Update Note (Sent to Customer via WhatsApp)</label>
                    <textarea name="status_notes" class="form-control" style="min-height:70px;" placeholder="e.g. Package dispatched from distribution hub via express shipment."><?= htmlspecialchars($single_order['status_notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Save Status & Send WhatsApp Update <i class="fa-brands fa-whatsapp"></i></button>
            </form>
        </div>

        <!-- Itemized Order Table -->
        <h4 style="font-size:1.1rem; font-weight:700; margin-bottom:14px;">Items Included</h4>
        <table class="cart-table responsive-stack" style="margin-bottom:20px;">
            <thead>
                <tr>
                    <th>Product Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td data-label="Item">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="<?= htmlspecialchars(get_image_url($item['image'], true)) ?>" style="width:44px; height:44px; object-fit:cover; border-radius:6px;">
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                            </div>
                        </td>
                        <td data-label="Price">Rs. <?= number_format($item['price'], 0) ?></td>
                        <td data-label="Quantity"><?= $item['qty'] ?></td>
                        <td data-label="Subtotal" style="font-weight:700;">Rs. <?= number_format($item['price'] * $item['qty'], 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="text-align:right; font-size:1.3rem; font-weight:800; color:var(--secondary-color);">
            Total Order Amount: <span style="color:var(--primary-color);">Rs. <?= number_format($single_order['total_amount'], 0) ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Orders List Table -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px;">
        <form method="GET" action="orders" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; width:100%;">
            <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search Order Code, Customer, Tracking, or Phone..." class="form-control" style="flex:1; min-width:220px; padding:8px 14px;">

            <select name="status" class="form-control" style="min-width:160px; padding:8px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $status_filter === 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $status_filter === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <button type="submit" class="btn-primary" style="padding:8px 16px;">Search Orders</button>
            <?php if (!empty($status_filter) || !empty($search_query)): ?>
                <a href="orders" class="btn-outline" style="padding:8px 14px;">Reset Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <table class="cart-table responsive-stack">
        <thead>
            <tr>
                <th>Order Code</th>
                <th>Tracking Number</th>
                <th>Customer Name</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $ord): ?>
                <tr>
                    <td data-label="Order Code"><strong><?= htmlspecialchars($ord['order_code']) ?></strong></td>
                    <td data-label="Tracking"><code><?= htmlspecialchars($ord['tracking_code']) ?></code></td>
                    <td data-label="Customer">
                        <strong><?= htmlspecialchars($ord['customer_name']) ?></strong>
                        <span style="display:block; font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($ord['customer_phone']) ?></span>
                    </td>
                    <td data-label="Date"><?= date('M j, Y H:i', strtotime($ord['created_at'])) ?></td>
                    <td data-label="Total" style="font-weight:700;">Rs. <?= number_format($ord['total_amount'], 0) ?></td>
                    <td data-label="Status">
                        <span class="status-badge status-<?= htmlspecialchars($ord['status']) ?>">
                            <?= htmlspecialchars($ord['status']) ?>
                        </span>
                    </td>
                    <td data-label="Action">
                        <a href="orders?view=<?= $ord['id'] ?>" class="btn-primary" style="padding:6px 14px; font-size:0.85rem;">
                            View & Update
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
