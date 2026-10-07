<?php
// index.php - Public Homepage

$current_page_key = 'home';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$home_data = get_page_data('home');

// Fetch Categories managed by admin
$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// Fetch Featured Products
$featured_products = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 
    ORDER BY p.id DESC LIMIT 6
")->fetchAll();
?>

<!-- 1. Hero Section (Dynamic CMS) -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <span class="hero-badge">NEW COLLECTION 2026</span>
                <h1 class="hero-title">
                    <?= htmlspecialchars($home_data['hero_title'] ?: 'Exquisite Elegance for Everyday Luxury') ?></h1>
                <p class="hero-desc">
                    <?= htmlspecialchars($home_data['hero_subtitle'] ?: 'Explore ViaCart curated signature collection of hand-crafted premium bags designed to elevate every occasion.') ?>
                </p>
                <div style="display:flex; gap:16px; flex-wrap:wrap;">
                    <a href="checkout" class="btn-primary">
                        <?= htmlspecialchars($home_data['hero_cta_text'] ?: 'Shop Collection') ?> &rarr;
                    </a>
                    <a href="track-order" class="btn-outline">Track Your Order</a>
                </div>
            </div>
            <div class="hero-image-wrapper">
                <div class="hero-image-card">
                    <img src="<?= htmlspecialchars(get_image_url($home_data['hero_image'] ?: 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=1200&auto=format&fit=crop&q=80')) ?>"
                        alt="ViaCart Featured Luxury Bag">
                    <div class="hero-tag">
                        <div style="font-size:2rem;"><i class="fa-solid fa-sparkles"></i></div>
                        <div>
                            <strong style="display:block; font-size:1.1rem; color:var(--secondary-color);">Premium
                                Quality</strong>
                            <span style="font-size:0.85rem; color:var(--text-muted);">Crafted with Genuine
                                Materials</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Categories Grid Section (Dynamic CMS Managed) -->
<section class="categories-section">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-tag">EXPLORE COLLECTIONS</span>
            <h2 class="section-title">Shop By Category</h2>
            <p class="section-subtitle">Discover our handcrafted luxury bag styles carefully curated for your wardrobe.
            </p>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <div class="category-card">
                    <div class="category-img-wrap">
                        <img src="<?= htmlspecialchars(get_image_url($cat['image'] ?: 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=600&auto=format&fit=crop&q=80')) ?>"
                            alt="<?= htmlspecialchars($cat['name']) ?>">
                    </div>
                    <div class="category-content">
                        <h3 class="category-name"><?= htmlspecialchars($cat['name']) ?></h3>
                        <p class="category-desc">
                            <?= htmlspecialchars($cat['description'] ?: 'Explore sleek luxury handcrafted ' . strtolower($cat['name']) . 's.') ?>
                        </p>
                        <a href="shop?category=<?= htmlspecialchars($cat['slug']) ?>" class="btn-primary"
                            style="text-align:center; justify-content:center;">
                            See Products of <?= htmlspecialchars($cat['name']) ?> &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 3. Featured Products Section -->
<section class="products-section">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-tag">POPULAR SELECTION</span>
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Handpicked luxury bestsellers crafted with precision and elegance.</p>
        </div>

        <div class="product-grid">
            <?php foreach ($featured_products as $prod): ?>
                <div class="product-card">
                    <?php if ($prod['old_price'] && $prod['old_price'] > $prod['price']): ?>
                        <span class="product-badge">SALE</span>
                    <?php endif; ?>

                    <div class="product-img-wrap">
                        <img src="<?= htmlspecialchars(get_image_url($prod['image'] ?: 'https://images.unsplash.com/photo-1566150905458-1bf1fc15a6a0?w=600&auto=format&fit=crop&q=80')) ?>"
                            alt="<?= htmlspecialchars($prod['name']) ?>">
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?= htmlspecialchars($prod['category_name']) ?></span>
                        <h3 class="product-title"><?= htmlspecialchars($prod['name']) ?></h3>
                        <div class="product-price-wrap">
                            <span class="product-price">Rs. <?= number_format($prod['price'], 0) ?></span>
                            <?php if ($prod['old_price'] && $prod['old_price'] > $prod['price']): ?>
                                <span class="product-old-price">Rs. <?= number_format($prod['old_price'], 0) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex; gap:8px; flex-direction:column;">
                            <div style="display:flex; gap:8px;">
                                <a href="product?id=<?= $prod['id'] ?>" class="btn-outline"
                                    style="flex:1; text-align:center; padding:10px 0;">Details</a>
                                <button
                                    onclick="CartEngine.addItem({id: <?= $prod['id'] ?>, name: '<?= addslashes($prod['name']) ?>', price: <?= $prod['price'] ?>, image: '<?= addslashes(get_image_url($prod['image'])) ?>'})"
                                    class="btn-primary" style="padding:10px 14px;" title="Add to Cart">
                                    <i class="fa-solid fa-cart-shopping"></i> Add
                                </button>
                            </div>
                            <button
                                onclick="CartEngine.buyNow({id: <?= $prod['id'] ?>, name: '<?= addslashes($prod['name']) ?>', price: <?= $prod['price'] ?>, image: '<?= addslashes(get_image_url($prod['image'])) ?>'})"
                                class="btn-secondary"
                                style="width:100%; padding:10px; font-size:0.9rem; text-align:center; justify-content:center;">
                                <i class="fa-solid fa-bolt"></i> Shop Now
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center; margin-top:40px;">
            <a href="shop" class="btn-secondary">View All Products in Shop &rarr;</a>
        </div>
    </div>
</section>

<!-- 4. Value Propositions Bar -->
<section class="features-bar">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div>
                    <h4 class="feature-title">Free Express Shipping</h4>
                    <p class="feature-desc">On all orders over Rs. 5,000 nationwide</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-lock"></i></div>
                <div>
                    <h4 class="feature-title">Secure Order Tracking</h4>
                    <p class="feature-desc">Real-time order status tracking system</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-solid fa-star"></i></div>
                <div>
                    <h4 class="feature-title">Authentic Craftsmanship</h4>
                    <p class="feature-desc">100% genuine luxury materials</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fa-brands fa-whatsapp"></i></div>
                <div>
                    <h4 class="feature-title">Dedicated Support</h4>
                    <p class="feature-desc">Friendly assistance 24/7 online</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>