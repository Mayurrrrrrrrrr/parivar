<?php
/**
 * मुख्य डैशबोर्ड (v2.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM vyakti WHERE parivar_id = ?");
$stmt->execute([$parivar_id]);
$total_members = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM karyakram WHERE parivar_id = ? AND tithi_gregorian >= CURDATE() AND tithi_gregorian <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute([$parivar_id]);
$month_events = $stmt->fetchColumn();

// Upcoming
$stmt = $pdo->prepare("SELECT * FROM karyakram WHERE parivar_id = ? AND tithi_gregorian >= CURDATE() ORDER BY tithi_gregorian ASC LIMIT 3");
$stmt->execute([$parivar_id]);
$upcoming = $stmt->fetchAll();

// Panchang Info (Today)
$today = date('Y-m-d');
$panchang_data = [];
$ch = curl_init("https://parivar.yuktaa.com/api/panchang.php?action=convert&gregorian=$today");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
if ($res) {
    $pj = json_decode($res, true);
    if ($pj && $pj['safalta']) $panchang_data = $pj['data'];
}
curl_close($ch);
?>

<header class="app-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="avatar avatar-saffron">प</div>
        <h1 style="font-size: 1.2rem;">नमस्ते, <?php echo s($_SESSION['naam']); ?></h1>
    </div>
    <a href="/pages/settings.php" style="color:var(--text-main); font-size:22px;"><i class="ti ti-settings-2"></i></a>
</header>

<div class="page-content">

    <!-- Hero Card -->
    <div class="card" style="background: linear-gradient(135deg, var(--rang-pramukh), #FF9F1C); border: none; color: white; position: relative; overflow: hidden; padding: 24px;">
        <div style="position: relative; z-index: 2;">
            <p style="font-size: 12px; font-weight: 600; opacity: 0.8; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 1px;">आज की तिथि</p>
            <h2 style="color:white; font-size: 24px; margin-bottom: 8px;" class="devanagari">
                <?php echo $panchang_data['formatted'] ?? 'तिथि लोड हो रही है...'; ?>
            </h2>
            <div style="display: flex; gap: 15px; font-size: 13px; opacity: 0.9; font-weight: 500;">
                <span>🌞 <?php echo date('d M Y'); ?></span>
                <span>✨ <?php echo $panchang_data['nakshatra'] ?? 'नक्षत्र...'; ?></span>
            </div>
        </div>
        <i class="ti ti-sun" style="position: absolute; right: -20px; top: -20px; font-size: 120px; color: rgba(255,255,255,0.1);"></i>
    </div>

    <!-- Quick Stats Row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
        <div class="card" style="margin-bottom: 0; padding: 16px; display: flex; align-items: center; gap: 12px;">
            <div class="avatar avatar-sm avatar-purple"><i class="ti ti-users"></i></div>
            <div>
                <div style="font-size: 18px; font-weight: 800;"><?php echo $total_members; ?></div>
                <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">कुल सदस्य</div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 16px; display: flex; align-items: center; gap: 12px;">
            <div class="avatar avatar-sm avatar-teal"><i class="ti ti-calendar-event"></i></div>
            <div>
                <div style="font-size: 18px; font-weight: 800;"><?php echo $month_events; ?></div>
                <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">इस माह कार्यक्रम</div>
            </div>
        </div>
    </div>

    <!-- Modern Action Grid -->
    <div class="section-header">
        <span class="section-title">त्वरित लिंक</span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px;">
        <a href="/pages/sadasy_banao.php" style="text-decoration: none; text-align: center;">
            <div class="card" style="margin-bottom: 8px; padding: 12px; background: #FFF5F2; border-color: #FFE5D9; box-shadow: none;">
                <i class="ti ti-user-plus" style="font-size: 24px; color: var(--rang-pramukh);"></i>
            </div>
            <span style="font-size: 10px; font-weight: 700; color: var(--text-secondary);">नया सदस्य</span>
        </a>
        <a href="/pages/vansh_vriksha.php" style="text-decoration: none; text-align: center;">
            <div class="card" style="margin-bottom: 8px; padding: 12px; background: #F5F3FF; border-color: #EDE9FE; box-shadow: none;">
                <i class="ti ti-hierarchy-2" style="font-size: 24px; color: #7C3AED;"></i>
            </div>
            <span style="font-size: 10px; font-weight: 700; color: var(--text-secondary);">वंश वृक्ष</span>
        </a>
        <a href="/pages/karyakram.php" style="text-decoration: none; text-align: center;">
            <div class="card" style="margin-bottom: 8px; padding: 12px; background: #F0FDF4; border-color: #DCFCE7; box-shadow: none;">
                <i class="ti ti-calendar-star" style="font-size: 24px; color: #16A34A;"></i>
            </div>
            <span style="font-size: 10px; font-weight: 700; color: var(--text-secondary);">कार्यक्रम</span>
        </a>
        <a href="/pages/parivar_feed.php" style="text-decoration: none; text-align: center;">
            <div class="card" style="margin-bottom: 8px; padding: 12px; background: #FEF2F2; border-color: #FEE2E2; box-shadow: none;">
                <i class="ti ti-message-2-share" style="font-size: 24px; color: #DC2626;"></i>
            </div>
            <span style="font-size: 10px; font-weight: 700; color: var(--text-secondary);">फ़ीड</span>
        </a>
    </div>

    <!-- Upcoming Events Section -->
    <div class="section-header">
        <span class="section-title">आगामी कार्यक्रम</span>
        <a href="/pages/karyakram.php" style="font-size: 12px; font-weight: 600; color: var(--rang-pramukh); text-decoration: none;">सभी देखें</a>
    </div>

    <?php if (empty($upcoming)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px; border-style: dashed; background: transparent;">
            <i class="ti ti-calendar-off" style="font-size: 48px; color: var(--text-muted); opacity: 0.3; margin-bottom: 12px; display: block;"></i>
            <p style="color: var(--text-muted); font-size: 14px;">फिलहाल कोई आगामी कार्यक्रम नहीं है।</p>
        </div>
    <?php else: ?>
        <?php foreach ($upcoming as $e): ?>
            <div class="event-card">
                <div class="avatar avatar-saffron"><?php echo getEventIcon($e['prakar']); ?></div>
                <div style="flex:1">
                    <div style="font-size: 14px; font-weight: 600; color: var(--text-main);"><?php echo s($e['shirshak']); ?></div>
                    <div class="vs-pill"><?php echo s($e['tithi_vs']); ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-main);"><?php echo date('d M', strtotime($e['tithi_gregorian'])); ?></div>
                    <div style="font-size: 10px; color: var(--text-muted);"><?php echo date('Y', strtotime($e['tithi_gregorian'])); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Senior Safety Check -->
    <div class="card" style="margin-top: 32px; background: #FFF1F2; border: 1.5px solid #FDA4AF;">
        <div style="display:flex; align-items:center; gap:16px;">
            <div style="font-size: 32px;">👵</div>
            <div style="flex:1">
                <h3 style="font-size: 15px; color: #9F1239;">आपातकालीन सहायता</h3>
                <p style="font-size: 12px; color: #BE123C; opacity: 0.8;">किसी भी समस्या के लिए परिवार प्रमुख को तुरंत संपर्क करें।</p>
            </div>
            <a href="tel:919999999999" class="btn btn-primary" style="width: auto; background: #E11D48; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3); padding: 8px 16px;">
                <i class="ti ti-phone"></i>
            </a>
        </div>
    </div>

</div>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
