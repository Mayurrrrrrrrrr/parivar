<?php
/**
 * नया सदस्य जोड़ें (Add Family Member)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = getParivarId();
?>

<div class="page-content">
    <div class="card" style="border-radius: 20px; padding: 24px;">
        <h2 style="margin-bottom: 8px;">नया सदस्य जोड़ें</h2>
        <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 1.5rem;">परिवार के किसी भी सदस्य की जानकारी यहाँ दर्ज करें।</p>

        <div class="login-tabs" style="margin-bottom: 24px;">
            <button class="login-tab active" onclick="switchTab('new')" id="tab_new">नया सदस्य</button>
            <button class="login-tab" onclick="switchTab('link')" id="tab_link">प्रोफ़ाइल लिंक करें</button>
        </div>

        <div id="form_new">
            <form action="/api/vyakti.php?action=banao" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>प्रथम नाम *</label>
                        <input type="text" name="pratham_naam" class="form-control hindi-type" required placeholder="जैसे: सुरेश">
                    </div>
                    <div class="form-group">
                        <label>मध्य नाम</label>
                        <input type="text" name="madhya_naam" class="form-control hindi-type" placeholder="जैसे: चन्द्र">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>कुल/उपनाम *</label>
                        <input type="text" name="kul_naam" class="form-control hindi-type" required placeholder="जैसे: शर्मा">
                    </div>
                    <div class="form-group">
                        <label>लिंग *</label>
                        <select name="ling" class="form-control" required>
                            <option value="purush">पुरुष</option>
                            <option value="stri">स्त्री</option>
                            <option value="anya">अन्य</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>गोत्र</label>
                        <input type="text" name="gotra" class="form-control hindi-type" placeholder="जैसे: भारद्वाज">
                    </div>
                    <div class="form-group">
                        <label>जन्म तिथि (Gregorian)</label>
                        <input type="date" name="janm_tithi_gregorian" id="dob_g" class="form-control" onchange="convertToVS(this, document.getElementById('dob_vs'), document.getElementById('vs_display'))">
                    </div>
                    <div class="form-group">
                        <label>विक्रम संवत्</label>
                        <input type="hidden" name="janm_tithi_vs" id="dob_vs">
                        <div id="vs_display" style="font-size: 12px; color: var(--rang-pramukh); font-weight: 600; padding-top: 8px;">📅 तिथि ऑटो-कैलकुलेट होगी</div>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>फोटो</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                    <div class="divider" style="grid-column: span 2;"></div>

                    <?php
                    $stmt = $pdo->prepare("SELECT id, pratham_naam, kul_naam FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
                    $stmt->execute([$parivar_id]);
                    $all_members = $stmt->fetchAll();
                    ?>
                    <div class="form-group">
                        <label>पिता</label>
                        <select name="pita_id" class="form-control">
                            <option value="">— चुनें —</option>
                            <?php foreach ($all_members as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo s($m['pratham_naam'] . ' ' . $m['kul_naam']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>माता</label>
                        <select name="mata_id" class="form-control">
                            <option value="">— चुनें —</option>
                            <?php foreach ($all_members as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo s($m['pratham_naam'] . ' ' . $m['kul_naam']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="divider" style="grid-column: span 2;"></div>

                    <div style="grid-column: span 2;">
                        <p style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">विस्तृत संबंध</p>
                    </div>
                    <div class="form-group">
                        <label>संबंधी</label>
                        <select name="relative_id" class="form-control">
                            <option value="">— चुनें —</option>
                            <?php foreach ($all_members as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo s($m['pratham_naam'] . ' ' . $m['kul_naam']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>उनका संबंध है</label>
                        <select name="relative_relation" class="form-control">
                            <option value="">— चुनें —</option>
                            <option value="pati">पति</option>
                            <option value="patni">पत्नी</option>
                            <option value="bhai">भाई</option>
                            <option value="behen">बहन</option>
                            <option value="mama">मामा</option>
                            <option value="chacha">चाचा</option>
                            <option value="damad">दामाद</option>
                            <option value="bahu">बहू</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">सदस्य सुरक्षित करें</button>
            </form>
        </div>

        <div id="form_link" style="display: none;">
            <div style="background: var(--bg-secondary); padding: 16px; border-radius: 12px; margin-bottom: 20px; font-size: 12px; line-height: 1.6;">
                <i class="ti ti-info-circle" style="color: var(--rang-pramukh);"></i> यदि व्यक्ति पहले से पोर्टल पर है, तो उनका <b>प्रोफ़ाइल शेयर कोड</b> यहाँ डालें।
            </div>
            
            <form action="/api/vyakti.php?action=link_profile" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label>शेयर कोड *</label>
                    <input type="text" name="share_code" class="form-control" required placeholder="VK-XXXXXX" style="font-family: monospace; font-size: 18px; text-align: center;">
                </div>
                <button type="submit" class="btn btn-primary">प्रोफ़ाइल लिंक करें</button>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.getElementById('form_new').style.display = tab === 'new' ? 'block' : 'none';
        document.getElementById('form_link').style.display = tab === 'link' ? 'block' : 'none';
        document.getElementById('tab_new').classList.toggle('active', tab === 'new');
        document.getElementById('tab_link').classList.toggle('active', tab === 'link');
    }
</script>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
