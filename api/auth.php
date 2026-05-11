<?php
/**
 * API — प्रमाणीकरण (Auth)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':    handleLogin($pdo); break;
    case 'register': handleRegister($pdo); break;
    case 'join':     handleJoin($pdo); break;
    case 'logout':   handleLogout(); break;
    default:
        echo json_encode(['safalta' => false, 'sandesh' => 'अज्ञात action']);
}

function handleLogin($pdo) {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        header('Location: /index.php?error=khaali_fields');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['parivar_id'] = $user['parivar_id'];
        $_SESSION['naam'] = $user['naam'];
        $_SESSION['bhumika'] = $user['bhumika'];
        header('Location: /pages/dashboard.php');
        exit;
    }
    
    header('Location: /index.php?error=galat_login');
    exit;
}

function handleRegister($pdo) {
    csrf_verify();
    $parivar_naam = trim($_POST['parivar_naam'] ?? '');
    $user_naam = trim($_POST['naam'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
    $family_code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO parivar (naam, parivar_code) VALUES (?, ?)");
        $stmt->execute([$parivar_naam, $family_code]);
        $parivar_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO users (parivar_id, naam, email, password_hash, bhumika) VALUES (?, ?, ?, ?, 'mukhya')");
        $stmt->execute([$parivar_id, $user_naam, $email, $password]);
        
        $pdo->commit();
        header('Location: /index.php?success=1');
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: /index.php?error=banao_fail');
    }
    exit;
}

function handleJoin($pdo) {
    csrf_verify();
    $family_code = trim($_POST['family_code'] ?? '');
    $user_naam = trim($_POST['naam'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT id FROM parivar WHERE parivar_code = ?");
    $stmt->execute([$family_code]);
    $parivar = $stmt->fetch();

    if ($parivar) {
        $stmt = $pdo->prepare("INSERT INTO users (parivar_id, naam, email, password_hash, bhumika) VALUES (?, ?, ?, ?, 'sadasy')");
        $stmt->execute([$parivar['id'], $user_naam, $email, $password]);
        header('Location: /index.php?success=1');
    } else {
        header('Location: /index.php?error=code_galat');
    }
    exit;
}

function handleLogout() {
    session_destroy();
    header('Location: /index.php');
    exit;
}
