<?php
/**
 * API — परिवार सेटिंग्स (Family Settings)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');
requireLogin();

$action = $_GET['action'] ?? '';
$parivar_id = currentParivarId();

if (!isMukhya()) {
    echo json_encode(['safalta' => false, 'sandesh' => 'केवल मुखिया ही परिवार की जानकारी बदल सकते हैं।']);
    exit;
}

switch ($action) {
    case 'update':
        csrf_verify();
        $parivar_naam = trim($_POST['parivar_naam'] ?? '');
        
        if (empty($parivar_naam)) {
            header('Location: /parivar/pages/settings.php?error=khaali');
            exit;
        }

        $stmt = $pdo->prepare("UPDATE parivar SET parivar_naam = ? WHERE id = ?");
        $stmt->execute([$parivar_naam, $parivar_id]);
        
        header('Location: /parivar/pages/settings.php?success=upadat_hua');
        exit;

    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
