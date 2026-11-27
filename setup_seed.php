<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Creates default Superadmin, HR, and Employee demo accounts with properly
 * hashed passwords. Run this once in your browser after importing
 * database/cedar_wellness.sql, then delete it (it deletes itself on success).
 */
require_once __DIR__ . '/config/database.php';

$defaultPassword = 'Cedar@123';
$hash = password_hash($defaultPassword, PASSWORD_BCRYPT);

$created = [];
$errors  = [];

try {
    $pdo->beginTransaction();

    // Superadmin
    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $chk->execute(['superadmin']);
    if (!$chk->fetch()) {
        $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'superadmin')")
            ->execute(['superadmin', 'superadmin@cedarwellness.test', $hash]);
        $created[] = 'superadmin';
    }

    // HR
    $chk->execute(['hr01']);
    if (!$chk->fetch()) {
        $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'hr')")
            ->execute(['hr01', 'hr01@cedarwellness.test', $hash]);
        $created[] = 'hr01';
    }

    // Sample employee (user + full HRIS employee profile), logs in via Employee ID or email
    $employeeCode = 'CWH-2026-00001';
    $employeeEmail = 'maria.santos@cedarwellness.test';
    $chk->execute([$employeeCode]);
    if (!$chk->fetch()) {
        $pdo->prepare("INSERT INTO users (username, email, password_hash, role, must_change_password) VALUES (?, ?, ?, 'employee', 1)")
            ->execute([$employeeCode, $employeeEmail, $hash]);
        $userId = $pdo->lastInsertId();

        $qr = 'CWH-QR-' . strtoupper(bin2hex(random_bytes(4)));
        $pdo->prepare("INSERT INTO employees
            (user_id, employee_code, first_name, middle_name, last_name, photo, date_of_birth, gender, civil_status, nationality,
             email, contact_number, complete_address, emergency_contact_name, emergency_contact_number,
             branch_id, department, position, employment_type, employment_status, hire_date, qr_code_value)
            VALUES (?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?, ?,?,?,?,?,?,?)")
            ->execute([
                $userId, $employeeCode, 'Maria', 'Reyes', 'Santos', null, '1996-04-12', 'Female', 'Single', 'Filipino',
                $employeeEmail, '0917-123-4567', 'Blk 5 Lot 12, Brgy. Poblacion, Indang, Cavite', 'Juan Santos', '0917-765-4321',
                1, 'Wellness Services', 'Massage Therapist', 'Regular', 'Active', date('Y-m-d'), $qr
            ]);
        $created[] = "$employeeCode (employee profile: Maria Santos)";
    }

    $pdo->commit();
} catch (Exception $ex) {
    $pdo->rollBack();
    $errors[] = $ex->getMessage();
}

$selfDeleted = false;
if (empty($errors)) {
    $selfDeleted = @unlink(__FILE__);
}
function e2($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Seed Setup — Cedar Wellness Hub</title>
<link rel="stylesheet" href="assets/css/style.css"></head>
<body style="padding:40px;max-width:640px;margin:0 auto;font-family:'Work Sans',sans-serif;">
  <h1>Cedar Wellness Hub — Setup</h1>
  <?php if ($errors): ?>
    <div class="alert alert-error">Error: <?= e2(implode(', ', $errors)) ?></div>
  <?php else: ?>
    <div class="alert alert-success">
      Setup complete. <?= $created ? 'Created: ' . e2(implode(', ', $created)) : 'Accounts already existed — nothing new created.' ?>
    </div>
    <p><strong>Default password for all seeded accounts:</strong> <code>Cedar@123</code></p>
    <p class="muted">Employee can log in with Employee ID <code>CWH-2026-00001</code> OR email <code>maria.santos@cedarwellness.test</code>.</p>
    <p class="muted"><?= $selfDeleted ? 'This setup file has deleted itself for security.' : 'Please delete setup_seed.php manually now for security.' ?></p>
  <?php endif; ?>
  <a class="btn btn-primary" href="index.php" style="display:inline-block;width:auto;">Go to Login</a>
</body></html>
