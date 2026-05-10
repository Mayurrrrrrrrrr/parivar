<?php
/**
 * API — व्यक्ति (Vyakti) प्रबंधन
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();
$action = $_GET['action'] ?? 'list';
$parivar_id = getParivarId();

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
    $stmt->execute([$parivar_id]);
    sendJson($stmt->fetchAll());
}

if ($action === 'tree_data') {
    // Fetch all members and their relationships for tree rendering
    $stmt = $pdo->prepare("SELECT id, pratham_naam as name, kul_naam, ling, jeevit, photo_url FROM vyakti WHERE parivar_id = ?");
    $stmt->execute([$parivar_id]);
    $nodes = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT s.* FROM sambandh s JOIN vyakti v ON s.vyakti_a_id = v.id WHERE v.parivar_id = ?");
    $stmt->execute([$parivar_id]);
    $links = $stmt->fetchAll();

    sendJson(['nodes' => $nodes, 'links' => $links]);
}

if ($action === 'banao') {
    csrf_verify();
    
    $photo_url = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = bin2hex(random_bytes(16)) . '.' . $ext;
            $target = __DIR__ . '/../assets/uploads/' . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photo_url = '/assets/uploads/' . $filename;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO vyakti (parivar_id, pratham_naam, madhya_naam, kul_naam, upnaam, ling, janm_tithi_gregorian, janm_tithi_vs, gotra, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $parivar_id, 
        $_POST['pratham_naam'], 
        $_POST['madhya_naam'] ?? '', 
        $_POST['kul_naam'], 
        $_POST['upnaam'] ?? '',
        $_POST['ling'],
        $_POST['janm_tithi_gregorian'] ?? null,
        $_POST['janm_tithi_vs'] ?? '',
        $_POST['gotra'] ?? '',
        $photo_url
    ]);
    header('Location: ../pages/dashboard.php?success=1');
    exit;
}

if ($action === 'update_photo') {
    csrf_verify();
    $id = $_POST['id'] ?? null;
    if (!$id) sendJson([], false, 'ID आवश्यक है');

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = __DIR__ . '/../assets/uploads/' . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $photo_url = '/assets/uploads/' . $filename;
            $stmt = $pdo->prepare("UPDATE vyakti SET photo_url = ? WHERE id = ? AND parivar_id = ?");
            $stmt->execute([$photo_url, $id, $parivar_id]);
            sendJson(['photo_url' => $photo_url]);
        }
    }
    sendJson([], false, 'अपलोड विफल');
}
