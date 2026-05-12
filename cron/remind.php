<?php
/**
 * कार्यक्रम स्मरण — Email Notifications
 * Cron: 0 8 * * * php /var/www/html/parivar/cron/remind.php
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/panchang.php';

// अगले 3 दिनों के recurring events
// Using the calculation logic provided in the prompt
$stmt = $pdo->prepare("
    SELECT k.*, v.pratham_naam, v.kul_naam,
    CASE 
        WHEN DATE_FORMAT(k.tithi_gregorian, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
        THEN DATE_FORMAT(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(k.tithi_gregorian, '%m-%d')), '%Y-%m-%d')
        ELSE DATE_FORMAT(CONCAT(YEAR(CURDATE())+1, '-', DATE_FORMAT(k.tithi_gregorian, '%m-%d')), '%Y-%m-%d')
    END as next_date
    FROM karyakram k
    LEFT JOIN vyakti v ON k.vyakti_id = v.id
    WHERE k.punravrutti = 1
    HAVING next_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ORDER BY next_date ASC
");
$stmt->execute();
$events = $stmt->fetchAll();

foreach ($events as $e) {
    $days_left = floor((strtotime($e['next_date']) - strtotime(date('Y-m-d'))) / 86400);
    $label = $days_left == 0 ? 'आज' : ($days_left == 1 ? 'कल' : $days_left . ' दिन बाद');
    
    // Log करो
    echo date('Y-m-d H:i:s') . " | {$e['shirshak']} — $label\n";
    
    // उस parivar के सभी users को email भेजो
    $users_stmt = $pdo->prepare("SELECT naam, email FROM users WHERE parivar_id = ? AND email IS NOT NULL");
    $users_stmt->execute([$e['parivar_id']]);
    $users = $users_stmt->fetchAll();
    
    foreach ($users as $u) {
        if (empty($u['email'])) continue;
        
        $subject = "🎉 {$e['shirshak']} — $label";
        $body = "नमस्ते {$u['naam']},\n\n";
        $body .= "{$e['shirshak']} {$label} है।\n";
        $body .= "तिथि: {$e['next_date']}\n";
        if ($e['tithi_vs']) $body .= "विक्रम संवत्: {$e['tithi_vs']}\n";
        $body .= "\nपरिवार पोर्टल: https://parivar.yuktaa.com\n";
        
        $headers = "From: noreply@yuktaa.com\r\nContent-Type: text/plain; charset=UTF-8";
        // Note: mail() will work on server if postfix/sendmail is configured
        mail($u['email'], $subject, $body, $headers);
    }
}

echo "✅ " . count($events) . " events processed.\n";
