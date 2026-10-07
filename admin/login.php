<?php
// admin/login.php - Admin Authentication Page

require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    header('Location: index');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (login_admin($username, $password)) {
        header('Location: index');
        exit;
    } else {
        $error = 'Invalid admin credentials! Please try again.';
    }
}

$settings = get_all_settings();
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'ViaCart';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= htmlspecialchars($site_name) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body style="background:var(--secondary-color); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#ffffff; border-radius:var(--radius-lg); padding:40px; max-width:440px; width:100%; box-shadow:var(--shadow-lg);">
        <div style="text-align:center; margin-bottom:30px;">
            <h1 style="font-size:1.8rem; font-weight:800; color:var(--secondary-color);"><i class="fa-solid fa-bag-shopping"></i> <?= htmlspecialchars($site_name) ?> CMS</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Sign in to access your e-commerce management panel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:0.9rem; font-weight:600;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login">
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="username" class="form-control" required value="admin">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary" style="width:100%; padding:14px; justify-content:center; font-size:1rem; margin-top:10px;">
                Sign In To CMS &rarr;
            </button>
        </form>

        <div style="text-align:center; margin-top:24px; font-size:0.85rem; color:var(--text-muted); border-top:1px solid var(--border-color); padding-top:16px;">
            Default login: <strong>admin</strong> / <strong>admin123</strong>
            <br>
            <a href="../index" style="color:var(--primary-color); display:inline-block; margin-top:8px;">&larr; Return To Main Website</a>
        </div>
    </div>
</body>
</html>
