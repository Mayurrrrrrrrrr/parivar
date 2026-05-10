<?php
/**
 * परिवार का इतिहास (v2.0) — Timeline Feature
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();

// Fetch all people births and all events, sorted by date
$stmt = $pdo->prepare("
    (SELECT janm_tithi_gregorian as event_date, janm_tithi_vs as vs_tithi, pratham_naam as title, 'janm' as type FROM vyakti WHERE parivar_id = ? AND janm_tithi_gregorian IS NOT NULL)
    UNION
    (SELECT tithi_gregorian as event_date, tithi_vs as vs_tithi, shirshak as title, prakar as type FROM karyakram WHERE parivar_id = ?)
    ORDER BY event_date ASC
");
$stmt->execute([$parivar_id, $parivar_id]);
$timeline = $stmt->fetchAll();

$currentYear = '';
?>

<header class="app-header">
    <a href="settings.php" style="color:white"><i class="ti ti-arrow-left"></i></a>
    <h1>📜 परिवार का इतिहास</h1>
</header>

<div class="page-content">
    
    <div class="timeline">
        <?php if (empty($timeline)): ?>
            <p class="text-muted text-center">अभी कोई इतिहास दर्ज नहीं है।</p>
        <?php else: ?>
            <?php foreach ($timeline as $item): ?>
                <?php 
                    $year = date('Y', strtotime($item['event_date']));
                    if ($year !== $currentYear): 
                        $currentYear = $year;
                ?>
                    <div class="divider"></div>
                    <div style="font-weight:700; color:var(--rang-pramukh); margin:16px 0 8px; font-size:14px;">
                        सन <?php echo $year; ?>
                    </div>
                <?php endif; ?>

                <div class="timeline-item">
                    <div class="timeline-title">
                        <?php 
                        if ($item['type'] === 'janm') echo "🧑 " . s($item['title']) . " का जन्म";
                        else echo getEventIcon($item['type']) . " " . s($item['title']);
                        ?>
                    </div>
                    <div class="timeline-meta">
                        <?php echo date('d M', strtotime($item['event_date'])); ?> | <?php echo s($item['vs_tithi']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
