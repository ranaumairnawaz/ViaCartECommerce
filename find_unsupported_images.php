<?php
require_once __DIR__ . '/includes/db.php';
$db = get_db();

$unsupported_exts = ['heic', 'heif', 'avif', 'tiff', 'tif', 'bmp'];
$bad_items = [];

// 1. Products
$products = $db->query("SELECT id, name, image, gallery_images FROM products")->fetchAll();
foreach ($products as $prod) {
    if (!empty($prod['image'])) {
        $ext = strtolower(pathinfo($prod['image'], PATHINFO_EXTENSION));
        if (in_array($ext, $unsupported_exts)) {
            $bad_items[] = "Product: " . htmlspecialchars($prod['name']) . " (ID: {$prod['id']}) - Main Image";
        }
    }
    if (!empty($prod['gallery_images'])) {
        $gallery = explode(',', $prod['gallery_images']);
        foreach ($gallery as $gimg) {
            $ext = strtolower(pathinfo(trim($gimg), PATHINFO_EXTENSION));
            if (in_array($ext, $unsupported_exts)) {
                $bad_items[] = "Product: " . htmlspecialchars($prod['name']) . " (ID: {$prod['id']}) - Gallery Image";
                break;
            }
        }
    }
}

// 2. Categories
$categories = $db->query("SELECT id, name, image FROM categories")->fetchAll();
foreach ($categories as $cat) {
    if (!empty($cat['image'])) {
        $ext = strtolower(pathinfo($cat['image'], PATHINFO_EXTENSION));
        if (in_array($ext, $unsupported_exts)) {
            $bad_items[] = "Category: " . htmlspecialchars($cat['name']) . " (ID: {$cat['id']})";
        }
    }
}

// 3. Combos
try {
    $combos = $db->query("SELECT id, name, image FROM combos")->fetchAll();
    foreach ($combos as $combo) {
        if (!empty($combo['image'])) {
            $ext = strtolower(pathinfo($combo['image'], PATHINFO_EXTENSION));
            if (in_array($ext, $unsupported_exts)) {
                $bad_items[] = "Combo Deal: " . htmlspecialchars($combo['name']) . " (ID: {$combo['id']})";
            }
        }
    }
} catch (Exception $e) {}

// 4. Pages (hero_image)
$pages = $db->query("SELECT page_key, hero_image FROM pages")->fetchAll();
foreach ($pages as $page) {
    if (!empty($page['hero_image'])) {
        $ext = strtolower(pathinfo($page['hero_image'], PATHINFO_EXTENSION));
        if (in_array($ext, $unsupported_exts)) {
            $bad_items[] = "Page: " . htmlspecialchars($page['page_key']) . " - Hero Image";
        }
    }
}

// 5. Settings (logo, favicon, etc)
$settings = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_value LIKE '%.heic' OR setting_value LIKE '%.avif' OR setting_value LIKE '%.bmp' OR setting_value LIKE '%.tiff' OR setting_value LIKE '%.heif'")->fetchAll();
foreach ($settings as $setting) {
    $bad_items[] = "Setting: " . htmlspecialchars($setting['setting_key']) . " (Value: " . htmlspecialchars($setting['setting_value']) . ")";
}

if (empty($bad_items)) {
    echo "<h1>All Good!</h1><p>No unsupported image formats found anywhere in your database.</p>";
} else {
    echo "<h1>Action Required: Unsupported Images Found</h1>";
    echo "<p>The following items contain image formats like .HEIC or .AVIF that may not display on all devices. Please edit them and upload .JPG, .PNG, or .WEBP images instead.</p>";
    echo "<ul>";
    foreach ($bad_items as $item) {
        echo "<li><strong>" . $item . "</strong></li>";
    }
    echo "</ul>";
}
?>
