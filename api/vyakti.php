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
        $pita_id = $_POST['pita_id'] ?? null;
        $mata_id = $_POST['mata_id'] ?? null;
        
        $photo_url = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo_url = uploadPhoto($_FILES['photo'], 'persons');
        }

        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO vyakti (parivar_id, pratham_naam, madhya_naam, kul_naam, ling, janm_tithi_gregorian, janm_tithi_vs, gotra, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$parivar_id, $pratham, $madhya, $kul, $ling, $gregorian, $vs, $gotra, $photo_url]);
            $new_person_id = $pdo->lastInsertId();

            // Auto-build Relations
            if ($pita_id) {
                // A (pita) -> B (putra/putri)
                $stmt = $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, 'pita')");
                $stmt->execute([$pita_id, $new_person_id]);
                
                // B -> A
                $prakar = ($ling === 'stri') ? 'putri' : 'putra';
                $stmt = $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)");
                $stmt->execute([$new_person_id, $pita_id, $prakar]);
            }

            if ($mata_id) {
                // A (mata) -> B (putra/putri)
                $stmt = $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, 'mata')");
                $stmt->execute([$mata_id, $new_person_id]);
                
                // B -> A
                $prakar = ($ling === 'stri') ? 'putri' : 'putra';
                $stmt = $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)");
                $stmt->execute([$new_person_id, $mata_id, $prakar]);
            }

            $pdo->commit();
            header('Location: /parivar/pages/dashboard.php?success=sadasy_joda');
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: /parivar/pages/sadasy_banao.php?error=fail');
        }
        exit;

    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
