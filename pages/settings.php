<?php
/**
 * परिवार सेटिंग्स — केवल मुख्य सदस्य के लिए
 */
require_once __DIR__ . '/../includes/header.php';
requireMukhya();

$parivar_id = getParivarId();

// परिवार की जानकारी प्राप्त करें
$stmt = $pdo->prepare("SELECT * FROM parivar WHERE id = ?");
$stmt->execute([$parivar_id]);
$parivar = $stmt->fetch();

// सदस्यों की सूची
$stmt = $pdo->prepare("SELECT * FROM users WHERE parivar_id = ? ORDER BY bhumika, naam");
$stmt->execute([$parivar_id]);
$members = $stmt->fetchAll();
?>

<div class="card">
    <h2>परिवार की जानकारी</h2>
    <form action="/api/family.php?action=update" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>परिवार का नाम</label>
                <input type="text" name="naam" value="<?php echo s($parivar['naam']); ?>" required>
            </div>
            <div class="form-group">
                <label>परिवार कोड (आमंत्रण के लिए)</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="text" id="family-code" value="<?php echo s($parivar['parivar_code']); ?>" readonly style="background: #f0f0f0; font-weight: bold; color: var(--rang-pramukh); flex: 1;">
                    <button type="button" onclick="copyCode()" style="width: auto; padding: 0.8rem;"><i class="fa fa-copy"></i></button>
                </div>
            </div>
            <div class="form-group" style="grid-column: span 2; display: flex; gap: 2rem; align-items: center; background: white; padding: 1rem; border-radius: 8px; border: 1px solid var(--rang-seemant);">
                <div id="qr-container">
                    <?php 
                        $join_url = "https://parivar.yuktaa.com/index.php?code=" . s($parivar['parivar_code']);
                        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($join_url);
                    ?>
                    <img src="<?php echo $qr_url; ?>" alt="QR Code" style="border: 1px solid #ddd; padding: 5px; width: 150px; height: 150px; background: white;">
                </div>
                <div>
                    <h4>परिवार से जोड़ें</h4>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 0.5rem;">यह QR कोड स्कैन करके या नीचे दिए बटन से लिंक शेयर करें।</p>
                    <button type="button" onclick="shareWhatsApp()" style="background: #25D366; width: auto; font-size: 0.9rem; padding: 0.5rem 1rem;">
                        <i class="fab fa-whatsapp"></i> WhatsApp पर भेजें
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>कुल गोत्र</label>
                <input type="text" name="gotra" value="<?php echo s($parivar['gotra']); ?>">
            </div>
            <div class="form-group">
                <label>कुलदेवी</label>
                <input type="text" name="kuldevi" value="<?php echo s($parivar['kuldevi']); ?>">
            </div>
        </div>
        <button type="submit" style="width: auto;">जानकारी अपडेट करें</button>
    </form>
</div>

<div class="card">
    <h3>परिवार के सदस्य</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
        <thead>
            <tr style="border-bottom: 2px solid var(--rang-seemant); text-align: left;">
                <th style="padding: 1rem;">नाम</th>
                <th style="padding: 1rem;">भूमिका</th>
                <th style="padding: 1rem;">कार्य</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m): ?>
                <tr style="border-bottom: 1px solid var(--rang-seemant);">
                    <td style="padding: 1rem;">
                        <strong><?php echo s($m['naam']); ?></strong><br>
                        <small style="color: #888;"><?php echo s($m['email'] ?? $m['phone']); ?></small>
                    </td>
                    <td style="padding: 1rem;">
                        <span class="badge" style="padding: 0.2rem 0.5rem; border-radius: 4px; background: <?php echo $m['bhumika'] == 'mukhya' ? 'var(--rang-pramukh)' : '#eee'; ?>; color: <?php echo $m['bhumika'] == 'mukhya' ? 'white' : '#666'; ?>;">
                            <?php echo $m['bhumika'] == 'mukhya' ? 'मुख्य' : 'सदस्य'; ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;">
                        <?php if ($m['id'] != $_SESSION['user_id']): ?>
                            <button onclick="changeRole(<?php echo $m['id']; ?>)" class="btn-secondary" style="width: auto; padding: 0.3rem 0.6rem; font-size: 0.8rem;">भूमिका बदलें</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" style="border-color: #fcc; background: #fff5f5;">
    <h3 style="color: var(--rang-asafal);">खतरनाक क्षेत्र</h3>
    <p style="font-size: 0.9rem; margin-bottom: 1rem;">परिवार का पूरा डेटा हटाने के लिए यहाँ क्लिक करें। यह वापस नहीं लिया जा सकता।</p>
    <button style="background: var(--rang-asafal); width: auto;">परिवार का डेटा मिटाएँ</button>
</div>

<script>
    function copyCode() {
        const code = document.getElementById('family-code').value;
        navigator.clipboard.writeText(code);
        alert('कोड कॉपी कर लिया गया है!');
    }

    function shareWhatsApp() {
        const code = document.getElementById('family-code').value;
        const text = `हमारे परिवार "<?php echo s($parivar['naam']); ?>" से जुड़ने के लिए इस लिंक पर जाएँ: https://parivar.yuktaa.com/index.php?code=${code}\nकोड: ${code}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
    }

    function changeRole(userId) {
        if (!confirm('क्या आप इस सदस्य की भूमिका बदलना चाहते हैं?')) return;
        // Logic for role change
        alert('सुविधा जल्द ही उपलब्ध होगी।');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
