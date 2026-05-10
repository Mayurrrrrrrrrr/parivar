<?php
/**
 * मुख्य पृष्ठ — लॉगिन / पंजीकरण / शामिल होना
 */
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>परिवार — आपका स्वागत है</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>परिवार</h1>
        <p>वंशावली एवं कार्यक्रम प्रबंधन पोर्टल</p>
    </header>

    <div class="container" style="max-width: 500px;">
        <?php if ($error): ?>
            <div class="card" style="border-color: var(--rang-asafal); color: var(--rang-asafal); padding: 0.8rem;">
                <?php 
                    if ($error == 'galat_login') echo 'गलत ईमेल या पासवर्ड।';
                    elseif ($error == 'code_galat') echo 'परिवार कोड गलत है।';
                    else echo 'कोई त्रुटि हुई।';
                ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="card" style="border-color: var(--rang-safal); color: var(--rang-safal); padding: 0.8rem;">
                पंजीकरण सफल! कृपया लॉगिन करें।
            </div>
        <?php endif; ?>

        <div class="card" id="login-section">
            <h2>लॉगिन करें</h2>
            <form action="api/auth.php?action=login" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label>ईमेल / फ़ोन</label>
                    <input type="text" name="email" required placeholder="अपना ईमेल या फ़ोन डालें">
                </div>
                <div class="form-group">
                    <label>पासवर्ड</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit">प्रवेश करें</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                <a href="#" onclick="showSection('join-section')">परिवार से जुड़ें</a> | 
                <a href="#" onclick="showSection('register-section')">नया परिवार बनाएँ</a>
            </p>
        </div>

        <div class="card" id="join-section" style="display: none;">
            <h2>परिवार से जुड़ें</h2>
            <form action="api/auth.php?action=join" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label>परिवार कोड (Family Code)</label>
                    <input type="text" name="family_code" required placeholder="जैसे: KUL4MJ" maxlength="6">
                </div>
                <div class="form-group">
                    <label>आपका नाम</label>
                    <input type="text" name="naam" required placeholder="पूरा नाम">
                </div>
                <div class="form-group">
                    <label>ईमेल / फ़ोन</label>
                    <input type="text" name="email" required>
                </div>
                <div class="form-group">
                    <label>पासवर्ड सेट करें</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">जुड़ें</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                <a href="#" onclick="showSection('login-section')">वापस लॉगिन पर जाएँ</a>
            </p>
        </div>

        <div class="card" id="register-section" style="display: none;">
            <h2>नया परिवार बनाएँ</h2>
            <form action="api/auth.php?action=register" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label>परिवार का नाम</label>
                    <input type="text" name="parivar_naam" required placeholder="जैसे: शर्मा परिवार">
                </div>
                <div class="form-group">
                    <label>आपका नाम</label>
                    <input type="text" name="naam" required>
                </div>
                <div class="form-group">
                    <label>ईमेल</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>पासवर्ड</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">परिवार बनाएँ</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;">
                <a href="#" onclick="showSection('login-section')">वापस लॉगिन पर जाएँ</a>
            </p>
        </div>
    </div>

    <script>
        function showSection(id) {
            document.getElementById('login-section').style.display = 'none';
            document.getElementById('join-section').style.display = 'none';
            document.getElementById('register-section').style.display = 'none';
            document.getElementById(id).style.display = 'block';
        }
    </script>
</body>
</html>
