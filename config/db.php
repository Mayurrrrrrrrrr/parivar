<?php
/**
 * डेटाबेस कनेक्शन — परिवार पोर्टल
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'parivar');
define('DB_USER', 'root');
define('DB_PASS', 'asjhb5465%&55fss'); // updated with server password
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
