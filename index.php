<?php
/**
 * मुख्य प्रवेश द्वार — लॉगिन/रजिस्टर (v2.0)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) {
    header('Location: /parivar/pages/dashboard.php');
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>परिवार — आपका डिजिटल कुल</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/parivar/assets/css/style.css">
</head>
<body style="padding-bottom: 0;">

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-brand">
            <h1>परिवार</h1>
            <p>मम परिवारः मम गौरवम्</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo s(getErrorMessage($error)); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">सफलता! अब आप लॉगिन कर सकते हैं।</div>
        <?php endif; ?>

        <div class="login-tabs">
            <button class="login-tab active" onclick="switchTab('login')">लॉगिन</button>
            <button class="login-tab" onclick="switchTab('join')">जुड़ें</button>
            <button class="login-tab" onclick="switchTab('register')">बनाएँ</button>
        </div>

        <!-- Login Form -->
        <form id="form-login" action="/parivar/api/auth.php?action=login" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>ईमेल या फोन</label>
                <input type="text" name="email" class="form-control" placeholder="example@mail.com" required>
            </div>
            <div class="form-group">
                <label>पासवर्ड</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary mt-1">प्रवेश करें</button>
        </form>

        <!-- Join Form -->
        <form id="form-join" action="/parivar/api/auth.php?action=join" method="POST" style="display:none">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>परिवार कोड (Family Code)</label>
                <input type="text" name="family_code" class="form-control" placeholder="जैसे: AB12CD" required maxlength="6">
            </div>
            <div class="form-group">
                <label>आपका नाम</label>
                <input type="text" name="naam" class="form-control" placeholder="जैसे: मयूर" required>
            </div>
            <div class="form-group">
                <label>ईमेल</label>
                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
            </div>
            <div class="form-group">
                <label>पासवर्ड</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary mt-1">परिवार से जुड़ें</button>
        </form>

        <!-- Register Form -->
        <form id="form-register" action="/parivar/api/auth.php?action=register" method="POST" style="display:none">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>परिवार का नाम (Surname/Kul)</label>
                <input type="text" name="parivar_naam" class="form-control" placeholder="जैसे: शांडिल्य परिवार" required>
            </div>
            <div class="form-group">
                <label>आपका नाम (मुखिया)</label>
                <input type="text" name="naam" class="form-control" required>
            </div>
            <div class="form-group">
                <label>ईमेल</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>पासवर्ड</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-1">नया परिवार पोर्टल बनाएँ</button>
        </form>

    </div>
</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('form').forEach(f => f.style.display = 'none');
        
        event.target.classList.add('active');
        document.getElementById('form-' + tab).style.display = 'block';
    }
</script>

</body>
</html>
