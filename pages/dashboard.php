<?php
/**
 * मुख्य डैशबोर्ड — आज का पंचांग और आगामी कार्यक्रम
 */
require_once __DIR__ . '/../includes/header.php';

$parivar_id = getParivarId();
$aaj_ki_tithi_full = (new PanchangCalculator())->getPanchang(date('Y-m-d'));
$aaj_ki_tithi = $aaj_ki_tithi_full['formatted'];

// कुलदेवी स्मरण (Navratri check)
$navratri_msg = '';
if ($aaj_ki_tithi_full['paksha'] === 'शुक्ल' && in_array($aaj_ki_tithi_full['maah'], ['चैत्र', 'आश्विन'])) {
    $tithi_idx = $aaj_ki_tithi_full['tithi'];
    $navratri_tithis = ['प्रतिपदा', 'द्वितीया', 'तृतीया', 'चतुर्थी', 'पंचमी', 'षष्ठी', 'सप्तमी', 'अष्टमी', 'नवमी'];
    if (in_array($tithi_idx, $navratri_tithis)) {
        $stmt = $pdo->prepare("SELECT kuldevi FROM parivar WHERE id = ?");
        $stmt->execute([$parivar_id]);
        $kd = $stmt->fetchColumn();
        if ($kd) {
            $navratri_msg = "🙏 कुलदेवी <strong>" . s($kd) . "</strong> को प्रणाम — " . $aaj_ki_tithi_full['maah'] . " नवरात्रि का शुभ अवसर";
        }
    }
}

// आज के कार्यक्रम
$stmt = $pdo->prepare("SELECT k.*, v.pratham_naam, v.kul_naam FROM karyakram k 
                       LEFT JOIN vyakti v ON k.vyakti_id = v.id 
                       WHERE k.parivar_id = ? AND k.tithi_gregorian = CURDATE()");
$stmt->execute([$parivar_id]);
$aaj_ke_karyakram = $stmt->fetchAll();

// आने वाले ७ दिन के कार्यक्रम
$stmt = $pdo->prepare("SELECT k.*, v.pratham_naam, v.kul_naam FROM karyakram k 
                       LEFT JOIN vyakti v ON k.vyakti_id = v.id 
                       WHERE k.parivar_id = ? AND k.tithi_gregorian > CURDATE() 
                       AND k.tithi_gregorian <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                       ORDER BY k.tithi_gregorian ASC");
$stmt->execute([$parivar_id]);
$aagami_karyakram = $stmt->fetchAll();
?>

<div class="card" style="background: linear-gradient(135deg, var(--rang-pramukh), var(--rang-uprang)); color: white;">
    <h2>आज का पंचांग</h2>
    <p style="font-size: 1.2rem;"><?php echo formatGregorianHindi(date('Y-m-d')); ?></p>
    <p style="font-size: 1.5rem; font-weight: bold; margin-top: 0.5rem;">
        <i class="fa fa-calendar-day"></i> <?php echo s($aaj_ki_tithi); ?>
    </p>
    <?php if ($navratri_msg): ?>
        <div style="margin-top: 1rem; padding: 0.8rem; background: rgba(255,255,255,0.2); border-radius: 8px; border: 1px solid rgba(255,255,255,0.3);">
            <?php echo $navratri_msg; ?>
        </div>
    <?php endif; ?>
</div>

<div class="stat-grid">
    <div class="card" style="margin-bottom: 0;">
        <h3>आज के कार्यक्रम</h3>
        <?php if (empty($aaj_ke_karyakram)): ?>
            <p>आज कोई कार्यक्रम नहीं है।</p>
        <?php else: ?>
            <ul style="list-style: none;">
                <?php foreach ($aaj_ke_karyakram as $k): ?>
                    <li style="margin-bottom: 0.5rem;">
                        <strong><?php echo s($k['shirshak']); ?></strong> 
                        <?php if ($k['vyakti_id']): ?>
                            — <?php echo s($k['pratham_naam']); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <div class="card" style="margin-bottom: 0;">
        <h3>आने वाले ७ दिन</h3>
        <?php if (empty($aagami_karyakram)): ?>
            <p>अगले ७ दिनों में कोई कार्यक्रम नहीं है।</p>
        <?php else: ?>
            <ul style="list-style: none;">
                <?php foreach ($aagami_karyakram as $k): ?>
                    <li style="margin-bottom: 0.5rem; border-bottom: 1px solid var(--rang-seemant); padding-bottom: 0.3rem;">
                        <span style="color: var(--rang-pramukh); font-size: 0.8rem;">
                            <?php echo formatGregorianHindi($k['tithi_gregorian']); ?>
                        </span><br>
                        <strong><?php echo s($k['shirshak']); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3>परिवार फ़ीड (ताज़ा पोस्ट)</h3>
    <div id="quick-feed">
        <p>लोड हो रहा है...</p>
    </div>
    <p style="text-align: right; margin-top: 1rem;">
        <a href="parivar_feed.php">सभी पोस्ट देखें <i class="fa fa-arrow-right"></i></a>
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/api/feed.php?action=list&limit=5')
            .then(r => r.json())
            .then(res => {
                const container = document.getElementById('quick-feed');
                if (res.safalta && res.data.length > 0) {
                    container.innerHTML = res.data.map(p => `
                        <div class="feed-post">
                            <div class="feed-meta"><strong>${p.user_naam}</strong> | ${p.banaya_at}</div>
                            <div>${p.sandesh}</div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p>अभी तक कोई पोस्ट नहीं है।</p>';
                }
            });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
