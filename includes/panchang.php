<?php
/**
 * पंचांग — विक्रम संवत् ↔ ग्रेगोरियन
 * Julian Day Number based lunar calculation
 */

$MASA_NAAM = [
    1=>'चैत्र',2=>'वैशाख',3=>'ज्येष्ठ',4=>'आषाढ़',
    5=>'श्रावण',6=>'भाद्रपद',7=>'आश्विन',8=>'कार्तिक',
    9=>'मार्गशीर्ष',10=>'पौष',11=>'माघ',12=>'फाल्गुन'
];
$TITHI_NAAM = [
    1=>'प्रतिपदा',2=>'द्वितीया',3=>'तृतीया',4=>'चतुर्थी',
    5=>'पंचमी',6=>'षष्ठी',7=>'सप्तमी',8=>'अष्टमी',
    9=>'नवमी',10=>'दशमी',11=>'एकादशी',12=>'द्वादशी',
    13=>'त्रयोदशी',14=>'चतुर्दशी',15=>'पूर्णिमा',30=>'अमावस्या'
];
$NAKSHATRA_NAAM = [
    1=>'अश्विनी',2=>'भरणी',3=>'कृत्तिका',4=>'रोहिणी',5=>'मृगशिरा',
    6=>'आर्द्रा',7=>'पुनर्वसु',8=>'पुष्य',9=>'आश्लेषा',10=>'मघा',
    11=>'पूर्वाफाल्गुनी',12=>'उत्तराफाल्गुनी',13=>'हस्त',14=>'चित्रा',
    15=>'स्वाति',16=>'विशाखा',17=>'अनुराधा',18=>'ज्येष्ठा',19=>'मूल',
    20=>'पूर्वाषाढ़ा',21=>'उत्तराषाढ़ा',22=>'श्रवण',23=>'धनिष्ठा',
    24=>'शतभिषा',25=>'पूर्वाभाद्रपद',26=>'उत्तराभाद्रपद',27=>'रेवती'
];
$RASHI_NAAM = [
    1=>'मेष',2=>'वृषभ',3=>'मिथुन',4=>'कर्क',5=>'सिंह',6=>'कन्या',
    7=>'तुला',8=>'वृश्चिक',9=>'धनु',10=>'मकर',11=>'कुंभ',12=>'मीन'
];

function toJD(int $d, int $m, int $y): float {
    if($m<=2){$y--;$m+=12;}
    $A=(int)($y/100); $B=2-$A+(int)($A/4);
    return (int)(365.25*($y+4716))+(int)(30.6001*($m+1))+$d+$B-1524.5;
}

function gregorianToVS(int $d, int $m, int $y): array {
    global $MASA_NAAM,$TITHI_NAAM,$NAKSHATRA_NAAM;
    $jd = toJD($d,$m,$y);
    // Tithi from synodic month
    $refNM = 2451549.952; // Known new moon JD
    $syn = 29.530588853;
    $elapsed = fmod($jd - $refNM, $syn);
    if($elapsed < 0) $elapsed += $syn;
    $tithiSeq = (int)($elapsed / ($syn/30)) + 1;
    if($tithiSeq > 30) $tithiSeq = 30;
    if($tithiSeq <= 15){$paksha='शुक्ल';$tithiN=$tithiSeq;}
    else{$paksha='कृष्ण';$tithiN=$tithiSeq-15;}
    if($tithiN==15 && $paksha=='कृष्ण') $tithiN=30;
    // VS Year
    $vsYear=($m>=4)?($y+57):($y+56);
    // Masa from solar longitude (approximate)
    $sunLon = fmod(280.46646 + 0.9856474*($jd-2451545.0), 360);
    if($sunLon<0) $sunLon+=360;
    $masaNum = (int)($sunLon/30)+1;
    if($masaNum>12) $masaNum=1;
    // Nakshatra from moon longitude
    $moonLon = fmod(218.3165 + 13.1763966*($jd-2451545.0), 360);
    if($moonLon<0) $moonLon+=360;
    $nakshatraNum = (int)($moonLon/(360/27))+1;
    if($nakshatraNum>27) $nakshatraNum=27;
    $tithiName = $TITHI_NAAM[$tithiN] ?? (string)$tithiN;
    $masaName  = $MASA_NAAM[$masaNum] ?? 'अज्ञात';
    $nakName   = $NAKSHATRA_NAAM[$nakshatraNum] ?? 'अज्ञात';
    return [
        'samvat'    => $vsYear,
        'masa'      => $masaName,
        'masa_num'  => $masaNum,
        'paksha'    => $paksha,
        'tithi'     => $tithiName,
        'tithi_num' => $tithiN,
        'nakshatra' => $nakName,
        'formatted' => "$masaName $paksha $tithiName, वि.सं. $vsYear",
        'short'     => "$masaName $paksha $tithiName"
    ];
}

function aajKiTithi(): string {
    $r = gregorianToVS((int)date('d'),(int)date('m'),(int)date('Y'));
    return $r['formatted'];
}

function vsYear(int $gregYear, int $month): int {
    return ($month >= 4) ? ($gregYear + 57) : ($gregYear + 56);
}

/**
 * किसी VS tithi की इस साल या अगले साल की Gregorian date
 * Used for tithi_varshik recurring events
 * @param string $tithi_vs e.g. "पौष कृष्ण एकादशी, वि.सं. २०७४"
 * @return string YYYY-MM-DD
 */
function getTithiNextGregorian(string $tithi_vs): string {
    global $MASA_NAAM, $TITHI_NAAM;
    
    // Parse करो masa और paksha tithi को string से
    foreach ($MASA_NAAM as $num => $masa) {
        if (strpos($tithi_vs, $masa) !== false) {
            $masa_num = $num;
            $masa_name = $masa;
            break;
        }
    }
    if (!isset($masa_num)) return date('Y-m-d');
    
    $paksha = (strpos($tithi_vs, 'शुक्ल') !== false) ? 'शुक्ल' : 'कृष्ण';
    
    // Masa → approximate Gregorian month (lunar month starts)
    $masa_to_greg_month = [
        1=>3, 2=>4, 3=>5, 4=>6, 5=>7, 6=>8,
        7=>9, 8=>10, 9=>11, 10=>12, 11=>1, 12=>2
    ];
    
    $greg_month = $masa_to_greg_month[$masa_num];
    $year = date('Y');
    
    // Paksha offset
    $paksha_offset = ($paksha === 'शुक्ल') ? 0 : 15;
    
    // Approximate date: masa start + tithi offset
    $base_ts = mktime(0, 0, 0, $greg_month, 1, $year);
    $approx_ts = $base_ts + ($paksha_offset * 86400);
    
    // अगर बीत गई तो अगले साल
    if ($approx_ts < strtotime('today')) {
        $base_ts = mktime(0, 0, 0, $greg_month, 1, $year + 1);
        $approx_ts = $base_ts + ($paksha_offset * 86400);
    }
    
    return date('Y-m-d', $approx_ts);
}
