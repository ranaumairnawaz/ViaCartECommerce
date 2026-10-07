<?php
// deals.php - Public Deals Page (Discounted Products, Combo Bundles & Coupon Codes)

$current_page_key = 'deals';
$page_seo_title = 'Deals & Offers | ViaCart';
$page_seo_desc = 'Shop the best discounts, combo bundles, and coupon codes on luxury bags at ViaCart.';
require_once __DIR__ . '/includes/header.php';

$deal_products = get_deal_products();
$active_combos = get_active_combos();
$active_coupons = get_active_coupons();
?>

<div class="container" style="padding-top:40px; padding-bottom:80px;">
    <div style="text-align:center; margin-bottom:40px;">
        <h1 style="font-size:clamp(1.9rem, 5vw, 2.6rem); font-weight:800; margin-bottom:10px;"><i class="fa-solid fa-fire"></i> Deals & Offers</h1>
        <p style="color:var(--text-muted); font-size:1.05rem;">Grab discounted bags, bundle combos, and exclusive coupon codes before they're gone.</p>
    </div>

    <?php if (empty($deal_products) && empty($active_combos) && empty($active_coupons)): ?>
        <div style="background:#fff; border:1px dashed var(--border-color); border-radius:var(--radius-md); padding:80px 20px; text-align:center;">
            <div style="font-size:4rem; margin-bottom:16px;"><i class="fa-solid fa-tag"></i></div>
            <h2 style="font-size:1.8rem; font-weight:800;">No Active Deals Right Now</h2>
            <p style="color:var(--text-muted); margin-top:8px; margin-bottom:24px;">Check back soon, or browse our full collection.</p>
            <a href="shop" class="btn-primary">Browse Shop Collection &rarr;</a>
        </div>
    <?php endif; ?>

    <!-- Combo Deals -->
    <?php if (!empty($active_combos)): ?>
        <section style="margin-bottom:56px;">
            <h2 class="deal-section-title"><i class="fa-solid fa-gift"></i> Combo Bundles</h2>
            <p style="color:var(--text-muted); margin-bottom:22px;">Buy these products together and save more.</p>

            <div class="grid-3">
                <?php foreach ($active_combos as $combo): ?>
                    <?php
                        $savings = $combo['original_total'] - $combo['combo_price'];
                        $combo_items_js = [];
                        foreach ($combo['items'] as $it) {
                            $combo_items_js[] = ['name' => $it['name'], 'qty' => (int)$it['qty']];
                        }
                    ?>
                    <div class="combo-card">
                        <div class="combo-img-wrap">
                            <img src="<?= htmlspecialchars(get_image_url($combo['image'] ?: 'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=600&auto=format&fit=crop&q=80')) ?>" alt="<?= htmlspecialchars($combo['name']) ?>">
                            <?php if ($savings > 0): ?>
                                <span class="discount-badge">SAVE Rs. <?= number_format($savings, 0) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="combo-body">
                            <h3 style="font-size:1.15rem; font-weight:800;"><?= htmlspecialchars($combo['name']) ?></h3>

                            <ul class="combo-items-list" style="padding-left:18px; margin:0;">
                                <?php foreach ($combo['items'] as $it): ?>
                                    <li><?= htmlspecialchars($it['name']) ?> <?= $it['qty'] > 1 ? '× ' . $it['qty'] : '' ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="product-price-wrap">
                                <span class="product-price">Rs. <?= number_format($combo['combo_price'], 0) ?></span>
                                <?php if ($combo['original_total'] > $combo['combo_price']): ?>
                                    <span class="product-old-price">Rs. <?= number_format($combo['original_total'], 0) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($combo['deal_ends_at'])): ?>
                                <span class="deal-countdown" data-countdown="<?= date('c', strtotime($combo['deal_ends_at'])) ?>">Loading...</span>
                            <?php endif; ?>

                            <button onclick='CartEngine.addComboItem({id: <?= $combo['id'] ?>, name: <?= json_encode($combo['name']) ?>, price: <?= $combo['combo_price'] ?>, image: <?= json_encode(get_image_url($combo['image'])) ?>, items: <?= json_encode($combo_items_js) ?>})' class="btn-primary" style="width:100%; text-align:center; justify-content:center; margin-top:auto;">
                                <i class="fa-solid fa-cart-shopping"></i> Add Combo to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Discounted Products -->
    <?php if (!empty($deal_products)): ?>
        <section style="margin-bottom:56px;">
            <h2 class="deal-section-title"><i class="fa-solid fa-bag-shopping"></i> Discounted Products</h2>
            <p style="color:var(--text-muted); margin-bottom:22px;">Automatically listed whenever a product has a special price.</p>

            <div class="product-grid">
                <?php foreach ($deal_products as $prod): ?>
                    <div class="product-card" style="position:relative;">
                        <span class="discount-badge"><?= $prod['discount_percent'] ?>% OFF</span>

                        <div class="product-img-wrap">
                            <img src="<?= htmlspecialchars(get_image_url($prod['image'] ?: 'https://images.unsplash.com/photo-1566150905458-1bf1fc15a6a0?w=600&auto=format&fit=crop&q=80')) ?>" alt="<?= htmlspecialchars($prod['name']) ?>">
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars($prod['category_name']) ?></span>
                            <h3 class="product-title"><?= htmlspecialchars($prod['name']) ?></h3>
                            <div class="product-price-wrap">
                                <span class="product-price">Rs. <?= number_format($prod['price'], 0) ?></span>
                                <span class="product-old-price">Rs. <?= number_format($prod['old_price'], 0) ?></span>
                            </div>

                            <?php if (!empty($prod['deal_ends_at'])): ?>
                                <span class="deal-countdown" data-countdown="<?= date('c', strtotime($prod['deal_ends_at'])) ?>" style="margin-bottom:10px;">Loading...</span>
                            <?php endif; ?>

                            <div style="display:flex; gap:8px;">
                                <a href="product?id=<?= $prod['id'] ?>" class="btn-outline" style="flex:1; text-align:center; padding:10px 0;">Details</a>
                                <button onclick="CartEngine.addItem({id: <?= $prod['id'] ?>, name: '<?= addslashes($prod['name']) ?>', price: <?= $prod['price'] ?>, image: '<?= addslashes(get_image_url($prod['image'])) ?>'})" class="btn-primary" style="padding:10px 14px;" title="Add to Cart">
                                    <i class="fa-solid fa-cart-shopping"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Coupon Codes -->
    <?php if (!empty($active_coupons)): ?>
        <section>
            <h2 class="deal-section-title"><i class="fa-solid fa-tag"></i> Coupon Codes</h2>
            <p style="color:var(--text-muted); margin-bottom:22px;">Copy a code below and apply it at checkout.</p>

            <div class="grid-3">
                <?php foreach ($active_coupons as $coupon): ?>
                    <div class="coupon-card">
                        <span class="coupon-code" id="coupon-<?= $coupon['id'] ?>"><?= htmlspecialchars($coupon['code']) ?></span>
                        <span>
                            <?= $coupon['discount_type'] === 'percent' ? number_format($coupon['discount_value'], 0) . '% OFF' : 'Rs. ' . number_format($coupon['discount_value'], 0) . ' OFF' ?>
                            <?php if (!empty($coupon['description'])): ?>
                                — <?= htmlspecialchars($coupon['description']) ?>
                            <?php endif; ?>
                        </span>
                        <?php if ($coupon['min_order_amount']): ?>
                            <span style="font-size:0.82rem; opacity:0.75;">Minimum order Rs. <?= number_format($coupon['min_order_amount'], 0) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($coupon['expires_at'])): ?>
                            <span class="deal-countdown" data-countdown="<?= date('c', strtotime($coupon['expires_at'])) ?>">Loading...</span>
                        <?php endif; ?>
                        <button class="coupon-copy-btn" onclick="copyCouponCode('<?= htmlspecialchars($coupon['code']) ?>', this)"><i class="fa-solid fa-clipboard"></i> Copy Code</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
function copyCouponCode(code, btn) {
    navigator.clipboard.writeText(code).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
        setTimeout(() => { btn.innerHTML = original; }, 2000);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
