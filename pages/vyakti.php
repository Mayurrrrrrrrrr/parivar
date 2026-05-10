<?php
/**
 * व्यक्ति प्रोफ़ाइल (v2.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$parivar_id = currentParivarId();

$stmt = $pdo->prepare("SELECT * FROM vyakti WHERE id = ? AND parivar_id = ?");
$stmt->execute([$id, $parivar_id]);
$v = $stmt->fetch();

if (!$v) {
    echo "<div class='page-content'>व्यक्ति नहीं मिला।</div>";
    include '../includes/nav.php';
    exit;
}

$initials = mb_substr($v['pratham_naam'], 0, 1);
?>

<header class="app-header">
    <a href="dashboard.php" style="color:white"><i class="ti ti-arrow-left"></i></a>
    <h1>प्रोफ़ाइल</h1>
    <a href="sadasy_banao.php?id=<?php echo $id; ?>" style="color:white"><i class="ti ti-edit"></i></a>
</header>

<div class="profile-hero">
    <?php if ($v['photo_url']): ?>
        <img src="/parivar/<?php echo $v['photo_url']; ?>" class="avatar avatar-lg" style="object-fit: cover;">
    <?php else: ?>
        <div class="avatar avatar-lg avatar-saffron"><?php echo $initials; ?></div>
    <?php endif; ?>
    <div class="full-name"><?php echo s($v['pratham_naam'] . ' ' . ($v['madhya_naam'] ? $v['madhya_naam'] . ' ' : '') . $v['kul_naam']); ?></div>
    <div class="upnaam"><?php echo s($v['upnaam'] ?: 'निकनेम नहीं'); ?></div>
    <div style="margin-top: 8px;">
        <span class="gotra-badge"><i class="ti ti-flame" style="font-size:12px"></i> गोत्र: <?php echo s($v['gotra'] ?: 'अज्ञात'); ?></span>
        <span class="gotra-badge"><i class="ti ti-moon" style="font-size:12px"></i> नक्षत्र: <?php echo s($v['nakshatra'] ?: 'अज्ञात'); ?></span>
    </div>
</div>

<div class="page-content">
    <!-- Birth info with dual date -->
    <div class="event-card">
        <div class="avatar avatar-sm avatar-saffron"><i class="ti ti-cake"></i></div>
        <div class="dual-date">
            <div class="greg">जन्म: <?php echo $v['janm_tithi_gregorian'] ? date('d F Y', strtotime($v['janm_tithi_gregorian'])) : 'अज्ञात'; ?></div>
            <div class="vs"><?php echo s($v['janm_tithi_vs'] ?: 'विक्रम संवत् अज्ञात'); ?></div>
        </div>
    </div>

    <?php if ($v['phone'] || $v['email']): ?>
    <div class="section-header"><span class="section-title">📞 सम्पर्क जानकारी</span></div>
    <div class="card" style="background:var(--bg-card); border-radius:12px; padding:12px; border:0.5px solid var(--seemant);">
        <?php if ($v['phone']): ?>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <i class="ti ti-phone" style="color:var(--rang-pramukh)"></i>
                <span style="font-size:14px"><?php echo s($v['phone']); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($v['email']): ?>
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="ti ti-mail" style="color:var(--rang-pramukh)"></i>
                <span style="font-size:14px"><?php echo s($v['email']); ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Family relationships -->
    <div class="section-header"><span class="section-title">👨‍👩‍👧‍👦 परिवार के सदस्य</span></div>
    <?php
    $stmt = $pdo->prepare("SELECT s.*, v.pratham_naam, v.kul_naam, v.photo_url FROM sambandh s JOIN vyakti v ON s.vyakti_b_id = v.id WHERE s.vyakti_a_id = ?");
    $stmt->execute([$id]);
    $rels = $stmt->fetchAll();
    foreach ($rels as $r): ?>
        <a href="vyakti.php?id=<?php echo $r['vyakti_b_id']; ?>" class="person-card" style="margin-bottom:8px">
            <div class="avatar avatar-sm avatar-teal"><?php echo mb_substr($r['pratham_naam'], 0, 1); ?></div>
            <div class="person-info">
                <div class="name"><?php echo s($r['pratham_naam'] . ' ' . $r['kul_naam']); ?></div>
                <div class="meta"><?php echo getRelationHindi($r['sambandh_prakar']); ?></div>
            </div>
        </a>
    <?php endforeach; ?>

    <?php if ($v['jeevan_parichay']): ?>
    <div class="section-header"><span class="section-title">📖 जीवन परिचय</span></div>
    <div class="card" style="background:var(--bg-card); border-radius:12px; padding:14px; border:0.5px solid var(--seemant); font-size:14px; line-height:1.6;">
        <?php echo nl2br(s($v['jeevan_parichay'])); ?>
    </div>
    <?php endif; ?>

</div>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
