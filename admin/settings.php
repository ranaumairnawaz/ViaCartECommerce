<?php
// admin/settings.php - Brand Design CMS, WhatsApp Gateway, & Full Site SEO CMS

$admin_page = 'settings';
require_once __DIR__ . '/admin_header.php';

$db = get_db();
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. General & Design Settings
    set_setting('site_name', trim($_POST['site_name'] ?? 'ViaCart'));
    set_setting('tagline', trim($_POST['tagline'] ?? ''));
    set_setting('primary_color', trim($_POST['primary_color'] ?? '#3b82f6'));
    set_setting('secondary_color', trim($_POST['secondary_color'] ?? '#0f172a'));
    set_setting('background_color', trim($_POST['background_color'] ?? '#f8fafc'));
    set_setting('typography', trim($_POST['typography'] ?? 'Inter'));

    // Handle Logo File Upload or Text URL
    $uploaded_logo = upload_image('logo_file');
    if ($uploaded_logo) {
        set_setting('logo_url', $uploaded_logo);
    } elseif (!empty($_POST['logo_url'])) {
        set_setting('logo_url', trim($_POST['logo_url']));
    }

    // Handle Favicon File Upload or Text URL
    $uploaded_favicon = upload_image('favicon_file');
    if ($uploaded_favicon) {
        set_setting('favicon_url', $uploaded_favicon);
    } elseif (!empty($_POST['favicon_url'])) {
        set_setting('favicon_url', trim($_POST['favicon_url']));
    }

    // 2. WhatsApp Gateway Settings
    set_setting('whatsapp_api_url', trim($_POST['whatsapp_api_url'] ?? 'http://13.60.229.151:3000/api/messages/send'));
    set_setting('whatsapp_api_key', trim($_POST['whatsapp_api_key'] ?? ''));
    set_setting('whatsapp_number', trim($_POST['whatsapp_number'] ?? ''));
    set_setting('whatsapp_notifications_enabled', isset($_POST['whatsapp_notifications_enabled']) ? '1' : '0');

    // 3. SEO Settings for all pages
    $pages_seo = ['home', 'shop', 'checkout', 'contact', 'privacy'];
    foreach ($pages_seo as $p_key) {
        $meta_title = trim($_POST["seo_{$p_key}_title"] ?? '');
        $meta_desc = trim($_POST["seo_{$p_key}_desc"] ?? '');
        $meta_keywords = trim($_POST["seo_{$p_key}_keywords"] ?? '');

        $stmt = $db->prepare("UPDATE pages SET meta_title = ?, meta_description = ?, meta_keywords = ? WHERE page_key = ?");
        $stmt->execute([$meta_title, $meta_desc, $meta_keywords, $p_key]);
    }

    $success_msg = 'Brand Design, WhatsApp Gateway, & Page SEO Settings successfully saved!';
}

$settings = get_all_settings();
$home_page = get_page_data('home');
$shop_page = get_page_data('shop');
$checkout_page = get_page_data('checkout');
$contact_page = get_page_data('contact');
$privacy_page = get_page_data('privacy');
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">Brand Design & WhatsApp CMS</h1>
        <p style="color:var(--text-muted);">Customize primary & secondary colors, typography, logos, WhatsApp API
            gateway, and SEO meta tags.</p>
    </div>
</div>

