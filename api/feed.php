<?php
/**
 * API — परिवार फ़ीड (Feed)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/upload.php';

header('Content-Type: application/json');
requireLogin();

$action = $_GET['action'] ?? '';
$parivar_id = currentParivarId();
$user_id = currentUserId();

switch ($action) {
    case 'list':
        $stmt = $pdo->prepare("SELECT f.*, u.naam as user_naam FROM parivar_feed f JOIN users u ON f.user_id = u.id WHERE f.parivar_id = ? ORDER BY f.banaya_at DESC");
        $stmt->execute([$parivar_id]);
        echo json_encode(['safalta' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'banao':
        csrf_verify();
        $sandesh = trim($_POST['sandesh'] ?? '');
        
        if (empty($sandesh)) {
            header('Location: ../pages/parivar_feed.php?error=khaali');
            exit;
        }

        $photo_url = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo_url = uploadPhoto($_FILES['photo'], 'feed');
        }

        $stmt = $pdo->prepare("INSERT INTO parivar_feed (parivar_id, user_id, sandesh, photo_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$parivar_id, $user_id, $sandesh, $photo_url]);
        
        header('Location: ../pages/parivar_feed.php?success=1');
        exit;

    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
