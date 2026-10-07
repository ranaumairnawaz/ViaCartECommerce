<?php
// apply-coupon.php - AJAX Endpoint: Validates a coupon code against the cart subtotal

require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

$code = trim($_POST['code'] ?? '');
$subtotal = floatval($_POST['subtotal'] ?? 0);

$result = validate_coupon($code, $subtotal);
echo json_encode($result);
