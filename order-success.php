<?php
// order-success.php - Order Confirmation Display

$current_page_key = 'checkout';
require_once __DIR__ . '/includes/db.php';

$order_code = isset($_GET['code']) ? trim($_GET['code']) : '';
$tracking_code = isset($_GET['trk']) ? trim($_GET['trk']) : '';

$db = get_db();
$stmt = $db->prepare("SELECT * FROM orders WHERE order_code = ?");
$stmt->execute([$order_code]);
$order = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:60px; padding-bottom:100px;">
    <div style="max-width:680px; margin:0 auto; text-align:center;" class="surface-box">
        <div style="width:80px; height:80px; background:#dcfce7; color:#166534; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2.5rem; margin:0 auto 20px auto;">
            <i class="fa-solid fa-check"></i>
        </div>
        <h1 style="font-size:2.2rem; font-weight:800; color:var(--secondary-color);">Thank You For Your Order!</h1>
        <p style="color:var(--text-muted); font-size:1.1rem; margin-top:8px;">Your order has been placed successfully and is currently being processed.</p>

        <div style="background:var(--bg-muted); border-radius:var(--radius-md); padding:24px; margin:30px 0; text-align:left; border:1px solid var(--border-color);">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <span style="color:var(--text-muted);">Order Reference Code:</span>
                <strong style="font-size:1.1rem; color:var(--secondary-color);"><?= htmlspecialchars($order_code) ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <span style="color:var(--text-muted);">Tracking Code:</span>
                <strong style="font-size:1.1rem; color:var(--primary-color);"><?= htmlspecialchars($tracking_code) ?></strong>
            </div>
            <?php if ($order): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                    <span style="color:var(--text-muted);">Customer Name:</span>
                    <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">Total Amount Paid:</span>
                    <strong style="font-size:1.2rem; color:var(--secondary-color);">Rs. <?= number_format($order['total_amount'], 0) ?> (<?= htmlspecialchars($order['payment_method']) ?>)</strong>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
            <a href="track-order?query=<?= urlencode($order_code) ?>" class="btn-primary" style="padding:14px 28px;">
                Track Live Order Status &rarr;
            </a>
            <a href="index" class="btn-outline" style="padding:14px 24px;">Return To Home</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('buy_now') === '1') {
        localStorage.removeItem('buy_now_item');
    } else {
        // Clear shopping cart on successful checkout
        CartEngine.clearCart();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
