<?php
require_once __DIR__ . '/../config/db.php';

echo "रिफ्रेशिंग डेटाबेस...\n";

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $tables = [
        'sambandh',
        'vyakti_parivar',
        'parivar_feed_reactions',
        'parivar_feed',
        'karyakram',
        'vyakti',
        'users',
        'parivar'
    ];

    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
        echo "Truncated $table\n";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "\nसफलता! सभी डेटा हटा दिया गया है। अब आप नया परिवार और मुखिया बना सकते हैं।\n";
} catch (Exception $e) {
    echo "त्रुटि: " . $e->getMessage() . "\n";
}
