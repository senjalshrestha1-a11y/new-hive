<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$page_title = 'Dashboard';

$total      = (int) $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$published  = (int) $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
$drafts     = (int) $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'draft'")->fetchColumn();
$totalViews = (int) $pdo->query("SELECT COALESCE(SUM(views),0) FROM news")->fetchColumn();

$search = trim($_GET['q'] ?? '');
$where  = '';
$params = [];
if ($search !== '') {
    $where = "WHERE title LIKE :q";
    $params[':q'] = '%' . $search . '%';
}

$sql = "SELECT news.*, categories.name AS category_name
        FROM news
        LEFT JOIN categories ON categories.id = news.category_id
        $where
        ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<h1>Dashboard</h1>

<div class="stats-row">
    <div class="stat-card"><div class="stat-num"><?= $total ?></div><div class="stat-label">Total Articles</div></div>
    <div class="stat-card"><div class="stat-num"><?= $published ?></div><div class="stat-label">Published</div></div>
    <div class="stat-card"><div class="stat-num"><?= $drafts ?></div><div class="stat-label">Drafts</div></div>
    <div class="stat-card"><div class="stat-num"><?= $totalViews ?></div><div class="stat-label">Total Views</div></div>
</div>

<div class="top-bar-actions">
    <form method="get" style="display:flex;gap:8px;">
        <input type="text" name="q" placeholder="Search articles..." value="<?= e($search) ?>" style="padding:8px 10px;border:1px solid #ccc;border-radius:5px;">
        <button type="submit" class="btn btn-secondary btn-small">Search</button>
    </form>
    <a href="<?= BASE_URL ?>/admin/add_news.php" class="btn">+ Add News Article</a>
</div>

<?php if (empty($articles)): ?>
    <p>No articles yet. Click "Add News Article" to create your first one.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Views</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= e($a['title']) ?></td>
                    <td><?= e($a['category_name'] ?: '—') ?></td>
                    <td><span class="status-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td><?= (int) $a['views'] ?></td>
                    <td><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
                    <td class="action-links">
                        <a href="<?= BASE_URL ?>/article.php?slug=<?= urlencode($a['slug']) ?>" target="_blank">View</a>
                        <a href="<?= BASE_URL ?>/admin/edit_news.php?id=<?= $a['id'] ?>">Edit</a>
                        <form method="post" action="<?= BASE_URL ?>/admin/delete_news.php" onsubmit="return confirm('Delete this article? This cannot be undone.');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <button type="submit" style="background:none;border:none;color:#b3261e;cursor:pointer;font:inherit;padding:0;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
