<?php
/**
 * सेटिंग्स (v2.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();
$stmt = $pdo->prepare("SELECT * FROM parivar WHERE id = ?");
$stmt->execute([$parivar_id]);
$parivar = $stmt->fetch();

$family_code = $parivar['parivar_code'];
?>

<header class="app-header">
    <h1>⚙️ सेटिंग्स</h1>
</header>

<div class="page-content">
    
    <!-- Family Code Card -->
    <div style="background:linear-gradient(135deg,#B5470B,#8B2500); border-radius:16px; padding:24px; text-align:center; color:white; box-shadow:var(--shadow-floating); margin-bottom:24px;">
        <p style="font-size:12px; opacity:0.7; margin-bottom:8px">परिवार कोड</p>
        <p style="font-size:36px; font-weight:700; letter-spacing:6px; font-family:monospace"><?php echo s($family_code); ?></p>
        <p style="font-size:11px; opacity:0.6; margin-top:8px">इसे शेयर करें — परिवार के लोग इससे जुड़ें</p>
        
        <?php 
            $msg = urlencode("🙏 हमारे परिवार पोर्टल 'परिवार' से जुड़ें! \nकोड: {$family_code}\nलिंक: https://parivar.yuktaa.com/");
            $wa_url = "https://wa.me/?text={$msg}";
            
            // Feature 3: QR Code
            $join_url = "https://parivar.yuktaa.com/?join=" . $family_code;
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=" . urlencode($join_url) . "&color=B5470B&bgcolor=FFF8F0";
        ?>
        <div style="text-align:center; margin:16px 0;">
            <img src="<?php echo $qr_url; ?>" alt="QR Code" style="border-radius:12px; border:3px solid rgba(255,255,255,0.3); width:120px; height:120px; background:white;">
            <p style="font-size:10px; color:rgba(255,255,255,0.6); margin-top:6px">QR Code स्कैन करें</p>
        </div>

        <a href="<?php echo $wa_url; ?>" style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.2); padding:10px 20px; border-radius:24px; color:white; font-size:13px; text-decoration:none" target="_blank">
            <i class="ti ti-brand-whatsapp"></i> WhatsApp पर शेयर करें
        </a>
    </div>

    <!-- User Info -->
    <div class="section-header"><span class="section-title">👤 मेरी जानकारी</span></div>
    <div class="card" style="background:var(--bg-card); border-radius:12px; padding:16px; border:0.5px solid var(--seemant); margin-bottom:12px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <span style="font-size:14px; color:var(--text-secondary)">नाम</span>
            <span style="font-size:14px; font-weight:500"><?php echo s($_SESSION['naam']); ?></span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:14px; color:var(--text-secondary)">भूमिका</span>
            <span class="countdown-chip chip-soon" style="font-size:11px"><?php echo s($_SESSION['bhumika'] === 'mukhya' ? 'मुख्य सदस्य' : 'सदस्य'); ?></span>
        </div>
    </div>

    <?php if (isMukhya()): ?>
    <!-- Family Edit (Mukhya Only) -->
    <div class="section-header"><span class="section-title">🏠 परिवार सेटिंग्स</span></div>
    
    <a href="merge_vyakti.php" class="event-card" style="margin-bottom:12px; display:flex; align-items:center; background:var(--bg-card); padding:12px; border-radius:12px; border:0.5px solid var(--seemant); text-decoration:none;">
        <div class="avatar avatar-sm" style="background:#FFE4D6; color:#D9534F; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:50%; margin-right:12px;"><i class="ti ti-users"></i></div>
        <div style="flex:1;">
            <div style="font-size:14px; font-weight:500; color:var(--text-primary);">प्रोफाइल मर्ज करें</div>
            <div style="font-size:11px; color:var(--text-secondary); margin-top:2px;">डुप्लीकेट व्यक्ति हटाएं</div>
        </div>
        <i class="ti ti-chevron-right" style="color:var(--text-muted)"></i>
    </a>

    <div class="card" style="background:var(--bg-card); border-radius:12px; padding:16px; border:0.5px solid var(--seemant);">
        <form action="/api/family.php?action=update" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>परिवार का नाम</label>
                <input type="text" name="parivar_naam" class="form-control hindi-type" value="<?php echo s($parivar['parivar_naam'] ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn btn-secondary" style="padding:8px 16px; font-size:12px; width:auto;">अपडेट करें</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="section-header"><span class="section-title">🛠️ अन्य</span></div>
    <a href="itihas.php" class="event-card" style="margin-bottom:8px">
        <div class="avatar avatar-sm avatar-purple"><i class="ti ti-history"></i></div>
        <div style="flex:1; font-size:14px; font-weight:500">परिवार का इतिहास</div>
        <i class="ti ti-chevron-right" style="color:var(--text-muted)"></i>
    </a>
    
    <a href="gotra_check.php" class="event-card" style="margin-bottom:8px">
        <div class="avatar avatar-sm avatar-teal"><i class="ti ti-shield-check"></i></div>
        <div style="flex:1; font-size:14px; font-weight:500">गोत्र मिलान जाँच</div>
        <i class="ti ti-chevron-right" style="color:var(--text-muted)"></i>
    </a>

    <div class="divider"></div>
    
    <a href="/api/auth.php?action=logout" class="btn btn-ghost" style="color:var(--rang-asafal); border-color:rgba(226,75,74,0.3)">
        <i class="ti ti-logout"></i> लॉगआउट करें
    </a>

</div>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
