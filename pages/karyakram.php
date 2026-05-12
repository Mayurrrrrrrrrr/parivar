<?php
/**
 * कार्यक्रम (v2.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $sql_upcoming = "
        SELECT k.*, v.pratham_naam, v.kul_naam,
          COALESCE(
            CASE 
              WHEN k.punravrutti_prakar = 'gregorian_varshik' THEN 
                DATE_ADD(k.tithi_gregorian, INTERVAL (YEAR(CURDATE()) - YEAR(k.tithi_gregorian) + (IF(DATE_ADD(k.tithi_gregorian, INTERVAL YEAR(CURDATE()) - YEAR(k.tithi_gregorian) YEAR) < CURDATE(), 1, 0))) YEAR)
              ELSE k.tithi_gregorian
            END, k.tithi_gregorian
          ) as next_date
        FROM karyakram k LEFT JOIN vyakti v ON k.vyakti_id = v.id 
        WHERE k.parivar_id = ? 
        ORDER BY next_date ASC
    ";
    $stmt = $pdo->prepare($sql_upcoming);
    $stmt->execute([$parivar_id]);
    $events = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT id, pratham_naam, kul_naam FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
    $stmt->execute([$parivar_id]);
    $persons = $stmt->fetchAll();
}
?>

<header class="app-header">
    <?php if ($action !== 'list'): ?>
        <a href="karyakram.php" style="color:white"><i class="ti ti-arrow-left"></i></a>
    <?php endif; ?>
    <h1>📅 कार्यक्रम</h1>
</header>

<div class="page-content">
    <?php if ($action === 'list'): ?>
        <div class="section-header">
            <span class="section-title">सभी कार्यक्रम</span>
            <a href="karyakram.php?action=new" class="btn btn-primary" style="width:auto; padding:6px 12px; font-size:12px">नया जोड़ें</a>
        </div>
        
        <?php foreach ($events as $e): ?>
            <div class="event-card">
                <div class="avatar avatar-saffron"><?php echo getEventIcon($e['prakar']); ?></div>
                <div style="flex:1">
                    <div style="font-size:14px; font-weight:500"><?php echo s($e['shirshak']); ?></div>
                    <div class="vs-pill"><?php echo s($e['tithi_vs']); ?></div>
                </div>
                <div class="dual-date" style="align-items:flex-end">
                    <div class="greg"><?php echo date('d M', strtotime($e['next_date'])); ?></div>
                    <div class="vs"><?php echo date('Y', strtotime($e['next_date'])); ?></div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="card" style="background:var(--bg-card); border-radius:16px; padding:20px; box-shadow:var(--shadow-floating);">
            <h3 style="margin-bottom:20px">नया कार्यक्रम जोड़ें</h3>
            <form action="/api/karyakram.php?action=banao" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                
                <div class="form-group">
                    <label>शीर्षक *</label>
                    <input type="text" name="shirshak" class="form-control hindi-type" required placeholder="जैसे: मयूर का जन्मदिन">
                </div>

                <div class="form-group">
                    <label>प्रकार</label>
                    <select name="prakar" class="form-control">
                        <option value="janmdin">जन्मदिन 🎂</option>
                        <option value="vivah_varshgaanth">विवाह वर्षगाँठ 💍</option>
                        <option value="punya_tithi">पुण्यतिथि 🙏</option>
                        <option value="puja">पूजा/उत्सव ✨</option>
                        <option value="any">अन्य</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>ग्रेगोरियन तिथि *</label>
                    <input type="date" name="tithi_gregorian" id="tithi_gregorian" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>विक्रम संवत् तिथि (ऑटो-कैलकुलेट)</label>
                    <input type="text" name="tithi_vs" id="tithi_vs" class="form-control" readonly style="background:var(--bg-secondary); border-color:transparent">
                    <small id="vs_display" style="color:var(--rang-pramukh); font-weight:500; margin-top:4px; display:block"></small>
                </div>

                <div class="form-group">
                    <label>संबंधित व्यक्ति</label>
                    <select name="vyakti_id" class="form-control">
                        <option value="">— चुनें —</option>
                        <?php foreach ($persons as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo s($p['pratham_naam'] . ' ' . $p['kul_naam']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mt-2">सुरक्षित करें</button>
            </form>
        </div>

        <script>
            document.getElementById('tithi_gregorian').addEventListener('change', function() {
                const date = this.value;
                if (!date) return;
                fetch(`/api/panchang.php?action=convert&gregorian=${date}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.safalta) {
                            document.getElementById('tithi_vs').value = data.data.formatted;
                            document.getElementById('vs_display').textContent = data.data.formatted;
                        }
                    });
            });
        </script>
    <?php endif; ?>
</div>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
