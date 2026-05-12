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
        $stmt = $pdo->prepare("SELECT v.* FROM vyakti v JOIN vyakti_parivar vp ON v.id = vp.vyakti_id WHERE vp.parivar_id = ? ORDER BY v.pratham_naam");
        $stmt->execute([$parivar_id]);
        echo json_encode(['safalta' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'tree':
        $req_parivar_id = !empty($_GET['parivar_id']) ? $_GET['parivar_id'] : $parivar_id;
        
        // Nodes for D3.js
        $stmt = $pdo->prepare("SELECT v.id, v.pratham_naam as name, v.kul_naam, v.ling, v.jeevit, v.photo_url FROM vyakti v JOIN vyakti_parivar vp ON v.id = vp.vyakti_id WHERE vp.parivar_id = ?");
        $stmt->execute([$req_parivar_id]);
        $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Find cross-family links
        foreach ($nodes as &$n) {
            $st = $pdo->prepare("SELECT p.id, p.naam FROM vyakti_parivar vp JOIN parivar p ON vp.parivar_id = p.id WHERE vp.vyakti_id = ? AND vp.parivar_id != ?");
            $st->execute([$n['id'], $req_parivar_id]);
            $n['other_parivars'] = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Edges (Relations)
        $stmt = $pdo->prepare("SELECT s.* FROM sambandh s JOIN vyakti_parivar vp1 ON s.vyakti_a_id = vp1.vyakti_id JOIN vyakti_parivar vp2 ON s.vyakti_b_id = vp2.vyakti_id WHERE vp1.parivar_id = ? AND vp2.parivar_id = ?");
        $stmt->execute([$req_parivar_id, $req_parivar_id]);
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
            // Duplicate Check
            $gregorian_for_check = empty($gregorian) ? null : $gregorian;
            $stmt_dup = $pdo->prepare("SELECT id FROM vyakti WHERE parivar_id = ? AND pratham_naam = ? AND kul_naam = ? AND ling = ? AND janm_tithi_gregorian <=> ?");
            $stmt_dup->execute([$parivar_id, $pratham, $kul, $ling, $gregorian_for_check]);
            if ($stmt_dup->fetchColumn()) {
                header('Location: /pages/dashboard.php?error=duplicate_entry');
                exit;
            }

            $pdo->beginTransaction();
            
            $share_code = 'VK-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
            $stmt = $pdo->prepare("INSERT INTO vyakti (parivar_id, pratham_naam, madhya_naam, kul_naam, ling, janm_tithi_gregorian, janm_tithi_vs, gotra, photo_url, share_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$parivar_id, $pratham, $madhya, $kul, $ling, $gregorian, $vs, $gotra, $photo_url, $share_code]);
            $new_person_id = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO vyakti_parivar (vyakti_id, parivar_id) VALUES (?, ?)")->execute([$new_person_id, $parivar_id]);

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
            header('Location: /pages/dashboard.php?success=sadasy_joda');
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: /pages/sadasy_banao.php?error=fail');
        }
        exit;

    case 'link_profile':
        csrf_verify();
        $code = $_POST['share_code'] ?? '';
        if (!$code) {
            header('Location: /pages/sadasy_banao.php?error=invalid_code');
            exit;
        }
        $stmt = $pdo->prepare("SELECT id, ling FROM vyakti WHERE share_code = ?");
        $stmt->execute([$code]);
        $linked_vyakti = $stmt->fetch();
        if ($linked_vyakti) {
            $linked_id = $linked_vyakti['id'];
            $ling = $linked_vyakti['ling'];
            
            $pdo->prepare("INSERT IGNORE INTO vyakti_parivar (vyakti_id, parivar_id) VALUES (?, ?)")->execute([$linked_id, $parivar_id]);
            
            // Build Extended Relation
            $rel_id = $_POST['relative_id'] ?? null;
            $rel_type = $_POST['relative_relation'] ?? '';
            if ($rel_id && $rel_type) {
                $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$linked_id, $rel_id, $rel_type]);

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
                    case 'bahu': $reciprocal = 'sasur'; break;
                    case 'samdhi':
                    case 'samdhan': $reciprocal = $is_female ? 'samdhan' : 'samdhi'; break;
                }
                if ($reciprocal) {
                    $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$rel_id, $linked_id, $reciprocal]);
                }
            }
            header('Location: /pages/dashboard.php?success=sadasy_joda');
        } else {
            header('Location: /pages/sadasy_banao.php?error=invalid_code');
        }
        exit;

    case 'merge':
        csrf_verify();
        requireMukhya();
        
        $primary_id = $_POST['primary_id'] ?? 0;
        $duplicate_id = $_POST['duplicate_id'] ?? 0;
        
        if (!$primary_id || !$duplicate_id || $primary_id == $duplicate_id) {
            header('Location: /pages/merge_vyakti.php?error=invalid_selection');
            exit;
        }

        try {
            $pdo->beginTransaction();
            
            // Validate that both belong to the same parivar
            $stmt = $pdo->prepare("SELECT id FROM vyakti WHERE id IN (?, ?) AND parivar_id = ?");
            $stmt->execute([$primary_id, $duplicate_id, $parivar_id]);
            if ($stmt->rowCount() !== 2) {
                throw new Exception("Invalid profiles");
            }
            
            // Update sambandh table where vyakti_a_id = duplicate_id
            $stmt = $pdo->prepare("UPDATE IGNORE sambandh SET vyakti_a_id = ? WHERE vyakti_a_id = ?");
            $stmt->execute([$primary_id, $duplicate_id]);
            
            // Update sambandh table where vyakti_b_id = duplicate_id
            $stmt = $pdo->prepare("UPDATE IGNORE sambandh SET vyakti_b_id = ? WHERE vyakti_b_id = ?");
            $stmt->execute([$primary_id, $duplicate_id]);
            
            // Update karyakram table
            $stmt = $pdo->prepare("UPDATE karyakram SET vyakti_id = ? WHERE vyakti_id = ?");
            $stmt->execute([$primary_id, $duplicate_id]);
            
            // Update parivar_feed table
            $stmt = $pdo->prepare("UPDATE parivar_feed SET vyakti_id = ? WHERE vyakti_id = ?");
            $stmt->execute([$primary_id, $duplicate_id]);
            
            // Instead of deleting vyakti globally, we remove them from the parivar
            $stmt = $pdo->prepare("DELETE FROM vyakti_parivar WHERE vyakti_id = ? AND parivar_id = ?");
            $stmt->execute([$duplicate_id, $parivar_id]);
            
            // Clean up orphaned vyakti (if they belong to 0 parivars)
            $pdo->exec("DELETE FROM vyakti WHERE id NOT IN (SELECT vyakti_id FROM vyakti_parivar)");
            
            // Clean up any remaining self-relations (A is related to A) after IGNORE
            $pdo->prepare("DELETE FROM sambandh WHERE vyakti_a_id = vyakti_b_id")->execute();
            
            $pdo->commit();
            header('Location: /pages/dashboard.php?success=merged');
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: /pages/merge_vyakti.php?error=fail');
        }
        exit;

    case 'sambandh_jodo':
        csrf_verify();
        $a = (int)$_POST['vyakti_a_id'];
        $b = (int)$_POST['vyakti_b_id'];
        $prakar = $_POST['sambandh_prakar'] ?? '';
        
        // Verify both belong to this parivar
        $check = $pdo->prepare("SELECT COUNT(*) FROM vyakti_parivar WHERE vyakti_id IN (?,?) AND parivar_id = ?");
        $check->execute([$a, $b, $parivar_id]);
        if ($check->fetchColumn() < 2) {
            header('Location: /pages/vyakti.php?id=' . $a . '&error=permission');
            exit;
        }
        
        // Gotra check for vivah
        if (in_array($prakar, ['pati','patni'])) {
            $ga = $pdo->prepare("SELECT gotra FROM vyakti WHERE id=?"); $ga->execute([$a]); $ga = $ga->fetchColumn();
            $gb = $pdo->prepare("SELECT gotra FROM vyakti WHERE id=?"); $gb->execute([$b]); $gb = $gb->fetchColumn();
            if ($ga && $gb && $ga === $gb) {
                header('Location: /pages/vyakti.php?id=' . $a . '&error=sagotra_warning');
                exit;
            }
        }
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?,?,?)");
        $stmt->execute([$a, $b, $prakar]);
        
        // Determine reciprocal (Simple version)
        $stmt_a = $pdo->prepare("SELECT ling FROM vyakti WHERE id = ?");
        $stmt_a->execute([$a]);
        $a_ling = $stmt_a->fetchColumn();
        $is_female = ($a_ling === 'stri');
        
        $reciprocal = '';
        switch ($prakar) {
            case 'pita':
            case 'mata': $reciprocal = $is_female ? 'putri' : 'putra'; break;
            case 'pati': $reciprocal = 'patni'; break;
            case 'patni': $reciprocal = 'pati'; break;
            case 'putra':
            case 'putri': 
                $stmt_b = $pdo->prepare("SELECT ling FROM vyakti WHERE id = ?");
                $stmt_b->execute([$b]);
                $b_ling = $stmt_b->fetchColumn();
                $reciprocal = ($b_ling === 'stri') ? 'mata' : 'pita'; 
                break;
            case 'bhai':
            case 'behen': $reciprocal = $is_female ? 'behen' : 'bhai'; break;
        }
        
        if ($reciprocal) {
            $stmt2 = $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?,?,?)");
            $stmt2->execute([$b, $a, $reciprocal]);
        }
        
        header('Location: /pages/vyakti.php?id=' . $a . '&success=sambandh_joda');
        exit;

    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
