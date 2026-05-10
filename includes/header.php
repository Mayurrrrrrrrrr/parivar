<?php
/**
 * मुख्य हेडर — सभी पेजों के लिए
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/panchang.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>परिवार — प्रबंधन</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; padding: 0;">
            <div style="text-align: left;">
                <h1 style="font-size: 1.5rem; margin: 0;"><a href="/pages/dashboard.php" style="color: white;">परिवार</a></h1>
            </div>
            <div style="font-size: 0.9rem;">
                <i class="fa fa-user"></i> <?php echo s(getUserName()); ?> | 
                <a href="/logout.php" style="color: white; font-weight: normal;">लॉगआउट</a>
            </div>
        </div>
    </header>

    <nav style="background: var(--rang-uprang); color: white; padding: 0.5rem 0;">
        <div class="container" style="display: flex; gap: 1rem; overflow-x: auto; white-space: nowrap; padding: 0 1rem;">
            <a href="/pages/dashboard.php" style="color: white; font-weight: normal;">मुख्य</a>
            <a href="/pages/sadasy_banao.php" style="color: white; font-weight: normal;">+ सदस्य जोड़ें</a>
            <a href="/pages/vansh_vriksha.php" style="color: white; font-weight: normal;">वंश वृक्ष</a>
            <a href="/pages/karyakram.php" style="color: white; font-weight: normal;">कार्यक्रम</a>
            <a href="/pages/parivar_feed.php" style="color: white; font-weight: normal;">फ़ीड</a>
            <a href="/pages/settings.php" style="color: white; font-weight: normal;">सेटिंग्स</a>
        </div>
    </nav>

    <main class="container">
