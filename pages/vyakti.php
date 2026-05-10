<?php
/**
 * व्यक्ति प्रोफ़ाइल — विस्तृत जानकारी और संबंध
 */
require_once __DIR__ . '/../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "व्यक्ति आईडी आवश्यक है।";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vyakti WHERE id = ? AND parivar_id = ?");
$stmt->execute([$id, getParivarId()]);
$v = $stmt->fetch();

if (!$v) {
    echo "व्यक्ति नहीं मिला।";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// संबंध प्राप्त करें
$stmt = $pdo->prepare("SELECT s.*, v.pratham_naam, v.kul_naam 
                       FROM sambandh s 
                       JOIN vyakti v ON s.vyakti_b_id = v.id 
                       WHERE s.vyakti_a_id = ?");
$stmt->execute([$id]);
$sambandh = $stmt->fetchAll();
?>

<div class="card" style="display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap;">
    <div style="flex: 0 0 150px; text-align: center;">
        <div style="width: 150px; height: 150px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 4rem; color: #ccc; overflow: hidden; border: 4px solid var(--rang-pramukh);">
            <?php if ($v['photo_url']): ?>
                <img src="<?php echo s($v['photo_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <i class="fa fa-user"></i>
            <?php endif; ?>
        </div>
        <div style="margin-top: 1rem;">
            <span class="badge" style="background: <?php echo $v['jeevit'] ? 'var(--rang-safal)' : '#888'; ?>; color: white; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem;">
                <?php echo $v['jeevit'] ? 'जीवित' : 'दिवंगत'; ?>
            </span>
        </div>
    </div>
    
    <div style="flex: 1; min-width: 300px;">
        <h1 style="margin-bottom: 0.2rem;"><?php echo s($v['pratham_naam'] . ' ' . ($v['madhya_naam'] ? $v['madhya_naam'] . ' ' : '') . $v['kul_naam']); ?></h1>
        <?php if ($v['upnaam']): ?>
            <p style="color: #666; font-style: italic;">(उपनाम: <?php echo s($v['upnaam']); ?>)</p>
        <?php endif; ?>
        
        <div style="margin-top: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <strong>जन्म:</strong> <?php echo formatGregorianHindi($v['janm_tithi_gregorian']); ?>
                <?php if ($v['janm_tithi_vs']): ?>
                    <br><small style="color: #888;"><?php echo s($v['janm_tithi_vs']); ?></small>
                <?php endif; ?>
            </div>
            <?php if (!$v['jeevit']): ?>
            <div>
                <strong>मृत्यु:</strong> <?php echo formatGregorianHindi($v['mrityu_tithi_gregorian']); ?>
            </div>
            <?php endif; ?>
            <div>
                <strong>गोत्र:</strong> <?php echo s($v['gotra'] ?? '-'); ?>
            </div>
            <div>
                <strong>नक्षत्र/राशि:</strong> <?php echo s(($v['nakshatra'] ?? '-') . ' / ' . ($v['rashi'] ?? '-')); ?>
            </div>
        </div>
    </div>
</div>

<div class="stat-grid">
    <div class="card" style="margin-bottom: 0;">
        <h3>पारिवारिक संबंध</h3>
        <?php if (empty($sambandh)): ?>
            <p>कोई संबंध दर्ज नहीं है।</p>
        <?php else: ?>
            <ul style="list-style: none;">
                <?php foreach ($sambandh as $s): ?>
                    <li style="margin-bottom: 0.5rem;">
                        <span style="color: var(--rang-pramukh); width: 80px; display: inline-block;">
                            <?php 
                                $prakar = [
                                    'pita'=>'पिता', 'mata'=>'माता', 'pati'=>'पति', 'patni'=>'पत्नी',
                                    'bhai'=>'भाई', 'behen'=>'बहन', 'putra'=>'पुत्र', 'putri'=>'पुत्री'
                                ];
                                echo $prakar[$s['sambandh_prakar']] ?? $s['sambandh_prakar'];
                            ?>:
                        </span>
                        <a href="vyakti.php?id=<?php echo $s['vyakti_b_id']; ?>">
                            <?php echo s($s['pratham_naam'] . ' ' . $s['kul_naam']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <h3>जीवन परिचय</h3>
        <p><?php echo nl2br(s($v['jeevan_parichay'] ?? 'विवरण उपलब्ध नहीं है।')); ?></p>
    </div>
</div>

<?php if (isMukhya()): ?>
<div class="card" style="margin-top: 1.5rem; border-color: var(--rang-pramukh);">
    <h3>सम्पर्क जानकारी (केवल मुख्य सदस्य को दृश्य)</h3>
    <p><strong>फ़ोन:</strong> <?php echo s($v['phone'] ?? '-'); ?></p>
    <p><strong>ईमेल:</strong> <?php echo s($v['email'] ?? '-'); ?></p>
    <p><strong>वर्तमान शहर:</strong> <?php echo s($v['vartaman_sheher'] ?? '-'); ?></p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
