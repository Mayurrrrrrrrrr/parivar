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

    <form action="/api/vyakti.php?action=banao" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>प्रथम नाम *</label>
                <input type="text" name="pratham_naam" required placeholder="जैसे: सुरेश">
            </div>
            <div class="form-group">
                <label>मध्य नाम</label>
                <input type="text" name="madhya_naam" placeholder="जैसे: चन्द्र">
            </div>
            <div class="form-group">
                <label>कुल/उपनाम *</label>
                <input type="text" name="kul_naam" required placeholder="जैसे: शर्मा">
            </div>
            <div class="form-group">
                <label>लिंग *</label>
                <select name="ling" required>
                    <option value="purush">पुरुष</option>
                    <option value="stri">स्त्री</option>
                    <option value="anya">अन्य</option>
                </select>
            </div>
            <div class="form-group">
                <label>जन्म तिथि (ग्रेगोरियन)</label>
                <input type="date" name="janm_tithi_gregorian" id="dob_g">
            </div>
            <div class="form-group">
                <label>जन्म तिथि (विक्रम संवत्)</label>
                <input type="text" name="janm_tithi_vs" id="dob_vs" readonly style="background: #f9f9f9;" placeholder="ऑटो-कैलकुलेट होगी">
            </div>
            <div class="form-group">
                <label>गोत्र</label>
                <input type="text" name="gotra" placeholder="जैसे: भारद्वाज">
            </div>
            <div class="form-group">
                <label>फोटो</label>
                <input type="file" name="photo" accept="image/*">
            </div>
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit">सदस्य सुरक्षित करें</button>
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
