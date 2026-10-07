<?php
// admin/pages.php - CMS for Home Hero, Contact Page, & Privacy Policy Content

$admin_page = 'pages';
require_once __DIR__ . '/admin_header.php';

$db = get_db();
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';

    if ($section === 'home_hero') {
        $hero_title = trim($_POST['hero_title'] ?? '');
        $hero_subtitle = trim($_POST['hero_subtitle'] ?? '');
        $hero_cta_text = trim($_POST['hero_cta_text'] ?? '');
        $hero_cta_url = trim($_POST['hero_cta_url'] ?? '');

        // Handle image upload or text URL
        $hero_img_upload = upload_image('hero_image_file');
        $hero_image = $hero_img_upload ?: trim($_POST['hero_image_url'] ?? '');

        $stmt = $db->prepare("
            UPDATE pages SET hero_title = ?, hero_subtitle = ?, hero_cta_text = ?, hero_cta_url = ?" .
            ($hero_image ? ", hero_image = ?" : "") . " WHERE page_key = 'home'
        ");

        $params = [$hero_title, $hero_subtitle, $hero_cta_text, $hero_cta_url];
        if ($hero_image) {
            $params[] = $hero_image;
        }
        $stmt->execute($params);
        $success_msg = 'Home Hero Section updated successfully!';

    } elseif ($section === 'contact_info') {
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $contact_address = trim($_POST['contact_address'] ?? '');
        $contact_hours = trim($_POST['contact_hours'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $stmt = $db->prepare("
            UPDATE pages SET contact_email = ?, contact_phone = ?, contact_address = ?, contact_hours = ?, content = ? WHERE page_key = 'contact'
        ");
        $stmt->execute([$contact_email, $contact_phone, $contact_address, $contact_hours, $content]);
        $success_msg = 'Contact Page details updated successfully!';

    } elseif ($section === 'privacy_content') {
        $content = trim($_POST['content'] ?? '');

        $stmt = $db->prepare("UPDATE pages SET content = ? WHERE page_key = 'privacy'");
        $stmt->execute([$content]);
        $success_msg = 'Privacy Policy document content updated successfully!';
    }
}

$home_data = get_page_data('home');
$contact_data = get_page_data('contact');
$privacy_data = get_page_data('privacy');
?>

<div class="admin-header">
    <div>
        <h1 style="font-size:2rem; font-weight:800;">Page Content CMS</h1>
        <p style="color:var(--text-muted);">Manage Home Page Hero Banner, Contact Details, and Privacy Policy Document.</p>
    </div>
</div>

<?php if (!empty($success_msg)): ?>
    <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<!-- 1. Home Hero Section CMS Form -->
<form method="POST" action="pages" enctype="multipart/form-data" class="admin-card">
    <input type="hidden" name="section" value="home_hero">
    <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
        1. Home Page Hero Banner CMS
    </h3>

    <div class="form-group">
        <label>Hero Title (Main Heading)</label>
        <input type="text" name="hero_title" class="form-control" required value="<?= htmlspecialchars($home_data['hero_title'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Hero Description</label>
        <textarea name="hero_subtitle" class="form-control" style="min-height:80px;"><?= htmlspecialchars($home_data['hero_subtitle'] ?? '') ?></textarea>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>CTA Button Text</label>
            <input type="text" name="hero_cta_text" class="form-control" value="<?= htmlspecialchars($home_data['hero_cta_text'] ?? 'Shop Collection') ?>">
        </div>
        <div class="form-group">
            <label>CTA Target Link URL</label>
            <input type="text" name="hero_cta_url" class="form-control" value="<?= htmlspecialchars($home_data['hero_cta_url'] ?? 'shop') ?>">
        </div>
    </div>

    <div class="form-group">
        <label>Right-Side Hero Image (Upload or URL)</label>
        <input type="file" name="hero_image_file" class="form-control" data-preview="heroPreview">
        <input type="text" name="hero_image_url" class="form-control" style="margin-top:8px;" placeholder="Or paste image URL..." value="<?= htmlspecialchars($home_data['hero_image'] ?? '') ?>">
        <?php if (!empty($home_data['hero_image'])): ?>
            <img id="heroPreview" src="<?= htmlspecialchars($home_data['hero_image']) ?>" style="max-height:160px; margin-top:12px; border-radius:12px; border:1px solid var(--border-color);">
        <?php else: ?>
            <img id="heroPreview" style="max-height:160px; margin-top:12px; display:none; border-radius:12px;">
        <?php endif; ?>
    </div>

    <button type="submit" class="btn-primary">Update Home Hero Section <i class="fa-solid fa-floppy-disk"></i></button>
</form>

<!-- 2. Contact Page Details CMS Form -->
<form method="POST" action="pages" class="admin-card">
    <input type="hidden" name="section" value="contact_info">
    <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
        2. Contact Us Page Information CMS
    </h3>

    <div class="grid-2">
        <div class="form-group">
            <label>Support Email Address</label>
            <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($contact_data['contact_email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Support Phone Number</label>
            <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($contact_data['contact_phone'] ?? '') ?>">
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Office Address</label>
            <textarea name="contact_address" class="form-control" style="min-height:70px;"><?= htmlspecialchars($contact_data['contact_address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Working Hours</label>
            <input type="text" name="contact_hours" class="form-control" value="<?= htmlspecialchars($contact_data['contact_hours'] ?? '') ?>">
        </div>
    </div>

    <div class="form-group">
        <label>Contact Intro Message / Instructions</label>
        <textarea name="content" class="form-control" style="min-height:90px;"><?= htmlspecialchars($contact_data['content'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn-primary">Update Contact Info <i class="fa-solid fa-floppy-disk"></i></button>
</form>

<!-- 3. Privacy Policy Content CMS Form -->
<form method="POST" action="pages" class="admin-card">
    <input type="hidden" name="section" value="privacy_content">
    <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
        3. Privacy Policy Document Content
    </h3>

    <div class="form-group">
        <label>Privacy Policy Body (HTML allowed)</label>
        <textarea name="content" class="form-control" style="min-height:260px; font-family:monospace;"><?= htmlspecialchars($privacy_data['content'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn-primary">Update Privacy Policy Content <i class="fa-solid fa-floppy-disk"></i></button>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
