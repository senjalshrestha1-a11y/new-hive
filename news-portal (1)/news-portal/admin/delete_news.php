<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? '')) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
$stmt->execute([$id]);
$image = $stmt->fetchColumn();

$pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);

if ($image && file_exists(UPLOAD_DIR . $image)) {
    unlink(UPLOAD_DIR . $image);
}

redirect(BASE_URL . '/admin/dashboard.php');
