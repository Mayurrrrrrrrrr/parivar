<?php
/**
 * API — कार्यक्रम (Events)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');
requireLogin();

$action = $_GET['action'] ?? '';
$parivar_id = currentParivarId();

switch ($action) {
    case 'list':
        $stmt = $pdo->prepare("SELECT k.*, v.pratham_naam FROM karyakram k LEFT JOIN vyakti v ON k.vyakti_id = v.id WHERE k.parivar_id = ? ORDER BY k.tithi_gregorian");
        $stmt->execute([$parivar_id]);
        echo json_encode(['safalta' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'upcoming':
        // Next 7 days
        $stmt = $pdo->prepare("
            SELECT k.*, v.pratham_naam 
            FROM karyakram k 
            LEFT JOIN vyakti v ON k.vyakti_id = v.id 
            WHERE k.parivar_id = ? 
            AND k.tithi_gregorian BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY k.tithi_gregorian
        ");
        $stmt->execute([$parivar_id]);
        echo json_encode(['safalta' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'aaj':
        $stmt = $pdo->prepare("SELECT k.*, v.pratham_naam FROM karyakram k LEFT JOIN vyakti v ON k.vyakti_id = v.id WHERE k.parivar_id = ? AND k.tithi_gregorian = CURDATE()");
        $stmt->execute([$parivar_id]);
        echo json_encode(['safalta' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'banao':
        csrf_verify();
        $shirshak = $_POST['shirshak'] ?? '';
        $prakar = $_POST['prakar'] ?? 'any';
        $gregorian = $_POST['tithi_gregorian'] ?? '';
        $vs = $_POST['tithi_vs'] ?? '';
        $punravrutti_prakar = $_POST['punravrutti_prakar'] ?? 'gregorian_varshik';
        $vyakti_id = !empty($_POST['vyakti_id']) ? $_POST['vyakti_id'] : null;

        $stmt = $pdo->prepare("INSERT INTO karyakram (parivar_id, vyakti_id, shirshak, prakar, tithi_gregorian, tithi_vs, punravrutti_prakar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$parivar_id, $vyakti_id, $shirshak, $prakar, $gregorian, $vs, $punravrutti_prakar]);
        
        header('Location: ../pages/karyakram.php?success=1');
        exit;

    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
