<?php
/**
 * सहायक कार्य (Helpers) — सामान्य उपयोग के फंक्शन्स
 */

/**
 * अंग्रेजी अंकों को हिंदी अंकों में बदलें
 */
function toHindiNumerals($number) {
    $hindi_numerals = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
    $str = (string)$number;
    $res = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $char = $str[$i];
        if (is_numeric($char)) {
            $res .= $hindi_numerals[$char];
        } else {
            $res .= $char;
        }
    }
    return $res;
}

/**
 * ग्रेगोरियन तारीख को हिंदी में दिखाएं (जैसे: १० मई २०२४)
 */
function formatGregorianHindi($date) {
    if (!$date) return '-';
    $ts = strtotime($date);
    $months = [
        1 => 'जनवरी', 2 => 'फ़रवरी', 3 => 'मार्च', 4 => 'अप्रैल',
        5 => 'मई', 6 => 'जून', 7 => 'जुलाई', 8 => 'अगस्त',
        9 => 'सितंबर', 10 => 'अक्टूबर', 11 => 'नवंबर', 12 => 'दिसंबर'
    ];
    $day = date('j', $ts);
    $month = $months[(int)date('n', $ts)];
    $year = date('Y', $ts);
    return toHindiNumerals($day) . ' ' . $month . ' ' . toHindiNumerals($year);
}

/**
 * इनपुट को सुरक्षित करें
 */
function s($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON रिस्पॉन्स भेजें
 */
function sendJson($data, $success = true, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'safalta' => $success,
        'data' => $data,
        'sandesh' => $message
    ]);
    exit;
}

/**
 * रैंडम कोड जेनरेट करें (परिवार कोड के लिए)
 */
function generateFamilyCode($length = 6) {
    return strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length));
}
