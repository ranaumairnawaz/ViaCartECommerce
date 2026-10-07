<?php
// privacy-policy.php - Privacy Policy Page

$current_page_key = 'privacy';
require_once __DIR__ . '/includes/db.php';

$privacy_data = get_page_data('privacy');

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:50px; padding-bottom:100px;">
    <div style="max-width:840px; margin:0 auto;" class="surface-box">
        <h1 style="font-size:2.4rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:16px; color:var(--secondary-color);">
            <?= htmlspecialchars($privacy_data['title'] ?: 'Privacy Policy') ?>
        </h1>

        <div style="font-size:1.05rem; line-height:1.8; color:var(--text-main);">
            <?= $privacy_data['content'] ?: '<p>Privacy policy content will appear here once updated in the CMS.</p>' ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
