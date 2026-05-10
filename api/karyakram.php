<?php
/**
 * API — कार्यक्रम (Karyakram) प्रबंधन
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();
$action = $_GET['action'] ?? 'list';
$parivar_id = getParivarId();

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT k.*, v.pratham_naam FROM karyakram k 
                           LEFT JOIN vyakti v ON k.vyakti_id = v.id 
                           WHERE k.parivar_id = ? ORDER BY k.tithi_gregorian DESC");
    $stmt->execute([$parivar_id]);
    sendJson($stmt->fetchAll());
}

if ($action === 'banao') {
    csrf_verify();
    $shirshak = $_POST['shirshak'] ?? '';
    $prakar = $_POST['prakar'] ?? 'any';
    $vyakti_id = !empty($_POST['vyakti_id']) ? $_POST['vyakti_id'] : null;
    $tithi_g = $_POST['tithi_gregorian'] ?? '';
    $tithi_vs = $_POST['tithi_vs'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO karyakram (parivar_id, vyakti_id, shirshak, prakar, tithi_gregorian, tithi_vs) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$parivar_id, $vyakti_id, $shirshak, $prakar, $tithi_g, $tithi_vs]);
    
    header('Location: ../pages/karyakram.php?success=1');
    exit;
}
