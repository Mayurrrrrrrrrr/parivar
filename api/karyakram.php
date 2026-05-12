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
        require_once __DIR__ . '/../includes/panchang.php';
        $stmt = $pdo->prepare("SELECT k.*, v.pratham_naam FROM karyakram k LEFT JOIN vyakti v ON k.vyakti_id = v.id WHERE k.parivar_id = ?");
        $stmt->execute([$parivar_id]);
        $all_events = $stmt->fetchAll();
        
        $upcoming = [];
        foreach ($all_events as $e) {
            if ($e['punravrutti_prakar'] === 'tithi_varshik' && $e['tithi_vs']) {
                $e['next_date'] = getTithiNextGregorian($e['tithi_vs']);
            } elseif ($e['punravrutti_prakar'] === 'gregorian_varshik') {
                $orig = date('m-d', strtotime($e['tithi_gregorian']));
                $this_year = date('Y') . '-' . $orig;
                $e['next_date'] = ($this_year >= date('Y-m-d')) ? $this_year : (date('Y')+1) . '-' . $orig;
            } else {
                $e['next_date'] = $e['tithi_gregorian'];
            }
            
            $days = floor((strtotime($e['next_date']) - strtotime(date('Y-m-d'))) / 86400);
            if ($days >= 0 && $days <= 30) {
                $upcoming[] = $e;
            }
        }
        
        usort($upcoming, fn($a,$b) => strcmp($a['next_date'], $b['next_date']));
        echo json_encode(['safalta' => true, 'data' => $upcoming]);
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
        
        header('Location: /pages/karyakram.php?success=1');
        exit;

    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