<?php if (!empty($success_msg)): ?>
    <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<form method="POST" action="settings" enctype="multipart/form-data">
    <!-- 1. Brand Identity & Colors Card -->
    <div class="admin-card">
        <h3
            style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
            1. Brand Identity & Design Systems
        </h3>

        <div class="grid-2">
            <div class="form-group">
                <label>Brand Name</label>
                <input type="text" name="site_name" class="form-control" required
                    value="<?= htmlspecialchars($settings['site_name'] ?? 'ViaCart') ?>">
            </div>
            <div class="form-group">
                <label>Brand Tagline</label>
                <input type="text" name="tagline" class="form-control"
                    value="<?= htmlspecialchars($settings['tagline'] ?? '') ?>" placeholder="A Pure Mind, Pure Bag">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Primary Brand Color</label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="color" name="primary_color"
                        value="<?= htmlspecialchars($settings['primary_color'] ?? '#3b82f6') ?>"
                        style="width:50px; height:44px; border:none; cursor:pointer;">
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($settings['primary_color'] ?? '#3b82f6') ?>" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Secondary Color (Headings & Dark Elements)</label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="color" name="secondary_color"
                        value="<?= htmlspecialchars($settings['secondary_color'] ?? '#0f172a') ?>"
                        style="width:50px; height:44px; border:none; cursor:pointer;">
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($settings['secondary_color'] ?? '#0f172a') ?>" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Background Color</label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="color" name="background_color"
                        value="<?= htmlspecialchars($settings['background_color'] ?? '#f8fafc') ?>"
                        style="width:50px; height:44px; border:none; cursor:pointer;">
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($settings['background_color'] ?? '#f8fafc') ?>" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Body & Heading Typography</label>
                <select name="typography" class="form-control">
                    <?php
                    $fonts = ['Inter', 'Outfit', 'Playfair Display', 'Montserrat', 'Roboto', 'Poppins'];
                    $current_font = $settings['typography'] ?? 'Inter';
                    foreach ($fonts as $f):
                        ?>
                        <option value="<?= $f ?>" <?= $current_font === $f ? 'selected' : '' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid-2" style="margin-top:10px;">
            <!-- Logo Input -->
            <div class="form-group">
                <label>Brand Logo Image (Upload or URL)</label>
                <input type="file" name="logo_file" class="form-control" data-preview="logoPreview">
                <input type="text" name="logo_url" class="form-control" style="margin-top:8px;"
                    placeholder="Or paste logo image URL..."
                    value="<?= htmlspecialchars($settings['logo_url'] ?? '') ?>">
                <?php if (!empty($settings['logo_url'])): ?>
                    <img id="logoPreview" src="<?= htmlspecialchars($settings['logo_url']) ?>"
                        style="max-height:50px; margin-top:10px; border-radius:6px; border:1px solid var(--border-color);">
                <?php else: ?>
                    <img id="logoPreview" style="max-height:50px; margin-top:10px; display:none;">
                <?php endif; ?>
            </div>

            <!-- Favicon Input -->
            <div class="form-group">
                <label>Favicon Icon (Upload or URL)</label>
                <input type="file" name="favicon_file" class="form-control" data-preview="faviconPreview">
                <input type="text" name="favicon_url" class="form-control" style="margin-top:8px;"
                    placeholder="Or paste favicon icon URL..."
                    value="<?= htmlspecialchars($settings['favicon_url'] ?? '') ?>">
                <?php if (!empty($settings['favicon_url'])): ?>
                    <img id="faviconPreview" src="<?= htmlspecialchars($settings['favicon_url']) ?>"
                        style="max-height:32px; margin-top:10px;">
                <?php else: ?>
                    <img id="faviconPreview" style="max-height:32px; margin-top:10px; display:none;">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 2. WhatsApp API Gateway Settings Card -->
    <div class="admin-card" style="border-left:4px solid #22c55e;">
        <h3
            style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px; color:#15803d; display:flex; align-items:center; gap:8px;">
            <i class="fa-brands fa-whatsapp"></i> 2. WhatsApp API Gateway & Floating Button Settings
        </h3>

        <div class="form-group">
            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                <input type="checkbox" name="whatsapp_notifications_enabled" value="1"
                    <?= ($settings['whatsapp_notifications_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                <strong style="font-size:1rem; color:#15803d;">Enable Automatic WhatsApp Notifications for Orders &
                    Status Updates</strong>
            </label>
        </div>

        <div class="grid-2" style="margin-top:14px;">
            <div class="form-group">
                <label>WhatsApp API Gateway URL *</label>
                <input type="url" name="whatsapp_api_url" class="form-control" required
                    value="<?= htmlspecialchars($settings['whatsapp_api_url'] ?? 'http://13.60.229.151:3000/api/messages/send') ?>"
                    placeholder="http://13.60.229.151:3000/api/messages/send">
            </div>

            <div class="form-group">
                <label>Authorization Bearer API Key *</label>
                <input type="text" name="whatsapp_api_key" class="form-control"
                    value="<?= htmlspecialchars($settings['whatsapp_api_key'] ?? 'wa_api_2c1cc6568c22a1f93affc44330dfd533') ?>"
                    placeholder="wa_api_2c1cc6568c22a1f93affc44330dfd533">
            </div>
        </div>

        <div class="form-group">
            <label>Receiving WhatsApp Phone Number (Admin Number) *</label>
            <input type="text" name="whatsapp_number" class="form-control"
                value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '12125550199') ?>"
                placeholder="12125550199 (Include country code without +)">
            <small style="color:var(--text-muted); display:block; margin-top:4px;">
                This phone number will receive instant WhatsApp order notifications and powers the floating WhatsApp
                chat button at the bottom right of the website.
            </small>
        </div>
    </div>

    <!-- 3. SEO Management for All Pages -->
    <div class="admin-card">
        <h3
            style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
            3. SEO Data CMS (Page Metadata)
        </h3>

        <!-- Home Page SEO -->
        <h4 style="color:var(--primary-color); margin-bottom:14px;"><i class="fa-solid fa-house"></i> Home Page SEO</h4>
        <div class="form-group">
            <label>Home Meta Title</label>
            <input type="text" name="seo_home_title" class="form-control"
                value="<?= htmlspecialchars($home_page['meta_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Home Meta Description</label>
            <textarea name="seo_home_desc" class="form-control"
                style="min-height:70px;"><?= htmlspecialchars($home_page['meta_description'] ?? '') ?></textarea>
        </div>

        <hr style="margin:24px 0; border:0; border-top:1px solid var(--border-color);">

        <!-- Shop Page SEO -->
        <h4 style="color:var(--primary-color); margin-bottom:14px;"><i class="fa-solid fa-bag-shopping"></i> Shop Page
            SEO</h4>
        <div class="form-group">
            <label>Shop Meta Title</label>
            <input type="text" name="seo_shop_title" class="form-control"
                value="<?= htmlspecialchars($shop_page['meta_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Shop Meta Description</label>
            <textarea name="seo_shop_desc" class="form-control"
                style="min-height:70px;"><?= htmlspecialchars($shop_page['meta_description'] ?? '') ?></textarea>
        </div>

        <hr style="margin:24px 0; border:0; border-top:1px solid var(--border-color);">

        <!-- Checkout Page SEO -->
        <h4 style="color:var(--primary-color); margin-bottom:14px;"><i class="fa-solid fa-credit-card"></i> Checkout
            Page SEO</h4>
        <div class="form-group">
            <label>Checkout Meta Title</label>
            <input type="text" name="seo_checkout_title" class="form-control"
                value="<?= htmlspecialchars($checkout_page['meta_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Checkout Meta Description</label>
            <textarea name="seo_checkout_desc" class="form-control"
                style="min-height:70px;"><?= htmlspecialchars($checkout_page['meta_description'] ?? '') ?></textarea>
        </div>

        <hr style="margin:24px 0; border:0; border-top:1px solid var(--border-color);">

        <!-- Contact Page SEO -->
        <h4 style="color:var(--primary-color); margin-bottom:14px;"><i class="fa-solid fa-phone"></i> Contact Us Page
            SEO</h4>
        <div class="form-group">
            <label>Contact Meta Title</label>
            <input type="text" name="seo_contact_title" class="form-control"
                value="<?= htmlspecialchars($contact_page['meta_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Contact Meta Description</label>
            <textarea name="seo_contact_desc" class="form-control"
                style="min-height:70px;"><?= htmlspecialchars($contact_page['meta_description'] ?? '') ?></textarea>
        </div>

        <hr style="margin:24px 0; border:0; border-top:1px solid var(--border-color);">

        <!-- Privacy Policy SEO -->
        <h4 style="color:var(--primary-color); margin-bottom:14px;"><i class="fa-solid fa-scroll"></i> Privacy Policy
            Page SEO</h4>
        <div class="form-group">
            <label>Privacy Meta Title</label>
            <input type="text" name="seo_privacy_title" class="form-control"
                value="<?= htmlspecialchars($privacy_page['meta_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Privacy Meta Description</label>
            <textarea name="seo_privacy_desc" class="form-control"
                style="min-height:70px;"><?= htmlspecialchars($privacy_page['meta_description'] ?? '') ?></textarea>
        </div>
    </div>

    <button type="submit" class="btn-primary" style="padding:16px 36px; font-size:1.1rem;">
        Save Settings <i class="fa-solid fa-floppy-disk"></i>
    </button>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>