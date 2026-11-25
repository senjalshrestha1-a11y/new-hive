<?php
// This file assumes config.php + functions.php are already loaded
// and require_login() has already been called by the including page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' - Admin' : 'Admin' ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="admin-body">

<div class="admin-header">
    <div class="container">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="admin-logo"><?= e(SITE_NAME) ?> &middot; Admin</a>
        <div class="admin-nav">
            <span>Hi, <?= e($_SESSION['admin_username']) ?></span>
            <a href="<?= BASE_URL ?>/index.php" target="_blank">View Site</a>
            <a href="<?= BASE_URL ?>/admin/change_password.php">Change Password</a>
            <a href="<?= BASE_URL ?>/admin/manage_admins.php">Manage Admins</a>
            <a href="<?= BASE_URL ?>/admin/logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="container admin-content">
