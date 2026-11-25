<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' - ' . e(SITE_NAME) : e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?= BASE_URL ?>/index.php" class="logo"><?= e(SITE_NAME) ?></a>

        <form class="search-form" action="<?= BASE_URL ?>/index.php" method="get">
            <input type="text" name="q" placeholder="Search news..." value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>

        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/index.php">Home</a>
            <?php foreach (get_categories($pdo) as $cat): ?>
                <a href="<?= BASE_URL ?>/index.php?category=<?= $cat['id'] ?>"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<main class="container page-content">
