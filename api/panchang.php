<?php
/**
 * API — पंचांग (Panchang) रूपांतरण
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/panchang.php';

requireLogin();
$action = $_GET['action'] ?? 'convert';

if ($action === 'convert') {
    $gregorian = $_GET['gregorian'] ?? date('Y-m-d');
    $ts = strtotime($gregorian);
    $d = (int)date('d', $ts);
    $m = (int)date('m', $ts);
    $y = (int)date('y', $ts);
    
    $res = gregorianToVS($d, $m, $y);
    sendJson($res);
}
