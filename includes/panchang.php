<?php
/**
 * पंचांग — विक्रम संवत् ↔ ग्रेगोरियन कनवर्टर
 * rss-shakha-app से लिया और पारिवार के लिए विस्तारित
 */

require_once __DIR__ . '/../config/db.php';

class PanchangCalculator {
    private $tithiNames = [
        1=>'प्रतिपदा', 2=>'द्वितीया', 3=>'तृतीया', 4=>'चतुर्थी',
        5=>'पंचमी', 6=>'षष्ठी', 7=>'सप्तमी', 8=>'अष्टमी',
        9=>'नवमी', 10=>'दशमी', 11=>'एकादशी', 12=>'द्वादशी',
        13=>'त्रयोदशी', 14=>'चतुर्दशी', 15=>'पूर्णिमा/अमावस्या'
    ];

    private $maahNames = [
        0=>'चैत्र', 1=>'वैशाख', 2=>'ज्येष्ठ', 3=>'आषाढ़',
        4=>'श्रावण', 5=>'भाद्रपद', 6=>'आश्विन', 7=>'कार्तिक',
        8=>'मार्गशीर्ष', 9=>'पौष', 10=>'माघ', 11=>'फाल्गुन'
    ];

    public function gregorianToJDN($year, $month, $day) {
        $a = intval((14 - $month) / 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;
        $jdn = $day + intval((153 * $m + 2) / 5) + 365 * $y
               + intval($y / 4) - intval($y / 100) + intval($y / 400) - 32045;
        return $jdn;
    }

    private function getSunLongitude($jdn) {
        $n = $jdn - 2451545.0;
        $L = fmod(280.46646 + 0.9856474 * $n, 360);
        $g = fmod(357.52911 + 0.9856003 * $n, 360);
        $gRad = deg2rad($g);
        $C = (1.914602 - 0.004817 * ($n / 36525)) * sin($gRad)
           + 0.019993 * sin(2 * $gRad)
           + 0.000289 * sin(3 * $gRad);
        return fmod($L + $C + 360, 360);
    }

    private function getMoonLongitude($jdn) {
        $n = $jdn - 2451545.0;
        $L0 = fmod(218.3165 + 13.17639648 * $n, 360);
        $M  = fmod(134.9634 + 13.06499295 * $n, 360);
        $F  = fmod(93.2721 + 13.22935020 * $n, 360);
        $Ms = fmod(357.5291 + 0.98560028 * $n, 360);
        $MRad  = deg2rad($M);
        $FRad  = deg2rad($F);
        $MsRad = deg2rad($Ms);
        $L0Rad = deg2rad($L0);
        $correction = 6.289 * sin($MRad)
                    - 1.274 * sin(2 * $L0Rad - $MRad)
                    + 0.658 * sin(2 * $L0Rad)
                    - 0.214 * sin(2 * $MRad)
                    - 0.186 * sin($MsRad)
                    - 0.114 * sin(2 * $FRad)
                    + 0.059 * sin(2 * $L0Rad - 2 * $MsRad)
                    + 0.057 * sin(2 * $L0Rad - $MRad - $MsRad);
        return fmod($L0 + $correction + 360, 360);
    }

    public function calculateTithi($jdn) {
        $sunLon  = $this->getSunLongitude($jdn);
        $moonLon = $this->getMoonLongitude($jdn);
        $diff = fmod($moonLon - $sunLon + 360, 360);
        $tithiNum = intval($diff / 12) + 1; // 1 to 30
        $paksha = ($tithiNum <= 15) ? 'शुक्ल' : 'कृष्ण';
        $tithiIndex = ($tithiNum <= 15) ? $tithiNum : $tithiNum - 15;
        
        if ($tithiNum == 15) {
            $tithiName = 'पूर्णिमा';
        } elseif ($tithiNum == 30) {
            $tithiName = 'अमावस्या';
        } else {
            $tithiName = $this->tithiNames[$tithiIndex] ?? 'अज्ञात';
        }

        return [
            'tithi_num' => $tithiNum,
            'tithi_index' => $tithiIndex,
            'tithi_name' => $tithiName,
            'paksha' => $paksha
        ];
    }

    public function calculateSamvat($gYear, $gMonth, $gDay) {
        // Approx conversion: VS = Gregorian + 57 (approx)
        // More accurately, VS starts in March/April
        if ($gMonth >= 4 || ($gMonth == 3 && $gDay >= 22)) {
            $vikramSamvat = $gYear + 57;
        } else {
            $vikramSamvat = $gYear + 56;
        }

        $dayOfYear = date('z', mktime(0, 0, 0, $gMonth, $gDay, $gYear));
        $lunarOffset = ($dayOfYear - 80 + 366) % 366;
        $maahIndex = intval($lunarOffset / 30.44) % 12;
        $maahName  = $this->maahNames[$maahIndex];

        return [
            'vikram_samvat' => $vikramSamvat,
            'maah' => $maahName
        ];
    }

    public function getPanchang($date) {
        $ts = strtotime($date);
        $y = (int)date('Y', $ts);
        $m = (int)date('m', $ts);
        $d = (int)date('d', $ts);

        $jdn = $this->gregorianToJDN($y, $m, $d);
        $tithi = $this->calculateTithi($jdn);
        $samvat = $this->calculateSamvat($y, $m, $d);

        return [
            'samvat' => $samvat['vikram_samvat'],
            'maah' => $samvat['maah'],
            'paksha' => $tithi['paksha'],
            'tithi' => $tithi['tithi_name'],
            'formatted' => $samvat['maah'] . ' ' . $tithi['paksha'] . ' ' . $tithi['tithi_name'] . ', वि.सं. ' . $samvat['vikram_samvat']
        ];
    }
}

/**
 * ग्रेगोरियन → विक्रम संवत्
 */
function gregorianToVS(int $day, int $month, int $year): array {
    $calc = new PanchangCalculator();
    return $calc->getPanchang("$year-$month-$day");
}

/**
 * विक्रम संवत् तिथि → ग्रेगोरियन (Search method)
 * Returns: DATE string YYYY-MM-DD
 */
function vsToGregorian(string $tithi, string $paksha, string $masa, int $samvat_year): string {
    $calc = new PanchangCalculator();
    // Samvat year N corresponds roughly to Gregorian year N-57
    $gYear = $samvat_year - 57;
    
    // Search within +/- 15 days of the estimated date
    // For simplicity in Phase 2, we search the entire year or a range
    // Start from March of gYear to March of gYear+1
    $start = strtotime(($gYear) . "-03-01");
    $end = strtotime(($gYear + 1) . "-05-01");
    
    for ($i = $start; $i <= $end; $i += 86400) {
        $d = date('Y-m-d', $i);
        $p = $calc->getPanchang($d);
        if ($p['maah'] === $masa && $p['paksha'] === $paksha && $p['tithi'] === $tithi) {
            return $d;
        }
    }
    return "";
}

/**
 * आज की तिथि विक्रम संवत् में
 */
function aajKiTithi(): string {
    $calc = new PanchangCalculator();
    $res = $calc->getPanchang(date('Y-m-d'));
    return $res['formatted'];
}

/**
 * अगली बार कब होगी यह तिथि — ग्रेगोरियन में
 */
function agliTithiGregorian(string $masa, string $paksha, string $tithi): string {
    $currentSamvat = (new PanchangCalculator())->calculateSamvat((int)date('Y'), (int)date('m'), (int)date('d'))['vikram_samvat'];
    
    // Check current samvat
    $d = vsToGregorian($tithi, $paksha, $masa, $currentSamvat);
    if ($d && $d >= date('Y-m-d')) return $d;
    
    // Check next samvat
    return vsToGregorian($tithi, $paksha, $masa, $currentSamvat + 1);
}
