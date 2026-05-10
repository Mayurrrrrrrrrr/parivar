<?php
/**
 * मुख्य प्रवेश द्वार — लॉगिन/रजिस्टर (v2.0)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) {
    header('Location: /pages/dashboard.php');
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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body style="padding-bottom: 0;">

<div class="login-wrapper">

    <div class="access-bar">
        <div class="circle-btn" onclick="toggleSeniorMode()" title="बड़ा फॉन्ट">
            <i class="ti ti-typography"></i>
        </div>
        <div class="circle-btn" onclick="toggleTheme()" id="theme-btn">
            <i class="ti ti-moon"></i>
        </div>
    </div>

    <div class="login-card">
        <div class="login-brand">
            <h1>परिवार</h1>
            <p>मम परिवारः मम गौरवम्</p>
        </div>

        <?php if ($error): ?>
            <div class="card" style="background: rgba(239, 71, 111, 0.1); border: 1px solid var(--rang-asafal); padding: 12px; margin-bottom: 20px; border-radius: 12px; color: var(--rang-asafal); font-size: 13px; text-align: center;">
                <i class="ti ti-alert-circle"></i> <?php echo s(getErrorMessage($error)); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="card" style="background: rgba(6, 214, 160, 0.1); border: 1px solid var(--rang-safal); padding: 12px; margin-bottom: 20px; border-radius: 12px; color: var(--rang-safal); font-size: 13px; text-align: center;">
                <i class="ti ti-check"></i> सफलता! अब आप लॉगिन कर सकते हैं।
            </div>
        <?php endif; ?>

        <div class="login-tabs">
            <button class="login-tab active" onclick="switchTab('login')">लॉगिन</button>
            <button class="login-tab" onclick="switchTab('join')">जुड़ें</button>
            <button class="login-tab" onclick="switchTab('register')">बनाएँ</button>
        </div>

        <!-- Login Form -->
        <form id="form-login" action="/api/auth.php?action=login" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>ईमेल या फोन</label>
                <input type="text" name="email" class="form-control" placeholder="example@mail.com" required>
            </div>
            <div class="form-group">
                <label>पासवर्ड</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary mt-1">प्रवेश करें <i class="ti ti-arrow-right"></i></button>
        </form>

        <!-- Join Form -->
        <form id="form-join" action="/api/auth.php?action=join" method="POST" style="display:none">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>परिवार कोड (Family Code)</label>
                <input type="text" name="family_code" class="form-control" placeholder="जैसे: AB12CD" required maxlength="6">
            </div>
            <div class="form-group">
                <label>आपका नाम</label>
                <input type="text" name="naam" class="form-control hindi-type" placeholder="जैसे: मयूर" required>
            </div>
            <div class="form-group">
                <label>ईमेल</label>
                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
            </div>
            <div class="form-group">
                <label>पासवर्ड</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary mt-1">परिवार से जुड़ें <i class="ti ti-users"></i></button>
        </form>

        <!-- Register Form -->
        <form id="form-register" action="/api/auth.php?action=register" method="POST" style="display:none">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>परिवार का नाम (Surname/Kul)</label>
                <input type="text" name="parivar_naam" class="form-control hindi-type" placeholder="जैसे: शांडिल्य परिवार" required>
            </div>
            <div class="form-group">
                <label>आपका नाम (मुखिया)</label>
                <input type="text" name="naam" class="form-control hindi-type" required>
            </div>
            <div class="form-group">
                <label>ईमेल</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>पासवर्ड</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-1">पोर्टल बनाएँ <i class="ti ti-sparkles"></i></button>
        </form>

    </div>
</div>

<script>
    // Theme & Accessibility Persistence
    (function() {
        const theme = localStorage.getItem('theme') || 'light';
        const senior = localStorage.getItem('senior_mode') === 'true';
        document.documentElement.setAttribute('data-theme', theme);
        if (senior) document.documentElement.classList.add('senior-mode');
    })();

    function switchTab(tab) {
        document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('form').forEach(f => f.style.display = 'none');
        
        event.target.classList.add('active');
        document.getElementById('form-' + tab).style.display = 'block';
    }

    function toggleSeniorMode() {
        const isSenior = document.documentElement.classList.toggle('senior-mode');
        localStorage.setItem('senior_mode', isSenior);
    }
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateThemeIcon();
    }
    function updateThemeIcon() {
        const theme = document.documentElement.getAttribute('data-theme');
        const btn = document.getElementById('theme-btn');
        if (btn) btn.innerHTML = theme === 'dark' ? '<i class="ti ti-sun"></i>' : '<i class="ti ti-moon"></i>';
    }
    updateThemeIcon();
</script>

</body>
</html>
