<?php
/**
 * कार्यक्रम प्रबंधन — सूची और जोड़ना
 */
require_once __DIR__ . '/../includes/header.php';

$parivar_id = getParivarId();

// कार्यक्रम सूची
$stmt = $pdo->prepare("SELECT k.*, v.pratham_naam FROM karyakram k 
                       LEFT JOIN vyakti v ON k.vyakti_id = v.id 
                       WHERE k.parivar_id = ? ORDER BY k.tithi_gregorian DESC");
$stmt->execute([$parivar_id]);
$events = $stmt->fetchAll();

// व्यक्तियों की सूची (dropdown के लिए)
$stmt = $pdo->prepare("SELECT id, pratham_naam, kul_naam FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
$stmt->execute([$parivar_id]);
$persons = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>परिवार के कार्यक्रम</h2>
    <button onclick="document.getElementById('add-event-form').style.display='block'" style="width: auto;">
        <i class="fa fa-plus"></i> नया कार्यक्रम
    </button>
</div>

<div id="add-event-form" class="card" style="display: none; border-color: var(--rang-pramukh);">
    <h3>नया कार्यक्रम जोड़ें</h3>
    <form action="/api/karyakram.php?action=banao" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>शीर्षक</label>
                <input type="text" name="shirshak" required placeholder="जैसे: राहुल का जन्मदिन">
            </div>
            <div class="form-group">
                <label>प्रकार</label>
                <select name="prakar">
                    <option value="janmdin">जन्मदिन</option>
                    <option value="vivah_varshgaanth">विवाह वर्षगाँठ</option>
                    <option value="punya_tithi">पुण्यतिथि</option>
                    <option value="puja">पूजा / उत्सव</option>
                    <option value="any">अन्य</option>
                </select>
            </div>
            <div class="form-group">
                <label>संबंधित व्यक्ति (Optional)</label>
                <select name="vyakti_id">
                    <option value="">-- चुनें --</option>
                    <?php foreach ($persons as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo s($p['pratham_naam'] . ' ' . $p['kul_naam']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>ग्रेगोरियन तिथि</label>
                <input type="date" name="tithi_gregorian" id="g-date" required onchange="convertDate()">
            </div>
        </div>
        <div class="form-group">
            <label>विक्रम संवत् तिथि (Auto-generated)</label>
            <input type="text" name="tithi_vs" id="vs-date" placeholder="तिथि यहाँ दिखेगी..." readonly style="background: #f0f0f0;">
        </div>
        <div style="display: flex; gap: 1rem;">
            <button type="submit">सेव करें</button>
            <button type="button" class="btn-secondary" onclick="document.getElementById('add-event-form').style.display='none'">रद्द करें</button>
        </div>
    </form>
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--rang-seemant); text-align: left;">
                <th style="padding: 1rem;">तारीख</th>
                <th style="padding: 1rem;">कार्यक्रम</th>
                <th style="padding: 1rem;">प्रकार</th>
                <th style="padding: 1rem;">संबंधित</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $e): ?>
                <tr style="border-bottom: 1px solid var(--rang-seemant);">
                    <td style="padding: 1rem;">
                        <?php echo formatGregorianHindi($e['tithi_gregorian']); ?><br>
                        <small style="color: #888;"><?php echo s($e['tithi_vs']); ?></small>
                    </td>
                    <td style="padding: 1rem; font-weight: bold;"><?php echo s($e['shirshak']); ?></td>
                    <td style="padding: 1rem;">
                        <?php 
                            $types = ['janmdin'=>'जन्मदिन', 'vivah_varshgaanth'=>'वर्षगाँठ', 'punya_tithi'=>'पुण्यतिथि', 'puja'=>'पूजा', 'any'=>'अन्य'];
                            echo $types[$e['prakar']] ?? $e['prakar'];
                        ?>
                    </td>
                    <td style="padding: 1rem;"><?php echo s($e['pratham_naam'] ?? 'पूरा परिवार'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function convertDate() {
        const date = document.getElementById('g-date').value;
        if (!date) return;
        
        fetch(`/api/panchang.php?action=convert&gregorian=${date}`)
            .then(r => r.json())
            .then(res => {
                if (res.safalta) {
                    document.getElementById('vs-date').value = res.data.formatted;
                }
            });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
