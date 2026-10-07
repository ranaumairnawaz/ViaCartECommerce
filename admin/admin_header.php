<?php
// admin/admin_header.php - Shared Admin Layout Header & Sidebar

require_once __DIR__ . '/../includes/auth.php';
check_admin_auth();

$db = get_db();
$settings = get_all_settings();
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'ViaCart';
$active_tab = isset($admin_page) ? $admin_page : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin CMS | <?= htmlspecialchars($site_name) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body style="background:var(--bg-body);">

<!-- Mobile Top Bar with Hamburger Menu -->
<div class="admin-mobile-bar" style="display:none; background:var(--secondary-color); color:#ffffff; padding:16px 20px; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:101; border-bottom:1px solid rgba(255,255,255,0.1);">
    <div style="font-size:1.2rem; font-weight:800; color:var(--primary-color);">
        <i class="fa-solid fa-bag-shopping"></i> <?= htmlspecialchars($site_name) ?> CMS
    </div>
    <button onclick="AdminDrawer.toggle();" style="background:var(--secondary-light); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:8px 14px; border-radius:8px; font-weight:700; cursor:pointer;">
        <i class="fa-solid fa-bars"></i> Menu
    </button>
</div>

<!-- Sidebar Drawer Overlay (Mobile) -->
<div class="admin-sidebar-overlay" onclick="AdminDrawer.close();"></div>

<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <i class="fa-solid fa-bag-shopping"></i> <?= htmlspecialchars($site_name) ?> CMS
        </div>

        <ul class="admin-menu">
            <li>
                <a href="index" class="<?= $active_tab === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="settings" class="<?= $active_tab === 'settings' ? 'active' : '' ?>">
                    <i class="fa-solid fa-palette"></i> Brand & SEO CMS
                </a>
            </li>
            <li>
                <a href="pages" class="<?= $active_tab === 'pages' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-pen"></i> Page Content CMS
                </a>
            </li>
            <li>
                <a href="categories" class="<?= $active_tab === 'categories' ? 'active' : '' ?>">
                    <i class="fa-solid fa-folder"></i> Categories CMS
                </a>
            </li>
            <li>
                <a href="products" class="<?= $active_tab === 'products' ? 'active' : '' ?>">
                    <i class="fa-solid fa-bag-shopping"></i> Products CMS
                </a>
            </li>
            <li>
                <a href="combos" class="<?= $active_tab === 'combos' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gift"></i> Combo Deals
                </a>
            </li>
            <li>
                <a href="coupons" class="<?= $active_tab === 'coupons' ? 'active' : '' ?>">
                    <i class="fa-solid fa-tag"></i> Coupon Codes
                </a>
            </li>
            <li>
                <a href="orders" class="<?= $active_tab === 'orders' ? 'active' : '' ?>">
                    <i class="fa-solid fa-box"></i> Orders & Tracking
                </a>
            </li>
        </ul>

        <div style="margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,0.1);">
            <a href="../index" target="_blank" style="color:#94a3b8; font-size:0.9rem; display:block; margin-bottom:10px;"><i class="fa-solid fa-globe"></i> View Live Website <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            <a href="logout" style="color:#f87171; font-size:0.9rem; font-weight:600; display:block;"><i class="fa-solid fa-right-from-bracket"></i> Log Out Admin</a>
        </div>
    </aside>

    <!-- Admin Main Content Area -->
    <main class="admin-main">
