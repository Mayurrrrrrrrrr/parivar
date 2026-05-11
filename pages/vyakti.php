<?php
/**
 * व्यक्ति प्रोफ़ाइल (Premium Redesign v3.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$parivar_id = currentParivarId();

$stmt = $pdo->prepare("SELECT * FROM vyakti WHERE id = ? AND parivar_id = ?");
$stmt->execute([$id, $parivar_id]);
$v = $stmt->fetch();

if (!$v) {
    echo "<div class='page-content' style='text-align:center; padding-top: 40px;'>व्यक्ति नहीं मिला।</div>";
    include '../includes/nav.php';
    exit;
}

$initials = mb_substr($v['pratham_naam'], 0, 1);
$fullName = trim($v['pratham_naam'] . ' ' . ($v['madhya_naam'] ? $v['madhya_naam'] . ' ' : '') . $v['kul_naam']);
?>

<style>
/* Profile Specific Premium Styles */
.profile-hero-v3 {
    background: linear-gradient(135deg, #FF6B35 0%, #F4A261 100%);
    padding: 60px 20px 40px;
    border-radius: 0 0 40px 40px;
    text-align: center;
    color: white;
    position: relative;
    box-shadow: 0 10px 30px rgba(255, 107, 53, 0.2);
    margin-bottom: 30px;
    overflow: hidden;
}

/* Add a subtle background pattern/glow */
.profile-hero-v3::after {
    content: '';
    position: absolute;
    top: -50%; right: -20%;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    border-radius: 50%;
}

.profile-hero-v3 .avatar-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
    z-index: 2;
}

.profile-hero-v3 .avatar-lg {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.9);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    background: white; /* fallback for initials */
    color: var(--rang-pramukh);
    font-size: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    object-fit: cover;
}

.profile-hero-v3 .name-title {
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 4px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 2;
    position: relative;
}

.profile-hero-v3 .glass-pill {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    padding: 4px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-top: 8px;
    z-index: 2;
    position: relative;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin: -50px 20px 24px; /* Pulls it up into the hero */
    position: relative;
    z-index: 10;
}

