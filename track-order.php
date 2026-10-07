<?php
// track-order.php - Live Order Tracking Portal

$current_page_key = 'track';
require_once __DIR__ . '/includes/db.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$order = null;
$searched = false;

if (!empty($query)) {
    $searched = true;
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_code = ? OR tracking_code = ?");
    $stmt->execute([$query, $query]);
    $order = $stmt->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:50px; padding-bottom:100px;">
    <div style="text-align:center; max-width:600px; margin:0 auto 40px auto;">
        <h1 style="font-size:2.4rem; font-weight:800;">Track Your Order</h1>
        <p style="color:var(--text-muted); margin-top:8px;">Enter your Order Reference Code (e.g. <code>VC-89421</code>) or Tracking Code (e.g. <code>TRK-XXXXX</code>) to check live status.</p>
    </div>

    <!-- Search Form -->
    <div style="max-width:600px; margin:0 auto 50px auto; padding:20px;" class="surface-box">
        <form method="GET" action="track-order" style="display:flex; gap:12px; flex-wrap:wrap;">
            <input type="text" name="query" value="<?= htmlspecialchars($query) ?>" placeholder="Enter Order Code or Tracking Code..." class="form-control" required style="font-size:1.05rem; flex:1; min-width:200px;">
            <button type="submit" class="btn-primary" style="padding:14px 28px; font-weight:700;">Track Order</button>
        </form>
    </div>

    <!-- Tracking Results -->
    <?php if ($searched): ?>
        <?php if (!$order): ?>
            <div style="max-width:600px; margin:0 auto; background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:30px; border-radius:var(--radius-md); text-align:center;">
                <h3 style="font-size:1.3rem; font-weight:700;">Order Not Found</h3>
                <p style="margin-top:8px;">We couldn't find an order matching "<strong><?= htmlspecialchars($query) ?></strong>". Please double check your code or contact customer support.</p>
            </div>
        <?php else: ?>
            <?php
            $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered'];
            $current_status = $order['status'];
            $status_index = array_search($current_status, $statuses);
            if ($status_index === false) {
                $status_index = -1;
            }
            $items = json_decode($order['items_json'], true) ?: [];
            ?>

            <div class="tracking-card">
                <div class="tracking-header">
                    <div>
                        <h2 style="font-size:1.5rem; font-weight:800;">Order <?= htmlspecialchars($order['order_code']) ?></h2>
                        <span style="font-size:0.9rem; color:var(--text-muted);">Placed on <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div>
                        <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                </div>

                <!-- Timeline -->
                <?php if ($order['status'] === 'Cancelled'): ?>
                    <div style="background:#fee2e2; color:#991b1b; padding:20px; border-radius:var(--radius-md); text-align:center; font-weight:700; margin-bottom:30px;">
                        <i class="fa-solid fa-circle-xmark"></i> Order Has Been Cancelled
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <div class="timeline-step <?= $status_index >= 0 ? ($status_index > 0 ? 'completed' : 'active') : '' ?>">
                            <div class="timeline-icon">1</div>
                            <div class="timeline-label">Order Placed</div>
                        </div>
                        <div class="timeline-step <?= $status_index >= 1 ? ($status_index > 1 ? 'completed' : 'active') : '' ?>">
                            <div class="timeline-icon">2</div>
                            <div class="timeline-label">Processing</div>
                        </div>
                        <div class="timeline-step <?= $status_index >= 2 ? ($status_index > 2 ? 'completed' : 'active') : '' ?>">
                            <div class="timeline-icon">3</div>
                            <div class="timeline-label">Shipped</div>
                        </div>
                        <div class="timeline-step <?= $status_index >= 3 ? 'completed active' : '' ?>">
                            <div class="timeline-icon">4</div>
                            <div class="timeline-label">Delivered</div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tracking Details Box -->
                <div style="background:var(--bg-muted); border-radius:var(--radius-md); padding:20px; margin-bottom:30px; border:1px solid var(--border-color);">
                    <div class="grid-2">
                        <div>
                            <span style="display:block; font-size:0.85rem; color:var(--text-muted); font-weight:600;">TRACKING NUMBER</span>
                            <strong style="font-size:1.1rem; color:var(--primary-color);"><?= htmlspecialchars($order['tracking_code']) ?></strong>
                        </div>
                        <div>
                            <span style="display:block; font-size:0.85rem; color:var(--text-muted); font-weight:600;">SHIPPING CARRIER</span>
                            <strong style="font-size:1.1rem; color:var(--secondary-color);"><?= htmlspecialchars($order['carrier_name'] ?: 'Express Logistics') ?></strong>
                        </div>
                    </div>
                    <?php if (!empty($order['status_notes'])): ?>
                        <div style="margin-top:16px; border-top:1px solid var(--border-color); padding-top:14px;">
                            <span style="display:block; font-size:0.85rem; color:var(--text-muted); font-weight:600;">STATUS UPDATE NOTE</span>
                            <p style="font-size:0.95rem; color:var(--text-main); margin-top:4px;"><?= nl2br(htmlspecialchars($order['status_notes'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Shipping Address & Items -->
                <div class="grid-2" style="gap:30px;">
                    <div>
                        <h4 style="font-size:1.1rem; font-weight:700; margin-bottom:12px;">Customer Details</h4>
                        <p><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
                        <p style="color:var(--text-muted); font-size:0.9rem;"><?= htmlspecialchars($order['customer_email']) ?></p>
                        <p style="color:var(--text-muted); font-size:0.9rem;"><?= htmlspecialchars($order['customer_phone']) ?></p>
                        <p style="margin-top:10px; font-size:0.9rem; background:#fff; padding:12px; border-radius:8px; border:1px solid var(--border-color);">
                            <i class="fa-solid fa-location-dot"></i> <?= nl2br(htmlspecialchars($order['customer_address'])) ?>
                        </p>
                    </div>

                    <div>
                        <h4 style="font-size:1.1rem; font-weight:700; margin-bottom:12px;">Items Ordered</h4>
                        <div style="background:#fff; border-radius:8px; border:1px solid var(--border-color); padding:12px;">
                            <?php foreach ($items as $item): ?>
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
                                    <span><?= htmlspecialchars($item['name']) ?> × <?= $item['qty'] ?></span>
                                    <strong>Rs. <?= number_format($item['price'] * $item['qty'], 0) ?></strong>
                                </div>
                            <?php endforeach; ?>
                            <div style="border-top:1px solid var(--border-color); padding-top:10px; margin-top:10px; display:flex; justify-content:space-between; font-weight:800;">
                                <span>Total Paid:</span>
                                <span style="color:var(--primary-color);">Rs. <?= number_format($order['total_amount'], 0) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
