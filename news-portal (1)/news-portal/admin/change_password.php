<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$page_title = 'Change Password';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($current, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hash, $admin['id']]);
            $success = 'Password updated successfully.';
        }
    }
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<h1>Change Password</h1>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<form method="post" style="max-width:420px;">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>
    </div>
    <div class="form-group">
        <label for="new_password">New Password (min 8 characters)</label>
        <input type="password" id="new_password" name="new_password" required minlength="8">
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
    </div>
    <button type="submit" class="btn">Update Password</button>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
