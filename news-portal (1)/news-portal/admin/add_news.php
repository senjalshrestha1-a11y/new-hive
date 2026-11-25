<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$page_title = 'Add News Article';
$error = '';

$title      = '';
$summary    = '';
$content    = '';
$category_id = '';
$status     = 'published';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $summary     = trim($_POST['summary'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $category_id = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
        $status      = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

        if ($title === '' || $content === '') {
            $error = 'Title and content are required.';
        } else {
            [$imageFilename, $uploadError] = handle_image_upload($_FILES['image'] ?? []);
            if ($uploadError) {
                $error = $uploadError;
            } else {
                $slug = unique_slug($pdo, $title);
                $stmt = $pdo->prepare("INSERT INTO news (title, slug, summary, content, image, category_id, admin_id, status)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $title, $slug, $summary ?: null, $content, $imageFilename,
                    $category_id, $_SESSION['admin_id'], $status
                ]);
                redirect(BASE_URL . '/admin/dashboard.php');
            }
        }
    }
}

$categories = get_categories($pdo);
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1>Add News Article</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" style="max-width:700px;">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required value="<?= e($title) ?>">
    </div>

    <div class="form-group">
        <label for="summary">Short Summary (shown on the homepage card)</label>
        <input type="text" id="summary" name="summary" maxlength="500" value="<?= e($summary) ?>">
    </div>

    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">— None —</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string) $category_id === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="image">Featured Image (optional)</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <div class="form-group">
        <label for="content">Content</label>
        <textarea id="content" name="content" required><?= e($content) ?></textarea>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published (visible to everyone)</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft (hidden from the public)</option>
        </select>
    </div>

    <button type="submit" class="btn">Publish Article</button>
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary" style="margin-left:8px;">Cancel</a>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
