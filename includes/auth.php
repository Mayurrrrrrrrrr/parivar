<?php
/**
 * प्रमाणीकरण — session management
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isMukhya(): bool {
    return isLoggedIn() && ($_SESSION['bhumika'] ?? '') === 'mukhya';
}

function isSadasy(): bool {
    return isLoggedIn() && ($_SESSION['bhumika'] ?? '') === 'sadasy';
}

function getBasePath() {
    $path = str_replace(['\\', '/pages', '/api', '/includes', '/cron'], ['/', '', '', '', ''], dirname($_SERVER['SCRIPT_NAME']));
    return rtrim($path, '/') . '/';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . getBasePath() . 'index.php?error=login_required');
        exit;
    }
}

function requireMukhya(): void {
    requireLogin();
    if (!isMukhya()) {
        header('Location: ' . getBasePath() . 'pages/dashboard.php?error=adhikar_nahi');
        exit;
    }
}

function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function currentParivarId(): int {
    return (int)($_SESSION['parivar_id'] ?? 0);
}

/**
 * CSRF Protection
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
        die('CSRF token mismatch');
    }
}
