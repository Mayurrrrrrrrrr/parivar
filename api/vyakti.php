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
        $sibling_ids = $_POST['sibling_ids'] ?? [];
        
        $photo_url = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo_url = uploadPhoto($_FILES['photo'], 'persons');
        }

        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO vyakti (parivar_id, pratham_naam, madhya_naam, kul_naam, ling, janm_tithi_gregorian, janm_tithi_vs, gotra, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$parivar_id, $pratham, $madhya, $kul, $ling, $gregorian, $vs, $gotra, $photo_url]);
            $new_person_id = $pdo->lastInsertId();

            // 1. Inherit Parents from Siblings if not set
            if ((!$pita_id || !$mata_id) && !empty($sibling_ids)) {
                foreach ($sibling_ids as $sid) {
                    if (!$pita_id) {
                        $st = $pdo->prepare("SELECT vyakti_a_id FROM sambandh WHERE vyakti_b_id = ? AND sambandh_prakar = 'pita' LIMIT 1");
                        $st->execute([$sid]);
                        $inherited_pita = $st->fetchColumn();
                        if ($inherited_pita) $pita_id = $inherited_pita;
                    }
                    if (!$mata_id) {
                        $st = $pdo->prepare("SELECT vyakti_a_id FROM sambandh WHERE vyakti_b_id = ? AND sambandh_prakar = 'mata' LIMIT 1");
                        $st->execute([$sid]);
                        $inherited_mata = $st->fetchColumn();
                        if ($inherited_mata) $mata_id = $inherited_mata;
                    }
                    if ($pita_id && $mata_id) break;
                }
            }

            // 2. Build Parent Relations
            if ($pita_id) {
                $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, 'pita')")->execute([$pita_id, $new_person_id]);
                $prakar = ($ling === 'stri') ? 'putri' : 'putra';
                $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$new_person_id, $pita_id, $prakar]);
            }

            if ($mata_id) {
                $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, 'mata')")->execute([$mata_id, $new_person_id]);
                $prakar = ($ling === 'stri') ? 'putri' : 'putra';
                $pdo->prepare("INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$new_person_id, $mata_id, $prakar]);
            }

            // 3. Build Sibling Relations
            foreach ($sibling_ids as $sid) {
                $stmt_s = $pdo->prepare("SELECT ling FROM vyakti WHERE id = ?");
                $stmt_s->execute([$sid]);
                $s_ling = $stmt_s->fetchColumn();

                $prakar_sid_to_new = ($s_ling === 'stri') ? 'behen' : 'bhai';
                $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$sid, $new_person_id, $prakar_sid_to_new]);

                $prakar_new_to_sid = ($ling === 'stri') ? 'behen' : 'bhai';
                $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$new_person_id, $sid, $prakar_new_to_sid]);
            }

            // 4. Build Extended Relation
            $rel_id = $_POST['relative_id'] ?? null;
            $rel_type = $_POST['relative_relation'] ?? '';
            if ($rel_id && $rel_type) {
                // New Person (C) is [rel_type] of [rel_id]
                $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$new_person_id, $rel_id, $rel_type]);

                // Determine reciprocal
                $reciprocal = '';
                $is_female = ($ling === 'stri');

                switch ($rel_type) {
                    case 'pati': $reciprocal = 'patni'; break;
                    case 'patni': $reciprocal = 'pati'; break;
                    case 'bhai':
                    case 'behen': $reciprocal = $is_female ? 'behen' : 'bhai'; break;
                    case 'mama':
                    case 'mausa':
                    case 'mami':
                    case 'mausi': $reciprocal = $is_female ? 'bhanji' : 'bhanja'; break;
                    case 'chacha':
                    case 'taau':
                    case 'fufa':
                    case 'chachi':
                    case 'tai':
                    case 'bua': $reciprocal = $is_female ? 'bhatiji' : 'bhatija'; break;
                    case 'dada':
                    case 'dadi': $reciprocal = $is_female ? 'poti' : 'pota'; break;
                    case 'nana':
                    case 'nani': $reciprocal = $is_female ? 'natini' : 'nati'; break;
                    case 'sasur':
                    case 'saas': $reciprocal = $is_female ? 'bahu' : 'damad'; break;
                    case 'sala':
                    case 'sali': $reciprocal = $is_female ? 'bhabhi' : 'jija'; break;
                    case 'damad':
                    case 'bahu': $reciprocal = 'sasur'; break; // Approximate
                    case 'samdhi':
                    case 'samdhan': $reciprocal = $is_female ? 'samdhan' : 'samdhi'; break;
                }

                if ($reciprocal) {
                    $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$rel_id, $new_person_id, $reciprocal]);
                }
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
