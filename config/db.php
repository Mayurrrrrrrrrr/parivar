<?php
/**
 * डेटाबेस कनेक्शन — परिवार पोर्टल
 */
$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $env[trim($parts[0])] = trim($parts[1], "\"' ");
        }
    }
}

define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_NAME', $env['DB_NAME'] ?? 'parivar');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // API responses standard format
    header('Content-Type: application/json');
    die(json_encode(['safalta' => false, 'sandesh' => 'डेटाबेस कनेक्शन विफल: ' . $e->getMessage()]));
}
