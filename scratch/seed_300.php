<?php
require_once __DIR__ . '/../config/db.php';

// Target parivar 1: Sharma, Target parivar 2: Verma
$pdo->exec("INSERT IGNORE INTO parivar (id, naam, parivar_code) VALUES (1, 'Sharma Parivar', 'SHRM01')");
$pdo->exec("INSERT IGNORE INTO parivar (id, naam, parivar_code) VALUES (2, 'Verma Parivar', 'VRMA02')");

$males = ['Aarav', 'Vihaan', 'Aditya', 'Sai', 'Arjun', 'Sai', 'Krishna', 'Ishaan', 'Shaurya', 'Ayaan', 'Rohan', 'Kunal', 'Dev', 'Samar', 'Karan', 'Raj', 'Yash', 'Prem', 'Shiv'];
$females = ['Saanvi', 'Aanya', 'Aadhya', 'Aaradhya', 'Ananya', 'Pari', 'Diya', 'Navya', 'Isha', 'Riya', 'Kavya', 'Neha', 'Priya', 'Simran', 'Pooja', 'Mira', 'Tara', 'Sita', 'Gita'];
$surnames = ['Sharma', 'Verma', 'Gupta', 'Singh', 'Patel', 'Kumar'];

$pdo->beginTransaction();

// Clean existing data for a fresh 300 tree in parivar 1 and 2
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

$personCount = 0;

// Gen 0
$rootM = addPerson('Brahma', 'Sharma', 'purush', 0, [1]);
$rootF = addPerson('Saraswati', 'Sharma', 'stri', 0, [1]);
addSambandh($rootM, $rootF, 'pati');
addSambandh($rootF, $rootM, 'patni');
$personCount += 2;

$queue = [ ['id_m' => $rootM, 'id_f' => $rootF, 'gen' => 0] ];

while(count($queue) > 0 && $personCount < 300) {
    $curr = array_shift($queue);
    
    // Create 3-4 children for this couple
    $numChildren = rand(3, 5);
    for ($i = 0; $i < $numChildren; $i++) {
        if ($personCount >= 300) break;
        
        $isMale = rand(0, 1) === 1;
        $name = $isMale ? $males[array_rand($males)] : $females[array_rand($females)];
        $ling = $isMale ? 'purush' : 'stri';
        $jeevit = $curr['gen'] > 2 ? 1 : (rand(0, 1) ? 1 : 0);
        
        $childId = addPerson($name, 'Sharma', $ling, $jeevit, [1]);
        $personCount++;
        
        // Link to parents
        addSambandh($curr['id_m'], $childId, 'pita');
        addSambandh($curr['id_f'], $childId, 'mata');
        addSambandh($childId, $curr['id_m'], $isMale ? 'putra' : 'putri');
        addSambandh($childId, $curr['id_f'], $isMale ? 'putra' : 'putri');
        
        // Marry the child if not in the last generation
        if ($curr['gen'] < 5 && $personCount < 300) {
            $spouseName = $isMale ? $females[array_rand($females)] : $males[array_rand($males)];
            $spouseLing = $isMale ? 'stri' : 'purush';
            $spouseSurname = $surnames[array_rand($surnames)];
            
            // Introduce cross-family! If spouseSurname is Verma, add to Parivar 2
            $parivars = [1];
            if (rand(0, 10) > 7) {
                $parivars[] = 2; // Mycelium network linkage!
            }
            
            $spouseId = addPerson($spouseName, $spouseSurname, $spouseLing, $jeevit, $parivars);
            $personCount++;
            
            if ($isMale) {
                addSambandh($childId, $spouseId, 'pati');
                addSambandh($spouseId, $childId, 'patni');
                $queue[] = ['id_m' => $childId, 'id_f' => $spouseId, 'gen' => $curr['gen'] + 1];
            } else {
                addSambandh($spouseId, $childId, 'pati');
                addSambandh($childId, $spouseId, 'patni');
                $queue[] = ['id_m' => $spouseId, 'id_f' => $childId, 'gen' => $curr['gen'] + 1];
            }
        }
    }
}

$pdo->commit();
echo "Successfully seeded $personCount persons with relations and cross-family links.\n";
?>
