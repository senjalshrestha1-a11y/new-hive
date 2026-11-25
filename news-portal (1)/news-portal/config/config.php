<?php
/**
 * Site-wide configuration.
 */

// Start the session once, everywhere, before any output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'News Hive');   // change to whatever you like
define('BASE_URL', '/news-portal');       // change if your folder name/path differs
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('ITEMS_PER_PAGE', 6);

require_once __DIR__ . '/db.php';