.stat-card {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 16px 8px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    border: 1px solid rgba(255,107,53,0.08);
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-icon {
    font-size: 24px;
    background: linear-gradient(135deg, #FF6B35, #FF9F1C);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 6px;
}

.stat-value {
    font-weight: 700;
    font-size: 13px;
    color: var(--text-main);
}

.stat-label {
    font-size: 10px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.contact-actions {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 24px;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.action-btn .circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--bg-card);
    color: var(--rang-pramukh);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.03);
    transition: all 0.2s;
}

.action-btn:hover .circle {
    background: var(--rang-pramukh);
    color: white;
    transform: scale(1.05);
    box-shadow: 0 10px 20px rgba(255,107,53,0.2);
}

.action-btn span {
    font-size: 12px;
    color: var(--text-secondary);
    font-weight: 600;
}

.premium-card {
    background: var(--bg-card);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(0,0,0,0.03);
}

.relation-card {
    display: flex;
    align-items: center;
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 12px;
    margin-bottom: 12px;
    text-decoration: none;
    color: inherit;
    border-left: 4px solid var(--rang-dwitiya);
    transition: all 0.2s;
}

.relation-card:hover {
    background: var(--bg-card);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-left-color: var(--rang-pramukh);
    transform: translateX(4px);
}

.story-card {
    position: relative;
    padding: 24px;
    background: linear-gradient(to bottom right, var(--bg-card), var(--bg-secondary));
}
[data-theme="dark"] .story-card { background: var(--bg-card); }

.story-card::before {
    content: '\201C';
    position: absolute;
    top: 10px;
    left: 20px;
    font-size: 80px;
    color: rgba(255,107,53,0.1);
    font-family: serif;
    line-height: 1;
}

.story-text {
    position: relative;
    z-index: 2;
    font-size: 15px;
    line-height: 1.8;
    color: var(--text-secondary);
    font-family: var(--shreni-font-devanagari);
}

/* Custom Header specific to this page to overlap gradient */
.app-header-transparent {
    position: absolute;
    top: 0; left: 0; right: 0;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 100;
}
.app-header-transparent a {
    color: white;
    font-size: 24px;
    text-decoration: none;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>

<div class="app-header-transparent">
    <a href="dashboard.php"><i class="ti ti-arrow-left"></i></a>
    <a href="sadasy_banao.php?id=<?php echo $id; ?>"><i class="ti ti-edit"></i></a>
</div>

<div class="profile-hero-v3">
    <div class="avatar-wrap">
        <?php if ($v['photo_url']): ?>
            <img src="../<?php echo $v['photo_url']; ?>" class="avatar-lg">
        <?php else: ?>
            <div class="avatar-lg"><?php echo $initials; ?></div>
        <?php endif; ?>
    </div>
    
    <div class="name-title"><?php echo s($fullName); ?></div>
    
    <?php if ($v['upnaam']): ?>
        <div class="glass-pill">उर्फ <?php echo s($v['upnaam']); ?></div>
    <?php endif; ?>
</div>

<div class="page-content" style="padding-top: 0;">

    <!-- Astrological Stat Grid -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-flame"></i></div>
            <div class="stat-value"><?php echo s($v['gotra'] ?: '-'); ?></div>
            <div class="stat-label">गोत्र</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-stars"></i></div>
            <div class="stat-value"><?php echo s($v['nakshatra'] ?: '-'); ?></div>
            <div class="stat-label">नक्षत्र</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ti ti-moon"></i></div>
            <div class="stat-value"><?php echo s($v['rashi'] ?: '-'); ?></div>
            <div class="stat-label">राशि</div>
        </div>
    </div>

    <!-- Contact Actions -->
    <?php if ($v['phone'] || $v['email']): ?>
    <div class="contact-actions">
        <?php if ($v['phone']): ?>
            <a href="tel:<?php echo s($v['phone']); ?>" class="action-btn">
                <div class="circle"><i class="ti ti-phone-filled"></i></div>
                <span>कॉल करें</span>
            </a>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $v['phone']); ?>" target="_blank" class="action-btn">
                <div class="circle" style="color: #25D366;"><i class="ti ti-brand-whatsapp"></i></div>
                <span>व्हाट्सएप</span>
            </a>
        <?php endif; ?>
        <?php if ($v['email']): ?>
            <a href="mailto:<?php echo s($v['email']); ?>" class="action-btn">
                <div class="circle"><i class="ti ti-mail-filled"></i></div>
                <span>ईमेल</span>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Birth Information -->
    <div class="premium-card" style="display: flex; align-items: center; gap: 16px;">
        <div style="background: rgba(255,107,53,0.1); padding: 16px; border-radius: 16px; color: var(--rang-pramukh);">
            <i class="ti ti-calendar-event" style="font-size: 32px;"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">जन्म तिथि</div>
            <div style="font-size: 16px; font-weight: 700; color: var(--text-main);">
                <?php echo $v['janm_tithi_gregorian'] ? date('d F Y', strtotime($v['janm_tithi_gregorian'])) : 'अज्ञात'; ?>
            </div>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                <?php echo s($v['janm_tithi_vs'] ?: 'विक्रम संवत् अज्ञात'); ?>
            </div>
        </div>
    </div>

    <!-- Family Relationships -->
    <div class="section-header"><span class="section-title">👨‍👩‍👧‍👦 परिवार के सदस्य</span></div>
    <?php
    $stmt = $pdo->prepare("SELECT s.*, v.pratham_naam, v.kul_naam, v.photo_url FROM sambandh s JOIN vyakti v ON s.vyakti_b_id = v.id WHERE s.vyakti_a_id = ?");
    $stmt->execute([$id]);
    $rels = $stmt->fetchAll();
    
    if (count($rels) > 0) {
        foreach ($rels as $r): ?>
            <a href="vyakti.php?id=<?php echo $r['vyakti_b_id']; ?>" class="relation-card">
                <?php if ($r['photo_url']): ?>
                    <img src="../<?php echo $r['photo_url']; ?>" style="width: 44px; height: 44px; border-radius: 12px; object-fit: cover; margin-right: 14px;">
                <?php else: ?>
                    <div class="avatar avatar-sm avatar-saffron" style="margin-right: 14px; font-size: 16px;"><?php echo mb_substr($r['pratham_naam'], 0, 1); ?></div>
                <?php endif; ?>
                <div style="flex: 1;">
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-main);"><?php echo s($r['pratham_naam'] . ' ' . $r['kul_naam']); ?></div>
                    <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo getRelationHindi($r['sambandh_prakar']); ?></div>
                </div>
                <i class="ti ti-chevron-right" style="color: var(--text-muted);"></i>
            </a>
        <?php endforeach;
    } else {
        echo "<p style='color: var(--text-muted); font-size: 14px; text-align: center; margin: 20px 0;'>कोई सदस्य नहीं जुड़ा है।</p>";
    }
    ?>

    <!-- Biography -->
    <?php if ($v['jeevan_parichay']): ?>
    <div class="section-header" style="margin-top: 24px;"><span class="section-title">📖 जीवन परिचय</span></div>
    <div class="premium-card story-card">
        <div class="story-text">
            <?php echo nl2br(s($v['jeevan_parichay'])); ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
