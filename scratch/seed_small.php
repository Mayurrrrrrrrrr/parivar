<?php
require_once __DIR__ . '/../config/db.php';

$pdo->exec("INSERT IGNORE INTO parivar (id, naam, parivar_code) VALUES (1, 'Sharma Parivar', 'SHRM01')");
$pdo->exec("INSERT IGNORE INTO parivar (id, naam, parivar_code) VALUES (2, 'Verma Parivar', 'VRMA02')");

$pdo->beginTransaction();
$pdo->exec("DELETE FROM vyakti WHERE parivar_id IN (1, 2)");
$pdo->exec("DELETE FROM sambandh");
$pdo->exec("DELETE FROM vyakti_parivar WHERE parivar_id IN (1, 2)");

function addPerson($pratham, $kul, $ling, $jeevit, $parivars) {
    global $pdo;
    $code = 'VK-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    $stmt = $pdo->prepare("INSERT INTO vyakti (parivar_id, pratham_naam, kul_naam, ling, jeevit, share_code) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$parivars[0], $pratham, $kul, $ling, $jeevit, $code]);
    $id = $pdo->lastInsertId();
    foreach ($parivars as $pid) {
        $pdo->prepare("INSERT IGNORE INTO vyakti_parivar (vyakti_id, parivar_id) VALUES (?, ?)")->execute([$id, $pid]);
    }
    return $id;
}

function addSambandh($a, $b, $type) {
    global $pdo;
    $pdo->prepare("INSERT IGNORE INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES (?, ?, ?)")->execute([$a, $b, $type]);
}

// === FAMILY 1: SHARMA ===
$s1_m = addPerson('रामप्रसाद', 'शर्मा', 'purush', 0, [1]);
$s1_f = addPerson('सावित्री', 'शर्मा', 'stri', 0, [1]);
addSambandh($s1_m, $s1_f, 'pati'); addSambandh($s1_f, $s1_m, 'patni');

$s2_m = addPerson('सुरेश', 'शर्मा', 'purush', 1, [1]); // Son
addSambandh($s1_m, $s2_m, 'pita'); addSambandh($s1_f, $s2_m, 'mata');
addSambandh($s2_m, $s1_m, 'putra'); addSambandh($s2_m, $s1_f, 'putra');

$s2_f = addPerson('मीना', 'शर्मा', 'stri', 1, [1]); // Daughter-in-law
addSambandh($s2_m, $s2_f, 'pati'); addSambandh($s2_f, $s2_m, 'patni');

$s3_m = addPerson('राहुल', 'शर्मा', 'purush', 1, [1]); // Grandson
addSambandh($s2_m, $s3_m, 'pita'); addSambandh($s2_f, $s3_m, 'mata');
addSambandh($s3_m, $s2_m, 'putra'); addSambandh($s3_m, $s2_f, 'putra');

$s3_f1 = addPerson('स्नेहा', 'शर्मा', 'stri', 1, [1]); // Granddaughter
addSambandh($s2_m, $s3_f1, 'pita'); addSambandh($s2_f, $s3_f1, 'mata');
addSambandh($s3_f1, $s2_m, 'putri'); addSambandh($s3_f1, $s2_f, 'putri');


// === FAMILY 2: VERMA ===
$v1_m = addPerson('दिनेश', 'वर्मा', 'purush', 0, [2]);
$v1_f = addPerson('कमला', 'वर्मा', 'stri', 0, [2]);
addSambandh($v1_m, $v1_f, 'pati'); addSambandh($v1_f, $v1_m, 'patni');

$v2_m = addPerson('राकेश', 'वर्मा', 'purush', 1, [2]); // Son
addSambandh($v1_m, $v2_m, 'pita'); addSambandh($v1_f, $v2_m, 'mata');
addSambandh($v2_m, $v1_m, 'putra'); addSambandh($v2_m, $v1_f, 'putra');

$v2_f = addPerson('सुनीता', 'वर्मा', 'stri', 1, [2]); // Daughter-in-law
addSambandh($v2_m, $v2_f, 'pati'); addSambandh($v2_f, $v2_m, 'patni');

// === THE MYCELIUM BRIDGE (Cross-Family) ===
// Neha is Rakesh Verma's daughter. She marries Rahul Sharma.
// She belongs to BOTH Verma [2] (by birth) and Sharma [1] (by marriage).
$neha = addPerson('नेहा', 'वर्मा', 'stri', 1, [1, 2]);

// Link to Verma parents
addSambandh($v2_m, $neha, 'pita'); addSambandh($v2_f, $neha, 'mata');
addSambandh($neha, $v2_m, 'putri'); addSambandh($neha, $v2_f, 'putri');

// Link to Sharma husband (Rahul)
addSambandh($s3_m, $neha, 'pati'); addSambandh($neha, $s3_m, 'patni');


$pdo->commit();
echo "Successfully seeded Sharma and Verma parivar with a cross-family bridge (Neha).\n";
?>
