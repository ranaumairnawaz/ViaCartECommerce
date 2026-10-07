<?php
// checkout.php - Checkout Form & Order Processing

$current_page_key = 'checkout';
require_once __DIR__ . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'COD');
    $coupon_code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    $items_json = trim($_POST['items_json'] ?? '[]');

    $items = json_decode($items_json, true);

    if (empty($customer_name) || empty($customer_email) || empty($customer_address) || empty($items)) {
        $error = 'Please fill in all required customer details and make sure your cart is not empty.';
    } else {
        $db = get_db();

        // Calculate Subtotal & Total Amount
        $subtotal = 0;
        $item_names_arr = [];
        foreach ($items as $item) {
            $subtotal += ($item['price'] * $item['qty']);
            $item_names_arr[] = $item['name'] . ' (x' . $item['qty'] . ')';
        }
        $shipping = $subtotal > 5000 ? 0 : 250;

        // Re-validate coupon server-side (never trust client-calculated discount)
        $discount_amount = 0;
        if (!empty($coupon_code)) {
            $coupon_result = validate_coupon($coupon_code, $subtotal);
            if ($coupon_result['valid']) {
                $discount_amount = $coupon_result['discount_amount'];
            } else {
                $coupon_code = null;
            }
        } else {
            $coupon_code = null;
        }

        $total_amount = max(0, $subtotal + $shipping - $discount_amount);

        // Generate Codes
        $random_num = rand(10000, 99999);
        $order_code = 'VC-' . $random_num;
        $tracking_code = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Save Order to MySQL
        $stmt = $db->prepare("
            INSERT INTO orders (order_code, tracking_code, customer_name, customer_email, customer_phone, customer_address, payment_method, coupon_code, discount_amount, status, total_amount, items_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)
        ");

        if ($stmt->execute([$order_code, $tracking_code, $customer_name, $customer_email, $customer_phone, $customer_address, $payment_method, $coupon_code, $discount_amount, $total_amount, $items_json])) {

            if (!empty($coupon_code)) {
                increment_coupon_usage($coupon_code);
            }

            // Build Tracking Link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST'];
            $tracking_url = "$protocol://$domain/ViaCart/track-order?query=" . urlencode($order_code);

            // 1. Send WhatsApp Notification to Admin
            $admin_number = get_setting('whatsapp_number', '12125550199');
            if (!empty($admin_number)) {
                $admin_msg = "🛍️ *New Order Placed on ViaCart!*\n\n" .
                             "*Order Code:* $order_code\n" .
                             "*Customer:* $customer_name\n" .
                             "*Phone:* $customer_phone\n" .
                             ($discount_amount > 0 ? "*Coupon Used:* $coupon_code (-Rs. " . number_format($discount_amount, 0) . ")\n" : "") .
                             "*Total Amount:* Rs. " . number_format($total_amount, 0) . " ($payment_method)\n" .
                             "*Items:* " . implode(", ", $item_names_arr) . "\n\n" .
                             "*Live Tracking:* $tracking_url";
                send_whatsapp_message($admin_number, $admin_msg);
            }

            // 2. Send WhatsApp Order Confirmation to Customer
            if (!empty($customer_phone)) {
                $cust_msg = "Hi $customer_name! Thank you for shopping with ViaCart. 🛍️\n\n" .
                            "*Order Reference:* $order_code\n" .
                            "*Tracking Code:* $tracking_code\n" .
                            "*Total Amount:* Rs. " . number_format($total_amount, 0) . "\n\n" .
                            "You can track your live shipment status anytime here:\n$tracking_url";
                send_whatsapp_message($customer_phone, $cust_msg);
            }

            $is_buy_now = isset($_POST['is_buy_now']) && $_POST['is_buy_now'] === '1';
            $success_url = "order-success?code=" . urlencode($order_code) . "&trk=" . urlencode($tracking_code);
            if ($is_buy_now) {
                $success_url .= "&buy_now=1";
            }
            header("Location: " . $success_url);
            exit;
        } else {
            $error = 'Failed to save order. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:40px; padding-bottom:80px;">
    <h1 style="font-size:2.2rem; font-weight:800; margin-bottom:24px;">Checkout</h1>

    <?php if (!empty($error)): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm" action="checkout">
        <input type="hidden" name="items_json" id="itemsJsonInput">
        <input type="hidden" name="is_buy_now" id="isBuyNowInput" value="0">

        <div class="layout-checkout">
            <!-- Customer Shipping Info -->
            <div style="background:#fff; border-radius:var(--radius-md); padding:30px; border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
                <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                    1. Shipping Information
                </h3>

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="John Doe">
                </div>

                <div class="grid-2" style="gap:16px;">
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="customer_email" class="form-control" required placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp / Phone Number *</label>
                        <input type="tel" name="customer_phone" class="form-control" required placeholder="12125550199">
                        <small style="color:var(--text-muted);">Include country code for instant WhatsApp tracking updates</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Full Delivery Address *</label>
                    <textarea name="customer_address" class="form-control" required placeholder="Street address, Apartment, City, Postal Code, Country"></textarea>
                </div>

                <h3 style="font-size:1.3rem; font-weight:800; margin-top:30px; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                    2. Payment Method
                </h3>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <label style="display:flex; align-items:center; gap:12px; border:1px solid var(--border-color); padding:16px; border-radius:12px; cursor:pointer;">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <div>
                            <strong style="display:block;">Cash on Delivery (COD)</strong>
                            <span style="font-size:0.85rem; color:var(--text-muted);">Pay with cash upon order delivery</span>
                        </div>
                    </label>
                    <label style="display:flex; align-items:center; gap:12px; border:1px solid var(--border-color); padding:16px; border-radius:12px; cursor:pointer;">
                        <input type="radio" name="payment_method" value="Bank Transfer">
                        <div>
                            <strong style="display:block;">Direct Bank Transfer / Card</strong>
                            <span style="font-size:0.85rem; color:var(--text-muted);">Receive bank account details on confirmation</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary-card">
                <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
                    Order Details
                </h3>

                <div id="checkoutItemsList" style="margin-bottom:20px; max-height:240px; overflow-y:auto;">
                    <!-- JS populated -->
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label>Have a Coupon Code?</label>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="couponInput" class="form-control" placeholder="Enter code" style="text-transform:uppercase;">
                        <button type="button" onclick="applyCoupon()" class="btn-outline" style="white-space:nowrap; padding:0 18px;">Apply</button>
                    </div>
                    <small id="couponMsg" style="display:block; margin-top:6px; font-weight:600;"></small>
                </div>

                <input type="hidden" name="coupon_code" id="couponCodeInput">

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="coSubtotal">Rs. 0</span>
                </div>
                <div class="summary-row" id="coDiscountRow" style="display:none; color:#166534;">
                    <span>Coupon Discount</span>
                    <span id="coDiscount">- Rs. 0</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span id="coShipping">Rs. 0</span>
                </div>
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span id="coTotal">Rs. 0</span>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; text-align:center; justify-content:center; margin-top:24px; padding:16px; font-size:1.1rem;">
                    Place Order Now <i class="fa-solid fa-champagne-glasses"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let cartSubtotal = 0;
let appliedDiscount = 0;

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const isBuyNow = urlParams.get('buy_now') === 'true';
    let items = [];
    if (isBuyNow) {
        items = JSON.parse(localStorage.getItem('buy_now_item')) || [];
        document.getElementById('isBuyNowInput').value = '1';
    } else {
        items = CartEngine.getCart();
    }

    if (items.length === 0) {
        window.location.href = 'cart';
        return;
    }

    document.getElementById('itemsJsonInput').value = JSON.stringify(items);

    let html = '';
    let subtotal = 0;

    items.forEach(item => {
        const itemSubtotal = item.price * item.qty;
        subtotal += itemSubtotal;
        html += `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:0.9rem;">
                <div>
                    <strong>${item.type === 'combo' ? '<i class="fa-solid fa-gift"></i> ' : ''}${item.name}</strong> × ${item.qty}
                </div>
                <span>Rs. ${itemSubtotal.toLocaleString()}</span>
            </div>
        `;
    });

    document.getElementById('checkoutItemsList').innerHTML = html;
    cartSubtotal = subtotal;
    updateCheckoutTotals();
});

function updateCheckoutTotals() {
    const shipping = cartSubtotal > 5000 ? 0 : 250;
    const total = Math.max(0, cartSubtotal + shipping - appliedDiscount);

    document.getElementById('coSubtotal').textContent = 'Rs. ' + cartSubtotal.toLocaleString();
    document.getElementById('coShipping').textContent = shipping === 0 ? 'FREE' : 'Rs. ' + shipping.toLocaleString();
    document.getElementById('coTotal').textContent = 'Rs. ' + total.toLocaleString();

    const discountRow = document.getElementById('coDiscountRow');
    if (appliedDiscount > 0) {
        document.getElementById('coDiscount').textContent = '- Rs. ' + appliedDiscount.toLocaleString();
        discountRow.style.display = 'flex';
    } else {
        discountRow.style.display = 'none';
    }
}

function applyCoupon() {
    const code = document.getElementById('couponInput').value.trim();
    const msgEl = document.getElementById('couponMsg');

    if (!code) {
        msgEl.textContent = 'Please enter a coupon code.';
        msgEl.style.color = '#991b1b';
        return;
    }

    const formData = new FormData();
    formData.append('code', code);
    formData.append('subtotal', cartSubtotal);

    fetch('apply-coupon', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            msgEl.textContent = data.message;
            if (data.valid) {
                msgEl.style.color = '#166534';
                appliedDiscount = data.discount_amount;
                document.getElementById('couponCodeInput').value = code;
            } else {
                msgEl.style.color = '#991b1b';
                appliedDiscount = 0;
                document.getElementById('couponCodeInput').value = '';
            }
            updateCheckoutTotals();
        })
        .catch(() => {
            msgEl.textContent = 'Could not validate coupon. Please try again.';
            msgEl.style.color = '#991b1b';
        });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
