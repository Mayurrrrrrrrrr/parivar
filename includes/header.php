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

    <?php require_once __DIR__ . '/nav.php'; ?>

    <main class="container">
