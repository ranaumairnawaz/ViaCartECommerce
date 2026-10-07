-- ViaCart MySQL Schema and Seed Data

CREATE DATABASE IF NOT EXISTS `viacart` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `viacart`;

-- 1. Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Pages Table
CREATE TABLE IF NOT EXISTS `pages` (
  `page_key` VARCHAR(50) NOT NULL PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NULL,
  `meta_title` VARCHAR(255) NULL,
  `meta_description` TEXT NULL,
  `meta_keywords` TEXT NULL,
  `hero_title` VARCHAR(255) NULL,
  `hero_subtitle` TEXT NULL,
  `hero_cta_text` VARCHAR(100) NULL,
  `hero_cta_url` VARCHAR(255) NULL,
  `hero_image` VARCHAR(500) NULL,
  `contact_email` VARCHAR(100) NULL,
  `contact_phone` VARCHAR(100) NULL,
  `contact_address` TEXT NULL,
  `contact_hours` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `image` VARCHAR(500) NULL,
  `description` TEXT NULL,
  `meta_title` VARCHAR(255) NULL,
  `meta_description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `category_id` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `old_price` DECIMAL(10,2) NULL,
  `deal_ends_at` DATETIME NULL,
  `stock` INT NOT NULL DEFAULT 10,
  `description` TEXT NULL,
  `image` VARCHAR(500) NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `meta_title` VARCHAR(255) NULL,
  `meta_description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `tracking_code` VARCHAR(100) NOT NULL UNIQUE,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `customer_address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'COD',
  `coupon_code` VARCHAR(50) NULL,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `carrier_name` VARCHAR(100) NULL,
  `status_notes` TEXT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `items_json` LONGTEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Combos Table (bundle multiple products at a special price)
CREATE TABLE IF NOT EXISTS `combos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `image` VARCHAR(500) NULL,
  `description` TEXT NULL,
  `combo_price` DECIMAL(10,2) NOT NULL,
  `deal_ends_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Combo Items Table (products included in each combo)
CREATE TABLE IF NOT EXISTS `combo_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `combo_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `qty` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`combo_id`) REFERENCES `combos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `discount_type` ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) NULL,
  `max_uses` INT NULL,
  `used_count` INT NOT NULL DEFAULT 0,
  `starts_at` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed Initial Settings
-- --------------------------------------------------------
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'ViaCart'),
('tagline', 'A Pure Mind, Pure Luxury Bags'),
('logo_url', ''),
('favicon_url', ''),
('primary_color', '#3b82f6'),
('secondary_color', '#0f172a'),
('background_color', '#f8fafc'),
('typography', 'Inter'),
('admin_email', 'admin@viacart.com'),
('whatsapp_api_url', 'http://13.60.229.151:3000/api/messages/send'),
('whatsapp_api_key', 'wa_api_2c1cc6568c22a1f93affc44330dfd533'),
('whatsapp_number', '12125550199'),
('whatsapp_notifications_enabled', '1');

-- Seed Initial Pages
INSERT IGNORE INTO `pages` (`page_key`, `title`, `content`, `meta_title`, `meta_description`, `meta_keywords`, `hero_title`, `hero_subtitle`, `hero_cta_text`, `hero_cta_url`, `hero_image`, `contact_email`, `contact_phone`, `contact_address`, `contact_hours`) VALUES
('home', 'Home', '', 'ViaCart | Luxury Bags & Modern Fashion', 'Discover exquisite luxury handbags, shoulder bags, envelope bags, and hobos crafted with timeless elegance.', 'viacart, luxury bags, handbags, envelope bag, hobo bag, frame bag', 'Exquisite Elegance for Everyday Luxury', 'Explore ViaCart curated signature collection of hand-crafted premium bags designed to elevate every occasion.', 'Shop Collection', 'shop.php', 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=1200&auto=format&fit=crop&q=80', '', '', '', ''),
('shop', 'Shop', '', 'Shop Collection | ViaCart Luxury Bags', 'Browse our complete catalog of luxury handbags, clutches, shoulder bags, and tote bags with fast nationwide delivery.', 'shop luxury bags, buy handbags online, via cart shop', '', '', '', '', '', '', '', '', ''),
('checkout', 'Checkout', '', 'Checkout | ViaCart', 'Complete your order securely with Cash on Delivery or Card payment at ViaCart.', 'checkout, place order, via cart', '', '', '', '', '', '', '', '', ''),
('contact', 'Contact Us', 'Have questions about your order or our collections? Get in touch with our customer service support team. We are happy to assist you!', 'Contact Us | ViaCart Support', 'Get in touch with ViaCart team for inquiries, support, and custom assistance.', 'contact viacart, customer support, email via cart', '', '', '', '', '', 'support@viacart.com', '+1 (800) 555-VIACART', '123 Luxury Boulevard, Suite 400, New York, NY 10001', 'Mon - Sat: 9:00 AM - 8:00 PM EST'),
('privacy', 'Privacy Policy', '<h2>Privacy Policy</h2><p>At ViaCart, we respect your privacy and are committed to protecting your personal data. This privacy notice explains how we collect, use, and process your information when you visit our website or make a purchase.</p><h3>1. Information We Collect</h3><p>We collect customer details including name, email address, phone number, shipping address, and order transaction details to process your purchases safely.</p><h3>2. How We Use Your Information</h3><p>Your data is strictly used for order processing, customer support, shipping tracking updates, and optional promotional announcements if subscribed.</p><h3>3. Data Protection & Security</h3><p>We implement robust physical and electronic security controls to prevent unauthorized access or disclosure of your personal data.</p>', 'Privacy Policy | ViaCart', 'Read ViaCart Privacy Policy detailing how we handle customer data, privacy rights, and transaction security.', 'privacy policy, viacart privacy, customer data security', '', '', '', '', '', '', '', '', '');

-- Seed Default Admin User (Password: admin123)
INSERT IGNORE INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$AZDxmwPFOIU0yLhqJvOap.aoVlJRFwivVGc/CaTNeoQfgWhYTPgWi');
