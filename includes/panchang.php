<?php
/**
 * पंचांग — विक्रम संवत् ↔ ग्रेगोरियन कनवर्टर
 * rss-shakha-app के algorithm पर आधारित
 */

define('VS_GREGORIAN_OFFSET', 57);

$MASA_NAAM = [
    1 => 'चैत्र', 2 => 'वैशाख', 3 => 'ज्येष्ठ', 4 => 'आषाढ़',
    5 => 'श्रावण', 6 => 'भाद्रपद', 7 => 'आश्विन', 8 => 'कार्तिक',
    9 => 'मार्गशीर्ष', 10 => 'पौष', 11 => 'माघ', 12 => 'फाल्गुन'
];

$TITHI_NAAM = [
    1 => 'प्रतिपदा', 2 => 'द्वितीया', 3 => 'तृतीया', 4 => 'चतुर्थी',
    5 => 'पंचमी', 6 => 'षष्ठी', 7 => 'सप्तमी', 8 => 'अष्टमी',
    9 => 'नवमी', 10 => 'दशमी', 11 => 'एकादशी', 12 => 'द्वादशी',
    13 => 'त्रयोदशी', 14 => 'चतुर्दशी', 15 => 'पूर्णिमा'
];

$MASA_GREGORIAN_START = [
    1 => ['month' => 3, 'day' => 22],   // चैत्र
    2 => ['month' => 4, 'day' => 21],   // वैशाख
    3 => ['month' => 5, 'day' => 22],   // ज्येष्ठ
    4 => ['month' => 6, 'day' => 22],   // आषाढ़
    5 => ['month' => 7, 'day' => 23],   // श्रावण
    6 => ['month' => 8, 'day' => 23],   // भाद्रपद
    7 => ['month' => 9, 'day' => 23],   // आश्विन
    8 => ['month' => 10, 'day' => 24],  // कार्तिक
    9 => ['month' => 11, 'day' => 23],  // मार्गशीर्ष
    10 => ['month' => 12, 'day' => 22], // पौष
    11 => ['month' => 1, 'day' => 21],  // माघ
    12 => ['month' => 2, 'day' => 20],  // फाल्गुन
];

/**
 * ग्रेगोरियन → विक्रम संवत्
 */
function gregorianToVS(int $day, int $month, int $year): array {
    global $MASA_NAAM, $TITHI_NAAM;
    
    $jd = gregorianToJulian($day, $month, $year);
    $synodicMonth = 29.530588853;
    $refNewMoon = 2451549.952;
    
    $daysSinceRef = $jd - $refNewMoon;
    $monthsSinceRef = $daysSinceRef / $synodicMonth;
    $currentMonthFraction = $monthsSinceRef - floor($monthsSinceRef);
    
    $tithiNum = (int)($currentMonthFraction * 30) + 1;
    if ($tithiNum > 30) $tithiNum = 30;
    
    if ($tithiNum <= 15) {
        $paksha = 'शुक्ल';
        $tithiDisplay = $tithiNum;
    } else {
        $paksha = 'कृष्ण';
        $tithiDisplay = $tithiNum - 15;
    }
    
    $masaNum = approximateMasa($month, $day);
    $vsYear = ($month >= 4) ? ($year + 57) : ($year + 56);
    
    $tithiName = $TITHI_NAAM[$tithiDisplay] ?? $tithiDisplay;
    $masaName = $MASA_NAAM[$masaNum] ?? 'अज्ञात';
    
    return [
        'samvat' => $vsYear,
        'masa' => $masaName,
        'masa_num' => $masaNum,
        'paksha' => $paksha,
        'tithi' => $tithiName,
        'tithi_num' => $tithiDisplay,
        'formatted' => "$masaName $paksha $tithiName, वि.सं. $vsYear"
    ];
}

function gregorianToJulian(int $d, int $m, int $y): float {
    if ($m <= 2) { $y--; $m += 12; }
    $A = (int)($y / 100);
    $B = 2 - $A + (int)($A / 4);
    return (int)(365.25 * ($y + 4716)) + (int)(30.6001 * ($m + 1)) + $d + $B - 1524.5;
}

function approximateMasa(int $gMonth, int $gDay): int {
    $dayOfYear = mktime(0,0,0,$gMonth,$gDay,2000);
    $marEquinox = mktime(0,0,0,3,22,2000);
    $diff = ($dayOfYear - $marEquinox) / 86400;
    if ($diff < 0) $diff += 365;
    $masaNum = (int)($diff / 29.53) + 1;
    if ($masaNum > 12) $masaNum = 12;
    if ($masaNum < 1) $masaNum = 1;
    return $masaNum;
}

/**
 * आज की तिथि
 */
function aajKiTithi(): string {
    $result = gregorianToVS((int)date('d'), (int)date('m'), (int)date('Y'));
    return $result['formatted'];
}

/**
 * VS tithi string से अगली Gregorian date
 */
function agliTithiGregorian(string $masaName, string $paksha, string $tithiName): string {
    global $MASA_NAAM, $MASA_GREGORIAN_START, $TITHI_NAAM;
    
    $masaNum = array_search($masaName, $MASA_NAAM);
    if (!$masaNum) return date('Y-m-d'); 
    
    $start = $MASA_GREGORIAN_START[$masaNum];
    $year = date('Y');
    
    $pakshaMult = ($paksha === 'शुक्ल') ? 0 : 15;
    $tithiNums = array_flip($TITHI_NAAM);
    $tithiNum = ($tithiNums[$tithiName] ?? 1) + $pakshaMult;
    
    $baseDate = mktime(0, 0, 0, $start['month'], $start['day'], $year);
    $eventDate = $baseDate + ($tithiNum * 0.9856 * 86400); 
    
    if ($eventDate < time()) {
        $baseDate = mktime(0, 0, 0, $start['month'], $start['day'], $year + 1);
        $eventDate = $baseDate + ($tithiNum * 0.9856 * 86400);
    }
    
    return date('Y-m-d', $eventDate);
}
