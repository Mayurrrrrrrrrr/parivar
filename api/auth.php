<?php
/**
 * API — प्रमाणीकरण (Auth)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT u.*, p.naam as parivar_naam FROM users u 
                           JOIN parivar p ON u.parivar_id = p.id 
                           WHERE u.email = ? OR u.phone = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_naam'] = $user['naam'];
        $_SESSION['parivar_id'] = $user['parivar_id'];
        $_SESSION['bhumika'] = $user['bhumika'];
        
        header('Location: ../pages/dashboard.php');
    } else {
        header('Location: ../index.php?error=galat_login');
    }
    exit;
}

if ($action === 'register') {
    $parivar_naam = $_POST['parivar_naam'] ?? '';
    $user_naam = $_POST['naam'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
    $family_code = generateFamilyCode();

    try {
        $pdo->beginTransaction();
        
        // 1. परिवार बनाएँ
        $stmt = $pdo->prepare("INSERT INTO parivar (naam, parivar_code) VALUES (?, ?)");
        $stmt->execute([$parivar_naam, $family_code]);
        $parivar_id = $pdo->lastInsertId();

        // 2. मुख्य उपयोगकर्ता बनाएँ
        $stmt = $pdo->prepare("INSERT INTO users (parivar_id, naam, email, password_hash, bhumika) VALUES (?, ?, ?, ?, 'mukhya')");
        $stmt->execute([$parivar_id, $user_naam, $email, $password]);
        
        $pdo->commit();
        header('Location: ../index.php?success=1');
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: ../index.php?error=banao_fail');
    }
    exit;
}

if ($action === 'join') {
    $family_code = $_POST['family_code'] ?? '';
    $user_naam = $_POST['naam'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT id FROM parivar WHERE parivar_code = ?");
    $stmt->execute([$family_code]);
    $parivar = $stmt->fetch();

    if ($parivar) {
        $stmt = $pdo->prepare("INSERT INTO users (parivar_id, naam, email, password_hash, bhumika) VALUES (?, ?, ?, ?, 'sadasy')");
        $stmt->execute([$parivar['id'], $user_naam, $email, $password]);
        header('Location: ../index.php?success=1');
    } else {
        header('Location: ../index.php?error=code_galat');
    }
    exit;
}
