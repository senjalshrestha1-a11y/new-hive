<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error = '';

// Very simple rate limiting to slow down brute-force attempts.
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['login_last_try'] = $_SESSION['login_last_try'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['login_last_try']) < 60) {
        $error = 'Too many failed attempts. Please wait a minute and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Success: reset attempts, regenerate session id to prevent fixation.
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            unset($_SESSION['login_attempts'], $_SESSION['login_last_try']);
            redirect(BASE_URL . '/admin/dashboard.php');
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['login_last_try'] = time();
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="form-card">
    <h1>Admin Login</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn" style="width:100%;">Log In</button>
    </form>

    <p style="text-align:center;margin-top:16px;font-size:14px;">
        <a href="<?= BASE_URL ?>/index.php">&larr; Back to the news portal</a>
    </p>
</div>

</body>
</html>
