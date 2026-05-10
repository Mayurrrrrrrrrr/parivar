<?php
/**
 * सहायक कार्य (Helpers) — परिवार पोर्टल
 */

/**
 * XSS सुरक्षा
 */
function s($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * यूजर का नाम प्राप्त करें
 */
function getUserName() {
    return $_SESSION['naam'] ?? 'यूजर';
}

/**
 * परिवार आईडी (Shortcut)
 */
function getParivarId() {
    return $_SESSION['parivar_id'] ?? 0;
}

/**
 * समय अंतराल (Time Ago)
 */
function time_ago($timestamp) {
    $time = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'अभी';
    if ($diff < 3600) return round($diff/60) . ' मिनट पहले';
    if ($diff < 86400) return round($diff/3600) . ' घंटे पहले';
    if ($diff < 2592000) return round($diff/86400) . ' दिन पहले';
    
    return date('d M Y', $time);
}

/**
 * कार्यक्रम का आइकन
 */
function getEventIcon($type) {
    $icons = [
        'janmdin' => '🎂',
        'vivah_varshgaanth' => '💍',
        'punya_tithi' => '🙏',
        'puja' => '✨',
        'utsav' => '🚩',
        'any' => '📅'
    ];
    return $icons[$type] ?? '📅';
}

/**
 * संबंधों का हिंदी नाम
 */
function getRelationHindi($type) {
    $relations = [
        'pita' => 'पिता',
        'mata' => 'माता',
        'pati' => 'पति',
        'patni' => 'पत्नी',
        'bhai' => 'भाई',
        'behen' => 'बहन',
        'putra' => 'पुत्र',
        'putri' => 'पुत्री',
        'datta_putra' => 'दत्तक पुत्र',
        'datta_putri' => 'दत्तक पुत्री'
    ];
    return $relations[$type] ?? $type;
}

/**
 * रीडायरेक्ट
 */
function redirect($url) {
    header("Location: $url");
    exit;
}
/**
 * त्रुटि संदेश (Error Messages)
 */
function getErrorMessage($code) {
    $errors = [
        'khaali_fields' => 'कृपया सभी जानकारी भरें।',
        'galat_login' => 'गलत ईमेल या पासवर्ड।',
        'banao_fail' => 'नया परिवार बनाने में त्रुटि हुई।',
        'code_galat' => 'परिवार कोड अमान्य है।',
        'csrf_fail' => 'सुरक्षा टोकन अमान्य है। कृपया पुनः प्रयास करें।'
    ];
    return $errors[$code] ?? 'कुछ गलत हुआ।';
}
