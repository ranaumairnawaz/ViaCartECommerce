<?php
// includes/header.php - Dynamic Public Header & SEO Engine

require_once __DIR__ . '/db.php';

$settings = get_all_settings();

// Dynamic Settings with defaults
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'ViaCart';
$tagline = !empty($settings['tagline']) ? $settings['tagline'] : 'Luxury Bags & Accessories';
$primary_color = !empty($settings['primary_color']) ? $settings['primary_color'] : '#3b82f6';
$secondary_color = !empty($settings['secondary_color']) ? $settings['secondary_color'] : '#0f172a';
$background_color = !empty($settings['background_color']) ? $settings['background_color'] : '#f8fafc';
$typography = !empty($settings['typography']) ? $settings['typography'] : 'Inter';
$logo_url = !empty($settings['logo_url']) ? $settings['logo_url'] : '';
$favicon_url = !empty($settings['favicon_url']) ? $settings['favicon_url'] : '';

// Page SEO Meta Fallbacks
$current_page = isset($current_page_key) ? $current_page_key : 'home';
$page_data = get_page_data($current_page);

$meta_title = !empty($page_seo_title) ? $page_seo_title : (!empty($page_data['meta_title']) ? $page_data['meta_title'] : $site_name . ' | ' . $tagline);
$meta_desc = !empty($page_seo_desc) ? $page_seo_desc : (!empty($page_data['meta_description']) ? $page_data['meta_description'] : 'Shop luxury bags at ' . $site_name);
$meta_keywords = !empty($page_seo_keywords) ? $page_seo_keywords : (!empty($page_data['meta_keywords']) ? $page_data['meta_keywords'] : 'viacart, e-commerce, luxury bags');

// Google Font loader
$font_family_map = [
    'Inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
    'Outfit' => 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap',
    'Playfair Display' => 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Inter:wght@400;500;600&display=swap',
    'Montserrat' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap',
    'Roboto' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap',
    'Poppins' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
];
$font_url = isset($font_family_map[$typography]) ? $font_family_map[$typography] : $font_family_map['Inter'];
$heading_font = ($typography === 'Playfair Display') ? "'Playfair Display', serif" : "'$typography', sans-serif";
$body_font = "'$typography', sans-serif";

