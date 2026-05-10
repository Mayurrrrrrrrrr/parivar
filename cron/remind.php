<?php
/**
 * कार्यक्रम स्मरण — cron job
 * Cron: 0 8 * * * php /var/www/html/parivar/cron/remind.php
 */
require_once __DIR__ . '/../config/db.php';

// अगले 7 दिनों के कार्यक्रम
$stmt = $pdo->prepare("
    SELECT k.*, v.pratham_naam, v.kul_naam, p.naam as parivar_naam
    FROM karyakram k
    LEFT JOIN vyakti v ON k.vyakti_id = v.id
    JOIN parivar p ON k.parivar_id = p.id
    WHERE k.tithi_gregorian BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
");
$stmt->execute();
$aane_wale = $stmt->fetchAll();

foreach ($aane_wale as $karyakram) {
    $daysLeft = round((strtotime($karyakram['tithi_gregorian']) - time()) / 86400);
    echo "📅 [{$karyakram['parivar_naam']}] {$karyakram['shirshak']} — {$daysLeft} दिन बाद ({$karyakram['tithi_gregorian']})\n";
    
    // Future: Add WhatsApp/Email notification logic here
}
