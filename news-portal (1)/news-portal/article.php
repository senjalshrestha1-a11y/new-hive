<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT news.*, categories.name AS category_name
                        FROM news
                        LEFT JOIN categories ON categories.id = news.category_id
                        WHERE slug = ? AND status = 'published'
                        LIMIT 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $page_title = 'Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><p>Sorry, that article could not be found.</p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Bump the view counter (simple, no dedup — fine for a small project)
$pdo->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

$page_title = $article['title'];
require_once __DIR__ . '/includes/header.php';
?>

<article>
    <div class="article-header">
        <?php if ($article['category_name']): ?>
            <span class="category-tag"><?= e($article['category_name']) ?></span>
        <?php endif; ?>
        <h1><?= e($article['title']) ?></h1>
        <div class="article-meta">
            Published <?= date('F j, Y \a\t g:i a', strtotime($article['created_at'])) ?>
            &middot; <?= (int) $article['views'] + 1 ?> views
        </div>
    </div>

    <?php if ($article['image']): ?>
        <img class="article-image" src="<?= UPLOAD_URL . e($article['image']) ?>" alt="<?= e($article['title']) ?>">
    <?php endif; ?>

    <div class="article-body">
        <?= nl2br(e($article['content'])) ?>
    </div>
</article>

<p style="margin-top:30px;"><a href="<?= BASE_URL ?>/index.php">&larr; Back to all news</a></p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
