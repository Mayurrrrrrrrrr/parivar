<?php
/**
 * डेटाबेस कनेक्शन — PDO का उपयोग करते हुए
 */

$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
    define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
    define('DB_NAME', $env['DB_NAME'] ?? 'parivar');
    define('DB_USER', $env['DB_USER'] ?? 'root');
    define('DB_PASS', $env['DB_PASS'] ?? '');
    define('APP_VERSION', $env['APP_VERSION'] ?? '1.0.0');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'parivar');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('APP_VERSION', '1.0.0');
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true,
        ]
    );
} catch (PDOException $e) {
    error_log('DB Connection failed: ' . $e->getMessage());
    die('डेटाबेस कनेक्शन विफल रहा।');
}
