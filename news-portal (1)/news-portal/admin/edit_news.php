<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$page_title = 'Edit News Article';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $summary     = trim($_POST['summary'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $category_id = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
        $status      = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
        $removeImage = !empty($_POST['remove_image']);

        if ($title === '' || $content === '') {
            $error = 'Title and content are required.';
        } else {
            [$newImage, $uploadError] = handle_image_upload($_FILES['image'] ?? []);
            if ($uploadError) {
                $error = $uploadError;
            } else {
                $imageToStore = $article['image'];

                if ($newImage) {
                    if ($article['image'] && file_exists(UPLOAD_DIR . $article['image'])) {
                        unlink(UPLOAD_DIR . $article['image']);
                    }
                    $imageToStore = $newImage;
                } elseif ($removeImage && $article['image']) {
                    if (file_exists(UPLOAD_DIR . $article['image'])) {
                        unlink(UPLOAD_DIR . $article['image']);
                    }
                    $imageToStore = null;
                }

                // Only regenerate the slug if the title actually changed.
                $slug = $article['title'] === $title ? $article['slug'] : unique_slug($pdo, $title, $id);

                $upd = $pdo->prepare("UPDATE news
                                      SET title = ?, slug = ?, summary = ?, content = ?, image = ?, category_id = ?, status = ?
                                      WHERE id = ?");
                $upd->execute([$title, $slug, $summary ?: null, $content, $imageToStore, $category_id, $status, $id]);

                redirect(BASE_URL . '/admin/dashboard.php');
            }
        }

        // Keep the form populated with attempted values on error.
        $article = array_merge($article, [
            'title' => $title, 'summary' => $summary, 'content' => $content,
            'category_id' => $category_id, 'status' => $status,
        ]);
    }
}

$categories = get_categories($pdo);
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1>Edit News Article</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" style="max-width:700px;">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= $article['id'] ?>">

    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required value="<?= e($article['title']) ?>">
    </div>

    <div class="form-group">
        <label for="summary">Short Summary</label>
        <input type="text" id="summary" name="summary" maxlength="500" value="<?= e($article['summary']) ?>">
    </div>

    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">— None —</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string) $article['category_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($article['image']): ?>
        <div class="form-group">
            <label>Current Image</label>
            <img src="<?= UPLOAD_URL . e($article['image']) ?>" alt="" style="max-width:200px;border-radius:6px;display:block;margin-bottom:8px;">
            <label style="font-weight:400;"><input type="checkbox" name="remove_image" value="1"> Remove this image</label>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="image">Replace Image (optional)</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <div class="form-group">
        <label for="content">Content</label>
        <textarea id="content" name="content" required><?= e($article['content']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <button type="submit" class="btn">Save Changes</button>
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary" style="margin-left:8px;">Cancel</a>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