// Fetch categories for navbar
$db = get_db();
$nav_categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="<?= htmlspecialchars($secondary_color) ?>">
    <title><?= htmlspecialchars($meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">

    <?php if ($favicon_url): ?>
        <link rel="icon" href="<?= htmlspecialchars($favicon_url) ?>">
    <?php endif; ?>

    <!-- Google Fonts Integration -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?= $font_url ?>" rel="stylesheet">

    <!-- CSS stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


    <!-- Dynamic Theme Styling -->
    <style>
        :root {
            --primary-color:
                <?= htmlspecialchars($primary_color) ?>
            ;
            --primary-hover:
                <?= htmlspecialchars($primary_color) ?>
                e6;
            --secondary-color:
                <?= htmlspecialchars($secondary_color) ?>
            ;
            --bg-body:
                <?= htmlspecialchars($background_color) ?>
            ;
            --font-heading:
                <?= $heading_font ?>
            ;
            --font-body:
                <?= $body_font ?>
            ;
        }

        body,
        .hero-section,
        .products-section,
        .features-bar {
            background: var(--bg-body) !important;
        }

        /* Navbar Dropdown Styles */
        .nav-links li.dropdown {
            position: relative;
            display: inline-block;
        }

        .nav-links li.dropdown .dropdown-content {
            display: none;
            position: absolute;
            background-color: var(--secondary-color);
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 999;
            border-radius: var(--radius-sm);
            top: 100%;
            left: 0;
            margin-top: 10px;
        }

        .nav-links li.dropdown:hover .dropdown-content {
            display: block;
        }

        .nav-links li.dropdown .dropdown-content a {
            color: #fff;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
            transition: background 0.3s;
        }

        .nav-links li.dropdown .dropdown-content a:hover {
            background-color: var(--primary-color);
        }
    </style>
</head>

<body>

    <!-- Header Top Announcement Bar -->
    <div class="header-top">
        <div class="container">
            <span><i class="fa-solid fa-sparkles"></i> Enjoy Free Nationwide Delivery on Orders Over Rs. 5,000!</span>
            <a href="track-order" style="margin-left:15px; color:#ffffff; text-decoration:underline;">Track Your
                Order</a>
        </div>
    </div>

    <!-- Main Header -->
    <header class="site-header">
        <div class="container">
            <nav class="navbar">
                <!-- Brand Logo -->
                <a href="index" class="brand-logo">
                    <?php if ($logo_url): ?>
                        <img src="<?= htmlspecialchars($logo_url) ?>" alt="<?= htmlspecialchars($site_name) ?>">
                    <?php else: ?>
                        <span><i class="fa-solid fa-bag-shopping"></i> <?= htmlspecialchars($site_name) ?></span>
                    <?php endif; ?>
                </a>

                <!-- Navigation Links -->
                <ul class="nav-links">
                    <li><a href="index" class="<?= ($current_page === 'home') ? 'active' : '' ?>">Home</a></li>
                    <li><a href="shop" class="<?= ($current_page === 'shop') ? 'active' : '' ?>">Shop</a></li>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropbtn">Categories <i
                                class="fa-solid fa-caret-down"></i></a>
                        <div class="dropdown-content">
                            <?php foreach ($nav_categories as $cat): ?>
                                <a
                                    href="shop?category=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li><a href="deals" class="<?= ($current_page === 'deals') ? 'active' : '' ?>"
                            style="color:#ef4444; font-weight:800;"><i class="fa-solid fa-fire"></i> Deals</a></li>
                    <li><a href="checkout" class="<?= ($current_page === 'checkout') ? 'active' : '' ?>">Checkout</a>
                    </li>
                    <li><a href="track-order" class="<?= ($current_page === 'track') ? 'active' : '' ?>">Track Order</a>
                    </li>
                    <li><a href="contact" class="<?= ($current_page === 'contact') ? 'active' : '' ?>">Contact</a></li>
                    <li><a href="privacy-policy" class="<?= ($current_page === 'privacy') ? 'active' : '' ?>">Privacy
                            Policy</a></li>
                </ul>

                <!-- Action Buttons & Cart -->
                <div class="nav-actions">
                    <a href="cart" class="btn-icon" title="Shopping Cart">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-badge" style="display:none;">0</span>
                    </a>
                    <button type="button" class="nav-toggle" onclick="PublicDrawer.toggle();" title="Menu"
                        aria-label="Open menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <!-- Mobile Sidebar Drawer Overlay -->
    <div class="public-sidebar-overlay" onclick="PublicDrawer.close();"></div>

    <!-- Mobile Sidebar Drawer -->
    <aside class="public-sidebar">
        <div class="public-sidebar-header">
            <span class="public-sidebar-brand">
                <?php if ($logo_url): ?>
                    <img src="<?= htmlspecialchars($logo_url) ?>" alt="<?= htmlspecialchars($site_name) ?>">
                <?php else: ?>
                    <i class="fa-solid fa-bag-shopping"></i> <?= htmlspecialchars($site_name) ?>
                <?php endif; ?>
            </span>
            <button type="button" class="public-sidebar-close" onclick="PublicDrawer.close();"
                aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <ul class="public-sidebar-menu">
            <li><a href="index" class="<?= ($current_page === 'home') ? 'active' : '' ?>"><i
                        class="fa-solid fa-house"></i> Home</a></li>
            <li><a href="shop" class="<?= ($current_page === 'shop') ? 'active' : '' ?>"><i
                        class="fa-solid fa-bag-shopping"></i> Shop</a></li>
            <li>
                <a href="javascript:void(0)" onclick="toggleMobileCategories(this)"
                    style="display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fa-solid fa-list"></i> Categories</span>
                    <i class="fa-solid fa-chevron-down" style="font-size:0.8em; transition: transform 0.3s;"></i>
                </a>
                <ul id="mobileCategoriesList"
                    style="display: none; padding-left: 15px; background: rgba(0,0,0,0.02); list-style: none;">
                    <?php foreach ($nav_categories as $cat): ?>
                        <li><a href="shop?category=<?= htmlspecialchars($cat['slug']) ?>"
                                style="font-size: 0.95rem; padding: 10px 20px; <?= ($current_page === 'category') ? 'active' : '' ?>"><i
                                    class="fa-solid fa-caret-right" style="font-size: 0.8em; margin-right: 5px;"></i>
                                <?= htmlspecialchars($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li><a href="deals" class="<?= ($current_page === 'deals') ? 'active' : '' ?>" style="color:#ef4444;"><i
                        class="fa-solid fa-fire"></i> Deals</a></li>
            <li><a href="cart" class="<?= ($current_page === 'cart') ? 'active' : '' ?>"><i
                        class="fa-solid fa-cart-shopping"></i> Cart</a></li>
            <li><a href="checkout" class="<?= ($current_page === 'checkout') ? 'active' : '' ?>"><i
                        class="fa-solid fa-circle-check"></i> Checkout</a></li>
            <li><a href="track-order" class="<?= ($current_page === 'track') ? 'active' : '' ?>"><i
                        class="fa-solid fa-box"></i> Track Order</a></li>
            <li><a href="contact" class="<?= ($current_page === 'contact') ? 'active' : '' ?>"><i
                        class="fa-solid fa-envelope"></i> Contact</a></li>
            <li><a href="privacy-policy" class="<?= ($current_page === 'privacy') ? 'active' : '' ?>"><i
                        class="fa-solid fa-file-lines"></i> Privacy Policy</a></li>
        </ul>
    </aside>

    <script>
        function toggleMobileCategories(el) {
            var list = document.getElementById('mobileCategoriesList');
            var icon = el.querySelector('.fa-chevron-down');
            if (list.style.display === "none") {
                list.style.display = "block";
                icon.style.transform = "rotate(180deg)";
            } else {
                list.style.display = "none";
                icon.style.transform = "rotate(0deg)";
            }
        }
    </script>