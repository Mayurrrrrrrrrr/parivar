<?php
/**
 * मुख्य हेडर — सभी पेजों के लिए
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/panchang.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>परिवार — आपका डिजिटल कुल</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <!-- App Styles -->
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">

    <script>
        // Theme & Accessibility Persistence
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            const senior = localStorage.getItem('senior_mode') === 'true';
            document.documentElement.setAttribute('data-theme', theme);
            if (senior) document.documentElement.classList.add('senior-mode');
        })();
    </script>
    
    <!-- Transliteration (Hindi Typing) -->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("elements", "1", { packages: "transliteration" });
      function onLoad() {
        var options = {
            sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
            destinationLanguage: [google.elements.transliteration.LanguageCode.HINDI],
            shortcutKey: 'ctrl+g',
            transliterationEnabled: true
        };
        var control = new google.elements.transliteration.TransliterationControl(options);
        var ids = document.querySelectorAll('.hindi-type');
        control.makeTransliteratable(ids);
      }
      google.setOnLoadCallback(onLoad);
    </script>

    <!-- App Styles -->
    <link rel="stylesheet" href="/parivar/assets/css/style.css">
</head>
<body>

<div class="access-bar">
    <div class="circle-btn" onclick="toggleSeniorMode()" title="बड़ा फॉन्ट (Senior Mode)">
        <i class="ti ti-typography"></i>
    </div>
    <div class="circle-btn" onclick="toggleTheme()" id="theme-btn">
        <i class="ti ti-moon"></i>
    </div>
</div>

<script>
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
        document.getElementById('theme-btn').innerHTML = theme === 'dark' ? '<i class="ti ti-sun"></i>' : '<i class="ti ti-moon"></i>';
    }
    updateThemeIcon();
</script>
