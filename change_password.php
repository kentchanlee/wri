<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$error = null; $success = null;
$forced = !empty($_GET['forced']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?")
            ->execute([$newHash, $user['id']]);
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?pwchanged=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Change Password — Cedar Wellness Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>try{const m=localStorage.getItem('cwh-theme')||'light';document.documentElement.dataset.theme=m==='system'?(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):m;document.documentElement.dataset.themeMode=m}catch(e){document.documentElement.dataset.theme='light'}</script>
<link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="auth-shell">
  <div class="auth-card">
    <img class="brand-mark" src="assets/img/logo.png" alt="Cedar Wellness Hub logo">
    <div class="auth-eyebrow">Secure Account</div>
    <h1>Change Password</h1>
    <p class="auth-sub"><?= $forced ? 'For security, please set a new password before continuing.' : 'Update your account password.' ?></p>

    <?php if ($error): ?><div class="alert alert-error">⚠ <?= e($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="field">
        <label>Current Password</label>
        <input type="password" name="current_password" required>
      </div>
      <div class="field">
        <label>New Password</label>
        <input type="password" name="new_password" minlength="8" required>
      </div>
      <div class="field">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" minlength="8" required>
      </div>
      <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
    <?php if (!$forced): ?>
      <div class="demo-accounts"><a href="<?= e(dashboard_url_for($_SESSION['role'])) ?>">&larr; Back to dashboard</a></div>
    <?php endif; ?>
  </div>
</div>
</body></html>
