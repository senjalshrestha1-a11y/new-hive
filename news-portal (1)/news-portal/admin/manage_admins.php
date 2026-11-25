<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$page_title = 'Manage Admins';
$error = '';
$success = '';

// ---- Handle: add new admin ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Username and password are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hash, $fullName ?: null]);
                $success = 'Admin account "' . $username . '" created.';
            } catch (PDOException $e) {
                $error = str_contains($e->getMessage(), 'Duplicate')
                    ? 'That username is already taken.'
                    : 'Could not create admin: ' . $e->getMessage();
            }
        }
    }
}

// ---- Handle: delete admin ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $deleteId = (int) ($_POST['id'] ?? 0);
        $totalAdmins = (int) $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

        if ($deleteId === (int) $_SESSION['admin_id']) {
            $error = "You can't delete your own account while logged in as it.";
        } elseif ($totalAdmins <= 1) {
            $error = 'You cannot delete the only remaining admin account.';
        } else {
            $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$deleteId]);
            $success = 'Admin account removed.';
        }
    }
}

$admins = $pdo->query("SELECT id, username, full_name, created_at FROM admins ORDER BY created_at ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<h1>Manage Admins</h1>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<h3 style="margin-bottom:10px;">Current Admins</h3>
<table class="admin-table" style="margin-bottom:30px;">
    <thead>
        <tr><th>Username</th><th>Full Name</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= e($a['username']) ?><?= (int) $a['id'] === (int) $_SESSION['admin_id'] ? ' (you)' : '' ?></td>
                <td><?= e($a['full_name'] ?: '—') ?></td>
                <td><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
                <td>
                    <?php if ((int) $a['id'] !== (int) $_SESSION['admin_id']): ?>
                        <form method="post" onsubmit="return confirm('Remove admin &quot;<?= e($a['username']) ?>&quot;? This cannot be undone.');" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <button type="submit" style="background:none;border:none;color:#b3261e;cursor:pointer;font:inherit;padding:0;">Remove</button>
                        </form>
                    <?php else: ?>
                        <span style="color:#999;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3 style="margin-bottom:10px;">Add a New Admin</h3>
<form method="post" style="max-width:420px;">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="full_name">Full Name (optional)</label>
        <input type="text" id="full_name" name="full_name">
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

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
