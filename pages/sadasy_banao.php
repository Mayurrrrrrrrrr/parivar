<?php
/**
 * नया सदस्य जोड़ें (Add Family Member)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = getParivarId();
?>

<div class="card">
    <h2>नया सदस्य जोड़ें</h2>
    <p style="color: #666; margin-bottom: 1.5rem;">परिवार के किसी भी सदस्य की जानकारी यहाँ दर्ज करें।</p>

    <form action="/parivar/api/vyakti.php?action=banao" method="POST" enctype="multipart/form-data">
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
            <div class="form-group">
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
                <label>जन्म तिथि (ग्रेगोरियन)</label>
                <input type="date" name="janm_tithi_gregorian" id="dob_g" class="form-control">
            </div>
            <div class="form-group">
                <label>जन्म तिथि (विक्रम संवत्)</label>
                <input type="text" name="janm_tithi_vs" id="dob_vs" class="form-control" readonly style="background: var(--bg-secondary);" placeholder="ऑटो-कैलकुलेट होगी">
            </div>
            <div class="form-group">
                <label>गोत्र</label>
                <input type="text" name="gotra" class="form-control hindi-type" placeholder="जैसे: भारद्वाज">
            </div>
            <div class="form-group">
                <label>फोटो</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>

            <div class="divider" style="grid-column: span 2;"></div>

            <!-- Parent Selection -->
            <?php
            $stmt = $pdo->prepare("SELECT id, pratham_naam, kul_naam FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
            $stmt->execute([$parivar_id]);
            $all_members = $stmt->fetchAll();
            ?>
            <div class="form-group">
                <label>पिता का नाम (यदि पोर्टल पर हैं)</label>
                <select name="pita_id" class="form-control">
                    <option value="">— चुनें —</option>
                    <?php foreach ($all_members as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo s($m['pratham_naam'] . ' ' . $m['kul_naam']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>माता का नाम (यदि पोर्टल पर हैं)</label>
                <select name="mata_id" class="form-control">
                    <option value="">— चुनें —</option>
                    <?php foreach ($all_members as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo s($m['pratham_naam'] . ' ' . $m['kul_naam']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>भाई / बहन (पोर्टल पर मौजूद सदस्यों को चुनें)</label>
                <select name="sibling_ids[]" class="form-control" multiple style="height: 100px;">
                    <?php foreach ($all_members as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo s($m['pratham_naam'] . ' ' . $m['kul_naam']); ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color:var(--text-muted)">Ctrl दबाकर एक से अधिक चुन सकते हैं। इनसे जुड़ने पर माता-पिता की जानकारी स्वतः जुड़ जाएगी।</small>
            </div>
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">सदस्य सुरक्षित करें</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof setupDateConversion === 'function') {
            setupDateConversion('dob_g', 'dob_vs');
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
