<?php
/**
 * API — परिवार फ़ीड (Feed) प्रबंधन
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();
$action = $_GET['action'] ?? 'list';
$parivar_id = getParivarId();

if ($action === 'list') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $stmt = $pdo->prepare("SELECT f.*, u.naam as user_naam FROM parivar_feed f 
                           JOIN users u ON f.user_id = u.id 
                           WHERE f.parivar_id = ? ORDER BY f.banaya_at DESC LIMIT ?");
    $stmt->execute([$parivar_id, $limit]);
    $posts = $stmt->fetchAll();
    
    // Format dates for display
    foreach ($posts as &$p) {
        $p['banaya_at'] = formatGregorianHindi($p['banaya_at']);
    }
    
    sendJson($posts);
}

if ($action === 'post') {
    csrf_verify();
    $sandesh = $_POST['sandesh'] ?? '';
    $user_id = $_SESSION['user_id'];
    $photo_url = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = bin2hex(random_bytes(16)) . '.' . $ext;
            $target = __DIR__ . '/../assets/uploads/' . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photo_url = '/assets/uploads/' . $filename;
            }
        }
    }

    if (!empty($sandesh) || !empty($photo_url)) {
        $stmt = $pdo->prepare("INSERT INTO parivar_feed (parivar_id, user_id, sandesh, photo_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$parivar_id, $user_id, $sandesh, $photo_url]);
    }
    
    header('Location: ../pages/parivar_feed.php');
    exit;
}

if ($action === 'delete') {
    $id = $_GET['id'] ?? null;
    $user_id = $_SESSION['user_id'];
    
    // Only owner can delete
    $stmt = $pdo->prepare("DELETE FROM parivar_feed WHERE id = ? AND user_id = ? AND parivar_id = ?");
    $stmt->execute([$id, $user_id, $parivar_id]);
    
    sendJson([], $stmt->rowCount() > 0, $stmt->rowCount() > 0 ? 'हटा दिया गया' : 'हटाने में विफल');
}
