<?php
/**
 * कार्यक्रम — सूची एवं प्रबंधन
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();

// कार्यक्रम सूची
$stmt = $pdo->prepare("SELECT k.*, v.pratham_naam FROM karyakram k LEFT JOIN vyakti v ON k.vyakti_id = v.id WHERE k.parivar_id = ? ORDER BY k.tithi_gregorian");
$stmt->execute([$parivar_id]);
$events = $stmt->fetchAll();

// व्यक्तियों की सूची (dropdown के लिए)
$stmt = $pdo->prepare("SELECT id, pratham_naam FROM vyakti WHERE parivar_id = ?");
$stmt->execute([$parivar_id]);
$persons = $stmt->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem;">
    <!-- सूची -->
    <div class="card">
        <h2>कार्यक्रम सूची</h2>
        <div style="margin-top: 1rem;">
            <?php foreach ($events as $e): ?>
                <div style="padding: 1rem; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 1rem;">
                    <div style="font-size: 1.5rem;"><?php echo getEventIcon($e['prakar']); ?></div>
                    <div style="flex: 1;">
                        <strong><?php echo s($e['shirshak']); ?></strong>
                        <?php if ($e['pratham_naam']): ?><small>(<?php echo s($e['pratham_naam']); ?>)</small><?php endif; ?>
                        <br>
                        <small style="color: #666;"><?php echo date('d M Y', strtotime($e['tithi_gregorian'])); ?> | <?php echo s($e['tithi_vs']); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- नया कार्यक्रम -->
    <div class="card">
        <h3>नया कार्यक्रम जोड़ें</h3>
        <form action="/api/karyakram.php?action=banao" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label>शीर्षक *</label>
                <input type="text" name="shirshak" required placeholder="जैसे: राहुल का जन्मदिन">
            </div>
            <div class="form-group">
                <label>प्रकार</label>
                <select name="prakar">
                    <option value="janmdin">जन्मदिन 🎂</option>
                    <option value="vivah_varshgaanth">विवाह वर्षगाँठ 💍</option>
                    <option value="punya_tithi">पुण्यतिथि 🙏</option>
                    <option value="puja">पूजा/उत्सव ✨</option>
                    <option value="any">अन्य</option>
                </select>
            </div>
            <div class="form-group">
                <label>ग्रेगोरियन तिथि *</label>
                <input type="date" name="tithi_gregorian" id="event_g" required onchange="convertDate()">
            </div>
            <div class="form-group">
                <label>विक्रम संवत् तिथि</label>
                <input type="text" name="tithi_vs" id="event_vs" readonly style="background: #f9f9f9;">
            </div>
            <div class="form-group">
                <label>संबंधित व्यक्ति (Optional)</label>
                <select name="vyakti_id">
                    <option value="">— चुनें —</option>
                    <?php foreach ($persons as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo s($p['pratham_naam']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">सुरक्षित करें</button>
        </form>
    </div>
</div>

<script>
    function convertDate() {
        const date = document.getElementById('event_g').value;
        if (!date) return;
        
        fetch(`/api/panchang.php?action=convert&gregorian=${date}`)
            .then(res => res.json())
            .then(data => {
                if (data.safalta) {
                    document.getElementById('event_vs').value = data.data.formatted;
                }
            });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
