<?php
/**
 * कार्यक्रम स्मरण (Event Reminder) — दैनिक रूप से चलाने के लिए
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/panchang.php';
require_once __DIR__ . '/../includes/helpers.php';

echo "📅 कार्यक्रम स्मरण जाँच शुरू...\n";

// आज के और अगले ३ दिन के कार्यक्रम
$target_date = date('Y-m-d');
$next_3_days = date('Y-m-d', strtotime('+3 days'));

$stmt = $pdo->prepare("SELECT k.*, p.naam as parivar_naam FROM karyakram k 
                       JOIN parivar p ON k.parivar_id = p.id 
                       WHERE k.tithi_gregorian BETWEEN ? AND ?");
$stmt->execute([$target_date, $next_3_days]);
$events = $stmt->fetchAll();

foreach ($events as $e) {
    echo "🔔 कार्यक्रम: {$e['shirshak']} ({$e['parivar_naam']}) - {$e['tithi_gregorian']}\n";
    
    // यहाँ आप Email या WhatsApp API कॉल कर सकते हैं
    // Phase 2 में हम केवल लॉग कर रहे हैं
}

// भविष्य की तिथियों के लिए पुनरावृत्ति (Tithi based)
// TODO: Implement auto-generation of next year's dates for tithi-based events
echo "✅ स्मरण जाँच पूरी हुई।\n";
