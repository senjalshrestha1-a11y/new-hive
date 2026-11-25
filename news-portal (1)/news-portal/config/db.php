<?php
/**
 * Database connection (PDO)
 * Edit these four values to match your local MySQL setup.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'news_portal';
$DB_USER = 'root';
$DB_PASS = '';   // set your MySQL root password if you have one

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
