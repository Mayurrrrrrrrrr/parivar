<?php
/**
 * प्रमाणीकरण (Authentication) — सत्र और भूमिका प्रबंधन
 */
session_start();

// PHP Hardening
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// CSRF Token Initialization
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * जांचें कि क्या उपयोगकर्ता लॉग इन है
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * अगर लॉग इन नहीं है तो लॉगिन पेज पर भेजें
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit;
    }
}

/**
 * जांचें कि क्या उपयोगकर्ता 'मुख्य' (Admin) है
 */
function isMukhya() {
    return isset($_SESSION['bhumika']) && $_SESSION['bhumika'] === 'mukhya';
}

/**
 * अगर मुख्य नहीं है तो डैशबोर्ड पर भेजें
 */
function requireMukhya() {
    requireLogin();
    if (!isMukhya()) {
        header('Location: /pages/dashboard.php?error=adhikar_nahi');
        exit;
    }
}

/**
 * वर्तमान परिवार आईडी प्राप्त करें
 */
function getParivarId() {
    return $_SESSION['parivar_id'] ?? null;
}

/**
 * वर्तमान उपयोगकर्ता नाम प्राप्त करें
 */
function getUserName() {
    return $_SESSION['user_naam'] ?? 'अतिथि';
}

/**
 * CSRF टोकन प्राप्त करें
 */
function csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * CSRF टोकन सत्यापित करें
 */
function csrf_verify() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('CSRF सत्यापन विफल रहा।');
    }
}
