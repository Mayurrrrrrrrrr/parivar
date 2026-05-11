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
    <a href="dashboard.php" style="color:white"><i class="ti ti-arrow-left"></i></a>
    <h1>📜 परिवार का इतिहास</h1>
</header>

<div class="page-content">
    
    <div class="timeline-container" style="position: relative; padding: 20px 0;">
        <!-- Vertical Line -->
        <div style="position: absolute; left: 20px; top: 0; bottom: 0; width: 2px; background: rgba(0,0,0,0.1);"></div>

        <?php if (empty($timeline)): ?>
            <div class="card" style="text-align: center; padding: 40px 20px; border-style: dashed; background: transparent; margin-left: 40px;">
                <i class="ti ti-book-off" style="font-size: 48px; color: var(--text-muted); opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                <p style="color: var(--text-muted); font-size: 14px;">अभी कोई इतिहास दर्ज नहीं है।</p>
            </div>
        <?php else: ?>
            <?php foreach ($timeline as $item): ?>
                <?php 
                    $dateObj = strtotime($item['event_date']);
                    $year = date('Y', $dateObj);
                    if ($year !== $currentYear): 
                        $currentYear = $year;
                ?>
                    <div style="position: relative; margin-bottom: 24px; padding-left: 40px;">
                        <div style="position: absolute; left: 14px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: var(--rang-pramukh); border: 3px solid white; box-shadow: 0 0 0 2px rgba(255,107,0,0.2); z-index: 2;"></div>
                        <div style="font-weight: 800; color: var(--rang-pramukh); font-size: 18px; line-height: 1;">
                            <?php echo $year; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="timeline-item" style="position: relative; margin-bottom: 20px; padding-left: 40px;">
                    <!-- Small Dot -->
                    <div style="position: absolute; left: 17px; top: 10px; width: 8px; height: 8px; border-radius: 50%; background: #CBD5E1; z-index: 1;"></div>
                    
                    <div class="card" style="margin: 0; padding: 16px; border-radius: 12px; transition: transform 0.2s;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <div style="font-size: 20px;">
                                <?php 
                                if ($item['type'] === 'janm') echo "👶";
                                else echo getEventIcon($item['type']);
                                ?>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--text-main);">
                                    <?php 
                                    if ($item['type'] === 'janm') echo s($item['title']) . " का जन्म";
                                    else echo s($item['title']);
                                    ?>
                                </h4>
                                <p style="margin: 4px 0 0; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                                    <?php echo date('d F', $dateObj); ?> • <?php echo s($item['vs_tithi']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<style>
.timeline-item:active .card {
    transform: scale(0.98);
    background: #F8FAFC;
}
</style>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
