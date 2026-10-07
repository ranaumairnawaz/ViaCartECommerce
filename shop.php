<?php
// shop.php - Public Shop Catalog Page

$current_page_key = 'shop';
require_once __DIR__ . '/includes/db.php';

$db = get_db();
$selected_category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_option = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';

// Fetch Categories for sidebar filter
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Build Query dynamically
$where_clauses = ["1=1"];
$params = [];

if (!empty($selected_category_slug)) {
    $stmt = $db->prepare("SELECT id, name, meta_title, meta_description FROM categories WHERE slug = ?");
    $stmt->execute([$selected_category_slug]);
    $cat_info = $stmt->fetch();
    if ($cat_info) {
        $where_clauses[] = "p.category_id = ?";
        $params[] = $cat_info['id'];
        $page_seo_title = !empty($cat_info['meta_title']) ? $cat_info['meta_title'] : $cat_info['name'] . " Collection | ViaCart";
        $page_seo_desc = !empty($cat_info['meta_description']) ? $cat_info['meta_description'] : "Shop luxury " . $cat_info['name'] . " bags at ViaCart.";
    }
}

if (!empty($search_query)) {
    $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_sql = implode(" AND ", $where_clauses);

// Sorting
$order_by = "p.id DESC";
if ($sort_option === 'price_low') {
    $order_by = "p.price ASC";
} elseif ($sort_option === 'price_high') {
    $order_by = "p.price DESC";
} elseif ($sort_option === 'name_asc') {
    $order_by = "p.name ASC";
}

$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE $where_sql 
    ORDER BY $order_by
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:40px; padding-bottom:80px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; margin-bottom:30px;">
        <div>
            <h1 style="font-size:2.2rem; font-weight:800;">
                <?= !empty($selected_category_slug) && isset($cat_info) ? htmlspecialchars($cat_info['name']) : 'Shop All Products' ?>
            </h1>
            <p style="color:var(--text-muted);">Showing <?= count($products) ?> luxury products</p>
        </div>

        <!-- Search & Filter Controls -->
        <form method="GET" action="shop" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center; width:100%;">
            <?php if (!empty($selected_category_slug)): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category_slug) ?>">
            <?php endif; ?>

            <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search bags..." class="form-control" style="flex:1; min-width:140px; padding:10px 14px;">

            <select name="sort" class="form-control" style="min-width:150px; padding:10px 14px;" onchange="this.form.submit()">
                <option value="default" <?= $sort_option === 'default' ? 'selected' : '' ?>>Newest Arrivals</option>
                <option value="price_low" <?= $sort_option === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= $sort_option === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name_asc" <?= $sort_option === 'name_asc' ? 'selected' : '' ?>>Name: A - Z</option>
            </select>

            <button type="submit" class="btn-primary" style="padding:10px 18px;">Search</button>
            <?php if (!empty($selected_category_slug) || !empty($search_query)): ?>
                <a href="shop" class="btn-outline" style="padding:10px 14px;">Reset Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="layout-shop">
        <!-- Category Sidebar Filter -->
        <aside class="shop-sidebar" style="background:#fff; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px;">
            <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:16px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">Categories</h3>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:10px;">
                <li>
                    <a href="shop" style="font-weight:<?= empty($selected_category_slug) ? '700; color:var(--primary-color)' : '500' ?>">
                        All Categories
                    </a>
                </li>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="shop?category=<?= htmlspecialchars($cat['slug']) ?>" style="font-weight:<?= $selected_category_slug === $cat['slug'] ? '700; color:var(--primary-color)' : '500' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Product Grid -->
        <main>
            <?php if (empty($products)): ?>
                <div style="background:#fff; border:1px dashed var(--border-color); border-radius:var(--radius-md); padding:60px; text-align:center;">
                    <div style="font-size:3rem; margin-bottom:14px;"><i class="fa-solid fa-bag-shopping"></i></div>
                    <h3 style="font-size:1.4rem; font-weight:700;">No Products Found</h3>
                    <p style="color:var(--text-muted); margin-top:8px;">Try clearing your search query or selecting a different category.</p>
                    <a href="shop" class="btn-primary" style="margin-top:20px;">View All Products</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $prod): ?>
                        <div class="product-card">
                            <?php if ($prod['old_price'] && $prod['old_price'] > $prod['price']): ?>
                                <span class="product-badge">SALE</span>
                            <?php endif; ?>

                            <div class="product-img-wrap">
                                <img src="<?= htmlspecialchars(get_image_url($prod['image'] ?: 'https://images.unsplash.com/photo-1566150905458-1bf1fc15a6a0?w=600&auto=format&fit=crop&q=80')) ?>" alt="<?= htmlspecialchars($prod['name']) ?>">
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
                                        <a href="product?id=<?= $prod['id'] ?>" class="btn-outline" style="flex:1; text-align:center; padding:10px 0;">Details</a>
                                        <button onclick="CartEngine.addItem({id: <?= $prod['id'] ?>, name: '<?= addslashes($prod['name']) ?>', price: <?= $prod['price'] ?>, image: '<?= addslashes(get_image_url($prod['image'])) ?>'})" class="btn-primary" style="padding:10px 14px;" title="Add to Cart">
                                            <i class="fa-solid fa-cart-shopping"></i> Add
                                        </button>
                                    </div>
                                    <button onclick="CartEngine.buyNow({id: <?= $prod['id'] ?>, name: '<?= addslashes($prod['name']) ?>', price: <?= $prod['price'] ?>, image: '<?= addslashes(get_image_url($prod['image'])) ?>'})" class="btn-secondary" style="width:100%; padding:10px; font-size:0.9rem; text-align:center; justify-content:center;">
                                        <i class="fa-solid fa-bolt"></i> Shop Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
