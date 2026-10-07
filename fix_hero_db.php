<?php
require_once __DIR__ . '/includes/db.php';
$db = get_db();
$stmt = $db->prepare("UPDATE pages SET hero_cta_url = 'checkout' WHERE page_key = 'home'");
$stmt->execute();
echo "Updated hero CTA URL to checkout in DB.";
?>
