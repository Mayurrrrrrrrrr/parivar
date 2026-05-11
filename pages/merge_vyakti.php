<?php
/**
 * व्यक्ति मर्ज करें (Merge Profiles)
 */
require_once __DIR__ . '/../includes/header.php';
requireMukhya();

$parivar_id = currentParivarId();
$stmt = $pdo->prepare("SELECT id, pratham_naam, kul_naam, janm_tithi_gregorian FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
$stmt->execute([$parivar_id]);
$vyaktis = $stmt->fetchAll();
?>
<header class="app-header">
    <a href="/pages/settings.php" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <h1>व्यक्ति मर्ज करें</h1>
</header>
<div class="page-content">
    <?php if (isset($_GET['error'])): ?>
        <div class="alert error" style="color: red; margin-bottom: 16px;">
            <?php 
                if ($_GET['error'] === 'invalid_selection') echo "कृपया सही व्यक्ति चुनें। दोनों व्यक्ति अलग-अलग होने चाहिए।";
                else echo "कुछ तकनीकी समस्या आ गई।";
            ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: var(--bg-card); padding: 16px; border-radius: 8px;">
        <p style="color: var(--text-secondary); margin-bottom: 16px; font-size: 0.9em;">
            यदि गलती से एक ही व्यक्ति के दो प्रोफाइल बन गए हैं, तो आप उन्हें यहाँ मिला (मर्ज कर) सकते हैं।
            'डुप्लीकेट प्रोफाइल' की सभी जानकारी 'मुख्य प्रोफाइल' में जुड़ जाएगी, और फिर डुप्लीकेट प्रोफाइल मिटा दिया जाएगा।
        </p>

        <form action="/api/vyakti.php?action=merge" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">मुख्य प्रोफाइल (जिसे रखना है):</label>
                <select name="primary_id" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">-- चुनें --</option>
                    <?php foreach ($vyaktis as $v): ?>
                        <option value="<?= $v['id'] ?>">
                            <?= htmlspecialchars($v['pratham_naam'] . ' ' . $v['kul_naam']) ?> 
                            <?= $v['janm_tithi_gregorian'] ? ' (DOB: ' . $v['janm_tithi_gregorian'] . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">डुप्लीकेट प्रोफाइल (जिसे हटाना है):</label>
                <select name="duplicate_id" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">-- चुनें --</option>
                    <?php foreach ($vyaktis as $v): ?>
                        <option value="<?= $v['id'] ?>">
                            <?= htmlspecialchars($v['pratham_naam'] . ' ' . $v['kul_naam']) ?>
                            <?= $v['janm_tithi_gregorian'] ? ' (DOB: ' . $v['janm_tithi_gregorian'] . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 24px; width: 100%; padding: 12px; background: var(--rang-pramukh); color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                प्रोफाइल मर्ज करें
            </button>
        </form>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const p = document.querySelector('select[name="primary_id"]').value;
        const d = document.querySelector('select[name="duplicate_id"]').value;
        if (p === d) {
            e.preventDefault();
            alert("मुख्य और डुप्लीकेट प्रोफाइल अलग-अलग होने चाहिए।");
        } else {
            if (!confirm("क्या आप वाकई इन दोनों प्रोफाइल्स को मर्ज करना चाहते हैं? यह क्रिया पूर्ववत (undo) नहीं की जा सकती।")) {
                e.preventDefault();
            }
        }
    });
</script>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
