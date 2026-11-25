<?php
/**
 * ONE-TIME SETUP SCRIPT
 * -----------------------------------------------------------------
 * Run this once in your browser (e.g. http://localhost/news-portal/setup/create_admin.php)
 * AFTER importing database/schema.sql, to create your first admin account.
 *
 * DELETE THIS FILE (or the whole /setup folder) once you're done —
 * it should never stay on a live/shared server.
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$done = false;

// Refuse to run if an admin already exists, unless explicitly confirmed.
$existingCount = (int) $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($existingCount > 0) {
        $message = 'An admin account already exists — this setup page only works once. Log in and use "Manage Admins" to add more.';
    } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'Username and password are required.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $fullName ?: null]);
            $done = true;
        } catch (PDOException $e) {
            $message = str_contains($e->getMessage(), 'Duplicate')
                ? 'That username is already taken.'
                : 'Could not create admin: ' . $e->getMessage();
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Admin Account</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="form-card">
    <h1>Create Admin Account</h1>

    <?php if ($done): ?>
        <div class="alert alert-success">
            Admin account created! You can now
            <a href="<?= BASE_URL ?>/admin/login.php">log in</a>.
        </div>
        <p style="font-size:13px;color:#a1690a;">
            Please delete the <code>/setup</code> folder now — leaving it in place
            would let anyone create new admin accounts.
        </p>
    <?php else: ?>
        <?php if ($existingCount > 0): ?>
            <div class="alert alert-error">
                An admin account already exists, so this one-time setup page is now locked.
                <a href="<?= BASE_URL ?>/admin/login.php">Log in</a> and use
                <strong>Manage Admins</strong> in the dashboard to add more admin accounts —
                or delete this <code>/setup</code> folder entirely, since you no longer need it.
            </div>
        <?php else: ?>

        <?php if ($message): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="full_name">Full Name (optional)</label>
                <input type="text" id="full_name" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password (min 8 characters)</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" required minlength="8">
            </div>
            <button type="submit" class="btn">Create Admin Account</button>
        </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
