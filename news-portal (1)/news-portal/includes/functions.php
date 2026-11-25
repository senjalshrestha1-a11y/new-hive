<?php
/**
 * Shared helper functions.
 */

// ---- Security / output helpers ----

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

// ---- Auth helpers (admin side) ----

function is_logged_in() {
    return !empty($_SESSION['admin_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect(BASE_URL . '/admin/login.php');
    }
}

// ---- Slug helper ----

function make_slug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'post-' . time();
}

function unique_slug(PDO $pdo, $title, $ignore_id = null) {
    $base = make_slug($title);
    $slug = $base;
    $i = 1;

    $sql = "SELECT COUNT(*) FROM news WHERE slug = :slug" . ($ignore_id ? " AND id != :id" : "");
    while (true) {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignore_id) {
            $stmt->bindValue(':id', $ignore_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }
        $i++;
        $slug = $base . '-' . $i;
    }
}

// ---- Image upload helper ----

function handle_image_upload($file) {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null]; // no file chosen, that's fine
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload error (code ' . $file['error'] . ').'];
    }

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!array_key_exists($ext, $allowed)) {
        return [null, 'Only JPG, PNG, GIF, or WEBP images are allowed.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return [null, 'Image must be smaller than 5MB.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if ($mime !== $allowed[$ext]) {
        return [null, 'File content does not match its extension.'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return [null, 'Failed to save the uploaded file.'];
    }

    return [$filename, null];
}

// ---- CSRF helpers ----

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

// ---- Data helpers ----

function get_categories(PDO $pdo) {
    return $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
}

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}
