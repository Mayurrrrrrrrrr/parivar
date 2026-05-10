<?php
/**
 * परिवार फ़ीड — संदेश एवं फोटो साझा करें
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();

// फ़ीड प्राप्त करें
$stmt = $pdo->prepare("SELECT f.*, u.naam as user_naam FROM parivar_feed f JOIN users u ON f.user_id = u.id WHERE f.parivar_id = ? ORDER BY f.banaya_at DESC");
$stmt->execute([$parivar_id]);
$feeds = $stmt->fetchAll();
?>

<div class="card">
    <h3>नया संदेश साझा करें</h3>
    <form action="/api/feed.php?action=banao" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div class="form-group">
            <textarea name="sandesh" rows="3" placeholder="आज क्या खास है? यहाँ लिखें..." required></textarea>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="flex: 1;">
                <input type="file" name="photo" accept="image/*" id="feed-photo" style="display: none;" onchange="updateFileName()">
                <label for="feed-photo" style="cursor: pointer; background: #eee; padding: 0.5rem 1rem; border-radius: 4px; display: inline-block;">
                    <i class="fa fa-camera"></i> फोटो जोड़ें
                </label>
                <span id="file-name" style="font-size: 0.8rem; color: #666;"></span>
            </div>
            <button type="submit" style="width: auto;">साझा करें</button>
        </div>
    </form>
</div>

<div class="container" style="max-width: 800px; padding: 0;">
    <?php foreach ($feeds as $f): ?>
        <div class="card feed-item">
            <div class="feed-header">
                <div class="feed-avatar"><?php echo mb_substr($f['user_naam'], 0, 1); ?></div>
                <div>
                    <strong><?php echo s($f['user_naam']); ?></strong><br>
                    <small style="color: #888;"><?php echo time_ago($f['banaya_at']); ?></small>
                </div>
            </div>
            <div class="feed-content" style="margin-top: 1rem;">
                <p style="white-space: pre-line;"><?php echo s($f['sandesh']); ?></p>
                <?php if ($f['photo_url']): ?>
                    <img src="/<?php echo $f['photo_url']; ?>" style="max-width: 100%; border-radius: 8px; margin-top: 1rem; box-shadow: var(--rang-chhaya);">
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function updateFileName() {
        const input = document.getElementById('feed-photo');
        const span = document.getElementById('file-name');
        if (input.files.length > 0) {
            span.textContent = input.files[0].name;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
