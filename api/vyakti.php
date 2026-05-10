<?php
/**
 * API — व्यक्ति (Persons)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/upload.php';

header('Content-Type: application/json');
requireLogin();

$action = $_GET['action'] ?? '';
$parivar_id = currentParivarId();

switch ($action) {
    case 'list':
        $stmt = $pdo->prepare("SELECT * FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
        $stmt->execute([$parivar_id]);
        echo json_encode(['safalta' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'tree':
        // Nodes for D3.js
        $stmt = $pdo->prepare("SELECT id, pratham_naam as name, kul_naam, ling, jeevit, photo_url FROM vyakti WHERE parivar_id = ?");
        $stmt->execute([$parivar_id]);
        $nodes = $stmt->fetchAll();

        // Edges (Relations)
        $stmt = $pdo->prepare("SELECT s.* FROM sambandh s JOIN vyakti v ON s.vyakti_a_id = v.id WHERE v.parivar_id = ?");
        $stmt->execute([$parivar_id]);
        $edges = $stmt->fetchAll();

        echo json_encode([
            'safalta' => true, 
            'data' => [
                'nodes' => $nodes,
                'edges' => $edges
            ]
        ]);
        break;

    case 'banao':
        csrf_verify();
        $pratham = $_POST['pratham_naam'] ?? '';
        $madhya = $_POST['madhya_naam'] ?? '';
        $kul = $_POST['kul_naam'] ?? '';
        $ling = $_POST['ling'] ?? 'purush';
        $gregorian = $_POST['janm_tithi_gregorian'] ?? null;
        $vs = $_POST['janm_tithi_vs'] ?? '';
        $gotra = $_POST['gotra'] ?? '';
        
        $photo_url = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo_url = uploadPhoto($_FILES['photo'], 'persons');
        }

        $stmt = $pdo->prepare("INSERT INTO vyakti (parivar_id, pratham_naam, madhya_naam, kul_naam, ling, janm_tithi_gregorian, janm_tithi_vs, gotra, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$parivar_id, $pratham, $madhya, $kul, $ling, $gregorian, $vs, $gotra, $photo_url]);
        
        header('Location: /parivar/pages/dashboard.php?success=sadasy_joda');
        exit;

    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
