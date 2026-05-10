<?php
/**
 * गोत्र मिलान जाँच (v2.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();
$s1 = $_GET['s1'] ?? '';
$s2 = $_GET['s2'] ?? '';
$result = null;

if ($s1 && $s2) {
    if (trim(strtolower($s1)) === trim(strtolower($s2))) {
        $result = ['safal' => false, 'sandesh' => 'सगोत्र विवाह वर्जित है। दोनों का गोत्र समान है।'];
    } else {
        $result = ['safal' => true, 'sandesh' => 'गोत्र भिन्न हैं। विवाह हेतु शास्त्रसम्मत है।'];
    }
}
?>

<header class="app-header">
    <a href="settings.php" style="color:white"><i class="ti ti-arrow-left"></i></a>
    <h1>🛡️ गोत्र जाँच</h1>
</header>

<div class="page-content">
    
    <div class="card" style="background:var(--bg-card); border-radius:16px; padding:20px; box-shadow:var(--shadow-floating); border:0.5px solid var(--seemant);">
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:20px; line-height:1.6;">
            सनातन धर्म के अनुसार सगोत्र विवाह वर्जित माना गया है। यहाँ आप दो व्यक्तियों के गोत्र का मिलान कर सकते हैं।
        </p>

        <form action="gotra_check.php" method="GET">
            <div class="form-group">
                <label>प्रथम पक्ष का गोत्र</label>
                <input type="text" name="s1" class="form-control" value="<?php echo s($s1); ?>" placeholder="जैसे: भरद्वाज" required>
            </div>
            <div class="form-group">
                <label>द्वितीय पक्ष का गोत्र</label>
                <input type="text" name="s2" class="form-control" value="<?php echo s($s2); ?>" placeholder="जैसे: शांडिल्य" required>
            </div>
            <button type="submit" class="btn btn-primary mt-1">जाँच करें</button>
        </form>

        <?php if ($result): ?>
            <div class="alert <?php echo $result['safal'] ? 'alert-success' : 'alert-danger'; ?> mt-2" style="text-align:center;">
                <i class="ti <?php echo $result['safal'] ? 'ti-check' : 'ti-x'; ?>" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                <div style="font-weight:600; font-size:15px;"><?php echo s($result['sandesh']); ?></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="section-header"><span class="section-title">💡 जानकारी</span></div>
    <div class="card" style="background:var(--bg-secondary); border-radius:12px; padding:14px; font-size:12px; color:var(--text-secondary); line-height:1.5;">
        "गोत्र" उस ऋषि के नाम को दर्शाता है जिनसे कुल की उत्पत्ति हुई है। एक ही गोत्र होने का अर्थ है कि दोनों एक ही पूर्वज की संतानें हैं।
    </div>

</div>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
