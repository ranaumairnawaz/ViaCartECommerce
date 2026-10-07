<?php
// product.php - Dynamic Product Detail Page

require_once __DIR__ . '/includes/db.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$db = get_db();

$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: shop');
    exit;
}

// Product Specific SEO Metadata
$page_seo_title = !empty($product['meta_title']) ? $product['meta_title'] : $product['name'] . " | ViaCart Luxury Bags";
$page_seo_desc = !empty($product['meta_description']) ? $product['meta_description'] : substr(strip_tags($product['description']), 0, 160);

// Related Products
$stmt_related = $db->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? 
    ORDER BY p.id DESC LIMIT 3
");
$stmt_related->execute([$product['category_id'], $product['id']]);
$related_products = $stmt_related->fetchAll();

$current_page_key = 'shop';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:50px; padding-bottom:80px;">
    <!-- Breadcrumb -->
    <div style="font-size:0.9rem; color:var(--text-muted); margin-bottom:30px;">
        <a href="index">Home</a> &nbsp;/&nbsp;
        <a href="shop">Shop</a> &nbsp;/&nbsp;
        <a
            href="shop?category=<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a>
        &nbsp;/&nbsp;
        <strong style="color:var(--secondary-color);"><?= htmlspecialchars($product['name']) ?></strong>
    </div>

    <!-- Main Product Section -->
    <div class="layout-product">
        <!-- Image Gallery -->
        <div>
            <!-- Main Image -->
            <div
                style="background:var(--bg-muted); border-radius:var(--radius-md); overflow:hidden; border:1px solid var(--border-color); margin-bottom:12px;">
                <img id="mainProductImg"
                    src="<?= htmlspecialchars(get_image_url($product['image'] ?: 'https://images.unsplash.com/photo-1566150905458-1bf1fc15a6a0?w=800&auto=format&fit=crop&q=80')) ?>"
                    alt="<?= htmlspecialchars($product['name']) ?>"
                    style="width:100%; height:480px; object-fit:cover; transition: opacity 0.2s ease;">
            </div>

            <?php
            $gallery = !empty($product['gallery_images']) ? explode(',', $product['gallery_images']) : [];
            // Add main image as the first thumbnail
            $all_images = array_merge([$product['image']], $gallery);
            $all_images = array_filter($all_images);
            if (count($all_images) > 1):
                ?>
                <div style="display:flex; gap:10px; overflow-x:auto; padding-bottom:8px;" class="hide-scrollbar">
                    <?php foreach ($all_images as $img): ?>
                        <button type="button"
                            onclick="document.getElementById('mainProductImg').src = '<?= htmlspecialchars(get_image_url($img, true)) ?>'"
                            style="border:2px solid transparent; background:var(--bg-muted); border-radius:8px; overflow:hidden; cursor:pointer; flex-shrink:0; padding:0; width:80px; height:80px;">
                            <img src="<?= htmlspecialchars(get_image_url($img, true)) ?>"
                                style="width:100%; height:100%; object-fit:cover;">
                        </button>
                    <?php endforeach; ?>
                </div>
                <style>
                    .hide-scrollbar::-webkit-scrollbar {
                        display: none;
                    }

                    .hide-scrollbar {
                        -ms-overflow-style: none;
                        scrollbar-width: none;
                    }

                    .hide-scrollbar button:focus,
                    .hide-scrollbar button:hover {
                        border-color: var(--primary-color) !important;
                    }
                </style>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div>
            <span
                style="font-size:0.85rem; font-weight:700; color:var(--primary-color); text-transform:uppercase; letter-spacing:1px;">
                <?= htmlspecialchars($product['category_name']) ?>
            </span>
            <h1 style="font-size:2.4rem; font-weight:800; margin-top:8px; margin-bottom:16px;">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <!-- Price & Stock -->
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
                <span style="font-size:2.2rem; font-weight:800; color:var(--primary-color);">Rs.
                    <?= number_format($product['price'], 0) ?></span>
                <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                    <span style="font-size:1.2rem; color:var(--text-light); text-decoration:line-through;">Rs.
                        <?= number_format($product['old_price'], 0) ?></span>
                    <span class="product-badge" style="position:static;">SAVE Rs.
                        <?= number_format($product['old_price'] - $product['price'], 0) ?></span>
                <?php endif; ?>
            </div>

            <div style="margin-bottom:24px;">
                <?php if ($product['stock'] > 0): ?>
                    <span
                        style="display:inline-flex; align-items:center; gap:6px; color:#166534; background:#dcfce7; padding:4px 12px; border-radius:99px; font-weight:600; font-size:0.88rem;">
                        <i class="fa-solid fa-check"></i> In Stock (<?= $product['stock'] ?> available)
                    </span>
                <?php else: ?>
                    <span
                        style="display:inline-flex; align-items:center; gap:6px; color:#991b1b; background:#fee2e2; padding:4px 12px; border-radius:99px; font-weight:600; font-size:0.88rem;">
                        <i class="fa-solid fa-xmark"></i> Out of Stock
                    </span>
                <?php endif; ?>
            </div>

            <p
                style="font-size:1.05rem; color:var(--text-muted); line-height:1.7; margin-bottom:24px; border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color); padding:20px 0;">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>

            <?php
            $prod_colors = !empty($product['colors']) ? array_map('trim', explode(',', $product['colors'])) : [];
            $prod_sizes = !empty($product['sizes']) ? array_map('trim', explode(',', $product['sizes'])) : [];
            $color_hex = [
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

            <?php if (!empty($prod_colors)): ?>
                <!-- Color Selector -->
                <div style="margin-bottom:20px;">
                    <p style="font-weight:700; font-size:0.95rem; margin-bottom:10px;">Color: <span id="selectedColorLabel"
                            style="color:var(--primary-color); font-weight:600;">—</span></p>
                    <div style="display:flex; flex-wrap:wrap; gap:10px;" id="colorSwatchWrap">
                        <?php foreach ($prod_colors as $c): ?>
                            <button type="button" class="prod-color-swatch" data-color="<?= htmlspecialchars($c) ?>"
                                title="<?= htmlspecialchars($c) ?>"
                                style="width:34px; height:34px; border-radius:50%; background:<?= htmlspecialchars($color_hex[$c] ?? '#ccc') ?>; border:3px solid var(--border-color); cursor:pointer; transition:transform 0.15s, border-color 0.15s;"
                                onclick="selectColor(this)">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($prod_sizes)): ?>
                <!-- Size Selector -->
                <div style="margin-bottom:24px;">
                    <p style="font-weight:700; font-size:0.95rem; margin-bottom:10px;">Size: <span id="selectedSizeLabel"
                            style="color:var(--primary-color); font-weight:600;">—</span></p>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;" id="sizeBtnWrap">
                        <?php
                        $size_labels = ['S' => 'Small', 'M' => 'Medium', 'L' => 'Large', 'XL' => 'Extra Large'];
                        foreach ($prod_sizes as $s):
                            ?>
                            <button type="button" class="prod-size-btn" data-size="<?= htmlspecialchars($s) ?>"
                                onclick="selectSize(this)"
                                style="padding:8px 20px; border-radius:8px; font-weight:700; font-size:0.9rem; border:2px solid var(--border-color); background:var(--bg-muted); cursor:pointer; transition:all 0.15s;">
                                <?= htmlspecialchars($s) ?> <span
                                    style="font-size:0.78rem; font-weight:500; color:var(--text-muted);"><?= htmlspecialchars($size_labels[$s] ?? '') ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quantity & Add to Cart -->
            <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap; margin-bottom:14px;">
                <div class="qty-control" style="padding:8px 12px;">
                    <button class="qty-btn"
                        onclick="let q = document.getElementById('prodQty'); if(q.value>1) q.value--;">-</button>
                    <input type="number" id="prodQty" value="1" min="1" max="<?= $product['stock'] ?>"
                        style="width:45px; text-align:center; border:none; background:transparent; font-weight:700; font-size:1rem;">
                    <button class="qty-btn" onclick="let q = document.getElementById('prodQty'); q.value++;">+</button>
                </div>

                <button onclick="addToCartProduct()" class="btn-primary" style="padding:14px 32px; font-size:1.05rem;">
                    <i class="fa-solid fa-cart-shopping"></i> Add To Cart
                </button>
            </div>

            <!-- Shop Now → Checkout -->
            <button onclick="shopNowProduct()" class="btn-secondary"
                style="width:100%; padding:14px; font-size:1.05rem; text-align:center; justify-content:center; margin-top:4px;">
                <i class="fa-solid fa-bolt"></i> Shop Now — Checkout
            </button>
        </div>

        <script>
            function getSelectedColor() {
                const active = document.querySelector('.prod-color-swatch.active-swatch');
                return active ? active.getAttribute('data-color') : '';
            }
            function getSelectedSize() {
                const active = document.querySelector('.prod-size-btn.active-size');
                return active ? active.getAttribute('data-size') : '';
            }
            function selectColor(btn) {
                document.querySelectorAll('.prod-color-swatch').forEach(b => {
                    b.classList.remove('active-swatch');
                    b.style.borderColor = 'var(--border-color)';
                    b.style.transform = 'scale(1)';
                });
                btn.classList.add('active-swatch');
                btn.style.borderColor = 'var(--primary-color)';
                btn.style.transform = 'scale(1.15)';
                const lbl = document.getElementById('selectedColorLabel');
                if (lbl) lbl.textContent = btn.getAttribute('data-color');
            }
            function selectSize(btn) {
                document.querySelectorAll('.prod-size-btn').forEach(b => {
                    b.classList.remove('active-size');
                    b.style.borderColor = 'var(--border-color)';
                    b.style.background = 'var(--bg-muted)';
                    b.style.color = 'inherit';
                });
                btn.classList.add('active-size');
                btn.style.borderColor = 'var(--primary-color)';
                btn.style.background = 'var(--primary-color)';
                btn.style.color = '#fff';
                const lbl = document.getElementById('selectedSizeLabel');
                if (lbl) lbl.textContent = btn.getAttribute('data-size');
            }
            function addToCartProduct() {
                const color = getSelectedColor();
                const size = getSelectedSize();
                const qty = document.getElementById('prodQty').value;
                const nameSuffix = [color, size].filter(Boolean).join(' / ');
                CartEngine.addItem({
                    id: <?= $product['id'] ?>,
                    name: '<?= addslashes($product['name']) ?>' + (nameSuffix ? ' [' + nameSuffix + ']' : ''),
                    price: <?= $product['price'] ?>,
                    image: '<?= addslashes(get_image_url($product['image'])) ?>',
                    color: color,
                    size: size
                }, qty);
            }
            function shopNowProduct() {
                const color = getSelectedColor();
                const size = getSelectedSize();
                const qty = document.getElementById('prodQty').value;
                const nameSuffix = [color, size].filter(Boolean).join(' / ');
                const item = {
                    id: <?= $product['id'] ?>,
                    name: '<?= addslashes($product['name']) ?>' + (nameSuffix ? ' [' + nameSuffix + ']' : ''),
                    price: <?= $product['price'] ?>,
                    image: '<?= addslashes(get_image_url($product['image'])) ?>',
                    color: color,
                    size: size,
                    qty: parseInt(qty)
                };
                localStorage.setItem('buy_now_item', JSON.stringify([item]));
                window.location.href = 'checkout?buy_now=true';
            }
        </script>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div style="margin-top:70px;">
            <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:24px;">Related Products</h2>
            <div class="product-grid">
                <?php foreach ($related_products as $prod): ?>
                    <div class="product-card">
                        <div class="product-img-wrap">
                            <img src="<?= htmlspecialchars(get_image_url($prod['image'] ?: 'https://images.unsplash.com/photo-1566150905458-1bf1fc15a6a0?w=600&auto=format&fit=crop&q=80')) ?>"
                                alt="<?= htmlspecialchars($prod['name']) ?>">
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars($prod['category_name']) ?></span>
                            <h3 class="product-title"><?= htmlspecialchars($prod['name']) ?></h3>
                            <div class="product-price-wrap">
                                <span class="product-price">Rs. <?= number_format($prod['price'], 0) ?></span>
                            </div>
                            <a href="product?id=<?= $prod['id'] ?>" class="btn-outline"
                                style="text-align:center; margin-top:10px;">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>