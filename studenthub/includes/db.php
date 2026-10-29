<?php
// Practical 8: PDO (prepared statements) + MySQLi connections
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $mysqli->set_charset('utf8mb4');
} catch (Throwable $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    if (!empty($GLOBALS['IS_API'])) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Database connection failed.']); exit; }
    die('<h2>Database connection failed.</h2><p>Start MySQL in XAMPP and import <code>database/studenthub.sql</code>.</p>');
}
