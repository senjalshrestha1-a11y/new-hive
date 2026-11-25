<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Home';

// ---- Filters ----
$search      = trim($_GET['q'] ?? '');
$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$page        = max(1, (int) ($_GET['page'] ?? 1));
$offset      = ($page - 1) * ITEMS_PER_PAGE;

$where  = ["status = 'published'"];
$params = [];

if ($search !== '') {
    $where[] = "(title LIKE :q OR summary LIKE :q OR content LIKE :q)";
    $params[':q'] = '%' . $search . '%';
}
if ($category_id > 0) {
    $where[] = "category_id = :cat";
    $params[':cat'] = $category_id;
}
$whereSql = implode(' AND ', $where);

// Total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / ITEMS_PER_PAGE));

// Fetch page of results
$sql = "SELECT news.*, categories.name AS category_name
        FROM news
        LEFT JOIN categories ON categories.id = news.category_id
        WHERE $whereSql
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', ITEMS_PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($search): ?>
    <h2>Search results for "<?= e($search) ?>"</h2>
<?php elseif ($category_id): ?>
    <?php
        $catStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $catStmt->execute([$category_id]);
        $catName = $catStmt->fetchColumn();
    ?>
    <h2><?= e($catName ?: 'Category') ?></h2>
<?php else: ?>
    <h2>Latest News</h2>
<?php endif; ?>

<?php if (empty($articles)): ?>
    <div class="empty-state">
        <p>No articles found<?= $search ? ' for your search.' : '.' ?></p>
    </div>
<?php else: ?>
    <div class="news-grid">
        <?php foreach ($articles as $a): ?>
            <div class="news-card">
                <a href="<?= BASE_URL ?>/article.php?slug=<?= urlencode($a['slug']) ?>">
                    <img src="<?= $a['image'] ? UPLOAD_URL . e($a['image']) : 'https://placehold.co/400x220?text=' . urlencode(SITE_NAME) ?>" alt="<?= e($a['title']) ?>">
                </a>
                <div class="card-body">
                    <?php if ($a['category_name']): ?>
                        <span class="category-tag"><?= e($a['category_name']) ?></span>
                    <?php endif; ?>
                    <h3><a href="<?= BASE_URL ?>/article.php?slug=<?= urlencode($a['slug']) ?>"><?= e($a['title']) ?></a></h3>
                    <?php if ($a['summary']): ?>
                        <p class="excerpt"><?= e($a['summary']) ?></p>
                    <?php endif; ?>
                    <div class="meta"><?= time_ago($a['created_at']) ?> &middot; <?= (int) $a['views'] ?> views</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php
                    $qs = $_GET;
                    $qs['page'] = $p;
                    $link = '?' . http_build_query($qs);
                ?>
                <?php if ($p == $page): ?>
                    <span class="current"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= e($link) ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
