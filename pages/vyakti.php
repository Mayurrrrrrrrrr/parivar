<?php
/**
 * व्यक्ति प्रोफ़ाइल — विस्तार से जानकारी
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$parivar_id = currentParivarId();

$stmt = $pdo->prepare("SELECT * FROM vyakti WHERE id = ? AND parivar_id = ?");
$stmt->execute([$id, $parivar_id]);
$v = $stmt->fetch();

if (!$v) {
    echo "<div class='card'>व्यक्ति नहीं मिला।</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// संबंध प्राप्त करें
$stmt = $pdo->prepare("
    SELECT s.*, v.pratham_naam, v.kul_naam 
    FROM sambandh s 
    JOIN vyakti v ON s.vyakti_b_id = v.id 
    WHERE s.vyakti_a_id = ?
");
$stmt->execute([$id]);
$relations = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
        <div style="width: 200px; height: 200px; border-radius: 50%; overflow: hidden; border: 5px solid var(--rang-pramukh);">
            <?php if ($v['photo_url']): ?>
                <img src="/<?php echo $v['photo_url']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <div style="width: 100%; height: 100%; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 4rem; color: #ccc;">
                    <i class="fa fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <h1 style="color: var(--rang-kaala); margin-bottom: 0.5rem;"><?php echo s($v['pratham_naam'] . ' ' . $v['kul_naam']); ?></h1>
            <?php if ($v['upnaam']): ?><p>उपनाम: <strong><?php echo s($v['upnaam']); ?></strong></p><?php endif; ?>
            <p>लिंग: <strong><?php echo $v['ling'] == 'purush' ? 'पुरुष' : ($v['ling'] == 'stri' ? 'स्त्री' : 'अन्य'); ?></strong></p>
            <p>स्थिति: <span class="badge" style="background: <?php echo $v['jeevit'] ? 'var(--rang-safal)' : '#888'; ?>; color: white; padding: 0.2rem 0.5rem; border-radius: 4px;"><?php echo $v['jeevit'] ? 'जीवित' : 'दिवंगत'; ?></span></p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
    <div class="card">
        <h3>जन्म एवं परिचय</h3>
        <ul style="list-style: none; margin-top: 1rem;">
            <li style="margin-bottom: 0.8rem;">📅 <strong>जन्म तिथि:</strong> <?php echo $v['janm_tithi_gregorian'] ? date('d F Y', strtotime($v['janm_tithi_gregorian'])) : 'अज्ञात'; ?></li>
            <li style="margin-bottom: 0.8rem;">🌙 <strong>तिथि:</strong> <?php echo s($v['janm_tithi_vs'] ?: 'अज्ञात'); ?></li>
            <li style="margin-bottom: 0.8rem;">🔱 <strong>गोत्र:</strong> <?php echo s($v['gotra'] ?: 'अज्ञात'); ?></li>
            <li style="margin-bottom: 0.8rem;">✨ <strong>नक्षत्र:</strong> <?php echo s($v['nakshatra'] ?: 'अज्ञात'); ?></li>
            <li style="margin-bottom: 0.8rem;">♈ <strong>राशि:</strong> <?php echo s($v['rashi'] ?: 'अज्ञात'); ?></li>
        </ul>
    </div>

    <div class="card">
        <h3>पारिवारिक संबंध</h3>
        <div style="margin-top: 1rem;">
            <?php if (empty($relations)): ?>
                <p style="color: #888;">कोई संबंध दर्ज नहीं है।</p>
            <?php else: ?>
                <?php foreach ($relations as $r): ?>
                    <div style="padding: 0.8rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                        <span><?php echo s($r['pratham_naam'] . ' ' . $r['kul_naam']); ?></span>
                        <span style="color: var(--rang-pramukh); font-weight: 600;"><?php echo getRelationHindi($r['sambandh_prakar']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($v['jeevan_parichay']): ?>
    <div class="card">
        <h3>जीवन परिचय</h3>
        <p style="margin-top: 1rem; white-space: pre-line;"><?php echo s($v['jeevan_parichay']); ?></p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
