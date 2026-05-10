<?php
/**
 * गोत्र जाँच — सगोत्र विवाह निषेध हेतु उपकरण
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();

// व्यक्तियों की सूची
$stmt = $pdo->prepare("SELECT id, pratham_naam, kul_naam, gotra FROM vyakti WHERE parivar_id = ? AND gotra IS NOT NULL AND gotra != ''");
$stmt->execute([$parivar_id]);
$persons = $stmt->fetchAll();

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a_id = $_POST['vyakti_a'] ?? 0;
    $b_id = $_POST['vyakti_b'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM vyakti WHERE id = ?");
    $stmt->execute([$a_id]);
    $vA = $stmt->fetch();
    
    $stmt->execute([$b_id]);
    $vB = $stmt->fetch();
    
    if ($vA && $vB) {
        $same_gotra = (strtolower(trim($vA['gotra'])) === strtolower(trim($vB['gotra'])));
        $result = [
            'vA' => $vA,
            'vB' => $vB,
            'same' => $same_gotra
        ];
    }
}
?>

<div class="card">
    <h2>गोत्र जाँच (Gotra Check)</h2>
    <p style="color: #666; margin-bottom: 1.5rem;">दो व्यक्तियों के बीच सगोत्र (समान गोत्र) की जाँच करें।</p>

    <form method="POST" class="card" style="background: #f9f9f9;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>प्रथम व्यक्ति</label>
                <select name="vyakti_a" required>
                    <option value="">— चुनें —</option>
                    <?php foreach ($persons as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo s($p['pratham_naam'] . ' (' . $p['gotra'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>द्वितीय व्यक्ति</label>
                <select name="vyakti_b" required>
                    <option value="">— चुनें —</option>
                    <?php foreach ($persons as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo s($p['pratham_naam'] . ' (' . $p['gotra'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit">जाँच करें</button>
    </form>

    <?php if ($result): ?>
        <div class="card" style="margin-top: 2rem; text-align: center; border-width: 2px; border-color: <?php echo $result['same'] ? 'var(--rang-asafal)' : 'var(--rang-safal)'; ?>;">
            <h3>जाँच परिणाम</h3>
            <div style="display: flex; justify-content: space-around; align-items: center; margin: 1.5rem 0;">
                <div>
                    <strong style="font-size: 1.2rem;"><?php echo s($result['vA']['pratham_naam']); ?></strong><br>
                    गोत्र: <span style="color: var(--rang-pramukh);"><?php echo s($result['vA']['gotra']); ?></span>
                </div>
                <div style="font-size: 2rem;">↔</div>
                <div>
                    <strong style="font-size: 1.2rem;"><?php echo s($result['vB']['pratham_naam']); ?></strong><br>
                    गोत्र: <span style="color: var(--rang-pramukh);"><?php echo s($result['vB']['gotra']); ?></span>
                </div>
            </div>

            <?php if ($result['same']): ?>
                <div style="background: #fff5f5; color: var(--rang-asafal); padding: 1rem; border-radius: 8px;">
                    <i class="fa fa-times-circle" style="font-size: 2rem;"></i><br>
                    <strong>सगोत्र (समान गोत्र) पाया गया!</strong><br>
                    शास्त्रीय परंपरा के अनुसार सगोत्र विवाह वर्जित माना जाता है।
                </div>
            <?php else: ?>
                <div style="background: #f5fff5; color: var(--rang-safal); padding: 1rem; border-radius: 8px;">
                    <i class="fa fa-check-circle" style="font-size: 2rem;"></i><br>
                    <strong>भिन्न गोत्र!</strong><br>
                    दोनों व्यक्तियों का गोत्र अलग है।
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
