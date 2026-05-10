<?php
/**
 * API — पंचांग कनवर्टर
 */
require_once __DIR__ . '/../includes/panchang.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'convert';

if ($action === 'convert') {
    $gregorian = $_GET['gregorian'] ?? date('Y-m-d');
    if (!$gregorian) {
        echo json_encode(['safalta' => false, 'sandesh' => 'तारीख आवश्यक है']);
        exit;
    }
    
    $parts = explode('-', $gregorian);
    if (count($parts) === 3) {
        $result = gregorianToVS((int)$parts[2], (int)$parts[1], (int)$parts[0]);
        echo json_encode(['safalta' => true, 'data' => $result]);
    } else {
        echo json_encode(['safalta' => false, 'sandesh' => 'गलत तारीख प्रारूप']);
    }
} else {
    echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}
