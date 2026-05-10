<?php
/**
 * डैशबोर्ड — मुख्य पृष्ठ
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();
$today_tithi = aajKiTithi();

// आज के कार्यक्रम
$stmt = $pdo->prepare("SELECT k.*, v.pratham_naam FROM karyakram k LEFT JOIN vyakti v ON k.vyakti_id = v.id WHERE k.parivar_id = ? AND k.tithi_gregorian = CURDATE()");
$stmt->execute([$parivar_id]);
$today_events = $stmt->fetchAll();

// आने वाले कार्यक्रम (७ दिन)
$stmt = $pdo->prepare("
    SELECT k.*, v.pratham_naam 
    FROM karyakram k 
    LEFT JOIN vyakti v ON k.vyakti_id = v.id 
    WHERE k.parivar_id = ? 
    AND k.tithi_gregorian BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY k.tithi_gregorian
");
$stmt->execute([$parivar_id]);
$upcoming_events = $stmt->fetchAll();

// ताज़ा फ़ीड
$stmt = $pdo->prepare("SELECT f.*, u.naam as user_naam FROM parivar_feed f JOIN users u ON f.user_id = u.id WHERE f.parivar_id = ? ORDER BY f.banaya_at DESC LIMIT 5");
$stmt->execute([$parivar_id]);
$feeds = $stmt->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
    <!-- पंचांग एवं आज के कार्यक्रम -->
    <div class="card">
        <h3>आज का पंचांग</h3>
        <div style="background: #FFF5E6; padding: 1rem; border-radius: 8px; border-left: 5px solid var(--rang-pramukh); margin-top: 0.5rem;">
            <p style="font-size: 1.2rem; font-weight: 600;"><?php echo date('d F Y'); ?></p>
            <p style="font-size: 1.4rem; color: var(--rang-pramukh); margin-top: 0.5rem;"><?php echo $today_tithi; ?></p>
        </div>

        <h3 style="margin-top: 2rem;">आज के कार्यक्रम</h3>
        <?php if (empty($today_events)): ?>
            <p style="color: #888;">आज कोई विशेष कार्यक्रम नहीं है।</p>
        <?php else: ?>
            <?php foreach ($today_events as $e): ?>
                <div style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                    <span style="font-size: 1.2rem;"><?php echo getEventIcon($e['prakar']); ?></span>
                    <strong><?php echo s($e['shirshak']); ?></strong>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- आने वाले कार्यक्रम -->
    <div class="card">
        <h3>आने वाले ७ दिन</h3>
        <?php if (empty($upcoming_events)): ?>
            <p style="color: #888;">अगले ७ दिनों में कोई कार्यक्रम नहीं है।</p>
        <?php else: ?>
            <div style="margin-top: 0.5rem;">
                <?php foreach ($upcoming_events as $e): ?>
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem 0; border-bottom: 1px dashed #ddd;">
                        <div style="text-align: center; min-width: 60px; background: #eee; padding: 0.3rem; border-radius: 4px;">
                            <span style="font-size: 0.8rem; display: block;"><?php echo date('M', strtotime($e['tithi_gregorian'])); ?></span>
                            <span style="font-size: 1.2rem; font-weight: bold;"><?php echo date('d', strtotime($e['tithi_gregorian'])); ?></span>
                        </div>
                        <div>
                            <strong><?php echo s($e['shirshak']); ?></strong><br>
                            <small style="color: #666;"><?php echo s($e['tithi_vs']); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- परिवार फ़ीड -->
<div class="card" style="margin-top: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>परिवार फ़ीड</h3>
        <a href="/pages/parivar_feed.php" style="color: var(--rang-pramukh); text-decoration: none;">सभी देखें →</a>
    </div>
    
    <?php if (empty($feeds)): ?>
        <p style="color: #888; text-align: center; padding: 2rem;">अभी तक कोई संदेश नहीं है।</p>
    <?php else: ?>
        <?php foreach ($feeds as $f): ?>
            <div class="feed-item">
                <div class="feed-header">
                    <div class="feed-avatar"><?php echo mb_substr($f['user_naam'], 0, 1); ?></div>
                    <div>
                        <strong><?php echo s($f['user_naam']); ?></strong><br>
                        <small style="color: #888;"><?php echo time_ago($f['banaya_at']); ?></small>
                    </div>
                </div>
                <div class="feed-content">
                    <p><?php echo nl2br(s($f['sandesh'])); ?></p>
                    <?php if ($f['photo_url']): ?>
                        <img src="/<?php echo $f['photo_url']; ?>" style="max-width: 100%; border-radius: 8px; margin-top: 0.5rem; max-height: 300px; object-fit: cover;">
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
