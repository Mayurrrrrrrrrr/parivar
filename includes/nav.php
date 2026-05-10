<?php /** Navigation — परिवार पोर्टल */ ?>
<nav class="main-nav">
    <a href="/pages/dashboard.php">🏠 मुख्य पृष्ठ</a>
    <a href="/pages/sadasy_banao.php">➕ सदस्य जोड़ें</a>
    <a href="/pages/vansh_vriksha.php">🌳 वंश वृक्ष</a>
    <a href="/pages/karyakram.php">📅 कार्यक्रम</a>
    <a href="/pages/parivar_feed.php">📢 परिवार फ़ीड</a>
    <a href="/pages/gotra_check.php">🔍 गोत्र जाँच</a>
    <?php if (isMukhya()): ?>
    <a href="/pages/settings.php">⚙️ सेटिंग्स</a>
    <?php endif; ?>
    <a href="/logout.php">🚪 लॉगआउट</a>
</nav>
