<?php
// includes/db.php - PDO MySQL Database Connection & Installer Helper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'viacart');

/**
 * Returns PDO connection to MySQL database, running auto-installation if database or tables do not exist.
 */
function get_db() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // First connect without database selected to verify/create database
        $root_pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $root_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Now connect directly to the database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Check if settings table exists
        $check = $pdo->query("SHOW TABLES LIKE 'settings'");
        if ($check->rowCount() === 0) {
            // Auto install schema
            $schema_file = __DIR__ . '/../schema.sql';
            if (file_exists($schema_file)) {
                $sql = file_get_contents($schema_file);
                $pdo->exec($sql);
            }
        }

        // Auto-upgrade schema for Deals, Combos & Coupons (safe on existing installs)
        $col_check = $pdo->query("SHOW COLUMNS FROM products LIKE 'deal_ends_at'");
        if ($col_check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE products ADD COLUMN deal_ends_at DATETIME NULL DEFAULT NULL AFTER old_price");

            $pdo->exec("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) NULL DEFAULT NULL AFTER payment_method");
            $pdo->exec("ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER coupon_code");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `combos` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(200) NOT NULL,
                `slug` varchar(200) NOT NULL,
                `image` varchar(500) DEFAULT NULL,
                `description` text DEFAULT NULL,
                `combo_price` decimal(10,2) NOT NULL,
                `deal_ends_at` datetime DEFAULT NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `combo_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `combo_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `qty` int(11) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                KEY `combo_id` (`combo_id`),
                KEY `product_id` (`product_id`),
                CONSTRAINT `combo_items_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE CASCADE,
                CONSTRAINT `combo_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `coupons` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `description` varchar(255) DEFAULT NULL,
                `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
                `discount_value` decimal(10,2) NOT NULL,
                `min_order_amount` decimal(10,2) DEFAULT NULL,
                `max_uses` int(11) DEFAULT NULL,
                `used_count` int(11) NOT NULL DEFAULT 0,
                `starts_at` datetime DEFAULT NULL,
                `expires_at` datetime DEFAULT NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }

        // Auto-upgrade: Add colors & sizes columns for product attributes (safe on existing installs)
        $colors_check = $pdo->query("SHOW COLUMNS FROM products LIKE 'colors'");
        if ($colors_check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE products ADD COLUMN colors TEXT NULL DEFAULT NULL AFTER description");
            $pdo->exec("ALTER TABLE products ADD COLUMN sizes TEXT NULL DEFAULT NULL AFTER colors");
        }

        // Auto-upgrade: Add gallery_images column
        $gallery_check = $pdo->query("SHOW COLUMNS FROM products LIKE 'gallery_images'");
        if ($gallery_check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE products ADD COLUMN gallery_images TEXT NULL DEFAULT NULL AFTER image");
        }

        return $pdo;
    } catch (PDOException $e) {
        die("<div style='font-family:sans-serif; padding:30px; background:#fee2e2; color:#991b1b; border-radius:12px; margin:40px auto; max-width:600px; text-align:center;'><h2>MySQL Connection Error</h2><p>" . htmlspecialchars($e->getMessage()) . "</p><p><small>Please make sure MySQL is running in XAMPP.</small></p></div>");
    }
}

/**
 * Get a single setting by key
 */
function get_setting($key, $default = '') {
    $db = get_db();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

/**
 * Update or insert a setting
 */
function set_setting($key, $value) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    return $stmt->execute([$key, $value]);
}

/**
 * Get all settings as an associative key-value array
 */
function get_all_settings() {
    $db = get_db();
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

/**
 * Get page row by key
 */
function get_page_data($page_key) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM pages WHERE page_key = ?");
    $stmt->execute([$page_key]);
    $row = $stmt->fetch();
    if (!$row) {
        return [
            'page_key' => $page_key,
            'title' => ucfirst($page_key),
            'content' => '',
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'hero_title' => '',
            'hero_subtitle' => '',
            'hero_cta_text' => '',
            'hero_cta_url' => '',
            'hero_image' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_address' => '',
            'contact_hours' => ''
        ];
    }
    return $row;
}

/**
 * Handle image file upload safely
 */
function upload_image($file_input_name, $target_dir = 'uploads/') {
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $upload_dir = __DIR__ . '/../' . trim($target_dir, '/') . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_tmp = $_FILES[$file_input_name]['tmp_name'];
    $file_name = $_FILES[$file_input_name]['name'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'jfif', 'png', 'webp', 'gif', 'svg'];
    if (!in_array($ext, $allowed)) {
        return null;
    }

    $new_filename = uniqid('img_', true) . '.' . $ext;
    $target_file = $upload_dir . $new_filename;

    if (move_uploaded_file($file_tmp, $target_file)) {
        return trim($target_dir, '/') . '/' . $new_filename;
    }

    return null;
}

/**
 * Resolves image URLs correctly for both public pages and admin panel pages
 */
function get_image_url($path, $is_admin = false) {
    if (empty($path)) {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    $clean_path = ltrim($path, '/');
    if ($is_admin) {
        return '../' . $clean_path;
    }
    return $clean_path;
}

/**
 * Convert string to URL-friendly slug
 */
function sanitize_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Get all products currently on a discount (old_price set and higher than price),
 * excluding deals whose optional expiry has passed. Sorted by biggest discount % first.
 */
function get_deal_products() {
    $db = get_db();
    $stmt = $db->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug,
               ROUND(((p.old_price - p.price) / p.old_price) * 100) AS discount_percent
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.old_price IS NOT NULL AND p.old_price > p.price
          AND (p.deal_ends_at IS NULL OR p.deal_ends_at > NOW())
        ORDER BY discount_percent DESC, p.created_at DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Get all active combo bundles (with their included products), excluding expired ones.
 */
function get_active_combos() {
    $db = get_db();
    $stmt = $db->query("
        SELECT * FROM combos
        WHERE is_active = 1 AND (deal_ends_at IS NULL OR deal_ends_at > NOW())
        ORDER BY created_at DESC
    ");
    $combos = $stmt->fetchAll();

    foreach ($combos as &$combo) {
        $item_stmt = $db->prepare("
            SELECT ci.qty, p.id, p.name, p.price, p.image
            FROM combo_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.combo_id = ?
        ");
        $item_stmt->execute([$combo['id']]);
        $combo['items'] = $item_stmt->fetchAll();

        $combo['original_total'] = 0;
        foreach ($combo['items'] as $item) {
            $combo['original_total'] += $item['price'] * $item['qty'];
        }
    }
    return $combos;
}

/**
 * Get all active, currently valid coupon codes (for display on the Deals page).
 */
function get_active_coupons() {
    $db = get_db();
    $stmt = $db->query("
        SELECT * FROM coupons
        WHERE is_active = 1
          AND (starts_at IS NULL OR starts_at <= NOW())
          AND (expires_at IS NULL OR expires_at > NOW())
          AND (max_uses IS NULL OR used_count < max_uses)
        ORDER BY created_at DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Validate a coupon code against an order subtotal.
 * Returns ['valid' => bool, 'message' => string, 'discount_amount' => float, 'coupon' => array|null]
 */
function validate_coupon($code, $subtotal) {
    $db = get_db();
    $code = strtoupper(trim($code));
    if ($code === '') {
        return ['valid' => false, 'message' => 'Please enter a coupon code.', 'discount_amount' => 0, 'coupon' => null];
    }

    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid coupon code.', 'discount_amount' => 0, 'coupon' => null];
    }
    if (!$coupon['is_active']) {
        return ['valid' => false, 'message' => 'This coupon is no longer active.', 'discount_amount' => 0, 'coupon' => null];
    }
    if ($coupon['starts_at'] && strtotime($coupon['starts_at']) > time()) {
        return ['valid' => false, 'message' => 'This coupon is not active yet.', 'discount_amount' => 0, 'coupon' => null];
    }
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) <= time()) {
        return ['valid' => false, 'message' => 'This coupon has expired.', 'discount_amount' => 0, 'coupon' => null];
    }
    if ($coupon['max_uses'] !== null && $coupon['used_count'] >= $coupon['max_uses']) {
        return ['valid' => false, 'message' => 'This coupon has reached its usage limit.', 'discount_amount' => 0, 'coupon' => null];
    }
    if ($coupon['min_order_amount'] !== null && $subtotal < $coupon['min_order_amount']) {
        return ['valid' => false, 'message' => 'Minimum order of Rs. ' . number_format($coupon['min_order_amount'], 0) . ' required for this coupon.', 'discount_amount' => 0, 'coupon' => null];
    }

    if ($coupon['discount_type'] === 'percent') {
        $discount = round($subtotal * ($coupon['discount_value'] / 100), 2);
    } else {
        $discount = min($coupon['discount_value'], $subtotal);
    }

    return ['valid' => true, 'message' => 'Coupon applied successfully!', 'discount_amount' => $discount, 'coupon' => $coupon];
}

/**
 * Increment a coupon's used_count (call after a successful order placement).
 */
function increment_coupon_usage($code) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
    $stmt->execute([strtoupper(trim($code))]);
}

/**
 * Send WhatsApp Notification via API Gateway
 */
function send_whatsapp_message($to, $message) {
    $api_url = get_setting('whatsapp_api_url', 'http://13.60.229.151:3000/api/messages/send');
    $api_key = get_setting('whatsapp_api_key', 'wa_api_2c1cc6568c22a1f93affc44330dfd533');
    $enabled = get_setting('whatsapp_notifications_enabled', '1');

    if ($enabled !== '1' || empty($api_url) || empty($api_key) || empty($to)) {
        return false;
    }

    // Clean phone number (digits only)
    $recipient = preg_replace('/[^0-9]/', '', $to);
    if (empty($recipient)) {
        return false;
    }

    $payload = json_encode([
        'to' => $recipient,
        'message' => $message
    ]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code >= 200 && $http_code < 300);
}
