<?php
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect(dashboard_url_for($_SESSION['role']));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both your Employee ID / Email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Incorrect Employee ID / Email or password.';
        } elseif ($user['status'] !== 'active') {
            $error = 'This account has been disabled. Contact your administrator.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'employee') {
                $es = $pdo->prepare("SELECT * FROM employees WHERE user_id = ? LIMIT 1");
                $es->execute([$user['id']]);
                $emp = $es->fetch();
                if ($emp) {
                    $_SESSION['employee_id']   = $emp['id'];
                    $_SESSION['display_name']  = $emp['first_name'] . ' ' . $emp['last_name'];
                }
            } else {
                $_SESSION['display_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ucfirst($user['role']) . ' — ' . $user['username'];
            }

            if ((int)$user['must_change_password'] === 1) {
                redirect(BASE_URL . '/change_password.php?forced=1');
            }
            redirect(dashboard_url_for($user['role']));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cedar Wellness Hub — Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>try{const m=localStorage.getItem('cwh-theme')||'light';document.documentElement.dataset.theme=m==='system'?(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):m;document.documentElement.dataset.themeMode=m}catch(e){document.documentElement.dataset.theme='light'}</script>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-shell">
  <div class="auth-card">
    <img class="brand-mark" src="assets/img/logo.png" alt="Cedar Wellness Hub logo">
    <h1>Cedar Wellness Hub</h1>
    <p class="auth-sub">Employee Management &amp; Attendance System</p>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠ <?= e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['loggedout'])): ?>
      <div class="alert alert-success">You've been signed out safely.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['pwchanged'])): ?>
      <div class="alert alert-success">Password updated. Please sign in again.</div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="field">
        <label for="username">Employee ID or Email</label>
        <input type="text" id="username" name="username" placeholder="e.g. CWH-2026-00001 or you@email.com" required autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary">Sign In</button>
    </form>

  </div>
</div>
</body>
</html>
