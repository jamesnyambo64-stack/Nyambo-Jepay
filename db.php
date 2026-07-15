<?php
session_start();

// Configure DB credentials or use environment variables in production
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'notifications';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    error_log($e->getMessage());
    exit('Database connection error');
}

// CSRF helpers
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_check($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}

// Simple sanitize
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
