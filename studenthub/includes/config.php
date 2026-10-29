<?php
// StudentHub configuration (Practical 8) - XAMPP defaults
define('DB_HOST', 'localhost');
define('DB_NAME', 'studenthub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SESSION_TIMEOUT', 900);          // 15 minutes
define('REMEMBER_DAYS', 30);
define('UPLOAD_DIR', __DIR__ . '/../uploads/posters/');
define('UPLOAD_URL', 'uploads/posters/');
define('MAX_UPLOAD', 2 * 1024 * 1024);   // 2 MB
define('BASE_PATH', dirname(__DIR__));
