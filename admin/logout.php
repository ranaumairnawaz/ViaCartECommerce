<?php
// admin/logout.php - Admin Logout Script

require_once __DIR__ . '/../includes/auth.php';
logout_admin();
header('Location: login');
exit;
