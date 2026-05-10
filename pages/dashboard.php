<?php
/**
 * डैशबोर्ड — मुख्य पृष्ठ (v2.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();
$today = gregorianToVS((int)date('d'), (int)date('m'), (int)date('Y'));

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM vyakti WHERE parivar_id = ?");
$stmt->execute([$parivar_id]);
$member_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM karyakram WHERE parivar_id = ? AND MONTH(tithi_gregorian) = MONTH(CURDATE())");
$stmt->execute([$parivar_id]);
$month_event_count = $stmt->fetchColumn();

// Today's events
$stmt = $pdo->prepare("SELECT k.*, v.pratham_naam, v.kul_naam FROM karyakram k LEFT JOIN vyakti v ON k.vyakti_id = v.id WHERE k.parivar_id = ? AND k.tithi_gregorian = CURDATE()");
$stmt->execute([$parivar_id]);
$today_events = $stmt->fetchAll();

// Countdown Banner (Next 3 days)
$stmt = $pdo->prepare("
    SELECT k.*, v.pratham_naam, v.kul_naam, DATEDIFF(tithi_gregorian, CURDATE()) as days_left 
    FROM karyakram k 
    LEFT JOIN vyakti v ON k.vyakti_id = v.id 
    WHERE k.parivar_id = ? 
    AND tithi_gregorian BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ORDER BY tithi_gregorian LIMIT 1
");
$stmt->execute([$parivar_id]);
$countdown_event = $stmt->fetch();
?>

<header class="app-header">
    <h1>🏡 परिवार</h1>
    <div class="user-chip"><i class="ti ti-user" style="font-size:14px"></i> <?php echo s(getUserName()); ?></div>
</header>

<div class="page-content">

    <!-- Countdown Banner -->
    <?php if ($countdown_event): ?>
    <div style="background:#FAEEDA;border-radius:12px;padding:10px 14px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
        <span style="font-size:20px"><?php echo getEventIcon($countdown_event['prakar']); ?></span>
        <div>
            <div style="font-size:13px;font-weight:500;color:#854F0B">
                <?php echo s($countdown_event['pratham_naam'] ?: $countdown_event['shirshak']); ?> का जन्मदिन <?php echo $countdown_event['days_left']; ?> दिन में है!
            </div>
            <div style="font-size:11px;color:#9C7A6A"><?php echo s($countdown_event['tithi_vs']); ?> | <?php echo date('d M', strtotime($countdown_event['tithi_gregorian'])); ?></div>
        </div>
        <?php 
            $msg = urlencode("🙏 " . ($countdown_event['pratham_naam'] ?: $countdown_event['shirshak']) . " का जन्मदिन " . date('d M', strtotime($countdown_event['tithi_gregorian'])) . " (" . $countdown_event['tithi_vs'] . ") को है। परिवार की ओर से हार्दिक शुभकामनाएँ! 🎂");
            $wa_url = "https://wa.me/?text={$msg}";
        ?>
        <a href="<?php echo $wa_url; ?>" style="margin-left:auto;font-size:20px" target="_blank">📱</a>
    </div>
    <?php endif; ?>

    <!-- Panchang Hero -->
    <div class="panchang-card">
        <div class="greg-date"><?php echo date('d F Y'); ?></div>
        <div class="vs-date"><?php echo $today['formatted']; ?></div>
        <div class="nakshatra">नक्षत्र: <?php echo $today['nakshatra']; ?></div>
    </div>

    <!-- Stats row -->
    <div class="cards-grid">
        <div class="stat-card"><div class="stat-number"><?php echo $member_count; ?></div><div class="stat-label">परिवार सदस्य</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $month_event_count; ?></div><div class="stat-label">इस माह कार्यक्रम</div></div>
    </div>

    <!-- Today's events -->
    <div class="section-header"><span class="section-title">🎉 आज के कार्यक्रम</span></div>
    <?php if (empty($today_events)): ?>
        <p class="text-muted text-center" style="padding: 1rem; background: var(--bg-card); border-radius: 12px;">आज कोई विशेष कार्यक्रम नहीं है।</p>
    <?php else: ?>
        <?php foreach ($today_events as $e): ?>
            <a href="vyakti.php?id=<?php echo $e['vyakti_id']; ?>" class="event-card">
                <div class="avatar avatar-saffron"><?php echo mb_substr($e['pratham_naam'] ?: 'P', 0, 1); ?></div>
                <div class="dual-date">
                    <div class="greg"><?php echo s($e['shirshak']); ?></div>
                    <div class="vs"><?php echo s($e['tithi_vs']); ?></div>
                </div>
                <span class="countdown-chip chip-today">आज</span>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Recent Posts -->
    <div class="section-header">
        <span class="section-title">📢 परिवार फ़ीड</span>
        <a href="parivar_feed.php" class="section-link">सभी देखें</a>
    </div>
    <?php
    $stmt = $pdo->prepare("SELECT f.*, u.naam as user_naam FROM parivar_feed f JOIN users u ON f.user_id = u.id WHERE f.parivar_id = ? ORDER BY f.banaya_at DESC LIMIT 2");
    $stmt->execute([$parivar_id]);
    $feeds = $stmt->fetchAll();
    foreach ($feeds as $f): ?>
        <div class="feed-card">
            <div class="feed-card-header">
                <div class="avatar avatar-sm avatar-purple"><?php echo mb_substr($f['user_naam'], 0, 1); ?></div>
                <div>
                    <div style="font-size:13px;font-weight:500"><?php echo s($f['user_naam']); ?></div>
                    <div style="font-size:10px;color:var(--text-muted)"><?php echo time_ago($f['banaya_at']); ?></div>
                </div>
            </div>
            <div class="feed-card-body"><?php echo nl2br(s($f['sandesh'])); ?></div>
            <?php if ($f['photo_url']): ?>
                <img src="/parivar/<?php echo $f['photo_url']; ?>" class="feed-card-photo">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>

<!-- FAB -->
<a href="karyakram.php?action=new" class="fab"><i class="ti ti-plus"></i></a>

<!-- Bottom Nav -->
<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
